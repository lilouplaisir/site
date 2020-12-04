<?php

/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Model\Carrier;

use LaPoste\Colissimo\Exception;
use Magento\Framework\Event\Manager;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Sales\Model\Order;
use LaPoste\Colissimo\Helper\Data;
use Magento\Framework\Url;
use LaPoste\Colissimo\Api\TrackingApi;

class LabellingApi implements \LaPoste\Colissimo\Api\Carrier\LabellingApi
{
    const API_BASE_URL = 'https://ws.colissimo.fr/sls-ws/SlsServiceWSRest/';

    /**
     * @var \LaPoste\Colissimo\Logger\Colissimo
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $transportBuilder;
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $orderModel;
    /**
     * @var \LaPoste\Colissimo\Helper\Data
     */
    protected $helperData;
    /**
     * @var \Magento\Framework\Url
     */
    protected $urlHelper;
    /**
     * @var \LaPoste\Colissimo\Api\TrackingApi
     */
    protected $trackingApi;
    /**
     * @var \Magento\Framework\Event\Manager
     */
    protected $eventManager;


    /**
     * LabellingApi constructor.
     * @param \LaPoste\Colissimo\Logger\Colissimo               $logger
     * @param \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
     * @param \Magento\Sales\Model\Order                        $orderModel
     * @param \LaPoste\Colissimo\Helper\Data                    $helperData
     * @param \Magento\Framework\Url                            $urlHelper
     * @param \LaPoste\Colissimo\Api\TrackingApi                $trackingApi
     * @param \Magento\Framework\Event\Manager                  $eventManager
     */
    public function __construct(
        \LaPoste\Colissimo\Logger\Colissimo $logger,
        TransportBuilder $transportBuilder,
        Order $orderModel,
        Data $helperData,
        Url $urlHelper,
        TrackingApi $trackingApi,
        Manager $eventManager
    ) {
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->orderModel = $orderModel;
        $this->helperData = $helperData;
        $this->urlHelper = $urlHelper;
        $this->trackingApi = $trackingApi;
        $this->eventManager = $eventManager;
    }

    /**
     * Return the URL of the given action in the Colissimo Api.
     * @param $action
     * @return string
     */
    protected function getApiUrl($action)
    {
        return self::API_BASE_URL.$action;
    }

    /**
     * Execute a query against the Colissimo Api for the given action, using the given params.
     * It will return an object with the deserialized JSON value.
     *
     * Will throw \LaPoste\Colissimo\Exception\ApiException if response is not 200.
     * @param       $action
     * @param       $responseHandler
     * @param array $params
     * @return mixed
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    protected function query($action, $responseHandler, $params = array())
    {
        $dataJson = json_encode($params, JSON_UNESCAPED_UNICODE);

        $dataJson = iconv("UTF-8", "ASCII//TRANSLIT", $dataJson);

        $url = $this->getApiUrl($action);

        $this->logger->debug(__METHOD__, array('url' => $url));

        $ch = curl_init();
        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL            => $url,
                CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
                CURLOPT_POST           => 1,
                CURLOPT_POSTFIELDS     => $dataJson,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_BINARYTRANSFER => 1,
            )
        );

        $response = curl_exec($ch);
        if (!$response) {
            $curlError = curl_error($ch);
            $curlErrno = curl_errno($ch);
            $this->logger->error(__METHOD__, array(
                'curl_errno' => $curlErrno,
                'curl_error' => $curlError,
            ));
            curl_close($ch);
            throw new Exception\ApiException($curlError, $curlErrno);
        }

        return $responseHandler($ch, $response);
    }


    /**
     * @param $body
     * @return array
     */
    public function parseMultipartBody($body)
    {
        $parts = [];

        preg_match('/--(.*)\b/', $body, $boundary);

        if (!empty($boundary)) {
            $messages = array_filter(
                array_map(
                    'trim',
                    explode($boundary[0], $body)
                )
            );


            foreach ($messages as $message) {
                if ('--' === $message) {
                    break;
                }


                $headers = [];
                list($headerLines, $body) = explode("\r\n\r\n", $message, 2);

                foreach (explode("\r\n", $headerLines) as $headerLine) {
                    list($key, $value) = preg_split('/:\s+/', $headerLine, 2);
                    $headers[strtolower($key)] = $value;
                }

                $parts[$headers['content-id']] = [
                    'headers' => $headers,
                    'body'    => $body,
                ];
            }
        }

        return $parts;
    }

    /**
     * @param $ch
     * @param $response
     * @return array
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    protected function handleMultipartResponse($ch, $response)
    {
        $returnStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch ($returnStatus) {
            case 200:
                curl_close($ch);

                $parts = $this->parseMultipartBody($response);

                if (!empty($parts['<jsonInfos>']) && !empty($parts['<label>'])) {
                    $jsonInfos = $parts['<jsonInfos>']['body'];
                    $label = $parts['<label>']['body'];

                    $cn23 = null;
                    if (!empty($parts['<cn23>']['body'])) {
                        $cn23 = $parts['<cn23>']['body'];
                    }

                    return [json_decode($jsonInfos), $label, $cn23];
                } else {
                    throw new Exception\ApiException(
                        'Bad response format',
                        Exception\ApiException::BAD_RESPONSE_FORMAT
                    );
                }
                break;

            default:
                curl_close($ch);
                $parts = $this->parseMultipartBody($response);

                $body = empty($parts['<jsonInfos>']) ? 0 : json_decode($parts['<jsonInfos>']['body']);

                if (!empty($body)) {
                    $messageType = $body->messages[0]->type;
                    $messageContent = $body->messages[0]->messageContent;

                    $errorMessage = 'Colissimo '.$messageType.' : '.$messageContent;

                    $loggerInfo = array(
                        'returnStatus' => $returnStatus,
                        'messageType' => $messageType,
                        'messageContent' => $messageContent,
                    );
                } else {
                    $errorMessage = 'CURL error:'.$response;

                    $loggerInfo = array(
                        'returnStatus' => $returnStatus,
                    );
                }

                $this->logger->warn(
                    __METHOD__,
                    $loggerInfo
                );

                throw new Exception\ApiException($errorMessage, $returnStatus);
        }
    }

    /**
     * @param $ch
     * @param $response
     * @return mixed
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    protected function handleCheckGenerateLabelResponse($ch, $response)
    {
        curl_close($ch);
        $responseDecode = json_decode($response);

        if (isset($responseDecode->messages[0]) && isset($responseDecode->messages[0]->id) && isset($responseDecode->messages[0]->messageContent)) {
            return $responseDecode;
        } else {
            throw new Exception\ApiException(
                'Bad response format',
                Exception\ApiException::BAD_RESPONSE_FORMAT
            );
        }
    }

    /**
     * @param $ch
     * @param $response
     * @return mixed
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    protected function handleMonopartResponse($ch, $response)
    {
        $returnStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        switch ($returnStatus) {
            case 200:
                $this->logger->debug(__METHOD__, array('response' => $response));
                curl_close($ch);

                return json_decode($response);

            default:
                curl_close($ch);
                $this->logger->warn(
                    __METHOD__,
                    array(
                        'returnStatus' => $returnStatus,
                    )
                );
                throw new Exception\ApiException('CURL error: '.$response, $returnStatus);
        }
    }


    /**
     * @param \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $payload
     * @return array|mixed
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    public function generateLabel(\LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $payload)
    {
        $this->logger->debug('Label generation query', ['payload' => $payload->getPayloadWithoutPassword(), 'method' => __METHOD__]);
        $currentPayload = $payload->assemble();
        $res = $this->query('generateLabel', array($this, 'handleMultipartResponse'), $currentPayload);
        $this->logger->debug('Label generation response', ['response' => $res[0], 'method' => __METHOD__]);
        if (empty($currentPayload['letter']['service']['orderNumber'])) {
            $this->logger->error('Error while generating label: order ID not found.');
            return $res;
        }

        $orderId = $currentPayload['letter']['service']['orderNumber'];
        // Return label : send it to customer. Else send tracking link
        if ($payload->getIsReturnLabel()) {
            $this->eventManager->dispatch(
                'lpc_generate_inward_label_after',
                [
                    'orderIds' => [$orderId],
                    'label'    => $res[1],
                ]
            );
        } else {
            $this->eventManager->dispatch(
                'lpc_generate_outward_label_after',
                [
                    'orderIds' => [$orderId],
                    'label'    => $res[1],
                ]
            );
        }

        return $res;
    }

    /**
     * @param \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $payload
     * @return mixed
     * @throws \LaPoste\Colissimo\Exception\ApiException
     */
    public function checkGenerateLabel(\LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $payload)
    {
        $this->logger->debug('Check generate label query', ['payload' => $payload->getPayloadWithoutPassword(), 'method' => __METHOD__]);
        $res = $this->query('checkGenerateLabel', array($this, "handleCheckGenerateLabelResponse"), $payload->assemble());
        $this->logger->debug('Check generate response', ['response' => $res[0], 'method' => __METHOD__]);

        return $res;
    }

    /**
     * @param array $payload
     * @return mixed
     * @throws Exception\ApiException
     */
    public function listMailBoxPickingDates(array $payload)
    {
        $payloadWithoutPass = $payload;
        unset($payloadWithoutPass['password']);
        $this->logger->debug('List mail box picking dates query', ['payload' => $payloadWithoutPass, 'method' => __METHOD__]);
        $res = $this->query('getListMailBoxPickingDates', array($this, 'handleMonopartResponse'), $payload);
        $this->logger->debug('List mail box picking dates response', ['response' => $res, 'method' => __METHOD__]);

        return $res;
    }

    /**
     * @param array $payload
     * @return array
     */
    public function planPickup(array $payload)
    {
        $payloadWithoutPass = $payload;
        unset($payloadWithoutPass['password']);
        $this->logger->debug('Plan pickup query', ['payload' => $payloadWithoutPass, 'method' => __METHOD__]);
        $res = $this->query('planPickup', array($this, 'handleMonopartResponse'), $payload);
        $this->logger->debug('Plan pickup response', ['response' => $res, 'method' => __METHOD__]);

        return $res;
    }
}

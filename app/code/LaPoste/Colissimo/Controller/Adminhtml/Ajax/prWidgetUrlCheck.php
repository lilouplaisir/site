<?php

/*******************************************************
 * Copyright (C) 2019 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Controller\Adminhtml\Ajax;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use LaPoste\Colissimo\Logger;
use LaPoste\Colissimo\Helper;

class PrWidgetUrlCheck extends Action
{
    protected $logger;
    /**
     * @var \LaPoste\Colissimo\Helper\Data
     */
    private $helperData;

    /**
     * @param Context $context
     * @param \LaPoste\Colissimo\Logger\Colissimo $logger
     * @param \LaPoste\Colissimo\Helper\Data $helperData
     */
    public function __construct(
        Context $context,
        Logger\Colissimo $logger,
        Helper\Data $helperData
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->helperData = $helperData;
    }

    /**
     * Ajax request checking if the widget URL can be called
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        if ($this->getRequest()->isAjax()) {
            try {
                $widgetUrl = $this->helperData->getAdvancedConfigValue('lpc_pr_front/prWidgetUrl');
                if (empty($widgetUrl)) {
                    $isWidgetUrlOk = false;
                    $result = __('Error calling URL:') . ' ' . __('No URL saved.');
                    $this->logger->error('Error during widget URL checking: no URL saved');
                } else {
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $widgetUrl);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/javascript'));
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

                    $this->logger->debug('CURL widget URL checking call', ['url' => $widgetUrl, 'method' => __METHOD__]);

                    $response = curl_exec($ch);

                    $this->logger->debug('CURL widget URL checking response', ['response' => $response, 'method' => __METHOD__]);

                    $responseInfo = curl_getinfo($ch);

                    $this->logger->debug('CURL widget URL checking info', ['info' => $responseInfo, 'method' => __METHOD__]);

                    if ($response && $responseInfo['http_code'] == 200) {
                        $isWidgetUrlOk = true;
                        $result = __('URL is valid');
                    } else {
                        $isWidgetUrlOk = false;
                        $result = __('Error calling URL:') . ' (' . $responseInfo['http_code'] . ') ' . curl_error($ch);
                        $this->logger->error('Error during widget URL checking: ' . curl_error($ch));
                    }
                    curl_close($ch);
                }

                $this->getResponse()
                    ->representJson(json_encode(['success' => $result, 'isWidgetUrlOk' => $isWidgetUrlOk]));
            } catch (\Exception $e) {
                $this->logger->error($e);

                $this->getResponse()
                    ->setHttpResponseCode(\Magento\Framework\Webapi\Exception::HTTP_INTERNAL_ERROR)
                    ->representJson(json_encode(['error' => $e->getMessage()]));
            }
        }
    }
}

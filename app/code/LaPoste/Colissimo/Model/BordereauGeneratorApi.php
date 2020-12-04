<?php

/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Model;

class BordereauGeneratorApi implements \LaPoste\Colissimo\Api\BordereauGeneratorApi
{
    const API_BASE_URL = 'https://ws.colissimo.fr/sls-ws/SlsServiceWS';

    protected $logger;

    protected $helperData;


    public function __construct(
        \LaPoste\Colissimo\Helper\Data $helperData,
        \LaPoste\Colissimo\Logger\Colissimo $logger
    )
    {
        $this->helperData = $helperData;
        $this->logger = $logger;
    }


    public function generateBordereauByParcelsNumbers(
        array $parcelNumbers,
        $login = null,
        $password = null
    )
    {
        if (null === $login) {
            $login = $this->helperData->getAdvancedConfigValue('lpc_general/id_webservices');
        }

        if (null === $password) {
            $password = $this->helperData->getAdvancedConfigValue('lpc_general/pwd_webservices');
        }

        $this->logger->debug(
            __METHOD__ . ' request',
            array(
                'url' => self::API_BASE_URL,
                'login' => $login,
                'parcelNumbers' => $parcelNumbers,
            )
        );


        $soapClient = new \LaPoste\Colissimo\Helper\LpcMTOMSoapClient(
            self::API_BASE_URL . '?wsdl',
            ['exceptions' => true]
        );

        $request = array(
            'contractNumber' => $login,
            'password' => $password,
            'generateBordereauParcelNumberList' => $parcelNumbers,
        );


        $response = $soapClient->generateBordereauByParcelsNumbers($request);
        $response = $response->return;

        $this->logger->debug(
            __METHOD__ . ' response',
            array(
                'response' => $response->messages,
            )
        );


        if (!empty($response->messages->id)) {
            $this->logger->error(
                __METHOD__ . ' error in API response',
                array(
                    'response' => $response->messages,
                )
            );
            throw new \LaPoste\Colissimo\Exception\BordereauGeneratorApiException(
                $response->messages->messageContent,
                $response->messages->id
            );
        }

        return $response;
    }


    public function getBordereauByNumber(
        $bordereauNumber,
        $login = null,
        $password = null
    )
    {
        if (null === $login) {
            $login = $this->helperData->getAdvancedConfigValue('lpc_general/id_webservices');
        }

        if (null === $password) {
            $password = $this->helperData->getAdvancedConfigValue('lpc_general/pwd_webservices');
        }

        $this->logger->debug(
            __METHOD__ . ' request',
            array(
                'url' => self::API_BASE_URL,
                'login' => $login,
                'bordereauNumber' => $bordereauNumber,
            )
        );

        $soapClient = new \LaPoste\Colissimo\Helper\LpcMTOMSoapClient(
            self::API_BASE_URL . '?wsdl',
            ['exceptions' => true]
        );

        $request = array(
            'contractNumber' => $login,
            'password' => $password,
            'bordereauNumber' => $bordereauNumber,
        );


        $response = $soapClient->getBordereauByNumber($request);
        $response = $response->return;

        $this->logger->debug(
            __METHOD__ . ' response',
            array(
                'response' => $response->messages,
            )
        );


        if (!empty($response->messages->id)) {
            $this->logger->error(
                __METHOD__ . ' error in API response',
                array(
                    'response' => $response->messages,
                )
            );
            throw new \LaPoste\Colissimo\Exception\TrackingApiException(
                $response->messages->messageContent,
                $response->messages->id
            );
        }

        return $response;
    }
}

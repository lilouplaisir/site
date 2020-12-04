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

use LaPoste\Colissimo\Model\Config\Source\CustomsCategory;
use Magento\Catalog\Model\ProductRepository;

class GenerateLabelPayload implements \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload
{
    const MAX_INSURANCE_AMOUNT = 1500;
    const FORCED_ORIGINAL_IDENT = 'A';
    const RETURN_LABEL_LETTER_MARK = 'R';
    const RETURN_TYPE_CHOICE_NO_RETURN = 3;

    protected $payload;

    protected $printFormats;

    protected $registeredMailLevel;

    protected $helperData;

    protected $logger;

    protected $isReturnLabel;

    protected $orderItemRepository;

    protected $invoiceService;

    protected $transaction;

    protected $countryOfferHelper;

    protected $productMetadata;

    protected $productRepository;

    public function __construct(
        \LaPoste\Colissimo\Model\Config\Source\PrintFormats $printFormats,
        \LaPoste\Colissimo\Model\Config\Source\RegisteredMailLevel $registeredMailLevel,
        \LaPoste\Colissimo\Helper\Data $helperData,
        \LaPoste\Colissimo\Logger\Colissimo $logger,
        \Magento\Sales\Api\OrderItemRepositoryInterface $orderItemRepository,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Framework\DB\Transaction $transaction,
        \LaPoste\Colissimo\Helper\CountryOffer $countryOfferHelper,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        ProductRepository $productRepository
    ) {
        $this->payload = array(
            'letter' => array(
                'service' => array(),
                'parcel' => array(),
            ),
        );

        $this->printFormats = $printFormats;
        $this->registeredMailLevel = $registeredMailLevel;
        $this->helperData = $helperData;
        $this->logger = $logger;
        $this->isReturnLabel = false;
        $this->invoiceService = $invoiceService;
        $this->transaction = $transaction;
        $this->orderItemRepository = $orderItemRepository;
        $this->countryOfferHelper = $countryOfferHelper;
        $this->productMetadata = $productMetadata;
        $this->productRepository = $productRepository;
    }

    public function withSender(array $sender = null, $storeId = null)
    {
        if (null === $sender) {
            $sender = array(
                'companyName' => $this->helperData->getConfigValue(
                    'general/store_information/name',
                    $storeId
                ),
                'street'      => $this->helperData->getConfigValue(
                    'general/store_information/street_line1',
                    $storeId
                ),
                'street2'     => $this->helperData->getConfigValue(
                    'general/store_information/street_line2',
                    $storeId
                ),
                'countryCode' => $this->helperData->getConfigValue(
                    'general/store_information/country_id',
                    $storeId
                ),
                'city'        => $this->helperData->getConfigValue(
                    'general/store_information/city',
                    $storeId
                ),
                'zipCode'     => $this->helperData->getConfigValue(
                    'general/store_information/postcode',
                    $storeId
                ),
                'email'       => $this->helperData->getConfigValue(
                    'sales_email/shipment_comment/identity',
                    $storeId
                ),
            );
        }

        $this->payload['letter']['sender'] = array(
            'address' => array(
                'companyName' => $sender['companyName'] ?? '',
                'firstName'   => $sender['firstName'] ?? '',
                'lastName'    => $sender['lastName'] ?? '',
                'line2'       => $sender['street'] ?? '',
                'countryCode' => $sender['countryCode'],
                'city'        => $sender['city'],
                'zipCode'     => $sender['zipCode'],
                'email'       => $sender['email'] ?? '',
            ),
        );

        if (!empty($sender['street2'])) {
            $this->payload['letter']['sender']['address']['line3'] = $sender['street2'];
        }

        $payloadCountryCode = $this->payload['letter']['sender']['address']['countryCode'];

        $this->payload['letter']['sender']['address']['countryCode'] = $this->countryOfferHelper->getMagentoCountryCodeFromSpecificDestination($payloadCountryCode) === false
            ? $payloadCountryCode
            : $this->countryOfferHelper->getMagentoCountryCodeFromSpecificDestination($payloadCountryCode);

        return $this;
    }

    public function withCommercialName($commercialName = null, $storeId = null)
    {
        if (null === $commercialName) {
            $commercialName = $this->helperData->getConfigValue(
                'general/store_information/name',
                $storeId
            );
        }


        if (empty($commercialName)) {
            unset($this->payload['letter']['service']['commercialName']);
        } else {
            $this->payload['letter']['service']['commercialName'] = $commercialName;
        }

        return $this;
    }

    public function withContractNumber($contractNumber = null, $storeId = null)
    {
        if (null === $contractNumber) {
            $contractNumber = $this->helperData->getAdvancedConfigValue(
                'lpc_general/id_webservices',
                $storeId
            );
        }

        if (empty($contractNumber)) {
            unset($this->payload['contractNumber']);
        } else {
            $this->payload['contractNumber'] = $contractNumber;
        }

        return $this;
    }

    public function withPassword($password = null, $storeId = null)
    {
        if (null === $password) {
            $password = $this->helperData->getAdvancedConfigValue(
                'lpc_general/pwd_webservices',
                $storeId
            );
        }

        if (empty($password)) {
            unset($this->payload['password']);
        } else {
            $this->payload['password'] = $password;
        }

        return $this;
    }

    public function withAddressee(array $addressee, $orderRef = null, $storeId = null)
    {
        $this->payload['letter']['addressee'] = array(
            'address' => array(
                'companyName' => $addressee['companyName'] ?? '',
                'firstName'   => $addressee['firstName'] ?? '',
                'lastName'    => $addressee['lastName'] ?? '',
                'line2'       => $addressee['street'],
                'countryCode' => $addressee['countryCode'],
                'city'        => $addressee['city'],
                'zipCode'     => $addressee['zipCode'],
                'email'       => $addressee['email'] ?? '',
                'mobileNumber'=> $addressee['mobileNumber'] ?? '',
            ),
        );

        $this->setFtdGivenCountryCodeId($addressee['countryCode'], $addressee['zipCode'], $storeId);

        if (!empty($addressee['street2'])) {
            $this->payload['letter']['addressee']['address']['line3'] = $addressee['street2'];
        }

        if ($this->isReturnLabel) {
            if ($this->helperData->getConfigValue(
                'lpc_advanced/lpc_return_labels/showServiceInformation',
                $storeId
            )) {
                $this->payload['letter']['addressee']['serviceInfo'] =
                    $this->helperData->getConfigValue(
                        'lpc_advanced/lpc_return_labels/serviceInformation',
                        $storeId
                    );
            }

            if ($this->helperData->getConfigValue(
                'lpc_advanced/lpc_return_labels/showOrderRef',
                $storeId
            )) {
                if (!empty($orderRef)) {
                    $this->payload['letter']['addressee']['codeBarForReference'] = "true";
                    $this->payload['letter']['addressee']['addresseeParcelRef'] = $orderRef;
                } else {
                    $this->logger->error(
                        'Unknown orderRef',
                        ['given' => $orderRef]
                    );
                }
            }
        }

        $payloadCountryCode = $this->payload['letter']['addressee']['address']['countryCode'];

        $this->payload['letter']['addressee']['address']['countryCode'] = $this->countryOfferHelper->getMagentoCountryCodeFromSpecificDestination($payloadCountryCode) === false
            ? $payloadCountryCode
            : $this->countryOfferHelper->getMagentoCountryCodeFromSpecificDestination($payloadCountryCode);

        return $this;
    }

    public function withPickupLocationId($pickupLocationId)
    {
        if (null === $pickupLocationId) {
            unset($this->payload['letter']['parcel']['pickupLocationId']);
        } else {
            $this->payload['letter']['parcel']['pickupLocationId'] = $pickupLocationId;
        }

        return $this;
    }


    public function withProductCode($productCode)
    {
        $allowedProductCodes = array(
            'A2P'  , 'ACCI' , 'BDP'  , 'BPR'  ,
            'CDS'  , 'CMT'  , 'COL'  , 'COLD' ,
            'COLI' , 'COM'  , 'CORE' , 'CORI' ,
            'DOM'  , 'DOM'  , 'DOS'  , 'DOS'  ,
            'ECO'  , 'J+1'  , 'COLR' ,
        );

        if (!in_array($productCode, $allowedProductCodes)) {
            $this->logger->error(
                'Unknown productCode',
                ['given' => $productCode, 'known' => $allowedProductCodes]
            );
            throw new \Exception('Unknown Product code!');
        }

        $this->payload['letter']['service']['productCode'] = $productCode;

        $this->payload['letter']['service']['returnTypeChoice'] = self::RETURN_TYPE_CHOICE_NO_RETURN;

        return $this;
    }

    protected function setFtdGivenCountryCodeId($destinationCountryId, $destinationPostcode, $storeId = null)
    {
        if ($this->countryOfferHelper->getFtdRequiredForDestination($destinationCountryId, $destinationPostcode) === true
            && $this->helperData->getAdvancedConfigValue('lpc_labels/isFtd', $storeId)) {
            $this->payload['letter']['parcel']['ftd'] = true;
        } else {
            unset($this->payload['letter']['parcel']['ftd']);
        }
    }

    public function withDepositDate(\DateTime $depositDate)
    {
        $now = new \DateTime();
        if ($depositDate->getTimestamp() < $now->getTimestamp()) {
            $this->logger->warning(
                'Given DepositDate is in the past, using today instead.',
                ['given' => $depositDate, 'now' => $now]
            );
            $depositDate = $now;
        }

        $this->payload['letter']['service']['depositDate'] = $depositDate->format('Y-m-d');

        return $this;
    }

    public function withPreparationDelay($delay = null, $storeId = null)
    {
        if (null === $delay) {
            $delay = $this->helperData->getAdvancedConfigValue(
                'lpc_labels/averagePreparationDelay',
                $storeId
            );
        }

        $depositDate = new \DateTime();

        $delay = (int)$delay;
        if ($delay > 0) {
            $depositDate->add(new \DateInterval("P{$delay}D"));
        } else {
            $this->logger->warning(
                'Preparation delay was not applied because it was negative or zero!',
                ['given' => $delay]
            );
        }

        return $this->withDepositDate($depositDate);
    }

    public function withOutputFormat($outputFormat = null, $storeId = null)
    {
        if (null === $outputFormat) {
            $outputFormat = $this->helperData->getAdvancedConfigValue(
                'lpc_labels/outwardPrintFormat',
                $storeId
            );
        }

        $allowedPrintFormats = array_map(
            function ($v) {
                return $v['value'];
            },
            $this->printFormats->toOptionArray()
        );

        if (!in_array($outputFormat, $allowedPrintFormats)) {
            $this->logger->error(
                'Unknown outputFormat',
                ['given' => $outputFormat, 'known' => $allowedPrintFormats]
            );
            throw new \Magento\Framework\Exception\LocalizedException(__('Bad output format'));
        }

        $this->payload['outputFormat'] = array(
            'x' => 0,
            'y' => 0,
            'outputPrintingType' => $outputFormat,
        );

        return $this;
    }

    public function withOrderNumber($orderNumber)
    {
        $this->payload['letter']['service']['orderNumber'] = $orderNumber;
		$this->payload['letter']['sender']['senderParcelRef'] = $orderNumber;

        return $this;
    }

    public function withPackage(
        \Magento\Framework\DataObject $package,
        array $items
    ) {
        $weightUnit = $package['weight_units'];

        if (empty($package['weight'])) {
            $totalWeight = 0;

            foreach ($items as $piece) {
                if (!empty($piece['row_weight'])) {
                    $weight = (double)$piece['row_weight'];
                } else {
                    $weight = (double)$piece['weight'] * $piece['qty'];
                }
                if ($weight < 0) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('Weight cannot be negative!')
                    );
                }

                $zendWeight = new \Zend_Measure_Weight(number_format($weight, 2, '.', ''), $weightUnit, new \Zend_Locale('en_US'));

                $weightInKg = (double)$zendWeight->convertTo(\Zend_Measure_Weight::KILOGRAM);
                $totalWeight += $weightInKg;
            }
        } else {

            $zendWeight = new \Zend_Measure_Weight(number_format($package['weight'], 2, '.', ''), $weightUnit, new \Zend_Locale('en_US'));

            $totalWeight = (double)$zendWeight->convertTo(\Zend_Measure_Weight::KILOGRAM);
        }


        if ($totalWeight < 0.01) {
            $totalWeight = 0.01;
        }

        $totalWeight = number_format($totalWeight, 2);

        $this->payload['letter']['parcel']['weight'] = $totalWeight;

        return $this;
    }

    public function withCustomsDeclaration(
        \Magento\Sales\Model\Order\Shipment $shipment,
        array $items,
        $destinationCountryId,
        $destinationPostcode,
        $storeId = null
    ) {
        if (!$this->helperData->getAdvancedConfigValue(
            'lpc_labels/isUsingCustomsDeclarations',
            $storeId
        )) {
            return $this;
        }

        $order = $shipment->getOrder();

        $invoiceCollection = $order->getInvoiceCollection();
        $invoiceCollectionCount = $invoiceCollection->count();

        // Check there is an invoice
        if ($invoiceCollectionCount == 0) {
            // customs declaration needs some invoice information
            $this->logger->error(__METHOD__ . ' : ' . __('Invoice missing for order #%1 to create label', $order->getIncrementId()));
            throw new \Exception(__('Invoice missing for order #%1 to create label', $order->getIncrementId()));
        }

        // No need details if no CN23 required
        if (!$this->countryOfferHelper->getIsCn23RequiredForDestination($destinationCountryId, $destinationPostcode)) {
            return $this;
        }

        // If CN23 and return label, we can only manage if we have one invoice only for the order
        if ($this->isReturnLabel && $invoiceCollectionCount > 1) {
            $this->logger->error(__METHOD__ . ' : ' . __('There must be only one invoice on order #%1 to create return label with CN23.', $order->getIncrementId()));
            throw new \Exception(__('There must be only one invoice on order #%1 to create return label with CN23.', $order->getIncrementId()));
        }

        $invoice = $invoiceCollection->getLastItem();

        $defaultHsCode = $this->helperData->getAdvancedConfigValue(
            'lpc_labels/defaultHsCode',
            $storeId
        );

        $customsArticles = array();

        foreach ($items as $piece) {
            if (empty($piece['currency'])) {
                // this happens when packages have been created by main magento process
                $piece = $this->rebuildPiece($piece);
            }

            $customsArticle = array(
                'description' => substr($piece['name'], 0, 64),
                'quantity' => $piece['qty'],
                'weight' => $piece['weight'], // unitary value
                'value' => (int)$piece['customs_value'], // unitary value
                'currency' => $piece['currency'],
                'artref' => substr($piece['sku'], 0, 44),
                'originalIdent' => self::FORCED_ORIGINAL_IDENT,
                'originCountry' => $piece['country_of_manufacture'],
                'hsCode'        => $piece['lpc_hs_code'],
            );

            // Set specific HS code if defined on the product
            if (empty($customsArticle['hsCode'])) {
                $customsArticle['hsCode'] = $defaultHsCode;
            }

            $customsArticles[] = $customsArticle;
        }

        $this->payload['letter']['customsDeclarations'] = array(
            'includeCustomsDeclarations' => 1,
            'contents' => array(
                'article' => $customsArticles,
            ),
            'invoiceNumber' => $invoice->getIncrementId(),
        );

        $transportationAmount = $order->getShippingAmount();

        // payload want centi-currency for these fields.
        $this->payload['letter']['service']['totalAmount'] = (int)($transportationAmount * 100);
        $this->payload['letter']['service']['transportationAmount'] = (int)($transportationAmount * 100);

        $customsCategory = $this->helperData->getAdvancedConfigValue(
            'lpc_labels/defaultCustomsCategory',
            $storeId
        );
        if (empty($customsCategory)) {
            $customsCategory = CustomsCategory::OTHER;
        }
        if ($this->isReturnLabel) {
            $customsCategory = CustomsCategory::RETURN_OF_ARTICLES;
        }


        $this->payload['letter']['customsDeclarations']['contents']['category'] = array(
            'value' => $customsCategory,
        );


        if ($this->isReturnLabel) {
            $originalInvoiceDate = \DateTime::createFromFormat(
                'Y-m-d H:i:s',
                $invoice->getCreatedAt()
            )
                ->format('Y-m-d');

            $originalParcelNumber = $this->getOriginalParcelNumberFromInvoice($invoice);

            $this->payload['letter']['customsDeclarations']['contents']['original'] =
                array(
                    array(
                        'originalIdent'         => self::FORCED_ORIGINAL_IDENT,
                        'originalInvoiceNumber' => $invoice->getIncrementId(),
                        'originalInvoiceDate'   => $originalInvoiceDate,
                        'originalParcelNumber'  => $originalParcelNumber,
                    ),
                );
        }

        return $this;
    }

    protected function getOriginalParcelNumberFromInvoice(
        \Magento\Sales\Api\Data\InvoiceInterface $invoice
    ) {
        $order = $invoice->getOrder();
        $tracksCollection = $order->getTracksCollection()
            ->setOrder('created_at', 'desc');

        // find the last non-return track number for this order
        foreach ($tracksCollection as $track) {
            $trackNumber = $track->getTrackNumber();

            if (self::RETURN_LABEL_LETTER_MARK !== substr($trackNumber, 1, 1)) {
                return $trackNumber;
            }
        }
    }



    public function withInsuranceValue($amount, $productCode, $countryCode)
    {
        if (!empty($this->payload['letter']['parcel']['recommendationLevel'])) {
            $this->logger->error(
                'RecommendationLevel and InsuranceValue are mutually incompatible.',
                ['wanting' => 'insuranceValue', 'alreadyGiven' => 'recommendationLevel']
            );
            throw new \Magento\Framework\Exception\LocalizedException(
                __('RecommendationLevel and InsuranceValue are mutually incompatible.')
            );
        }

        $amount = (double)$amount;

        $productCodeAvailableForInsurance = array("DOS", "COL", "BPR", "A2P", "CDS", "CORE", "CORI", "COLI");

        if (!in_array($productCode, $productCodeAvailableForInsurance) || ($productCode == "DOS" && $countryCode != "FR")) {
            return $this;
        }

        if ($amount > self::MAX_INSURANCE_AMOUNT) {
            $this->logger->warning(
                'Given insurance value amount is too big, forced to ' . self::MAX_AMOUNT,
                ['given' => $amount, 'max' => self::MAX_INSURANCE_AMOUNT]
            );

            $amount = self::MAX_INSURANCE_AMOUNT;
        }

        if ($amount > 0) {
            // payload want centi-euros for this field.
            $this->payload['letter']['parcel']['insuranceValue'] = (int)($amount * 100);
        } else {
            $this->logger->warning(
                'Insurance value was not applied because it was negative or zero!',
                ['given' => $amount]
            );
        }

        return $this;
    }

    public function withRecommendationLevel($recommendation)
    {
        $allowedRegisteredMailLevel = $this->registeredMailLevel->toArray();
        unset($allowedRegisteredMailLevel[null]);
        $allowedRegisteredMailLevel = array_keys($allowedRegisteredMailLevel);

        if (!in_array($recommendation, $allowedRegisteredMailLevel)) {
            $this->logger->error(
                'Unknown recommendation level',
                ['given' => $recommendation, 'known' => $allowedRegisteredMailLevel]
            );
            throw new \Magento\Framework\Exception\LocalizedException(__('Bad recommendation level'));
        }

        if (!empty($this->payload['letter']['parcel']['insuranceValue'])) {
            $this->logger->error(
                'RecommendationLevel and InsuranceValue are mutually incompatible.',
                ['wanting' => 'recommendationLevel', 'alreadyGiven' => 'insuranceValue']
            );
            throw new \Magento\Framework\Exception\LocalizedException(
                __('RecommendationLevel and InsuranceValue are mutually incompatible.')
            );
        }

        $this->payload['letter']['parcel']['recommendationLevel'] = $recommendation;

        return $this;
    }

    public function withCODAmount($amount)
    {
        $amount = (double)$amount;

        if ($amount > 0) {
            $this->payload['letter']['parcel']['COD'] = true;
            // payload want centi-euros for this field.
            $this->payload['letter']['parcel']['CODAmount'] = (int)($amount * 100);
        } else {
            $this->logger->warning(
                'CODAmount was not applied because it was negative or zero!',
                ['given' => $amount]
            );
        }

        return $this;
    }


    public function withReturnReceipt($value = true)
    {
        if ($value) {
            $this->payload['letter']['parcel']['returnReceipt'] = true;
        } else {
            unset($this->payload['letter']['parcel']['returnReceipt']);
        }

        return $this;
    }

    public function withInstructions($instructions)
    {
        if (empty($instructions)) {
            unset($this->payload['letter']['parcel']['instructions']);
        } else {
            $this->payload['letter']['parcel']['instructions'] = $instructions;
        }

        return $this;
    }


    public function withCuserInfoText($info = null)
    {
        $customFields = array();

        if (null === $info) {
            $info = $this->helperData->getCuserInfoText();
        }

        $customField = array(
            "key" => "CUSER_INFO_TEXT",
            "value" => $info,
        );

        $customFields[] = $customField;

        $this->payload['fields'] = array(
            'customField' => $customFields,
        );

        return $this;
    }

    public function isReturnLabel($isReturnLabel = true)
    {
        $this->isReturnLabel = $isReturnLabel;

        return $this;
    }

    public function getIsReturnLabel()
    {
        return $this->isReturnLabel;
    }

    public function checkConsistency()
    {
        $this->checkPickupLocationId();
        $this->checkCommercialName();
        $this->checkSenderAddress();
        $this->checkAddresseeAddress();

        return $this;
    }

    public function assemble()
    {
        return array_merge($this->payload); // makes a copy
    }


    protected function checkPickupLocationId()
    {
        $productCodesNeedingPickupLocationIdSet = [
            'A2P', 'BPR', 'ACP', 'CDI',
            'CMT', 'BDP', 'PCS',
        ];

        if (in_array($this->payload['letter']['service']['productCode'], $productCodesNeedingPickupLocationIdSet)
            && (
                !isset($this->payload['letter']['parcel']['pickupLocationId'])
                ||
                empty($this->payload['letter']['parcel']['pickupLocationId'])
            )) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The ProductCode used requires that a pickupLocationId is set!')
            );
        }

        if (!in_array($this->payload['letter']['service']['productCode'], $productCodesNeedingPickupLocationIdSet)
            && isset($this->payload['letter']['parcel']['pickupLocationId'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The ProductCode used requires that a pickupLocationId is *not* set!')
            );
        }
    }

    protected function checkCommercialName()
    {
        $productCodesNeedingCommercialName = [
            'A2P', 'BPR',
        ];

        if (in_array($this->payload['letter']['service']['productCode'], $productCodesNeedingCommercialName)
            && (
                !isset($this->payload['letter']['service']['commercialName'])
                ||
                empty($this->payload['letter']['service']['commercialName'])
            )) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The ProductCode used requires that a commercialName is set!')
            );
        }
    }

    protected function checkSenderAddress()
    {
        $address = $this->payload['letter']['sender']['address'];

        if (empty($address['companyName'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('companyName must be set in Sender address!')
            );
        }

        if (empty($address['line2'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('line2 must be set in Sender address!')
            );
        }

        if (empty($address['countryCode'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('countryCode must be set in Sender address!')
            );
        }

        if (empty($address['zipCode'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('zipCode must be set in Sender address!')
            );
        }

        if (empty($address['city'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('city must be set in Sender address!')
            );
        }
    }

    protected function checkAddresseeAddress()
    {
        $productCodesNeedingMobileNumber = [
            'A2P', 'BPR',
        ];

        $address = $this->payload['letter']['addressee']['address'];

        if (empty($address['companyName'])
            && (empty($address['firstName']) || empty($address['lastName']))
        ) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('companyName or (firstName + lastName) must be set in Addressee address!')
            );
        }

        if ($this->isReturnLabel) {
            if (empty($address['companyName'])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('companyName must be set in Addressee address for return label!')
                );
            }
        }

        if (empty($address['line2'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('line2 must be set in Addressee address!')
            );
        }

        if (empty($address['countryCode'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('countryCode must be set in Addressee address!')
            );
        }

        if (empty($address['zipCode'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('zipCode must be set in Addressee address!')
            );
        }

        if (empty($address['city'])) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('city must be set in Addressee address!')
            );
        }

        if (in_array($this->payload['letter']['service']['productCode'], $productCodesNeedingMobileNumber)
            && (
                !isset($address['mobileNumber'])
                ||
                empty($address['mobileNumber'])
            )) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('The ProductCode used requires that a mobile number is set!')
            );
        }
    }

    protected function rebuildPiece(array $piece)
    {
        $orderItem = $this->orderItemRepository
            ->get($piece['order_item_id']);
        $order = $orderItem->getOrder();

        $piece['currency'] = $order->getOrderCurrencyCode();
        $piece['sku'] = $orderItem->getSku();
        $piece['country_of_manufacture'] = $orderItem->getProduct()->getCountryOfManufacture();
        $piece['lpc_hs_code']            = $orderItem->getProduct()->getLpcHsCode();

        return $piece;
    }

    /**
     * @param $trackingNumber
     */
    public function setOriginalTrackingNumber($trackingNumber)
    {
        $this->payload['letter']['customsDeclarations']['contents']['original'][0]['originalParcelNumber'] = $trackingNumber;
    }

    public function resetPayload()
    {
        $this->payload = array();

        return $this;
    }

    /**
     * Return payload without password for logging by example
     *
     * @return array
     */
    public function getPayloadWithoutPassword()
    {
       $payloadWithoutPass = $this->payload;

       unset($payloadWithoutPass['password']);

       return $payloadWithoutPass;
    }
}

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

use LaPoste\Colissimo\Helper\Data;
use LaPoste\Colissimo\Helper\CountryOffer;
use LaPoste\Colissimo\Helper\Pdf;
use \Magento\Shipping\Model\Carrier\AbstractCarrierOnline;
use \Magento\Shipping\Model\Carrier\CarrierInterface;
use \Magento\Framework\DataObject;
use Magento\Quote\Model\Quote\Address\RateRequest;
use LaPoste\Colissimo\Model\Shipping\ReturnLabelGenerator;
use \Magento\Customer\Model\Session;
use \Magento\Framework\ObjectManagerInterface;
use \LaPoste\Colissimo\Api\Carrier\OffersApi;


class Colissimo extends AbstractCarrierOnline implements CarrierInterface
{
    const CODE = 'colissimo';

    const CODE_SHIPPING_METHOD_RELAY = 'pr';
    const CODE_SHIPPING_METHOD_DOMICILE_SS = 'domiciless';
    const CODE_SHIPPING_METHOD_DOMICILE_AS = 'domicileas';
    const CODE_SHIPPING_METHOD_EXPERT = 'expert';
    const CODE_SHIPPING_METHOD_FLASH_SS = 'flashss';
    const CODE_SHIPPING_METHOD_FLASH_AS = 'flashas';
    const URL_SUIVI_COLISSIMO = "https://www.laposte.fr/professionnel/outils/suivre-vos-envois?code={lpc_tracking_number}";

    const METHODS_NAME = [
        self::CODE_SHIPPING_METHOD_DOMICILE_SS,
        self::CODE_SHIPPING_METHOD_DOMICILE_AS,
        self::CODE_SHIPPING_METHOD_RELAY,
        self::CODE_SHIPPING_METHOD_EXPERT,
        self::CODE_SHIPPING_METHOD_FLASH_SS,
        self::CODE_SHIPPING_METHOD_FLASH_AS,
    ];

    protected $_code = self::CODE;

    protected $rateFactory = null;

    protected $rateErrorFactory = null;

    protected $generateLabelPayload;

    protected $labellingApi;

    protected $helperData;

    protected $logger;

    protected $helperCountryOffer;
    /**
     * @var \LaPoste\Colissimo\Helper\Pdf
     */
    protected $helperPdf;
    /**
     * @var \LaPoste\Colissimo\Model\Shipping\ReturnLabelGenerator
     */
    private $returnLabelGenerator;

    protected $customerSession;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    protected $checkoutSession;

    /**
     * @var \LaPoste\Colissimo\Model\Carrier\OffersApi
     */
    protected $offersApi;

    protected $timeZone;

    /**
     * Colissimo constructor.
     *
     * @param \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload         $generateLabelPayload
     * @param \LaPoste\Colissimo\Api\Carrier\LabellingApi                 $labellingApi
     * @param \LaPoste\Colissimo\Logger\Colissimo                         $colissimoLogger
     * @param \Magento\Framework\App\Config\ScopeConfigInterface          $scopeInterface
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory  $rateErrorFactory
     * @param \Psr\Log\LoggerInterface                                    $logger
     * @param \Magento\Framework\Xml\Security                             $xmlSecurity
     * @param \Magento\Shipping\Model\Simplexml\ElementFactory            $xmlElFactory
     * @param \Magento\Shipping\Model\Rate\ResultFactory                  $rateFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\ResultFactory              $trackFactory
     * @param \Magento\Shipping\Model\Tracking\Result\ErrorFactory        $trackErrorFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory       $trackStatusFactory
     * @param \Magento\Directory\Model\RegionFactory                      $regionFactory
     * @param \Magento\Directory\Model\CountryFactory                     $countryFactory
     * @param \Magento\Directory\Model\CurrencyFactory                    $currencyFactory
     * @param \Magento\Directory\Helper\Data                              $directoryData
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface        $stockRegistry
     * @param \LaPoste\Colissimo\Helper\Data                              $helperData
     * @param \LaPoste\Colissimo\Helper\CountryOffer                      $helperCountryOffer
     * @param \LaPoste\Colissimo\Helper\Pdf                               $helperPdf
     * @param \LaPoste\Colissimo\Model\Shipping\ReturnLabelGenerator      $returnLabelGenerator
     * @param Session                                                     $customerSession
     * @param ObjectManagerInterface                                      $objectManager
     * @param \Magento\Checkout\Model\Session                             $checkoutSession
     * @param OffersApi                                                   $offersApi
     * @param DateTime                                                    $dateTime
     * @param \Magento\Framework\Stdlib\DateTime\TimeZone                 $timeZone
     * @param array                                                       $data
     */
    public function __construct(
        \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $generateLabelPayload,
        \LaPoste\Colissimo\Api\Carrier\LabellingApi $labellingApi,
        \LaPoste\Colissimo\Logger\Colissimo $colissimoLogger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeInterface,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Xml\Security $xmlSecurity,
        \Magento\Shipping\Model\Simplexml\ElementFactory $xmlElFactory,
        \Magento\Shipping\Model\Rate\ResultFactory $rateFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\ResultFactory $trackFactory,
        \Magento\Shipping\Model\Tracking\Result\ErrorFactory $trackErrorFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Directory\Model\RegionFactory $regionFactory,
        \Magento\Directory\Model\CountryFactory $countryFactory,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Directory\Helper\Data $directoryData,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        Data $helperData,
        CountryOffer $helperCountryOffer,
        Pdf $helperPdf,
        ReturnLabelGenerator $returnLabelGenerator,
        Session $customerSession,
        ObjectManagerInterface $objectManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        OffersApi $offersApi,
        \Magento\Framework\Stdlib\DateTime\TimeZone $timeZone,
        array $data = []
    ) {
        parent::__construct(
            $scopeInterface,
            $rateErrorFactory,
            $logger,
            $xmlSecurity,
            $xmlElFactory,
            $rateFactory,
            $rateMethodFactory,
            $trackFactory,
            $trackErrorFactory,
            $trackStatusFactory,
            $regionFactory,
            $countryFactory,
            $currencyFactory,
            $directoryData,
            $stockRegistry,
            $data
        );

        $this->_rateFactory = $rateFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->generateLabelPayload = $generateLabelPayload;
        $this->labellingApi = $labellingApi;
        $this->logger = $colissimoLogger;
        $this->helperData = $helperData;
        $this->helperCountryOffer = $helperCountryOffer;
        $this->helperPdf = $helperPdf;
        $this->returnLabelGenerator = $returnLabelGenerator;
        $this->customerSession = $customerSession;
        $this->objectManager = $objectManager;
        $this->checkoutSession = $checkoutSession;
        $this->offersApi = $offersApi;
        $this->timeZone = $timeZone;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        $availableMethods = [];
        foreach (self::METHODS_NAME as $oneName) {
            if ($this->helperData->getConfigValue('carriers/lpc_group/' . $oneName . '_enable')) {
                $availableMethods['colissimo' . $oneName] = $oneName;
            }
        }

        return $availableMethods;
    }

    public function getTracking($trackings)
    {
        if (!is_array($trackings)) {
            $trackings = [$trackings];
        }

        $result = $this->_trackFactory->create();
        foreach ($trackings as $tracking) {
            $status = $this->_trackStatusFactory->create();
            $status->setCarrier(self::CODE);
            $status->setCarrierTitle($this->getConfigData('title'));
            $status->setTracking($tracking);
            $status->setPopup(1);
            $status->setUrl(str_replace("{lpc_tracking_number}", $tracking, self::URL_SUIVI_COLISSIMO));
            $result->append($status);
        }

        return $result;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     *
     * @return \Magento\Framework\DataObject
     * @throws \Exception
     */
    protected function _doShipmentRequest(DataObject $request)
    {
        if ($request->getIsReturnLabel()) {
            // Directly creates return label
            $labelGenerationPayload = $this->mapRequestToReturn($request);
            $returnLabelGenerationPayload = null;
        } else {
            // Creates label
            $labelGenerationPayload = clone $this->mapRequestToShipment($request);

            // If needed will create return label at the same time
            // In this case, we need to revert sender data to correctly build return payload
            $returnLabelGenerationPayload = null;
            if ($this->helperData->getConfigValue(
                'lpc_advanced/lpc_return_labels/createReturnLabelWithOutward',
                $request->getStoreId()
            )) {
                $revertedRequest = $this->revertSenderInfo($request);
                $returnLabelGenerationPayload = $this->mapRequestToReturn($revertedRequest);
            }
        }

        return $this->makeRequest($request, $labelGenerationPayload, $returnLabelGenerationPayload);
    }

    /**
     * Revert shipper and recipient information
     *
     * @param $request
     *
     * @return \Magento\Framework\DataObject
     */
    protected function revertSenderInfo($request)
    {
        $revertedRequest = new DataObject();

        $originalData = $request->getData();
        foreach ($originalData as $key => $value) {
            if (strpos($key, 'shipper_') === 0) {
                $newKey = 'recipient_' . substr($key, 8);
                $revertedRequest->setData($newKey, $value);
            } elseif (strpos($key, 'recipient_') === 0) {
                $newKey = 'shipper_' . substr($key, 10);
                $revertedRequest->setData($newKey, $value);
            } else {
                $revertedRequest->setData($key, $value);
            }
        }

        return $revertedRequest;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     * @param \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $labelGenerationPayload
     * @param \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload|null $returnLabelGenerationPayload
     *
     * @return \Magento\Framework\DataObject
     */
    protected function makeRequest(
        DataObject $request, \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $labelGenerationPayload, \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload $returnLabelGenerationPayload = null
    ) {
        $result = new DataObject();
        try {
            // call Api
            list($shipmentDataInfo, $labelBinary, $cn23Binary) = $this->labellingApi->generateLabel($labelGenerationPayload);

            // parse result
            $parcelNumber = null;
            if ($shipmentDataInfo->labelResponse) {
                $parcelNumber = $shipmentDataInfo->labelResponse->parcelNumber;
            }

            // store info
            if (empty($parcelNumber)) {
                $result->setErrors($shipmentDataInfo->messages);
            } else {
                $result->setTrackingNumber($parcelNumber);
                $completeLabel = $labelBinary;
                if (!empty($cn23Binary)) {
                    $completeLabel = $this->helperPdf->combineLabelsPdf([$labelBinary, $cn23Binary])->render();
                }
                $result->setShippingLabelContent($completeLabel);
                $result->setCn23Content($cn23Binary);
            }

            if (!is_null($returnLabelGenerationPayload) && $returnLabelGenerationPayload instanceof \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload) {
                // Set manually the original tracking number needed for CN23 (not saved yet in database so not added when creating return payload)
                $returnLabelGenerationPayload->setOriginalTrackingNumber($parcelNumber);

                //call Api
                list(
                    $shipmentDataInfo, $labelBinary, $cn23Binary
                    ) = $this->labellingApi->generateLabel($returnLabelGenerationPayload);

                // parse result
                $parcelNumber = null;
                if ($shipmentDataInfo->labelResponse) {
                    $parcelNumber = $shipmentDataInfo->labelResponse->parcelNumber;
                }

                //store return label in our custom field
                if (empty($parcelNumber)) {
                    $result->setErrors($shipmentDataInfo->messages);
                } else {
                    $completeLabel = $labelBinary;
                    if (!empty($cn23Binary)) {
                        $completeLabel = $this->helperPdf->combineLabelsPdf([$labelBinary, $cn23Binary])->render();
                    }
                    // Add the tracking to shipment
                    $shipment = $request->getOrderShipment();
                    $carrierTitle = $this->getConfigData('title');
                    $this->returnLabelGenerator->addTrackNumbers($shipment, [$parcelNumber], self::CODE, $carrierTitle);
                    $result->setLpcReturnShippingLabelContent($completeLabel);
                }
            }
        } catch (\Exception $e) {
            $this->logger->error(
                'Error while generating Label',
                ['request' => $request, 'message' => $e->getMessage()]
            );
            $result->setErrors($e->getMessage());
        }

        $this->changeOrderStatusIfNeeded($request);

        return $result;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     */
    protected function changeOrderStatusIfNeeded(DataObject $request)
    {
        if (!$request->getIsReturnLabel()) {
            $defaultStatusAfterLabelling = $this->helperData->getConfigValue(
                'lpc_advanced/lpc_labels/orderStatusAfterGeneration',
                $request->getStoreId()
            );

            if (null !== $defaultStatusAfterLabelling) {
                $order = $request->getOrderShipment()->getOrder();

                $totalQtyOrdered = $order->getBaseTotalQtyOrdered();

                $nbShippedItems = 0;
                foreach ($order->getAllVisibleItems() as $item) {
                    $nbShippedItems += $item->getQtyShipped();
                }

                if ($nbShippedItems === $totalQtyOrdered) {
                    $order->setStatus($defaultStatusAfterLabelling);
                    $order->save;
                }
            }
        }
    }

    /**
     * Map request to shipment
     *
     * @param \Magento\Framework\DataObject $request
     *
     * @return \LaPoste\Colissimo\Api\Carrier\GenerateLabelPayload
     * @throws \Exception
     */
    protected function mapRequestToShipment(DataObject $request)
    {
        $sender = [
            'companyName' => $request['shipper_contact_company_name'],
            'firstName' => $request['shipper_contact_person_first_name'],
            'lastName' => $request['shipper_contact_person_last_name'],
            'street' => $request['shipper_address_street_1'],
            'street2' => $request['shipper_address_street_2'],
            'city' => $request['shipper_address_city'],
            'zipCode' => $request['shipper_address_postal_code'],
            'email' => $request['shipper_email'],
        ];

        $senderSpecificCountryCode = $this->helperCountryOffer->getLpcCountryCodeSpecificDestination($request['shipper_address_country_code'], $sender['zipCode']);

        $sender['countryCode'] = $senderSpecificCountryCode === false ? $request['shipper_address_country_code'] : $senderSpecificCountryCode;

        $recipient = [
            'companyName' => $request['recipient_contact_company_name'],
            'firstName' => $request['recipient_contact_person_first_name'],
            'lastName' => $request['recipient_contact_person_last_name'],
            'street' => $request['recipient_address_street_1'],
            'street2' => $request['recipient_address_street_2'],
            'city' => $request['recipient_address_city'],
            'zipCode' => $request['recipient_address_postal_code'],
            'email' => $request['recipient_email'],
            'mobileNumber' => $request['recipient_contact_phone_number'],
        ];

        $recipientSpecificCountryCode = $this->helperCountryOffer->getLpcCountryCodeSpecificDestination($request['recipient_address_country_code'], $recipient['zipCode']);
        $recipient['countryCode'] = $recipientSpecificCountryCode === false ? $request['recipient_address_country_code'] : $recipientSpecificCountryCode;

        $productCode = $this->helperCountryOffer->getProductCodeFromRequest($request);
        if ($productCode === false) {
            $this->logger->error('Not allowed for this destination');
            throw new \Exception(__('Not allowed for this destination'));
        }

        $payload = $this->generateLabelPayload->resetPayload()
            ->withContractNumber(null, $request->getStoreId())
            ->withPassword(null, $request->getStoreId())
            ->withCommercialName(null, $request->getStoreId())
            ->withCuserInfoText()

            ->withSender($sender, $request->getStoreId())
            ->withAddressee($recipient, null, $request->getStoreId())

            ->withPreparationDelay($request->getPreparationDelay(), $request->getStoreId())

            ->withProductCode($productCode)
            ->withOutputFormat($request->getOutputFormat(), $request->getStoreId())
            ->withInstructions($request->getInstructions())

            ->withOrderNumber($request->getOrderShipment()->getOrder()->getIncrementId())
            ->withPackage($request->getPackageParams(), $request->getPackageItems())
            ->withCustomsDeclaration(
                $request->getOrderShipment(),
                $request->getPackageItems(),
                $recipient['countryCode'],
                $recipient['zipCode'],
                $request->getStoreId()
            );

        if ($request->getShippingMethod() == self::CODE_SHIPPING_METHOD_RELAY) {
            $payload->withPickupLocationId($request->getOrderShipment()->getOrder()->getLpcRelayId());
        }

        $isUsingInsurance = $this->helperData->getConfigValue(
            'lpc_advanced/lpc_labels/isUsingInsurance',
            $request->getStoreId()
        );

        if ($isUsingInsurance) {
            $shipment = $request->getOrderShipment();
            $total = 0;
            foreach ($shipment->getAllItems() as $item) {
                $orderItem = $item->getOrderItem();
                if (!empty($orderItem)) {
                    $total += $orderItem->getBaseRowTotal();
                }
            }

            $payload->withInsuranceValue($total, $productCode, $recipient['countryCode']);
        }

        $registeredMailLevel = $this->helperData->getConfigValue(
            'lpc_advanced/lpc_labels/registeredMailLevel',
            $request->getStoreId()
        );
        if (!empty($registeredMailLevel)) {
            $payload->withRecommendationLevel($registeredMailLevel);
        }

        return $payload;
    }

    /**
     * @param \Magento\Framework\DataObject $request
     *
     * @return mixed
     * @throws \Exception
     */
    protected function mapRequestToReturn(DataObject $request)
    {
        $sender = [
            'firstName' => $request['shipper_contact_person_first_name'],
            'lastName' => $request['shipper_contact_person_last_name'],
            'street' => $request['shipper_address_street_1'],
            'street2' => $request['shipper_address_street_2'],
            'city' => $request['shipper_address_city'],
            'zipCode' => $request['shipper_address_postal_code'],
            'email' => $request['shipper_email'],
        ];

        $senderSpecificCountryCode = $this->helperCountryOffer->getLpcCountryCodeSpecificDestination($request['shipper_address_country_code'], $sender['zipCode']);
        $sender['countryCode'] = $senderSpecificCountryCode === false ? $request['shipper_address_country_code'] : $senderSpecificCountryCode;

        $recipient = [
            'companyName' => $request['recipient_contact_company_name'],
            'firstName' => $request['recipient_contact_person_first_name'],
            'lastName' => $request['recipient_contact_person_last_name'],
            'street' => $request['recipient_address_street_1'],
            'street2' => $request['recipient_address_street_2'],
            'city' => $request['recipient_address_city'],
            'zipCode' => $request['recipient_address_postal_code'],
            'email' => $request['recipient_email'],
        ];

        $recipientSpecificCountryCode = $this->helperCountryOffer->getLpcCountryCodeSpecificDestination($request['recipient_address_country_code'], $recipient['zipCode']);
        $recipient['countryCode'] = $recipientSpecificCountryCode === false ? $request['recipient_address_country_code'] : $recipientSpecificCountryCode;

        $productCode = $this->helperCountryOffer->getProductCodeFromRequest($request, true);
        if ($productCode === false) {
            $this->logger->error('Not allowed for this destination');
            throw new \Exception(__('Not allowed for this destination'));
        }

        $payload = $this->generateLabelPayload->resetPayload()
            ->isReturnLabel()
            ->withContractNumber(null, $request->getStoreId())
            ->withPassword(null, $request->getStoreId())
            ->withCommercialName(null, $request->getStoreId())
            ->withCuserInfoText()

            ->withSender($sender, $request->getStoreId())
            ->withAddressee($recipient, null, $request->getStoreId())

            //Est ce qu'on a une date maximum pour renvoyer le colis ? On met quoi ici ? A demander a la demo
            ->withPreparationDelay($request->getPreparationDelay(), $request->getStoreId())

            ->withProductCode($productCode)
            ->withOutputFormat($request->getOutputFormat(), $request->getStoreId())
            ->withInstructions($request->getInstructions())

            ->withOrderNumber($request->getOrderShipment()->getOrder()->getIncrementId())
            ->withPackage($request->getPackageParams(), $request->getPackageItems())
            ->withCustomsDeclaration(
                $request->getOrderShipment(),
                $request->getPackageItems(),
                $sender['countryCode'],
                $sender['zipCode'],
                $request->getStoreId()
            );

        return $payload;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\RateRequest $request
     *
     * @return bool|\Magento\Framework\DataObject|\Magento\Shipping\Model\Rate\Result|null
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->isActive()) {
            return false;
        }

        /** @var \Magento\Shipping\Model\Rate\Result $result */
        $result = $this->_rateFactory->create();

        $destCountryId = $request->getDestCountryId();
        $cartWeight = $request->getPackageWeight();
        $cartPrice = $request->getPackageValue();
        $destPostCode = $request->getDestPostcode();

        $flashAvailability = $this->flashAvailability($request);

        foreach (self::METHODS_NAME as $oneName) {
            if ($this->helperData->getConfigValue('carriers/lpc_group/' . $oneName . '_enable')) {

                if ($oneName === self::CODE_SHIPPING_METHOD_FLASH_SS && !$flashAvailability['withoutSignature']) {
                    continue;
                }

                if ($oneName === self::CODE_SHIPPING_METHOD_FLASH_AS && !$flashAvailability['withSignature']) {
                    continue;
                }

                // Should we use cart weight or price (doesn't change the rest of the computation)
                if ('cartprice' === $this->helperData->getConfigValue('carriers/lpc_group/' . $oneName . '_priceorweight')) {
                    $cartValue = $cartPrice;
                } else {
                    $cartValue = $cartWeight;
                }

                // Check max weight accepted for the method
                $maxWeightAccepted = $this->helperData->getConfigValue('carriers/lpc_group/' . $oneName . '_maxweight');
                if (!empty($maxWeightAccepted) && $cartValue > $maxWeightAccepted) {
                    continue;
                }


                $method = $this->getLpcShippingMethod($oneName, $destCountryId, $destPostCode, $cartValue);
                if (!empty($method)) {
                    $result->append($method);
                }
            }
        }

        return $result;
    }

    /**
     * Build method if destination and weight fits configuration
     *
     * @param $methodCode
     * @param $destCountryId
     * @param $destPostCode
     * @param $cartWeight
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method|null
     */
    private function getLpcShippingMethod($methodCode, $destCountryId, $destPostCode, $cartWeight)
    {
        // Free shipping set for the method
        if ($this->helperData->getConfigValue('carriers/lpc_group/' . $methodCode . '_free')) {
            $method = $this->getMethodStructure($methodCode);
            $method->setPrice(0);

            return $method;
        }

        // Get available slices for this destination and weight order by prices asc
        $priceDataEncoded = $this->helperData->getConfigValue('carriers/lpc_group/' . $methodCode . '_setup');
        $priceData = $this->helperData->decodeFromConfig($priceDataEncoded);
        if (empty($priceData)) {
            return null;
        }
        $slices = $this->helperCountryOffer->getSlicesForDestination($methodCode, $destCountryId, $priceData, $destPostCode, $cartWeight);

        if (empty($slices)) {
            return null;
        }

        $method = $this->getMethodStructure($methodCode);

        $methodPrice = $this->isColissimoPass() ? 0 : $this->helperData->getValueDependingMgVersion($slices[0], 'price');
        $method->setPrice($methodPrice);

        return $method;
    }

    /**
     * Prepare the base structure of the shipping method (same for all Colissimo methods)
     *
     * @param $methodCode
     *
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function getMethodStructure($methodCode)
    {
        $method = $this->_rateMethodFactory->create();
        $method->setCarrier(self::CODE);
        $method->setCarrierTitle($this->getConfigData('title'));
        $method->setMethod($methodCode);
        $name = $this->helperData->getConfigValue('carriers/lpc_group/' . $methodCode . '_label');
        $method->setMethodTitle(!empty($name) ? $name : 'colissimo');

        return $method;
    }


    /**
     * Do request to shipment
     *
     * @param Request $request
     *
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function requestToShipment($request)
    {
        $packages = $request->getPackages();
        if (!is_array($packages) || !$packages) {
            throw new \Magento\Framework\Exception\LocalizedException(__('No packages for request'));
        }
        if ($request->getStoreId() != null) {
            $this->setStore($request->getStoreId());
        }
        $data = [];
        foreach ($packages as $packageId => $package) {
            $request->setPackageId($packageId);
            $request->setPackagingType($package['params']['container']);
            $request->setPackageWeight($package['params']['weight']);
            $request->setPackageParams(new \Magento\Framework\DataObject($package['params']));
            $request->setPackageItems($package['items']);
            $result = $this->_doShipmentRequest($request);

            if ($result->hasErrors()) {
                $this->rollBack($data);
                break;
            } else {
                $labelContent = $result->getShippingLabelContent();
                $cn23Content = $result->getCn23Content();
                if (!empty($cn23Content)) {
                    $this->setCn23FlagForShipment($request->getOrderShipment());
                }

                $data[] = [
                    'tracking_number' => $result->getTrackingNumber(),
                    'label_content' => $labelContent,
                ];

                // Save return label if generated simultaneously
                $returnLabelContent = $result->getLpcReturnShippingLabelContent();
                if (!empty($returnLabelContent)) {
                    $request->getOrderShipment()->setLpcReturnLabel($returnLabelContent);
                }
            }
            if (!isset($isFirstRequest)) {
                $request->setMasterTrackingId($result->getTrackingNumber());
                $isFirstRequest = false;
            }
        }

        $response = new DataObject(['info' => $data]);
        if ($result->getErrors()) {
            $response->setErrors($result->getErrors());
        }

        return $response;
    }

    /**
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     *
     * @throws \Exception
     */
    public function setCn23FlagForShipment($shipment)
    {
        $shipment->setDataUsingMethod(
            \LaPoste\Colissimo\Setup\UpgradeSchema::DB_CN23_FLAG_COLUMN_NAME,
            true
        );

        $shipment->save();
    }

    private function isColissimoPass()
    {
        if ($this->helperData->isModuleOutputEnabled('Quadra_Colissimopass')) {
            $colissimoPassModelUser = $this->objectManager->create(\Quadra\Colissimopass\Model\User::class);
            if ($this->customerSession->isLoggedIn()) {
                return $colissimoPassModelUser->checkIsLog() && $colissimoPassModelUser->checkIsActive();
            } else {
                $colissimoPassSession = $this->checkoutSession->getData('colissimopass_contract');

                return $colissimoPassSession['isLog'] == 1 && $colissimoPassSession['status'] == "ACTIVE";
            }
        }

        return false;
    }

    private function flashAvailability(RateRequest $request)
    {
        $maxHourFlash = new \DateTime($this->helperData->getConfigValue('carriers/lpc_group/flash_maxHour'));
        $actualHour = new \DateTime($this->timeZone->date()->format('H:i'));

        $flashAvailability = [
            'withoutSignature' => false,
            'withSignature' => false,
        ];

        if (($this->helperData->getConfigValue('carriers/lpc_group/'.self::CODE_SHIPPING_METHOD_FLASH_SS.'_enable') || $this->helperData->getConfigValue('carriers/lpc_group/'.self::CODE_SHIPPING_METHOD_FLASH_AS.'_enable')) && $actualHour < $maxHourFlash) {

            $addressee = [
                'line2' => $request->getDestStreet(),
                'city' => $request->getDestCity(),
                'zipCode' => $request->getDestPostcode(),
                'countryCode' => $request->getDestCountryId(),
            ];

            $depositDate = date("c");

            try {
                $offersAvailable = $this->offersApi->getColissimoOffers($addressee, $depositDate);

                if ($offersAvailable->message->code == '0') {
                    foreach ($offersAvailable->offers as $oneOffer) {
                        switch ($oneOffer->productCode) {
                            case 'J+1':
                                $flashAvailability['withSignature'] = true;
                                break;
                            case 'COLR':
                                $flashAvailability['withoutSignature'] = true;
                                break;
                        }
                    }
                } else {
                    $this->logger->error('Error API check Colissimo Flash availability.'.$offersAvailable->message->type.': '.$offersAvailable->message->label);
                }
            } catch (\LaPoste\Colissimo\Exception\ApiException $e) {
            }
        }

        return $flashAvailability;
    }
}

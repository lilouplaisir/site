<?php

/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use \LaPoste\Colissimo\Model\Carrier\Colissimo;
use LaPoste\Colissimo\Helper\Data;

class CountryOffer extends AbstractHelper
{
    const PATH_TO_COUNTRIES_PER_ZONE_JSON_FILE = __DIR__ . '/../resources/capabilitiesByCountry.json';
    const CACHE_IDENTIFIER_PREFIX = 'lpc_country_offer_';
    const CACHE_IDENTIFIER_COUNTRIES_PER_ZONE = self::CACHE_IDENTIFIER_PREFIX . 'countriesPerZone';
    const CACHE_IDENTIFIER_COUNTRIES_PER_ZONE_WITH_TRAD = self::CACHE_IDENTIFIER_PREFIX . 'countriesPerZoneWithTrad';

    //Some region have a specific country code that is not handle by Magento. The pattern is $countryCodeSpecificsDestinations['MagentoCountryCode']['startOfPostCode'] = "CustomLpcCountryCode"
    protected $countryCodeSpecificsDestinations = array(
        'ES' => array(
            '35' => 'IC',
            '38' => 'IC',
        ),
    );

    private $productInfoByDestination;

    private $countriesPerZone;

    protected $countryInformation;
    protected $cache;
    protected $helperData;

    public function __construct(
        \Magento\Directory\Api\CountryInformationAcquirerInterface $countryInformationAcquirer,
        \Magento\Framework\App\CacheInterface $cache,
        Data $helperData
    )
    {
        $this->countryInformationAcquirer = $countryInformationAcquirer;
        $this->cache = $cache;
        $this->helperData = $helperData;
        $countriesPerZoneCachedInfo = $this->cache->load(self::CACHE_IDENTIFIER_COUNTRIES_PER_ZONE);
        if (!empty($countriesPerZoneCachedInfo)) {
            $this->countriesPerZone = json_decode(
                $countriesPerZoneCachedInfo,
                JSON_OBJECT_AS_ARRAY
            );
        }
    }

    // Translate country and area names
    public function getCountriesPerZoneWithTrad()
    {
        $cachedInfo = $this->cache->load(self::CACHE_IDENTIFIER_COUNTRIES_PER_ZONE_WITH_TRAD);
        if (empty($cachedInfo)) {
            $countriesPerZone = $this->getCountriesPerZone();
            foreach ($countriesPerZone as &$oneZone) {
                $oneZone['name'] = __($oneZone['name']);
                foreach ($oneZone['countries'] as $countryCode => &$oneCountry) {
                    try {
                        $oneCountry['name'] = $this->countryInformationAcquirer
                            ->getCountryInfo($countryCode)
                            ->getFullNameLocale();
                    } catch (\Exception $e) {
                        $oneCountry["name"] = $countryCode == "IC" ? __("Canary Islands") : $countryCode;
                    }
                }
            }

            $this->cache->save(
                json_encode($countriesPerZone),
                self::CACHE_IDENTIFIER_COUNTRIES_PER_ZONE_WITH_TRAD
            );
            return $countriesPerZone;
        } else {
            return json_decode($cachedInfo, JSON_OBJECT_AS_ARRAY);
        }
    }

    /**
     * Get only slices of the correct destination (either country or area containing the country)
     * Warning : specific case for some spanish territories (country code still ES but some region code are not in the same Colissimo area and pricing)
     * @param $methodCode : shipping method code
     * @param $destCountryId : destination country code
     * @param $priceData : configuration of the shipping method
     * @param $destPostCode : destination postcode
     * @param int $cartWeight : cart weight (or price depending on config option). Computation is the same no matter the value inside
     * @return array of slices ordered by price asc
     */
    public function getSlicesForDestination($methodCode, $destCountryId, $priceData, $destPostCode, $cartWeight = 0)
    {
        $countriesPerZone = $this->getCountriesPerZone();

        $slicesDest = array();

        $maxWeightPerArea = array();

        $lpcCountryCodeSpecificDestination = $this->getLpcCountryCodeSpecificDestination($destCountryId, $destPostCode);

        foreach ($priceData as $oneSlice) {
            $oneSliceWeight = $this->helperData->getValueDependingMgVersion($oneSlice, 'weight');
            $oneSlicePrice = $this->helperData->getValueDependingMgVersion($oneSlice, 'price');
            $oneSliceArea = $this->helperData->getValueDependingMgVersion($oneSlice, 'area');
            if ($oneSliceWeight > $cartWeight || $oneSliceWeight == '' || $oneSlicePrice == '') {
                continue;
            }

            if ($oneSliceArea == $destCountryId) {
                //Slice is a country (not a specific country or not a specific region)
                if ($lpcCountryCodeSpecificDestination === false) {
                    $slicesDest[] = $oneSlice;
                    $this->checkMaxWeight($oneSlice, $maxWeightPerArea);
                }
            } elseif ($lpcCountryCodeSpecificDestination !== false && $lpcCountryCodeSpecificDestination == $oneSliceArea) {
                // Slice is a specific code. We should add if the destination is one of the specific
                $slicesDest[] = $oneSlice;
                $this->checkMaxWeight($oneSlice, $maxWeightPerArea);
            } elseif (array_key_exists($oneSliceArea, $countriesPerZone) && array_key_exists($destCountryId, $countriesPerZone[$oneSliceArea]['countries']) && $countriesPerZone[$oneSliceArea]['countries'][$destCountryId][$methodCode] && $lpcCountryCodeSpecificDestination === false) {
                // Area (Z1 to Z6) not a specific case
                $slicesDest[] = $oneSlice;
                $this->checkMaxWeight($oneSlice, $maxWeightPerArea);
            } elseif ($lpcCountryCodeSpecificDestination !== false) {
                // Area (Z1 to Z6) specific case
                if (array_key_exists($oneSliceArea, $countriesPerZone) && array_key_exists($lpcCountryCodeSpecificDestination, $countriesPerZone[$oneSliceArea]['countries']) && $countriesPerZone[$oneSliceArea]['countries'][$lpcCountryCodeSpecificDestination][$methodCode]) {
                    $slicesDest[] = $oneSlice;
                    $this->checkMaxWeight($oneSlice, $maxWeightPerArea);
                }
            }
        }

        // Remove slice if exist a more weight corresponding slice for the same area
        foreach ($slicesDest as $key => $oneSlice) {
            $oneSliceWeight = $this->helperData->getValueDependingMgVersion($oneSlice, 'weight');
            $oneSliceArea = $this->helperData->getValueDependingMgVersion($oneSlice, 'area');
            if ($oneSliceWeight != $maxWeightPerArea[$oneSliceArea]) {
                unset($slicesDest[$key]);
            }
        }

        // Sort by price asc to get only the cheaper
        usort($slicesDest, array($this, "sortPerPrice"));

        return $slicesDest;
    }

    /**
     * Add to
     * @param $slice
     * @param $maxWeightPerArea
     */
    protected function checkMaxWeight($slice, &$maxWeightPerArea)
    {
        $sliceWeight = $this->helperData->getValueDependingMgVersion($slice, 'weight');
        $sliceArea = $this->helperData->getValueDependingMgVersion($slice, 'area');
        if (!array_key_exists($sliceArea, $maxWeightPerArea) || $sliceWeight > $maxWeightPerArea[$sliceArea]) {
            $maxWeightPerArea[$sliceArea] = $sliceWeight;
        }
    }

    /**
     * Order the slices by price asc
     * @param $a
     * @param $b
     * @return int
     */
    protected function sortPerPrice($a, $b)
    {
        $priceA = $this->helperData->getValueDependingMgVersion($a, 'price');
        $priceB = $this->helperData->getValueDependingMgVersion($b, 'price');
        if ($priceA == $priceB) {
            return 0;
        }

        return ($priceA < $priceB) ? -1 : 1;
    }

    /**
     * Return product code
     * @param $methodCode
     * @param $destinationCountryId
     * @param $destinationPostalCode
     * @param bool $isReturn
     * @return bool|string
     */
    public function getProductCodeForDestination(
        $methodCode,
        $destinationCountryId,
        $destinationPostalCode,
        $isReturn = false
    )
    {
        $productInfo = $this->getProductInfoForDestination($destinationCountryId, $destinationPostalCode);

        if ($isReturn) {
            return !empty($productInfo['return']) ? $productInfo['return'] : false;
        }

        switch ($methodCode) {
            case Colissimo::CODE_SHIPPING_METHOD_DOMICILE_SS:
            case Colissimo::CODE_SHIPPING_METHOD_DOMICILE_AS:
                return !empty($productInfo[$methodCode]) ? $productInfo[$methodCode] : false;
            case Colissimo::CODE_SHIPPING_METHOD_EXPERT:
                return $productInfo[$methodCode] ? 'COLI' : false;
            case Colissimo::CODE_SHIPPING_METHOD_FLASH_SS:
                return $productInfo[$methodCode] ? 'COLR' : false;
            case Colissimo::CODE_SHIPPING_METHOD_FLASH_AS:
                return $productInfo[$methodCode] ? 'J+1' : false;
            case Colissimo::CODE_SHIPPING_METHOD_RELAY:
            default:
                throw new \Exception('Shipping method not managed');
        }
    }

    protected function getProductInfoForDestination($destinationCountryId, $destinationPostcode)
    {
        $countryCodeSpecificDestination = $this->getLpcCountryCodeSpecificDestination($destinationCountryId, $destinationPostcode);

        $destinationCountryCode = $countryCodeSpecificDestination === false ? $destinationCountryId : $countryCodeSpecificDestination;

        if (null === $this->productInfoByDestination) {
            $this->productInfoByDestination = array();
            foreach ($this->getCountriesPerZone() as $zone) {
                foreach ($zone['countries'] as $countryCode => $productInfo) {
                    $this->productInfoByDestination[$countryCode] = $productInfo;
                }
            }
        }

        return $this->productInfoByDestination[$destinationCountryCode];
    }

    public function getIsCn23RequiredForDestination($destinationCountryId, $destinationPostcode)
    {
        $productInfo = $this->getProductInfoForDestination($destinationCountryId, $destinationPostcode);
        return $productInfo['cn23'];
    }

    public function getFtdRequiredForDestination($destinationCountryId, $destinationPostcode)
    {
        $productInfo = $this->getProductInfoForDestination($destinationCountryId, $destinationPostcode);
        return $productInfo['ftd'];
    }

    public function getProductCodeFromRequest(\Magento\Framework\DataObject $request, $isReturn = false)
    {
        if (Colissimo::CODE_SHIPPING_METHOD_RELAY === $request->getShippingMethod()) {
            if ($isReturn) {
                return $this->getProductCodeForDestination(
                    $request->getShippingMethod(),
                    $request['shipper_address_country_code'],
                    $request->getShipperAddressPostalCode(),
                    true
                );
            }

            return $request->getOrderShipment()->getOrder()->getLpcRelayType();
        }

        $countryCode = $isReturn ? $request['shipper_address_country_code'] : $request['recipient_address_country_code'];
        $postcode = $isReturn ? $request['shipper_address_postal_code'] : $request['recipient_address_postal_code'];

        return $this->getProductCodeForDestination(
            $request->getShippingMethod(),
            $countryCode,
            $postcode,
            $isReturn
        );
    }


    public function getCountriesPerZone()
    {
        if (null === $this->countriesPerZone) {
            $this->countriesPerZone = json_decode(
                file_get_contents(
                    self::PATH_TO_COUNTRIES_PER_ZONE_JSON_FILE
                ),
                \JSON_OBJECT_AS_ARRAY
            );

            $this->cache->save(
                json_encode($this->countriesPerZone),
                self::CACHE_IDENTIFIER_COUNTRIES_PER_ZONE
            );
        }

        return $this->countriesPerZone;
    }


    /**
     * Get the Colissimo specific destination country code from a magento country code and a postcode. Used for capabilitiesByCountry.json
     * @param $countryCode
     * @param $postCode
     * @return bool|string
     */
    public function getLpcCountryCodeSpecificDestination($countryCode, $postCode)
    {
        if (array_key_exists($countryCode, $this->countryCodeSpecificsDestinations)) {
            foreach ($this->countryCodeSpecificsDestinations[$countryCode] as $oneStartPostCode => $oneLpcRegionCode) {
                if (strpos($postCode, strval($oneStartPostCode)) === 0) {
                    return $oneLpcRegionCode;
                }
            }
        }
        return false;
    }

    /**
     * Retrieve Magento Country Code from a country code specific destination
     * @param $countryCodeSpecificDestination
     * @return bool|string
     */
    public function getMagentoCountryCodeFromSpecificDestination($countryCodeSpecificDestination)
    {
        foreach ($this->countryCodeSpecificsDestinations as $oneCountryCode => $oneCountryCodeSpecificsDestinations) {
            if (in_array($countryCodeSpecificDestination, $oneCountryCodeSpecificsDestinations)) {
                return $oneCountryCode;
            }
        }
        return false;
    }

    /**
     * Get all countries available for a delivery method
     * @param $method
     * @return array
     */
    public function getCountriesForMethod($method)
    {
        $countriesOfMethod = array();
        $countriesPerZone = $this->getCountriesPerZone();

        foreach ($countriesPerZone as &$oneZone) {
            foreach ($oneZone['countries'] as $countryCode => &$oneCountry) {
                if ($oneCountry[$method] === true) {
                    $countriesOfMethod[] = $countryCode;
                }
            }
        }
        return $countriesOfMethod;
    }

    /**
     * List all countries from one zone
     * @param $zone
     * @return array
     */
    public function getCountriesFromOneZone($zone)
    {
        $countriesOfZone = array();
        $countriesPerZone = $this->getCountriesPerZone();

        if (!empty($countriesPerZone[$zone]['countries'])) {
            foreach ($countriesPerZone[$zone]['countries'] as $countryCode => &$oneZone) {
                $countriesOfZone[] = $countryCode;
            }
        } else {
            $countriesOfZone[] = $zone;
        }

        return $countriesOfZone;
    }

}

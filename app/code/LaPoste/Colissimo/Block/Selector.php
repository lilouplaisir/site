<?php

namespace LaPoste\Colissimo\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use LaPoste\Colissimo\Helper\Data;
use \LaPoste\Colissimo\Logger\Colissimo;
use LaPoste\Colissimo\Model\PickUpPointApi;
use LaPoste\Colissimo\Helper\CountryOffer;


class Selector extends Template
{
    protected $_template = "selector.phtml";
    protected $helperData;
    public $colissimoLogger;
    protected $_pickUpPointApi;
    protected $countryOffer;

    public function __construct(Context $context, Data $helperData, Colissimo $logger, PickUpPointApi $pickUpPointApi, CountryOffer $countryOffer, array $data = [])
    {
        $this->helperData = $helperData;
        $this->colissimoLogger = $logger;
        $this->_pickUpPointApi = $pickUpPointApi;
        $this->countryOffer = $countryOffer;
        parent::__construct($context, $data);
    }

    public function lpcAjaxUrlLoadRelaysList()
    {
        return $this->getUrl("lpc/relays/LoadRelays");
    }

    public function getAjaxSetInformationRelayUrl()
    {
        return $this->getUrl("lpc/relays/SetRelayInformationSession");
    }

    public function getGoogleMapsUrl()
    {
        $apiKey = $this->helperData->getAdvancedConfigValue('lpc_pr_front/lpc_google_maps_api_key');
        $urlGoogleMaps = empty($apiKey) || $apiKey == '0' ? '' : "https://maps.googleapis.com/maps/api/js?key=" . $apiKey;

        return $urlGoogleMaps;
    }

    public function lpcPrView()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/choosePRDisplayMode");
    }

    public function lpcWidgetUrl()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/prWidgetUrl");
    }

    public function lpcGetAuthenticationToken()
    {
        $authenticateResponse = $this->_pickUpPointApi->authenticate();

        if ($authenticateResponse === false) {
            return false;
        } else {
            return $authenticateResponse->token;
        }
    }

    public function lpcGetAveragePreparationDelay()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/averagePreparationDelay");
    }

    public function lpcGetAddressTextColor()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/prAddressTextColor");
    }

    public function lpcGetListTextColor()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/prListTextColor");
    }

    public function lpcGetCustomizeWidget()
    {
        return $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/prCustomizeWidget");
    }

    public function lpcGetFontWidgetPr()
    {
        $fontValue = $this->helperData->getConfigValue("lpc_advanced/lpc_pr_front/prDisplayFont");

        $fontNames = [
            'georgia' => 'Georgia, serif',
            'palatino' => '"Palatino Linotype", "Book Antiqua", Palatino, serif',
            'times' => '"Times New Roman", Times, serif',
            'arial' => 'Arial, Helvetica, sans-serif',
            'arialblack' => '"Arial Black", Gadget, sans-serif',
            'comic' => '"Comic Sans MS", cursive, sans-serif',
            'impact' => 'Impact, Charcoal, sans-serif',
            'lucida' => '"Lucida Sans Unicode", "Lucida Grande", sans-serif',
            'tahoma' => 'Tahoma, Geneva, sans-serif',
            'trebuchet' => '"Trebuchet MS", Helvetica, sans-serif',
            'verdana' => 'Verdana, Geneva, sans-serif',
            'courier' => '"Courier New", Courier, monospace',
            'lucidaconsole' => '"Lucida Console", Monaco, monospace',
        ];

        return $fontNames[$fontValue];
    }

    /**
     * Get list of enabled countries for relay method
     * @return string
     */
    public function getWidgetListCountry()
    {
        // Get theoric countries available for relay method
        $countriesOfMethod = $this->countryOffer->getCountriesForMethod('pr');

        // If always free, all countries of relay method are available in the widget
        if ('1' === $this->helperData->getConfigValue("carriers/lpc_group/pr_free")) {
            return implode(',', $countriesOfMethod);
        }

        // Get all areas configured for relay method
        $configPRJson = $this->helperData->getConfigValue("carriers/lpc_group/pr_setup");
        $configPR = $this->helperData->decodeFromConfig($configPRJson);

        // Get the countries in both.
        $countriesTmp = array();
        foreach ($configPR as $oneConfig) {
            $countriesZone = $this->countryOffer->getCountriesFromOneZone($this->helperData->getValueDependingMgVersion($oneConfig, 'area'));
            foreach ($countriesZone as $oneCountry) {
                if (in_array($oneCountry, $countriesOfMethod)) {
                    $countriesTmp[$oneCountry] = 1;
                }
            }
        }
        $countries = array_keys($countriesTmp);

        return implode(',', $countries);
    }
}
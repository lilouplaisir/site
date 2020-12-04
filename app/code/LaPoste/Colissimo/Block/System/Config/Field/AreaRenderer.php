<?php
// Block for the areas dropdown in the method setups

namespace LaPoste\Colissimo\Block\System\Config\Field;

use \Magento\Framework\View\Element\Html\Select;
use LaPoste\Colissimo\Helper\CountryOffer;

class AreaRenderer extends Select
{
    protected $countriesPerZone;

    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        CountryOffer $helperCountryOffer,
        array $data = []
    ) {
        $this->countriesPerZone = $helperCountryOffer->getCountriesPerZoneWithTrad();
        parent::__construct($context, $data);
    }

    public function _toHtml()
    {
        preg_match('/.*\[(.*)_setup\].*/', $this->getName(), $matches);
        if (!empty($matches[1])) {
            // Reset option to build options for different methods
            $this->setOptions([]);

            foreach ($this->countriesPerZone as $zoneCode => $oneZone) {
                $arrayCountries = array();
                foreach ($oneZone['countries'] as $countryCode => $oneCountry) {
                    if ($oneCountry[$matches[1]]) {
                        $arrayCountries[$countryCode] = $oneCountry['name'];
                    }
                }
                if (!empty($arrayCountries)) {
                    $this->addOption($zoneCode, __($oneZone['name']));
                    if ($zoneCode != 'FR') {
                        $this->addOption($arrayCountries, $oneZone['name']);
                    }
                }
            }
        }

        return parent::_toHtml();
    }

    public function setInputName($value)
    {
        return $this->setName($value);
    }
}

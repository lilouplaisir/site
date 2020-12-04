<?php
/**
 * Source Model for "point retrait" display mode configuration field
 **/
namespace LaPoste\Colissimo\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

class PRDisplayMode implements ArrayInterface
{

    /*
      * Option getter
      * @return array
    */
    public function toOptionArray()
    {
        $options = [
            ['value' => 'webservice', 'label' => 'Webservice'],
            ['value' => 'widget', 'label' => 'Widget'],
        ];

        return $options;
    }
}

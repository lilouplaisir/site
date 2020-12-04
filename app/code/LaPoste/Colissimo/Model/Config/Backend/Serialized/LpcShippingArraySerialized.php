<?php
/*******************************************************
 * Copyright (C) 2018 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Model\Config\Backend\Serialized;

/**
 * Sort arrays of price slices per area and wieght before save
 */
class LpcShippingArraySerialized extends \Magento\Config\Model\Config\Backend\Serialized
{
    /**
     * Unset array element with '__empty' key
     *
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        $value = $this->handleDecimalValues($value);

        uasort($value, array($this, "sortPerArea"));

        if (is_array($value)) {
            unset($value['__empty']);
        }
        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * Compare two slices to order them. Via area (country code) first and then weight
     * @param $a : one slice containing area, weight and price
     * @param $b : one slice containing area, weight and price
     * @return int
     */
    protected function sortPerArea($a, $b)
    {
        if (empty($a)) {
            return 1;
        }

        if (empty($b)) {
            return -1;
        }

        $strComp = strcmp($a['area'], $b['area']);
        if ($strComp === 0) {
            return ceil($a['weight'] - $b['weight']);
        }

        return $strComp;
    }

    /**
     * Cast comma decimals into dot decimals
     *
     * @param $value
     * @return mixed
     */
    protected function handleDecimalValues($value)
    {
        $keysToHandle = [
            'weight',
            'price'
        ];

        foreach ($value as $oneSlideKey => $oneSlideValue) {
            if (is_array($oneSlideValue)) {
                foreach ($keysToHandle as $oneKey){
                    $oneSlideValue[$oneKey] = str_replace(',', '.', $oneSlideValue[$oneKey]);
                }

                $value[$oneSlideKey] = $oneSlideValue;
            }
        }

        return $value;
    }
}

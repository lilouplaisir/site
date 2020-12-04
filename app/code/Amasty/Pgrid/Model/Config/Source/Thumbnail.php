<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

declare(strict_types=1);

namespace Amasty\Pgrid\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Thumbnail implements OptionSourceInterface
{
    const NOT_ADDED = 0;
    const ADDED = 1;

    public function toOptionArray(): array
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $result;
    }

    public function toArray(): array
    {
        return [
            self::ADDED => __('Added'),
            self::NOT_ADDED => __('Not Added')
        ];
    }
}

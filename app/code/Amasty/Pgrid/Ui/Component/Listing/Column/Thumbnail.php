<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Pgrid
 */

declare(strict_types=1);

namespace Amasty\Pgrid\Ui\Component\Listing\Column;

class Thumbnail extends \Magento\Catalog\Ui\Component\Listing\Columns\Thumbnail
{
    public const NAME = 'column.thumbnail'; //overriding to add filters on grid
}

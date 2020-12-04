<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MGS\Mpanel\CustomerData;

/**
 * Default item
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
	/**
     * @var string
     */
    const SIDEBAR_ITEMS_NUMBER = 3;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishlistHelper;

    /**
     * @var \Magento\Catalog\Helper\ImageFactory
     */
    protected $imageHelperFactory;

    /**
     * @var \Magento\Framework\App\ViewInterface
     */
    protected $view;

    /**
     * @var \Magento\Wishlist\Block\Customer\Sidebar
     */
    protected $block;
	
	/**
     * @var \MGS\Mpanel\Helper\Data
     */
    protected $panelHelper;
	
	/**
     * @var \MGS\Mpanel\Helper\Data
     */
    protected $panelImageHelper;
	
	public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
		\MGS\Mpanel\Helper\Data $panelHelper,
		\MGS\Mpanel\Helper\Image $panelImageHelper
    ) {
        $this->wishlistHelper = $wishlistHelper;
        $this->imageHelperFactory = $imageHelperFactory;
        $this->block = $block;
        $this->view = $view;
		$this->panelHelper = $panelHelper;
        $this->panelImageHelper = $panelImageHelper;
    }
	
	protected function getImageData($product)
    {
        /** @var \Magento\Catalog\Helper\Image $helper */
        $helper = $this->imageHelperFactory->create()
            ->init($product, 'wishlist_sidebar_block');

		
		$imageHelper = $this->panelImageHelper->init($product, 'mini_cart_product_thumbnail');

        $template = $helper->getFrame()
            ? 'Magento_Catalog/product/image'
            : 'Magento_Catalog/product/image_with_borders';

        $imageSize = $this->panelHelper->getImageMinSize();

        $width = $imageSize['width'];

        $height = $imageSize['height'];

        return [
            'template' => $template,
            'src' => $imageHelper->getUrl(),
            'width' => $width,
            'height' => $height,
            'alt' => $helper->getLabel(),
        ];
    }
}

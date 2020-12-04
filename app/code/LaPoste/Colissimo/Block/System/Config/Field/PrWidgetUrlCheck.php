<?php

/*******************************************************
 * Copyright (C) 2019 La Poste.
 *
 * This file is part of La Poste - Colissimo module.
 *
 * La Poste - Colissimo module can not be copied and/or distributed without the express
 * permission of La Poste.
 *******************************************************/

namespace LaPoste\Colissimo\Block\System\Config\Field;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use LaPoste\Colissimo\Helper;

class PrWidgetUrlCheck extends Field
{
    /**
     * @var string
     */
    protected $_template = 'LaPoste_Colissimo::system/config/field/prWidgetUrlCheck.phtml';
    protected $helperData;

    /**
     * @param Context $context
     * @param \LaPoste\Colissimo\Helper\Data $helperData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper\Data $helperData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helperData = $helperData;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Return ajax url for prWidgetUrlCheck button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl(
            $this->helperData->getAdminRoute('ajax', 'prWidgetUrlCheck')
        );
    }

    /**
     * Generate button html
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'prWidgetUrlCheck_button',
                'label' => __('Check'),
            ]
        );

        return $button->toHtml();
    }
}

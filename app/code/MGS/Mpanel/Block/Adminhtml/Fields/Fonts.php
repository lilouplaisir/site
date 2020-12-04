<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Block\Adminhtml\Fields;

/**
 * Sitemap edit form container
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Fonts extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * Retrieve element HTML markup
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $output = parent::_getElementHtml($element);

        //ob_start();

        $htmlId = $element->getHtmlId();
        $el = str_replace('mgstheme_fonts_', '', $htmlId);
        if ($el == 'h1' || $el == 'h2' || $el == 'h3' || $el == 'h4' || $el == 'h5' || $el == 'h6') {
            $output .= '<' . $el . ' id="' . $htmlId . '_view" style="display:block; padding:2px 0 0 0; margin:0">Heading ' . str_replace('h', '', $el) . '</' . $el . '>';
        } 
		else {
            if ($el == 'price') {
                $output .= '<div class="price-box accent" style="font-size:20px; display:block; padding:2px 0 0 0; margin:0"><span class="price" id="' . $element->getHtmlId() . '_view">$289</span></div>';
            } else {
                if ($el == 'menu') {
                    $output .= '<div id="' . $htmlId . '_view" style="font-size:16px; display:block; padding:2px 0 0 0; margin:0">HOME</div>';
                } else {
                    $output .= '<span id="' . $element->getHtmlId() . '_view" style="font-size:14px; display:block; padding:2px 0 0 0; margin:0">Lorem ipsum dolor sit amet</span>';
                }
            }
        }
		
		$output.="<script type='text/javascript'>
			require([
				'jquery'
			], function(jQuery){
				(function($) {
					$('#".$element->getHtmlId()."').change(function  () {
						$('#".$element->getHtmlId()."_view').css({fontFamily:  $('#".$element->getHtmlId()."').val().replace('+', ' ')});
						$('<link />', {href: '//fonts.googleapis.com/css?family=' + $('#".$element->getHtmlId()."').val(), rel: 'stylesheet', type:  'text/css'}).appendTo('head');
					}).keyup(function () {
						$('#".$element->getHtmlId()."_view').css({ fontFamily: $('#".$element->getHtmlId()."').val().replace('+', ' ')});
						$('<link />', {href: '//fonts.googleapis.com/css?family=' + $('#".$element->getHtmlId()."').val(), rel: 'stylesheet', type: 'text/css'}).appendTo('head');
					}).keydown(function () {
						$('#".$element->getHtmlId()."_ view').css({fontFamily: $('#".$element->getHtmlId()."').val().replace('+', ' ')});
						$('<link />', {href: '//fonts.googleapis.com/css?family=' + $('#".$element->getHtmlId()."').val(), rel: 'stylesheet', type: 'text/css'}).appendTo('head');
					});
					$('#".$element->getHtmlId()."').trigger('change');
				})(jQuery);
			});
		</script>";

        return $output;
    }
}

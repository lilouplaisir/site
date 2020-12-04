<?php
namespace MGS\Mpanel\Block\Widget;
 
class Slider extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
	public function _toHtml()
    {
    	$this->setTemplate('widget/owl_slider.phtml');
		return parent::_toHtml();
    }
	
	public function getAnimateSlider()
	{
		$animated = $this->getData('animate');
		$result = [];
		switch ($animated) {
            case 1:
                $result = array('out' => 'fadeOut', 'in' => 'fadeIn');
                break;
            case 2:
                $result = array('out' => 'fadeOutDown', 'in' => 'fadeInDown');
                break;
            case 3:
                $result = array('out' => 'fadeOut', 'in' => 'slideInDown');
                break;
            case 4:
                $result = array('out' => 'slideOutRight', 'in' => 'slideInLeft');
                break;
        }
		return $result;
	}
	public function getConfigResponsive()
	{
		$responsive = '{ 0 : {items: 1}, 480 : {items: 1}, 768 : {items: 1}, 980 : {items: 1}, 1200 : {items: 1} }';
		if($this->getData('banner_item') != ""){
			$responsive = $this->getData('banner_item');
		}
		return $responsive;
	}
	
	public function getAutoSpeed()
	{
		$autoSpeed = 3000;
		if($this->getData('banner_owl_speed') != ""){
			$autoSpeed = $this->getData('banner_owl_speed');
		}
		return $autoSpeed;
	}
	
	public function getAutoPlay()
	{
		if($this->getData('banner_owl_auto') != "" && $this->getData('banner_owl_auto') == 1){
			return 'true';
		}
		return 'false';
	}
	
	
	public function getControlNav()
	{
		if($this->getData('banner_owl_nav') != "" && $this->getData('banner_owl_nav') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getControlDots()
	{
		if($this->getData('banner_owl_dot') != "" && $this->getData('banner_owl_dot') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getRightToLeft()
	{
		if($this->getData('banner_owl_rtl') != "" && $this->getData('banner_owl_rtl') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getLoop()
	{
		if($this->getData('banner_owl_loop') != "" && $this->getData('banner_owl_loop') == 1){
			return 'true';
		}
		return 'false';
	}
	
	public function getHeightLoad()
	{
		$html = '700px';
		if($this->getData('banner_height') != "" && $this->getData('banner_height') == 1){
			$html = $this->getData('banner_height') . 'px';
		}
		return $html;
	}
	
	public function checkFull()
	{
		if($this->getData('banner_full') != "" && $this->getData('banner_full') == 1){
			return true;
		}
		return false;
	}
}
















<?php
namespace MGS\Amp\Helper;

class Setting extends \Magento\Framework\App\Helper\AbstractHelper {
	public function decodeHtmlTag($content){
		$result = str_replace("&lt;","<",$content);
		$result = str_replace("&gt;",">",$result);
		$result = str_replace('&#34;','"',$result);
		$result = str_replace("&#39;","'",$result);
		return $result;
	}
	
	public function getAmpCarouselSetting($data){
		$html = 'height="380" layout="fixed-height" type="slides"';
		if(isset($data['autoplay']) && $data['autoplay']){
			$html .= ' autoplay delay="5000"';
		}

		if(isset($data['navigation']) && $data['navigation']){
			$html .= ' controls';
		}
		return $html;
	}
}
<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Social\Block;

use Magento\Framework\View\Element\Template;

/**
 * Main contact form block
 */
class Instagram extends Template 
{
	
	public function _iscurl(){
		if(function_exists('curl_version')) {
			return true;
		} else {
			return false;
		}
	}	
	
	public function getInstagramUserId($user_name = NULL, $client_id = NULL) {
		$host = "https://api.instagram.com/v1/users/search?q=".$user_name."&client_id=".$client_id;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, TRUE);
		//print_r($content); exit();
		if(isset($content['meta']['error_message']) || !$content['data'][0]['id']) {
			echo 'This instagram information is not true.';
			return false;
		} else {
			return $content['data'][0]['id'];
		}
	}
	
	public function getInstagramData($access_token = NULL,$user_name = NULL, $client_id = NULL, $count = NULL, $width = NULL, $height = NULL) {
		$host = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$access_token;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, true);
		$j = 0;
		$i = 0;
		if(isset($content['data'])) {
			foreach($content['data'] as $contents){
				$j++;
			}
		}
		if(!(isset($content['data'][$i]['images']['low_resolution']['url'])) || !$content['data'][$i]['images']['low_resolution']['url']) {
			echo 'There are not any images in this instagram.';
			return false;
		}
		if(!$width){
			$width = 100;
		}
		if(!$height){
			$height = 100;
		}
		for($i=0 ; $i<$j; $i++){
			$html = "<a href='".$content['data'][$i]['link']."' rel='nofollow' target='_blank'><img width='".$width."' height='".$height."' src='".$content['data'][$i]['images']['low_resolution']['url']."' alt='' /></a>";
			echo $html;
		}
	}
	
	public function getWidgetInstagramData($access_token = NULL,$user_name = NULL, $client_id = NULL, $count = NULL, $width = NULL, $height = NULL) {
		$host = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$access_token;
		if($this->_iscurl()) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $host);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

			//curl_setopt($ch1, CURLOPT_POSTFIELDS, $para1);
			$content = curl_exec($ch);
			curl_close($ch);
		}
		else {
			$content = file_get_contents($host);
		}
		$content = json_decode($content, true);
		$j = 0;
		$i = 0;
		if(isset($content['data'])) {
			foreach($content['data'] as $contents){
				$j++;
			}
		}
		if(!(isset($content['data'][$i]['images']['low_resolution']['url'])) || !$content['data'][$i]['images']['low_resolution']['url']) {
			echo 'There are not any images in this instagram.';
			return false;
		}
		$images = array();
		for($i=0 ; $i<$j; $i++){
			$images[$i] = $content['data'][$i]['images']['low_resolution']['url'];
		}
		return $images;
	}
}
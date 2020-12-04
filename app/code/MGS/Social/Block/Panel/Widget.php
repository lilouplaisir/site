<?php

namespace MGS\Social\Block\Panel;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;


class Widget extends Template{
	
	
	public function __construct(
        Context $context,
		\MGS\Social\Helper\Data $helper
    )
    {       
		$this->helper = $helper;
        parent::__construct($context);
    }
	
	public function getFacebookFanBox() {
        $pageUrl = $this->getPageUrl();
        $width = $this->getWidth();
        $height = $this->getHeight();
		$pageTab = $this->getFacebookTabs();
		
        if ($this->getSmallHeader()) {
            $useSmallHeader = 'true';
        } else {
            $useSmallHeader = 'false';
        }
		
		if ($this->getFitInside()) {
			$dataAdaptContainerWidth = 'true';
		}else {
            $dataAdaptContainerWidth = 'false';
        }
		
        if ($this->getHideCover()) {
            $dataHideCover = 'true';
        } else {
            $dataHideCover = 'false';
        }
		
        if ($this->getShowFacepile()) {
            $dataShowFacepile = 'true';
        } else {
            $dataShowFacepile = 'false';
        }
		
        if ($this->getShowPosts()) {
            $dataShowPosts = 'true';
        } else {
            $dataShowPosts = 'false';
        }
		
        if ($pageUrl != '' && $width != '' && $height != '') {
            return '<div class="fb-page" data-tabs="'. $pageTab . '" data-href="'. $pageUrl . '" data-width="' . $width . '" data-height="' . $height . '" data-small-header="' . $useSmallHeader . '" data-adapt-container-width="' . $dataAdaptContainerWidth . '" data-hide-cover="' . $dataHideCover . '" data-show-facepile="' . $dataShowFacepile . '" data-show-posts="' . $dataShowPosts . '"><div class="fb-xfbml-parse-ignore"><blockquote cite="' . $pageUrl . '"><a href="' . $pageUrl . '">' . $this->getTitle() . '</a></blockquote></div></div>';
        } else {
            return null;
        }
    }
	
	public function getTwitterData($tweetUser,$token,$token_secret,$consumer_key,$consumer_secret, $count){
		$host = 'api.twitter.com';
		$method = 'GET';
		$path = '/1.1/statuses/user_timeline.json'; // api call path

		$query = array( // query parameters
			'screen_name' => $tweetUser,
			'count' => $count
		);

		$oauth = array(
			'oauth_consumer_key' => $consumer_key,
			'oauth_token' => $token,
			'oauth_nonce' => (string)mt_rand(), // a stronger nonce is recommended
			'oauth_timestamp' => time(),
			'oauth_signature_method' => 'HMAC-SHA1',
			'oauth_version' => '1.0'
		);

		$oauth = array_map("rawurlencode", $oauth); // must be encoded before sorting
		$query = array_map("rawurlencode", $query);

		$arr = array_merge($oauth, $query); // combine the values THEN sort

		asort($arr); // secondary sort (value)
		ksort($arr); // primary sort (key)

		// http_build_query automatically encodes, but our parameters
		// are already encoded, and must be by this point, so we undo
		// the encoding step
		$querystring = urldecode(http_build_query($arr, '', '&'));

		$url = "https://$host$path";

		// mash everything together for the text to hash
		$base_string = $method."&".rawurlencode($url)."&".rawurlencode($querystring);

		// same with the key
		$key = rawurlencode($consumer_secret)."&".rawurlencode($token_secret);

		// generate the hash
		$signature = rawurlencode(base64_encode(hash_hmac('sha1', $base_string, $key, true)));

		// this time we're using a normal GET query, and we're only encoding the query params
		// (without the oauth params)
		$url .= "?".http_build_query($query);

		$oauth['oauth_signature'] = $signature; // don't want to abandon all that work!
		ksort($oauth); // probably not necessary, but twitter's demo does it

		// also not necessary, but twitter's demo does this too
		$oauth = array_map(array($this, 'add_quotes'), $oauth);

		// this is the full value of the Authorization line
		$auth = "OAuth " . urldecode(http_build_query($oauth, '', ', '));

		// if you're doing post, you need to skip the GET building above
		// and instead supply query parameters to CURLOPT_POSTFIELDS
		$options = array( CURLOPT_HTTPHEADER => array("Authorization: $auth"),
						  //CURLOPT_POSTFIELDS => $postfields,
						  CURLOPT_HEADER => false,
						  CURLOPT_URL => $url,
						  CURLOPT_RETURNTRANSFER => true,
						  CURLOPT_SSL_VERIFYPEER => false);

		// do our business
		$feed = curl_init();
		curl_setopt_array($feed, $options);
		$json = curl_exec($feed);
		curl_close($feed);

		$twitter_data = json_decode($json);
		
		return $twitter_data;
	}
	
	public function add_quotes($str) { 
		return '"'.$str.'"'; 
	}

	public function relativeTimeUnix($pastTime){
		$origStamp = strtotime($pastTime);					
		$currentStamp = time();		
		$difference = intval(($currentStamp - $origStamp));
		return $difference;
	}
	
	public function formatTwitString($strTweet){
		$strTweet = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/','<a href="$0" target="_blank">$0</a>',$strTweet);
		$strTweet = preg_replace('/@([a-z0-9_]+)/i', '<a href="http://twitter.com/$1" target="_blank">@$1</a>', $strTweet);
		$strTweet = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $strTweet);

		$str = new \Magento\Framework\Stdlib\StringUtils;
		$str = $strTweet;
		
		return $str;
	}
	
	public function getLastTwitter($count = NULL){
		$tweetUser = $this->helper->getConfig('twitter_settings/client_twitteruser');

		$token = $this->helper->getConfig('twitter_settings/client_token');
		
		$token_secret = $this->helper->getConfig('twitter_settings/client_tokenSecret');
		
		$consumer_key = $this->helper->getConfig('twitter_settings/client_id');
		
		$consumer_secret = $this->helper->getConfig('twitter_settings/client_secret');
		
		$twitter_data = $this->getTwitterData($tweetUser,$token,$token_secret,$consumer_key,$consumer_secret, $count);
		
		$twitter_data = json_decode(json_encode($twitter_data), true);
		
		if($token!='' && $token_secret!='' && $consumer_key!='' && $consumer_secret!='' && $tweetUser!=''){
			if(!isset($twitter_data['errors'])){
				try{
					if(count($twitter_data)>0){
						return $twitter_data;
					}else {
						return;
					}
				}
				catch(Exception $e){
					return $e->getMessage();
				}
			}
		}
		return;
	}
	
	public function relativeTime($pastTime){
		$origStamp = strtotime($pastTime);	
			
		$currentStamp = time();		
		$difference = intval(($currentStamp - $origStamp));
		
		if($difference < 0)
		{
			return false;
		} 			
		
		if($difference <= 5){
			return $this->__("Just now");
		}			

		if($difference <= 20){
			return $this->__("Seconds ago");
		}			
		if($difference <= 60){
			return $this->__("A minute ago");
		}			
		if($difference < 3600){
			return intval($difference/60).__(" minutes ago");
		}			
		if($difference <= 1.5*3600){
			return $this->__("One hour ago");
		} 		
		if($difference < 23.5*3600){
			return round($difference/3600).__(" hours ago");
		}		
		
		if($difference < 1.5*24*3600){
			return __("One day ago");
		}           
		
		if($difference < 8640000000){
			return  round($difference/86400).__(" days ago");
		}		
			
	}
	
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
			$html = "<a href='".$content['data'][$i]['link']."' rel='nofollow' target='_blank'><img src='".$content['data'][$i]['images']['low_resolution']['url']."' alt='' /></a>";
			echo $html;
		}
	}
	
	public function getWidgetInstagramData() {
		$result = [];
		$instagramToken = $this->helper->getConfig('instagram_setting/access_token');
		if($instagramToken!=''){
			$host = "https://api.instagram.com/v1/users/self/media/recent/?access_token=".$instagramToken;
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

			$content = json_decode($content, true);
			
			$i=0;
			$limit = $this->getLimit();
			if(isset($content['data']) && count($content['data']) > 0){
				foreach($content['data'] as $data){
					$i++;
					$result[] = [
						'src' => $data['images'][$this->getResolution()]['url'],
						'link' => $data['link'],
						'like' => $data['likes']['count'],
						'comment' => $data['comments']['count']
					];
					if(($limit!='') && ($limit!=0) && ($i==$limit)){
						break;
					}
				}
			}
		}
		
		return $result;
	}
}
?>
<?php

namespace MGS\Social\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MGS\Social\Helper\Data as SocialHelper;


class Tweets extends Template{
	
	 public function __construct(
        Context $context,
        SocialHelper $socialHelper
    )
    {       
        $this->socialHelper = $socialHelper;
        parent::__construct($context);
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
	
	public function formatTwitString($strTweet, $truncate){
		$strTweet = preg_replace('/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/','<a href="$0" target="_blank">$0</a>',$strTweet);
		$strTweet = preg_replace('/@([a-z0-9_]+)/i', '<a href="http://twitter.com/$1" target="_blank">@$1</a>', $strTweet);
		$strTweet = preg_replace('/(^|\s)#(\w*[a-zA-Z_]+\w*)/', '\1<a href="http://search.twitter.com/search?q=%23\2">#\2</a>', $strTweet);

		$str = new \Magento\Framework\Stdlib\StringUtils;
		$str = $str->substr($strTweet,0,$truncate);
		
		return $str;
	}
	
	public function getLastTwitter($tweetUser = NULL, $count = NULL, $truncate = NULL){
	
		if($tweetUser == null){
			$tweetUser = $this->socialHelper->getConfig('twitter_settings/client_twitteruser');
		}
		$token = $this->socialHelper->getConfig('twitter_settings/client_token');
		
		$token_secret = $this->socialHelper->getConfig('twitter_settings/client_tokenSecret');
		
		$consumer_key = $this->socialHelper->getConfig('twitter_settings/client_id');
		
		
		$consumer_secret = $this->socialHelper->getConfig('twitter_settings/client_secret');
		
		if($count == null || $count=='' || $count == 0){
			$count = $this->socialHelper->getConfig('twitter_settings/client_count');
		}
		
		$twitter_data = $this->getTwitterData($tweetUser,$token,$token_secret,$consumer_key,$consumer_secret, $count);
		
		$twitter_data = json_decode(json_encode($twitter_data), true);
		
		
		$html = '';
		if($token!='' && $token_secret!='' && $consumer_key!='' && $consumer_secret!='' && $tweetUser!=''){
			if(!isset($twitter_data['errors'])){
				try{
					if(count($twitter_data)>0){
						$html .= '<div class="twitter_feed social-tweet">';
						foreach ($twitter_data as $key => $value) {
							$html .= '<div class="tweet-container"><div class="icon"><i class="fa fa-twitter"></i></div><div class="tweet-content">@ <a href="https://twitter.com/'.$twitter_data[$key]['user']['screen_name'].'/status/'.$twitter_data[$key]['id_str'].'">'.$twitter_data[$key]['user']['screen_name'].'</a><div>'.$this->formatTwitString($twitter_data[$key]['text']).'<p class="times"> <a href="https://twitter.com/'.$twitter_data[$key]['user']['screen_name'].'/status/'.$twitter_data[$key]['id_str'].'">about '.$this->relativeTime($twitter_data[$key]['created_at']).'</a></p></div></div></div>';
						}
						$html .= '</div>';
					}
				}
				catch(Exception $e){
					echo $e->getMessage();
				}
			}
		}
		return $html;
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
	
}
?>
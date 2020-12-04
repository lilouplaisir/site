<?php

namespace MGS\Blog\Helper;

class Data extends \MGS\Mpanel\Helper\Data
{

    public function getConfig($key, $store = null)
    {
		return $this->getStoreConfig('blog/' . $key);
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA);
    }

    public function getRoute()
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route;
    }

    public function getTagUrl($tag)
    {
        $route = $this->getConfig('general_settings/route');
        if ($this->getConfig('general_settings/route') == '') {
            $route = 'blog';
        }
        return $this->_storeManager->getStore()->getBaseUrl() . $route . '/tag/' . urlencode($tag);
    }

    public function convertSlashes($tag, $direction = 'back')
    {
        if ($direction == 'forward') {
            $tag = preg_replace("#/#is", "&#47;", $tag);
            $tag = preg_replace("#\\\#is", "&#92;", $tag);
            return $tag;
        }
        $tag = str_replace("&#47;", "/", $tag);
        $tag = str_replace("&#92;", "\\", $tag);
        return $tag;
    }

    public function checkLoggedIn()
    {
        return $this->_objectManager->get('Magento\Customer\Model\Session')->isLoggedIn();
    }
	
    public function getThumbnailPost($post)
    {	
		$html = "";
		if($post->getVideoThumbId() != ""){
			if($post->getVideoThumbType() == "youtube"){
				$video_url = 'https://www.youtube.com/embed/'.$post->getVideoThumbId();
			}else {
				$video_url = 'https://player.vimeo.com/video/'.$post->getVideoThumbId();
			}
			$html .= '<div class="video-responsive">';
			$html .= '<iframe width="1024" height="768" src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
			$html .= '</div>';
		}
        return $html;
    }
	
	public function getPostUrl($post) {
		$store = $this->_storeManager->getStore()->getCode();
		
		if($store){
			$url = $post->getPostUrlWithNoCategory() . '?___store=' . $store;
		}else{
			$url = $post->getPostUrlWithNoCategory();
		}
		
		return $url;
	}
	
    public function getImagePost($post)
    {	
		$html = "";
		if($post->getImageType() == "video"){
			if($post->getVideoBigId() != ""){
				if($post->getVideoBigType() == "youtube"){
					$video_url = 'https://www.youtube.com/embed/'.$post->getVideoBigId();
				}else {
					$video_url = 'https://player.vimeo.com/video/'.$post->getVideoBigId();
				}
				$html .= '<div class="video-responsive">';
				$html .= '<iframe width="1024" height="768" src="'.$video_url.'" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
				$html .= '</div>';
			}
		}else {
			if($post->getImageUrl() == ""){
				$html = "";
			}else {
				$html .= '<img class="img-responsive" alt="'.$post->getTitle().'" src="'.$post->getImageUrl().'">';
			}
		}
        return $html;
    }
	
	
	public function getThumbnailImgVideoPost($post)
    {	
		if($post->getThumbType() == "video"){
			if($post->getVideoThumbId() != ""){
				if($post->getVideoThumbType() == "youtube"){
					return 'http://img.youtube.com/vi/'.$post->getVideoThumbId().'/hqdefault.jpg';
				}else {
					$info = 'thumbnail_medium';
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, 'vimeo.com/api/v2/video/'.$post->getVideoThumbId().'.php');
					curl_setopt($ch, CURLOPT_HEADER, 0);
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
					curl_setopt($ch, CURLOPT_TIMEOUT, 10);
					$output = unserialize(curl_exec($ch));
					$output = $output[0][$info];
					curl_close($ch);
					return $output;
				}
			}
			
		}
		return;
    }
	
}

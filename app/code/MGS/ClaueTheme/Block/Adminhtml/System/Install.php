<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Block\Adminhtml\System;

class Install extends \MGS\Mpanel\Block\Adminhtml\System\Install
{	
	
	public function isLocalhost() {
        $whitelist = array(
            '127.0.0.1',
			'localhost',
            '::1'
        );
        
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }

	
	public function _getHeaderCommentHtml($element)
    {
		$html = '<table class="form-list" cellspacing="0"><tbody>';

		if(is_dir($this->_dir)) {
            if ($dh = opendir($this->_dir)) {
				$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
				$activeKey = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/claue');
				$dirs = scandir($this->_dir);
				
				foreach($dirs as $theme){
					if(($theme !='') && ($theme!='.') && ($theme!='..')){
						$themeName = $this->convertString($theme);
						
						if($storeId = $this->getRequest()->getParam('store')){
							$url = $this->getUrl('adminhtml/mpanel/install', ['store'=>$storeId, 'theme'=>$theme]);
						}
						elseif($websiteId = $this->getRequest()->getParam('website')){
							$url = $this->getUrl('adminhtml/mpanel/install', ['website'=>$websiteId, 'theme'=>$theme]);
						}else{
							$url = $this->getUrl('adminhtml/mpanel/install', ['theme'=>$theme]);
						}
						
						$html .= '<tr><td style="padding:0 30px 10px">';
						if(($theme=='claue' || $theme=='claue_rtl') && !$this->isLocalhost()){
							
							if($activeKey!=''){
								$html .= '<button data-ui-id="widget-button-0" onclick="setLocation(\''.$url.'\')" class="action-default scalable" type="button" title="'.__('Install %1 theme', $themeName).'"><span>'.__('Install %1 theme', $themeName).'</span></button>';
							}else{
								$html .= '<button data-ui-id="widget-button-0" onclick="return false;" class="action-default scalable" type="button" title="'.__('Install %1 theme', $themeName).'" disabled="disabled" style="margin-right:10px"><span>'.__('Install %1 theme', $themeName).'</span></button><a href="'.$this->getUrl('adminhtml/system_config/edit/section/active_theme').'" style="text-decoration:none"><span style="color:#ff0000">'.__('Activation is required.').'</span></a>';
							}
						}else{
							$html .= '<button data-ui-id="widget-button-0" onclick="setLocation(\''.$url.'\')" class="action-default scalable" type="button" title="'.__('Install %1 theme', $themeName).'"><span>'.__('Install %1 theme', $themeName).'</span></button>';
						}
						$html .= '</td></tr>';
					}
				}

                closedir($dh);
            }
        }

        
		$html .= '</tbody></table>';
        return $html;
    }


}

<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Block\Adminhtml\System;

use Magento\Framework\App\Filesystem\DirectoryList;
class Import extends \MGS\Mpanel\Block\Adminhtml\System\Import
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
		$html = '';
		if(is_dir($this->_dir)) {
            if ($dh = opendir($this->_dir)) {
				$dirs = scandir($this->_dir);
				
				foreach($dirs as $theme){
					if(($theme !='') && ($theme!='.') && ($theme!='..')){
						$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
						$activeKey = $objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/claue');
						$themeName = $this->convertString($theme);
						$html .= '<div>';
						$html .= '<div class="section-config"><div class="entry-edit-head admin__collapsible-block"><span class="entry-edit-head-link" id="mgstheme_import_'.$theme.'-link"></span><a onclick="Fieldset.toggleCollapse(\'mgstheme_import_'.$theme.'\', \''.$this->getUrl('adminhtml/system_config/state').'\'); return false;" href="#mgstheme_import_'.$theme.'-link" id="mgstheme_import_'.$theme.'-head">'.$themeName.'</a></div><input type="hidden" value="0" name="config_state[mgstheme_import_'.$theme.']" id="mgstheme_import_'.$theme.'-state"><fieldset id="mgstheme_import_'.$theme.'" class="config admin__collapsible-block" style="display:none"><legend>'.$themeName.'</legend>';
						
						$themeDir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes/'.$theme.'/homes');
						
						$fileHomes = array();
						if (is_dir($themeDir)) {
							if ($dhHome = opendir($themeDir)) {
								while ($fileHomes[] = readdir($dhHome));
								sort($fileHomes);
								if(count($fileHomes)>0){
									$html .= '<table><tbody>';
									foreach ($fileHomes as $fileHome){
										$file_parts_home = pathinfo($themeDir.'/'.$fileHome);
										if(isset($file_parts_home['extension']) && $file_parts_home['extension']=='xml'){
											$html .= '<tr>';
											$html .= '<td style="padding:0 40px 10px 0; width:250px">';
											$homeName = $home = str_replace('.xml','',$fileHome);
											$homeName = $this->convertString($homeName);
											$html .= '<img src="'.$this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]).'mgs/'.$theme.'/homes/'.$home.'.png" width="250"/>';
											$html .= '</td><td>';
											
											if($storeId = $this->getRequest()->getParam('store')){
												$url = $this->getUrl('adminhtml/mpanel/import', ['store'=>$storeId, 'theme'=>$theme, 'home'=>$home]);
											}
											elseif($websiteId = $this->getRequest()->getParam('website')){
												$url = $this->getUrl('adminhtml/mpanel/import', ['website'=>$websiteId, 'theme'=>$theme, 'home'=>$home]);
											}else{
												$url = $this->getUrl('adminhtml/mpanel/import', ['theme'=>$theme, 'home'=>$home]);
											}
											if(($theme=='claue' || $theme=='claue_rtl') && !$this->isLocalhost()){
												
												if($activeKey!=''){
													$html .= '<button data-ui-id="widget-button-0" onclick="setLocation(\''.$url.'\')" class="action-default scalable" type="button" title="'.__('Import %1', $homeName).'"><span>'.__('Import %1', $homeName).'</span></button>';
												}else{
													$html .= '<button data-ui-id="widget-button-0" onclick="return false;" class="action-default scalable" type="button" title="'.__('Import %1', $homeName).'" style="margin-right:10px" disabled="disabled"><span>'.__('Import %1', $homeName).'</span></button><a href="'.$this->getUrl('adminhtml/system_config/edit/section/active_theme').'" style="text-decoration:none"><span style="color:#ff0000">'.__('Activation is required.').'</span></a>';
												}
											}else{
												$html .= '<button data-ui-id="widget-button-0" onclick="setLocation(\''.$url.'\')" class="action-default scalable" type="button" title="'.__('Import %1', $homeName).'"><span>'.__('Import %1', $homeName).'</span></button>';
											}
											
											$html .= '</td></tr>';
										}
									}
									$html .= '</tbody></table>';
								}
							}
						}
						
						$html .= '</fieldset><script type="text/javascript">//<![CDATA[require([\'prototype\'],function(){Fieldset.applyCollapse(\'mgstheme_import_'.$theme.'\');});//]]></script></div>';
						
						$html .= '</div>';
					}
				}

                closedir($dh);
            }
        }

        return $html;
    }

}

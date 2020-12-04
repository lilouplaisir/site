<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\Mpanel\Controller\Adminhtml\Mpanel;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Export extends \MGS\Mpanel\Controller\Adminhtml\Mpanel
{	
    protected $_section;
	
    protected $_block;
	
    protected $_banner;
	
    protected $_config;
	
	protected $_filesystem;
	
	protected $_ioFile;
	
	protected $_themeHelper;
	
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\Magento\Config\Model\ResourceModel\Config\Data\CollectionFactory $config,
		\MGS\Mpanel\Model\ResourceModel\Section\CollectionFactory $sectionFactory,
		\MGS\Mpanel\Model\ResourceModel\Childs\CollectionFactory $blockFactory,
		\MGS\Promobanners\Model\ResourceModel\Promobanners\CollectionFactory $bannerFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Filesystem\Io\File $ioFile,
		\MGS\Mpanel\Helper\Data $_themeHelper
	){
        parent::__construct($context);
		$this->_config = $config;
		$this->_section = $sectionFactory;
		$this->_block = $blockFactory;
		$this->_banner = $bannerFactory;
		$this->_filesystem = $filesystem;
		$this->_ioFile = $ioFile;
		$this->_themeHelper = $_themeHelper;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
		if(($theme = $this->getRequest()->getParam('theme')) && ($store = $this->getRequest()->getParam('store'))){
			$pageIdentifier = $this->_themeHelper->getStoreConfig('web/default/cms_home_page', $store);
			$page = $this->_objectManager->create('Magento\Cms\Model\Page')
				->setStoreId($store)->load($pageIdentifier);
			$pageId = $page->getId();
			$sectionCollection = $this->_section->create();
			$sectionCollection->addFieldToFilter('store_id', $store);
			$sectionCollection->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
			if(count($sectionCollection)>0){
				$content = "<home>\n";
				foreach($sectionCollection as $section){
					$content .= "\t<section>\n";
					$sectionData = $section->getData();
					unset($sectionData['block_id'], $sectionData['store_id'], $sectionData['page_id']);
					foreach($sectionData as $sectionColumn=>$value){
						$content .= "\t\t<".$sectionColumn."><![CDATA[".$value."]]></".$sectionColumn.">\n";
					}
					$content .= "\t</section>\n";
				}
				
				$blockCollection = $this->_block->create();
				$blockCollection->addFieldToFilter('store_id', $store);
				$blockCollection->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
				if(count($blockCollection)>0){
					$bannerIds = [];
					foreach($blockCollection as $block){
						$content .= "\t<block>\n";
						$blockData = $block->getData();
						unset($blockData['child_id'], $blockData['home_name'], $blockData['static_block_id'], $blockData['store_id'], $blockData['page_id']);
						foreach($blockData as $blockColumn=>$blockValue){
							$content .= "\t\t<".$blockColumn."><![CDATA[".$blockValue."]]></".$blockColumn.">\n";
							
						}
						
						if($blockData['type']=='promo_banner'){
							$setting = $blockData['setting'];
							$arrSetting = json_decode($setting, true);
							$bannerIds[] = $arrSetting['banner_id'];
							//echo '<pre>'; print_r($arrSetting); die();
						}
						
						$content .= "\t</block>\n";
					}
					
					if(count($bannerIds)>0){
						$bannerCollection = $this->_banner->create();
						$bannerCollection->addFieldToFilter('identifier', ['in'=>[$bannerIds]]);
						if(count($bannerCollection)>0){
							$content .= "\t<promo_banner>\n";
							foreach($bannerCollection as $banner){
								$bannerData = $banner->getData();
								unset($bannerData['promobanners_id'], $bannerData['creation_time'], $bannerData['update_time']);
								$content .= "\t\t<item>\n";
								foreach($bannerData as $bannerColumn=>$bannerValue){
									$content .= "\t\t\t<".$bannerColumn."><![CDATA[".$bannerValue."]]></".$bannerColumn.">\n";
								}
								$content .= "\t\t</item>\n";
							}
							$content .= "\t</promo_banner>\n";
						}
					}
				}
				
				
				$generalThemeSetting = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'mgstheme/general/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
				
				$backgroundThemeSetting = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'mgstheme/background/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
				
				$fontsThemeSetting = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'mgstheme/fonts/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
					
				$customStyleThemeSetting = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'mgstheme/custom_style/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
				
				if((count($generalThemeSetting)>0) || (count($backgroundThemeSetting)>0) || (count($fontsThemeSetting)>0) || (count($customStyleThemeSetting)>0)){
					$content .= "\t<theme_setting>\n";
					if(count($generalThemeSetting)>0){
						$content .= "\t\t<general>\n";
						foreach($generalThemeSetting as $general){
							$field = str_replace('mgstheme/general/','',$general->getPath());
							$content .= "\t\t\t<".$field.">".$general->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</general>\n";
					}
					
					if(count($backgroundThemeSetting)>0){
						$content .= "\t\t<background>\n";
						foreach($backgroundThemeSetting as $background){
							$field = str_replace('mgstheme/background/','',$background->getPath());
							$content .= "\t\t\t<".$field.">".$background->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</background>\n";
					}
					
					if(count($fontsThemeSetting)>0){
						$content .= "\t\t<fonts>\n";
						foreach($fontsThemeSetting as $fonts){
							$field = str_replace('mgstheme/fonts/','',$fonts->getPath());
							$content .= "\t\t\t<".$field.">".$fonts->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</fonts>\n";
					}
					
					if(count($customStyleThemeSetting)>0){
						$content .= "\t\t<custom_style>\n";
						foreach($customStyleThemeSetting as $custom_style){
							$field = str_replace('mgstheme/custom_style/','',$custom_style->getPath());
							$content .= "\t\t\t<".$field.">".$custom_style->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</custom_style>\n";
					}
					$content .= "\t</theme_setting>\n";
				}
				
				$generalThemeColor = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'color/general/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
					
				$headerThemeColor = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'color/header/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
					
				$mainThemeColor = $this->_config->create()
					->addFieldToFilter('path', ['like'=>'color/main/%'])
					->addFieldToFilter('scope', 'stores')
					->addFieldToFilter('scope_id', $store);
				
				if((count($generalThemeColor)>0) || (count($headerThemeColor)>0) || (count($mainThemeColor)>0)){
					$content .= "\t<color_setting>\n";
					if(count($generalThemeColor)>0){
						$content .= "\t\t<general>\n";
						foreach($generalThemeColor as $general){
							$field = str_replace('color/general/','',$general->getPath());
							$content .= "\t\t\t<".$field.">".$general->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</general>\n";
					}
					
					if(count($headerThemeColor)>0){
						$content .= "\t\t<header>\n";
						foreach($headerThemeColor as $header){
							$field = str_replace('color/header/','',$header->getPath());
							$content .= "\t\t\t<".$field.">".$header->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</header>\n";
					}
					
					if(count($mainThemeColor)>0){
						$content .= "\t\t<main>\n";
						foreach($mainThemeColor as $main){
							$field = str_replace('color/main/','',$main->getPath());
							$content .= "\t\t\t<".$field.">".$main->getValue()."</".$field.">\n";
						}
						$content .= "\t\t</main>\n";
					}
					
					$content .= "\t</color_setting>\n";
				}
				
				$content .= "\n</home>";
			
				$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes/'.$theme.'/homes/');
				$io = $this->_ioFile;
				$file = $filePath . '/home_of_store_' . $store . '.xml';
				$io->setAllowCreateFolders(true);
				$io->open(array('path' => $filePath));
				$io->write($file, $content, 0644);
				$io->streamClose();
			}
		}
        $this->_redirect($this->_redirect->getRefererUrl());
    }
}

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
use Magento\Framework\Exception\LocalizedException;
class Import extends \MGS\Mpanel\Controller\Adminhtml\Mpanel
{
	/**
     * Backend Config Model Factory
     *
     * @var \Magento\Config\Model\Config\Factory
     */
    protected $_configFactory;
	
	/**
     * @var \Magento\Framework\Stdlib\StringUtils
     */
    protected $_string;
	
	/**
	 * @var \Magento\Framework\Xml\Parser
	 */
	private $_parser;
	
	protected $_filesystem;
	
	protected $_xmlArray;
	protected $_home;
	protected $_theme;
	protected $_storeManager;
	protected $_themeHelper;
	protected $_sectionFactory;
    protected $_childsFactory;
	
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
	
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\Magento\Config\Model\Config\Factory $configFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Xml\Parser $parser,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\StringUtils $string,
		\MGS\Mpanel\Helper\Data $_themeHelper,
		\Magento\Framework\View\Element\Context $urlContext,
		\MGS\Mpanel\Model\ResourceModel\Section\CollectionFactory $sectionFactory,
		\MGS\Mpanel\Model\ResourceModel\Childs\CollectionFactory $childsFactory
	){
        parent::__construct($context);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
		$this->_storeManager = $storeManager;
		$this->_themeHelper = $_themeHelper;
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		$this->_sectionFactory = $sectionFactory;
		$this->_childsFactory = $childsFactory;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if(($this->_theme = $this->getRequest()->getParam('theme')) && ($this->_home = $this->getRequest()->getParam('home'))){
			$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes/'.$this->_theme.'/homes');
			$homepageFile = $dir.'/'.$this->_home.'.xml';
			
			if($websiteId = $this->getRequest()->getParam('website')){
				$stores = $this->_storeManager->getWebsite($websiteId)->getStores();
			}else{
				$stores = $this->_storeManager->getWebsite()->getStores();
			}
			$storeIds = [];
			foreach($stores as $_store){
				$storeIds[] = $_store->getId();
			}
			
			if (is_readable($homepageFile)){
				try {
					$this->_xmlArray = $this->_parser->load($homepageFile)->xmlToArray();
					/* Import Sections */
					
					$homeStores = $this->_objectManager->create('MGS\Mpanel\Model\Store')
						->getCollection();
					
					if($this->getRequest()->getParam('website')){
						$homeStores->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($storeId = $this->getRequest()->getParam('store')){
							$homeStores->addFieldToFilter('store_id', $storeId);
						}
					}
					
					
					if (count($homeStores) > 0){
						foreach ($homeStores as $_homeStore){
							$_homeStore->delete();
						}
					}
					
					$pageIdentifier = $this->_themeHelper->getStoreConfig('web/default/cms_home_page', $storeId);
					$arrIdentifier = explode('|', $pageIdentifier);
					$page = $this->_objectManager->create('Magento\Cms\Model\Page')
						->setStoreId($storeId)->load($arrIdentifier[0]);
					$pageId = $page->getId();
					
					// Remove old sections
					$sections = $this->_objectManager->create('MGS\Mpanel\Model\Section')
						->getCollection();
					$sections->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
					
					if($this->getRequest()->getParam('website')){
						$sections->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($this->getRequest()->getParam('store')){
							$sections->addFieldToFilter('store_id', $storeId);
						}
					}

					if (count($sections) > 0){
						foreach ($sections as $_section){
							$_section->delete();
						}
					}
					
					// Remove old blocks
					$childs = $this->_objectManager->create('MGS\Mpanel\Model\Childs')
						->getCollection();
					$childs->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
					if($this->getRequest()->getParam('website')){
						$childs->addFieldToFilter('store_id', ['in'=>$storeIds]);
					}else{
						if($this->getRequest()->getParam('store')){
							$childs->addFieldToFilter('store_id', $storeId);
						}
					}

					if (count($childs) > 0){
						foreach ($childs as $_child){
							$_child->delete();
						}
					}
					
					// Set use page builder for store view
					if($this->getRequest()->getParam('store')){
						$this->_objectManager->create('MGS\Mpanel\Model\Store')->setStoreId($storeId)->setStatus(1)->save();
					}
					else{
						foreach($storeIds as $_store){
							$this->_objectManager->create('MGS\Mpanel\Model\Store')->setStoreId($_store)->setStatus(1)->save();
						}
					}
					
					$html = '';
					
					// Import new sections
					$sectionArray = $this->_xmlArray['home']['section'];
					if(isset($sectionArray)){
						if($this->getRequest()->getParam('store')){
							if(isset($sectionArray[0]['name'])){
								foreach($sectionArray as $section){
									$section['store_id'] = $storeId;
									$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($section)->save();
								}
							}else{
								$sectionArray['store_id'] = $storeId;
								$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($sectionArray)->save();
							}
						}
						else{
							if(isset($sectionArray[0]['name'])){
								foreach($storeIds as $_store){
									foreach($sectionArray as $section){
										$section['store_id'] = $_store;
										$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($section)->save();
									}
								}
							}else{
								foreach($storeIds as $_store){
									$sectionArray['store_id'] = $_store;
									$this->_objectManager->create('MGS\Mpanel\Model\Section')->setData($sectionArray)->save();
								}
							}
						}
					}
					
					// Import new blocks
					$blockArray = $this->_xmlArray['home']['block'];
					if(isset($blockArray)){
						if($this->getRequest()->getParam('store')){
							if(isset($blockArray[0]['block_name'])){
								foreach($blockArray as $block){
									$block['store_id'] = $storeId;
									$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($block)->save();
								}
							}else{
								$blockArray['store_id'] = $storeId;
								$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($blockArray)->save();
							}
						}
						else{
							if(isset($blockArray[0]['block_name'])){
								foreach($storeIds as $_store){
									foreach($blockArray as $block){
										$block['store_id'] = $_store;
										$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($block)->save();
									}
								}
							}else{
								foreach($storeIds as $_store){
									$blockArray['store_id'] = $_store;
									$this->_objectManager->create('MGS\Mpanel\Model\Childs')->setData($blockArray)->save();
								}
							}
						}
					}

					$this->importPromoBanner();
					
					/* Import Theme Setting And Color Setting*/
					$this->_importSetting();
					
					/* Import CMS Page */
					if($storeId = $this->getRequest()->getParam('store')){
						$this->importContentForCms($storeId, $pageId);
					}else{
						foreach($storeIds as $storeId){
							$this->importContentForCms($storeId, $pageId);
						}
					}
					
					$this->messageManager->addSuccess(__('%1 was successfully imported.', $this->convertString($this->_home)));
				}catch (\Exception $e) {
					// display error message
					$this->messageManager->addError($e->getMessage());
					//echo $e->getMessage();
				}
			}else{
				$this->messageManager->addError(__('Cannot import this homepage.'));
			}
			$this->_themeHelper->generateCssForAll();
		}else{
			$this->messageManager->addError(__('This homepage no longer exists.'));
		}
		$this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
	
	public function importContentForCms($storeId, $pageId){
		
		$sections = $this->getSections($storeId, $pageId);
			
		$html = '';
		if(count($sections)>0){
			foreach($sections as $_section){
				$html .= '<div'.$this->getSectionSetting($_section).'>';
				if($_section->getFullwidth()){
					$html .= '<div class="container-fluid no-padding">';
				}else{
					$html .= '<div class="container">';
				}
				
				$cols = $this->getBlockCols($_section);
				$class = $_section->getBlockClass();
				if($class!=''){
					$class = json_decode($class, true);
				}
				
				if(count($cols)>1){
					$html .= '<div class="row">';
						foreach($cols as $key=>$col){
							$blockClass = $this->getBlockClass($_section, $col, $class, $key);
							$html .= '<div class="'.$blockClass.'">';
								$html .= '<div class="row">';
									
									$blocks = $this->getBlocks($storeId, $_section->getName().'-'.$key, $pageId);
									
									foreach($blocks as $_block){
										$setting = json_decode($_block->getSetting(), true);
											$html .= '<div class="panel-block-row '.$this->getChildClass($_block, $setting).'"';
											if(isset($setting['animation']) && $setting['animation']!=''){
												$html .= ' data-appear-animation="'.$setting['animation'].'"';
											}
											if(isset($setting['animation_delay']) && $setting['animation_delay']!=''){
												$html .= ' data-appear-animation-delay="'.$setting['animation_delay'].'"';
											} 
											$html .= '>';
											$html .= $_block->getBlockContent();
											$html .= '</div>';
									}
									
								$html .= '</div>';
							$html .= '</div>';
						}
					$html .= '</div>';
				}else{
					$html .= '<div class="row">';
						$html .= '<div class="col-lg-12 col-md-12">';
							$html .= '<div class="row">';
								
								$blocks = $this->getBlocks($storeId, $_section->getName().'-0', $pageId);
								
								foreach($blocks as $_block){
									$setting = json_decode($_block->getSetting(), true);
										$html .= '<div class="panel-block-row '.$this->getChildClass($_block, $setting).'"';
										if(isset($setting['animation']) && $setting['animation']!=''){
											$html .= ' data-appear-animation="'.$setting['animation'].'"';
										}
										if(isset($setting['animation_delay']) && $setting['animation_delay']!=''){
											$html .= ' data-appear-animation-delay="'.$setting['animation_delay'].'"';
										} 
										$html .= '>';
										$html .= $_block->getBlockContent();
										$html .= '</div>';
								}
							$html .= '</div>';
						$html .= '</div>';
					$html .= '</div>';
				}
				
				$html .= '</div></div>';
			}
		}
		if($html!=''){
			$cmsPageModel = $this->_objectManager->create('Magento\Cms\Model\Page');
			$cmsPageModel->load($pageId);
			$cmsPageModel->setContent($html);
			$cmsPageModel->save();
		}
	}
	
	public function getSections($storeId, $pageId){
		$sectionCollection = $this->_sectionFactory->create()
			->addFieldToFilter('store_id', $storeId)
			->setOrder('block_position', 'ASC');
		$sectionCollection->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');

		return $sectionCollection;
	}
	
	public function getBlocks($storeId, $blockName, $pageId){
		$blocks = $this->_childsFactory->create()
				->addFieldToFilter('block_name', $blockName)
				->addFieldToFilter('store_id', $storeId)
				->setOrder('position', 'ASC');
		$blocks->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
		

		return $blocks;
	}
	
	public function getChildClass($block, $setting){
		$class = 'col-md-' . $block->getCol();

		if($block->getClass()!=''){
			$class .= ' '.$block->getClass();
		}
        if (isset($setting['custom_class']) && $setting['custom_class'] != '') {
            $class .= ' ' . $setting['custom_class'];
        }
        if (isset($setting['text_colour']) && $setting['text_colour'] != '') {
            $class .= ' ' . $setting['text_colour'];
        }
        if (isset($setting['link_colour']) && $setting['link_colour'] != '') {
            $class .= ' ' . $setting['link_colour'];
        }
        if (isset($setting['link_hover_colour']) && $setting['link_hover_colour'] != '') {
            $class .= ' ' . $setting['link_hover_colour'];
        }
        if (isset($setting['button_colour']) && $setting['button_colour'] != '') {
            $class .= ' ' . $setting['button_colour'];
        }
        if (isset($setting['button_hover_colour']) && $setting['button_hover_colour'] != '') {
            $class .= ' ' . $setting['button_hover_colour'];
        }
        if (isset($setting['button_text_colour']) && $setting['button_text_colour'] != '') {
            $class .= ' ' . $setting['button_text_colour'];
        }
        if (isset($setting['button_text_hover_colour']) && $setting['button_text_hover_colour'] != '') {
            $class .= ' ' . $setting['button_text_hover_colour'];
        }
        if (isset($setting['button_border_colour']) && $setting['button_border_colour'] != '') {
            $class .= ' ' . $setting['button_border_colour'];
        }
        if (isset($setting['button_border_hover_colour']) && $setting['button_border_hover_colour'] != '') {
            $class .= ' ' . $setting['button_border_hover_colour'];
        }
        if (isset($setting['price_colour']) && $setting['price_colour'] != '') {
            $class .= ' ' . $setting['price_colour'];
        }
		if (isset($setting['animation']) && $setting['animation'] != '') {
            $class .= ' animated';
        }

        return $class;
	}
	
	public function getBlockClass($section, $col, $arrClass, $key){
		$class = 'col-lg-'.$col.' col-md-'.$col;
		
		$colTablets = json_decode($section->getTabletCols(), true);
		if(is_array($colTablets) && isset($colTablets[$key])){
			$class .= ' col-sm-'.$colTablets[$key];
		}
		$colMobiles = json_decode($section->getMobileCols(), true);
		if(is_array($colMobiles) && isset($colMobiles[$key])){
			$class .= ' col-xs-'.$colMobiles[$key];
		}
		if(is_array($arrClass) && isset($arrClass[$key])){
			$class .= ' '.$arrClass[$key];
		}

		return $class;
	}
	
	public function getBlockCols($section){
		$cols = $section->getBlockCols();
		$cols = str_replace(' ','',$cols);
		$arr = explode(',', $cols);
		return $arr;
	}
	
	public function getSectionSetting($section){
		$html = ' class="';
        if ($section->getId()) {
            if ($section->getClass() != '') {
                $html.= $section->getClass() ;
            }

            if ($section->getParallax() & ($section->getBackgroundImage() != '')) {
                $html.= ' parallax';
            }

            $html.= '" style="';

            if ($section->getBackground() != '') {
                $html.= 'background-color: ' .$section->getBackground() . ';';
            }

            if ($section->getBackgroundImage() != '') {
                $html.= 'background-image: url(\'' . $this->_urlBuilder->getBaseUrl(['_type' => \Magento\Framework\UrlInterface::URL_TYPE_MEDIA]) . 'mpanel/backgrounds/' . $section->getBackgroundImage() . '\');';

                if (!$section->getParallax()) {
					if($section->getBackgroundRepeat()){
						$html.= 'background-repeat:repeat;';
					}else{
						$html.= 'background-repeat:no-repeat;';
					}
					
					if($section->getBackgroundCover()){
						$html.= 'background-size:cover;';
					}
                }
            }



            if ($section->getPaddingTop() != '') {
                $html.= ' padding-top:' . $section->getPaddingTop() . 'px;';
            }

            if ($section->getPaddingBottom() != '') {
                $html.= ' padding-bottom:' . $section->getPaddingBottom() . 'px;';
            }
			
			$html.= '"';
			
			if ($section->getParallax()) {
                $html.= ' data-stellar-vertical-offset="20" data-stellar-background-ratio="0.6"';
            }

        }
		
        return $html;
	}
	
	public function convertString($theme){
		$themeName = str_replace('_',' ',$theme);
		return ucfirst($themeName);
	}
	
	/* Import Promotion Banners */
	public function importPromoBanner(){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home']['promo_banner']['item'])){
			foreach($parsedArray['home']['promo_banner']['item'] as $banner){
				if(is_array($banner)){
					$identifier = $banner['identifier'];
					$bannerData = $banner;
				}else{
					$identifier = $parsedArray['home']['promo_banner']['item']['identifier'];
					$bannerData = $parsedArray['home']['promo_banner']['item'];
				}
				
				$banners = $this->_objectManager->create('MGS\Promobanners\Model\Promobanners')
					->getCollection()
					->addFieldToFilter('identifier', $identifier);
				if (count($banners) > 0){
					foreach ($banners as $_banner){
						$_banner->delete();
					}
				}
				
				$this->_objectManager->create('MGS\Promobanners\Model\Promobanners')->setData($bannerData)->save();
				
			}
		}
		return;
	}
	
	public function _importSetting(){
		/* Import Theme Setting */
		$this->imporSetting('theme_setting', 'mgstheme');
		
		/* Import Color Setting */
		$this->imporSetting('color_setting', 'color');
		
		/* Import Panel Setting */
		//$this->imporSetting('panel_setting', 'mpanel');
		
		return;
	}
	
	public function imporSetting($xmlNode, $section){
		$parsedArray = $this->_xmlArray;
		if(isset($parsedArray['home'][$xmlNode])){
			$website = $this->getRequest()->getParam('website');
			$store = $this->getRequest()->getParam('store');
			$groups = [];
			if(count($parsedArray['home'][$xmlNode])>0){
				foreach($parsedArray['home'][$xmlNode] as $groupName=>$_group){
					$fields = [];
					foreach($_group as $field=>$value){
						//if($value!=''){
							$fields[$field] = ['value'=>$value];
						//}
					}
					
					$groups[$groupName] = [
						'fields' => $fields
					];
				}
			}
			
			$configData = [
				'section' => $section,
				'website' => $website,
				'store' => $store,
				'groups' => $groups
			];

			/** @var \Magento\Config\Model\Config $configModel  */
			$configModel = $this->_configFactory->create(['data' => $configData]);
			$configModel->save();
		}
		return;
	}
	
	/**
     * Custom save logic for section
     *
     * @return void
     */
    protected function _saveSection()
    {
        $method = '_save' . $this->_string->upperCaseWords('design', '_', '');
        if (method_exists($this, $method)) {
            $this->{$method}();
        }
    }
}

<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\Mpanel\Controller\Index;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
class Confirm extends \Magento\Framework\App\Action\Action
{
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    protected $_sectionFactory;
    protected $_childsFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context, 
		\Magento\Framework\View\Element\Context $urlContext,
		CustomerSession $customerSession,
		\MGS\Mpanel\Model\ResourceModel\Section\CollectionFactory $sectionFactory,
		\MGS\Mpanel\Model\ResourceModel\Childs\CollectionFactory $childsFactory
	){
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		$this->customerSession = $customerSession;
		parent::__construct($context);
		
		$this->_sectionFactory = $sectionFactory;
		$this->_childsFactory = $childsFactory;
	}
	
	public function urlDecode($url)
    {
        $url = base64_decode(strtr($url, '-_,', '+/='));
        return $this->_urlBuilder->sessionUrlVar($url);
    }
	
	public function getSections($storeId, $pageId, $page){
		$sectionCollection = $this->_sectionFactory->create();
		$sectionCollection->addFieldToFilter('store_id', $storeId)->setOrder('block_position', 'ASC');
		
		if($page=='cms_index_index'){
			$sectionCollection->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
		}else{
			$sectionCollection->addFieldToFilter('page_id', $pageId);
		}

		return $sectionCollection;
	}
	
	public function getBlocks($storeId, $blockName, $pageId, $page){
		$blocks = $this->_childsFactory->create()
				->addFieldToFilter('block_name', $blockName)
				->addFieldToFilter('store_id', $storeId)
				->setOrder('position', 'ASC');
				
		if($page=='cms_index_index'){
			$blocks->getSelect()->where('(main_table.page_id='.$pageId.') or (main_table.page_id IS NULL)');
		}else{
			$blocks->addFieldToFilter('page_id', $pageId);
		}	

		return $blocks;
	}
	
    public function execute()
    {
		if(($this->customerSession->getUsePanel() == 1) && ($referer = $this->getRequest()->getParam('referrer')) && ($pageId = $this->getRequest()->getParam('page_id')) && ($storeId = $this->getRequest()->getParam('store_id')) && ($page = $this->getRequest()->getParam('page'))){
			$sections = $this->getSections($storeId, $pageId, $page);
			
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
										
										$blocks = $this->getBlocks($storeId, $_section->getName().'-'.$key, $pageId, $page);
										
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
									
									$blocks = $this->getBlocks($storeId, $_section->getName().'-0', $pageId, $page);
									
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
				try {
					$cmsPageModel->save();
					$this->messageManager->addSuccess(__('You saved the page.'));
					
				} catch (LocalizedException $e) {
					$this->messageManager->addError($e->getMessage());
				} catch (\Exception $e) {
					$this->messageManager->addException($e, __('Something went wrong while saving the page.'));
				}
			}else{
				$this->messageManager->addError(__('Have no content to import'));
			}
			
			$url = $this->urlDecode($referer);
		}else{
			$url = $this->_redirect->getRefererUrl();
		}
		
		$resultRedirect = $this->resultRedirectFactory->create();
		$resultRedirect->setUrl($url);
		return $resultRedirect;
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
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
}

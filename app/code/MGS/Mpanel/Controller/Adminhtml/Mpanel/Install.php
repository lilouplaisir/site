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
class Install extends \MGS\Mpanel\Controller\Adminhtml\Mpanel
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
	
	protected $_themeHelper;
	
	/**
     * @param Action\Context $context
     * @param PostDataProcessor $dataProcessor
     */
    public function __construct(
		Action\Context $context,
		\Magento\Config\Model\Config\Factory $configFactory,
		\Magento\Framework\Filesystem $filesystem,
		\Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Stdlib\StringUtils $string,
		\MGS\Mpanel\Helper\Data $_themeHelper
	){
        parent::__construct($context);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
		$this->_themeHelper = $_themeHelper;
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if($theme = $this->getRequest()->getParam('theme')){
			$dir = $this->_filesystem->getDirectoryRead(DirectoryList::APP)->getAbsolutePath('code/MGS/Mpanel/data/themes/'.$theme);
			$staticBlocksFile = $dir.'/install.xml';

			$section = 'design';
			$website = $this->getRequest()->getParam('website');
			$store = $this->getRequest()->getParam('store');
			
			$themePath = 'Mgs/'.$theme;
			
			$themeModel = $this->getModel()
				->getCollection()
				->addFieldToFilter('theme_path', $themePath)
				->getFirstItem();
			
			if($themeModel->getThemeId()){
				$groups = [
					'theme'=> [
						'fields' => [
							'theme_id' => [
								'value' => $themeModel->getThemeId()
							]
						]
					]
				];

				$configData = [
					'section' => $section,
					'website' => $website,
					'store' => $store,
					'groups' => $groups
				];
				
				//echo '<pre>'; print_r($configData); die();
				/** @var \Magento\Config\Model\Config $configModel  */
				$configModel = $this->_configFactory->create(['data' => $configData]);
				try {
					$configModel->save();
					$this->messageManager->addSuccess(__('%1 theme was successfully installed.', $this->convertString($theme)));
					
					if (is_readable($staticBlocksFile))
					{
						$parsedArray = $this->_parser->load($staticBlocksFile)->xmlToArray();
						if(isset($parsedArray['install']['static_block']['item']) && (count($parsedArray['install']['static_block']['item'])>0)){
							foreach($parsedArray['install']['static_block']['item'] as $staticBlock){
								if(is_array($staticBlock)){
									$identifier = $staticBlock['identifier'];
									$staticBlockData = $staticBlock;
								}else{
									$identifier = $parsedArray['install']['static_block']['item']['identifier'];
									$staticBlockData = $parsedArray['install']['static_block']['item'];
								}
								
								$staticBlocksCollection = $this->_objectManager->create('Magento\Cms\Model\Block')
									->getCollection()
									->addFieldToFilter('identifier', $identifier)
									->load();
								if (count($staticBlocksCollection) > 0){
									foreach ($staticBlocksCollection as $_item){
										$_item->delete();
									}
								}
								
								$this->_objectManager->create('Magento\Cms\Model\Block')->setData($staticBlockData)->setIsActive(1)->setStores(array(0))->save();
								
							}
						}
						
						if(isset($parsedArray['install']['cms_page']['item']) && (count($parsedArray['install']['cms_page']['item'])>0)){

							foreach($parsedArray['install']['cms_page']['item'] as $cmsPage){
								if(is_array($cmsPage)){
									$identifier = $cmsPage['identifier'];
									$cmsPageData = $cmsPage;
								}else{
									$identifier = $parsedArray['install']['cms_page']['item']['identifier'];
									$cmsPageData = $parsedArray['install']['cms_page']['item'];
								}
								
								$cmsPageCollection = $this->_objectManager->create('Magento\Cms\Model\Page')
									->getCollection()
									->addFieldToFilter('identifier', $identifier)
									->load();
								
								if (count($cmsPageCollection) > 0){
									foreach ($cmsPageCollection as $_item){
										$_item->delete();
									}
								}
								
								$this->_objectManager->create('Magento\Cms\Model\Page')->setData($cmsPageData)->setIsActive(1)->setStores(array(0))->save();
							}
						}
						
						/* Import Theme Setting And Color Setting*/
						$this->_importSetting($parsedArray);
						
					}else{
						$this->messageManager->addError(__('Cannot import static blocks and cms pages.'));
					}
					
					
				}catch (\Exception $e) {
					// display error message
					$this->messageManager->addError($e->getMessage());
				}
				
			}else{
				$this->messageManager->addError(__('This theme no longer exists.'));
			}
			$this->_themeHelper->generateCssForAll();
		}else{
			$this->messageManager->addError(__('This theme no longer exists.'));
		}
		$this->_redirect($this->_redirect->getRefererUrl());
		return;
    }
	
	public function convertString($theme){
		$themeName = str_replace('_',' ',$theme);
		return ucfirst($themeName);
	}
	
	public function getModel(){
		return $this->_objectManager->create('Magento\Theme\Model\Theme');
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
	
	public function _importSetting($parsedArray){
		/* Import Theme Setting */
		$this->imporSetting($parsedArray, 'theme_setting', 'mgstheme');
		
		/* Import Panel Setting */
		$this->imporSetting($parsedArray, 'panel_setting', 'mpanel');
		
		return;
	}
	
	public function imporSetting($parsedArray, $xmlNode, $section){
		if(isset($parsedArray['install'][$xmlNode])){
			$website = $this->getRequest()->getParam('website');
			$store = $this->getRequest()->getParam('store');
			$groups = [];
			if(count($parsedArray['install'][$xmlNode])>0){
				foreach($parsedArray['install'][$xmlNode] as $groupName=>$_group){
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
}

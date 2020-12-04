<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MGS\ClaueTheme\Controller\Adminhtml\ClaueTheme;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Exception\LocalizedException;
class Import extends \MGS\Mpanel\Controller\Adminhtml\Mpanel\Import
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
		\MGS\Mpanel\Model\ResourceModel\Childs\CollectionFactory $childsFactory,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter
	){
        parent::__construct($context, $configFactory, $filesystem, $parser, $storeManager, $string, $_themeHelper, $urlContext, $sectionFactory, $childsFactory);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->_parser = $parser;
		$this->_storeManager = $storeManager;
		$this->_themeHelper = $_themeHelper;
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		$this->_sectionFactory = $sectionFactory;
		$this->_childsFactory = $childsFactory;
		$this->configWriter = $configWriter;
    }
	
	public function isLocalhost() {
        $whitelist = array(
            '127.0.0.1',
			'localhost',
            '::1'
        );
        
        return in_array($_SERVER['REMOTE_ADDR'], $whitelist);
    }
	
    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if(($this->_theme = $this->getRequest()->getParam('theme')) && ($this->_home = $this->getRequest()->getParam('home'))){
			if(($this->_theme=='claue' || $this->_theme=='claue_rtl') && !$this->isLocalhost()){
				$activeKey = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('active_theme/activate/claue');
				if($activeKey==''){
					$this->messageManager->addError(__('Please activate the theme first.'));
					$this->_redirect($this->_redirect->getRefererUrl());
					return;
				}else{
					$keyValue = trim($activeKey);
					$baseUrl = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('web/unsecure/base_url');
					
					$domain = str_replace('http://','',$baseUrl);
					$domain = str_replace('https://','',$domain);
					$domain = trim(preg_replace('/^.*?\\/\\/(.*)?\\//', '$1', $domain));
					if(strpos($domain, "/")){
						$domain = substr($domain, 0, strpos($domain, "/"));
					}
					$magentoVersion =  $this->_objectManager->get('Magento\Framework\App\ProductMetadataInterface')->getVersion();
					
					$ch = curl_init();
					curl_setopt($ch, CURLOPT_URL, "https://www.magesolution.com/licensekey/index/activate/item/20155150/theme/Claue/key/$keyValue/domain/$domain/version/$magentoVersion");
					curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
					curl_setopt($ch, CURLOPT_USERAGENT, 'ACTIVATE-THEMEFOREST-THEME');

					$result = curl_exec($ch);
					curl_close($ch);
					if($result!='Activated'){
						$this->configWriter->save('active_theme/activate/claue', NULL);
						$this->messageManager->addError(__('The theme has not been activated or your purchase code has been used for another domain.'));
						$this->_redirect($this->_redirect->getRefererUrl());
						return;
					}
					
				}
			}
			
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
}

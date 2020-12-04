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
class Install extends \MGS\Mpanel\Controller\Adminhtml\Mpanel\Install
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
		\MGS\Mpanel\Helper\Data $_themeHelper,
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter
	){
        parent::__construct($context, $configFactory, $filesystem, $parser, $string, $_themeHelper);
		$this->_configFactory = $configFactory;
        $this->_string = $string;
		$this->_filesystem = $filesystem;
		$this->configWriter = $configWriter;
		$this->_parser = $parser;
		$this->_themeHelper = $_themeHelper;
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
        if($theme = $this->getRequest()->getParam('theme')){
			if(($theme=='claue' || $theme=='claue_rtl') && !$this->isLocalhost()){
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
	
	
	public function getModel(){
		return $this->_objectManager->create('Magento\Theme\Model\Theme');
	}
}

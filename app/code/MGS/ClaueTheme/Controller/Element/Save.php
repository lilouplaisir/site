<?php
/**
 *
 * Copyright Â© 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
 
namespace MGS\ClaueTheme\Controller\Element;

use Magento\Framework\Controller\ResultFactory;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
class Save extends \Magento\Framework\App\Action\Action
{
	protected $_storeManager;
	
	/**
     * Url Builder
     *
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
	
	protected $_filesystem;
	
	protected $_file;

    /**
     * File Uploader factory
     *
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploaderFactory;

	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		CustomerSession $customerSession,
		\Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
		\Magento\Framework\Filesystem\Driver\File $file,
		\Magento\Framework\View\Element\Context $urlContext
	)     
	{
		$this->_storeManager = $storeManager;
		$this->customerSession = $customerSession;
		$this->_urlBuilder = $urlContext->getUrlBuilder();
		$this->_filesystem = $filesystem;
        $this->_fileUploaderFactory = $fileUploaderFactory;
		$this->_file = $file;
		parent::__construct($context);
	}
	
	public function getModel($model){
		return $this->_objectManager->create($model);
	}
	
    public function execute()
    {
		if($this->customerSession->getUsePanel() == 1){
			$data = $this->getRequest()->getPostValue();
			//echo '<pre>'; print_r($data); die();
			switch ($data['type']) {
				/* Static content Block */
				case "static":
					$this->removePanelImages('panel',$data);
					$content = $data['content'];
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Text content block. Please wait to reload page.');
					break;
					
				case "owl_banner":
					$this->removePanelImages('slider',$data);
					if(isset($data['setting']['images']) && (count($data['setting']['images'])>0)){
						$dataInit = ['fullscreen', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'loop', 'parallax', 'fullscreen', 'fullheight', 'navtemple'];
						
						$data = $this->reInitData($data, $dataInit);
						$speed = '';
						if($data['setting']['speed']){
							$speed = $data['setting']['speed'];
						}
                        $dot = $data['setting']['pagination'];
						if($data['setting']['navtemple'] && $data['setting']['navtemple'] == 4){
							$dot = 0;
						}
                        
                        $rmimage = $data['setting']['removeimg'];
						$images = $data['setting']['imgname'];
						$links = $data['setting']['links'];
						$customclass = $data['setting']['ctclass'];
						$html = $data['setting']['html'];
                        
                        foreach ($rmimage as $key => $value) {
                            if($value == "remove"){
                                $images[$key] = "";
                            }
                        }
                        
                        foreach ($images as $key => $value) {
                            if($value == ""){
                                unset($images[$key]);
                                unset($links[$key]);
                                unset($customclass[$key]);
                                unset($html[$key]);
                            }
                        }
						$str_images = implode(',',$images);
						$str_links = implode(',',$links);
						$str_customclass = implode(',',$customclass);
						$str_html = implode('<separator_html>',$html);
                        $str_html = htmlentities($str_html);
						$sliderContent = 'images="'.$str_images.'" links="'.$str_links.'" customclass="'.$str_customclass.'" html="'.$str_html.'"';
                        
						$content = '{{block class="MGS\Mpanel\Block\Widget\OwlCarousel" '.$sliderContent.' fullscreen="'.$data['setting']['fullscreen'].'" autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" offset="'.$data['setting']['offset'].'" fullheight="'.$data['setting']['fullheight'].'" pagination="'.$dot.'" loop="'.$data['setting']['loop'].'" speed="'.$speed.'" navtemple="'.$data['setting']['navtemple'].'" effect="'.$data['setting']['effect'].'" template="widget/owl_slider.phtml"}}';
						
						$data['block_content'] = $content;
						$result['message'] = 'success';
						$sessionMessage = __('You saved the OWL Carousel Slider block. Please wait to reload page.');
					}else{
						$this->messageManager->addError(__('You have not add any images to slider.'));
						$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
						$resultRedirect->setUrl($this->_redirect->getRefererUrl());
						return $resultRedirect;
					}
					break;
					
				/* New Products Block */
				case "new_products":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore', 'numbercol', 'percol'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					if($data['setting']['use_tabs']){
						$template = 'products/new/category-tabs.phtml';
					}else{
						$template = 'products/new/'.$data['setting']['template'];
					}
					
					$content = '{{block class="MGS\Mpanel\Block\Products\NewProducts" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['template']=='list.phtml'){
						$content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
					}
					
					if($data['setting']['template']=='grid.phtml'){
						$content .= ' perrow="'.$data['setting']['perrow'].'"';
					}
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the New Products block. Please wait to reload page.');
					break;
					
				/* Attribute Products Block */
				case "attribute_products":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore', 'numbercol', 'percol'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					if($data['setting']['use_tabs']){
						$template = 'products/attribute/category-tabs.phtml';
					}else{
						$template = 'products/attribute/'.$data['setting']['template'];
					}
					
					$content = '{{block class="MGS\Mpanel\Block\Products\Attributes" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" attribute="'.$data['setting']['attribute'].'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['template']=='list.phtml'){
						$content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
					}
					
					if($data['setting']['template']=='grid.phtml'){
						$content .= ' perrow="'.$data['setting']['perrow'].'"';
					}
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved Products by Attribute block. Please wait to reload page.');
					break;
				
				/* Sale Products Block */
				case "sale":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore', 'numbercol', 'percol'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					if($data['setting']['use_tabs']){
						$template = 'products/sale/category-tabs.phtml';
					}else{
						$template = 'products/sale/'.$data['setting']['template'];
					}
					
					$content = '{{block class="MGS\Mpanel\Block\Products\Sale" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['template']=='list.phtml'){
						$content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
					}
					
					if($data['setting']['template']=='grid.phtml'){
						$content .= ' perrow="'.$data['setting']['perrow'].'"';
					}
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Sale Products block. Please wait to reload page.');
					break;
				
				/* Top Rate Products Block */
				case "rate":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					if($data['setting']['use_tabs']){
						$template = 'products/rate/category-tabs.phtml';
					}else{
						$template = 'products/rate/'.$data['setting']['template'];
					}
					
					$content = '{{block class="MGS\Mpanel\Block\Products\Rate" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['template']=='list.phtml'){
						$content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
					}
					
					if($data['setting']['template']=='grid.phtml'){
						$content .= ' perrow="'.$data['setting']['perrow'].'"';
					}
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Top Rate Products block. Please wait to reload page.');
					break;
				
				/* Category Products Block */
				case "category_products":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					if($data['setting']['use_tabs']){
                        if($data['setting']['template']=='grid.phtml'){
                            $template = 'products/category_products/category-tabs.phtml';
                        }elseif($data['setting']['template']=='grid_v2.phtml') {
                            $template = 'products/category_products/category-tabs-v2.phtml';
                        }else {
                            $template = 'products/category_products/category-tabs-v3.phtml';
                        }
					}else{
						$template = 'products/category_products/'.$data['setting']['template'];
					}
					
					$content = '{{block class="MGS\Mpanel\Block\Products\Category" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['template']=='list.phtml'){
						$content .= ' numbercol="'.$data['setting']['numbercol'].'" percol="'.$data['setting']['percol'].'"';
					}
					
					if($data['setting']['template']=='grid.phtml'){
						$content .= ' perrow="'.$data['setting']['perrow'].'"';
					}
					if($data['setting']['template']!='masonry.phtml'){
                        if($data['setting']['use_slider']){
                            $content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
                        }
                    }
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Category Products block. Please wait to reload page.');
					break;
				
				/* Deals Block */
				case "deals":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore'];
					$data = $this->reInitData($data, $dataInit);
					$categories = '';
					if(isset($data['setting']['category_id'])){
						$categories = implode(',',$data['setting']['category_id']);
					}
					if($data['setting']['template']=='list.phtml'){
						$data['setting']['use_tabs'] = 0;
					}
					
					$template = 'products/deals/'.$data['setting']['template'];
					
					$content = '{{block class="MGS\Mpanel\Block\Products\Deals" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" perrow="'.$data['setting']['perrow'].'" ratio="'.$data['setting']['ratio'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" template="'.$template.'"';
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Deals block. Please wait to reload page.');
					break;
				
				/* Product Tabs Block */
				case "tabs":
					if(isset($data['setting']['tabs']) && count($data['setting']['tabs'])>0){
						$tabs = $data['setting']['tabs'];
						$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'use_tabs', 'loadmore'];
						$data = $this->reInitData($data, $dataInit);
						$categories = '';
						if(isset($data['setting']['category_id'])){
							$categories = implode(',',$data['setting']['category_id']);
						}
						
						usort($tabs, function ($item1, $item2) {
							if ($item1['position'] == $item2['position']) return 0;
							return $item1['position'] < $item2['position'] ? -1 : 1;
						});
						
						$tabType = $tabLabel = [];
						foreach($tabs as $tab){
							$tabType[] = $tab['value'];
							$tabLabel[] = $tab['label'];
						}
						$tabs = implode(',',$tabType);
						$labels = implode(',',$tabLabel);
						
						$content = '{{block class="MGS\Mpanel\Block\Products\Tabs" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" perrow="'.$data['setting']['perrow'].'" category_ids="'.$categories.'" use_slider="'.$data['setting']['use_slider'].'" loadmore="'.$data['setting']['loadmore'].'" ratio="'.$data['setting']['ratio'].'" template="products/tabs/grid.phtml" tabs="'.$tabs.'"';
						
						if($data['setting']['use_slider']){
							$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
						}
						
						$content .='labels="'.$labels.'"';
						
						$content .= '}}';
						
						$data['block_content'] = $content;
						$result['message'] = 'success';
						$sessionMessage = __('You saved the Product Tabs block. Please wait to reload page.');
					}else{
						$this->messageManager->addError(__('You have not add any tabs.'));
						$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
						$resultRedirect->setUrl($this->_redirect->getRefererUrl());
						return $resultRedirect;
					}
					break;
				
				/* Single Product Block */
				case "special_product":
					$dataInit = ['product_name', 'product_price', 'product_rating', 'product_categories', 'product_description', 'product_addcart', 'landing_mode'];
					$data = $this->reInitData($data, $dataInit);
					
					$content = '{{block class="MGS\Mpanel\Block\Products\SpecialProduct" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" product_id="'.$data['setting']['product_id'].'" ratio="'.$data['setting']['ratio'].'" product_name="'.$data['setting']['product_name'].'" product_price="'.$data['setting']['product_price'].'" product_rating="'.$data['setting']['product_rating'].'" product_categories="'.$data['setting']['product_categories'].'" product_description="'.$data['setting']['product_description'].'" product_addcart="'.$data['setting']['product_addcart'].'" landing_mode="'.$data['setting']['landing_mode'].'"';
					
					if($data['setting']['product_description'] && isset($data['setting']['characters_count']) && ($data['setting']['characters_count']!='')){
						$content .= ' characters_count="'.$data['setting']['characters_count'].'"';
					}
					
					$content .= ' template="products/single/default.phtml"}}';

					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Single Product block. Please wait to reload page.');
					break;
				
				/* Megamenu Block */
				case "megamenu":
					$menu = $data['setting']['menu'];
					$menu = explode(':', $menu);
					$menuType = 'vertical';
					$menuBlock = '\Vertical';
					if($menu[1]==1){
						$menuType = 'horizontal';
						$menuBlock = '\Horizontal';
					}
					$content = '{{block class="MGS\Mmegamenu\Block'.$menuBlock.'" menu_id="'.$menu[0].'" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" template="panel/'.$menuType.'.phtml"}}';

					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Megamenu block. Please wait to reload page.');
					break;
					
				/* Brand Block */
				case "brands":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination'];
					$data = $this->reInitData($data, $dataInit);
					$content = '{{block class="MGS\Brand\Block\Widget\Home" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" perrow="'.$data['setting']['perrow'].'" use_slider="'.$data['setting']['use_slider'].'" brand_by="'.$data['setting']['brand_by'].'" template="widget/home.phtml"';
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Brand block. Please wait to reload page.');
					break;
				
				/* Promotion Banner Block */
				case "promo_banner":
					if((!isset($data['chooser'])) || ($data['chooser']=='new')){
						$result = $this->savePromoBanner($data['banner']);
					}else{
						$result['message'] = 'success';
						$result['data'] = $data['banner']['choose_identifier'];
					}
					$data['block_content'] = '{{widget type="MGS\Promobanners\Block\Widget\Banner" banner_id="'.$result['data'].'" template="banner.phtml"}}';
					$data['setting']['banner_id'] = $result['data'];
					unset($data['chooser'], $data['banner']);
					$sessionMessage = __('You saved the Promotion Banner block. Please wait to reload page.');
					break;
				
				/* Latest Posts Block */
				case "latest_posts":
					$dataInit = ['number_row', 'show_short_content', 'view_as', 'show_thumbnail', 'autoplay', 'stop_auto', 'navigation', 'pagination'];
					$data = $this->reInitData($data, $dataInit);
					$cates = implode(',',$data['setting']['post_category']);
					$content = '{{widget type="MGS\Blog\Block\Widget\Latest" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" post_category="'.$cates.'" number_of_posts="'.$data['setting']['number_of_posts'].'" items="'.$data['setting']['items'].'" show_thumbnail="'.$data['setting']['show_thumbnail'].'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" template="widget/'.$data['setting']['template'].'"';
					
					if($data['setting']['show_short_content']){
						$content .= ' show_short_content="1" limit_characters_short_content="'.$data['setting']['limit_characters_short_content'].'"';
					}
					
					if($data['setting']['view_as']){
						$content .= ' view_as="owl_carousel" autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Latest Posts block. Please wait to reload page.');
					break;
				
				/* Portfolio Block */
				case "portfolio":
					$dataInit = ['show_categories', 'show_thumbnail', 'show_content', 'use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination'];
					$data = $this->reInitData($data, $dataInit);
					$categories = implode(',',$data['setting']['categories']);
					$content = '{{block class="MGS\Portfolio\Block\Widget" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" category_ids="'.$categories.'" limit="'.$data['setting']['limit'].'" perrow="'.$data['setting']['perrow'].'" show_categories="'.$data['setting']['show_categories'].'" show_content="'.$data['setting']['show_content'].'" show_thumbnail="'.$data['setting']['show_thumbnail'].'" use_slider="'.$data['setting']['use_slider'].'" template="widget/'.$data['setting']['template'].'"';
					
					if($data['setting']['show_content']){
						$content .= ' character_count="'.$data['setting']['character_count'].'"';
					}
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Portfolio block. Please wait to reload page.');
					break;
				
				/* Testimonial Block */
				case "testimonial":
					$dataInit = ['show_avatar', 'use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination'];
					$data = $this->reInitData($data, $dataInit);
					$content = '{{block class="MGS\Testimonial\Block\Testimonial" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" testimonials_count="'.$data['setting']['limit'].'" perrow="'.$data['setting']['perrow'].'" show_avatar="'.$data['setting']['show_avatar'].'" use_slider="'.$data['setting']['use_slider'].'" template="'.$data['setting']['template'].'"';
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					
					$content .= '}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Testimonial block. Please wait to reload page.');
					break;
				
				/* Facebook Fan Box Block */
				case "facebook":
					$dataInit = ['hide_cover', 'show_facepile', 'hide_call_to', 'small_header', 'fit_inside', 'show_posts'];
					$data = $this->reInitData($data, $dataInit);
					$tabs = implode(',',$data['setting']['facebook_tabs']);
					$content = '{{block class="MGS\Social\Block\Panel\Widget" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" page_url="'.$data['setting']['page_url'].'" width="'.$data['setting']['width'].'" height="'.$data['setting']['height'].'" facebook_tabs="'.$tabs.'" hide_cover="'.$data['setting']['hide_cover'].'" show_facepile="'.$data['setting']['show_facepile'].'" show_posts="'.$data['setting']['show_posts'].'" small_header="'.$data['setting']['small_header'].'" fit_inside="'.$data['setting']['fit_inside'].'" template="widget/fanbox.phtml"}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the facebook Fanbox block. Please wait to reload page.');
					break;
				
				/* Twitter Feed Block */
				case "twitter":
					$dataInit = ['use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination'];
					$data = $this->reInitData($data, $dataInit);
					
					$content = '{{block class="MGS\Social\Block\Panel\Widget" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" use_slider="'.$data['setting']['use_slider'].'" feed_count="'.$data['setting']['feed_count'].'"';
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'"';
					}
					
					$content .=  ' template="widget/twitter_feed.phtml"}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Twitter Feed block. Please wait to reload page.');
					break;
				
				/* Instagram Block */
				case "instagram":
					$dataInit = ['link', 'like', 'use_slider', 'autoplay', 'stop_auto', 'navigation', 'pagination', 'comment'];
					$data = $this->reInitData($data, $dataInit);
					$content = '{{block class="MGS\Social\Block\Panel\Widget" instagram_hashtag="'.$this->encodeHtml($data['setting']['hastag']).'" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" limit="'.$data['setting']['limit'].'" resolution="'.$data['setting']['resolution'].'" link="'.$data['setting']['link'].'" perrow="'.$data['setting']['perrow'].'" use_slider="'.$data['setting']['use_slider'].'" like="'.$data['setting']['like'].'" comment="'.$data['setting']['comment'].'"';
					
					if($data['setting']['use_slider']){
						$content .= ' autoplay="'.$data['setting']['autoplay'].'" stop_auto="'.$data['setting']['stop_auto'].'" navigation="'.$data['setting']['navigation'].'" pagination="'.$data['setting']['pagination'].'" number_row="'.$data['setting']['number_row'].'"';
					}
					$content .=  ' template="widget/instagram.phtml"}}';
					
					$data['block_content'] = $content;
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Instagram block. Please wait to reload page.');
					break;
				/* Lookbook Block */
				case "lookbook":
					$data['block_content'] = '{{widget type="MGS\Lookbook\Block\Widget\Lookbook" lookbook_id="'.$data['setting']['lookbook_id'].'" template="MGS_Lookbook::widget/lookbook.phtml"}}';
					
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Lookbook block. Please wait to reload page.');
					break;
				
				/* Lookbook Block */
				case "lookbook_slider":
					$data['block_content'] = '{{widget type="MGS\Lookbook\Block\Widget\Slider" slider_id="'.$data['setting']['slide_id'].'" template="MGS_Lookbook::widget/slider.phtml"}}';
					
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Lookbook Slider block. Please wait to reload page.');
					break;
				/* Instagram Shop Block */
				case "snapppt":
					$content = '{{block class="MGS\Social\Block\Panel\Widget" mgs_panel_title="'.$this->encodeHtml($data['setting']['title']).'" mgs_panel_note="'.$this->encodeHtml($data['setting']['additional_content']).'" template="widget/snapppt_shop.phtml"}}';
					
					$data['block_content'] = $content;
					
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Snapppt shop block. Please wait to reload page.');
					break;
				/* Video Banner Block */
				case "video_banner":
                    $dataInit = ['autoplay', 'controls', 'loop', 'muted', 'preload'];
					$data = $this->reInitData($data, $dataInit);
                    
                    $htmltoString = "";
                    if($data['setting']['html_content']){
                        $htmltoString = htmlentities($data['setting']['html_content']);
                    }
                    
					$content = '{{block class="Magento\Framework\View\Element\Template" autoplay="'.$data['setting']['autoplay'].'" controls="'.$data['setting']['controls'].'" loop="'.$data['setting']['loop'].'" poster_image="'.$data['setting']['poster_image'].'" video_background="'.$data['setting']['video_background'].'" muted="'.$data['setting']['muted'].'" video_type="'.$data['setting']['video_type'].'" video_url="'.$data['setting']['video_url'].'" preload="'.$data['setting']['preload'].'" height="'.$data['setting']['height'].'" html_content="'.$htmltoString.'" template="MGS_Mpanel::widget/video_banner.phtml"}}';
					

                    $data['block_content'] = $content;
                    
					$result['message'] = 'success';
					$sessionMessage = __('You saved the Video Banner block. Please wait to reload page.');
					break;
			}
			
			if($result['message']=='success'){
				$this->saveBlockData($data, $sessionMessage);
			}else{
				return $this->getMessageHtml('danger', $result['message'], false);
			}
		}
		else{
			$resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
			$resultRedirect->setUrl($this->_redirect->getRefererUrl());
			return $resultRedirect;
		}
    }
	
	/* Create Promotion Banner */
	public function savePromoBanner($data){
		if(isset($data['identifier'])){
			
			if(!isset($data['banner_id'])){
				$existBanners = $this->getModel('MGS\Promobanners\Model\Promobanners')
					->getCollection()
					->addFieldToFilter('identifier', $data['identifier']);
				
				if(count($existBanners)>0){
					$result['message'] = __('Identifier already exist. Please use other identifier');
					return $result;
				}
			}
			
			if(isset($_FILES['filename']['name']) && $_FILES['filename']['name'] != '') {
				try {
					$uploader = $this->_fileUploaderFactory->create(['fileId' => 'filename']);
					$uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
					$uploader->setAllowRenameFiles(true);
					$uploader->setFilesDispersion(true);
					
				} catch (\Exception $e) {
					$result['message'] = $e->getMessage();
					return $result;
				}
				$path = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('promobanners/');
				$uploader->save($path);
				$fileName = $uploader->getUploadedFileName();
				$data['filename'] = $fileName;
			}

			$model = $this->getModel('MGS\Promobanners\Model\Promobanners');
			$model->setData($data);
			
			if(isset($data['banner_id'])){
				$id = $data['banner_id'];
				unset($data['banner_id']);
				$model->setId($id);
			}
			
			try {
				// save the data
				$model->save();
				
				$result['message'] = 'success';
				$result['data'] = $model->getIdentifier();
				return $result;
			} catch (\Exception $e) {
				$result['message'] = $e->getMessage();
				return $result;
			}
		}
	}
	
	/* Save data to childs table */
	public function saveBlockData($data, $sessionMessage){
		$model = $this->getModel('MGS\Mpanel\Model\Childs');
		$data['setting'] = json_encode($data['setting']);
		if ($data['type'] == 'separator') {
			$data['col'] = 12;
		}
		
		if(!isset($data['child_id'])){
			$storeId = $this->_storeManager->getStore()->getId();
			$data['store_id'] = $storeId;
			$data['position'] = $this->getNewPositionOfChild($data['store_id'], $data['block_name']);
		}
		
		
		$model->setData($data);
		if(isset($data['child_id'])){
			$id = $data['child_id'];
			unset($data['child_id']);
			$model->setId($id);
		}
		try {
			// save the data
			$model->save();
			return $this->getMessageHtml('success', $sessionMessage, true);
		} catch (\Exception $e) {
			return $this->getMessageHtml('danger', $e->getMessage(), false);
		}
	}
	
	/* Set value 0 for not exist data */
	public function reInitData($data, $dataInit){
		foreach($dataInit as $item){
			if(!isset($data['setting'][$item])){
				$data['setting'][$item] = 0;
			}
		}
		return $data;
	}
	
	/* Get position of new block for sort */
	public function getNewPositionOfChild($storeId, $blockName){
		$child = $this->getModel('MGS\Mpanel\Model\Childs')
                ->getCollection()
                ->addFieldToFilter('store_id', $storeId)
                ->addFieldToFilter('block_name', $blockName)
                ->setOrder('position', 'DESC')
                ->getFirstItem();

        if ($child->getId()) {
            $position = (int) $child->getPosition() + 1;
        } else {
            $position = 1;
        }

        return $position;
	}
	
	/* Show message after save block */
	public function getMessageHtml($type, $message, $reload){
		$html = '<style type="text/css">
			.container {
				padding: 0px 15px;
				margin-top:60px;
			}
			.page.messages .message {
				padding: 15px;
				font-family: "Lato",arial,tahoma;
				font-size: 14px;
			}
			.page.messages .message-success {
				background-color: #dff0d8;
			}
			.page.messages .message-danger {
				background-color: #f2dede;
			}
		</style>';
		$html .= '<main class="page-main container">
			<div class="page messages"><div data-placeholder="messages"></div><div>
				<div class="messages">
					<div class="message-'.$type.' '.$type.' message" data-ui-id="message-'.$type.'">
						<div>'.$message.'</div>
					</div>
				</div>
			</div>
		</div></main>';
		
		if($reload){
			$html .= '<script type="text/javascript">window.parent.location.reload();</script>';
		}
		
		return $this->getResponse()->setBody($html);
	}
	
	public function removePanelImages($type,$data){
		if(isset($data['remove']) && (count($data['remove'])>0)){
			foreach($data['remove'] as $filename){
				$filePath = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA)->getAbsolutePath('wysiwyg/'.$type.'/') . $filename;
				if ($this->_file->isExists($filePath))  {
					$this->_file->deleteFile($filePath);
				}
			}
		}
	}
	
	public function encodeHtml($html){
		$result = str_replace("<","&lt;",$html);
		$result = str_replace(">","&gt;",$result);
		$result = str_replace('"','&#34;',$result);
		$result = str_replace("'","&#39;",$result);
		return $result;
	}
}
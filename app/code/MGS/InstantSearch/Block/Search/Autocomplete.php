<?php
namespace MGS\InstantSearch\Block\Search;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use MGS\InstantSearch\Helper\Data;

class Autocomplete extends Template
{
    const XML_PATH_PRODUCT_SORT_ORDER = 'instantsearch/additional_product/sort_order';
    const XML_PATH_CATEGORY_SORT_ORDER = 'instantsearch/additional_category/sort_order';
    const XML_PATH_CMS_PAGE_SORT_ORDER = 'instantsearch/additional_cms_page/sort_order';
    const XML_PATH_BLOG_SORT_ORDER = 'instantsearch/additional_blog/sort_order';
    /**
    * @var array|\Magento\Checkout\Block\Checkout\LayoutProcessorInterface []
    */
   protected $layoutProcessors;

   /**
     * @var QueryFactory
     */
    private $_queryFactory;

    /**
     * Catalog search data
     *
     * @var Data
     */
    protected $_catalogSearchData;


   /**
     * @var Data
     */
    protected $_inSearchHelper;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param QueryFactory $queryFactory
     * @param Data $inSearchHelper
     * @param array $layoutProcessors
     * @param array $data
     */
    public function __construct(
        Context $context,
        QueryFactory $queryFactory,
        Data $inSearchHelper,
        array $layoutProcessors = [],
        \Magento\CatalogSearch\Helper\Data $catalogSearchData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_queryFactory = $queryFactory;
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->layoutProcessors = $layoutProcessors;
        $this->_catalogSearchData = $catalogSearchData;
        $this->_inSearchHelper = $inSearchHelper;
    }

    /**
     * Retrieve search action url
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl("instantsearch/ajax/result");
    }
    /**
     *
     * @return string
     */
    public function getTextNoRsult()
    {   
        return $this->getDefaultProducts();
    }

    protected function getDefaultProducts() {
        $productHtml = '';
        $productHtml = 'SORRY, NO MATCH FOR YOUR SEARCHED ITEM <span id="search_query"></span>';
        $productHtml .= '<div class="_toptext"><span>If you were not able to find what you are looking for:</span>';
        $productHtml .= '<ul><li>Try more general words</li><li>Try a synonym</li><li>Apply filters</li></ul></div>';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $store = $objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore();
        $productCollection = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $collection = $productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->setPageSize(4);
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $collection->addAttributeToSort('entity_id','desc');

        if ($collection->count()) {
            $productHtml .= '<div class="content-heading">
                                <h4 class="title">
                                    <span id="block-related-heading" role="heading" aria-level="2">NEW ARRIVALS</span>
                                </h4>
                            </div>';
            $productHtml .= '<div class="products wrapper grid products-grid" id="product-wrapper">
                <ul class="products list items product-items">';
                foreach ($collection as $product) {
                    $productImageUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' .$product->getImage();
                    $brandname = $product->getResource()->getAttribute('mgs_brand')->getFrontend()->getValue($product);
                    $productUrl = $product->getProductUrl();
                    $productPrice = $this->getLayout()->getBlock('product.price.render.default')->render(
                                        'final_price',
                                        $product
                                    );
                    $productHtml .= '<li class="item product product-item"><div class="product-item-info">';
                    $productHtml .= '<div class="product photo product-item-photo"><a href="'.$productUrl.'">
					<div style="background-image: url(' .$productImageUrl. '); width: 100%; position: absolute; top: 50%; left: 0; transform: translateY(-50%); height: 100%; background-size: cover;"></div>
					</a></div>';
                    $productHtml .= '<div class="product details product-item-details">';
                    $productHtml .= '<div class="product brand_name product-item-name">'.$brandname.'</div>';
                    $productHtml .= '<div class="product name product-item-name"><strong><a class="product-item-link" href="'.$productUrl.'"></a>'.$product->getName().'</strong></div>'.$productPrice;
                    $productHtml .= '<div class="custom_addto_cart">
                                    <button type="button" title="Add to Bag" class="action tocart">
                                    <a href="'.$productUrl.'">Add to Bag</a>
                                    </button>
                                </div>';
                    $productHtml .= '</div></div></li>';
                }
                $productHtml .= '</ul></div>';
        }
        return $productHtml;
    }

    /**
     * Get category search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        return __("Category Search results for: '%1'", $this->_catalogSearchData->getEscapedQueryText());
    }

    public function getJsLayout()
    {
        foreach ($this->layoutProcessors as $processor) {
            $this->jsLayout = $processor->process($this->jsLayout);
        }
        $this->jsLayout['components']['instant_search_form']['config']['textNoResult'] = $this->getTextNoRsult();
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['product']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) ? 
            $this->_inSearchHelper->getConfig(self::XML_PATH_PRODUCT_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['category']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) ?
            $this->_inSearchHelper->getConfig(self::XML_PATH_CATEGORY_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
            ['page']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) ?
            $this->_inSearchHelper->getConfig(self::XML_PATH_CMS_PAGE_SORT_ORDER) : 0;
        $this->jsLayout['components']['instant_search_form']['children']['steps']['children']
        ['blog']['sortOrder'] = $this->_inSearchHelper->getConfig(self::XML_PATH_BLOG_SORT_ORDER) ?
        $this->_inSearchHelper->getConfig(self::XML_PATH_BLOG_SORT_ORDER) : 0;
        $this->jsLayout['components']['autocompleteDataProvider']['config']['url'] = $this->getSearchUrl();
        $this->jsLayout['components']['autocompleteBindEvents']['config']['url'] = $this->getUrl("instantsearch/result");
        return \Zend_Json::encode($this->jsLayout);
    }

    public function getInstantSearchConfig()
    {
        $responseData = [];
        $responseData['result']['product'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['category'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['page'] = array('data'=>[],'size'=>0, 'url'=>'');
        $responseData['result']['blog'] = array('data'=>[],'size'=>0, 'url'=>'');
        return \Zend_Json::encode($responseData);
    }
}

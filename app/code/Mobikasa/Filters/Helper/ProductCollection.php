<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */
namespace Mobikasa\Filters\Helper;
/**
 * Cache helper
 */
class ProductCollection extends \Magento\Framework\App\Helper\AbstractHelper {
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    protected $_objectManager = null;
    protected $_categoryFactory;
    protected $_category;
    protected $_productCollectionFactory;
    protected $eavConfig;

    /**
     * Constructor.
     * 
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct
    (   
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    )
    {   
        $this->jsonHelper      = $jsonHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->eavConfig = $eavConfig;
        $this->_categoryFactory = $categoryFactory;
    }
    public function getProductCollection($category_id)
    {    
        $cats = [];
        $productData = [];
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addCategoriesFilter(['in' => $category_id]);
        $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
        $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
        $baseUrl =  $this->_objectManager
                        ->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()
                        ->getBaseUrl();
        /*$request = $this->_objectManager->get('Magento\Framework\App\Request\Http');  
        $brand = $request->getParam('mgs_brand');
        $size = $request->getParam('size');
        $color = $request->getParam('color');
        $price = $request->getParam('price');*/
                       
        foreach ($collection as $product) 
        {     
            $cats[] = $product->getCategoryIds();
        }
        $all_cats = [];
        foreach ($cats as  $cat) {
            foreach ($cat as $key => $value) {
                if(!in_array($value, $all_cats, true)){
                    array_push($all_cats, $value);
                }
            }
        }
        $finalData = [];
        foreach ($all_cats as $val) {
            $categoryCollection = $this->_objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
            $categories = $categoryCollection->create()->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $val); 
            foreach ($categories->getData() as $k => $v) {
                if($v['children_count'] == 0){
                    $_categoryParent = $this->_categoryFactory->create()->load($v['parent_id']);
                    if($_categoryParent->getName() != 'Designers'){
                        $_category = $this->_categoryFactory->create()->load($v['entity_id']);
                        $finalData[$v['entity_id']] = $v['entity_id'];
                        $finalData[$v['entity_id']] = $_category->getName();
                    }
                }
            }
        }
        return $finalData;
    }
}

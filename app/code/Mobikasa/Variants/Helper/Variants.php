<?php
/**
 * @category    Ubertheme
 * @package     Ubertheme_UbDatamigration
 * @author      Ubertheme.com
 * @copyright   Copyright 2009-2016 Ubertheme
 */
namespace Mobikasa\Variants\Helper;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\CookieManagerInterface;

/**
 * Cache helper
 */
class Variants extends \Magento\Framework\App\Helper\AbstractHelper {

    const COOKIE_DURATION = 60; 

    /**
     * @var CookieManagerInterface
     */
    protected $cookieManager;
 
    /**
     * @var CookieMetadataFactory
     */
    protected $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $jsonHelper;
    protected $_objectManager = null;
    protected $_categoryFactory;
    protected $_category;
    protected $_productCollectionFactory;
    protected $eavConfig;
    protected $productRepository;

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
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    )
    {   
        $this->jsonHelper      = $jsonHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_objectManager = $objectManager;
        $this->eavConfig = $eavConfig;
        $this->_categoryFactory = $categoryFactory;
        $this->productRepository = $productRepository;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }
    public function getProductData($product_id)
    {   
        $_product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
        $productType = $_product->getTypeID();
        if($productType == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE)
        {   
           $baseUrl =  $this->_objectManager
                        ->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()
                        ->getBaseUrl();
            $repository = $this->_objectManager->create('Magento\Catalog\Model\ProductRepository');
            $product = $repository->getById($product_id);
            $data = $product->getTypeInstance()->getConfigurableOptions($product);

             $children = $product->getTypeInstance()->getUsedProducts($product);
             
             if($children){
               $data = []; 
               $optionsChild = [];
                foreach ($children as $childs){
                  if($childs->getColor()){
                    if(!in_array($childs->getColor(), $data, true)){
                        array_push($data, $childs->getColor());
                        $optionsChild[] = $childs->getData();
                    }
                  }
                }
             } 

              if($optionsChild){
                  $finalData = [];
                  foreach ($optionsChild as  $child) { 

                  $_kid = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($child['entity_id']);

                  $helperImport  = $this->_objectManager->get('\Magento\Catalog\Helper\Image');

                  $imageUrl = $helperImport->init($_kid, 'product_page_image_small')
                              ->setImageFile($_kid->getSmallImage()) // image,small_image,thumbnail
                              ->resize(120)
                              ->getUrl();
                 

                 
                  $getWebPUrl =  $this->_objectManager->create('Magefan\WebP\Api\GetWebPUrlInterface');
                  $createWebPImage =  $this->_objectManager->create('Magefan\WebP\Api\CreateWebPImageInterface');                   
                  $createWebPImage->execute($imageUrl);
                  $webpUrl = $getWebPUrl->execute($imageUrl);
                  $imageUrl = ($webpUrl) ? $webpUrl : $imageUrl;

		  $finalData[$child['color']]['color'][$_kid->getResource()->getAttribute('color')->getFrontend()->getValue($_kid)] = $imageUrl;

                  $childinfo = $this->_objectManager
                                  ->create('Magento\Catalog\Model\Product')
                                  ->load($child['entity_id']); 

                    $images = $childinfo->getMediaGalleryImages();
                    foreach($images as $_child){ 
                      $clildImage =  $_child->getUrl();
                        $createWebPImage->execute($clildImage);
                        $webpUrl = $getWebPUrl->execute($clildImage);
                        $clildImage = ($webpUrl) ? $webpUrl : $clildImage;
                        $finalData[$child['color']]['thumbnails'][] = $clildImage;
				 // $finalData[$child['color']]['thumbnails'][] = $_child->getUrl();
                      }
                    $productStockObj = $this->_objectManager->get('Magento\CatalogInventory\Api\StockRegistryInterface')->getStockItem($child['entity_id']);
                    $finalData[$child['color']]['stock_status'] = $productStockObj->getIsInStock();
                  }
              } 
             return $jasonData = $this->jsonHelper->jsonEncode($finalData); 
        } 
    }
    public function getPDPData($product_id){ 
      $product_coockie = 'Product_'.$product_id;
      $finalData = [];
      $coockieData = [];
      $_cockData = [];
      $colData = [];
      $jason_coockie_Data = [];
      
      $_product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($product_id);
      //$cookieValue = $this->cookieManager->getCookie($product_coockie);

      /*if($cookieValue){
        $jason_coockie_Data = $this->jsonHelper->jsonDecode($cookieValue);
      }*/ 

      
      /*if(isset($jason_coockie_Data['you_may_also_like']) && count($jason_coockie_Data['you_may_also_like']) > 0 || isset($jason_coockie_Data['complete_your_look']) && count($jason_coockie_Data['complete_your_look']) > 0) {

           $_cockData['you_may_also_like'] = $this->getCoockiesData($jason_coockie_Data['you_may_also_like']);
           $_cockData['complete_your_look'] = $this->getCoockiesData($jason_coockie_Data['complete_your_look']);

           return $_cockData;
          
      }else{ */
            $you_may_also_like_col = $_product->getResource()
                                ->getAttribute('you_may_also_like')
                                ->getFrontend()
                                ->getValue($_product);
            if($you_may_also_like_col){ //upsell products
                $_completeData = $this->getCollectionData($you_may_also_like_col);
                $finalData['you_may_also_like'] = $_completeData;
               // $coockieData['you_may_also_like'] = $_completeData;
            }//end upsell section

            $complete_ur_look_col = $_product->getResource()
                                ->getAttribute('complete_your_look')
                                ->getFrontend()
                                ->getValue($_product);
            if($complete_ur_look_col){ //related products
                $completeData = $this->getCollectionData($complete_ur_look_col);
                $finalData['complete_your_look'] = $completeData;
               // $coockieData['complete_your_look'] = $completeData;
            }//end related section
     // }

      /* Set the data in Coockie */
      /*foreach ($coockieData['you_may_also_like'] as $key => $value) {
        $_cockData['you_may_also_like'][] = $key;
      }
      foreach ($coockieData['complete_your_look'] as $keys => $values) {
        $_cockData['complete_your_look'][] = $keys;
      }
      $jasonData = $this->jsonHelper->jsonEncode($_cockData);
      $metadata = $this->cookieMetadataFactory
          ->createPublicCookieMetadata()
          ->setDuration(self::COOKIE_DURATION);
      $this->cookieManager
          ->setPublicCookie($product_coockie, $jasonData, $metadata);*/
      /* Completed Set the data in Coockie */  
      

      return $finalData;
    }

   

    public function getCollectionData($collection_id){
      $colData = [];
      $collection = $this->_productCollectionFactory->create();
      $collection->addAttributeToSelect('*');
      $collection->addCategoriesFilter(['in' => $collection_id]);
      $collection->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH);
      $collection->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
      $collection->setPageSize(4);
      $collection ->getSelect()->orderRand();

      $baseUrl =  $this->_objectManager
                        ->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()
                        ->getBaseUrl();
      $count = 0;
      $_url_with_color = '';
      $SpecialPrice = '';
      foreach ($collection as $product) 
      { 

        $colData[$product->getEntityId()]['sku'] = $product->getSku();
        $colData[$product->getEntityId()]['name'] = $product->getName();
        $colData[$product->getEntityId()]['designer'] = $product->getResource()
                                                                ->getAttribute('mgs_brand')
                                                                ->getFrontend()
                                                                ->getValue($product);

        if(!empty($product->getSmallImage()) && $product->getSmallImage() != 'no_selection' ){
            $productSmallImage = $this->_objectManager->create('Magento\Catalog\Helper\Image')
            ->init($product, 'product_page_image_small')
            ->setImageFile($product->getSmallImage()) // image,small_image,thumbnail
            ->resize(300)
            ->getUrl();

            $colData[$product->getEntityId()]['image'] = $productSmallImage;

            // $colData[$product->getEntityId()]['image'] = $baseUrl.'pub/media/catalog/product'.$product->getSmallImage();
        }else{
            $colData[$product->getEntityId()]['image'] = $baseUrl.'pub/media/image/product_thumb.jpg';
        }

        if(!empty($product->getHover()) && $product->getHover() != 'no_selection'){
            $productHoverImage = $this->_objectManager->create('Magento\Catalog\Helper\Image')
            ->init($product, 'product_page_image_hover')
            ->setImageFile($product->getHover()) // image,small_image,thumbnail
            ->resize(300)
            ->getUrl();

            // $colData[$product->getEntityId()]['hover_image'] = $baseUrl.'pub/media/catalog/product'.$product->getHover();
            $colData[$product->getEntityId()]['hover_image'] = $productHoverImage;
        }

        if($product->getTypeId() == "configurable"){
            $_children = $product->getTypeInstance()->getUsedProducts($product);
            $childSpecialPrice = '';

                foreach ($_children as $child)
                {
                  $childProductPrice = $child->getPrice();
                  $childSpecialPrice = $child->getSpecialPrice();
                }    
            $colData[$product->getEntityId()]['price'] = '$'.number_format((float)$childProductPrice, 2, '.', '');  
            if($childSpecialPrice){
              $colData[$product->getEntityId()]['special_price'] = '$'.number_format((float)$childSpecialPrice, 2, '.', ''); 
            }else{
              $colData[$product->getEntityId()]['special_price'] = '';
            } 
                            
         } else{ 
              $colData[$product->getEntityId()]['price'] = '$'.number_format((float)$product->getPrice(), 2, '.', '');
              $SpecialPrice = $product->getPriceInfo()->getPrice('special_price')->getValue();
              if($SpecialPrice){
                $colData[$product->getEntityId()]['special_price'] = '$'.number_format((float)$product->getSpecialPrice(), 2, '.', '');
              }else{
                $colData[$product->getEntityId()]['special_price'] = '';
              }
         }

        
        $parent_product = $this->_objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($product->getEntityId());

        if (isset($parent_product[0])){ 
          $_parproduct = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($parent_product[0]);
          $productUrlKey = $_parproduct->getUrlKey();
          $this->scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
          $productSuffix = $this->scopeConfig->getValue('catalog/seo/product_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
          $productUrl = $baseUrl . $productUrlKey . $productSuffix;
          $getcolor = $product->getResource()->getAttribute('color'); 
          $getcolorValue = $getcolor->getFrontend()->getValue($product);
          $_url_with_color = $productUrl."?color=".$getcolorValue;
          }else{
            $_url_with_color = '';
            $parent_product = [];
            $_parproduct = '';
          }
        if(empty($_url_with_color)){
          $colData[$product->getEntityId()]['url'] = $product->getProductUrl();  
        }else{
          $colData[$product->getEntityId()]['url'] = $_url_with_color;  
        }  
        

        if ($count++ == 3) break;
        
      } 
      return $colData;
    }

   /* public function getCoockiesData($jsonData){ 
      $baseUrl =  $this->_objectManager
                        ->get('Magento\Store\Model\StoreManagerInterface')
                        ->getStore()
                        ->getBaseUrl();
      $colData = [];                        
      $_url_with_color = '';
      foreach ($jsonData as $data) 
      { 
        //print_r($data); die('killer');
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($data);
        $colData[$data]['sku'] = $product->getSku();
        $colData[$data]['name'] = $product->getName();
        $colData[$data]['designer'] = $product->getResource()
                                                                ->getAttribute('mgs_brand')
                                                                ->getFrontend()
                                                                ->getValue($product);

        if(!empty($product->getSmallImage()) && $product->getSmallImage() != 'no_selection' ){
            $colData[$data]['image'] = $baseUrl.'pub/media/catalog/product'.$product->getSmallImage();    
        }else{
            $colData[$data]['image'] = $baseUrl.'pub/media/image/product_thumb.jpg';
        }

        if(!empty($product->getHover()) && $product->getHover() != 'no_selection'){
            $colData[$data]['hover_image'] = $baseUrl.'pub/media/catalog/product'.$product->getHover();    
        }

        if($product->getTypeId() == "configurable"): 
            $_children = $product->getTypeInstance()->getUsedProducts($product);
                foreach ($_children as $child)
                {
                  $childProductPrice = $child->getPrice();
                }    
            $colData[$data]['price'] = '$'.number_format((float)$childProductPrice, 2, '.', '');                    
        else:
            $colData[$data]['price'] = '$'.number_format((float)$product->getPrice(), 2, '.', '');
        endif;

        
        $parent_product = $this->_objectManager->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')->getParentIdsByChild($data);

         if (!empty($parent_product[0])){ 
            $_parproduct = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($parent_product[0]);
            $productUrlKey = $_parproduct->getUrlKey();
            $this->scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
            $productSuffix = $this->scopeConfig->getValue('catalog/seo/product_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $productUrl = $baseUrl . $productUrlKey . $productSuffix;
            $getcolor = $product->getResource()->getAttribute('color'); 
            $getcolorValue = $getcolor->getFrontend()->getValue($product);
            $_url_with_color = $productUrl."?color=".$getcolorValue;
          }else{
            $_url_with_color = '';
            $parent_product = [];
            $_parproduct = '';
          }
          //echo $product->getUrlKey().'<br/>';
          
          if(empty($_url_with_color)){

            $colData[$data]['url'] = $baseUrl . $product->getUrlKey().$this->scopeConfig->getValue('catalog/seo/product_url_suffix', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);;  
          }else{
            $colData[$data]['url'] = $_url_with_color;  
          } 
      }
      return $colData;
    }*/
    

}

<?php
use Magento\Framework\App\Bootstrap;
require __DIR__ . '/app/bootstrap.php';
$bootstrap = Bootstrap::create(BP, $_SERVER);
$obj = $bootstrap->getObjectManager();
$state = $obj->get('Magento\Framework\App\State');
$state->setAreaCode('frontend');
try
{
	$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
	$parent_new_arrivals = array(5);
	//$_categories =  array(64,38,27,53,75,130);
	$_categories =  array(53,75,130);
	
	foreach ($_categories as $key => $categoryId) {
		$categoryFactory = $objectManager->get('\Magento\Catalog\Model\CategoryFactory');
		// Get category object
		$category = $categoryFactory->create()->load($categoryId);
		$categoryProductCollection = $category->getProductCollection();
		$categoryProductCollection->addAttributeToSelect('*');
		if ($categoryProductCollection && count($categoryProductCollection) > 0) {
		    foreach ($categoryProductCollection as $product) { 
		        echo $product->getProductUrl() . "<br />";
		      	$categoryCollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
				$productRepository = $objectManager->get('\Magento\Catalog\Model\ProductRepository');
				$_product = $productRepository->getById($product->getId());

				$newCategoryIds = $_product->getCategoryIds();

				$_all_cat = array_merge($parent_new_arrivals,$newCategoryIds);
				//print_r($_all_cat); die;
			    
			    $categoryLinkRepository = $objectManager->get('\Magento\Catalog\Api\CategoryLinkManagementInterface');
			    $categoryLinkRepository->assignProductToCategories($product->getSku(), $_all_cat);

				//die('killer');

		        
		    }
		}	

	}
	//die('killer');

}
catch(\Exception $e)
{
   echo $e->getMessage();
   exit;
}
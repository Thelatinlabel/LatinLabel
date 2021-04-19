<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Helper;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Class ProductData
 *
 * @package Bss\PreOrder\Helper
 */
class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productInfo;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $stockRegistry;

    /**
     * @var Configurable
     */
    protected $typeConfigurable;

    /**
     * @var Grouped
     */
    protected $typeGrouped;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * ProductData constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        Configurable $typeConfigurable,
        Grouped $typeGrouped,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->typeConfigurable = $typeConfigurable;
        $this->typeGrouped = $typeGrouped;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Get All Date Configurable Product and Child
     *
     * @param int $productEntityId
     * @return array
     * @throws
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        if ($this->helper->isEnable()) {

            $childIds = $this->typeConfigurable->getChildrenIds($productEntityId);
            $items = $this->getChildrenProduct($childIds[0]);
            $detailStockChildProducts = $this->getDetailStockChildProduct($childIds[0]);

            foreach ($items as $item) {
                $childProduct = [];
                if (isset($detailStockChildProducts[$item->getId()])) {
                    $childProduct = $childProduct + $detailStockChildProducts[$item->getId()];

                }
                $childProduct['productId'] = $item->getId();
                $childProduct['preorder'] = $item->getPreorder();
                $childProduct['restock'] = $this->helper->formatDate($item->getRestock());
                $message = $this->helper->replaceVariableX(
                    $item->getMessage(),
                    $this->helper->formatDate($item->getRestock())
                );
                if ($message == "") {
                    $message = $this->helper->replaceVariableX(
                        $this->helper->getMess(),
                        $this->helper->formatDate($item->getRestock())
                    );
                }

                $childProduct['message'] = $message;

                $button = __("Pre-Order");
                if ($this->helper->getButton()) {
                    $button = $this->helper->getButton();
                }

                $childProduct['button'] = $button;

                $result['child'][$item->getId()] = $childProduct;
            }
        }
        return $result;
    }

    /**
     * @param $productIds
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    public function getChildrenProduct($productIds)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter('entity_id', ['in' => $productIds]);
        return $collection;
    }

    /**
     * @param $productIds
     * @return array
     */
    public function getDetailStockChildProduct($productIds)
    {
        $stockData = [];
        $criteria = $this->stockItemCriteriaFactory->create();
        $criteria->setProductsFilter($productIds);
        $collection = $this->stockItemRepository->getList($criteria);
        foreach ($collection->getItems() as $item) {
            $productId = $item->getProductId();
            $stock_status = $item->getIsInStock();
            if (!$this->helper->checkVersion()) {
                $stock_status = $this->helper->getIsInStock($productId);
            }
            $stockData[$productId] = [
                'stock_number' => $item->getQty(),
                'stock_status' => $stock_status
            ];
        }
        return $stockData;
    }

    /**
     * @param $product
     * @param $superAttribute
     * @return bool|\Magento\Catalog\Model\Product|null
     */
    public function getChildFromProductAttribute($product, $superAttribute)
    {
        $usedChild = $this->typeConfigurable->getProductByAttributes($superAttribute, $product);
        if ($usedChild) {
            return $usedChild;
        }
        return false;
    }

    /**
     * Check pre order of all child product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isPreOrderForAllChild($product)
    {
        $childIds = [];
        if ($product->getTypeId() == Configurable::TYPE_CODE) {
            $childIds = $this->typeConfigurable->getChildrenIds($product->getId());
        } elseif ($product->getTypeId() == Grouped::TYPE_CODE) {
            $childIds = array_values($this->typeGrouped->getChildrenIds($product->getId()));
        }
        if (!empty($childIds)) {
            $items = $this->getChildrenProduct($childIds[0]);
            $detailStockChildProducts = $this->getDetailStockChildProduct($childIds[0]);

            foreach ($items as $item) {
                if (isset($detailStockChildProducts[$item->getId()])) {
                    $preOrder = $item->getPreorder();
                    $isInStock = $detailStockChildProducts[$item->getId()]['stock_status'];
                    if ($preOrder == Order::ORDER_NO || ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }
}

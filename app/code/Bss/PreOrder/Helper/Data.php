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

use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 *
 * @package Bss\PreOrder\Helper
 */
class Data extends AbstractHelper
{
    const ORDER_NO = 0;
    const ORDER_YES = 1;
    const ORDER_OUT_OF_STOCK = 2;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Bss\PreOrder\Model\ResourceModel\PreOrder
     */
    protected $preOrderResource;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository
     * @param \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository,
        \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        parent::__construct($context);
        $this->stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->timezone = $timezone;
        $this->preOrderResource = $preOrderResource;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Get Pre Order By Product Id
     *
     * @param int $productId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPreOrder($productId)
    {
        return $this->preOrderResource->getPreOrder($productId);
    }

    /**
     * Get Product By Id
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductItem($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Get Product By Sku
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Get product by id
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Compare Verion with 2.3.0
     *
     * @return bool
     */
    public function checkVersion()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $msiEnable = $this->moduleManager->isOutputEnabled('Magento_Inventory');
        if (!$msiEnable) {
            return true;
        }
        return version_compare($magentoVersion, '2.3.0', '<');
    }

    /**
     * Check Stock Status Product
     *
     * @param int $productId
     * @return bool|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsInStock($productId)
    {
        if ($this->checkVersion()) {
            return $this->stockItemRepository->getStockItem($productId, $this->getStoreId())->getIsInStock();
        }
        return $this->productRepository->getById($productId)->isAvailable();
    }

    /**
     * @param $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->getStockItem($productId, $this->getStoreId());
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Is Enable Module
     *
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'preorder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Is Mixed Order
     *
     * @return bool
     */
    public function isMix()
    {
        return $this->scopeConfig->isSetFlag(
            'preorder/general/mix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Button Html Text
     *
     * @return string
     */
    public function getButton()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/button',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Message Pre Order
     *
     * @return string
     */
    public function getMess()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/mess',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Note Pre Order
     *
     * @return string
     */
    public function getNote()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/note',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get Cart Message
     *
     * @return string
     */
    public function getCartMess()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/cartmess',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Format Date
     *
     * @param string $date
     * @param int $format
     * @param bool $showTime
     * @param string $timezone
     * @param string $pattern
     * @return bool|string
     */
    public function formatDate(
        $date,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null,
        $pattern = 'd MMM Y'
    ) {
        if ($date) {
            return $this->timezone->formatDateTime(
                $date,
                $format,
                $showTime ? $format : \IntlDateFormatter::NONE,
                null,
                $timezone,
                $pattern
            );
        }

        return false;
    }

    /**
     * Get Mess with Restock
     *
     * @param string $mess
     * @param string $restock
     * @return mixed
     */
    public function replaceVariableX($mess, $restock)
    {
        $mess = str_replace(
            "{date}",
            $restock,
            $mess
        );
        return $mess;
    }

    /**
     * Check Is Pre Order
     *
     * @param bool $preOrder
     * @param bool $isInStock
     * @param bool $parentStockCheck
     * @return bool
     */
    public function isPreOrder($preOrder, $isInStock, $parentStockCheck = true)
    {
        if (($preOrder == self::ORDER_YES || ($preOrder == self::ORDER_OUT_OF_STOCK && !$isInStock)
            || !$parentStockCheck)) {
            return true;
        }

        return false;
    }
}

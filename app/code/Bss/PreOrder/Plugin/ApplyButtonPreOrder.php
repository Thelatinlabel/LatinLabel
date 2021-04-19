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
namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

/**
 * Class ApplyButtonPreOrder
 *
 * @package Bss\PreOrder\Plugin
 */
class ApplyButtonPreOrder
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \\Bss\PreOrder\Helper\ProductData
     */
    protected $helperProduct;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * ApplyButtonPreOrder constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\PreOrder\Helper\ProductData $helperProduct
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Bss\PreOrder\Helper\ProductData $helperProduct,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->helperProduct = $helperProduct;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
    }

    /**
     * Apply Button Pre Order For Product.
     *
     * @param \Magento\Catalog\Pricing\Render\FinalPriceBox $subject
     * @param string $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $currentProduct = $this->registry->registry('current_product');
            $product = $subject->getSaleableItem();

            $this->getProductConfigurableWishList($subject, $product);

            $productId = $product->getId();
            $preorder = $this->helper->getPreOrder($productId);
            $isInStock = $this->helper->getIsInStock($productId);

            $parentStatusCheck = $this->checkPreOrderForParent($product);

            $parentType = "";
            if ($currentProduct) {
                /* For Product Page */
                $parentType = $currentProduct->getTypeId();
            }
            
            if ((!$isInStock && $preorder == Order::ORDER_OUT_OF_STOCK) ||
                $preorder == Order::ORDER_YES || $parentStatusCheck
            ) {
                $block = $this->getReturnResults($product, $parentType);
                $result .= $block;
            }
        }
        return $result;
    }

    /**
     * @param $subject
     * @param $product
     */
    protected function getProductConfigurableWishList($subject, &$product)
    {
        if ($subject->getData('price_type_code') == 'wishlist_configured_price') {
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $superAttribute = $subject->getItem()->getBuyRequest()->getData('super_attribute');
                $child = $this->helperProduct->getChildFromProductAttribute($product, $superAttribute);
                if ($child) {
                    $product = $child;
                }
            }
        }
    }

    /**
     * @param $product
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkPreOrderForParent($product)
    {
        $typeId = $product->getTypeId();
        $isParentPreOrder = false;
        if ($typeId == Configurable::TYPE_CODE || $typeId == Grouped::TYPE_CODE) {
            $isParentPreOrder = $this->helperProduct->isPreOrderForAllChild($product);
        }
        return $isParentPreOrder;
    }

    /**
     * @param $product
     * @param $parentType
     * @return string
     */
    protected function getReturnResults($product, $parentType)
    {
        if ($product->getTypeId() == Grouped::TYPE_CODE) {
            /* Change add to cart button to Preorder for group product if all child product is preorder */
            $block = $this->layoutFactory->create()
                ->createBlock(\Bss\PreOrder\Block\PreOrderProduct::class)
                ->setTemplate('Bss_PreOrder::grouped_addtocart.phtml')
                ->setProduct($product)
                ->setParentType($parentType)
                ->toHtml();
        } else {
            $block = $this->layoutFactory->create()
                ->createBlock(\Bss\PreOrder\Block\PreOrderProduct::class)
                ->setTemplate('Bss_PreOrder::preorder_product.phtml')
                ->setProduct($product)
                ->setParentType($parentType)
                ->toHtml();
        }
        return $block;
    }
}

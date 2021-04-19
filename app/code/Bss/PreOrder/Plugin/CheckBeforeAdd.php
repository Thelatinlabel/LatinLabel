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

use Bss\PreOrder\Helper\Data;

/**
 * Class CheckBeforeAdd
 *
 * @package Bss\PreOrder\Plugin
 */
class CheckBeforeAdd
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;
    
    /**
     * CheckBeforeAdd constructor.
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Validate Product Before Add
     *
     * @param \Magento\Checkout\Model\Cart $subject
     * @param array $productInfo
     * @param array $requestInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $isPreOrderItem = $this->request->getParam('is_preorder');

        if ($this->helper->isEnable()) {
            if (!$this->helper->isMix()) {
                $cartItems = $subject->getQuote()->getAllItems();
                if ($this->request->getParam('super_group')) {
                    $this->validateForGroupProduct($cartItems, $requestInfo);
                } else {
                    if (!empty($cartItems)) {
                        $this->validateWithCart($cartItems, $isPreOrderItem);
                    }
                }
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * Validate For Group Product
     *
     * @param array $cartItems
     * @param array $requestInfo
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateForGroupProduct($cartItems, $requestInfo)
    {
        $countProduct = $countPreOrder = 0;
        foreach ($requestInfo['super_group'] as $key => $value) {
            $countProduct++;
            if ($value && !empty($cartItems)) {
                /* Validate with product in Cart */
                $isPreOrderItem = $this->request->getParam('is_preorder_group_' . $key);
                $this->validateWithCart($cartItems, $isPreOrderItem);
            } else {
                /* Validate with other child product in request if Cart no items */
                $isPreOrderItem = $this->request->getParam('is_preorder_group_' . $key);
                if ($isPreOrderItem) {
                    $countPreOrder++;
                }
            }
        }
        if (empty($cartItems)) {
            if ($countProduct != $countPreOrder) {
                $this->returnErrorMess();
            }
        }
    }

    /**
     * Validate Request Product With Items In Cart
     *
     * @param array $cartItems
     * @param bool $isPreOrderItem
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function validateWithCart($cartItems, $isPreOrderItem)
    {
        $typeConfi = \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE;
        foreach ($cartItems as $item) {
            if ($item->getProduct()->getTypeId() == $typeConfi) {
                continue;
            } else {
                $itemId = $item->getProduct()->getId();
                $preOrderCart = $this->helper->getPreOrder($itemId);
                $inStockCart = $this->helper->getIsInStock($itemId);
                $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart);
                $this->isPreOrderCart($isPreOrderItem, $isPreOrderCart);
            }
        }
    }

    /**
     * Validate
     *
     * @param bool $isPreOrderItem
     * @param bool $isPreOrderCart
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function isPreOrderCart($isPreOrderItem, $isPreOrderCart)
    {
        if (($isPreOrderItem && !$isPreOrderCart) || (!$isPreOrderItem && $isPreOrderCart)) {
            $this->returnErrorMess();
        }
    }

    /**
     * Return Error Message
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function returnErrorMess()
    {
        $message = "We could not add both pre-order and regular items to an order.";
        throw new \Magento\Framework\Exception\LocalizedException(__($message));
    }
}

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
namespace Bss\PreOrder\Plugin\Order;

use Bss\PreOrder\Model\Attribute\Source\Order;

/**
 * Class Status
 *
 * @package Bss\PreOrder\Plugin\Order
 */
class Status
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * OrderStatus constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Apply New Order Status If Order has Pre Order Product
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param string $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetStatus(\Magento\Sales\Model\Order $subject, $status)
    {
        if ($this->helper->isEnable()) {
            $items = $subject->getAllVisibleItems();
            foreach ($items as $item) {
                if ($item->getProduct()) {
                    $productId = $item->getProduct()->getId();
                    $isInStock = $this->helper->getIsInStock($productId);
                    $preOrder = $this->helper->getPreOrder($productId);

                    $status = $this->checkStatus($preOrder, $isInStock, $status);

                    if ($preOrder == Order::ORDER_YES || ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock == 0)) {
                        if ($status == 'processing') {
                            $subject->setState('processing');
                            $status = 'processing_preorder';
                        }
                    }
                }
            }
        }
        return [$status];
    }

    /**
     * Check Have Pre Order Product
     *
     * @param string $preOrder
     * @param bool $isInStock
     * @param int $status
     * @return string
     */
    private function checkStatus($preOrder, $isInStock, $status)
    {
        if ($preOrder == Order::ORDER_YES || ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
            if ($status == 'pending') {
                $status = 'pending_preorder';
            }
        }
        return $status;
    }
}

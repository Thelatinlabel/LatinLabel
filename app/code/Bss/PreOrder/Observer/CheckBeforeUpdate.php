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
namespace Bss\PreOrder\Observer;

use Bss\PreOrder\Helper\Data;
use Magento\Framework\Event\ObserverInterface;

/**
 * Class CheckBeforeUpdate
 *
 * @package Bss\PreOrder\Observer
 */
class CheckBeforeUpdate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * CheckBeforeUpdate constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Validate When Update Cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getCart()->getQuote();
        $infoDataObject = $observer->getInfo()->getData();
        $items = $quote->getAllItems();
        $i = 0;
        if ($this->helper->isEnable() && !$this->helper->isMix()) {
            foreach ($items as $item) {
                if ($i > 0) {
                    $itemId = $item->getId();
                    $productId = $item->getProductId();

                    $preOrder = $this->helper->getPreOrder($productId);
                    $isInStock = $this->helper->getIsInStock($productId);
                    if (!isset($infoDataObject[$itemId])) {
                        continue;
                    }
                    if ($preOrder == 2 && !$isInStock) {
                        $message = "We could not add both pre-order and regular items to an order";
                        throw new \Magento\Framework\Exception\LocalizedException(__($message));
                    }
                }
                $i++;
            }
        }
    }
}

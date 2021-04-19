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

/**
 * Class SkipValidate
 *
 * @package Bss\PreOrder\Plugin
 */
class SkipValidate
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * SkipValidate constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     *  Skip Check For PreOrder Product
     *
     * @param \Magento\CatalogInventory\Model\Quote\Item\QuantityValidator $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, \Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $is_preorder = $observer->getEvent()->getData('is_preorder');
            if ($is_preorder) {
                return;
            }
        }
        return $proceed($observer);
    }
}

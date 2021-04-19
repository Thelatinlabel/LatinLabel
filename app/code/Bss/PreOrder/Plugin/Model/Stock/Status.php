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
namespace Bss\PreOrder\Plugin\Model\Stock;

use Bss\PreOrder\Model\Attribute\Source\Order;

/**
 * Class Status
 *
 * @package Bss\PreOrder\Plugin\Model\Stock
 */
class Status
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    protected $productHelper;

    /**
     * Status constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Bss\PreOrder\Helper\ProductData $productHelper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Bss\PreOrder\Helper\ProductData $productHelper
    ) {
        $this->helper = $helper;
        $this->productHelper = $productHelper;
    }

    /**
     * Get Stock Status
     *
     * @param \Magento\CatalogInventory\Model\Stock\Status $subject
     * @param string $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetStockStatus($subject, $result)
    {
        if ($this->helper->isEnable() && !$result) {
            $productId = $subject->getData('product_id');
            if ($subject->getData('type_id') == 'configurable') {
                return $this->productHelper->isPreOrderForAllChild($subject);
            } else {
                $preOrder = $this->helper->getPreOrder($productId);
                if ($preOrder == Order::ORDER_YES || $preOrder == Order::ORDER_OUT_OF_STOCK) {
                    return true;
                }
            }
        }
        return $result;
    }
}

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

/**
 * Class SkipCheck
 *
 * @package Bss\PreOrder\Plugin
 */
class SkipCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $product;

    /**
     * SkipCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Catalog\Model\Product $product
    ) {
        $this->helper = $helper;
        $this->product = $product;
    }

    /**
     * Skip Check Is Salable For PreOrder Product
     *
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundIsSalable(\Magento\Catalog\Model\Product $subject, callable $proceed)
    {
        if ($this->helper->isEnable()) {
            $store = $this->helper->getStoreId();
            $preOrder = $subject->getResource()->getAttributeRawValue($subject->getId(), 'preorder', $store);
            if ($preOrder == 1 || $preOrder == 2) {
                return true;
            }
            if ($subject->getTypeId() == 'grouped') {
                $childProductCollection = $subject->getTypeInstance()->getAssociatedProducts($subject);
                $x = [];
                foreach ($childProductCollection as $child) {
                    $x[] = $this->loadProduct($child);
                }
                if (in_array(1, $x) || in_array(2, $x)) {
                    return true;
                }
            }
        }
        $returnValue = $proceed();
        return $returnValue;
    }

    /**
     * @param $child
     * @return mixed
     */
    protected function loadProduct($child)
    {
        return $this->product->load($child->getId())->getData('preorder');
    }
}

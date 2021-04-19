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
namespace Bss\PreOrder\Block;

use Magento\Framework\View\Element\Template;

/**
 * Class PreOrderProduct
 *
 * @package Bss\PreOrder\Block
 */
class PreOrderProduct extends Template
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * PreOrderProduct constructor.
     *
     * @param Template\Context $context
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Bss\PreOrder\Helper\Data $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
    }

    /**
     * Get Message Pre Order
     *
     * @return string
     */
    public function getMessage()
    {
        $typeId = $this->getParentType();
        $restockDate = $this->getRestockDate();
        $message = $this->helper->replaceVariableX(
            $this->getProduct()->getData("message"),
            $restockDate
        );

        if ($typeId == \Magento\ConfigurableProduct\Model\Product\Type\Configurable::TYPE_CODE) {
            return "";
        }

        if ($message == "") {
            $message = $this->helper->replaceVariableX($this->helper->getMess(), $restockDate);
        }

        return $message;
    }

    /**
     * Get Button Pre Order Html
     *
     * @return \Magento\Framework\Phrase|string
     */
    public function getButtonHtml()
    {
        $button = __("Pre-Order");
        if ($this->helper->getButton()) {
            $button = $this->helper->getButton();
        }

        return $button;
    }

    /**
     * Get ReStock Date
     *
     * @return string
     */
    public function getRestockDate()
    {
        $restock = $this->helper->formatDate($this->getProduct()->getData('restock'));
        return $restock;
    }

    /**
     * Check is Group Product
     *
     * @return bool
     */
    public function isGroupProduct()
    {
        $typeId = $this->getParentType();

        if ($typeId == \Magento\GroupedProduct\Model\Product\Type\Grouped::TYPE_CODE) {
            return true;
        }

        return false;
    }

    /**
     * Get Notice Pre Order Mess
     *
     * @return array
     */
    public function getNote()
    {
        $note = explode(" ", $this->helper->getNote());
        $key = array_search("{date}", $note);
        if ($key !== false) {
            unset($note[$key]);
        }
        return $note;
    }

    /**
     * Get Cart Message
     *
     * @return string
     */
    public function getCartMess()
    {
        return $this->helper->getCartMess();
    }
}

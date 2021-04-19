<?php
namespace Bss\PreOrder\Model;

class Factory
{
    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_objectManager = $objectManager;
    }

    /**
     * Create model
     *
     * @return \Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku
     */
    public function create()
    {
        return $this->_objectManager->create(\Magento\InventorySalesAdminUi\Model\GetSalableQuantityDataBySku::class);
    }
}

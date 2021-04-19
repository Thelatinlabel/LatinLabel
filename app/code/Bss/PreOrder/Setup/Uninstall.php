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
namespace Bss\PreOrder\Setup;

use Magento\Eav\Setup\EavSetupFactory;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface as UninstallInterface;

/**
 * Class Uninstall
 *
 * @package Bss\ProductLabel\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * @var EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleSetup;

    /**
     * @var \Bss\PreOrder\Model\ResourceModel\Order\Status
     */
    private $orderStatusResource;

    /**
     * Uninstall constructor.
     * @param EavSetupFactory $eavSetupFactory
     * @param ModuleDataSetupInterface $moduleSetup
     * @param \Bss\PreOrder\Model\ResourceModel\Order\Status $orderStatusResource
     */
    public function __construct(
        EavSetupFactory $eavSetupFactory,
        ModuleDataSetupInterface $moduleSetup,
        \Bss\PreOrder\Model\ResourceModel\Order\Status $orderStatusResource
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->moduleSetup = $moduleSetup;
        $this->orderStatusResource = $orderStatusResource;
    }

    /**
     * Remove data Pre Order Add to Database
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function uninstall(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {

        /** @var EavSetup $eavSetup */
        $eavSetup = $this->eavSetupFactory->create(['setup' => $this->moduleSetup]);
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'preorder');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'message');
        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, 'restock');

        $this->orderStatusResource->deletePreOrderStatus();
    }
}

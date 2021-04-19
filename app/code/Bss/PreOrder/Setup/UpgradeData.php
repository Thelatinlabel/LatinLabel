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

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\UpgradeDataInterface;

/**
 * Class UpgradeData
 *
 * @package Bss\PreOrder\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory
     */
    protected $groupCollectionFactory;

    /**
     * UpgradeData constructor.
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Group\CollectionFactory $groupCollectionFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->groupCollectionFactory = $groupCollectionFactory;
    }

    /**
     * Upgrade Eav Attribute
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $groupCollection = $this->groupCollectionFactory->create();
        $arrGroups = [];
        foreach ($groupCollection as $group) {
            if (in_array($group->getAttributeGroupName(), $arrGroups)) {
                continue;
            }
            array_push($arrGroups, $group->getAttributeGroupName());
        }
        if (!empty($arrGroups)) {
            $group = $arrGroups[0];
        } else {
            $group = 'General';
        }

        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'preorder',
            'group',
            $group
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'preorder',
            'source',
            \Bss\PreOrder\Model\Attribute\Source\Order::class
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'message',
            'group',
            $group
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'preorder',
            'used_in_product_listing',
            true
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'message',
            'used_in_product_listing',
            true
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'restock',
            'used_in_product_listing',
            true
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'preorder',
            'apply_to',
            'simple'
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'message',
            'apply_to',
            'simple'
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'restock',
            'apply_to',
            'simple'
        );
    }
}

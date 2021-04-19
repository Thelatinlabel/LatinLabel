<?php
/**
 * Copyright 2020 aheadWorks. All rights reserved.
See LICENSE.txt for license details.
 */

namespace Aheadworks\Rma\Block\Customer\Request\View;

use Aheadworks\Rma\Api\CustomFieldRepositoryInterface;
use Aheadworks\Rma\Api\Data\RequestCustomFieldValueInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Block\Product\ImageBuilder as ProductImageBuilder;
use Aheadworks\Rma\Block\CustomField\Input\Renderer\Factory as CustomFieldRendererFactory;
use Aheadworks\Rma\Api\Data\RequestItemInterface;
use Aheadworks\Rma\Model\Request\Resolver\OrderItem as OrderItemResolver;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\ProductRepository;

/**
 * Class Items
 *
 * @method RequestItemInterface[] getOrderItems()
 * @method Items setOrderItems(RequestItemInterface[] $orderItems)
 * @method int getStatusId()
 * @method Items setStatusId(int $statusId)
 * @package Aheadworks\Rma\Block\Customer\Request\View
 */
class Items extends Template
{
    /**
     * {@inheritdoc}
     */
    protected $_template = 'Aheadworks_Rma::customer/request/view/items.phtml';

    /**
     * @var ProductImageBuilder
     */
    private $productImageBuilder;

    /**
     * @var CustomFieldRepositoryInterface
     */
    private $customFieldRepository;

    /**
     * @var CustomFieldRendererFactory
     */
    private $customFieldRendererFactory;

    /**
     * @var OrderItemResolver
     */
    private $orderItemResolver;

    /**
     * @var ProductRepository
     */
    private $_productRepository;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * @param Context $context
     * @param ProductImageBuilder $productImageBuilder
     * @param CustomFieldRepositoryInterface $customFieldRepository
     * @param CustomFieldRendererFactory $customFieldRendererFactory
     * @param OrderItemResolver $orderItemResolver
     * @param ProductRepository $productRepository
     * @param Configurable $configurable
     * @param array $data
     */
    public function __construct(
        Context $context,
        ProductImageBuilder $productImageBuilder,
        CustomFieldRepositoryInterface $customFieldRepository,
        CustomFieldRendererFactory $customFieldRendererFactory,
        OrderItemResolver $orderItemResolver,
        ProductRepository $productRepository,
        Configurable $configurable,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->productImageBuilder = $productImageBuilder;
        $this->customFieldRepository = $customFieldRepository;
        $this->customFieldRendererFactory = $customFieldRendererFactory;
        $this->orderItemResolver = $orderItemResolver;
        $this->_productRepository = $productRepository;
        $this->configurable = $configurable;
    }

    /**
     * Retrieve order item product
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProduct($itemId)
    {
        return $this->orderItemResolver->getItemProduct($itemId);
    }

    /**
     * Retrieve order item product image html
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProductImageHtml($itemId)
    {
        $product = $this->orderItemResolver->getItemProduct($itemId);
        $configurableProductIds = $this->configurable->getParentIdsByChild($product->getId());
        if ((!$product->getThumbnail() || $product->getThumbnail() == "no_selection") && isset($configurableProductIds[0])) {
            $parentId = $configurableProductIds[0];
            $product = $this->_productRepository->getById($parentId);
        }

        return $this->productImageBuilder->setProduct($product)
            ->setImageId('product_thumbnail_image')
            ->create()
            ->toHtml();
    }
    /**
     * Retrieve item unit price html
     *
     * @param int $itemId
     * @return string
     */
    public function getItemPriceHtml($itemId)
    {
        /** @var \Magento\Sales\Block\Order\Item\Renderer\DefaultRenderer $block */
        $block = $this->getLayout()->getBlock('item_unit_price');
        if (!$block) {
            return '';
        }
        $block->setItem($this->orderItemResolver->getItemWithPrice($itemId));

        return $block->toHtml();
    }

    /**
     * Retrieve product view url
     *
     * @param int $itemId
     * @return string
     */
    public function getItemProductUrl($itemId)
    {
        return $this->orderItemResolver->getItemProductUrl($itemId);
    }

    /**
     * Retrieve order item name by id
     *
     * @param int $itemId
     * @return string
     */
    public function getItemName($itemId)
    {
        return $this->orderItemResolver->getName($itemId);
    }

    /**
     * Retrieve request custom fields input html
     *
     * @param RequestCustomFieldValueInterface $requestItemCustomField
     * @param int $requestItemId
     * @return string
     */
    public function getRequestItemCustomFieldHtml($requestItemCustomField, $requestItemId)
    {
        $customField = $this->customFieldRepository->get($requestItemCustomField->getFieldId());
        $fieldName = 'order_items.' . $requestItemId . '.custom_fields.' . $customField->getId();
        $value = $requestItemCustomField->getValue();
        $renderer = $this->customFieldRendererFactory
            ->create($customField, $this->getStatusId(), $fieldName, $value);

        return $renderer->toHtml();
    }
}

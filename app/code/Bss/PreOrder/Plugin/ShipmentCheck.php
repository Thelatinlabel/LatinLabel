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
 * Class ShipmentCheck
 *
 * @package Bss\PreOrder\Plugin
 */
class ShipmentCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ShipmentCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->helper = $helper;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Not Apply Create Shippment If Order Have PreOrder Product
     *
     * @param \Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator $subject
     * @param callable $proceed
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, \Magento\Sales\Api\Data\ShipmentInterface $entity)
    {
        if ($this->helper->isEnable()) {
            $order = $this->orderRepository->get($entity->getOrderId());
            $items = $order->getItems();
            foreach ($items as $item) {
                $productId = $item->getProductId();

                $isInStock = $this->helper->getIsInStock($productId);
                $preOrder = $this->helper->getPreOrder($productId);

                $status = $this->orderRepository->get($entity->getOrderId())->getStatus();
                if ($preOrder == Order::ORDER_YES || ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock) &&
                    ($status == 'pending_preorder' || $status == 'processing_preorder')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__("Could not create a shipment because "
                            . $item->getName()
                            . " is a pre-order product."));
                }
            }
        }
        return $proceed($entity);
    }
}

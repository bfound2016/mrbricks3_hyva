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
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Model\Order\Shipment\Validation\QuantityValidator;
use Magento\Sales\Api\OrderRepositoryInterface;

class ShipmentCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var OrderRepositoryInterface
     */
    protected $orderRepository;

    /**
     * ShipmentCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Not Apply Create Shipment If Order Have PreOrder Product
     *
     * @param QuantityValidator $subject
     * @param callable $proceed
     * @param ShipmentInterface $entity
     * @return mixed
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, ShipmentInterface $entity)
    {
        $type_allows = ['simple', 'virtual', 'downloadable'];
        if ($this->helper->isEnable()) {
            $items = $entity->getItems();
            $productPreOrder = $entity->getOrder()->getProductPreOrder();
            $order = $this->orderRepository->get($entity->getOrderId());
            $orderItemsById = $this->getOrderItems($order);
            $listPreOrder = [];
            if ($productPreOrder && $productPreOrder != '[]') {
                $listPreOrder = array_keys($this->helper->serializeClass()->unserialize($productPreOrder));
            }
            foreach ($items as $item) {
                /* @var \Magento\Sales\Model\Order\Shipment\Item $item */
                $productId = $item->getProductId();
                $product = $this->helper->getProductItem($productId);
                $orderItem = $orderItemsById[$item->getOrderItemId()];
                $qtyToShip = $item->getQty();
                $itemQtyOrdered = $orderItem->getQtyOrdered();
                $salableQty = $this->helper->getProductSalableQty($item, $item->getEntityId(), $itemQtyOrdered);
                if (!in_array($product->getTypeId(), $type_allows) || !in_array($product->getId(), $listPreOrder)) {
                    continue;
                }
                $preOrder = $this->helper->getPreOrder($productId);
                $isInStock = $this->helper->getIsInStock($productId);
                if (($preOrder == Order::ORDER_YES && $this->helper->isAvailablePreOrder($productId)) ||
                    ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock == 0 && $qtyToShip > $salableQty)) {
                    throw new LocalizedException(
                        __(
                            "Only create shipment with in stock product. Could not create a shipment because "
                            . $item->getName()
                            . " is a pre-order product."
                        )
                    );
                }
            }
        }
        return $proceed($entity);
    }

    /**
     * @param OrderInterface $order
     * @return OrderItemInterface[]
     */
    private function getOrderItems(OrderInterface $order)
    {
        $orderItemsById = [];
        foreach ($order->getItems() as $item) {
            $orderItemsById[$item->getItemId()] = $item;
        }

        return $orderItemsById;
    }
}

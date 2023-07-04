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
namespace Bss\PreOrder\Observer;

use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Bss\PreOrder\Model\Attribute\Source\Order;

class OrderPlaceBefore implements ObserverInterface
{
    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * OrderPlaceBefore constructor.
     * @param PreOrderHelper $preOrderHelper
     */
    public function __construct(
        PreOrderHelper $preOrderHelper
    ) {
        $this->preOrderHelper = $preOrderHelper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getOrder();
        if ($this->preOrderHelper->isEnable()) {
            $items = $order->getAllVisibleItems();
            $listProductPreOrder = [];
            $arrayTypeAllow = ['simple','virtual','downloadable'];
            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $item->getProduct();
                if ($product && in_array($product->getTypeId(), $arrayTypeAllow)) {
                    $listProductPreOrder = $this->checkItemPreOrder($product, $order, $item, $listProductPreOrder);
                }
            }
            $order->setProductPreOrder($this->preOrderHelper->serializeClass()->serialize($listProductPreOrder));
        }
    }

    /**
     * @param mixed $product
     * @param \Magento\Sales\Model\Order $order
     * @param mixed $item
     * @param array $listProductPreOrder
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function checkItemPreOrder($product, &$order, $item, $listProductPreOrder)
    {
        $productId = $product->getId();
        $isInStock = $this->preOrderHelper->getIsInStock($productId);
        $preOrder = $this->preOrderHelper->getPreOrder($productId, $order->getStoreId());
        $message = $this->preOrderHelper->replaceVariableX(
            $this->preOrderHelper->getNote(),
            $this->preOrderHelper->getPreOrderFromDate($productId, $order->getStoreId()),
            $this->preOrderHelper->getPreOrderToDate($productId, $order->getStoreId())
        );

        $salableQty = $this->preOrderHelper->getProductSalableQty($product, $product->getEntityId(), $item->getQtyOrdered());
        if ($isInStock) {
            if ($item->getQtyOrdered() > $salableQty) {
                $isInStock = 0;
            }
        } else {
            $stockStatus = $this->preOrderHelper->getStockItem($productId)->getIsInStock();
            if ($item->getQtyOrdered() == $salableQty && $stockStatus) {
                $isInStock = 1;
            }
        }

        if (($preOrder == Order::ORDER_YES && $this->preOrderHelper->isAvailablePreOrder($productId)) ||
            ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock == 0)) {
            $listProductPreOrder[$productId] = $message;
        }
        $order->setHasPreOrderProduct(false);
        if (!empty($listProductPreOrder)) {
            $order->setHasPreOrderProduct(true);
        }
        return $listProductPreOrder;
    }
}

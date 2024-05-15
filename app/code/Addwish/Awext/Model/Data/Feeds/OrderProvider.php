<?php declare(strict_types = 1);

namespace Addwish\Awext\Model\Data\Feeds;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Addwish\Awext\Api\Data\Feeds\OrderProviderInterface;
use Addwish\Awext\Helper\Config as ConfigHelper;

# order and order item class can be found here:
# /vendor/magento/module-sales/Model/Order.php
# /vendor/magento/module-sales/Model/Order/Item.php

class OrderProvider implements OrderProviderInterface {
    /**
     * @var array
     */
    protected $orderFeedArray = [];

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

     /**
     * @var ConfigHelper
     */
    protected $configHelper;

    public function __construct(
        ProductRepositoryInterface $productRepository,
        ConfigHelper $configHelper
    ) {
        $this->productRepository = $productRepository;
        $this->configHelper = $configHelper;
    }

    public function generate(array $dataArray): array {
        foreach ($dataArray as $order) {
            if ($order instanceof OrderInterface) {
                $orderItems = $order->getItems();
                $orderForFeed = [
                    self::ORDER_NUMBER_NODE_NAME => $order->getIncrementId(),
                    self::ORDER_STATUS_NODE_NAME => $order->getStatus(),
                    self::ORDER_STATE_NODE_NAME => $order->getState(),
                    self::ORDER_CURRENCY_NODE_NAME => $order->getOrderCurrencyCode(),
                    self::ORDER_TOTAL_NODE_NAME => $order->getGrandTotal(),
                    self::ORDER_TOTAL_UPDATED_NODE_NAME => ($order->getBaseGrandTotal() - $order->getBaseTotalRefunded()),
                    self::BASE_CURRENCY_NODE_NAME => $order->getBaseCurrencyCode(),
                    self::BASE_TOTAL_NODE_NAME => $order->getBaseGrandTotal(),
                    self::BASE_TOTAL_UPDATED_NODE_NAME => ($order->getGrandTotal() - $order->getTotalRefunded()),
                    self::DATE_NODE_NAME => $order->getCreatedAt(),
                    self::DATE_UPDATED_NODE_NAME => $order->getUpdatedAt(),
                    self::PRODUCTS_NODE_NAME => $this->getProductList($orderItems),
                    self::CUSTOMER_GROUP_ID_NODE_NAME => $order->getCustomerGroupId(),
                ];
                if ($this->configHelper->isEmailsInTrackingEnabled()) {
                    $orderForFeed[self::CUSTOMER_EMAIL_NODE_NAME] = $order->getCustomerEmail();
                    $orderForFeed[self::CUSTOMER_COUNTRY_NODE_NAME] = $order->getBillingAddress()->getCountryId();
                }
                $this->orderFeedArray[] = $orderForFeed;
            }
        }
        return $this->orderFeedArray;
    }

    protected function getProductList(array $orderItems): array {
        $productList = [];
        foreach ($orderItems as $item) {
            # check that the product still exists
            # and check whether the current item has a parent.
            # a simple product added on its own will not have a parent.
            # a simple product added as a variation of a configurable product
            # or part of a bundled product, will have a parent.
            # both the parent and the children are part of the orderitems, 
            # so by asking for the children of the parent items 
            # we are not missing anything by excluding the children with this check.
            if ($item->getProduct() && !$item->getParentItem()) {
                $productInOrder = [
                    self::PRODUCT_NUMBER_NODE_NAME => $item->getProduct()->getSku(),
                    self::PRODUCT_IS_RETURNED_NODE_NAME => ($item->getQtyOrdered() == $item->getQtyRefunded()),
                    self::PRODUCT_QTY_NODE_NAME => $item->getQtyOrdered(),
                    self::PRODUCT_QTY_UPDATED_NODE_NAME => ($item->getQtyOrdered() - $item->getQtyRefunded())
                ];
                # if this is a simple product added directly, it will not have children.
                # if it is a configurabe product it will have children.
                if ($item->getHasChildren()) {
                    $childItems = [];
                    foreach ($item->getChildrenItems() as $child) {
                        $childItem = [
                            self::CHILD_PRODUCT_NUMBER_NODE_NAME => $child->getProduct()->getSku(),
                            self::PRODUCT_IS_RETURNED_NODE_NAME => ($child->getQtyOrdered() == $child->getQtyRefunded()),
                            self::PRODUCT_QTY_NODE_NAME => $child->getQtyOrdered(),
                            self::PRODUCT_QTY_UPDATED_NODE_NAME => ($child->getQtyOrdered() - $child->getQtyRefunded())
                        ];
                        $childItems[] = $childItem;
                    }
                    $productInOrder[self::PRODUCT_CHILDREN_NODE_NAME] = $childItems;
                }
                $productList[] = $productInOrder;
            }
        }
        return $productList;
    }
}
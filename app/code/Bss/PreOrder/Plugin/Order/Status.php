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

namespace Bss\PreOrder\Plugin\Order;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\Catalog\Model\Product;

class Status
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * OrderStatus constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    )
    {
        $this->helper = $helper;
    }

    /**
     * Apply New Order Status If Order has Pre Order Product
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param string $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetStatus(\Magento\Sales\Model\Order $subject, $status)
    {
        if ($this->helper->isEnable()) {
            $items = $subject->getAllItems();
            $productPreOrder = $subject->getProductPreOrder();
            $listPreOrder = [];
            if ($productPreOrder && $productPreOrder != '[]') {
                $listPreOrder = array_keys($this->helper->serializeClass()->unserialize($productPreOrder));
            }
            $notAllowType = ['configurable', 'bundle', 'grouped'];
            foreach ($items as $item) {
                /** @var Product|null $product */
                $product = $item->getProduct();
                if ($product || !in_array($item->getProductType(), $notAllowType)) {
                    $productId = $product->getId();
                    $isInStock = $this->helper->getIsInStock($productId);
                    $preOrder = $this->helper->getPreOrder($productId);
                    $state = $subject->getState();
                    $status = $this->checkStatusPending($subject, $status, $state);
                    if ($this->checkHasPreOrderProduct($preOrder, $productId, $isInStock)) {
                        if ($state == \Magento\Sales\Model\Order::STATE_PROCESSING
                            && in_array($productId, $listPreOrder) && $subject->getStatus() != 'processing_preorder') {
                            $status = 'processing_preorder';
                        }
                        if ($status == 'payment_review') {
                            $listPreOrder[] = $productId;
                        }
                    }
                }
            }
        }
        return [$status];
    }
    /**
     * @param \Magento\Sales\Model\Order $subject
     * @param $result
     * @param $status
     * @return mixed
     */
    public function afterSetStatus(\Magento\Sales\Model\Order $subject, $result, $status)
    {
        if ($status == 'payment_review' && $subject->getProductPreorder()) {
            $subject->setCheckPreOrder(1);
        }
       return $result;
    }
    /**
     * Check Have Pre Order Product
     *
     * @param \Magento\Sales\Model\Order $subject
     * @param int $status
     * @param string $state
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function checkStatusPending($subject, $status, $state)
    {
        if ($subject->getHasPreOrderProduct()) {
            if ($state == \Magento\Sales\Model\Order::STATE_NEW) {
                $status = 'pending_preorder';
            }
        }
        return $status;
    }

    /**
     * @param string $preOrder
     * @param int $productId
     * @param bool $isInStock
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function checkHasPreOrderProduct($preOrder, $productId, $isInStock)
    {
        if (($preOrder == Order::ORDER_YES && $this->helper->isAvailablePreOrder($productId)) ||
            ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
            return true;
        }
        return false;
    }
}

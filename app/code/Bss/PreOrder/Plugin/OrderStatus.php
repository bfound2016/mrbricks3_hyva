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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Model\Attribute\Source\Order;

class OrderStatus
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Sales\Api\Data\OrderInterface
     */
    protected $order;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\AreaList
     */
    protected $areaList;

    /**
     * OrderStatus constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Sales\Api\Data\OrderInterface $order
     * @param \Magento\Framework\App\AreaList $areaList
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\App\AreaList $areaList
    ) {
        $this->helper = $helper;
        $this->order = $order;
        $this->request = $request;
        $this->areaList = $areaList;
    }

    /**
     * @param $subject
     * @param $status
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeSetStatus(\Magento\Sales\Model\Order $subject, $status)
    {
        if ($this->helper->isEnable()) {
            $items = $subject->getAllVisibleItems();
            foreach ($items as $item) {
                if ($item->getProductType()=='configurable') {
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $product = $this->helper->getProductItemBySku($sku);
                    $productId = $product->getId();
                    $preOrder = $product->getData('preorder');
                    $isInStock = $product->isAvailable();
                } else {
                    $productId = $item->getProductId();
                    $preOrder = $this->helper->getPreOrder($productId);
                    $isInStock = $this->helper->getIsInStock($productId);
                }
                $stockItem = $this->helper->getStockItem($productId);
                $stock = $stockItem->getQty();

                $status = $this->checkStatus($preOrder, $isInStock, $stock, $status);

                if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && $isInStock==0)) {
                    if ($status=='processing') {
                        $subject->setState('processing');
                        $status = 'processing_preorder';
                    }
                }
            }
        }
        return [$status];
    }

    /**
     * @param $preOrder
     * @param $isInStock
     * @param $stock
     * @param $status
     * @return string
     */
    private function checkStatus($preOrder, $isInStock, $stock, $status)
    {
        if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && ($isInStock==0
                    || ($isInStock == 1
                        && $stock < 0)))) {
            if ($status=='pending') {
                $status = 'pending_preorder';
            }
        }
        return $status;
    }
}

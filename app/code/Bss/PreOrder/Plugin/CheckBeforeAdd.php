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

use Bss\PreOrder\Helper\Data;
use Bss\PreOrder\Model\Attribute\Source\Order;

class CheckBeforeAdd
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Checkout\Model\Cart\RequestInfoFilterInterface
     */
    protected $requestInfoFilter;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObject;

    /**
     * CheckBeforeAdd constructor.
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     * @param \Magento\Framework\DataObjectFactory $dataObject
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\DataObjectFactory $dataObject
    ) {

        $this->helper = $helper;
        $this->request = $request;
        $this->configurable = $configurable;
        $this->urlBuilder = $urlBuilder;
        $this->dataObject = $dataObject;
    }

    /**
     * @param $cart
     * @param $requestInfo
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkGrouped($cart, $requestInfo)
    {
        foreach ($requestInfo['super_group'] as $key => $value) {
            if ($value==1) {
                $preOrderItem = $this->helper->getPreOrder($key);
                $inStockItem = $this->helper->getIsInStock($key);
                $itemSku = $cart[0]->getSku();
                $itemId = $this->helper->getProductItemBySku($itemSku)->getId();
                $preOrderCart = $this->helper->getPreOrder($itemId);
                $inStockCart = $this->helper->getIsInStock($itemId);
                $isPreOrderItem = $this->helper->isPreOrder($preOrderItem, $inStockItem);
                $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart);

                if (($isPreOrderItem && !$isPreOrderCart) || (!$isPreOrderItem && $isPreOrderCart)) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * @param $requestInfo
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function groupedNoCart($requestInfo)
    {
        $product=$this->helper->getProductItem($requestInfo['product']);
        if ($product->getTypeId()=='grouped') {
            $i=0;
            $preOrderCart = true;
            $inStockCart = true;
            foreach ($requestInfo['super_group'] as $key => $value) {
                if ($value==1) {
                    if ($i == 0) {
                        $preOrderCart = $this->helper->getPreOrder($key);
                        $inStockCart = $this->helper->getIsInStock($key);
                    }
                    if ($i != 0) {
                        $preOrderItem = $this->helper->getPreOrder($key);
                        $inStockItem = $this->helper->getIsInStock($key);
                        $isPreOrderItem = $this->helper->isPreOrder($preOrderItem, $inStockItem);
                        $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart);

                        if ($this->isPreOrderCart($isPreOrderItem, $isPreOrderCart)) {
                            return true;
                        }
                    }
                    $i++;
                }
            }
        }
        return false;
    }

    /**
     * @param $cart
     * @param $requestInfo
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ifGroupNoCart($cart, $requestInfo)
    {
        if (!$cart) {
            return $this->groupedNoCart($requestInfo);
        }
        return false;
    }

    /**
     * @param \Magento\Checkout\Model\Cart $subject
     * @param int|\Magento\Catalog\Model\Product $productInfo
     * @param \Magento\Framework\DataObject|int $requestInfo
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        $request = $this->getProductRequest($requestInfo);
        $productId = $requestInfo['product'];
        $minQty = $this->helper->getStockItem($productId)->getMinSaleQty();
        if (!$request->getQty()) {
            $request->setQty($minQty);
        }

        if ($this->helper->isEnable()) {
            if (!$this->helper->isMix()) {
                $cart = $subject->getQuote()->getAllVisibleItems();
                $error = $this->ifGroupNoCart($cart, $requestInfo);
                if ($cart) {
                    $product=$this->helper->getProductItem($requestInfo['product']);
                    if ($product->getTypeId()=='grouped') {
                        $error = $this->checkGrouped($cart, $requestInfo);
                        $this->noticeError($error);
                        return [$productInfo, $requestInfo];
                    }

                    if ($product->getTypeId()=='configurable') {
                        $childProduct = $this->configurable
                            ->getProductByAttributes($requestInfo['super_attribute'], $product);
                        $productId = $childProduct->getId();
                    } else {
                        $productId = $requestInfo['product'];
                    }

                    $preOrderItem = $this->helper->getPreOrder($productId);
                    $inStockItem = $this->helper->getIsInStock($productId);

                    $itemSku = $cart[0]->getSku();
                    $item = $this->helper->getProductItemBySku($itemSku);
                    $itemId = $item->getId();
                    $preOrderCart = $item->getData('preorder');

                    $inStockCart = $this->helper->getIsInStock($itemId);

                    $isPreOrderItem = $this->helper->isPreOrder($preOrderItem, $inStockItem);
                    $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart);

                    if ($this->isPreOrderCart($isPreOrderItem, $isPreOrderCart)) {
                        $error = true;
                    }
                    $qty = $this->helper->getStockItem($productId)->getQty();
                    $qtyAdded = $request->getQty();
                    $qtyAdded = $this->checkProductInCart($qtyAdded, $cart, $productId);

                    $this->checkException($isPreOrderCart, $preOrderItem, $qty, $qtyAdded, $productId);
                }
                $this->noticeError($error);
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * @param $isPreOrderItem
     * @param $isPreOrderCart
     * @return bool
     */
    protected function isPreOrderCart($isPreOrderItem, $isPreOrderCart)
    {
        if (($isPreOrderItem && !$isPreOrderCart) || (!$isPreOrderItem && $isPreOrderCart)) {
            return true;
        }
        return false;
    }

    /**
     * @param $isPreOrderCart
     * @param $preOrderItem
     * @param $qty
     * @param $qtyAdded
     * @param $productId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function checkException($isPreOrderCart, $preOrderItem, $qty, $qtyAdded, $productId)
    {
        if (!$isPreOrderCart && $preOrderItem==Order::ORDER_OUT_OF_STOCK && ($qty < $qtyAdded)) {
            $name = $this->helper->getProductItem($productId)->getName();
            $message = "We don't have as many ".$name." as you requested. ";
            $message .= "We could not add both pre-order and regular items to an order.";
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        }
    }

    /**
     * @param $error
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function noticeError($error)
    {
        if ($error) {
            $message = "We could not add both pre-order and regular items to an order.";
            throw new \Magento\Framework\Exception\LocalizedException(__($message));
        }
    }

    /**
     * @param $requestInfo
     * @return \Magento\Framework\DataObject
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getProductRequest($requestInfo)
    {
        if ($requestInfo instanceof \Magento\Framework\DataObject) {
            $request = $requestInfo;
        } elseif (is_numeric($requestInfo)) {
            $request = $this->dataObject->create(['qty' => $requestInfo]);
        } elseif (is_array($requestInfo)) {
            $request = $this->dataObject->create($requestInfo);
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('We found an invalid request for adding product to quote.')
            );
        }
        $this->getRequestInfoFilter()->filter($request);

        return $request;
    }

    /**
     * @return mixed
     */
    protected function getRequestInfoFilter()
    {
        if ($this->requestInfoFilter === null) {
            $this->requestInfoFilter = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Checkout\Model\Cart\RequestInfoFilterInterface::class);
        }
        return $this->requestInfoFilter;
    }

    /**
     * @param $qtyAdded
     * @param $cart
     * @param $productId
     * @return mixed
     */
    protected function checkProductInCart($qtyAdded, $cart, $productId)
    {
        foreach ($cart as $item) {
            if ($item->getProductId()==$productId) {
                return $qtyAdded = $qtyAdded + $item->getQty();
            }
        }
        return $qtyAdded;
    }
}

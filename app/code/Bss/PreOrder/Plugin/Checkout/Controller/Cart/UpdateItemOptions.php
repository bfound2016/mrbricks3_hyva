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
namespace Bss\PreOrder\Plugin\Checkout\Controller\Cart;

class UpdateItemOptions
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    protected $hasPreOrderItem = false;
    protected $hasNormalItem = false;

    /**
     * CheckBeforeUpdate constructor.
     * @param Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory
    ) {
        $this->helper = $helper;
        $this->cart = $cart;
        $this->configurable = $configurable;
        $this->messageManager = $messageManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject
     */
    public function beforeExecute($subject)
    {
        $id = (int)$subject->getRequest()->getParam('id');
        $params = $subject->getRequest()->getParams();
        if (isset($id) && isset($params['qty']) && $this->helper->isEnable() && !$this->helper->isMix()) {
            $quoteItems = $this->cart->getQuote()->getAllItems();
            $defaultQty = $params['qty'];
            foreach ($quoteItems as $item) {
                $productId = $item->getProduct()->getId();
                $product = $item;
                $qty = $item->getQty();
                if ($id == $item->getId()) {
                    $defaultQty = $qty;
                    $qty = $params['qty'];
                }
                if ($item->getProduct()->getTypeId() == 'configurable') {
                    $requestInfo = $item->getBuyRequest();
                    $product = $this->helper->getProductById($productId);
                    $product = $this->configurable->getProductByAttributes(
                        $requestInfo['super_attribute'],
                        $product
                    );
                    $productId = $product->getId();
                }
                $this->checkPreOrderItem($product, $productId, $qty);
            }
            $this->checkShowError($subject, $defaultQty);
        }
    }

    /**
     * @param \Magento\Checkout\Controller\Cart\UpdateItemOptions $subject
     * @param float $qty
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function checkShowError($subject, $qty)
    {
        try {
            if ($this->hasPreOrderItem && $this->hasNormalItem) {
                $this->hasPreOrderItem = false;
                $this->hasNormalItem = false;
                $message = "We could not add both pre-order and regular items to an order";
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
        } catch (\Exception $e) {
            $subject->getRequest()->setParam('qty', $qty);
            $this->messageManager->addErrorMessage($e->getMessage());
            return null;
        }
    }

    /**
     * @param mixed $item
     * @param int $productId
     * @param float $qty
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function checkPreOrderItem($item, $productId, $qty)
    {
        $preOrderCart = $this->helper->getPreOrder($productId);
        $inStockCart = $this->helper->getIsInStock($productId);
        $availabilityPreOrder = $this->helper->isAvailablePreOrder($productId);
        $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart, $availabilityPreOrder);
        if ($inStockCart && $preOrderCart == 2) {
            $qtyProduct = $this->helper->getProductSalableQty($item, $productId);
            if ($qty > $qtyProduct) {
                $isPreOrderCart = true;
            }
        }
        if ($isPreOrderCart) {
            $this->hasPreOrderItem = true;
        } else {
            $this->hasNormalItem = true;
        }
        return $isPreOrderCart;
    }
}

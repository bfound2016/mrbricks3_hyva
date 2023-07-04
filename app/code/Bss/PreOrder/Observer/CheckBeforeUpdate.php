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

use Bss\PreOrder\Helper\Data;
use Magento\Framework\Event\ObserverInterface;

class CheckBeforeUpdate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;
    protected $hasPreOrderItem = false;
    protected $hasNormalItem = false;
    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurable;

    /**
     * CheckBeforeUpdate constructor.
     * @param Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
     */
    public function __construct(
        Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable
    ) {
        $this->helper = $helper;
        $this->configurable = $configurable;
    }

    /**
     * Validate When Update Cart
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getCart()->getQuote();
        $infoDataObject = $observer->getInfo()->getData();
        $items = $quote->getAllItems();
        if ($this->helper->isEnable() && !$this->helper->isMix()) {
            foreach ($items as $item) {
                $productId = $item->getProduct()->getId();
                $product = $item;
                $qty = $item->getQty();
                if (isset($infoDataObject[$item->getId()])) {
                    $qty = $infoDataObject[$item->getId()]['qty'];
                }
                if ($item->getProduct()->getTypeId() == 'configurable') {
                    $requestInfo =$item->getBuyRequest();
                    $product = $this->helper->getProductById($productId);
                    $product = $this->configurable->getProductByAttributes(
                        $requestInfo['super_attribute'],
                        $product
                    );
                    $productId = $product->getId();
                }
                $this->checkPreOrderCartItem($product, $productId, $qty);
            }
            if ($this->hasPreOrderItem && $this->hasNormalItem) {
                $this->hasPreOrderItem = false;
                $this->hasNormalItem = false;
                $message = "We could not add both pre-order and regular items to an order";
                throw new \Magento\Framework\Exception\LocalizedException(__($message));
            }
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
    protected function checkPreOrderCartItem($item, $productId, $qty)
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

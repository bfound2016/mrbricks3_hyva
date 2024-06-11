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
namespace Bss\PreOrder\Plugin\Block\Ui;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Registry;
use \Bss\PreOrder\Helper\Data;

class ProductViewCounter
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @var \\Bss\PreOrder\Helper\ProductData
     */
    private $helperProduct;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * ProductViewCounter constructor.
     * @param Data $helper
     * @param SerializerInterface $serialize
     * @param \Bss\PreOrder\Helper\ProductData $helperProduct
     * @param Registry $registry
     */
    public function __construct(
        Data $helper,
        SerializerInterface $serialize,
        \Bss\PreOrder\Helper\ProductData $helperProduct,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->serialize = $serialize;
        $this->helperProduct = $helperProduct;
        $this->registry = $registry;
    }

    /**
     * @param $subject
     * @param $result
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentProductData(
        $subject,
        $result
    ) {
        if ($this->helper->isEnable()) {
            /** @var ProductInterface $product */
            $product = $this->registry->registry('product');
            if ($product && $product->getId()) {
                $parentStockCheck = true;
                $currentProductData = $this->serialize->unserialize($result);
                $label = $this->helper->getButton() ? $this->helper->getButton() : __("Pre-Order");
                $productId = $product->getId();
                $preOrder = $this->helper->getPreOrder($productId);
                $isInStock = $this->helper->getIsInStock($productId);
                if ($product->getTypeId()=='configurable') {
                    $parentStockCheck = $this->helperProduct->isStockParent($productId);
                }
                if ($this->helper->isPreOrder($preOrder, $isInStock, $parentStockCheck)) {
                    $currentProductData['items'][$productId]['add_to_cart_button']['pre-order'] = $label;
                    return $this->serialize->serialize($currentProductData);
                }
            }
        }
        return $result;
    }
}

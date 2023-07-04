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
namespace Bss\PreOrder\Plugin\Block\Ui;

use Magento\Framework\Registry;
use Bss\PreOrder\Helper\Data;

class ProductViewCounter
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    private $helperProduct;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * ProductViewCounter constructor.
     * @param Data $helper
     * @param \Bss\PreOrder\Helper\ProductData $helperProduct
     * @param Registry $registry
     */
    public function __construct(
        Data $helper,
        \Bss\PreOrder\Helper\ProductData $helperProduct,
        Registry $registry
    ) {
        $this->helper = $helper;
        $this->helperProduct = $helperProduct;
        $this->registry = $registry;
    }

    /**
     * Aplly Button Pre Order for current Product
     *
     * @param \Magento\Catalog\Block\Ui\ProductViewCounter $subject
     * @param string $result
     * @return bool|string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetCurrentProductData(
        $subject,
        $result
    ) {
        if ($this->helper->isEnable()) {
            $product = $this->registry->registry('product');
            if ($product && $product->getId()) {
                $currentProductData = $this->helper->serializeClass()->unserialize($result);
                $label = $this->helper->getButton() ? $this->helper->getButton() : __("Pre-Order");
                $productId = $product->getId();
                $preOrder = $product->getData('preorder');
                $isInStock = $product->getData('is_salable');
                $fromDate =  $product->getData('pre_oder_from_date');
                $toDate =  $product->getData('pre_oder_to_date');
                $parentStockCheck = $this->helperProduct->isPreOrderForAllChild($product) ? false : true;
                $availabilityPreOrder = $this->helper->isAvailablePreOrderFromFlatData($fromDate, $toDate);
                if ($this->helper->isPreOrder($preOrder, $isInStock, $availabilityPreOrder, $parentStockCheck)) {
                    $currentProductData['items'][$productId]['add_to_cart_button']['pre-order'] = $label;
                    return $this->helper->serializeClass()->serialize($currentProductData);
                }
            }
        }
        return $result;
    }
}

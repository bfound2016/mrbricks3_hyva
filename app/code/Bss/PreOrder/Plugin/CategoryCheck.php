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

class CategoryCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \\Bss\PreOrder\Helper\ProductData
     */
    protected $helperProduct;

    /**
     * CategoryCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Bss\PreOrder\Helper\ProductData $helperProduct
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Bss\PreOrder\Helper\ProductData $helperProduct
    ) {
        $this->helper=$helper;
        $this->request = $request;
        $this->helperProduct = $helperProduct;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterToHtml($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $button = __("Pre-Order");
            if ($this->helper->getButton()) {
                $button = $this->helper->getButton();
            }
            $page = $this->request->getFullActionName();

            switch ($page) {
                case "catalog_product_view":
                    $result = $this->getHtmlProductPage($subject, $result, $button);
                    break;
                default:
                    $result = $this->getHtmlOtherPage($page, $subject, $result, $button);
            }
        }
       
        return $result;
    }

    /**
     * @param $subject
     * @param $result
     * @param $button
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getHtmlProductPage($subject, $result, $button)
    {
        if ($subject->getSaleableItem()->getTypeId() == 'configurable') {
            $productId = $subject->getSaleableItem()->getId();
            $parentStatusCheck = $this->helperProduct->isStatusParentConfi($productId);
            if ($parentStatusCheck) {
                $result = $result."<span class='pre-order'>".$button."</span>";
            }
        }
        return $result;
    }

    /**
     * @param $page
     * @param $subject
     * @param $result
     * @param $button
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getHtmlOtherPage($page, $subject, $result, $button)
    {
        $parentStockCheck = true;
        if ($page == 'wishlist_index_index' && $subject->getSaleableItem()->getTypeId()=='configurable') {
            $sku        = $subject->getSaleableItem()->getSku();
            $product    = $this->helper->getProductItemBySku($sku);
            $productId  = $product->getId();
            $preOrder   = $product->getData('preorder');
            $isInStock  = $product->isAvailable();
        } else {
            $productId  = $subject->getSaleableItem()->getId();
            $preOrder   = $this->helper->getPreOrder($productId);
            $isInStock  = $this->helper->getIsInStock($productId);
        }
        if ($subject->getSaleableItem()->getTypeId()=='configurable') {
            $parentStockCheck = $this->helperProduct->isStockParent($productId);
        }
        if ( ( ($preOrder == 1 && $isInStock) || ($preOrder ==2 && !$isInStock)) && ( $page!="catalog_product_view" && $page!="catalog_category_view" )|| !$parentStockCheck) {
            $result = $result."<span class='pre-order'><i class='fa fa-clock'></i>".$button."</span>".'
            <script type="text/javascript">
                require(["jquery"], function($){
                    $(".product-item").trigger("contentUpdated");
                });                    
            </script>
            <script type="text/x-magento-init">
                {
                    "*": {
                        "Bss_PreOrder/js/index": {}
                    }
                }
            </script>';
        }
        return $result;
    }
}

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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\PreOrder\Ui\DataProvider\Product\Listing\Collector;

use Bss\PreOrder\Helper\ProductData;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductRenderExtensionFactory;
use Magento\Catalog\Api\Data\ProductRenderInterface;
use Magento\Catalog\Ui\DataProvider\Product\ProductRenderCollectorInterface;
use Bss\PreOrder\Helper\Data;

class PreOrder implements ProductRenderCollectorInterface
{
    /** PreOrder key */
    const KEY = "pre_order";

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    protected $helperProduct;

    /**
     * @var ProductRenderExtensionFactory
     */
    protected $productRenderExtensionFactory;

    /**
     * @param ProductRenderExtensionFactory $productRenderExtensionFactory
     * @param Data $helper
     * @param ProductData $helperProduct
     */
    public function __construct(
        ProductRenderExtensionFactory $productRenderExtensionFactory,
        Data $helper,
        ProductData $helperProduct
    ) {
        $this->helper = $helper;
        $this->helperProduct = $helperProduct;
        $this->productRenderExtensionFactory = $productRenderExtensionFactory;
    }

    /**
     * @param ProductInterface $product
     * @param ProductRenderInterface $productRender
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function collect(ProductInterface $product, ProductRenderInterface $productRender)
    {
        if ($this->helper->isEnable()) {
            $extensionAttributes = $productRender->getExtensionAttributes();

            if (!$extensionAttributes) {
                $extensionAttributes = $this->productRenderExtensionFactory->create();
            }

            $label = $this->helper->getButton() ? $this->helper->getButton() : __("Pre-Order");
            $preOrder = $product->getData('preorder');
            $isInStock = $product->getData('is_salable');
            $fromDate =  $product->getData('pre_oder_from_date');
            $toDate =  $product->getData('pre_oder_to_date');
            $parentStockCheck = $this->helperProduct->isPreOrderForAllChild($product) ? false : true;
            $availabilityPreOrder = $this->helper->isAvailablePreOrderFromFlatData($fromDate, $toDate);

            if ($this->helper->isPreOrder($preOrder, $isInStock, $availabilityPreOrder, $parentStockCheck)) {
                $extensionAttributes
                    ->setPreOrder($label);

                $productRender->setExtensionAttributes($extensionAttributes);
            }
        }
    }
}

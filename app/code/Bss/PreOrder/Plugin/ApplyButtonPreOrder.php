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
 * @category  BSS
 * @package   Bss_PreOrder
 * @author    Extension Team
 * @copyright Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Pricing\Render\FinalPriceBox;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\LocalizedException;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class ApplyButtonPreOrder
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
     * @var \Bss\PreOrder\Helper\ProductData
     */
    protected $helperProduct;

    /**
     * @var \Magento\Framework\View\LayoutFactory
     */
    protected $layoutFactory;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * ApplyButtonPreOrder constructor.
     *
     * @param \Bss\PreOrder\Helper\Data             $helper
     * @param \Magento\Framework\App\Request\Http   $request
     * @param \Bss\PreOrder\Helper\ProductData      $helperProduct
     * @param \Magento\Framework\View\LayoutFactory $layoutFactory
     * @param \Magento\Framework\Registry           $registry
     * @param ProductRepositoryInterface            $productRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request,
        \Bss\PreOrder\Helper\ProductData $helperProduct,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\Registry $registry,
        ProductRepositoryInterface $productRepository
    ) {
        $this->helper = $helper;
        $this->request = $request;
        $this->helperProduct = $helperProduct;
        $this->layoutFactory = $layoutFactory;
        $this->registry = $registry;
        $this->productRepository = $productRepository;
    }

    /**
     * Apply Button Pre Order For Product.
     *
     * @param  FinalPriceBox $subject
     * @param  string        $result
     * @return string
     * @throws LocalizedException
     */
    public function afterToHtml($subject, $result)
    {
        if ($this->helper->isEnable()) {
            $currentProduct = $this->registry->registry('current_product');
            $product = $subject->getSaleableItem();

            $this->getProductConfigurableWishList($subject, $product);
            $preorder = $this->getPreOrderOfProductType($product);
            $isInStock = $product->getData('is_salable');

            $parentStatusCheck = $this->checkPreOrderForParent($product);

            $parentType = "";
            if ($currentProduct) {
                if ($currentProduct->getTypeId() == Grouped::TYPE_CODE
                    || $currentProduct->getId() == $product->getId()
                ) {
                    /* For Product Page */
                    $parentType = $currentProduct->getTypeId();
                    if ($currentProduct->getId() == $product->getId()) {
                        $isInStock = $this->helper->getIsInStock($currentProduct->getId());
                    }
                }
            }

            $productType = $product->getTypeId();
            /**
             * @purpose check compatible with cp grid module
             * @reason  cp grid call to many time to func toHtml() on simple and configurable
             * we need to call on configurable product only
             */
            if ($this->helper->checkProductConfigurableGridView()
                && $parentType == Configurable::TYPE_CODE
                && $productType == "simple"
            ) {
                return $result;
            }
            $isAvailablePreOrder = $this->helper->isAvailablePreOrderFromFlatData(
                $product->getData('pre_oder_from_date'),
                $product->getData('pre_oder_to_date')
            );

            return $this->addHtml(
                $isInStock,
                $preorder,
                $isAvailablePreOrder,
                $result,
                $product,
                $parentType,
                $parentStatusCheck
            );
        }
        return $result;
    }

    /**
     * @param  $product
     * @return false
     */
    private function getPreOrderOfProductType($product)
    {
        $allowType = ['simple', 'downloadable', 'virtual'];
        if (in_array($product->getTypeId(), $allowType)) {
            return $product->getData('preorder');
        }
        return false;
    }

    /**
     * @param  int|bool     $isInStock
     * @param  int|bool     $preorder
     * @param  int|bool     $isAvailablePreOrder
     * @param  mixed|string $result
     * @param  mixed        $product
     * @param  string       $parentType
     * @param  int|bool     $parentStatusCheck
     * @return mixed|string
     */
    private function addHtml(
        $isInStock,
        $preorder,
        $isAvailablePreOrder,
        $result,
        $product,
        $parentType,
        $parentStatusCheck
    ) {
        if ((!$isInStock && $preorder == Order::ORDER_OUT_OF_STOCK)
            || ($preorder == Order::ORDER_YES && $isAvailablePreOrder) || $parentStatusCheck
        ) {
            $block  = $this->getReturnResults($product, $parentType, $parentStatusCheck);
            $result .= $block;
        }
        return $result;
    }

    /**
     * @param FinalPriceBox $subject
     * @param Product       $product
     */
    protected function getProductConfigurableWishList($subject, &$product)
    {
        if ($subject->getData('price_type_code') == 'wishlist_configured_price') {
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $superAttribute = $subject->getItem()->getBuyRequest()->getData('super_attribute');
                $child = $this->helperProduct->getChildFromProductAttribute($product, $superAttribute);
                if ($child) {
                    $product = $child;
                }
            }
        }
    }

    /**
     * @param  Product $product
     * @return bool
     * @throws LocalizedException
     */
    protected function checkPreOrderForParent($product)
    {
        $typeId = $product->getTypeId();
        $isParentPreOrder = false;
        if ($typeId == Configurable::TYPE_CODE || $typeId == Grouped::TYPE_CODE) {
            $isParentPreOrder = $this->helperProduct->isPreOrderForAllChild($product);
        }
        return $isParentPreOrder;
    }

    /**
     * @param  Product $product
     * @param  string  $parentType
     * @param  boolean $parentStatusCheck
     * @return string
     */
    protected function getReturnResults($product, $parentType, $parentStatusCheck)
    {
        $blockHtml = '';
        if ($parentType == Grouped::TYPE_CODE && $parentStatusCheck) {
            $groupedProduct = $this->productRepository->getById($product->getId());
            /* Change add to cart button to Preorder for group product if all child product is preorder */
            $blockHtml .= $this->layoutFactory->create()
                ->createBlock(\Bss\PreOrder\Block\PreOrderProduct::class)
                ->setTemplate('Bss_PreOrder::grouped_addtocart.phtml')
                ->setProduct($groupedProduct)
                ->setParentType($parentType)
                ->toHtml();
        }
        $template = 'Bss_PreOrder::preorder_product.phtml';
        if ($this->helper->checkProductConfigurableGridView()) {
            $template = 'Bss_PreOrder::pre_order_cp_grid.phtml';
        }
        $blockHtml .= $this->layoutFactory->create()
            ->createBlock(\Bss\PreOrder\Block\PreOrderProduct::class)
            ->setTemplate($template)
            ->setProduct($product)
            ->setParentType($parentType)
            ->toHtml();
        return $blockHtml;
    }
}

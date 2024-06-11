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
namespace Bss\PreOrder\Plugin\Pricing\ConfigurableProduct;

use Magento\Catalog\Api\Data\ProductInterface;
use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\Catalog\Model\ResourceModel\Product\LinkedProductSelectBuilderInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class LowestPrice
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableData;

    /**
     * @var LinkedProductSelectBuilderInterface
     */
    private $linkedProductSelectBuilder;

    /**
     * Key is product id and store id. Value is array of prepared linked products
     *
     * @var array
     */
    private $linkedProductMap;

    /**
     * LowestPrice constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param LinkedProductSelectBuilderInterface $linkedProductSelectBuilder
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LinkedProductSelectBuilderInterface $linkedProductSelectBuilder,
        CollectionFactory $collectionFactory
    ) {
        $this->helper               = $helper;
        $this->configurableData     = $configurableData;
        $this->storeManager         = $storeManager;
        $this->linkedProductSelectBuilder = $linkedProductSelectBuilder;
        $this->collectionFactory    = $collectionFactory;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param ProductInterface $product
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundGetProducts(
        $subject,
        callable $proceed,
        ProductInterface $product
    ) {
        $result = $proceed($product);
        if ($this->helper->isEnable() && $product->getTypeId() == 'configurable') {
            $min = 0;
            $parentProduct = $this->configurableData->getChildrenIds($product->getId());
            $key = $this->storeManager->getStore()->getId() . '-' . $product->getId();
            foreach ($parentProduct[0] as $childId) {
                $product = $this->helper->getProductItem($childId);
                $isInStock = $product->isAvailable();
                $preOrder = $product->getData('preorder');
                if ($preOrder==Order::ORDER_YES || $preOrder==Order::ORDER_OUT_OF_STOCK || $isInStock) {
                    if (isset($minPrice)) {
                        if ($product->getFinalPrice() < $minPrice) {
                            $min = $childId;
                            $minPrice = $product->getFinalPrice();
                        }
                    } else {
                        $min = $childId;
                        $minPrice = $product->getFinalPrice();
                    }
                }
            }
            return $this->returnResult($min, $result, $key);
        }
        return $result;
    }

    /**
     * @param $min
     * @param $result
     * @param $key
     * @return mixed
     */
    private function returnResult($min, $result, $key)
    {
        if ($min == 0) {
            return $result;
        } else {
            $productIds = [$min];
            $this->linkedProductMap[$key] = $this->collectionFactory->create()
                ->addAttributeToSelect(
                    ['price', 'special_price', 'special_from_date', 'special_to_date', 'tax_class_id']
                )
                ->addIdFilter($productIds)
                ->getItems();
            return $this->linkedProductMap[$key];
        }
    }
}

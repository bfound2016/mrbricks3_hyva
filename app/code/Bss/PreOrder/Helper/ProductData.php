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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Helper;

use Bss\PreOrder\Model\Attribute\Source\Order;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\GroupedProduct\Model\Product\Type\Grouped;

class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productInfo;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    protected $stockRegistry;

    /**
     * @var Configurable
     */
    protected $typeConfigurable;

    /**
     * @var Grouped
     */
    protected $typeGrouped;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemRepositoryInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory
     */
    protected $stockItemCriteriaFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * ProductData constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $typeConfigurable
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $collectionFactory,
        \Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        \Magento\CatalogInventory\Api\StockItemRepositoryInterface $stockItemRepository,
        Configurable $typeConfigurable,
        Grouped $typeGrouped,
        Data $helper
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
        $this->stockItemRepository = $stockItemRepository;
        $this->typeConfigurable = $typeConfigurable;
        $this->typeGrouped = $typeGrouped;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Get All Date Configurable Product and Child
     *
     * @param mixed $allowProduct
     * @return array
     * @throws
     */
    public function getAllData($allowProduct)
    {
        $result = [];
        if ($this->helper->isEnable()) {
            $hasButton = $this->helper->getButton();

            foreach ($allowProduct as $item) {
                $childProduct['stock_status'] = false;
                if ($item->getData('is_salable')) {
                    $childProduct['stock_status'] = true;
                }
                $childProduct['productId'] = $item->getData('entity_id');
                $childProduct['preorder'] = $item->getData('preorder');
                $childProduct['pre_oder_from_date'] = $this->helper->formatDate($item->getData('pre_oder_from_date'));
                $childProduct['pre_oder_to_date'] = $this->helper->formatDate($item->getData('pre_oder_to_date'));
                $childProduct['availability_preorder'] = $this->helper->isAvailablePreOrderFromFlatData(
                    $childProduct['pre_oder_from_date'],
                    $childProduct['pre_oder_to_date']
                );
                $messageProduct = $item->getData('message');
                $childProduct['availability_message'] = $this->helper->replaceVariableX(
                    $item->getData('availability_message'),
                    $childProduct['pre_oder_from_date'],
                    $childProduct['pre_oder_to_date']
                );

                $messageProduct = $messageProduct !== null ? $messageProduct : '';
                $template_mess = !empty(trim($messageProduct)) ? $messageProduct : $this->helper->getMess();
                $childProduct['message'] = $this->helper->replaceVariableX(
                    $template_mess,
                    $childProduct['pre_oder_from_date'],
                    $childProduct['pre_oder_to_date']
                );

                $button = __("Pre-Order");
                if ($hasButton) {
                    $button = $hasButton;
                }

                $childProduct['button'] = $button;

                $result['child'][$item->getData('entity_id')] = $childProduct;
            }
        }
        return $result;
    }

    /**
     * @param mixed $product
     * @param mixed $superAttribute
     * @return bool|\Magento\Catalog\Model\Product|null
     */
    public function getChildFromProductAttribute($product, $superAttribute)
    {
        $usedChild = $this->typeConfigurable->getProductByAttributes($superAttribute, $product);
        if ($usedChild) {
            return $usedChild;
        }
        return false;
    }

    /**
     * Check pre order of all child product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isPreOrderForAllChild($product)
    {
        if ($product->getTypeId() == 'configurable') {
            $items = $product->getTypeInstance()->getUsedProducts($product);
        } elseif ($product->getTypeId() == 'grouped') {
            $items = $product->getTypeInstance()->getAssociatedProducts($product);
        } else {
            return false;
        }
        if (!empty($items)) {
            foreach ($items as $item) {
                $preOrder = $item->getData('preorder');
                $isInStock = $item->getData('is_salable');
                if ($preOrder == Order::ORDER_NO ||
                    ($preOrder == Order::ORDER_OUT_OF_STOCK && $isInStock) ||
                    $preOrder == Order::ORDER_YES && !$this->helper->isAvailablePreOrderFromFlatData(
                        $this->helper->formatDate($item->getData('pre_oder_from_date')),
                        $this->helper->formatDate($item->getData('pre_oder_to_date'))
                    )
                ) {
                    return false;
                }
            }
        }
        return true;
    }
}

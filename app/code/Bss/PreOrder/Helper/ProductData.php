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
namespace Bss\PreOrder\Helper;

use Bss\PreOrder\Model\Attribute\Source\Order;

class ProductData extends \Magento\Framework\Url\Helper\Data
{
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    private $productInfo;

    /**
     * @var \Magento\CatalogInventory\Model\StockRegistry
     */
    private $stockRegistry;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $customer;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    private $httpContext;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\ProductMetadata $productMetadata
     */
    private $productMetadata;

    /**
     * ProductData constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Model\ProductRepository $productInfo
     * @param \Magento\CatalogInventory\Model\StockRegistry $stockRegistry
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\Customer $customer
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\ProductMetadata $productMetadata
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductRepository $productInfo,
        \Magento\CatalogInventory\Model\StockRegistry $stockRegistry,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customer,
        Data $helper,
        \Magento\Framework\App\ProductMetadata $productMetadata
    ) {
        $this->productInfo = $productInfo;
        $this->stockRegistry = $stockRegistry;
        $this->configurableData = $configurableData;
        $this->httpContext = $httpContext;
        $this->storeManager = $storeManager;
        $this->customer = $customer;
        $this->helper = $helper;
        $this->productMetadata = $productMetadata;
        parent::__construct($context);
    }

    /**
     * @return bool
     */
    public function checkVersion()
    {
        $version = $this->productMetadata->getVersion();
        if (version_compare($version, '2.1.0') >= 0) {
            return true;
        }
        return false;
    }

    /**
     * @param int $productEntityId
     * @return array
     * @throws
     */
    public function getAllData($productEntityId)
    {
        $result = [];
        if ($this->helper->isEnable()) {
            $mapr = [];
            $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
            $product = $this->productInfo->getById($productEntityId);

            $parentAttribute = $this->configurableData->getConfigurableAttributes($product);
            $result['entity'] = $productEntityId;
            foreach ($parentAttribute as $attrKey => $attrValue) {
                foreach ($product->getAttributes()[$attrValue->getProductAttribute()->getAttributeCode()]
                    ->getOptions() as $tvalue) {
                    $result['map'][$attrValue->getAttributeId()]['label'] = $attrValue->getLabel();
                    $result['map'][$attrValue->getAttributeId()][$tvalue->getValue()] = $tvalue->getLabel();
                    $mapr[$attrValue->getAttributeId()][$tvalue->getLabel()] = $tvalue->getValue();
                }
            }
            
            foreach ($parentProduct[0] as $simpleProduct) {
                $childProduct = [];
                $childProduct['entity'] = $simpleProduct;
                $child = $this->productInfo->getById($childProduct['entity']);
                $childStock = $this->stockRegistry->getStockItem($childProduct['entity']);

                $childProduct['stock_number'] = $childStock->getQty();
                $childProduct['stock_status'] = $child->isAvailable();
                $childProduct['productId'] = $childProduct['entity'];
                $childProduct['preorder'] = $child->getData('preorder');
                $childProduct['restock'] = $this->helper->formatDate($child->getData('restock'));
                $message = $this->helper->replaceVariableX(
                    $child->getData('message'),
                    $this->helper->formatDate($child->getData('restock'))
                );
                if ($message=="") {
                    $message = $this->helper->replaceVariableX(
                        $this->helper->getMess(),
                        $this->helper->formatDate($child->getData('restock'))
                    );
                }

                $childProduct['message'] = $message;

                $button = __("Pre-Order");
                if ($this->helper->getButton()) {
                    $button = $this->helper->getButton();
                }

                $childProduct['button'] = $button;

                $key = '';
                foreach ($parentAttribute as $attrKey => $attrValue) {
                    $attrLabel = $attrValue->getProductAttribute()->getAttributeCode();
                    $childRow = $child->getAttributes()[$attrLabel]->getFrontend()->getValue($child);
                    if ($this->checkVersion()) {
                        $key .= $mapr[$attrValue->getAttributeId()][$childRow] . '_';
                    } else {
                        $key = $mapr[$attrValue->getAttributeId()][$childRow] . '_' . $key;
                    }
                }

                $result['child'][$key] = $childProduct;
            }
        }
        return $result;
    }

    /**
     * If All Child Configurable Product PreOrder -> Parents PreOrder
     *
     * @param int $productEntityId
     * @return bool
     * @throws
     */
    public function isStockParent($productEntityId)
    {
        $inStock = false;
        $parentProduct = $this->configurableData->getChildrenIds($productEntityId);
        foreach ($parentProduct[0] as $simpleProduct) {
            $child = $this->productInfo->getById($simpleProduct);
            $stock = $child->isAvailable();
            $preOrder = $child->getData('preorder');
            if ($stock == 1 && $preOrder != 1) {
                $inStock = true;
                break;
            }
        }
        return $inStock;
    }

    /**
     * @param $productId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isStatusParent($productId)
    {
        $parentProduct = $this->configurableData->getChildrenIds($productId);
        $allPreOrder = $onePreOrder = 0;
        foreach ($parentProduct[0] as $simpleProduct) {
            $allPreOrder++;
            $preOrder = $this->helper->getPreOrder($simpleProduct);
            if ($preOrder==Order::ORDER_YES || $preOrder==Order::ORDER_OUT_OF_STOCK) {
                $onePreOrder++;
            }
        }
        if ($allPreOrder == $onePreOrder)
        {
            return true;
        }
        return false;
    }

    /**
     * @param $productId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function isStatusParentConfi($productId)
    {
        $parentProduct = $this->configurableData->getChildrenIds($productId);
        $allPreOrder = $onePreOrder = 0;
        foreach ($parentProduct[0] as $simpleProduct) {
            $allPreOrder++;
            $preOrder = $this->helper->getPreOrder($simpleProduct);
            $isInStock = $this->helper->getIsInStock($simpleProduct);
            if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
                $onePreOrder++;
        }
        }
        if ($allPreOrder == $onePreOrder)
        {
            return true;
        }
        return false;
    }
}

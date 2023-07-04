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
namespace Bss\PreOrder\Model\Product\Type;

use Magento\Catalog\Api\ProductRepositoryInterface;
use \Bss\PreOrder\Helper\Data as PreOrderHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Grouped extends \Magento\GroupedProduct\Model\Product\Type\Grouped
{
    /**
     * @var PreOrderHelper
     */
    protected $helper;

    /**
     * @param \Magento\Catalog\Model\Product\Option $catalogProductOption
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Catalog\Model\Product\Type $catalogProductType
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Psr\Log\LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Msrp\Helper\Data $msrpData
     * @param PreOrderHelper $helper
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Catalog\Model\Product\Option $catalogProductOption,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Catalog\Model\Product\Type $catalogProductType,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\MediaStorage\Helper\File\Storage\Database $fileStorageDb,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Registry $coreRegistry,
        \Psr\Log\LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        \Magento\GroupedProduct\Model\ResourceModel\Product\Link $catalogProductLink,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Product\Attribute\Source\Status $catalogProductStatus,
        \Magento\Framework\App\State $appState,
        \Magento\Msrp\Helper\Data $msrpData,
        PreOrderHelper $helper,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null
    ) {
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository,
            $catalogProductLink,
            $storeManager,
            $catalogProductStatus,
            $appState,
            $msrpData,
            $serializer
        );
        $this->helper = $helper;
    }

    /**
     * Get simple associated products.
     * Override function to add pre order attributes to collection
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    public function getAssociatedProducts($product)
    {
        if (!$this->helper->isEnable()) {
            return parent::getAssociatedProducts($product);
        }
        if (!$product->hasData($this->_keyAssociatedProducts)) {
            $associatedProducts = [];

            $this->setSaleableStatus($product);

            //add attributes preorder to select
            $defaultAttribute = [
                'name',
                'price',
                'special_price',
                'special_from_date',
                'special_to_date',
                'tax_class_id',
                'image'
            ];
            $mergeAttributes = array_merge($defaultAttribute, PreOrderHelper::LIST_PREORDER_ATTRIBUTES);
            $collection = $this->getAssociatedProductCollection(
                $product
            )->addAttributeToSelect(
                [$mergeAttributes] // select attributes
            )->addFilterByRequiredOptions()->setPositionOrder()->addStoreFilter(
                $this->getStoreFilter($product)
            )->addAttributeToFilter(
                'status',
                ['in' => $this->getStatusFilters($product)]
            );

            foreach ($collection as $item) {
                $associatedProducts[] = $item;
            }

            $product->setData($this->_keyAssociatedProducts, $associatedProducts);
        }
        return $product->getData($this->_keyAssociatedProducts);
    }

    /**
     * Returns product info
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param bool $isStrictProcessMode
     * @return array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getProductInfo(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $isStrictProcessMode
    ) {
        $productsInfo = $buyRequest->getSuperGroup() ?: [];
        $associatedProducts = $this->getAssociatedProducts($product);

        if (!is_array($productsInfo)) {
            return __('Please specify the quantity of product(s).')->render();
        }
        $hasEnabledModule = $this->helper->isEnable();
        foreach ($associatedProducts as $subProduct) {
            $productId = $subProduct->getId();
            $preOrderCart = $subProduct->getData('preorder');
            $inStockCart = $subProduct->getData('is_salable');
            $preorderFromDate = $subProduct->getData('pre_oder_from_date');
            $preorderToDate = $subProduct->getData('pre_oder_to_date');
            $availabilityPreOrder = $this->helper->isAvailablePreOrderFromFlatData(
                $preorderFromDate,
                $preorderToDate
            );
            $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart, $availabilityPreOrder);
            if ($hasEnabledModule &&
                !$inStockCart &&
                !$isPreOrderCart) {
                continue;
            }

            if (!isset($productsInfo[$productId]) && ($inStockCart || $isPreOrderCart)) {
                if ($isStrictProcessMode && !$subProduct->getQty()) {
                    return __('Please specify the quantity of product(s).')->render();
                }
                if (!$preOrderCart) {
                    $productsInfo[$productId] = $subProduct->getData('is_salable') ? (float)$subProduct->getQty() : 0;
                } else {
                    $productsInfo[$productId] = (float)$subProduct->getQty();
                }
            }
        }
        return $productsInfo;
    }

    /**
     * Prepare product and its configuration to be added to some products list.
     * Perform standard preparation process and add logic specific to Grouped product type.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _prepareProduct(
        \Magento\Framework\DataObject $buyRequest,
        $product,
        $processMode
    ) {
        $products = [];
        $associatedProductsInfo = [];
        $isPreOrderCart = false;
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);
        $productsInfo = $this->getProductInfo($buyRequest, $product, $isStrictProcessMode);
        if (is_string($productsInfo)) {
            return $productsInfo;
        }
        $associatedProducts = !$isStrictProcessMode || !empty($productsInfo)
            ? $this->getAssociatedProducts($product)
            : false;

        $hasEnabledModule = $this->helper->isEnable();
        foreach ($associatedProducts as $subProduct) {
            $productId = $subProduct->getId();
            if ($hasEnabledModule) {
                $preOrderCart = $subProduct->getData('preorder');
                $inStockCart = $subProduct->getData('is_salable');
                $preorderFromDate = $subProduct->getData('pre_oder_from_date');
                $preorderToDate = $subProduct->getData('pre_oder_to_date');
                $availabilityPreOrder = $this->helper->isAvailablePreOrderFromFlatData(
                    $preorderFromDate,
                    $preorderToDate
                );
                $isPreOrderCart = $this->helper->isPreOrder($preOrderCart, $inStockCart, $availabilityPreOrder);
                if (!$inStockCart && !$isPreOrderCart) {
                    continue;
                }
            }
            if (!isset($productsInfo[$productId]) ||
                !is_numeric($productsInfo[$productId]) ||
                empty($productsInfo[$productId])) {
                continue;
            }
            $qty = $productsInfo[$productId];

            $_result = $subProduct->getTypeInstance()->_prepareProduct($buyRequest, $subProduct, $processMode);

            if (is_string($_result)) {
                return $_result;
            } elseif (!isset($_result[0])) {
                return __('Cannot process the item.')->render();
            }
            if ($isStrictProcessMode) {
                $_result[0]->setCartQty($qty);
                $_result[0]->addCustomOption('product_type', self::TYPE_CODE, $product);
                $_result[0]->addCustomOption(
                    'info_buyRequest',
                    $this->serializer->serialize(
                        [
                            'super_product_config' => [
                                'product_type' => self::TYPE_CODE,
                                'product_id' => $product->getId(),
                            ],
                        ]
                    )
                );
                $_result[0]->setData('is_pre_order', $isPreOrderCart);
                $products[] = $_result[0];
            } else {
                $associatedProductsInfo[] = [$subProduct->getId() => $qty];
                $product->addCustomOption('associated_product_' . $subProduct->getId(), $qty);
            }
        }
        if (!$isStrictProcessMode || count($associatedProductsInfo)) {
            $product->addCustomOption('product_type', self::TYPE_CODE, $product);
            $product->addCustomOption('info_buyRequest', $this->serializer->serialize($buyRequest->getData()));

            $products[] = $product;
        }
        if (count($products)) {
            return $products;
        }
        return __('Please specify the quantity of product(s).')->render();
    }
}

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

use Bss\PreOrder\Model\Attribute\Source\Order as SourceOrder;
use Magento\Catalog\Model\ProductFactory;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
    const ORDER_NO = 0;
    const ORDER_YES = 1;
    const ORDER_OUT_OF_STOCK = 2;

    const DISPLAY_OOS_PATH_CONFIG = 'preorder/general/display_oos_with_pre_status_only';
    const ENABLE_MODULE_CONFIGURABLE_GRID_VIEW = "configuablegridview/general/active";
    const LIST_PREORDER_ATTRIBUTES = [
        'preorder',
        'pre_oder_from_date',
        'pre_oder_to_date',
        'message',
        'availability_message'
    ];

    /**
     * @var boolean
     */
    protected $preOrderItem;

    /**
     * @var boolean
     */
    protected $preOrderCartItem;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    protected $stockItemRepository;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * @var \Bss\PreOrder\Model\ResourceModel\PreOrder
     */
    protected $preOrderResource;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product
     */
    protected $resourceProduct;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * @var \Bss\PreOrder\Model\Factory
     */
    protected $multiSourceInventory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var Configurable
     */
    private $configurable;

    /**
     * Data constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository
     * @param \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource
     * @param \Magento\Catalog\Model\ResourceModel\Product $resourceProduct
     * @param ProductMetadataInterface $productMetadata
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param ProductFactory $productFactory
     * @param \Magento\Framework\Serialize\SerializerInterface $serializer
     * @param \Bss\PreOrder\Model\Factory $multiSourceInventory
     * @param \Magento\Framework\App\Request\Http $request
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository,
        \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource,
        \Magento\Catalog\Model\ResourceModel\Product $resourceProduct,
        ProductMetadataInterface $productMetadata,
        \Magento\Framework\Module\Manager $moduleManager,
        ProductFactory $productFactory,
        \Magento\Framework\Serialize\SerializerInterface $serializer,
        \Magento\Framework\App\Request\Http $request,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurable,
        \Bss\PreOrder\Model\Factory $multiSourceInventory
    ) {
        $this->request = $request;
        $this->configurable = $configurable;
        $this->registry = $registry;
        parent::__construct($context);
        $this->stockItemRepository = $stockItemRepository;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->timezone = $timezone;
        $this->preOrderResource = $preOrderResource;
        $this->resourceProduct = $resourceProduct;
        $this->productMetadata = $productMetadata;
        $this->moduleManager = $moduleManager;
        $this->productFactory = $productFactory;
        $this->serializer = $serializer;
        $this->multiSourceInventory = $multiSourceInventory;
    }

    /**
     * @param mixed $product
     * @param int $productId
     * @param float $itemQtyOrdered
     * @return int|float|null
     */
    public function getProductSalableQty($product, $productId, $itemQtyOrdered = 0)
    {
        if ($this->checkVersion()) {
            $qtyProduct = $this->getStockItem($productId)->getQty() + $itemQtyOrdered;
        } else {
            $qtyProduct = $this->getSalableQtyOnlyStock($product->getSku()) + $itemQtyOrdered;
        }
        return $qtyProduct ? $qtyProduct : 0;
    }

    /**
     * @param string|null $sku
     * @return int|float
     * @deprecated 1.2.0
     */
    public function getSalableQty($sku)
    {
        $qtySalable = 0;
        $data = $this->multiSourceInventory->getSalableQtyBySku()->execute($sku);
        if ($data && is_array($data)) {
            foreach ($data as $stockSource) {
                if (isset($stockSource['qty'])) {
                    $qtySalable += $stockSource['qty'];
                }
            }
        }
        return $qtySalable;
    }

    /**
     * Get salable qty by stock current website
     *
     * @param string $sku
     * @return int|float
     */
    public function getSalableQtyOnlyStock($sku)
    {
        return $this->multiSourceInventory->getSalableQtyOnlyStock($sku);
    }

    /**
     * @return mixed
     */
    public function serializeClass()
    {
        return $this->serializer;
    }

    /**
     * Get Pre Order By Product Id
     *
     * @param int $productId
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPreOrder($productId, $storeId = null)
    {
        $storeId = $storeId ? $storeId : $this->getStoreId();
        $preOrderData = $this->resourceProduct->getAttributeRawValue(
            $productId,
            'preorder',
            $storeId
        );
        return $preOrderData ? $preOrderData : 0;
    }

    /**
     * Get Product By Id
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductItem($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Get Product By Sku
     *
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    /**
     * Get product by id
     *
     * @param int $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductById($productId)
    {
        try {
            return $this->productRepository->getById($productId);
        } catch (\Exception $exception) {
            return '';
        }
    }

    /**
     * Compare Version with 2.3.0
     *
     * @return bool
     */
    public function checkVersion()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        $msiEnable = $this->moduleManager->isOutputEnabled('Magento_Inventory');
        if (!$msiEnable) {
            return true;
        }
        return version_compare($magentoVersion, '2.3.0', '<');
    }

    /**
     * Check Stock Status Product
     *
     * @param int $productId
     * @return bool|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsInStock($productId)
    {
        if ($this->checkVersion()) {
            return $this->stockItemRepository->getStockItem($productId, $this->getStoreId())->getIsInStock();
        }
        return $this->productRepository->getById($productId)->isAvailable();
    }

    /**
     * @param $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->getStockItem($productId, $this->getStoreId());
    }

    /**
     * Get Store Id
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return 0;
        }
        return $this->storeManager->getStore()->getId();
    }

    /**
     * Is Enable Module
     * @param int|null $storeId
     * @return bool
     */
    public function isEnable($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            'preorder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Is Available to Pre order
     *
     * @param int $productId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isAvailablePreOrder($productId)
    {
        $fromDateStr = $this->resourceProduct->getAttributeRawValue(
            $productId,
            'pre_oder_from_date',
            $this->getStoreId()
        );
        $toDateStr = $this->resourceProduct->getAttributeRawValue(
            $productId,
            'pre_oder_to_date',
            $this->getStoreId()
        );
        return $this->isAvailablePreOrderFromFlatData($fromDateStr, $toDateStr);
    }

    /**
     * @param string $fromDateStr
     * @param string $toDateStr
     * @return bool
     */
    public function isAvailablePreOrderFromFlatData($fromDateStr, $toDateStr)
    {
        $fromDate = $fromDateStr ? strtotime($fromDateStr) : false;
        $toDate = $toDateStr ? strtotime($toDateStr) : false;
        $currentDate = strtotime($this->timezone->date()->format('Y-m-d'));
        return $this->checkisAvailableDate($fromDate, $toDate, $currentDate);
    }

    /**
     * @param string $fromDate
     * @param string $toDate
     * @param string $currentDate
     * @return bool
     */
    private function checkisAvailableDate($fromDate, $toDate, $currentDate)
    {
        if ((!$fromDate && !$toDate) || ($currentDate <= $toDate && $currentDate >= $fromDate) ||
            ($currentDate <= $toDate && !$fromDate) || ($currentDate >= $fromDate && !$toDate)
        ) {
            return true;
        }
        return false;
    }

    /**
     * @param int $productId
     * @param null $storeId
     * @return array|bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPreOrderFromDate($productId, $storeId = null)
    {
        $storeId = $storeId ? $storeId : $this->getStoreId();
        $fromDateStr = $this->resourceProduct->getAttributeRawValue(
            $productId,
            'pre_oder_from_date',
            $storeId
        );
        return $fromDateStr ? $this->formatDate($fromDateStr) : '';
    }

    /**
     * @param int $productId
     * @param null $storeId
     * @return array|bool|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getPreOrderToDate($productId, $storeId = null)
    {
        $storeId = $storeId ? $storeId : $this->getStoreId();
        $toDateStr = $this->resourceProduct->getAttributeRawValue(
            $productId,
            'pre_oder_to_date',
            $storeId
        );
        return $toDateStr ? $this->formatDate($toDateStr) : '';
    }

    /**
     * Is Mixed Order
     *
     * @param int|null $storeId
     * @return bool
     */
    public function isMix($storeId = null)
    {
        return $this->isEnable() && $this->scopeConfig->isSetFlag(
            'preorder/general/mix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Button Html Text
     *
     * @param int|null $storeId
     * @return string
     */
    public function getButton($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'preorder/general/button',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Message Pre Order
     *
     * @param int|null $storeId
     * @return string
     */
    public function getMess($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'preorder/general/mess',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Note Pre Order
     *
     * @param int|null $storeId
     * @return string
     */
    public function getNote($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'preorder/general/note',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get Display Out Of Stock With Pre-Order Only
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getDisplayOutOfStock($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::DISPLAY_OOS_PATH_CONFIG,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return bool
     */
    public function isDisplayOutOfStockProduct()
    {
        $displayOutOfStock = $this->getDisplayOutOfStock();
        $displayOutOfStockCore = $this->scopeConfig->getValue(
            \Magento\CatalogInventory\Model\Configuration::XML_PATH_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if ($displayOutOfStockCore && $displayOutOfStock) {
            return true;
        }
        return false;
    }

    /**
     * Format Date
     *
     * @param string $date
     * @param int $format
     * @param bool $showTime
     * @param string $timezone
     * @param string $pattern
     * @return bool|string
     */
    public function formatDate(
        $date,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null,
        $pattern = 'd MMM Y'
    ) {
        if ($date && strtotime($date)) {
            return $this->timezone->formatDateTime(
                $date,
                $format,
                $showTime ? $format : \IntlDateFormatter::NONE,
                null,
                $timezone,
                $pattern
            );
        }

        return false;
    }

    /**
     * Get Mess
     *
     * @param string $mess
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return mixed
     */
    public function replaceVariableX($mess, $fromDate, $toDate)
    {
        $preOrderDate = '';
        $fromDate = $fromDate !== null ? $fromDate : '';
        $toDate = $toDate !== null ? $toDate : '';
        $mess = $mess !== null ? $mess : '';

        if (trim($fromDate) != '' && trim($toDate) != '') {
            $preOrderDate = __('from %1 to %2', $fromDate, $toDate);
        } elseif (trim($fromDate) != '' && trim($toDate) == '') {
            $preOrderDate = __('from %1', $fromDate);
        } elseif (trim($fromDate) == '' && trim($toDate) != '') {
            $preOrderDate = __('to %1', $toDate);
        }
        return str_replace(["{preorder_date}"], [$preOrderDate], $mess);
    }

    /**
     * Check Is Pre Order
     *
     * @param bool $preOrder
     * @param bool $isInStock
     * @param bool $parentStockCheck
     * @param bool $availability
     * @return bool
     */
    public function isPreOrder($preOrder, $isInStock, $availability = true, $parentStockCheck = true)
    {
        if (($preOrder == self::ORDER_YES && $availability) || ($preOrder == self::ORDER_OUT_OF_STOCK && !$isInStock)
            || !$parentStockCheck) {
            return true;
        }

        return false;
    }

    /**
     * Get Availability Message
     *
     * @param mixed $product
     * @return mixed
     */
    public function getAvailabilityMessage($product)
    {
        $mess = $product->getData('availability_message');
        $preOrderFromDate = $this->formatDate($product->getData('pre_oder_from_date'));
        $preOrderToDate = $this->formatDate($product->getData('pre_oder_to_date'));

        return $this->replaceVariableX($mess, $preOrderFromDate, $preOrderToDate);
    }

    /**
     * @param string $message
     * @param string $preOrderFromDate
     * @param string $preOrderToDate
     * @return mixed
     */
    public function getAvailMessageFromFlatData($message, $preOrderFromDate, $preOrderToDate)
    {
        return $this->replaceVariableX($message, $preOrderFromDate, $preOrderToDate);
    }

    /**
     * Check product is configurable grid view table
     *
     * @return bool
     */
    public function checkProductConfigurableGridView()
    {
        $currentProduct = $this->registry->registry('current_product');
        if ($currentProduct && !$currentProduct->getDisableGridTableView() && $this->isEnabledModuleCPGridView()) {
            return true;
        }
        return false;
    }

    /**
     * Check is enable Module CPGridView
     *
     * @return bool
     */
    public function isEnabledModuleCPGridView()
    {
        $config = $this->scopeConfig->getValue(
            self::ENABLE_MODULE_CONFIGURABLE_GRID_VIEW,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $installModule = $this->_moduleManager->isEnabled('Bss_ConfiguableGridView');
        if ($config && $installModule) {
            return true;
        }
        return false;
    }

    /**
     * Get message and add to tooltip
     *
     * @param int $productId
     * @return mixed|string
     */
    public function getAvailabilityMessageByPid($productId)
    {
        $product = $this->productFactory->create()->load($productId);
        if ($product->getSku()) {
            $message = $this->replaceVariableX(
                $product->getMessage(),
                $this->formatDate($product->getPreOderFromDate()),
                $this->formatDate($product->getPreOderToDate())
            );
            if ($message == "") {
                $message = $this->replaceVariableX(
                    $this->getMess(),
                    $this->formatDate($product->getPreOderFromDate()),
                    $this->formatDate($product->getPreOderToDate())
                );
            }
            return $message;
        }
        return '';
    }

    /**
     * @param $product
     * @param $requestInfo
     * @param false $needCheck
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function checkPreOrderItem($product, $requestInfo, $needCheck = false)
    {
        $isPreOrderItem = $this->request->getParam('is_preorder');
        if ($needCheck || !$isPreOrderItem) {
            $isPreOrderItem = $this->getPreOrder($product->getId()) == 1
                && $this->isAvailablePreOrder($product->getId());
        }

        $qtyOrder = isset($requestInfo['qty']) ? $requestInfo['qty'] : 1;
        $productId = $this->getProductId($requestInfo, $product);
        if (!$isPreOrderItem && $productId) {
            if ($product->getTypeId() == Configurable::TYPE_CODE) {
                $product = $this->getProductById($productId);

                if (!isset($requestInfo['super_attribute'])) {
                    throw new LocalizedException($this->configurable->getSpecifyOptionMessage());
                }

                $product = $this->configurable->getProductByAttributes(
                    $requestInfo['super_attribute'],
                    $product
                );
                $productId = $product->getId();
            }
            $preOrderProduct = $this->getPreOrder($productId);
            $qtyProduct = $this->getProductSalableQty($product, $productId);
            if ($preOrderProduct == 2 && $qtyOrder > $qtyProduct) {
                $isPreOrderItem = 1;
            }
        }
        return [
            'isPreOrderItem' => (bool)$isPreOrderItem,
            'qtyOrder' => $qtyOrder,
            'productId' => $productId
        ];
    }

    /**
     * Get product Id
     *
     * @param array $requestInfo
     * @param mixed $product
     * @return int
     */
    private function getProductId($requestInfo, $product)
    {
        return isset($requestInfo['product']) ? $requestInfo['product'] : $product->getId();
    }

    /**
     * Validate Request Product With Items In Cart
     *
     * @param array $cartItems
     * @param array $preOrderItem
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateWithCart($cartItems, $preOrderItem)
    {
        $typeConfi = Configurable::TYPE_CODE;
        $isPreOrderItem = $preOrderItem['isPreOrderItem'];
        foreach ($cartItems as $item) {
            $productId = $item->getProduct()->getId();
            $product = $item;
            if ($item->getProduct()->getTypeId() == $typeConfi) {
                $requestInfo =$item->getBuyRequest();
                $product = $this->getProductById($productId);
                $product = $this->configurable->getProductByAttributes(
                    $requestInfo['super_attribute'],
                    $product
                );
                $productId = $product->getId();
                $preOrderProduct = $this->getPreOrder($productId);
                $qtyProduct = $this->getProductSalableQty($product, $productId);
                if (!$isPreOrderItem && $preOrderItem['productId'] == $productId
                    && ($preOrderItem['qtyOrder'] +$requestInfo['qty'] > $qtyProduct)) {
                    $this->preOrderItem = true;
                    continue;
                }
                if ($preOrderProduct == 2 && $requestInfo['qty'] > $qtyProduct) {
                    $this->isPreOrderCart($isPreOrderItem, 1);
                    $this->preOrderCartItem = true;
                    continue;
                }
            }
            $isPreOrderCart = $this->checkPreOrderCartItem($product, $productId, $preOrderItem);
            if ($productId == $preOrderItem['productId']) {
                continue;
            }
            $this->isPreOrderCart($isPreOrderItem, $isPreOrderCart);
        }
        $this->checkDisplayMessage();
    }

    /**
     * @param $productIds
     * @param $cartItems
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function validateAddRelatedProduct($productIds, $cartItems)
    {
        foreach ($productIds as $productId) {
            $productId = (int) $productId;
            if (!$productId) {
                continue;
            }
            $currentProduct = $this->getProductById($productId);
            if ($currentProduct && $currentProduct->isVisibleInCatalog()) {
                $stockItem = $this->getStockItem(
                    $productId,
                    $this->storeManager->getStore()->getWebsiteId()
                );
                $qty = 1;
                $minQty = $stockItem->getMinSaleQty();
                if ($minQty && $minQty > 0) {
                    $qty = $minQty;
                }
            }
            $requestInfo = [
                'qty' => $qty,
                'product' => $currentProduct->getId()
            ];
            $preOrderItem = $this->checkPreOrderItem($currentProduct, $requestInfo, true);
            $this->validateWithCart($cartItems, $preOrderItem);
        }
    }

    /**
     * Validate For Group Product
     *
     * @param array $cartItems
     * @param array $requestInfo
     * @throws LocalizedException
     */
    public function validateForGroupProduct($cartItems, $requestInfo)
    {
        $countNormalProduct = $countPreOrder = 0;
        foreach ($requestInfo['super_group'] as $key => $value) {
            if ($value) {
                if (!empty($cartItems)) {
                    /* Validate with product in Cart */
                    $preOrderItem = $this->checkPreOrderGroup($key, $value);
                    $this->validateWithCart($cartItems, $preOrderItem);
                } else {
                    /* Validate with other child product in request if Cart no items */
                    $isPreOrderItem = $this->checkPreOrderGroup($key, $value)['isPreOrderItem'];
                    if ($isPreOrderItem) {
                        $countPreOrder++;
                    } else {
                        $countNormalProduct++;
                    }
                }
            }
        }
        if (empty($cartItems)) {
            if ($countNormalProduct && $countPreOrder) {
                $this->returnErrorMess();
            }
        }
    }

    /**
     * @throws LocalizedException
     */
    private function checkDisplayMessage()
    {
        if ($this->preOrderItem && $this->preOrderCartItem) {
            $this->preOrderItem = false;
            $this->preOrderCartItem = false;
            $this->returnErrorMess();
        }
    }

    /**
     * Validate
     *
     * @param bool $isPreOrderItem
     * @param bool $isPreOrderCart
     * @throws LocalizedException
     */
    protected function isPreOrderCart($isPreOrderItem, $isPreOrderCart)
    {
        if (($isPreOrderItem && !$isPreOrderCart) || (!$isPreOrderItem && $isPreOrderCart)) {
            $this->returnErrorMess();
        }
    }

    /**
     * Return Error Message
     *
     * @throws LocalizedException
     */
    protected function returnErrorMess()
    {
        $message = "We could not add both pre-order and regular items to an order.";
        throw new LocalizedException(__($message));
    }

    /**
     * @param mixed $item
     * @param int $productId
     * @param array $preOrderItem
     * @return bool
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function checkPreOrderCartItem($item, $productId, $preOrderItem)
    {
        $preOrderCart = $this->getPreOrder($productId);
        $inStockCart = $this->getIsInStock($productId);
        $availabilityPreOrder = $this->isAvailablePreOrder($productId);
        $isPreOrderCart = $this->isPreOrder($preOrderCart, $inStockCart, $availabilityPreOrder);
        if ($inStockCart && $preOrderCart == 2) {
            $qtyProduct = $this->getProductSalableQty($item, $productId);
            if ($item->getQty() > $qtyProduct) {
                $isPreOrderCart = true;
            }
            $isPreOrderItem = $preOrderItem['isPreOrderItem'];
            if (!$isPreOrderItem && $preOrderItem['productId'] == $productId
                && ($preOrderItem['qtyOrder'] + $item->getQty() > $qtyProduct)) {
                $this->preOrderItem = true;
            }
        }
        if (!$isPreOrderCart) {
            $this->preOrderCartItem = true;
        }
        return $isPreOrderCart;
    }

    /**
     * @param int $key
     * @param float $value
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function checkPreOrderGroup($key, $value)
    {
        $isPreOrderItem = $this->request->getParam('is_preorder_group_' . $key);
        $productId = $key;
        if (!$isPreOrderItem) {
            $product = $this->getProductById($key);
            $preOrderProduct = $this->getPreOrder($productId);
            $qtyProduct = $this->getProductSalableQty($product, $productId);
            if ($preOrderProduct == 2 && $value > $qtyProduct) {
                $isPreOrderItem = 1;
            }
        }
        return [
            'isPreOrderItem' => (bool)$isPreOrderItem,
            'qtyOrder' => $value,
            'productId' => $productId
        ];
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Check if product can be pre-ordered
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param null|\Magento\Quote\Model\Quote\Item $item
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @return bool
     */
    public function checkPreOrderAvailability($product, $item = null)
    {
        $isInStock = $product->getData('is_salable');
        $preOrder = $product->getData('preorder');
        $fromDate =  $this->getPreOrderFromDate($product->getId());
        $toDate =  $this->getPreOrderToDate($product->getId());
        if (
            (
                $preOrder ==  SourceOrder::ORDER_YES
                && $this->isAvailablePreOrderFromFlatData($fromDate, $toDate)
            )
            ||
            (
                $preOrder == SourceOrder::ORDER_OUT_OF_STOCK &&
                    $isInStock == 0 || ($item && $this->checkQtyItemProduct($product, $item))
            )
        ) {
            return true;
        }
        return false;
    }

    /**
     * Check qty salable product with qty of item
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return bool
     */
    public function checkQtyItemProduct($product, $item)
    {
        $productSalableQty = $this->getProductSalableQty(
            $product,
            $product->getId()
        );
        if ($item->getQty() > $productSalableQty || ($item->getQtyOrdered() && $productSalableQty < 0)) {
            return true;
        }
        return false;
    }
}

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
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Bss\PreOrder\Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends AbstractHelper
{
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
     * @var \Magento\Framework\App\ProductMetadataInterface
     */
    protected $productMetadata;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
     * @param \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository
     * @param \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository,
        \Bss\PreOrder\Model\ResourceModel\PreOrder $preOrderResource,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata
    ) {
        parent::__construct($context);
        $this->stockItemRepository = $stockItemRepository;
        $this->registry = $registry;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->timezone = $timezone;
        $this->preOrderResource = $preOrderResource;
        $this->productMetadata = $productMetadata;
    }

    /**
     * @param $productId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPreOrder($productId)
    {
        return $this->preOrderResource->getPreOrder($productId);
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductItem($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * @param string $sku
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws
     */
    public function getProductItemBySku($sku)
    {
        return $this->productRepository->get($sku);
    }

    public function checkVersion()
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.0', '<')) {
            return false;
        }
        return true;
    }

    /**
     * @param int $productId
     * @return bool|int
     */
    public function getIsInStock($productId)
    {
        $magentoVersion = $this->productMetadata->getVersion();
        if (version_compare($magentoVersion, '2.3.0', '<')) {
            return $this->stockItemRepository->getStockItem($productId, $this->getStoreId())->getIsInStock();
        }

        return $this->productRepository->getById($productId)->isAvailable();
    }

    /**
     * @param int $productId
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     */
    public function getStockItem($productId)
    {
        return $this->stockItemRepository->getStockItem($productId, $this->getStoreId());
    }

    /**
     * @return int
     * @throws
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->scopeConfig->isSetFlag(
            'preorder/general/enable',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return bool
     */
    public function isMix()
    {
        return $this->scopeConfig->isSetFlag(
            'preorder/general/mix',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getButton()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/button',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getMess()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/mess',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getNote()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/note',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getCartMess()
    {
        return $this->scopeConfig->getValue(
            'preorder/general/cartmess',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param \Magento\Framework\Stdlib\DateTime $date
     * @param int $format
     * @param bool $showTime
     * @param null $timezone
     * @param string $pattern
     * @return string
     */
    public function formatDate(
        $date,
        $format = \IntlDateFormatter::SHORT,
        $showTime = false,
        $timezone = null,
        $pattern = 'd MMMM   Y'
    ) {
        if ($date) {
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
     * @param $mess
     * @param $restock
     * @return mixed
     */
    public function replaceVariableX($mess, $restock)
    {
        $mess = str_replace(
            "{date}",
            $restock,
            $mess
        );
        return $mess;
    }

    /**
     * @param $preOrder
     * @param $isInStock
     * @param $parentStockCheck
     * @return bool
     */
    /*
    public function isPreOrder($preOrder, $isInStock, $parentStockCheck = true)
    {
        if (($preOrder == Order::ORDER_YES || ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)
            || !$parentStockCheck)) {
            return true;
        }
        return false;
    }
    */

    // Modified check to ensure Pre Order only active for products in stock
    public function isPreOrder($preOrder, $isInStock, $parentStockCheck = true)
    {
        if ((($preOrder == Order::ORDER_YES && $isInStock) || ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)
            || !$parentStockCheck)) {
            return true;
        }
        return false;
    }
}

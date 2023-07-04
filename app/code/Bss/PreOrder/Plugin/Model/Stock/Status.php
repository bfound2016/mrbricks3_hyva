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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Model\Stock;

use Bss\PreOrder\Model\Attribute\Source\Order;

class Status
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    protected $productHelper;

    /**
     * @var \Bss\PreOrder\Model\Factory
     */
    protected $multiSourceInventory;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $_moduleManager;

    /**
     * @var \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface
     */
    protected $stockItemRepository;

    /**
     * Status constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Bss\PreOrder\Helper\ProductData $productHelper
     * @param \Bss\PreOrder\Model\Factory $multiSourceInventory
     * @param \Magento\Framework\Module\Manager $_moduleManager
     * @param \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Bss\PreOrder\Helper\ProductData $productHelper,
        \Bss\PreOrder\Model\Factory $multiSourceInventory,
        \Magento\Framework\Module\Manager $_moduleManager,
        \Magento\CatalogInventory\Model\Spi\StockRegistryProviderInterface $stockItemRepository
    ) {
        $this->helper = $helper;
        $this->productHelper = $productHelper;
        $this->multiSourceInventory = $multiSourceInventory;
        $this->stockItemRepository = $stockItemRepository;
        $this->_moduleManager = $_moduleManager;
    }

    /**
     * Get Stock Status
     *
     * @param \Magento\CatalogInventory\Model\Stock\Status $subject
     * @param string $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function afterGetStockStatus($subject, $result)
    {
        $productId = $subject->getData('product_id');
        $sku = $subject->getData('sku');

        if ($this->helper->isEnable() && !$result && $productId) {
            if ($subject->getData('type_id') == 'configurable') {
                return true;
            } else {
                $preOrder = $this->helper->getPreOrder($productId);
                $isInStock = $this->getIsInStock($productId, $sku);
                if (($preOrder == Order::ORDER_YES && $this->helper->isAvailablePreOrder($productId)) ||
                    ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
                    return true;
                }
            }
        }
        return $result;
    }

    /**
     * Check Stock Status Product
     *
     * @param int $productId
     * @return bool|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getIsInStock($productId, $sku)
    {
        $enableMSI = $this->_moduleManager->isEnabled('Magento_InventorySalesApi');
        if ($this->helper->checkVersion() || !$enableMSI) {
            return $this->stockItemRepository->getStockItem($productId, $this->helper->getStoreId())->getIsInStock();
        }
        return $this->multiSourceInventory->getSalableQtyBySource($sku);
    }
}

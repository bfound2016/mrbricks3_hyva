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
namespace Bss\PreOrder\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Stock
 */
class Stock
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var mixed
     */
    protected $getProductSalableQty;

    /**
     * @var mixed
     */
    protected $stockResolver;

    /**
     * Object Manager
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * Construct
     *
     * @param StoreManagerInterface $storeManager
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->storeManager = $storeManager;
        $this->objectManager = $objectManager;
    }

    /**
     * Get salable qty by stock current website
     *
     * @param string $sku
     * @return int|float
     */
    public function getProductSalableQty($sku)
    {
        try {
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $stock = $this->createStockResolver()->execute(\Magento\InventorySalesApi\Api\Data\SalesChannelInterface::TYPE_WEBSITE, $websiteCode);
            $stockId = $stock->getStockId();
            return $this->createGetProductSalableQty()->execute($sku, $stockId);
        } catch (\Exception $exception) {
            return 0;
        }
    }

    /**
     * Create object Magento\InventorySales\Model\GetProductSalableQty
     *
     * @return mixed
     */
    public function createGetProductSalableQty()
    {
        if (!$this->getProductSalableQty) {
            $this->getProductSalableQty = $this->objectManager->create(\Magento\InventorySales\Model\GetProductSalableQty::class);
        }
        return $this->getProductSalableQty;
    }

    /**
     * Create object: Magento\InventorySalesApi\Api\StockResolverInterface
     *
     * @return mixed
     */
    public function createStockResolver()
    {
        if (!$this->stockResolver) {
            $this->stockResolver = $this->objectManager->create(\Magento\InventorySalesApi\Api\StockResolverInterface ::class);
        }
        return $this->stockResolver;
    }
}

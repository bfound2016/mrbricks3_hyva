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
namespace Bss\PreOrder\Plugin\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class StockStatusBaseSelectProcessor
 */
class StockStatusBaseSelectProcessor
{
    const BSS_C_P_E_I = "bss_c_p_e_i";

    /**
     * @var \Bss\PreOrder\Model\MinPriceConfigurable
     */
    protected $minPriceConfigurable;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\PreOrder\Model\Attribute\Source\Module
     */
    protected $sourceModule;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param \Bss\PreOrder\Helper\Data $helperData
     * @param \Bss\PreOrder\Model\Attribute\Source\Module $sourceModule
     * @param StockConfigurationInterface $stockConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        \Bss\PreOrder\Model\MinPriceConfigurable $minPriceConfigurable,
        \Bss\PreOrder\Helper\Data $helperData,
        \Bss\PreOrder\Model\Attribute\Source\Module $sourceModule,
        StockConfigurationInterface $stockConfig,
        StoreManagerInterface $storeManager
    ) {
        $this->minPriceConfigurable = $minPriceConfigurable;
        $this->helperData = $helperData;
        $this->sourceModule = $sourceModule;
        $this->stockConfig = $stockConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * Lowest price when config preorder
     * Improves the select with stock status sub query.
     *
     * @param \Magento\InventoryConfigurableProduct\Pricing\Price\LowestPriceOptionsProvider\StockStatusBaseSelectProcessor $subject
     * @param callable $proceed
     * @param Select $select
     * @return Select
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundProcess($subject, callable $proceed, Select $select)
    {
        if (!$this->helperData->isEnable()) {
            return $proceed($select);
        }

        if (!$this->stockConfig->isShowOutOfStock()) {
            return $select;
        }

        $stockId = $this->minPriceConfigurable->getStockId();
        if ($stockId === $this->minPriceConfigurable->getDefaultStockProviderId()) {
            $stockTable = $this->minPriceConfigurable->getStockTableDefault();
            $isSalableColumnName = 'stock_status';

            /** @var Select $select */
            $select->join(
                ['stock' => $stockTable],
                sprintf(
                    'stock.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            );
        } else {
            $stockTable = $this->minPriceConfigurable->getStockTable($stockId);
            $isSalableColumnName = \Magento\InventoryIndexer\Indexer\IndexStructure::IS_SALABLE;

            /** @var Select $select */
            $select->join(
                ['stock' => $stockTable],
                sprintf('stock.sku = %s.sku', BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS),
                []
            );
        }
        return $this->minPriceConfigurable->handlePreOrder($select, $isSalableColumnName);
    }

}

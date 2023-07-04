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
namespace Bss\PreOrder\Plugin\ConfigurableProduct\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\BaseSelectProcessorInterface;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Status as StockStatusResource;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\InventoryIndexer\Model\StockIndexTableNameResolverInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
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
     * @var StockStatusResource
     */
    private $stockStatusResource;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Bss\PreOrder\Model\Attribute\Source\Module
     */
    protected $sourceModule;

    /**
     * @var StockIndexTableNameResolverInterface
     */
    private $stockIndexTableNameResolver;

    /**
     * @var StockConfigurationInterface
     */
    private $stockConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param \Bss\PreOrder\Model\MinPriceConfigurable $minPriceConfigurable
     * @param ResourceConnection $resourceConnection
     * @param StockConfigurationInterface $stockConfig
     * @param \Bss\PreOrder\Helper\Data $helperData
     * @param \Bss\PreOrder\Model\Attribute\Source\Module $sourceModule
     * @param StockStatusResource $stockStatusResource
     */
    public function __construct(
        \Bss\PreOrder\Model\MinPriceConfigurable $minPriceConfigurable,
        ResourceConnection $resourceConnection,
        StockConfigurationInterface $stockConfig,
        \Bss\PreOrder\Helper\Data $helperData,
        \Bss\PreOrder\Model\Attribute\Source\Module $sourceModule,
        StockStatusResource $stockStatusResource
    ) {
        $this->minPriceConfigurable = $minPriceConfigurable;
        $this->resourceConnection = $resourceConnection;
        $this->stockConfig = $stockConfig;
        $this->helperData = $helperData;
        $this->sourceModule = $sourceModule;
        $this->stockStatusResource = $stockStatusResource;
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

        if ($this->stockConfig->isShowOutOfStock()) {
            $select->joinInner(
                ['stock' => $this->stockStatusResource->getMainTable()],
                sprintf(
                    'stock.product_id = %s.entity_id',
                    BaseSelectProcessorInterface::PRODUCT_TABLE_ALIAS
                ),
                []
            );
        }

        return $this->minPriceConfigurable->handlePreOrder($select, "stock_status");
    }
}

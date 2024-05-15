<?php

namespace Addwish\Awext\Plugin\Model\ResourceModel\Stock;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;

//use Magento\Framework\Model\AbstractModel as StockItem;
use Magento\CatalogInventory\Model\Stock\Item as StockItem;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as StockItemResource;

class StockItemUpdate {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @var DeltaItemHelper
     */
    private $deltaItemHelper;

    /**
     * @param DeltaItemHelper $deltaItemHelper
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper,
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->configHelper = $configHelper;
        $this->deltaItemHelper = $deltaItemHelper;
    }

    public function afterSave(StockItemResource $stockItemResource, StockItemResource $result = null, StockItem $stockItem) {
        try {
            if ($this->configHelper->isDeltaItemsUpdatedByStockChanges()) {
                // these are the fields magento uses to determine if a product should be reindexed on save.
                // notice they dont do it on qty changes - we could do the same if we find the current implementation too taxing.
                /*
                $fields = [
                    'is_in_stock',
                    'use_config_manage_stock',
                    'manage_stock',
                ];
                foreach ($fields as $field) {
                    if ($stockItem->dataHasChangedFor($field)) {
                        $stockItemResource->addCommitCallback(function () use ($stockItem) {
                            $this->deltaItemHelper->updateDeltaItemsByIds([$stockItem->getProductId()]);
                        });
                    }
                }
                */
                $stockItemResource->addCommitCallback(function () use ($stockItem) {
                    $this->deltaItemHelper->updateDeltaItemsByIds([$stockItem->getProductId()]);
                });
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }

    public function afterDelete(StockItemResource $stockItemResource, StockItemResource $result = null, StockItem $stockItem) {
        try {
            if ($this->configHelper->isDeltaItemsUpdatedByStockChanges()) {
                $stockItemResource->addCommitCallback(function () use ($stockItem) {
                    $this->deltaItemHelper->updateDeltaItemsByIds([$stockItem->getProductId()]);
                });
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }

        return $result;
    }
    
}

<?php
namespace Addwish\Awext\Observer;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class IndexUpdateObserver implements ObserverInterface {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    public function execute(Observer $observer) {
        try {
            $event = $observer->getEvent();
            if (!$event) {
                return;
            }
            // This event is triggered every time an index changes status - status can be valid, (working?) and invalid.
            // We are only interested in the cases where an index changes from invalid, 
            // as it indicates the index is valid agian or about to be.
            // We are currently also only interested in some of the indexes.
            // These would mainly be triggered if a full reindex is happening, not when a single product entry is reindexed.
            // https://devdocs.magento.com/guides/v2.4/config-guide/cli/config-cli-subcommands-index.html

            // "catalogrule_product" is triggered each time a catalog rule is applied to products in bulk.
            // technically it is possible to get only the affected products and therefor do a proper delta
            // instead of a full feed sync. We will do that at a later stage.

            // "catalog_category_flat" is triggered if a category's products are changed from the category edit.
            // among other situations as well.
            // we are currently attempting to catch the affected products in our category plugin/interceptor.

            // "catalog_product_flat" we might want to consider this, together with "catalog_category_flat"
            // if it turns out we have issues with flat tables.

            $index_ids = array(
                "catalog_product_price", 
                "cataloginventory_stock",
                "catalogrule_product",
                "inventory"
            );
            $origData = $event->getData("data_object")->getOrigData();
            if (in_array($origData["indexer_id"], $index_ids) && $origData["status"] === "invalid") {
                $this->configHelper->setLastIndexUpdateTime(time());
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }

}
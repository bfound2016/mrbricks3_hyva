<?php

namespace Addwish\Awext\Plugin\Model\Product;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Catalog\Model\Product\Action;

class ProductBulkUpdate {
    /**
     * @var DeltaItemHelper
     */
    private $deltaItemHelper;

    /**
     * @param DeltaItemHelper $deltaItemHelper
     */
    public function __construct(
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->deltaItemHelper = $deltaItemHelper;
    }

    public function afterUpdateAttributes(Action $subject, Action $result = null, $productIds) {
        try {
            $this->deltaItemHelper->updateDeltaItemsByIds($productIds);
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }

    public function afterUpdateWebsites(Action $subject, Action $result = null, array $productIds) {
        try {
            $this->deltaItemHelper->updateDeltaItemsByIds($productIds, false);
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }
}

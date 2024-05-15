<?php

namespace Addwish\Awext\Plugin\Model\ResourceModel\Product;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ResourceModel\Product as ProductResource;

class ProductUpdate {
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

    public function afterSave(ProductResource $productResource, ProductResource $result, Product $product) {
        try {
            $productResource->addCommitCallback(function () use ($product) {
                $this->deltaItemHelper->updateDeltaItems([$product]);
            });
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }

    public function afterDelete(ProductResource $productResource, ProductResource $result, Product $product) { 
        try {
            $productResource->addCommitCallback(function () use ($product) {
                $this->deltaItemHelper->updateDeltaItems([$product]);
            });
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }
}

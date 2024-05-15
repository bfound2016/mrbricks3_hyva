<?php

namespace Addwish\Awext\Observer;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Catalog\Model\Category;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

// Note: category save() function is not triggered when executing the category move() function.
// That is why we need this observer, even though we have the category save() plugin.

class CategoryMoveObserver implements ObserverInterface {
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

    public function execute(Observer $observer) {
        try {
            $event = $observer->getEvent();
                if (!$event) {
                    return;
                }
            $category = $observer->getEvent()->getCategory();
            // here we are checking that the path was changed.
            // if the path changed, then the categoryIds and the hierarchies
            // of the related products will be updated.
            if ($category->getOrigData("path") !== $category->getData("path")) {
                // getProductsPosition returns array($productId => $position)
                // getProductCollection() filters on current store.
                // if we want to save deltaItem pr store, we should use 
                // $productIds = (array) $category->getProductCollection()->getColumnValues("entity_id")
                $productIds = array_keys($category->getProductsPosition());
                $productIds = array_unique($this->getProductIdsFromSubCategories($category, $productIds));
                $this->deltaItemHelper->updateDeltaItemsByIds($productIds);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }

    // recursive helper to get all sub categories and their product ids.
    private function getProductIdsFromSubCategories(Category $category, $productIds = []) {
        try {
            $subCategories = $category->getChildrenCategories();
            foreach ($subCategories as $subCategory) {
                $productIds = array_merge(array_keys($subCategory->getProductsPosition()), $productIds);
                $productIds = $this->getProductIdsFromSubCategories($subCategory, $productIds);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        
        return $productIds;
    }

}

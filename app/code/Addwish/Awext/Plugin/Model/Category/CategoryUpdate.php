<?php

namespace Addwish\Awext\Plugin\Model\Category;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\ResourceModel\Category as CategoryResourceModel;

class CategoryUpdate {
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

    // this is the plugin function. 
    public function afterSave(CategoryResourceModel $categoryResource, CategoryResourceModel $result, Category $category) {
        $categoryResource->addCommitCallback(function () use ($category) {
            $this->handleProductsOnCategorySave($category);
        });

        return $result;
    }

    // our own helper
    private function handleProductsOnCategorySave(Category $category) {
        try {
            $productIds = [];
            // if name, status or path of the category changed,
            // we want to update all products in the category, 
            // as it would mean an update to the products' hierarchies.
            if ($category->getOrigData("name") !== $category->getData("name")
                    || $category->getOrigData("is_active") !== $category->getData("is_active")
                    || $category->getOrigData("path") !== $category->getData("path")) {
                // getProductsPosition returns array($productId => $position)
                // getProductCollection() filters on current store.
                // if we want to save deltaItem pr store, we should use 
                // $productIds = (array) $category->getProductCollection()->getColumnValues("entity_id")
                $productIds = array_keys($category->getProductsPosition());
                // same goes for all products in the subCategories. even though this might get a bit heavy.
                $productIds = array_unique($this->getProductIdsFromSubCategories($category, $productIds));
            }
            // if the products in the category was changed in the save event, 
            // getChangedProductIds will return a list of the ids.
            // this will be null if only a category attribute like name was updated.
            if ($category->getChangedProductIds()) {
                $productIds = array_unique(array_merge($category->getChangedProductIds(), $productIds));
            }
            $this->deltaItemHelper->updateDeltaItemsByIds($productIds, false);
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return;
    }

    // this is the plugin function. 
    public function afterDelete(CategoryResourceModel $categoryResource, CategoryResourceModel $result, Category $category) {
        $categoryResource->addCommitCallback(function () use ($category) {
            $this->handleProductsOnCategoryDelete($category);
        });

        return $result;
    }

    // our own helper
    private function handleProductsOnCategoryDelete(Category $category) {
        try {
            $productIds = array_keys($category->getProductsPosition());
            $this->deltaItemHelper->updateDeltaItemsByIds($productIds, false);
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

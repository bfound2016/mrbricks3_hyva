<?php declare(strict_types = 1);

namespace Addwish\Awext\Plugin\Model\ResourceModel\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Store\Model\Store;
use Closure;

// This plugin makes it possible to filter the product collection on store id
// when using productRepository with searchCriteria.
// UPDATE; now its only here to support older versions of magento. 
class CollectionFilter {
    /**
     * Adding filtration by store_id. Supported 'eq' condition.
     *
     * @param ProductCollection $collection
     * @param Closure           $proceed
     * @param array             $fields
     * @param string|null       $condition
     *
     * @return ProductCollection
     */
    public function aroundAddFieldToFilter(ProductCollection $collection, Closure $proceed, $fields, $condition = null) {
        if (is_array($fields)) {
            foreach ($fields as $key => $filter) {
                if ($filter['attribute'] == Store::STORE_ID && isset($filter['eq'])) {
                    $collection->addStoreFilter($filter['eq']);
                    unset($fields[$key]);
                }
            }
        }

        if ($fields) {
            return $proceed($fields, $condition);
        }

        return $collection;
    }
}
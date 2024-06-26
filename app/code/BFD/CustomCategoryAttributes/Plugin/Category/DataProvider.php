<?php
namespace BFD\CustomCategoryAttributes\Plugin\Category;

class DataProvider
{
    public function afterGetData(\Magento\Catalog\Model\Category\DataProvider $subject, $result)
    {
        foreach ($result as &$category) {
            if (isset($category['category_logo'])) {
                $category['category_logo'] = 'catalog/category/' . $category['category_logo'];
            }
        }
        return $result;
    }
}
<?php
namespace BFD\CategoryBackgroundAttribute\Model\Category;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{

    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'category_background'; // custom image field

        return $fields;
    }
}
<?php
namespace BFD\CategoryLogoAttribute\Model\Category;

class DataProvider extends \Magento\Catalog\Model\Category\DataProvider
{

    protected function getFieldsMap()
    {
        $fields = parent::getFieldsMap();
        $fields['content'][] = 'category_logo'; // custom image field

        return $fields;
    }
}
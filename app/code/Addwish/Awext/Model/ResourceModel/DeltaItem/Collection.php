<?php declare(strict_types=1);

namespace Addwish\Awext\Model\ResourceModel\DeltaItem;

use Addwish\Awext\Model\DeltaItem\DeltaItem as DeltaItemModel;
use Addwish\Awext\Model\ResourceModel\DeltaItem as DeltaItemResourceModel;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection {
    protected function _construct() {
        $this->_init(
            DeltaItemModel::class,
            DeltaItemResourceModel::class
        );
    }
}
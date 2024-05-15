<?php declare(strict_types=1);

namespace Addwish\Awext\Model\DeltaItem;

use Addwish\Awext\Model\ResourceModel\DeltaItem as DeltaItemResourceModel;
use Magento\Framework\Model\AbstractModel;

class DeltaItem extends AbstractModel {
    protected function _construct() {
        $this->_init(DeltaItemResourceModel::class);
    }
}

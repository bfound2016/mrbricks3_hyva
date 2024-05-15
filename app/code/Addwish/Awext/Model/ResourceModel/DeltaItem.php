<?php declare(strict_types=1);

namespace Addwish\Awext\Model\ResourceModel;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class DeltaItem extends AbstractDb {
    protected function _construct() {
        $this->_init("hello_retail_delta", "id");
    }
}
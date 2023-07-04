<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model\ResourceModel\Order;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class Status extends AbstractDb
{
    /**
     * Inherit Docs
     */
    public function _construct()
    {
        $this->_init('sales_order_status', 'status');
    }

    /**
     * Delete PreOrder Status when uninstall Module by composer
     */
    public function deletePreOrderStatus()
    {
        $connection = $this->getConnection();
        $connection->delete(
            $this->getTable('sales_order_status'),
            ['status = ?' => "pending_preorder"]
        );
        $connection->delete(
            $this->getTable('sales_order_status'),
            ['status = ?' => "processing_preorder"]
        );
    }
}

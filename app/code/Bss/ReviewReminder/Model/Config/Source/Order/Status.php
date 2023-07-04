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
 * @package    Bss_ReviewReminder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\ReviewReminder\Model\Config\Source\Order;

use Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot;

class Status extends \Magento\Sales\Model\ResourceModel\Order\Status\Collection
{
    /**
     * To Option Array
     * @return array
     */
    public function toOptionArray()
    {
        $arrAllStatus = $this->_toOptionArray('status', 'label');
        $arrOptionsAccept = [
            'canceled', 'closed', 'complete', 'pending', 'processing'
        ];

        foreach ($arrAllStatus as $key => $value) {
            if (!in_array($value['value'], $arrOptionsAccept)) {
                unset($arrAllStatus[$key]);
            }
        }

        return $arrAllStatus;
    }
}

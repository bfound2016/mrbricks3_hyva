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

namespace Bss\ReviewReminder\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\ModuleContextInterface;

/**
 * Class UpgradeData
 * @package Bss\ReviewReminder\Setup
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();
        if (version_compare($context->getVersion(), '1.0.8', '<=')) {
            $configDataTable = $installer->getTable('core_config_data');
            $readAdapter = $installer->getConnection('core_read');
            $writeAdapter = $installer->getConnection('core_write');
            $select = $readAdapter->select()
                ->from($configDataTable)
                ->where("path = 'reviewreminder/coupon/choose'");
            $oldConfig = $readAdapter->fetchAll($select);
            foreach ($oldConfig as $row) {
                if ($row['value'] == 0) {
                    continue;
                }
                $data = [
                    'scope' => $row['scope'],
                    'scope_id' => $row['scope_id'],
                    'path' => 'reviewreminder/coupon/rule',
                    'value' => $row['value']
                ];
                $writeAdapter->insert(
                    $configDataTable,
                    $data
                );
                $writeAdapter->update(
                    $configDataTable,
                    ['value' => 1],
                    ['config_id = ?' => $row['config_id']]
                );
            }
        }
        $installer->endSetup();
    }
}

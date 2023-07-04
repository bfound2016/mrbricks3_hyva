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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
declare(strict_types=1);

namespace Bss\PreOrder\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class UpdateNotePreOrder implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Update attribute
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'message',
            [
                'note' => __("{preorder_date} can be used as the Pre-order Button Availability period.
                If this field is blank, the default message edited in the configuration will be displayed")
            ]
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'availability_message',
            [
                'note' => __("{preorder_date} can be used as the Pre-order Button Availability period.")
            ]
        );
    }

    /**
     * Dependent with v1.1.6
     */
    public static function getDependencies()
    {
        return [
            \Bss\PreOrder\Setup\Patch\Data\UpdateDataV116::class
        ];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}

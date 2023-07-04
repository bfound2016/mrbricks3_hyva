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

class UpdateAttribute implements DataPatchInterface
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
            'preorder',
            'source',
            \Bss\PreOrder\Model\Attribute\Source\Order::class
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'preorder',
            'used_in_product_listing',
            true
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'message',
            'used_in_product_listing',
            true
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'preorder',
            'apply_to',
            'simple'
        );

        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'message',
            'apply_to',
            'simple'
        );
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}

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

use Magento\Catalog\Model\Product;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Setup\EavSetupFactory;

class UpdateDataV118 implements DataPatchInterface
{
    const BSS_PRE_ORDER_TAB = 'Pre Order';
    const BSS_PRE_ORDER_ATTRIBUTES = [
        'preorder' => 10,
        'message' => 20,
        'availability_message' => 40,
        'pre_oder_to_date' => 50,
        'pre_oder_from_date' => 60
    ];
    /**
     * @var ModuleDataSetupInterface
     */
    private $setup;

    /**
     * @var EavSetupFactory
     */
    protected $eavSetupFactory;

    /**
     * @param ModuleDataSetupInterface $setup
     * @param EavSetupFactory $eavSetupFactory
     */
    public function __construct(
        ModuleDataSetupInterface $setup,
        EavSetupFactory          $eavSetupFactory
    ) {
        $this->setup = $setup;
        $this->eavSetupFactory = $eavSetupFactory;
    }

    /**
     * Update and remove attribute
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $arrSetUseListingAttribute = [
            'availability_message',
            'pre_oder_from_date',
            'pre_oder_to_date'
        ];
        // set use listing for attribute
        foreach ($arrSetUseListingAttribute as $attribute) {
            $eavSetup->updateAttribute(
                \Magento\Catalog\Model\product::ENTITY,
                $attribute,
                'used_in_product_listing',
                true
            );
        }
        //Assign attribute for Tab Pre Order
        $setup = $this->setup;
        $this->assignAttributeToTab($setup, $eavSetup);
        // Remove attribute Restock
        $eavSetup->removeAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'restock'
        );
        $eavSetup->updateAttribute(
            \Magento\Catalog\Model\product::ENTITY,
            'pre_oder_from_date',
            [
                'note' => __("This config only works when the product is set Pre-order: Yes")
            ]
        );
    }

    /**
     * Assign Attribute To Tab
     *
     * @param ModuleDataSetupInterface $setup
     * @param \Magento\EAV\Setup\EavSetup $eavSetup
     * @throws LocalizedException
     */
    protected function assignAttributeToTab($setup, $eavSetup)
    {
        $select = $setup->getConnection()->select()->from(
            $setup->getTable('eav_attribute_set')
        )->where(
            'entity_type_id = :entity_type_id'
        );
        $entityTypeId = $eavSetup->getEntityTypeId(Product::ENTITY);
        $sets = $setup->getConnection()->fetchAll($select, ['entity_type_id' => $entityTypeId]);
        foreach ($sets as $set) {
            if (!$eavSetup->getAttributeGroup(Product::ENTITY, $set['attribute_set_id'], self::BSS_PRE_ORDER_TAB)) {
                $eavSetup->addAttributeGroup($entityTypeId, $set['attribute_set_id'], self::BSS_PRE_ORDER_TAB);
            }
            foreach (self::BSS_PRE_ORDER_ATTRIBUTES as $code => $sortOrder) {
                $eavSetup->addAttributeToSet(
                    $entityTypeId,
                    $set['attribute_set_id'],
                    self::BSS_PRE_ORDER_TAB,
                    $code,
                    $sortOrder
                );
            }
        }
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

    /**
     * Compare ver module.
     *
     * @return string
     */
    public static function getVersion()
    {
        return '1.1.8';
    }
}

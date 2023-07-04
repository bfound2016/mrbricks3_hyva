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

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Framework\Setup\ModuleContextInterface;

class EstablishData implements DataPatchInterface
{
    /**
     * @var \Magento\Eav\Setup\EavSetupFactory
     */
    private $eavSetupFactory;

    /**
     * @var \Magento\Sales\Model\Order\StatusFactory
     */
    private $orderStatusFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status
     */
    private $status;

    /**
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status $status
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory              $eavSetupFactory,
        \Magento\Sales\Model\Order\StatusFactory        $orderStatusFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status $status
    ) {
        $this->eavSetupFactory = $eavSetupFactory;
        $this->orderStatusFactory = $orderStatusFactory;
        $this->status = $status;
    }

    /**
     * Install Setup PreOrder
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function apply()
    {
        $eavSetup = $this->eavSetupFactory->create();
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'preorder',
            [
                'type' => 'int',
                'label' => 'Pre-Order',
                'input' => 'select',
                'source' => \Bss\PreOrder\Model\Attribute\Source\Order::class,
                'required' => false,
                'sort_order' => 57,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => false,
                'apply_to' => 'simple,virtual,downloadable'
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'message',
            [
                'type' => 'text',
                'label' => 'Pre-Order Message',
                'input' => 'text',
                'required' => false,
                'sort_order' => 58,
                'global' => \Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface::SCOPE_STORE,
                'is_used_in_grid' => false,
                'is_visible_in_grid' => false,
                'is_filterable_in_grid' => false,
                'visible' => true,
                'is_html_allowed_on_front' => true,
                'visible_on_front' => false,
                'apply_to' => 'simple,virtual,downloadable',
                'note' => __("{date} & {preorder_date} can be used as Availability Date. If this field is blank,
                the default message edited in the configuration will be displayed.")
            ]
        );
        $pending = [
            'status' => 'pending_preorder',
            'label' => 'Pending Pre-Order',
        ];
        $this->orderStatusFactory->create()->setData($pending)->save();
        $this->status->assignState('pending_preorder', 'new', false, true);

        $processing = [
            'status' => 'processing_preorder',
            'label' => 'Processing Pre-Order',
        ];
        $this->orderStatusFactory->create()->setData($processing)->save();
        $this->status->assignState('processing_preorder', 'processing', false, true);
    }

    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
}

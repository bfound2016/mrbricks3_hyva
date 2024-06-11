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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
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
     * InstallData constructor.
     * @param \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory
     * @param \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Status $status
     */
    public function __construct(
        \Magento\Eav\Setup\EavSetupFactory $eavSetupFactory,
        \Magento\Sales\Model\Order\StatusFactory $orderStatusFactory,
        \Magento\Sales\Model\ResourceModel\Order\Status $status
    ) {
    
        $this->eavSetupFactory = $eavSetupFactory;
        $this->orderStatusFactory = $orderStatusFactory;
        $this->status = $status;
    }

    /**
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
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
                'note' => __("{date} can be used as Availability Date. If this field is blank, the default message
                 edited in the configuration will be displayed.")
            ]
        );
        $eavSetup->addAttribute(
            \Magento\Catalog\Model\Product::ENTITY,
            'restock',
            [
                'type' => 'datetime',
                'label' => 'Availability Date',
                'input' => 'date',
                'required' => false,
                'sort_order' => 59,
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
}

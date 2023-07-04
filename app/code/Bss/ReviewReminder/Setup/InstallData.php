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

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

class InstallData implements InstallDataInterface
{
    /**
     * Eav setup factory
     *
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    private $ruleFactory;

    /**
     * Eav setup factory
     *
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Group\Collection
     */
    private $customerGroup;

    /**
     * InstallData constructor.
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\Framework\App\State $state
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
     */
    public function __construct(
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\Framework\App\State $state,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Group\Collection $customerGroup
    ) {
        $this->ruleFactory = $ruleFactory;
        $this->state = $state;
        $this->storeManager = $storeManager;
        $this->customerGroup = $customerGroup;
    }

    /**
     * Install
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->state->setAreaCode('global');
        $rule = $this->ruleFactory->create();
        $data = [
            'name' => 'Review Reminder Cart Rule',
            'uses_per_coupon' => 1,
            'uses_per_customer' => 1,
            'discount_amount' => 10,
            'discount_qty' => 0,
            'is_active' => 1,
            'use_auto_generation' => 1,
            'is_rss' => 0,
            'stop_rules_processing' => 0,
            'apply_to_shipping' => 0,
            'description' => 'Discount 10%',
            'simple_action' => 'by_percent',
            'coupon_type' => 2,
            'discount_step' => 0,
            'website_ids' => $this->getWebsiteIds(),
            'customer_group_ids' => $this->getCustomerGroups()
        ];
        $rule->addData($data)->save();
    }

    /**
     * @return array
     */
    private function getWebsiteIds()
    {
        $websites = $this->storeManager->getWebsites();
        $id = [];
        foreach ($websites as $website) {
            if ($website->getId() == 0) {
                continue;
            }
            $id[] = $website->getId();
        }
        return $id;
    }

    /**
     * @return array
     */
    private function getCustomerGroups()
    {
        $groups = $this->customerGroup->toOptionArray();
        $groupIds = [];
        foreach ($groups as $group) {
            $groupIds[] = $group['value'];
        }
        return $groupIds;
    }
}

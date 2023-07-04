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
namespace Bss\ReviewReminder\Model\Source;

class Coupon implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * CollectionFactory
     *
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    protected $ruleCollection;

    /**
     * Coupon constructor.
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection
     */
    public function __construct(
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollection
    ) {
        $this->ruleCollection = $ruleCollection;
    }

    /**
     * To Option Array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $rules = $this->ruleCollection->create()
        ->addFieldToFilter('is_active', ['eq' => 1])
        ->addFieldToFilter('use_auto_generation', ['eq' => 1])
        ->getData();
        $data[] = ['value' => 0,  'label' => __('--Please Select--')];
        foreach ($rules as $value) {
            $data[] = ['value' => $value['rule_id'],  'label' => $value['name']];
        }
        return $data;
    }
}

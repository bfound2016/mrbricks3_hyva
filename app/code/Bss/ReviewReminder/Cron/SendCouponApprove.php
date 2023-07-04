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

namespace Bss\ReviewReminder\Cron;

/**
 * Class SendCoupon
 * @package Bss\ReviewReminder\Cron
 */
class SendCouponApprove
{
    /**
     * @var \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB
     */
    protected $connectionDB;

    /**
     * @var \Bss\ReviewReminder\Helper\SendCoupon
     */
    protected $helperSendCoupon;

    /**
     * SendCouponApprove constructor.
     * @param \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB
     * @param \Bss\ReviewReminder\Helper\SendCoupon $helperSendCoupon
     */
    public function __construct(
        \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB,
        \Bss\ReviewReminder\Helper\SendCoupon $helperSendCoupon
    ) {
        $this->connectionDB = $connectionDB;
        $this->helperSendCoupon = $helperSendCoupon;
    }

    /**
     * Index action
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $collection = $this->connectionDB->getReviewCollection();
        foreach ($collection as $review) {
            $this->helperSendCoupon->sendCoupon($review, 'cron');
        }
    }
}

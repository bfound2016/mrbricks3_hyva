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

namespace Bss\ReviewReminder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;

/**
 * Class ReviewSaveAfter
 * @package Bss\ReviewReminder\Observer
 */
class ReviewSaveAfter implements ObserverInterface
{
    /**
     * @var \Bss\ReviewReminder\Helper\SendCoupon
     */
    protected $helperSendCoupon;

    /**
     * ReviewSaveAfter constructor.
     * @param \Bss\ReviewReminder\Helper\SendCoupon $helperSendCoupon
     */
    public function __construct(
        \Bss\ReviewReminder\Helper\SendCoupon $helperSendCoupon
    ) {
        $this->helperSendCoupon = $helperSendCoupon;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $review = $event->getDataObject()->getData();
        $this->helperSendCoupon->sendCoupon($review, 'frontend');
    }
}

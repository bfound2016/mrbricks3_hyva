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
use Magento\Framework\App\Area;

class CheckoutSubmitAllAfterObserver implements ObserverInterface
{
    /**
     * Helper
     *
     * @var \Bss\ReviewReminder\Helper\Data
     */
    protected $helper;

    /**
     * Remind Log Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * LoggerInterface
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * CheckoutSubmitAllAfterObserver constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Framework\App\State $state
    ) {
        $this->helper = $helper;
        $this->remindLogFactory = $remindLogFactory;
        $this->logger = $logger;
        $this->state = $state;
    }

    /**
     * @param Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(Observer $observer)
    {
        $area = $this->state->getAreaCode();
        if ($area == Area::AREA_ADMINHTML) {
            return;
        }

        if ($this->helper->configEnable(false)) {
            $event = $observer->getEvent();
            $orders = (!empty($observer->getEvent()->getOrder()))? [$event->getOrder()]: $event->getOrders();

            $remindLog = $this->remindLogFactory->create();
            try {
                foreach ($orders as $order) {
                    $this->saveRemindLog($remindLog, $order);
                }
            } catch (\Exception $e) {
                $this->logger->debug('Review Reminder: '.$e);
            }
        }
    }

    /**
     * @param object $remindLog
     * @param object $order
     * @return void
     */
    protected function saveRemindLog($remindLog, $order)
    {
        $customerGroupId = $order->getCustomerGroupId();
        $configCustomerGroup = $this->helper->configCustomerGroup();
        if ($this->inCustomerGroup($customerGroupId, $configCustomerGroup)) {
            $reviewCollection = $remindLog->getResourceCollection()->addFieldToFilter(
                'order_id',
                [
                    'eq' => $order->getId()
                ]
            )->addFieldToFilter(
                'increment_id',
                [
                    'eq' =>  $order->getIncrementId()
                ]
            )->addFieldToFilter(
                'order_email',
                [
                    'eq' =>  $order->getCustomerEmail()
                ]
            );

            if ($reviewCollection->getSize() > 0) {
                return;
            }
            $log['order_email'] = $order->getCustomerEmail();
            $log['increment_id'] = $order->getIncrementId();
            $log['order_id'] = $order->getId();
            $log['create_date'] = $order->getCreatedAt();
            if ($order->getCustomerId()) {
                $log['customer_id'] = $order->getCustomerId();
            }
            $remindLog->setData($log);
            $remindLog->save();
        }
    }

    /**
     * In Customer Group
     *
     * @param String $customerGroupID
     * @param String $configCustomerGroup
     * @return bool
     */
    protected function inCustomerGroup($customerGroupID, $configCustomerGroup)
    {
        $listCustomerGroup = explode(",", $configCustomerGroup);
        if (in_array($customerGroupID, $listCustomerGroup)) {
            return true;
        }
        return false;
    }
}

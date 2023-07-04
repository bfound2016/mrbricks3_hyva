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

namespace Bss\ReviewReminder\Helper;

/**
 * Class SendCoupon
 * @package Bss\ReviewReminder\Helper
 */
class SendCoupon extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var string
     */
    protected $table = '';

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var \Bss\ReviewReminder\Helper\Email
     */
    protected $emailSender;

    /**
     * @var \Bss\ReviewReminder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB
     */
    protected $connectionDB;

    /**
     * SendCoupon constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param Email $emailSender
     * @param Data $helper
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Bss\ReviewReminder\Helper\Email $emailSender,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Magento\Framework\App\RequestInterface $request,
        \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB
    ) {
        parent::__construct($context);
        $this->orderFactory = $orderFactory;
        $this->customerFactory = $customerFactory;
        $this->emailSender = $emailSender;
        $this->helper = $helper;
        $this->request = $request;
        $this->connectionDB = $connectionDB;
    }

    /**
     * Send Coupon
     *
     * @param array $review
     * @param string $type
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function sendCoupon($review, $type)
    {
        $productId = $review['entity_pk_value'];
        $customerId = $review['customer_id'];
        $reviewId = $review['review_id'];
        $storeId = $review['store_id'];
        $customerEmail = '';
        $customerName = '';
        $this->table = $this->connectionDB->getTableName('review');
        $ruleId = $this->helper->getCouponRule($storeId);

        if ($customerId == null || !$customerId) {
            $customerId = 0;
            $customerGroup = 2;
        } else {
            $customerGroup = 1;
        }
        if (!$this->checkSendCoupon($reviewId, $customerGroup, $storeId, $ruleId, $type)) {
            return;
        }
        $orderId = false;
        if ($customerId === 0) {
            if ($type !== 'cron') {
                $orderId = $this->request->getParam('orderId');
                if (!$orderId) {
                    return;
                }
            } else {
                $orderId = $review['orderId'];
                if ($orderId === null) {
                    $this->update($reviewId, ['review_reminder_send_coupon' => 0]);
                    return;
                }
            }
            $order = $this->orderFactory->create()->load($orderId);
            if (!$order->getId()) {
                if ($type === 'cron') {
                    $this->update($reviewId, ['review_reminder_send_coupon' => 0]);
                }
                return;
            }
            if ($type !== 'cron') {
                $data['order_id'] = $orderId;
            }
            if ($order->getCustomerId()) {
                $customerId = $order->getCustomerId();
            } else {
                $customerEmail = $order->getCustomerEmail();
                $customerName = $order->getCustomerName();
            }
        }
        $reviewStatusConfig = $this->helper->getSendCouponDependReviewStatusConfig($storeId);

        if ($type !== 'cron') {
            $statusId = $review['status_id'];
            if ($reviewStatusConfig == \Bss\ReviewReminder\Model\Config\Source\Review\Status::APPROVE
                && $statusId != \Magento\Review\Model\Review::STATUS_APPROVED
            ) {
                $data['review_reminder_send_coupon'] = 1;
                $this->update($reviewId, $data);
                return;
            }
        }

        if ($customerId != 0) {
            $customer = $this->customerFactory->create()->load($customerId);
            if ($customer->getId()) {
                $customerEmail = $customer->getEmail();
                $customerName = $customer->getName();
            } else {
                if ($type === 'cron') {
                    $data['review_reminder_send_coupon'] = 0;
                    $this->update($reviewId, $data);
                }
                return;
            }
        }
        $remindOrder = $this->connectionDB->getOrderId($customerId, $customerEmail);
        /*Check In Review Reminder Logs*/
        if (!empty($remindOrder)) {
            $checkProductOrder = $this->connectionDB->checkProductOrder($productId, $remindOrder);
            if (!empty($checkProductOrder)) {
                $checkSendCouponBefore = $this->connectionDB->checkSendCouponBefore(
                    $productId,
                    $customerId,
                    $remindOrder,
                    $storeId
                );
                if (empty($checkSendCouponBefore)) {
                    try {
                        $data = [
                            'rule_id' => $ruleId,
                            'qty' => 1,
                            'length' => 12,
                            'format' => 'alphanum',
                            'prefix' => null,
                            'suffix' => null,
                            'dash' => 0
                        ];
                        $couponCode = $this->helper->getCouponCode($data);
                        $templateVar = [
                            'customerName' => $customerName,
                            'code' => $couponCode[0]
                        ];
                        $emailTemplate = $this->helper->configCouponEmailTemplate();
                        $this->emailSender->sendEmail($customerEmail, $emailTemplate, $templateVar, $storeId);
                    } catch (\Exception $e) {
                        $this->update($reviewId, ['review_reminder_send_coupon' => 3]);
                        $this->helper->writeLog('Review Reminder: ' . $e->getMessage());
                        return;
                    }
                }
                if ($orderId) {
                    $this->update($reviewId, ['review_reminder_send_coupon' => 2, 'order_id' => $orderId]);
                } else {
                    $this->update($reviewId, ['review_reminder_send_coupon' => 2]);
                }

            } else {
                $this->update($reviewId, ['review_reminder_send_coupon' => 0]);
            }
        } else {
            $this->update($reviewId, ['review_reminder_send_coupon' => 0]);
        }
    }

    /**
     * Check Send Coupon
     *
     * @param int $reviewId
     * @param int $customerGroup
     * @param int $storeId
     * @param int $ruleId
     * @param string $type
     * @return bool
     */
    protected function checkSendCoupon($reviewId, $customerGroup, $storeId, $ruleId, $type)
    {
        if ($this->helper->configEnable($storeId)
            && $this->helper->isEnableSendCoupon($storeId)
            && $ruleId != 0
            && in_array($customerGroup, $this->helper->getSendCouponCustomerGroup($storeId))
        ) {
            return true;
        } else {
            if ($type == 'cron') {
                $this->update($reviewId, ['review_reminder_send_coupon' => 0]);
            }
            return false;
        }
    }

    /**
     * Update State
     *
     * @param int $reviewId
     * @param array $data
     */
    protected function update($reviewId, $data)
    {
        $this->connectionDB->updateData($this->table, $reviewId, $data);
    }
}

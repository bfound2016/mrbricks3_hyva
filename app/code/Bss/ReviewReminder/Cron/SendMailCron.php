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
 * Class SendMailCron
 * @package Bss\ReviewReminder\Cron
 */
class SendMailCron
{
    /**
     * Logger
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * Helper
     *
     * @var \Bss\ReviewReminder\Helper\Data
     */
    protected $helper;

    /**
     * Email Sender
     *
     * @var \Bss\ReviewReminder\Helper\Email
     */
    protected $emailSender;

    /**
     * Remind Log Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $datetime;

    /**
     * Order Factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $state;

    /**
     * SendMailCron constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Bss\ReviewReminder\Helper\Email $emailSender
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param \Magento\Framework\App\State $state
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Psr\Log\LoggerInterface $logger,
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Bss\ReviewReminder\Helper\Email $emailSender,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Magento\Framework\App\State $state
    ) {
        $this->orderFactory = $orderFactory;
        $this->logger = $logger;
        $this->remindLogFactory = $remindLogFactory;
        $this->emailSender = $emailSender;
        $this->helper = $helper;
        $this->datetime = $datetime;
        $this->state = $state;
    }

    /**
     * Check Accept Order Status
     *
     * @param \Magento\Sales\Model\Order $order
     * @return bool
     */
    public function checkAcceptOrderStatus($order)
    {
        return in_array($order->getStatus(), explode(',', $this->helper->configOrderStatus()));
    }

    /**
     * Execute
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        if (!$this->state->getAreaCode()) {
            $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_FRONTEND);
        }
        $remindLogs = $this->helper->getRemindBySentCount();
        $sent = 0;
        $err = 0;
        $currentTime = $this->datetime->date();
        try {
            foreach ($remindLogs as $item) {
                $orderId = $item['order_id'];
                $order = $this->loadOrder($orderId);
                $storeCode = $order->getStore()->getCode();
                if ($this->checkAcceptOrderStatus($order)) {
                    $dayAfter = $this->helper->configDaySendMail($storeCode);
                    $dayAfter = ($item['status']) ? 1 : $dayAfter;
                    $daySendMail = ($dayAfter) ? ($item['sent_count'] + 1) * $dayAfter : 0;
                    $dateSendMail = date('Y-m-d H:i:s', strtotime($item['create_date'] . '+' . $daySendMail . ' days'));
                    if ($currentTime >= $dateSendMail) {
                        try {
                            $this->sendMail($item, $storeCode, $order);
                            $sent = +1;
                        } catch (\Exception $e) {
                            $err = +1;
                        }
                    }
                }
            }
            $this->helper->writeLog($sent);
            $this->helper->writeLog($err);
        } catch (\Exception $e) {
            $this->logger->info('Review Reminder - Send Mail: ' . $e->getMessage());
        }
    }

    /**
     * Remove Review Reminder
     *
     * @param array $remindData
     * @param string $storeCode
     * @param \Magento\Sales\Model\Order $order
     */
    protected function sendMail($remindData, $storeCode, $order)
    {
        $remindLog = $this->remindLogFactory->create()->load($remindData['remindlog_id']);
        $email = $remindData['order_email'];

        $emailReceiver = $email;
        $emailReceiver = str_replace(' ', '', $emailReceiver);

        $emailTemplate = $this->helper->configEmailTemplate();
        $storeId = $order->getStoreId();
        if ($this->helper->configEnableByStore($storeCode)) {
            $templateVar = [
                'order' => $order,
                'storeName' => $order->getStore(),
                'customerName' => $order->getCustomerName(),
                'incrementId' => $order->getIncrementId(),
                'createdAt' => $order->getCreatedAt()
            ];
            $this->emailSender->sendEmail($emailReceiver, $emailTemplate, $templateVar, $storeId);
            $remindLog->setStatus(1);
            $sentCount = $remindData['sent_count'] + 1;
            $remindLog->setSentCount($sentCount);
            $remindLog->save();
        }
    }

    /**
     * Load Order
     *
     * @param string $id
     * @return \Magento\Sales\Model\Order
     */
    protected function loadOrder($id)
    {
        $order = $this->orderFactory->create()->load($id);
        return $order;
    }
}

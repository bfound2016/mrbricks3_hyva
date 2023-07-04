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
namespace Bss\ReviewReminder\Controller\Adminhtml\RemindLog;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

class MassSend extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

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
     * Collection Factory
     *
     * @var \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;

    /**
     * Order Factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Constructor
     *
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Bss\ReviewReminder\Helper\Email $emailSender
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory $collectionFactory
     * @param Context $context
     */
    public function __construct(
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Bss\ReviewReminder\Helper\Email $emailSender,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory $collectionFactory,
        Context $context
    ) {
        $this->orderFactory = $orderFactory;
        $this->remindLogFactory = $remindLogFactory;
        $this->filter = $filter;
        $this->emailSender = $emailSender;
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Check Accept Order Status
     * @param object $order
     * @return bool
     */
    protected function checkAcceptOrderStatus($order)
    {
        return in_array($order->getStatus(), explode(',', $this->helper->configOrderStatus()));
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        if ($this->helper->configEnable(false)) {
            $maxSent = $this->helper->configMaxEmail();
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $sent = 0;
            $err = 0;

            try {
                foreach ($collection as $item) {
                    $order = $this->loadOrder($item['order_id']);
                    if ($this->checkAcceptOrderStatus($order)) {
                        /** @var \Bss\ReviewReminder\Model\RemindLog $item */
                        if ($item['sent_count'] < $maxSent &&
                            !$this->helper->checkOrderReviewed($order)
                        ) {
                            $this->sendMail($item);
                            $sent++;
                        } else {
                            $err++;
                        }
                    }
                }
                if ($sent) {
                    $this->messageManager->addSuccessMessage(__('A total of %1 emails have been sent.', $sent));
                }
                
                if ($err) {
                    $this->messageManager->addErrorMessage(__('A total of %1 emails could not send.', $err));
                }
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory
                                    ->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('*/*/');

            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            }
        } else {
            $this->messageManager->addWarningMessage(__('Module Reivew Reminder is disabled.'));
            $resultRedirect->setPath('bss_reviewreminder/*/');
            return $resultRedirect;
        }
    }

    /**
     * Remove ReviewReminder
     *
     * @param \Bss\ReviewReminder\Model\RemindLog $remindData
     * @return void
     */
    protected function sendMail($remindData)
    {
        $remindLog = $this->remindLogFactory->create()->load($remindData['remindlog_id']);
        $email = $remindData['order_email'];

        $emailReceiver = $email;
        $emailReceiver = str_replace(' ', '', $emailReceiver);

        $emailTemplate = $this->helper->configEmailTemplate();
        $orderId = $remindData['order_id'];
        $order = $this->orderFactory->create()->load($orderId);
        $storeCode = $order->getStore()->getCode();
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
            $sentCount = $remindData['sent_count']+1;
            $remindLog->setSentCount($sentCount);
            $remindLog->save();
        }
    }

    /**
     * LoadOrder
     * @param int $orderId
     * @return mixed
     */
    protected function loadOrder($orderId)
    {
        return $this->orderFactory->create()->load($orderId);
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ReviewReminder::reminder_log");
    }
}

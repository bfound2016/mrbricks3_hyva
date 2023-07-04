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

class Send extends \Magento\Backend\App\Action
{
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
     * Order Factory
     *
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $orderFactory;

    /**
     * Remind Log Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * PageFactory
     *
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Bss\ReviewReminder\Helper\Email $emailSender
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Bss\ReviewReminder\Helper\Email $emailSender,
        \Bss\ReviewReminder\Helper\Data $helper,
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->orderFactory = $orderFactory;
        $this->emailSender = $emailSender;
        $this->helper = $helper;
        $this->datetime = $datetime;
        $this->resultPageFactory = $resultPageFactory;
        $this->remindLogFactory = $remindLogFactory;
        parent::__construct($context);
    }

    /**
     * @param object $order
     * @return bool
     */
    protected function checkAcceptOrderStatus($order)
    {
        return in_array($order->getStatus(), explode(',', $this->helper->configOrderStatus()));
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $id = $this->getRequest()->getParam('remindlog_id');
        if ($id) {
            $remindLog = $this->remindLogFactory->create()->load($id);
            $remindData = $remindLog->getData();
            $email = $remindData['order_email'];

            $emailTemplate = $this->helper->configEmailTemplate();
            $emailReceiver = $email;
            $emailReceiver = str_replace(' ', '', $emailReceiver);

            $orderId = $remindData['order_id'];
            $order = $this->orderFactory->create()->load($orderId);

            $storeCode = $order->getStore()->getCode();
            $storeId = $order->getStoreId();
            if ($this->helper->configEnableByStore($storeCode)) {
                if ($this->checkAcceptOrderStatus($order)) {
                    $maxSent = $this->helper->configMaxEmail();
                    $templateVar = [
                        'order' => $order,
                        'storeName' => $order->getStore(),
                        'customerName' => $order->getCustomerName(),
                        'incrementId' => $order->getIncrementId(),
                        'createdAt' => $order->getCreatedAt()
                    ];
                    try {
                        if ($remindData['sent_count'] < $maxSent) {
                            $this->emailSender->sendEmail($emailReceiver, $emailTemplate, $templateVar, $storeId);
                            $remindLog->setStatus(1);
                            $sentCount = $remindData['sent_count']+1;
                            $remindLog->setSentCount($sentCount);
                            $remindLog->save();
                            $this->messageManager->addSuccessMessage(__('The remind email has been sent.'));
                            $resultRedirect->setPath('bss_reviewreminder/*/');
                            return $resultRedirect;
                        } else {
                            $this->messageManager->addWarningMessage(
                                __(
                                    'Has sent the maximum number of mail or Customer has reviewed all products in order'
                                )
                            );
                            $resultRedirect->setPath('bss_reviewreminder/*/');
                            return $resultRedirect;
                        }
                    } catch (\Exception $e) {
                        $this->messageManager->addErrorMessage($e->getMessage());
                        $resultRedirect->setPath('bss_reviewreminder/*/', ['remindlog_id' => $id]);
                        return $resultRedirect;
                    }
                } else {
                    $this->messageManager->addWarningMessage(
                        __(
                            'Review reminder is not applied for  %1 orders.',
                            $order->getStatus()
                        )
                    );
                    $resultRedirect->setPath('bss_reviewreminder/*/');
                    return $resultRedirect;
                }

            } else {
                $this->messageManager->addWarningMessage(
                    __(
                        'Module Review Reminder is disabled at %1.',
                        $order->getStore()->getName()
                    )
                );
                $resultRedirect->setPath('bss_reviewreminder/*/');
                return $resultRedirect;
            }
        } else {
            $this->messageManager->addErrorMessage(__('This email to send was not found.'));
            $resultRedirect->setPath('bss_reviewreminder/*/');
            return $resultRedirect;
        }
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

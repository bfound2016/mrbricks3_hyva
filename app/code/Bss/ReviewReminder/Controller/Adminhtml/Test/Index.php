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
namespace Bss\ReviewReminder\Controller\Adminhtml\Test;

use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Result Raw Factory
     *
     * @var \Magento\Framework\Controller\Result\RawFactory
     */
    protected $resultRawFactory;

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
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;

    /**
     * Index constructor.
     * @param \Magento\Framework\Controller\Result\RawFactory $resultRawFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Bss\ReviewReminder\Helper\Email $emailSender
     * @param \Bss\ReviewReminder\Helper\Data $helper
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Bss\ReviewReminder\Helper\Email $emailSender,
        \Bss\ReviewReminder\Helper\Data $helper,
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->resultRawFactory = $resultRawFactory;
        $this->helper = $helper;
        $this->datetime = $datetime;
        $this->emailSender = $emailSender;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $email = $this->getRequest()->getPost('email');
        $result = __('The test review reminder has been sent to') . ' ' . $email . '. Please check your email.';
        try {
            $storeId = (int) $this->getRequest()->getParam('store', 0);
            $emailReceiver = $email;
            $emailReceiver = str_replace(' ', '', $emailReceiver);
            $timeSender = $this->datetime->gmtDate('Y-m-d H:i:s')." GMT";

            $emailTemplate = $this->helper->configEmailTemplate();
            $templateVar = [
                            'customerName' => "Test Review Reminder",
                            'incrementId' => "#TEST01",
                            'createdAt' => $timeSender
                        ];
            $this->emailSender->sendEmail($emailReceiver, $emailTemplate, $templateVar, $storeId);
        } catch (\Exception $e) {
            $result = __($e->getMessage());
        }

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultRawFactory->create();
        $resultRaw->setContents($this->makeClickableLinks($result));
        return $resultRaw;
    }

    /**
     * Make link clickable
     * @param string $s
     * @return string
     */
    protected function makeClickableLinks($s)
    {
        return preg_replace(
            '@(https?://([-\w\.]+[-\w])+(:\d+)?(/([\w/_\.#-]*(\?\S+)?[^\.\s])?)?)@',
            '<a href="$1" target="_blank">$1</a>',
            $s
        );
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ReviewReminder::config_reviewreminder");
    }
}

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

class MassDelete extends \Magento\Backend\App\Action
{
    /**
     * Mass Action Filter
     *
     * @var \Magento\Ui\Component\MassAction\Filter
     */
    protected $filter;

    /**
     * Collection Factory
     *
     * @var \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory
     */
    protected $remindLogFactory;

    /**
     * Constructor
     *
     * @param \Magento\Ui\Component\MassAction\Filter $filter
     * @param \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory $remindLogFactory
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Ui\Component\MassAction\Filter $filter,
        \Bss\ReviewReminder\Model\ResourceModel\RemindLog\CollectionFactory $remindLogFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->filter            = $filter;
        $this->remindLogFactory = $remindLogFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
        $collection = $this->filter->getCollection($this->remindLogFactory->create());
        $delete = 0;
        try {
            foreach ($collection as $item) {
                /** @var \Bss\ReviewReminder\Model\RemindLog $item */
                $this->removeRemindLog($item);
                $delete++;
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $delete));
            return $resultRedirect->setPath('*/*/');

        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $resultRedirect->setPath('*/*/');
            return $resultRedirect;
        }
    }

    /**
     * RemoveRemindLog
     *
     * @param object $remindLog
     */
    protected function removeRemindLog($remindLog)
    {
        $remindLog->delete();
    }

    /**
     * Check Rule
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed("Bss_ReviewReminder::delete");
    }
}

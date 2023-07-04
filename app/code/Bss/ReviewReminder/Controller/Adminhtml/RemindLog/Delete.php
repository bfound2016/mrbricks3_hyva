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

class Delete extends \Magento\Backend\App\Action
{
    /**
     * Remind Log Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * Constructor
     *
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param Context|\Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->remindLogFactory = $remindLogFactory;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     * @throws \Exception
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);

        $id = $this->getRequest()->getParam('remindlog_id');
        if ($id) {
            try {
                /** @var \Bss\ReviewReminder\Model\ReviewReminder $ReviewReminder */
                $remindLog = $this->remindLogFactory->create()->load($id);

                $remindLog->delete();
                $this->messageManager->addSuccessMessage(__('The remind log has been deleted.'));
                $resultRedirect->setPath('bss_reviewreminder/*/');
                return $resultRedirect;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $resultRedirect->setPath('bss_reviewreminder/*/edit', ['remindlog_id' => $id]);
                return $resultRedirect;
            }
        }

        $this->messageManager->addErrorMessage(__('This remind log to delete was not found.'));
        $resultRedirect->setPath('remindlog_id/*/');
        return $resultRedirect;
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

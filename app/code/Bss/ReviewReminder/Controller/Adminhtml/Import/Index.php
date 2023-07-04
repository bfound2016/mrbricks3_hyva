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
namespace Bss\ReviewReminder\Controller\Adminhtml\Import;

use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 * @package Bss\ReviewReminder\Controller\Adminhtml\Import
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Bss\ReorderProduct\Model\ResourceModel\ImportFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB
     */
    protected $connectionDB;

    /**
     * Index constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Bss\ReviewReminder\Model\ResourceModel\ConnectionDB $connectionDB
    ) {
        parent::__construct($context);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->connectionDB = $connectionDB;
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\MailException
     */
    public function execute()
    {
        try {
            $table = $this->connectionDB->getTableName('bss_reviewreminder_remindlog');
            $issetRemindLog = $this->connectionDB->getIssetRemindLog($table);
            $collection = $this->orderCollectionFactory->create();
            if ($issetRemindLog && !empty($issetRemindLog)) {
                $collection->addFieldToFilter('entity_id', ['nin' => $issetRemindLog]);
            }
            $data = [];
            $i = 0;
            foreach ($collection as $order) {
                if ($i == 51) {
                    $this->connectionDB->insertMultiple($table, $data);
                    $data = [];
                    $i = 0;
                }
                $data[$i]['status'] = 0;
                $data[$i]['sent_count'] = 0;
                $data[$i]['order_id'] = $order->getId();
                $data[$i]['increment_id'] = $order->getIncrementId();
                $data[$i]['order_email'] = $order->getCustomerEmail();
                if ($order->getCustomerId()) {
                    $data[$i]['customer_id'] = $order->getCustomerId();
                } else {
                    $data[$i]['customer_id'] = null;
                }
                $data[$i]['create_date'] = $order->getCreatedAt();
                $i++;
            }
            if ($i < 51 && !empty($data)) {
                $this->connectionDB->insertMultiple($table, $data);
            }
            $this->messageManager->addSuccessMessage('Import Success!');
        } catch (\LogicException $exception) {
            $this->messageManager->addErrorMessage('There was an error in the process of importing orders');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
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

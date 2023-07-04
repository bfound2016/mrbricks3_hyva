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
namespace Bss\ReviewReminder\Model\ResourceModel;

class RemindLog extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $dateTime;

    /**
     * Constructor
     *
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    ) {
        $this->dateTime = $date;
        parent::__construct($context);
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_reviewreminder_remindlog', 'remindlog_id');
    }

    /**
     * Get Remind By Sent Count
     * @param int $sentCount
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemindBySentCount($sentCount)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('sent_count < :sentCount');
        $binds = ['sentCount' => (int) $sentCount];
        
        return $adapter->fetchAssoc($select, $binds);
    }

    /**
     * Get Remind Less Than Date
     * @param string $date
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRemindLessThanDate($date)
    {
        $adapter = $this->getConnection();
        $select = $adapter->select()
            ->from($this->getMainTable())
            ->where('create_date <= :date');
        $binds = ['date' => $date];
        
        return $adapter->fetchAssoc($select, $binds);
    }

    /**
     * Before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|\Bss\Popup\Model\Popup $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->dateTime->date());
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateTime->date());
        }
        return parent::_beforeSave($object);
    }
}

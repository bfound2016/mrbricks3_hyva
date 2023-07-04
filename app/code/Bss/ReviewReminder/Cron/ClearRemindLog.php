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

class ClearRemindLog
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
     * Remind Log Factory
     *
     * @var \Bss\ReviewReminder\Model\RemindLogFactory
     */
    protected $remindLogFactory;

    /**
     * Date Time
     *
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $datetime;

    /**
     * ClearRemindLog constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $datetime
     * @param \Bss\ReviewReminder\Helper\Data $helper
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Bss\ReviewReminder\Model\RemindLogFactory $remindLogFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $datetime,
        \Bss\ReviewReminder\Helper\Data $helper
    ) {
        $this->logger = $logger;
        $this->remindLogFactory = $remindLogFactory;
        $this->helper = $helper;
        $this->datetime = $datetime;
    }

    /**
     * Index action
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $deleted = 0;
        $daysClearLog = $this->helper->configClearLog();
        $currentTime = $this->datetime->date();
        $dateClearLog = date('Y-m-d H:i:s', strtotime($currentTime. '-'.$daysClearLog.' days'));
        $remindLogs = $this->helper->getRemindLessThanDate($dateClearLog);
        if ($this->helper->configEnable() && $daysClearLog) {
            try {
                foreach ($remindLogs as $item) {
                    $remindLog = $this->loadRemindLog($item['remindlog_id']);
                    $this->removeRemindLog($remindLog);
                    $deleted++;
                }

                $this->logger->info('Review Reminder - Clear Log Cron.'. $deleted .' logs have been deleted.');

            } catch (\Exception $e) {
                $this->logger->info('Review Reminder - Clear Log: '.$e->getMessage());
            }
        }
    }

    /**
     * Load Remind Log
     * @param int $remindlogId
     * @return mixed
     */
    public function loadRemindLog($remindlogId)
    {
        return $this->remindLogFactory->create()->load($remindlogId);
    }

    /**
     * Remove Remind Log
     * @param object $remindLog
     */
    protected function removeRemindLog($remindLog)
    {
        $remindLog->delete();
    }
}

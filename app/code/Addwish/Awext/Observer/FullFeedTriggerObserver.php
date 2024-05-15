<?php
namespace Addwish\Awext\Observer;

use Addwish\Awext\Helper\Config as ConfigHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class FullFeedTriggerObserver implements ObserverInterface {
    /**
     * @var ConfigHelper
     */
    protected $configHelper;

    /**
     * @param ConfigHelper $configHelper
     */
    public function __construct(
        ConfigHelper $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    public function execute(Observer $observer) {
        try {
            $event = $observer->getEvent();
            if (!$event) {
                return;
            }
            $this->configHelper->setLastIndexUpdateTime(time());
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }

}
<?php
namespace Addwish\Awext\Observer;

use Magento\Checkout\Model\Session;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderTrackingObserver implements ObserverInterface {
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @param Session $checkoutSession
     */
    public function __construct(
        Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    public function execute(Observer $observer) {
        try {
            $event = $observer->getEvent();
            if (!$event) {
                return;
            }
            $order = $event->getOrder();
            if ($order) {
                $this->checkoutSession->setAddwishOrderSuccess(true);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }

}
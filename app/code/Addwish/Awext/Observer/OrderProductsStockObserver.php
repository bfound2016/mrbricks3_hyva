<?php
namespace Addwish\Awext\Observer;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OrderProductsStockObserver implements ObserverInterface {
    /**
     * @var DeltaItemHelper
     */
    private $deltaItemHelper;

    /**
     * @param DeltaItemHelper $deltaItemHelper
     */
    public function __construct(
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->deltaItemHelper = $deltaItemHelper;
    }

    public function execute(Observer $observer) {
        try {
            $event = $observer->getEvent();
            if (!$event) {
                return;
            }
            $order = $event->getOrder();
            if ($order) {
                # a simple product added on its own will not have a parent.
                # a simple product added as a variation of a configurable product,
                # or part of a bundled product, will have a parent.
                # both the parent and the children are part of the orderitems.
                # but children can also be fetched by getChildrenItems()
                $products = [];
                foreach($order->getItems() as $orderItem) {
                    if ($orderItem->getProduct() && !$orderItem->getParentItem()) {
                        $products[] = $orderItem->getProduct();
                        # if this is a configurable or bundled product,
                        # we will check to see if any of the children are visible individually.
                        # if so, then we flag those as well.
                        if ($orderItem->getHasChildren()){
                            foreach ($orderItem->getChildrenItems() as $childOrderItem){
                                $childProduct = $childOrderItem->getProduct();
                                if ($childProduct->isVisibleInSiteVisibility()) {
                                    $products[] = $childProduct;
                                }
                            }
                        }
                    }
                }
                $this->deltaItemHelper->updateDeltaItems($products);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }

}
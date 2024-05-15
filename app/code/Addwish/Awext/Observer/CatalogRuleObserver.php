<?php

namespace Addwish\Awext\Observer;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

// OBS THIS IS CURRENTLY NOT IN USE, COMMENTED OUT IN EVENTS.XML
class CatalogRuleObserver implements ObserverInterface {
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
            $ruleProductsWebsitesAssociations = $event->getRule()->getMatchingProductIds();
            /* getMatchingProductIds returns in the following format:
            [
                productId1 => [
                    websiteId1 => 1,
                    websiteId2 => 0
                ],
                productId2 => [
                    websiteId1 => 0,
                    websiteId2 => 0
                ]
            ] */
            $productIds = [];
            if (!empty($ruleProductsWebsitesAssociations)) {
                foreach ($ruleProductsWebsitesAssociations as $productId => $websiteAssociations) {
                    // currently just checking whether the rule applies to a product in ANY website.
                    // if we eventually keep track of deltaItems pr. website, we want to expand on this.
                    if (in_array(1, $websiteAssociations)) {
                        $productIds[] = $productId;
                    }
                }
            }
            $this->deltaItemHelper->updateDeltaItemsByIds($productIds);
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
    }
}

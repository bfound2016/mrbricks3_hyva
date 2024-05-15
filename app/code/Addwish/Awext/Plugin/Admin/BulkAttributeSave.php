<?php

namespace Addwish\Awext\Plugin\Admin;

use Addwish\Awext\Model\DeltaItem\DeltaItemHelper;
use Magento\Catalog\Helper\Product\Edit\Action\Attribute as AttributeAction;
use Magento\Catalog\Controller\Adminhtml\Product\Action\Attribute\Save;

class BulkAttributeSave {
    /**
     * @var AttributeAction
     */
    private $attributeAction;

    /**
     * @var DeltaItemHelper
     */
    private $deltaItemHelper;

    /**
     * @param AttributeAction $attributeAction
     * @param DeltaItemHelper $deltaItemHelper
     */
    public function __construct(
        AttributeAction $attributeAction,
        DeltaItemHelper $deltaItemHelper
    ) {
        $this->attributeAction = $attributeAction;
        $this->deltaItemHelper = $deltaItemHelper;
    }

    public function afterExecute(Save $subject, $result) {
        try {
            $productIds = $this->attributeAction->getProductIds();
            if ($productIds) {
                $this->deltaItemHelper->updateDeltaItemsByIds($productIds);
            }
        } catch (\Exception|\Throwable $e) {
            // ignore
        }
        return $result;
    }
}


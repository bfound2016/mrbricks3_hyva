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
 * @package    Bss_PreOrder
 * @author     Extension Team
 * @copyright  Copyright (c) 2018-2019 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Helper;

use Magento\Catalog\Model\Product;
use Bss\PreOrder\Helper\Data as PreOrderHelper;
use Magento\Catalog\Model\ResourceModel\Product\Collection;

class Stock
{
    /**
     * @var PreOrderHelper
     */
    protected $preOrderHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * CheckStockOptions constructor.
     * @param PreOrderHelper $preOrderHelper
     */
    public function __construct(
        PreOrderHelper $preOrderHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->preOrderHelper = $preOrderHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * Get Product not set preorder
     *
     * @param \Magento\CatalogInventory\Helper\Stock $layer
     * @param Collection $collection
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeAddIsInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $layer,
        $collection
    ) {
        if ($this->preOrderHelper->isEnable()
            && $collection->hasFlag('allow_check_out_stock_pre_order')
            && $this->preOrderHelper->isDisplayOutOfStockProduct()
        ) {
            /** @var \Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection $collection */
            $productCollection = clone $collection;
            $collection->addAttributeToSelect('preorder');
            $productCollection->addAttributeToFilter('preorder', [['eq' => 0],['null' => true]], 'left');
            $collection->setFlag('ignore_product', $productCollection->getAllIds());
        }
    }

    /**
     * Ignore list product out stock and not set preorder
     *
     * @param \Magento\CatalogInventory\Helper\Stock $layer
     * @param mixed $result
     * @param Collection $collection
     * @return void $result
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterAddIsInStockFilterToCollection(
        \Magento\CatalogInventory\Helper\Stock $layer,
        $result,
        $collection
    ) {
        $websiteId = $this->storeManager->getStore()->getWebsiteId();
        if ($this->preOrderHelper->isEnable()
            && $collection->hasFlag('allow_check_out_stock_pre_order')
            && !empty($collection->getFlag('ignore_product'))
            && $this->preOrderHelper->isDisplayOutOfStockProduct()
        ) {
            /** @var Collection $collection */
            $cloneCollection = clone $collection;
            $cloneCollection->getSelect()->where(
                'stock_status_index.stock_status  = 0 AND e.entity_id in (?)',
                $collection->getFlag('ignore_product')
            );
            $ignoreItem = $cloneCollection->getAllIds();
            if (!empty($ignoreItem)) {
                $collection->getSelect()->where(
                    ' e.entity_id not in (?)',
                    $ignoreItem
                );
            }
            $collection->setFlag('allow_check_out_stock_pre_order', false);
        }
        return $result;
    }
}

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
namespace Bss\PreOrder\Plugin;

use Bss\PreOrder\Helper\Data;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\StockStateProvider;
use Magento\Framework\Exception\LocalizedException;

class QuoteCheck
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * QuoteCheck constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Check Quote Items
     *
     * @param StockStateProvider $subject
     * @param StockItemInterface $stockItem
     * @param float $qty
     * @param float $summaryQty
     * @param int $origQty
     * @return array
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeCheckQuoteItemQty($subject, $stockItem, $qty, $summaryQty, $origQty = 0)
    {
        if ($this->helper->isEnable()) {
            $productId = $stockItem->getProductId();
            $preOrder = $this->helper->getPreOrder($productId);
            if (($preOrder == 1 && $this->helper->isAvailablePreOrder($productId)) || $preOrder == 2) {
                $stockItem->setIsInStock(true);
            }
        }
        return [$stockItem, $qty, $summaryQty, $origQty];
    }

    /**
     * Check Quantity
     *
     * @param StockStateProvider $subject
     * @param callable $proceed
     * @param StockItemInterface $stockItem
     * @param float $qty
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundCheckQty($subject, callable $proceed, $stockItem, $qty)
    {
        if ($this->helper->isEnable()) {
            $productId = $stockItem->getProductId();
            $preOrder = $this->helper->getPreOrder($productId);
            if (($preOrder == 1 && $this->helper->isAvailablePreOrder($productId)) || $preOrder == 2) {
                return true;
            }
        }
        return $proceed($stockItem, $qty);
    }
}

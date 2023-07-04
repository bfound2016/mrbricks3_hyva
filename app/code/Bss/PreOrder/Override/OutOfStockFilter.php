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
declare(strict_types=1);

namespace Bss\PreOrder\Override;

use Magento\Catalog\Model\Product;
use Magento\CatalogInventory\Api\Data\StockStatusInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\DataObject;

/**
 * Removes out of stock products from cart candidates when appropriate.
 */
class OutOfStockFilter
{
    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @param StockRegistryInterface $stockRegistry
     */
    public function __construct(StockRegistryInterface $stockRegistry)
    {
        $this->stockRegistry = $stockRegistry;
    }

    /**
     * Removes out of stock products for requests that don't specify the super group.
     *
     * @param Grouped $subject
     * @param array|string $result
     * @param DataObject $buyRequest
     * @return string|array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterPrepareForCartAdvanced(Grouped $subject, $result, DataObject $buyRequest)
    {
        if (!is_array($result) && $result instanceof Product) {
            $result = [$result];
        }

        // Only remove out-of-stock products if no quantities were specified
        if (is_array($result) && !empty($result) && !$buyRequest->getData('super_group')) {
            foreach ($result as $index => $cartItem) {
                $productStockStatus = $this->stockRegistry->getProductStockStatus($cartItem->getId());
                $isPreOrderCart = $cartItem->getData('is_pre_order');
                if ($productStockStatus == StockStatusInterface::STATUS_OUT_OF_STOCK && !$isPreOrderCart) {
                    unset($result[$index]);
                }
            }
        }

        return $result;
    }
}

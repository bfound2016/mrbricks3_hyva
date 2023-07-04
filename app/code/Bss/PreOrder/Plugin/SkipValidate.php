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

use Magento\CatalogInventory\Model\Quote\Item\QuantityValidator;
use Magento\Framework\Event\Observer;
use Bss\PreOrder\Model\Attribute\Source\Order;

class SkipValidate
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * SkipValidate constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     *  Skip Check For PreOrder Product
     *
     * @param QuantityValidator $subject
     * @param callable $proceed
     * @param Observer $observer
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $is_preorder = $observer->getEvent()->getData('is_preorder');
            if ($is_preorder === null) {
                $quoteItem = $observer->getEvent()->getItem();
                $productId = $quoteItem->getProductId();
                $preOrder = $this->helper->getPreOrder($productId);
                if ($preOrder == Order::ORDER_YES || $preOrder == Order::ORDER_OUT_OF_STOCK) {
                    return;
                }
            }
            if ($is_preorder) {
                return;
            }
        }
        return $proceed($observer);
    }
}

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
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Model\Stock;

use Bss\PreOrder\Model\Attribute\Source\Order;

class Status
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    protected $productHelper;

    /**
     * Status constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Bss\PreOrder\Helper\ProductData $productHelper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Bss\PreOrder\Helper\ProductData $productHelper
    ) {
        $this->helper=$helper;
        $this->productHelper = $productHelper;
    }

    /**
     * @param $subject
     * @param $result
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @todo Make the stock status for preorder products depending on positive stock qty by using helper method
     */
    public function afterGetStockStatus($subject, $result)
    {
        if ($this->helper->isEnable() && !$result) {
            $productId = $subject->getData('product_id');

            if ($subject->getData('type_id') == 'configurable') {
                return $this->productHelper->isStatusParent($productId);
            }

            else {
                $preOrder = $this->helper->getPreOrder($productId);
                if ($preOrder == Order::ORDER_YES || $preOrder == Order::ORDER_OUT_OF_STOCK) {
                    return true;
                }
            }
        }
        return $result;
    }
}

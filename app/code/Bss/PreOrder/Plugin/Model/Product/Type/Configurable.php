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
 * @copyright  Copyright (c) 2018-2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Plugin\Model\Product\Type;

use Bss\PreOrder\Model\Attribute\Source\Order;

class Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * Apply Is Salable For Pre Order Product
     *
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $subject
     * @param bool $result
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterIsSalable(
        $subject,
        $result,
        \Magento\Framework\Pricing\SaleableInterface $salableItem
    ) {
        if ($salableItem->getTypeId() == 'configurable'
            && !$result
            && $this->helper->isEnable()
            && !$this->helper->getRegistry()->registry('check_parent_stock_status')
            //skip check isSalable of preorder when get stock status title configurable
        ) {
            $listChildProduct = $subject->getUsedProducts($salableItem);
            foreach ($listChildProduct as $child) {
                $isInStock = $child->getData('is_salable');
                $preOrder = $child->getData('preorder');
                if (($preOrder == Order::ORDER_YES && $this->helper->isAvailablePreOrderFromFlatData(
                    $child['pre_oder_from_date'],
                    $child['pre_oder_to_date']
                )) ||
                    ($preOrder == Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
                    $result = true;
                    break;
                }
            }
        }
        $this->helper->getRegistry()->unregister('check_parent_stock_status');
        return $result;
    }
}

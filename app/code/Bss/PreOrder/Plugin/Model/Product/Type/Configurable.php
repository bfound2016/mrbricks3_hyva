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
namespace Bss\PreOrder\Plugin\Model\Product\Type;

use Bss\PreOrder\Model\Attribute\Source\Order;

class Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    private $configurableData;

    /**
     * Configurable constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $configurableData
    ) {
        $this->helper               = $helper;
        $this->configurableData     = $configurableData;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param \Magento\Framework\Pricing\SaleableInterface $salableItem
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundIsSalable(
        $subject,
        callable $proceed,
        \Magento\Framework\Pricing\SaleableInterface $salableItem
    ) {
        $result = $proceed($salableItem);
        if ($salableItem->getTypeId() == 'configurable' && !$result && $this->helper->isEnable()) {
            $parentProduct = $this->configurableData->getChildrenIds($salableItem->getId());
            foreach ($parentProduct[0] as $childId) {
                $isInStock = $this->helper->getIsInStock($childId);
                $preOrder = $this->helper->getPreOrder($childId);
                if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
                    $result = true;
                    break;
                };
            }
        }
        return $result;
    }
}

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
namespace Bss\PreOrder\Plugin;

class SkipCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * SkipCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper=$helper;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param callable $proceed
     * @return bool
     */
    public function aroundIsSalable(\Magento\Catalog\Model\Product $subject, callable $proceed)
    {
        if ($this->helper->isEnable()) {
            $store = $this->helper->getStoreId();
            $preOrder = $subject->getResource()->getAttributeRawValue($subject->getId(), 'preorder', $store);
            if ($preOrder == 1 || $preOrder == 2) {
                return true;
            }
        }
        $returnValue = $proceed();
        return $returnValue;
    }
}

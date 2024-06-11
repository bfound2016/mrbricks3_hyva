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
        $this->helper=$helper;
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, callable $proceed, \Magento\Framework\Event\Observer $observer)
    {
        if ($this->helper->isEnable()) {
            $quoteItem = $observer->getEvent()->getItem();
            $productId = $quoteItem->getProductId();
            $preOrder = $this->helper->getPreOrder($productId);
            if ($preOrder == 1 || $preOrder == 2) {
                return;
            }
        }
        return $proceed($observer);
    }
}

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

class ProductCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * ProductCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\View\LayoutInterface $layout
    ) {
        $this->helper=$helper;
        $this->layout = $layout;
    }

    /**
     * @param \Magento\Catalog\Pricing\Render\PriceBox $subject
     * @param string $result
     * @return string
     */
    public function afterToHtml($subject, $result)
    {
        if ($this->helper->isEnable()) {

            $product        = $subject->getSaleableItem();
            $preOrder       = $product->getData("preorder");
            $restock        = $this->helper->formatDate($product->getData('restock'));
            $message        = $this->helper->replaceVariableX($product->getData("message"), $restock);

            if ($message=="") {
                $message = $this->helper->replaceVariableX($this->helper->getMess(), $restock);
            }

            $button = __("Pre-Order");

            if ($this->helper->getButton()) {
                $button = $this->helper->getButton();
            }

            $isInStock = $product->isAvailable();
            $handles = $this->layout->getUpdate()->getHandles();

            if (in_array('catalog_product_view', $handles)) {
                if ($preOrder == 1 || $preOrder == 2 && $isInStock) {
                    $html = "<span class='message'></span>";
                    if ($restock) {
                        $html .= "<p class='restock'><i class='fa fa-clock'></i> Pre-order: dit product is binnenkort beschikbaar en wordt verzonden op ".$restock."</p>";
                    }
                    return $result.$html."<span class='pre-order'>".$button."</span>";
                }
            }
        }
        return $result;
    }
}

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
namespace Bss\PreOrder\Plugin\Cart;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Exception\NoSuchEntityException;

class Notice
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * Notice constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param $subject
     * @param $item
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItemHtml($subject, $item)
    {
        if ($this->helper->isEnable()) {
            try {
                if ($item->getProduct()->getTypeId() == Configurable::TYPE_CODE) {
                    /* Load configurable product by sku */
                    $productSku = $item->getSku();
                    $product = $this->helper->getProductBySku($productSku);
                } else {
                    /* Load other product by id, becase some product has custom option sku */
                    $productId = $item->getProduct()->getId();
                    $product = $this->helper->getProductById($productId);
                }

                $show_mess = $this->helper->checkPreOrderAvailability($product, $item);

                if ($show_mess) {
                    $item->setMessage($this->helper->getNote());
                }
            } catch (NoSuchEntityException $e) {
                return [$item];
            }
        }
        return [$item];
    }
}

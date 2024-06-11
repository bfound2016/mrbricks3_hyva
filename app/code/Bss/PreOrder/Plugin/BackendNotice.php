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

use Bss\PreOrder\Model\Attribute\Source\Order;

class BackendNotice
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * BackendNotice constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->helper=$helper;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItem($subject, $result)
    {
        if ($this->helper->isEnable()) {
            if ($result->getProductType()=='configurable') {
                $sku = $result->getProductOptionByCode('simple_sku');
                $product = $this->helper->getProductItemBySku($sku);
                $productId = $product->getId();
            } else {
                $productId = $result->getProductId();
                $product = $this->helper->getProductItem($productId);
            }
            $isInStock = $product->isAvailable();
            $preOrder = $product->getData('preorder');
            $message = $this->helper->replaceVariableX(
                $this->helper->getNote(),
                $this->helper->formatDate($product->getData('restock'))
            );
            if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && !$isInStock)) {
                return $result->setDescription($message);
            }
        }
        return $result;
    }
}

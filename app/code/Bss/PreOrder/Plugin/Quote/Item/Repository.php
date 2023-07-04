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
 * @copyright  Copyright (c) 2021 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\PreOrder\Plugin\Quote\Item;

use Magento\Quote\Api\Data\CartItemInterface;
use Magento\Quote\Model\QuoteIdMaskFactory;

class Repository
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $data;

    /**
     * @var QuoteIdMaskFactory
     */
    protected $quoteIdMaskFactory;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    protected $quoteRepository;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $http;

    /**
     * @param \Bss\PreOrder\Helper\Data $data
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Magento\Framework\App\Request\Http $http
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $data,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Magento\Framework\App\Request\Http $http
    ) {
        $this->data = $data;
        $this->quoteRepository = $quoteRepository;
        $this->http = $http;
    }

    /**
     * Check allow mixin preorder when add product via api
     *
     * @param \Magento\Quote\Model\Quote\Item\Repository $subject
     * @param CartItemInterface $cartItem
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSave($subject, CartItemInterface $cartItem)
    {
        $fullActionName = $this->http->getRequestString();
        $quoteId = $cartItem->getQuoteId();
        if ($this->data->isEnable()
            && strpos($fullActionName, 'carts') !== false
            && $this->http->getMethod() == 'POST'
        ) {
            if (!$this->data->isMix()) {
                $sku = $cartItem->getSku();
                $product = $this->data->getProductBySku($sku);
                $quote = $this->quoteRepository->getActive($quoteId);
                $cartItems = $quote->getAllItems();
                $requestInfo['qty'] = $cartItem->getQty();
                $requestInfo['product'] = $product->getId();
                $productOption = $cartItem->getProductOption();

                //Get Super attribute from payload api
                if ($productOption && $productOption->getExtensionAttributes()) {
                    $configurableOption = $productOption->getExtensionAttributes()->getConfigurableItemOptions();
                    foreach ($configurableOption as $value) {
                        $requestInfo['super_attribute'][$value['option_id']] = $value['option_value'];
                    }
                }
                if (!empty($cartItems)) {
                    $preOrderItem = $this->data->checkPreOrderItem($product, $requestInfo);
                    $this->data->validateWithCart($cartItems, $preOrderItem);
                }
            }
        }
    }
}

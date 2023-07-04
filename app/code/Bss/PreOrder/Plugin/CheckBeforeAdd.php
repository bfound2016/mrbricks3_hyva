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

use Bss\PreOrder\Helper\Data;
use Magento\Checkout\Model\Cart;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CheckBeforeAdd
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * CheckBeforeAdd constructor.
     * @param Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Validate Product Before Add
     *
     * @param Cart $subject
     * @param mixed $productInfo
     * @param array $requestInfo
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function beforeAddProduct($subject, $productInfo, $requestInfo = null)
    {
        if ($this->helper->isEnable()) {
            if (!$this->helper->isMix()) {
                $cartItems = $subject->getQuote()->getAllItems();
                if ($this->request->getParam('super_group')) {
                    $this->helper->validateForGroupProduct($cartItems, $requestInfo);
                } else {
                    if (!empty($cartItems)) {
                        $preOrderItem = $this->helper->checkPreOrderItem($productInfo, $requestInfo);
                        $this->helper->validateWithCart($cartItems, $preOrderItem);
                    }
                }
            }
        }
        return [$productInfo, $requestInfo];
    }

    /**
     * @param $subject
     * @param $productIds
     * @return array
     */
    public function beforeAddProductsByIds($subject, $productIds)
    {
        if ($this->helper->isEnable()) {
            if (!$this->helper->isMix()) {
                $cartItems = $subject->getQuote()->getAllItems();
                if (!empty($cartItems) && !empty($productIds)) {
                    $this->helper->validateAddRelatedProduct($productIds, $cartItems);
                }
            }
        }
        return [$productIds];
    }
}

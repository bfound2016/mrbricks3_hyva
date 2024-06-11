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

class ShipmentCheck
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Response\RedirectInterface
     */
    protected $redirect;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    protected $orderRepository;


    /**
     * ShipmentCheck constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Response\RedirectInterface $redirect
     * @param \Magento\Framework\App\Response\Http $response
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
    ) {
        $this->helper=$helper;
        $this->redirect = $redirect;
        $this->response = $response;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param $subject
     * @param $proceed
     * @param \Magento\Sales\Api\Data\ShipmentInterface $entity
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundValidate($subject, $proceed, \Magento\Sales\Api\Data\ShipmentInterface $entity)
    {
        if ($this->helper->isEnable()) {
            $items = $entity->getItems();
            foreach ($items as $item) {
                if ($item->getProductType() == 'configurable') {
                    $sku = $item->getProductOptionByCode('simple_sku');
                    $product = $this->helper->getProductItemBySku($sku);
                    $isInStock = $product->isAvailable();
                } else {
                    $productId = $item->getProductId();
                    $product = $this->helper->getProductItem($productId);
                    $isInStock = $this->helper->getIsInStock($productId);
                }

                $status = $this->orderRepository->get($entity->getOrderId())->getStatus();
                if ($product->getData('preorder') == 1 || ($product->getData('preorder') == 2 && $isInStock == 0
                    && ($status == 'pending_preorder' || $status == 'processing_preorder'))) {
                    $this->redirect->redirect($this->response, '*/*/new/order_id/'
                        . $entity->getOrderId());
                    throw new \Magento\Framework\Exception\LocalizedException(__("Could not create a shipment because "
                            .$product->getName()
                            ." is a pre-order product."));
                }
            }
        }
        return $proceed($entity);
    }
}

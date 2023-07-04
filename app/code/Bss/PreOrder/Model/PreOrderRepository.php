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
 * @copyright  Copyright (c) 2021-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model;

use Bss\PreOrder\Api\PreOrderRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class PreOrderRepository implements PreOrderRepositoryInterface
{
    const CONFIG_ENABLE = 'enable';
    const CONFIG_ALLOW_MIXIN = 'mix';
    const CONFIG_STOCK_STATUS_ONLY = 'display_oos_with_pre_status_only';
    const DEFAULT_BUTTON_TEXT = 'button';
    const CART_ORDER_NOTE = 'note';
    const DEFAULT_PRODUCT_MESSAGE = 'mess';
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helperData;

    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * PreOrderRepository constructor.
     * @param \Bss\PreOrder\Helper\Data $helperData
     * @param \Magento\Catalog\Model\ProductRepository $productRepository
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helperData,
        \Magento\Catalog\Model\ProductRepository $productRepository
    ) {
        $this->helperData = $helperData;
        $this->productRepository = $productRepository;
    }

    /**
     * Get Product PreOrder data By Sku
     *
     * @param string $sku
     * @param int|null $storeId
     * @return array
     * @throws LocalizedException
     */
    public function get($sku, $storeId = null)
    {
        try {
            if ($sku) {
                $product = $this->productRepository->get($sku, false, $storeId);
                $messageProduct = $product->getData('message');
                $messageProduct = $messageProduct !== null ? $messageProduct : '';
                $templateMess = !empty(trim($messageProduct)) ? $messageProduct : $this->helperData->getMess();
                return [[
                    'preorder' => $product->getData('preorder'),
                    'pre_oder_from_date' => $product->getData('pre_oder_from_date'),
                    'pre_oder_to_date' => $product->getData('pre_oder_to_date'),
                    'availability_message' => $product->getData('availability_message'),
                    'message' => $product->getTypeId() == 'simple' ? $templateMess : null,
                    'is_in_stock' =>  $product->getData('is_salable')
                ]];
            }
        } catch (\Exception $exception) {
            throw new  LocalizedException(__($exception->getMessage()));
        }
        return [];
    }

    /**
     * Get Pre order Configuration
     *
     * @param int|null $storeId
     * @return array
     */
    public function getConfig($storeId = null)
    {
        try {
            return [[
                self::CONFIG_ENABLE => $this->helperData->isEnable($storeId),
                self::CONFIG_ALLOW_MIXIN => $this->helperData->isMix($storeId),
                self::CONFIG_STOCK_STATUS_ONLY => $this->helperData->getDisplayOutOfStock($storeId),
                self::DEFAULT_BUTTON_TEXT => $this->helperData->getButton($storeId),
                self::CART_ORDER_NOTE => $this->helperData->getNote($storeId),
                self::DEFAULT_PRODUCT_MESSAGE => $this->helperData->getMess($storeId)
            ]];
        } catch (\Exception $exception) {
            throw new  LocalizedException(__($exception->getMessage()));
        }
        return [];
    }
}

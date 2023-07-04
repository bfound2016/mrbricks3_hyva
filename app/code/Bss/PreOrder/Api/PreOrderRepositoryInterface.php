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

namespace Bss\PreOrder\Api;

interface PreOrderRepositoryInterface
{
    /**
     * Get Product PreOrder data By Sku
     *
     * @param string $sku
     * @param int|null $storeId
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($sku, $storeId = null);

    /**
     * Get Configuration PreOrder
     *
     * @param int|null $storeId
     * @return mixed
     */
    public function getConfig($storeId = null);
}

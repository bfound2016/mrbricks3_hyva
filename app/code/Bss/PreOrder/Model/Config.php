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
 * @copyright  Copyright (c) 2018-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\PreOrder\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Class Stock
 */
class Config
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Config constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface  $storeManager
    ) {
        $this->storeManager = $storeManager;
    }

    /**
     * Get Store Id
     *
     * @return int
     */
    public function getStoreId()
    {
        if ($this->storeManager->isSingleStoreMode()) {
            return 0;
        }
        try {
            return $this->storeManager->getStore()->getId();
        } catch (\Exception $exception) {
            return 0;
        }


    }
}

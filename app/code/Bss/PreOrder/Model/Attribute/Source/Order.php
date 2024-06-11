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
namespace Bss\PreOrder\Model\Attribute\Source;

class Order extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const ORDER_NO = 0;
    const ORDER_YES = 1;
    const ORDER_OUT_OF_STOCK = 2;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['label' => __('No'), 'value' => self::ORDER_NO],
                ['label' => __('Yes'), 'value' => self::ORDER_YES],
                ['label' => __('When Product Become Out Of Stock'), 'value' => self::ORDER_OUT_OF_STOCK],
            ];
        }
        return $this->_options;
    }
}

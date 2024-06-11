<?php
/**
 * BFound Digital Services.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * Custom plugin to get preorder item info into minicart
 *
 * @category   BFound
 * @package    Bss_PreOrder
 * @author     Paul Leenheer
 * @copyright  Copyright (c) 2019 BFound Digital Services.
 * @license
 */

namespace Bss\PreOrder\Plugin;

class DefaultItemPlugin
{

    public function __construct(
        \Bss\PreOrder\Helper\Data $helper

    ) {
        $this->helper   =   $helper;
    }

    public function afterGetItemData(
        \Magento\Checkout\CustomerData\AbstractItem $subject,
        $result,
        \Magento\Quote\Model\Quote\Item $item)
    {
        if ( $this->helper->isEnable() ) {
            $preOrder       = $this->helper->getPreOrder($item->getProduct()->getId());
            $reStock        = $this->helper->formatDate($item->getProduct()->getData("restock"));

            $data = ["preorder"=>$preOrder, "restock"=>$reStock];
        }

        return \array_merge(
            $result, $data
        );
    }
}

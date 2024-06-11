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
namespace Bss\PreOrder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Bss\PreOrder\Helper\Data;

class CheckBeforeUpdate implements ObserverInterface
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * CheckBeforeUpdate constructor.
     * @param Data $helper
     */
    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $quote = $observer->getCart()->getQuote();
        $infoDataObject = $observer->getInfo()->getData();
        $items = $quote->getAllItems();
        $i=0;
        if ($this->helper->isEnable() && !$this->helper->isMix()) {
            foreach ($items as $item) {
                if ($i>0) {
                    $itemId = $item->getId();
                    $productId = $item->getProductId();

                    $qtyValue=$this->helper->getStockItem($productId)->getQty();

                    $preOrder = $this->helper->getPreOrder($productId);

                    if (!isset($infoDataObject[$itemId])) {
                        continue;
                    }
                    $qtyUpdate =  $infoDataObject[$itemId]['qty'];

                    if ($preOrder==2 && $qtyUpdate>$qtyValue) {
                        $message = "We don't have as many ".$item->getName()." as you requested. ";
                        $message .= "We could not add both pre-order and regular items to an order";
                        throw new \Magento\Framework\Exception\LocalizedException(__($message));
                    }

                }
                $i++;
            }
        }
    }
}

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

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Quote\Model\Quote\Item\AbstractItem as QuoteItem;
use Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn;
use Magento\Sales\Model\Order\Item;

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
        $this->helper = $helper;
    }

    /**
     * Extra Note Pre Order Product
     *
     * @param DefaultColumn $subject
     * @param Item|QuoteItem $result
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetItem($subject, $result)
    {
        if ($this->helper->isEnable()) {
            if (!$result->getProduct()) {
                return $result;
            }
            if ($result->getProductType() == Configurable::TYPE_CODE) {
                $productId = $this->helper->getProductBySku($result->getProductOptionByCode('simple_sku'))->getId();
            } else {
                $productId = $result->getProduct()->getId();
            }
            $listProductPreOrder = $result->getOrder()->getProductPreOrder();
            if ($listProductPreOrder) {
                $listProductPreOrder = $this->helper->serializeClass()->unserialize($listProductPreOrder);
                if (!empty($listProductPreOrder) && in_array($productId, array_keys($listProductPreOrder))) {
                    return $result->setDescription($listProductPreOrder[$productId]);
                }
            }
        }
        return $result;
    }
}

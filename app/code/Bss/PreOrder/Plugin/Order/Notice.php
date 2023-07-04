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
namespace Bss\PreOrder\Plugin\Order;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

class Notice
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * OrderNotice constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->helper = $helper;
        $this->request = $request;
    }

    /**
     * Extra Note Pre Order Product
     *
     * @param \Magento\Sales\Block\Items\AbstractItems $subject
     * @param \Magento\Framework\DataObject $item
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeGetItemHtml($subject, $item)
    {
        $action = $this->request->getFullActionName();
        if ($this->helper->isEnable()) {
            if ($item->getProductType() == Configurable::TYPE_CODE) {
                if (strpos($action, 'multishipping_checkout') !== false) {
                    $product = $this->helper->getProductBySku($item->getSku());
                } else {
                    $product = $this->helper->getProductBySku($item->getProductOptionByCode('simple_sku'));
                }
            } else {
                $product = $this->helper->getProductById($item->getProductId());
            }

            if ($product && $product instanceof \Magento\Catalog\Api\Data\ProductInterface) {

                $message = $this->helper->replaceVariableX(
                    $this->helper->getNote(),
                    $this->helper->formatDate($product->getData('pre_oder_from_date')),
                    $this->helper->formatDate($product->getData('pre_oder_to_date'))
                );
                if ($this->helper->checkPreOrderAvailability($product, $item)) {
                    return [$item->setDescription($message)];
                }
            }
        }
        return [$item];
    }
}

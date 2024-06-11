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
namespace Bss\PreOrder\Block;

class Grouped extends \Magento\GroupedProduct\Block\Product\View\Type\Grouped
{
    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    protected $helper;

    /**
     * Grouped constructor.
     * @param \Bss\PreOrder\Helper\Data $helper
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\Stdlib\ArrayUtils $arrayUtils
     * @param array $data
     */
    public function __construct(
        \Bss\PreOrder\Helper\Data $helper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Stdlib\ArrayUtils $arrayUtils,
        array $data = []
    ) {
        $this->helper = $helper;
        parent::__construct(
            $context,
            $arrayUtils,
            $data
        );
    }

    /**
     * @param $productId
     * @return \Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getProductItems($productId)
    {
        return $this->helper->getProductItem($productId);
    }

    /**
     * @param int $productId
     * @return bool|int
     */
    public function getIsInStock($productId)
    {
        return $this->helper->getIsInStock($productId);
    }

    /**
     * @return bool
     */
    public function isEnable()
    {
        return $this->helper->isEnable();
    }

    /**
     * @param $product
     * @return string
     */
    public function getMess($product)
    {
        $mess = $this->helper->replaceVariableX(
            $product->getData('message'),
            $this->helper->formatDate($product->getData('restock'))
        );
        if ($mess=="") {
            $mess = $this->helper->replaceVariableX(
                $this->helper->getMess(),
                $this->helper->formatDate($product->getData('restock'))
            );
        }
        return $mess;
    }
}

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
namespace Bss\PreOrder\Plugin\Block\Product\View\Type;

use Bss\PreOrder\Model\Attribute\Source\Order;

class Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    private $linkData;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    private $jsonDecoder;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helper;

    /**
     * Configurable constructor.
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Framework\Json\DecoderInterface $jsonDecoder
     * @param \Bss\PreOrder\Helper\ProductData $linkData
     * @param \Bss\PreOrder\Helper\Data $helper
     */
    public function __construct(
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Bss\PreOrder\Helper\ProductData $linkData,
        \Bss\PreOrder\Helper\Data $helper
    ) {
        $this->linkData = $linkData;
        $this->jsonEncoder = $jsonEncoder;
        $this->jsonDecoder = $jsonDecoder;
        $this->helper=$helper;
    }

    /**
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetJsonConfig($subject, $result)
    {
        $childProduct = $this->linkData->getAllData($subject->getProduct()->getEntityId());
        $config = $this->jsonDecoder->decode($result);
        $config["preorder"] = $childProduct;
        return $this->jsonEncoder->encode($config);
    }

    /**
     * @param $subject
     * @param callable $proceed
     * @return mixed
     */
    public function aroundGetAllowProducts($subject, callable $proceed)
    {
        if ($this->helper->isEnable()) {
            $isPreOrder = false;
            $products = [];
            $usedProducts = $subject->getProduct()->getTypeInstance()->getUsedProducts($subject->getProduct(), null);
            foreach ($usedProducts as $product) {
                $preOrder = $product->getData('preoder');
                $isInStock = $product->isAvailable();
                if ($preOrder==Order::ORDER_YES || ($preOrder==Order::ORDER_OUT_OF_STOCK && $isInStock==0)) {
                    $isPreOrder = true;
                }
                if ($product->getStatus() == 1) {
                    $products[] = $product;
                }
            }
            if ($isPreOrder) {
                $subject->setAllowProducts($products);
                return $subject->getData('allow_products');
            }
        }
        $returnValue = $proceed();
        return $returnValue;
    }
}

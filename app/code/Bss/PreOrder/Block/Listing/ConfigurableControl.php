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
namespace Bss\PreOrder\Block\Listing;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Helper\Product as CatalogProduct;
use Magento\ConfigurableProduct\Helper\Data;
use Magento\ConfigurableProduct\Model\ConfigurableAttributeData;
use Magento\Customer\Helper\Session\CurrentCustomer;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\ArrayUtils;
use Magento\Swatches\Helper\Data as SwatchData;
use Magento\Swatches\Helper\Media;

/**
 * Swatch renderer block in Category page
 *
 * @codeCoverageIgnore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ConfigurableControl extends \Magento\Swatches\Block\Product\Renderer\Listing\Configurable
{
    /**
     * @var \Bss\PreOrder\Helper\ProductData
     */
    private $linkData;

    /**
     * @var \Bss\PreOrder\Helper\Data
     */
    private $helperBss;

    /**
     * ConfigurableControl constructor.
     * @param Context $context
     * @param ArrayUtils $arrayUtils
     * @param EncoderInterface $jsonEncoder
     * @param Data $helper
     * @param CatalogProduct $catalogProduct
     * @param CurrentCustomer $currentCustomer
     * @param PriceCurrencyInterface $priceCurrency
     * @param ConfigurableAttributeData $configurableAttributeData
     * @param SwatchData $swatchHelper
     * @param Media $swatchMediaHelper
     * @param \Bss\PreOrder\Helper\ProductData $linkData
     * @param \Bss\PreOrder\Helper\Data $helperBss
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ArrayUtils $arrayUtils,
        EncoderInterface $jsonEncoder,
        Data $helper,
        CatalogProduct $catalogProduct,
        CurrentCustomer $currentCustomer,
        PriceCurrencyInterface $priceCurrency,
        ConfigurableAttributeData $configurableAttributeData,
        SwatchData $swatchHelper,
        Media $swatchMediaHelper,
        \Bss\PreOrder\Helper\ProductData $linkData,
        \Bss\PreOrder\Helper\Data $helperBss,
        array $data = []
    ) {
        $this->linkData = $linkData;
        $this->helperBss = $helperBss;
        parent::__construct(
            $context,
            $arrayUtils,
            $jsonEncoder,
            $helper,
            $catalogProduct,
            $currentCustomer,
            $priceCurrency,
            $configurableAttributeData,
            $swatchHelper,
            $swatchMediaHelper,
            $data
        );
    }

    /**
     * @return string
     */
    public function getJsonChildProductData()
    {
        return $this->jsonEncoder->encode(
            $this->linkData->getAllData(
                $this->getProduct()->getEntityId()
            )
        );
    }

    /**
     * @return string
     */
    public function getRendererTemplate()
    {
        if ($this->helperBss->isEnable()) {
            return 'Bss_PreOrder::listing.phtml';
        }
        return $this->_template;
    }

    /**
     * @return string
     */
    public function checkVersion()
    {
        return $this->linkData->checkVersion();
    }
}

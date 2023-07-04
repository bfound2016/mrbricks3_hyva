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
var config = {
    config: {
        mixins: {
            "Magento_Swatches/js/swatch-renderer" : {
                "Bss_PreOrder/js/product/configurable/swatch-renderer": true
            },
            "Magento_ConfigurableProduct/js/configurable" : {
                "Bss_PreOrder/js/product/configurable/configurable": true
            },
            "Magento_Catalog/js/catalog-add-to-cart" : {
                "Bss_PreOrder/js/catalog-add-to-cart": true
            },
            "Magento_Catalog/js/product/addtocart-button" : {
                "Bss_PreOrder/js/product/addtocart-button": true
            },
            "Bss_ConfiguableGridView/js/swatch" : {
                "Bss_PreOrder/js/swatch-mixin": true
            }
        }
    }
};
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
define(
    [
        "jquery",
        "jquery/ui",
        "Bss_PreOrder/js/listing"
    ],
    function ($) {
        $.widget('bss.listing_renderer', {
            options: {
                productId:'',
                selectorProduct:'',
                onlySwatches:'',
                enableControlLabel:'',
                numberToShow:'',
                jsonConfig:'',
                jsonSwatchConfig:'',
                jsonChildProduct:'',
                mediaCallback:''
            },
            _create: function () {
                var $widget = this;

                $widget._EventListener();
            },
            _EventListener: function () {
                $('.swatch-opt-'+this.options.productId+'').SwatchRenderer({
                    selectorProduct: this.options.selectorProduct,
                    onlySwatches: this.options.onlySwatches,
                    enableControlLabel: this.options.enableControlLabel,
                    numberToShow: this.options.numberToShow,
                    jsonConfig: this.options.jsonConfig,
                    jsonSwatchConfig: this.options.jsonSwatchConfig,
                    jsonChildProduct: this.options.jsonChildProduct,
                    mediaCallback: this.options.mediaCallback
                });
            }
        });
        return $.bss.listing_renderer;
    }
);

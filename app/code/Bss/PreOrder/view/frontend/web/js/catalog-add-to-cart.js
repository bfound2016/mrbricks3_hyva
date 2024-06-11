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
define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function ($, $t) {
    'use strict';
    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
           
            enableAddToCartButton: function ($this, $widget) {
                var addToCartButtonTextAdded = $t('Added'),
                    addToCartButton = $this.find('.action.tocart'),
                    addToCartButtonTextDefault = $this.parent().parent().parent().parent().find(".pre-order").text();
                if ($this.parent().parent().parent().find(".pre-order").text()) {
                    addToCartButtonTextDefault = $this.parent().parent().parent().find(".pre-order").text();
                }
                if ( addToCartButtonTextDefault == "" || addToCartButtonTextDefault == undefined ) {
                    addToCartButtonTextDefault = $t('Add to Cart');
                }
                addToCartButton.find('span').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);

                setTimeout(function () {
                    addToCartButton.removeClass('disabled');
                    addToCartButton.find('span').text(addToCartButtonTextDefault);
                    addToCartButton.attr('title', addToCartButtonTextDefault);
                }, 1000);
            }

        });

        return $.mage.catalogAddToCart;
    }
});

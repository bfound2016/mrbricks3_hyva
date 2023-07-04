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
define([
    'jquery',
    'mage/translate'
], function ($) {
    'use strict';
    return function (widget) {

        $.widget('mage.catalogAddToCart', widget, {
            options: {
                addToCartButtonText: '.action.tocart.primary span'
            },

            /** @inheritdoc */
            _create: function () {
                this._super();
                var self = this;

                self._changeButtonText();

            },
            ajaxSubmit: function (form) {
                var self = this;

                self._changeButtonText();
                this._super(form);
            },

            _changeButtonText: function () {
                var self = this;

                self.options.addToCartButtonTextDefault = $(this.element).find(self.options.addToCartButtonText).html();
            }
        });

        return $.mage.catalogAddToCart;
    };
});

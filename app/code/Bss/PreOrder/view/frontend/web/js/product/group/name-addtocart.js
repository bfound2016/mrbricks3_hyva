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
    'mage/translate'
], function ($) {
    "use strict";
    $.widget('bss.preorder_product_add', {
        options: {
            addToCartButtonText: '.action.tocart.primary span',
            addToCartButtonSelector: '.action.tocart.primary',
            productPageContainer: '.product-info-main',
            otherPageContainer: '.product-item-details',
            count: 0
        },

        _create: function () {
            var self = this;

            self._changeButton();

        },

        _changeButton: function () {
            var self = this;

            $('input[name="group_is_preorder"]').each(function () {
                if ($(this).closest(self.options.productPageContainer).length) {
                    self._ApplyForProductPage(this);
                } else {
                    self._ApplyForOther(this);
                }
            });
        },

        _ApplyForProductPage: function (elemnt) {
            var self = this;

            // eslint-disable-next-line max-len
            $(elemnt).closest(self.options.productPageContainer).find(self.options.addToCartButtonText).text(self.options.textButton);
            // eslint-disable-next-line max-len
            $(elemnt).closest(self.options.productPageContainer).find(self.options.addToCartButtonSelector).attr('title', self.options.textButton);
        },

        _ApplyForOther: function (elemnt) {
            var self = this,
                check = true;

            if ($(elemnt).parent().find(self.options.addToCartButtonSelector).length > 0) {
                $(elemnt).parent().find(self.options.addToCartButtonText).text(self.options.textButton);
                $(elemnt).parent().find(self.options.addToCartButtonSelector).attr('title', self.options.textButton);
                check = false;
            } else {
                self.options.count++;
                if (self.options.count < 3 && check) {
                    self._ApplyButton(elemnt);
                }
            }
        }
    });

    return $.bss.preorder_product_add;
});

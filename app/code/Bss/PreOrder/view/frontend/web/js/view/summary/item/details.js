/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'uiRegistry',
    'mage/translate',
    'Magento_Ui/js/modal/confirm',
    'Magento_Checkout/js/model/quote',
    'Swissup_CheckoutCart/js/action/update-cart',
    'Swissup_CheckoutCart/js/action/remove-item'
], function ($, Component, ko, uiRegistry,$t, modalConfirm ,quote, updateCartAction, removeItemAction) {
    "use strict";

    var quoteItemData = window.checkoutConfig.quoteItemData;

    return Component.extend({
        defaults: {
            template: 'Magento_Checkout/summary/item/details'
        },

        quoteItemData: quoteItemData,

        initialize: function () {
            this._super();
            this.updateQty();
            return this;
        },

        updateQty: function () {
            console.log("update called");
            this.availableQty   =   ko.observableArray([1,2,3,4,5,"Meer"]);
        },

        updateCart: function (item, qty){

        },

        getValue: function (quoteItem) {
            return quoteItem.name;
        },

        getIsPreOrder: function(quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.preorder;
        },

        getRestockDate: function (quoteItem) {
            var item = this.getItem(quoteItem.item_id);
            return item.restock;
        },


        getItem: function(item_id) {
            var itemElement = null;
            _.each(this.quoteItemData, function(element, index) {
                if (element.item_id == item_id) {
                    itemElement = element;
                }
            });
            return itemElement;
        },

        /**
         * Mixin functionality details-mixin Swissup_CheckoutCart
         */
        incQty: function(item) {
            this.applyQty(item.item_id, $(".swissup_edit_cart_2 input.qty").val() + 1);
        },
        decQty: function(item, event) {
            if ($(".swissup_edit_cart_2 input.qty").val() - 1 === 0) {
                this.removeItem(item);
            } else {
                this.applyQty(item.item_id, $(".swissup_edit_cart_2 input.qty").val() - 1);
            }
        },
        newQty: function(item, event) {
            if (item.qty == 0) {
                this.removeItem(item, event);
            } else {
                var quoteItem = this.getQuoteItemById(item.item_id);

                if (this.isValidQty(quoteItem.qty, item.qty)) {
                    this.applyQty(item.item_id, item.qty);
                } else {
                    item.qty = quoteItem.qty;
                    $(event.target).val(item.qty);
                }
            }
        },
        applyQty: function(itemId, qty) {

            if (isNaN(qty)) {
                $(".swissup_edit_cart").hide();
                $(".swissup_edit_cart_2").show();
            }
            else {
                var quoteItem = this.getQuoteItemById(itemId);
                quoteItem.qty = qty;

                var params = {
                    cartItem: {
                        item_id: itemId,
                        qty: qty,
                        quote_id: quote.getQuoteId()
                    }
                };

                updateCartAction(quote, params);
            }
        }
        ,
        removeItem: function(item, event) {
            var quoteItem = this.getQuoteItemById(item.item_id);
            modalConfirm({
                content: $t('Are you sure you want to remove this item?'),
                actions: {
                    confirm: function() {
                        removeItemAction(quote, item.item_id);
                    },
                    cancel: function () {
                        if (event) {
                            item.qty = quoteItem.qty;
                            $(event.target).val(item.qty);
                        }
                    }
                }
            });
        },
        isValidQty: function (origin, changed) {
            return (origin != changed) &&
                (changed.length > 0) &&
                (changed - 0 == changed) &&
                (changed - 0 > 0);
        },
        getQuoteItemById: function(item_id) {
            return $.grep(quote.getItems(), function(item) {
                return item.item_id == item_id;
            })[0];
        }

    });
});

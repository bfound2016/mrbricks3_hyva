/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Customer/js/view/customer'
], function ($, Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            var review = this.getUrlParameter('review');
            if (review) {
                review = window.atob(review);
                review = JSON.parse(review);
                if (typeof review['orderId'] !== "undefined" && review['orderId'] != '') {
                    $('#review-form').append('<input name="orderId" type="hidden" value="' + review['orderId'] + '">');
                }
            }
            this.review = customerData.get('review').extend({
                disposableCustomerData: 'review'
            });
        },

        /**
         * @return {*}
         */
        nickname: function () {
            return this.review().nickname || customerData.get('customer')().firstname;
        },

        /**
         * @param string sParam
         * @returns {*}
         */
        getUrlParameter: function (sParam) {
            var sPageURL = window.location.search.substring(1),
                sURLVariables = sPageURL.split('&'),
                sParameterName,
                i;

            for (i = 0; i < sURLVariables.length; i++) {
                sParameterName = sURLVariables[i].split('=');

                if (sParameterName[0] === sParam) {
                    return sParameterName[1] === undefined ? true : decodeURIComponent(sParameterName[1]);
                }
            }
        }
    });
});
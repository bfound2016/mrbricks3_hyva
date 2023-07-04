define([
    'uiComponent',
    'mage/translate'
], function (Component, $t) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_PreOrder/view/pre_order/details'
        },
        initialize: function () {
            return this._super();
        },
        getLabelPreOrder: function (items) {
            var pids = window.checkoutConfig.pre_order_ids;
            var note = window.checkoutConfig.pre_order_note;
            var enable = window.checkoutConfig.pre_order_enable;

            if (undefined !== items.item_id && enable) {
                if (pids.indexOf(items.item_id) !== -1) {
                    // eslint-disable-next-line max-depth,eqeqeq
                    if (undefined != note && note !== '') {
                        return $t(note);
                    }
                    return $t('Pre-Ordered Product');
                }
                return false;
            }
            return false;
        }
    });
});

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
define(
    ['mage/translate'],
    function ($t) {
        'use strict';

        return function (Component) {
            return Component.extend({
                isSalable: function (row) {
                    window.labelNotPreOrder = $t('Add to Cart');
                    if (row['add_to_cart_button']['pre-order'] || row['extension_attributes']['pre_order']) {
                        row['is_salable'] = 1;
                        this.label = row['add_to_cart_button']['pre-order'] ? row['add_to_cart_button']['pre-order'] : row['extension_attributes']['pre_order'];
                    } else {
                        this.label = window.labelNotPreOrder;
                    }
                    return this._super(row);
                }
            });
        };
    }
);

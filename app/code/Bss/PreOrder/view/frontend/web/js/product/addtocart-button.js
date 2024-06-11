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
    ],
    function () {
        'use strict';

        return function (Component) {
            return Component.extend({
                isSalable: function (row) {
                    if (row['add_to_cart_button']['pre-order']) {
                        this.label = row['add_to_cart_button']['pre-order'];
                    }
                    return this._super(row);
                }
            });
        }
    }
);

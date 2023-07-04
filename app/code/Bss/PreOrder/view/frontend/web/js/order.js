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
    'jquery'
], function ($) {
    "use strict";
    $.widget('bss.order_preorder_product', {
        _create: function () {
            $("span.order-notice").hide();
            $("span.order-notice").css('margin-bottom','10px');
            $("span.note-pieces").css('display','none');
            var isPreOrder = true;

            $("span.note-pieces").each(
                function () {
                    let note = $(this).html();
                    if ($('td.col.name:contains("'+note+'")').length == 0) {
                        isPreOrder = false;
                    }
                }
            );
            if (isPreOrder) {
                $('.page-title-wrapper').append($("span.order-notice"));
                $("span.order-notice").css('display','block');
            }
        },
    });
    return bss.order_preorder_product;
});

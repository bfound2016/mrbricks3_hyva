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
require(
    [
    'jquery'
    ],
    function ($) {
        $('span[class="pre-order"]').each(
            function () {
                $(this).parent().find('.action.tocart.primary span').text($(this).text());
                $(this).hide();
            }
        );
        $("span.order-notice").hide();
        $("span.note-pieces").hide();
        var isPreOrder = true;
        $("span[class='note-pieces']").each(
            function () {
                var note = $(this).html();
                if ($('.cart.item.message.notice div:contains("'+note+'")').length == 0) {
                    isPreOrder = false;
                }
            }
        );
        if (isPreOrder) {
            $('.page.messages').prepend($("span.order-notice"));
            $("span.order-notice").show();
        }
    }
);

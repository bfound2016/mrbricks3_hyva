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
        $(".pre-order").parent().find(".message").hide();
        $(".pre-order").parent().find(".restock").hide();
        $(".pre-order").hide();
        $(".row-tier-price td").each(
            function () {
                $(this).append($(this).parent().parent().find(".preorder-message"));
            }
        );
    }
);
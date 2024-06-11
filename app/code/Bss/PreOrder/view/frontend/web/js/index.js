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
    return function () {
        $('span[class="pre-order"]').each(
            function () {
                //$(this).next().find('.action.tocart.primary span').text($(this).text());
                //$(this).next().next().find('.action.tocart.primary span').text($(this).text());
                // $(this).next().next().next().find('.action.tocart.primary span').text($(this).text());
                // $(this).parent().next().find('.action.tocart.primary span').text($(this).text());
                //$(this).css("display","none");
            }
        );
    }
});
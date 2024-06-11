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
                $(this).parent().parent().find('.product-add-form').find('.action.primary.tocart').text($(this).text());
                $('div.stock span').remove();
                $(this).css("display","none");
            }
        );
        $('p[class="restock"]').each(
            function () {
                $(this).parent().find('div.stock').prepend($(this));
            }
        );
    }
);
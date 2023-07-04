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
 * @package    Bss_ReviewReminder
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    return function(config){
        window.reviewReminderSendTestEmail = function(){
        
            $.ajax({
                url: config.adminUrl,
                type: 'post',
                dataType: 'html',
                data: {
                    form_key: FORM_KEY,
                    email: $('#reviewreminder_debug_email_test').val(),
                },
                showLoader: true
            }).done(function(data) {
                alert({
                    title:'Result',
                    content:data
                });
            });
        }
    }
});

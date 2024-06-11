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
    'jquery',
    'mage/translate',
    'underscore'
], function ($, $t) {
    'use strict';
    return function (widget, _) {

        $.widget('mage.configurable', widget, {
          
            /**
             * Configure an option, initializing it's state and enabling related options, which
             * populates the related option's selection and resets child option selections.
             * @private
             * @param {*} element - The element associated with a configurable option.
             */
            _configureElement: function (element) {
                this._super(element);

                this._UpdateDetailPreOrder();
            },

            _UpdateDetailPreOrder: function () {
                var $widget = this,
                    index = '',
                    childProductData = this.options.spConfig.preorder;
                $(".super-attribute-select").each(function () {
                    var option_id = $(this).attr("option-selected");
                    if (typeof option_id === "undefined" && $(this).val() !== "") {
                        option_id = $(this).val();
                    }
                    if (option_id !== null && $(this).val() !== "") {
                        index += option_id + '_';
                    }
                });
                if (!childProductData['child'].hasOwnProperty(index)) {
                    $widget._ResetPreOrder();
                    return false;
                }
                $widget._UpdatePreOrder(
                    childProductData['child'][index]['stock_status'],
                    childProductData['child'][index]['productId'],
                    childProductData['child'][index]['preorder'],
                    childProductData['child'][index]['restock'],
                    childProductData['child'][index]['message'],
                    childProductData['child'][index]['button']
                );
            },

            _UpdatePreOrder: function ($status, $productId, $preorder, $restock, $message, $button) {
                if ($preorder==1 || ($preorder==2 && !$status)) {
                    $('#product-addtocart-button').removeAttr('disabled');
                    $('#product-addtocart-button').html($button);
                    $('.container-child-product').html("");
                    $('.product-info-stock-sku .stock span').html("");
                    if ($restock) {
                        $('.product-info-stock-sku .stock span').html($t("Availability Date: ")+$restock);
                    }
                    $('.mess-preorder').remove();
                    $('.product-add-form').prepend("<span class='mess-preorder'>"+$message+"<span>");
                } else if ($status) {
                    $('#product-addtocart-button').removeAttr('disabled');
                    $('#product-addtocart-button').html($t('Add to Cart'));
                    $('.container-child-product').html("");
                    $('.mess-preorder').remove();
                    $('.product-info-stock-sku .stock span').html($t("In Stock"));
                } else {
                    $('#product-addtocart-button').attr('disabled', 'disabled');
                    $('#product-addtocart-button').html($t('Add to Cart'));
                    $('.mess-preorder').remove();
                    $('.product-info-stock-sku .stock span').html($t("Out of Stock"));
                }
            },

            _ResetPreOrder: function ($form) {
                if (this.options.spConfig.preorder['stock_status'] > 0) {
                    $('#product-addtocart-button').removeAttr('disabled');
                    $('.container-child-product').html("");
                } else {
                    $('#product-addtocart-button').attr('disabled', 'disabled');
                    $('.container-child-product').html("");
                }
                $('.mess-preorder').html("");
                $('.product-info-stock-sku .stock span').html($t("In Stock"));
            },
            _UpdatePrice: function () {
                var $widget = this,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    index = '',
                    childProductData = this.options.spConfig.preorder,
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result;

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');

                    options[attributeId] = $(this).attr('option-selected');
                });

                result = $widget.options.spConfig.optionPrices[_.findKey($widget.options.spConfig.index, options)];

                $productPrice.trigger(
                    'updatePrice',
                    {
                        'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                    }
                );

                if (!childProductData['child'].hasOwnProperty(index)) {
                    return false;
                }

                if (childProductData['child'][index]['stock_status'] > 0) {
                    if (result.oldPrice.amount !== result.finalPrice.amount) {
                        $(this.options.slyOldPriceSelector).show();
                    } else {
                        $(this.options.slyOldPriceSelector).hide();
                    }
                }
            }
        });

        return $.mage.configurable;
    }
});

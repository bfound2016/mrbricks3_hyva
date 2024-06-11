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
    'underscore',
    'mage/translate',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'mage/translate'
], function ($, _, $t) {
    'use strict';
    return function (widget) {

        $.widget('mage.SwatchRenderer', widget, {

            /**
             * Event for swatch options
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnClick: function ($this, $widget) {
                $widget._super($this, $widget);
                if (!(this.options.jsonConfig.preorder == undefined)) {
                    $widget._UpdateDetailPreOrder();
                }
            },

            /**
             * Event for select
             *
             * @param {Object} $this
             * @param {Object} $widget
             * @private
             */
            _OnChange: function ($this, $widget) {
                $widget._super($this, $widget);
                if (!(this.options.jsonConfig.preorder == undefined)) {
                    $widget._UpdateDetailPreOrder();
                }
            },

            _UpdateDetailPreOrder: function () {
                var $widget = this,
                    index = '',
                    childProductData = this.options.jsonConfig.preorder;
                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
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
                    $('.price-box.price-final_price').css('display', 'block');
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
                    $('.price-box.price-final_price').css('display', 'block');
                    $('#product-addtocart-button').removeAttr('disabled');
                    $('#product-addtocart-button').html($t('Add to Cart'));
                    $('.container-child-product').html("");
                    $('.mess-preorder').remove();
                    $('.product-info-stock-sku .stock span').html($t("In Stock"));
                } else {
                    $('.price-box.price-final_price').css('display', 'none');
                    $('#product-addtocart-button').attr('disabled', 'disabled');
                    $('#product-addtocart-button').html($t('Add to Cart'));
                    $('.mess-preorder').remove();
                    $('.product-info-stock-sku .stock span').html($.mage.__($t('Out of Stock')));
                }
            },

            _ResetPreOrder: function ($form) {
                if (this.options.jsonConfig.preorder['stock_status'] > 0) {
                    $('.price-box.price-final_price').css('display', 'block');
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
                    childProductData = this.options.jsonConfig.preorder,
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result;

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    index += $(this).attr('option-selected') + '_';
                });

                $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                    var attributeId = $(this).attr('attribute-id');

                    options[attributeId] = $(this).attr('option-selected');
                });

                result = $widget.options.jsonConfig.optionPrices[_.findKey($widget.options.jsonConfig.index, options)];

                //Set Min prices
                var min = this.options.jsonConfig.optionPrices[Object.keys(this.options.jsonConfig.optionPrices)[0]].finalPrice.amount;
                $.each(this.options.jsonConfig.optionPrices, function (index, value) {
                    if ( value.finalPrice.amount < min ) {
                        min = value.finalPrice.amount;
                    }
                });
                this.options.jsonConfig.prices.basePrice.amount = min;
                this.options.jsonConfig.prices.finalPrice.amount = min;

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

        return $.mage.SwatchRenderer;
    }
});

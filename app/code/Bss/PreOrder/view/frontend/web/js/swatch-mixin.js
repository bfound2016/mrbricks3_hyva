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
    'mage/template',
    'mage/translate'
], function ($, template, $t) {
    'use strict';

    var mixinSwatch = {
        _create: function () {
            var $widget = this;

            $widget._super();

            if (this.isEnabledPackage()) {
                // eslint-disable-next-line max-len
                $widget.options.oldtextstock = $($widget.options.productPageContainer).find($widget.options.stockSelector).html();
                $widget.element.on(
                    'click',
                    function () {
                        $('.bss-swatch .swatch-option').removeClass('selected');
                        $(this).find('.swatch-option').addClass('selected');
                        $('.item-info').removeClass('bss-selected-cp');
                        $widget.element.parent('.item-info').addClass('bss-selected-cp');
                        $widget._UpdateDetailPreOrder();
                        return $widget._LoadProductMedia($(this));
                    }
                );
                $widget.element.parent('.item-info').click(function () {
                    if (!$(this).hasClass('bss-selected-cp')) {
                        $('.item-info').removeClass('bss-selected-cp');
                        $(this).addClass('bss-selected-cp');
                        $(this).find('.bss-swatch').first().click();
                    }
                });
                $('#bss_configurablegridview .qty_att_product').on("change", function () {
                    if (!$(this).parents('.item-info').hasClass('bss-selected-cp')) {
                        $(this).parents('.item-info').addClass('bss-selected-cp');
                        $(this).parents('.item-info').find('.bss-swatch').first().click();
                    }
                });
            }
        },

        /**
         * Get chosen product
         *
         * @returns int|null
         */
        getProductChild: function () {
            var products = this._CalcProducts();

            // eslint-disable-next-line no-undef,eqeqeq
            return _.isArray(products) && products.length == 1 ? products[0] : null;
        },

        _UpdateDetailPreOrder: function () {
            var $widget = this,
                productId,
                childProductData = this.options.jsonConfig.preorder,
                $parent = ".product-item-info";

            if ($('.catalog-product-view').length) {
                $parent = ".product-info-main";
            }
            productId = $widget.getProductChild();
            if (undefined === productId) {
                // Refer to Bss_ConfiguableGridView/js/swatch/configurable.js L201
                productId = this.element.parent('.item-info').attr("product_id");
            }
            if (productId && childProductData['child'].hasOwnProperty(productId)) {
                $widget._UpdatePreOrder(
                    childProductData['child'][productId]['stock_status'],
                    childProductData['child'][productId]['preorder'],
                    childProductData['child'][productId]['availability_preorder'],
                    childProductData['child'][productId]['message'],
                    childProductData['child'][productId]['button'],
                    childProductData['child'][productId]['availability_message'],
                    $parent
                );
            } else {
                $widget._ResetPreOrder($parent);
            }
        },

        // eslint-disable-next-line max-len
        _UpdatePreOrder: function (status, preorder, availability_preorder, message, button, availability_message, parent) {
            var $widget = this;

            $($widget.element).parents(parent).find($widget.options.availabilityMessageClass).remove();
            // eslint-disable-next-line eqeqeq
            if ( preorder == 1 && availability_preorder || preorder == 2 && !status) {
                if (availability_message) {
                    if (!$($widget.element).parents(parent).find($widget.options.availabilityMessageClass).length) {
                        let availabilityMessage = $t(availability_message);
                        let messageTemplate = template($widget.options.tmplAvailabilityMessage);
                        let messageHtml = messageTemplate({message: availabilityMessage});
                        $($widget.element).parents(parent).find($widget.options.stockSelector).after(messageHtml);
                    } else {
                        $($widget.element).parents(parent).find($widget.options.availabilityMessageClass).empty().html(availability_message);
                    }
                }
                $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html(button);
                $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', button);
                $($widget.element).parents(parent).find('form').prepend($widget.options.preOrderInput);
            } else {
                if ($widget.options.oldtextstock !='') {
                    $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                }
                $($widget.element).parents(parent).find($widget.options.availabilityMessageClass).remove();
                $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html($t('Add to Cart'));
                $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', $t('Add to Cart'));
                $($widget.element).parents(parent).find('input[name=is_preorder]').remove();
            }
        },

        _ResetPreOrder: function (parent) {
            var $widget = this;
            if ($widget.options.oldtextstock !='') {
                $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
            }
            $($widget.element).parents(parent).find('input[name=is_preorder]').remove();
        },

        _UpdatePrice: function () {
            this._super();
            if (this.isEnabledPackage()) {
                var $widget = this,
                    productId,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    childProductData = this.options.spConfig.preorder,
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result;
                if (!$.isEmptyObject(childProductData) && childProductData) {

                    $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                        var attributeId = $(this).attr('attribute-id');

                        options[attributeId] = $(this).attr('option-selected');
                    });

                    result = $widget.options.spConfig.optionPrices[_.findKey($widget.options.spConfig.index, options)];

                    //Set Min prices
                    var min = this.options.spConfig.optionPrices[Object.keys(this.options.spConfig.optionPrices)[0]].finalPrice.amount;

                    $.each(this.options.spConfig.optionPrices, function (index, value) {
                        if (value.finalPrice.amount < min) {
                            min = value.finalPrice.amount;
                        }
                    });
                    this.options.spConfig.prices.basePrice.amount = min;
                    this.options.spConfig.prices.finalPrice.amount = min;

                    $productPrice.trigger(
                        'updatePrice',
                        {
                            'prices': $widget._getPrices(result, $productPrice.priceBox('option').prices)
                        }
                    );

                    productId = $widget.getProductChild();
                    if (!childProductData['child'].hasOwnProperty(productId)) {
                        return false;
                    }

                    if (childProductData['child'][productId]['stock_status'] > 0) {
                        if (result.oldPrice.amount !== result.finalPrice.amount) {
                            $(this.options.slyOldPriceSelector).show();
                        } else {
                            $(this.options.slyOldPriceSelector).hide();
                        }
                    }
                }
            }
        },
        isEnabledPackage: function () {
            var jsonConfig = this.options.jsonConfig;

            if (undefined !== jsonConfig.preorder &&
                undefined !== jsonConfig.isEnabledPackage &&
                jsonConfig.isEnabledPackage === 1) {
                return true;
            }
            return false;
        }
    };

    return function (swatchWidget) {
        $.widget('bss.swatch', swatchWidget, mixinSwatch);
        return $.bss.swatch;
    }
});

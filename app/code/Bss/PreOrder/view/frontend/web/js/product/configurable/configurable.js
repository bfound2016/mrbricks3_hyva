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
    'mage/template',
    'underscore'
], function ($, $t, template) {
    'use strict';
    return function (widget, _) {

        $.widget('mage.configurable', widget, {
            defaultAddToCartText: undefined,
            options: {
                addToCartButtonText: '.action.tocart.primary span',
                addToCartButtonSelector: '.action.tocart.primary',
                stockSelector: '.product-info-stock-sku .stock',
                productPageContainer: '.product-info-main',
                otherPageContainer: '.product-item-details',
                preOrderInput: '<input type="hidden" name="is_preorder" value="1">',
                oldtextstock:'',
                availabilityMessageClass: '.product-info-stock-sku .stock p.bss-pre-order-availability-message',
                tmplAvailabilityMessage: '<p class="bss-pre-order-availability-message"><%- message %></p>'
            },

            /**
             * @private
             */
            _create: function () {
                var $widget = this,
                    count = 0,
                    size =0;

                // eslint-disable-next-line max-len
                // _configureElement will run before in $widget._super();, so change default text add tocart
                this.defaultAddToCartText = $(this.element).parents('.product-info-main').find($widget.options.addToCartButtonText).text();
                // eslint-disable-next-line max-len
                $widget.options.oldtextstock = $($widget.options.productPageContainer).find($widget.options.stockSelector).html();
                $widget._super();
            },

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
                    productId = this.simpleProduct,
                    childProductData = this.options.spConfig.preorder,
                    $parent = ".product-info-main";

                if (productId) {
                    if (childProductData['child']) {
                        if (childProductData['child'].hasOwnProperty(productId)) {
                            $widget._UpdatePreOrder(
                                childProductData['child'][productId]['stock_status'],
                                childProductData['child'][productId]['preorder'],
                                childProductData['child'][productId]['availability_preorder'],
                                childProductData['child'][productId]['message'],
                                childProductData['child'][productId]['button'],
                                childProductData['child'][productId]['availability_message'],
                                $parent
                            );
                        }
                    }
                } else {
                    $widget._ResetPreOrder($parent);
                }
            },

            _UpdatePreOrder: function (
                status,
                preorder,
                availability_preorder,
                message,
                button,
                availability_message,
                parent
            ) {
                var $widget = this;
                $($widget.element).parents(parent).find($widget.options.availabilityMessageClass).remove();
                // eslint-disable-next-line eqeqeq
                if (preorder == 1 && availability_preorder || preorder == 2 && !status) {
                    if ($widget.options.oldtextstock !='') {
                        if (!status) {
                            $($widget.element).parents(parent).find(this.options.stockSelector).children('span').html($t('Out Of Stock'));
                        } else {
                            // eslint-disable-next-line max-len
                            $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                        }
                    }
                    if (availability_message) {
                        // eslint-disable-next-line max-depth
                        let availabilityMessage = $t(availability_message);
                        let messageTemplate = template($widget.options.tmplAvailabilityMessage);
                        let messageHtml = messageTemplate({message: availabilityMessage});
                        let elementAvailMessage = $($widget.element).parents(parent).find($widget.options.availabilityMessageClass);
                        if (!elementAvailMessage.length) {
                            $($widget.element).parents(parent).find($widget.options.stockSelector).html($($widget.element).parents(parent).find($widget.options.stockSelector).html() + messageHtml);
                        } else {
                            elementAvailMessage.text(availabilityMessage);
                        }
                    }
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html(button);
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', button);

                    /* Fix for CP 1 attributes and All options is pre Order */
                    if ($($widget.element).parents(parent).find('.mess-preorder').length) {
                        if(!this.options.spConfig.isEnabledPackage) {
                            $($widget.element).parents(parent).find('.mess-preorder').html(message);
                        }
                    } else {
                        $("<span class='mess-preorder'>" + message + "<span>").insertAfter($widget.element);
                    }
                    $($widget.element).parents(parent).find('form').prepend($widget.options.preOrderInput);
                } else {
                    if ($widget.options.oldtextstock !='') {
                        $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                    }
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html($t('Add to Cart'));
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', $t('Add to Cart'));
                    if(!this.options.spConfig.isEnabledPackage) {
                        $($widget.element).parents(parent).find('.mess-preorder').remove();
                    }
                    $($widget.element).parents(parent).find('input[name=is_preorder]').remove();
                }
            },

            _ResetPreOrder: function (parent) {
                var $widget = this;
                if ($widget.options.oldtextstock !='') {
                    $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                }
                if (undefined !== $widget.defaultAddToCartText) {
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html($t($widget.defaultAddToCartText));
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', $t($widget.defaultAddToCartText));
                } else {
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html($t('Add to Cart'));
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', $t('Add to Cart'));
                }
                if(!this.options.spConfig.isEnabledPackage) {
                    $($widget.element).parents(parent).find('.mess-preorder').remove();
                }
                $($widget.element).parents(parent).find('input[name=is_preorder]').remove();
            },

            _UpdatePrice: function () {
                var $widget = this,
                    productId,
                    $product = $widget.element.parents($widget.options.selectorProduct),
                    $productPrice = $product.find(this.options.selectorProductPrice),
                    childProductData = this.options.spConfig.preorder,
                    options = _.object(_.keys($widget.optionsMap), {}),
                    result;

                $widget._super();
                if (!$.isEmptyObject(childProductData) && childProductData) {
                    $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected] ,' +
                        '.' + $widget.options.classes.attributeClass + '[data-option-selected]').each(function () {
                        var attributeId = $(this).attr('attribute-id') || $(this).attr('data-attribute-id');

                        options[attributeId] = $(this).attr('option-selected') || $(this).attr('data-option-selected');
                    });

                    result = $widget.options.spConfig.optionPrices[_.findKey($widget.options.spConfig.index, options)];

                    //Set Min prices
                    var min = this.options.spConfig.optionPrices[Object.keys(this.options.spConfig.optionPrices)[0]].finalPrice.amount;
                    $.each(this.options.spConfig.optionPrices, function (index, value) {
                        if ( value.finalPrice.amount < min ) {
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
        });

        return $.mage.configurable;
    }
});

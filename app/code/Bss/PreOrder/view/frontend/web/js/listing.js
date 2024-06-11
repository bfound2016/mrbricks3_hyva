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
    'jquery/ui',
    'underscore',
    'jquery/jquery.parsequery',
    'mage/validation/validation',
    'Magento_Swatches/js/swatch-renderer'
], function ($, $t) {
    $.widget('bss.SwatchRenderer', $.mage.SwatchRenderer, {

        /**
         * Event for swatch options
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnClick: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                $label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.hasClass('disabled')) {
                return;
            }

            if ($this.hasClass('selected')) {
                $parent.removeAttr('option-selected').find('.selected').removeClass('selected');
                $input.val('');
                $label.text('');
            } else {
                $parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
                $label.text($this.attr('option-label'));
                $input.val($this.attr('option-id'));
                $this.addClass('selected');
            }

            $widget._UpdateDetailStock();
            $widget._Rebuild();

            if ($widget.element.parents($widget.options.selectorProduct)
                    .find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
            ) {
                $widget._UpdatePrice();
            }

            $widget._LoadProductMedia();
            $input.trigger('change');
        },

        /**
         * Event for select
         *
         * @param {Object} $this
         * @param {Object} $widget
         * @private
         */
        _OnChange: function ($this, $widget) {
            var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
                attributeId = $parent.attr('attribute-id'),
                $input = $parent.find('.' + $widget.options.classes.attributeInput);

            if ($widget.inProductList) {
                $input = $widget.productForm.find(
                    '.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
                );
            }

            if ($this.val() > 0) {
                $parent.attr('option-selected', $this.val());
                $input.val($this.val());
            } else {
                $parent.removeAttr('option-selected');
                $input.val('');
            }

            $widget._Rebuild();
            $widget._UpdatePrice();
            $widget._LoadProductMedia();
            $input.trigger('change');
        },

        /**
         * Event for more switcher
         *
         * @param {Object} $this
         * @private
         */
        _OnMoreClick: function ($this) {
            $this.nextAll().show();
            $this.blur().remove();
        },

        /**
         * Rewind options for controls
         *
         * @private
         */
        _Rewind: function (controls) {
            // controls.find('div[option-id], option[option-id]').removeClass('disabled').removeAttr('disabled');
            // controls.find('div[option-empty], option[option-empty]').attr('disabled', true).addClass('disabled');
        },

        /**
         * Rebuild container
         *
         * @private
         */
        _Rebuild: function () {

            var $widget = this,
                controls = $widget.element.find('.' + $widget.options.classes.attributeClass + '[attribute-id]'),
                selected = controls.filter('[option-selected]');

            // Enable all options
            $widget._Rewind(controls);

            // done if nothing selected
            if (selected.size() <= 0) {
                return;
            }

            // Disable not available options
            controls.each(function () {
                var $this = $(this),
                    id = $this.attr('attribute-id'),
                    products = $widget._CalcProducts(id);

                if (selected.size() === 1 && selected.first().attr('attribute-id') === id) {
                    return;
                }

                $this.find('[option-id]').each(function () {
                    var $element = $(this),
                        option = $element.attr('option-id');

                    if (!$widget.optionsMap.hasOwnProperty(id) || !$widget.optionsMap[id].hasOwnProperty(option) ||
                        $element.hasClass('selected') ||
                        $element.is(':selected')) {
                        return;
                    }
                });
            });
        },

        _UpdateDetailStock: function () {

            var $widget = this,
                $this = $widget.element,
                index = '',
                childProductData = this.options.jsonChildProduct;
            $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                index += $(this).attr('option-selected') + '_';
            });
            if (!childProductData['child'].hasOwnProperty(index)) {
                $widget._ResetStock();
                return false;
            }
            $widget._UpdateStock(
                childProductData['child'][index]['stock_status'],
                childProductData['child'][index]['productId'],
                childProductData['child'][index]['preorder'],
                childProductData['child'][index]['button']
            );
        },

        _UpdateStock: function ($status, $productId, $preorder, $button) {
            var $widget = this,
                $this = $widget.element;
            if ($preorder==1 || ($preorder==2 && !$status)) {
                $this.parent().find('.action.tocart.primary').removeAttr('disabled');
                $this.parent().find('.action.tocart.primary').html($button);
            } else if ($status) {
                $this.parent().find('.action.tocart.primary').removeAttr('disabled');
                $this.parent().find('.action.tocart.primary').html($t('Add to Cart'));
            } else {
                $this.parent().find('.action.tocart.primary').attr('disabled', 'disabled');
                $this.parent().find('.action.tocart.primary').html($t('Out Of Stock'));
            }

        },

        _ResetStock: function ($form) {
            var $widget = this,
                $this = $widget.element;
            $this.parent().find('.action.tocart.primary').removeAttr('disabled');
            $this.parent().find('.action.tocart.primary').html($t('Add to Cart'));
        }
    });
    return $.bss.SwatchRenderer;
});
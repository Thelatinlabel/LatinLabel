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
            options: {
                addToCartButtonText: '.action.tocart.primary span',
                addToCartButtonSelector: '.action.tocart.primary',
                stockSelector: '.product-info-stock-sku .stock span',
                productPageContainer: '.product-info-main',
                otherPageContainer: '.product-item-details',
                preOrderInput: '<input type="hidden" name="is_preorder" value="1">',
                oldtextstock:''
            },

            /**
             * @private
             */
            _create: function () {
                var $widget = this;
                $widget._super();
                $widget.options.oldtextstock = $($widget.options.productPageContainer).find($widget.options.stockSelector).html();
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

                if (productId && childProductData['child'].hasOwnProperty(productId)) {
                    $widget._UpdatePreOrder(
                        childProductData['child'][productId]['stock_status'],
                        childProductData['child'][productId]['preorder'],
                        childProductData['child'][productId]['restock'],
                        childProductData['child'][productId]['message'],
                        childProductData['child'][productId]['button'],
                        $parent
                    );
                } else {
                    $widget._ResetPreOrder($parent);
                }
            },

            _UpdatePreOrder: function (status, preorder, restock, message, button, parent) {
                var $widget = this;

                if (preorder == 1 || (preorder == 2 && !status)) {
                    
                    if (restock) {
                        $($widget.element).parents(parent).find($widget.options.stockSelector).html($t("Availability Date: ") + restock);
                    } else if ($widget.options.oldtextstock !='') {
                        $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                    }
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html(button);
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', button);

                    /* Fix for CP 1 attributes and All options is pre Order */
                    if ($($widget.element).parents(parent).find('.mess-preorder').length) {
                        $($widget.element).parents(parent).find('.mess-preorder').html(message);
                    } else {
                        $("<span class='mess-preorder'>" + message + "<span>").insertAfter($widget.element);
                    }
                    $($widget.element).parents(parent).find('form').prepend($widget.options.preOrderInput);
                } else {
                    if ($widget.options.oldtextstock !='') {
                        $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                    }
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonText).html($t('Add to Bag'));
                    $($widget.element).parents(parent).find($widget.options.addToCartButtonSelector).attr('title', $t('Add to Bag'));
                    $($widget.element).parents(parent).find('.mess-preorder').remove();
                    $($widget.element).parents(parent).find('input[name=is_preorder]').remove();
                }
            },

            _ResetPreOrder: function (parent) {
                var $widget = this;
                if ($widget.options.oldtextstock !='') {
                    $($widget.element).parents(parent).find($widget.options.stockSelector).html($widget.options.oldtextstock);
                }
                $($widget.element).parents(parent).find('.mess-preorder').remove();
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

                    $widget.element.find('.' + $widget.options.classes.attributeClass + '[option-selected]').each(function () {
                        var attributeId = $(this).attr('attribute-id');

                        options[attributeId] = $(this).attr('option-selected');
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

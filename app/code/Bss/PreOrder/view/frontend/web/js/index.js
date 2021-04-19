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
    'mage/translate'
], function ($, $t) {
    "use strict";
    $.widget('bss.preorder_product', {
        options: {
            addToCartButtonText: '.action.tocart.primary span',
            addToCartButtonSelector: '.action.tocart.primary',
            stockSelector: '.product-info-stock-sku .stock span',
            productPageContainer: '#maincontent',
            otherPageContainer: '.product-item-details',
            AddToCartContainer: '.product-item-actions',
            comparePageContainer: '.cell.product.info',
            comparePage: '.catalog-product_compare-index',
            preOrderInput: '<input type="hidden" name="is_preorder" value="1">'
        },

        _create: function () {
            var self = this;
            self._changeButton();
            $('body').on('contentUpdated', function () {
                self._changeButton();
            })

        },

        _changeButton: function () {
            var self = this;
            $('div[class="bss-pre-order"]').each(function () {
                if ($(this).parents('.catalog-product-view .product-info-main').length) {
                    self._ApplyForProductPage(this);
                } else {
                    self._ApplyForOther(this);
                }

                $(this).show();
                $(this).remove();
            });
        },

        _ApplyForProductPage: function (elemnt) {
            var self = this;
            if ( $(elemnt).closest(self.options.comparePage).length) {
                self.options.productPageContainer = self.options.comparePageContainer
            }
            $(elemnt).parents(self.options.productPageContainer).find('#product_addtocart_form ' + self.options.addToCartButtonText).text(self.options.buttonText);
            $(elemnt).parents(self.options.productPageContainer).find('#product_addtocart_form ' + self.options.addToCartButtonSelector).attr('title', self.options.buttonText);
            if (self.options.restock) {
                var html = $t('Availability Date: ') + self.options.restock;
                $(elemnt).parents(self.options.productPageContainer + ' .product-info-main').find(self.options.stockSelector).html(html);
            }
            var formElement = $(elemnt).parents(self.options.productPageContainer).find('#product_addtocart_form').first();
            formElement.prepend(self.options.preOrderInput);
            $(elemnt).find('.mess-preorder').detach().insertBefore(formElement);
        },

        _ApplyForOther: function (elemnt) {
            var self = this;
            var parent_element = $(elemnt).parent();
            if ($(elemnt).parents(self.options.otherPageContainer).length) {
                parent_element = $(elemnt).parents(self.options.otherPageContainer);
            }
            parent_element.find(self.options.addToCartButtonText).text(self.options.buttonText);
            parent_element.find(self.options.addToCartButtonSelector).attr('title', self.options.buttonText);
            parent_element.find('form').prepend(self.options.preOrderInput);
            parent_element.find(self.options.AddToCartContainer).css('margin', '5px 0 10px');
            var formElement = parent_element.parent().find(self.options.AddToCartContainer).first();
            $(elemnt).find('.mess-preorder').detach().insertBefore(formElement);
        }
    });

    return $.bss.preorder_product;
});
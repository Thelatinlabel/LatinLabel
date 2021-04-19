/**
 * Copyright 2020 aheadWorks. All rights reserved.\nSee LICENSE.txt for license details.
 */

define([
    'jquery'
], function ($) {
    "use strict";

    return {

        textFieldSelector: '[data-role="coupon-code-text"]',

        /**
         * Copy text to clipboard
         */
        copy: function () {
            $(this.textFieldSelector).select();
            document.execCommand('Copy');
        }
    };
});
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define(
    [
        'ko',
        'jquery',
        'uiComponent'
    ],
    function (ko, $, Component) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'MGS_InstantSearch/search/category',
                result: {
                    category: {
                        data: ko.observableArray([]),
                        size: ko.observable(0),
                        url: ko.observable('')
                    }
                },
                isVisible: false
            },
            initialize: function () {
                var self = this;
                this._super();
                self.result.category = window.instantSearch.category;
                this.isVisible = ko.computed(function () {
                    if(self.result.category) {
                        var sum = self.result.category.size();
                    } else {
                        var sum = 0;
                    }
                    
                    if (sum > 0) {
                        return true; }
                    return false;
                }, this);
            }
        });
    }
);

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
                template: 'MGS_InstantSearch/search/blog',
                result: {
                    blog: {
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
                self.result.blog = window.instantSearch.blog;
                this.isVisible = ko.computed(function () {
                    if(self.result.blog) {
                        var sum = self.result.blog.size();
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

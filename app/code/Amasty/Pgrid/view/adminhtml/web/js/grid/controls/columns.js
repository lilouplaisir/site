/**
 * Pgrid Columns Component
 */
define([
    'Magento_Ui/js/grid/controls/columns',
    'uiLayout',
    'uiRegistry',
    'ko'
], function (Columns, layout, registry, ko) {
    'use strict';

    return Columns.extend({
        defaults: {
            selectedTab: 'tab1',
            template: 'Amasty_Pgrid/ui/grid/controls/columns',
            rowTmpl: 'Amasty_Pgrid/ui/grid/controls/row',
            sectionTmpl: 'Amasty_Pgrid/ui/grid/controls/section',
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },
            modules: {
                client: '${ $.clientConfig.name }',
                source: '${ $.provider }',
                editorCell: '${ $.editorCellConfig.provider }',
                listingFilter: '${ $.listingFilterConfig.provider }'
            }
        },

        initialize: function () {
            this._super();

            layout([ this.clientConfig ]);

            return this;
        },

        initObservable: function () {
            this._super()
                .track([ 'selectedTab' ]);

            return this;
        },

        initElement: function (el) {
            el.track(['label', 'ampgrid_editable', 'ampgrid_filterable', 'ampgrid_title', 'ampgrid_marker']);
            el.headerTmpl = 'Amasty_Pgrid/ui/grid/columns/text';
        },

        hasSelected: function (tabKey) {
            return this.selectedTab === tabKey;
        },

        /**
         * Split data into three columns
         * @returns {[]}
         */
        prepareColumnsData: function () {
            var self = this,
                columns = [],
                index;

            this.elems.each(function (elem) {
                index = self.getElemIndex(elem);

                if (typeof columns[index] === 'undefined') {
                    columns[index] = [];
                }

                columns[index].push(elem);
            });

            return columns;
        },

        getElemIndex: function (elem) {
            if (this.isDefaultColumn(elem)) {
                return 0;
            }

            if (this.isExtraColumn(elem)) {
                return 1;
            }

            if (this.isAttributeColumn(elem)) {
                return 2;
            }

            return 0;
        },

        isDefaultColumn: function (elem) {
            return elem.ampgrid && !elem.amastyExtra && !elem.amastyAttribute;
        },

        isAttributeColumn: function (elem) {
            return elem.ampgrid && elem.amastyAttribute;
        },

        isExtraColumn: function (elem) {
            return elem.ampgrid && elem.amastyExtra;
        },

        close: function () {
            return this;
        },

        /**
         * Controls current operations with grid columns
         */
        prepareColumns: function (index) {
            var columns = this,
                current,
                parentComponent,
                filter;

            columns.editorCell().model.columns('showLoader');
            this.elems.each(function (elem, currentIndex) {
                current = columns.storage().get('current.columns.' + elem.index);
                elem.label = elem.ampgrid.title;

                if (ko.isObservable(elem.ampgrid_editable)) {
                    elem.ampgrid_editable(elem.ampgrid.editable);
                }

                if (ko.isObservable(elem.ampgrid_marker)) {
                    elem.ampgrid_marker(elem.ampgrid.marker);
                }

                if (current) {
                    current.visible = elem.visible;
                    current.ampgrid_title = elem.ampgrid.title;
                    current.ampgrid_editable = elem.ampgrid.editable;
                    current.ampgrid_filterable = elem.ampgrid.filterable;
                    current.ampgrid_marker = elem.ampgrid.marker;
                }

                columns.editorCell().initColumn(elem.index);

                filter = columns.listingFilter().elems.findWhere({
                    index: elem.index
                });

                if (!filter && elem.ampgrid.filterable) {
                    elem.filter = elem.default_filter;
                    columns.listingFilter().addFilter(elem);
                }

                if (filter && !elem.ampgrid.filterable) {
                    filter.visible(false);
                } else if (filter && elem.visible && elem.ampgrid.filterable) {
                    filter.visible(true);
                }

                if (elem.index === index) {
                    parentComponent = elem.requestModule(elem.parentName);
                    parentComponent().unshiftElement(currentIndex);
                }
            });
        },

        reloadGridData: function (data) {
            var currentData;

            if (data.visible === false) {
                return this;
            }

            this.prepareColumns(data.index);

            currentData = this.source().get('params');
            currentData.data = JSON.stringify({ 'column': data.index });

            this.client()
                .save(currentData)
                .done(this.amastyReload);

            return this;
        },

        saveBookmark: function () {
            this.prepareColumns();
            this.storage().saveState();
            this.editorCell().model.columns('hideLoader');
        },

        amastyReload: function () {
            registry.get('index = product_listing').source.reload();
        }
    });
});

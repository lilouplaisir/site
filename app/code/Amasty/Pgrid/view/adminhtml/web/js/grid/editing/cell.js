/**
 * Pgrid Cell Component
 */
define([
    'ko',
    'Magento_Ui/js/lib/view/utils/async',
    'underscore',
    'uiRegistry',
    'uiClass',
    'Amasty_Pgrid/js/action/notification'
], function (ko, $, _, registry, Class, notification) {
    'use strict';

    return Class.extend({
        defaults: {
            rootSelector: '${ $.columnsProvider }:.admin__data-grid-wrap',
            tableSelector: '${ $.rootSelector } -> table',
            cellSelector: '${ $.tableSelector } tbody tr.data-row div.data-grid-cell-content',
            rowTemplate: 'Amasty_Pgrid/grid/cells/row',
            rowTmpl:
                    '<!-- ko with: _editor -->' +
                        '<!-- ko if: isEditable($row().entity_id, $col.index) -->' +
                            '<!-- ko with: getField($row().entity_id, $col.index) -->' +
                                '<!-- ko template: $parent.fieldTmpl --><!-- /ko -->' +
                            '<!-- /ko -->' +
                        '<!-- /ko -->' +
                   '<!-- /ko -->',
            headerButtonsTmpl:
                '<!-- ko template: headerButtonsTmpl --><!-- /ko -->',
            selectors: {
                panel: '[data-ampgrid-js="panel"]',
                gridWrapper: '[data-role="grid-wrapper"]',
                editAction: '.action-menu-item',
                generalDropdownAction: '.admin-user .admin__action-dropdown-menu a',
                newProductAction: '#add_new_product-button',
                newProductOptions: '#add_new_product .dropdown-menu',
                dropDownMenu: '.dropdown-menu',
                itemDefault: '.item-default'
            }
        },

        initialize: function () {
            _.bindAll(
                this,
                'initRoot',
                'initCell',
                'cellBindings'
            );

            this._super();

            this.model = registry.get(this.model);

            $.async(this.rootSelector, this.initRoot);
            $.async(this.cellSelector, this.initCell);

            this.addNotificationsEvents();

            return this;
        },

        addNotificationsEvents: function () {
            var newProductOptions = document.querySelector(this.selectors.newProductOptions);

            $(this.selectors.gridWrapper).on('click', this.selectors.editAction, this.showNotification.bind(this, false));
            $(this.selectors.newProductAction).on('click', this.showNotification.bind(this, false));
            $(this.selectors.generalDropdownAction).on('click', this.showNotification.bind(this, false));
            newProductOptions.addEventListener('click', this.showNotification.bind(this, true), true);
        },

        showNotification: function (isSetLocation, event) {
            var deferred,
                href,
                $element = $(event.currentTarget);

            if (window.isPgridEditable && $element.attr('target') !== '_blank') {
                event.preventDefault();
                event.stopPropagation();

                window.isNotificationEnabled = true;
                deferred = $.Deferred();
                notification(deferred).showNotification();

                $.when(deferred).done(function () {
                    window.isNotificationEnabled = false;
                    window.isPgridEditable = false;

                    if (isSetLocation) {
                        event.target.onclick();

                        return;
                    }

                    href = $(event.currentTarget).attr('href');

                    if (!href) {
                        $(this.selectors.dropDownMenu).find(this.selectors.itemDefault).trigger('click');

                        return;
                    }

                    window.location.href = $(event.currentTarget).attr('href');
                }.bind(this));
            }
        },

        initRoot: function (node) {
            $(this.headerButtonsTmpl)
                .insertBefore(node)
                .applyBindings(this.model);

            this.initScroll();

            return this;
        },

        initScroll: function () {
            $(window).on('scroll', _.throttle(this.toggleFixedPgrid, 200).bind(this));
        },

        toggleFixedPgrid: function (event) {
            var panel = $(this.selectors.panel),
                offsetTop,
                isActive;

            if (panel.length) {
                offsetTop = panel.offset().top - panel.height();
                isActive = $(event.currentTarget).scrollTop() > offsetTop;
                panel.toggleClass('-fixed', isActive);
            }
        },

        cellBindings: function (ctx) {
            var model = this.model,
                visible = false,
                productId,
                colIndex,
                field;

            return {
                visible: ko.computed(function () {
                    visible = false;

                    if (model.rowsData[ctx.$index]) {
                        productId = model.rowsData[ctx.$index].entity_id;
                        colIndex = ctx.$col.index;
                        field = model.getField(productId, colIndex);

                        visible = !field || field.visible() === false;
                    }

                    return visible;
                })
            };
        },

        initCells: function () {
            var cell = this;

            $.async(this.cellSelector, function (node) {
                cell.initCell(node);
            });
        },

        initColumn: function (colIndex) {
            var cell = this;

            $.async(this.cellSelector, function (node) {
                if (ko.contextFor(node).$col.index == colIndex) {
                    cell.initCell(node);
                }
            });
        },

        initCell: function (node) {
            var koNode = ko.contextFor(node),
                $editingCell;

            if (this.model.isEditableColumn(koNode.$col.index) &&
                koNode.hasAmpgridEditor !== true) {
                $(node).extendCtx({ _editor: this.model }).bindings(this.cellBindings);

                $editingCell = $(this.rowTmpl)
                    .insertBefore(node)
                    .applyBindings(node);

                ko.utils.domNodeDisposal.addDisposeCallback(node, this.removeEditingCell.bind(this, $editingCell));

                koNode.hasAmpgridEditor = true;
            }

            return this;
        },

        removeEditingCell: function (cell) {
            _.toArray(cell).forEach(ko.removeNode);
        }
    });
});

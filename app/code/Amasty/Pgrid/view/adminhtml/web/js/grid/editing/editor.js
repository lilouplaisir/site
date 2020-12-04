/**
 * Pgrid Editor Component
 */
define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'mage/translate',
    'uiCollection',
    'Amasty_Pgrid/js/action/save',
    'Amasty_Pgrid/js/action/messages'
], function (_, utils, layout, $t, Collection, saveAction, amMessage) {
    'use strict';

    return Collection.extend({
        defaults: {
            isMultiEditing: true,
            isMultiEditingActive: false,
            fieldTmpl: 'Amasty_Pgrid/ui/grid/editing/field',
            headerButtonsTmpl: 'Amasty_Pgrid/ui/grid/editing/header-buttons',
            successMsg: $t('You have successfully saved your edits.'),
            saveData: {},
            columnNames: ['price', 'cost', 'special_price'],
            templates: {
                fields: {
                    base: {
                        parent: '${ $.$data.editor.name }',
                        name: '${ $.$data.productId }_${ $.$data.column.index }',
                        provider: '${ $.parent }',
                        dataScope: 'rowsData.${ $.$data.rowIndex }.${ $.$data.column.index }',
                        isEditor: true,
                        reloadOnFocused: true,
                        modules: {
                            parentObject: '${ $.parent }'
                        }
                    },
                    text: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'Amasty_Pgrid/ui/form/element/input'
                    },
                    number: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'Amasty_Pgrid/ui/form/element/inputNumber'
                    },
                    price: {
                        component: 'Magento_Ui/js/form/element/abstract',
                        template: 'Amasty_Pgrid/ui/form/element/input',
                        dataScope: 'rowsData.${ $.$data.rowIndex }.amasty_${ $.$data.column.index }'
                    },
                    date: {
                        component: 'Magento_Ui/js/form/element/date',
                        template: 'ui/form/element/date',
                        dateFormat: 'MMM d, y h:mm:ss a',
                        reloadOnFocused: false,
                        reloadOnUpdate: true
                    },
                    select: {
                        component: 'Magento_Ui/js/form/element/select',
                        template: 'Amasty_Pgrid/ui/form/element/select',
                        options: '${ JSON.stringify($.$data.column.options) }',
                        reloadOnUpdate: true
                    },
                    multiselect: {
                        component: 'Magento_Ui/js/form/element/multiselect',
                        options: '${ JSON.stringify($.$data.column.options) }',
                        template: 'Amasty_Pgrid/ui/form/element/multiselect'
                    },
                    textarea: {
                        component: 'Magento_Ui/js/form/element/textarea',
                        template: 'Amasty_Pgrid/ui/form/element/textarea'
                    }
                }
            },
            cellConfig: {
                component: 'Amasty_Pgrid/js/grid/editing/cell',
                name: '${ $.name }_cell',
                model: '${ $.name }',
                columnsProvider: '${ $.columnsProvider }'
            },
            clientConfig: {
                component: 'Magento_Ui/js/grid/editing/client',
                name: '${ $.name }_client'
            },

            imports: {
                rowsData: '${ $.dataProvider }:data.items',
                filters: '${ $.dataProvider }:params.filters'
            },
            listens: {
                saveData: 'updateSaveState',
                elems: 'updateSaveState',
                '${ $.dataProvider }:params.paging.pageSize': 'onPagingSizeChanged'
            },
            modules: {
                columns: '${ $.columnsProvider }',
                client: '${ $.clientConfig.name }',
                source: '${ $.dataProvider }',
                cell: '${ $.cellConfig.name }'
            }
        },

        initialize: function () {
            _.bindAll(
                this,
                'onDataSaved',
                'onSaveError',
                'clearSaveData'
            );

            this._super();

            this.amMessage = amMessage(this);

            layout([ this.clientConfig ]);
            layout([ this.cellConfig ]);

            this.source().on('reloaded', this.clearSaveData);

            return this;
        },

        initObservable: function () {
            this._super()
                .track([
                    'saveData',
                    'rowsData'
                ])
                .observe({
                    canSave: false,
                    hasActive: false,
                    messages: []
                });

            return this;
        },

        onPagingSizeChanged: function () {
            if (this.cell()) {
                this.cell().initCells();
            }
        },

        onInputKeyUp: function (component, event) {
            var value = this.value();

            if ((this.editorType === 'textarea' && event.ctrlKey && event.which === 13) ||
                (this.editorType !== 'textarea' && event.which === 13)
            ) {
                this.focused(false);
            }

            if (this.editorType === 'number') {
                this.value(value.toString().replace(/[^0-9\\+-.]/gm, ''));
            }

            window.isPgridEditable = true;

            return true;
        },

        onFieldUpdated: function (hasChanged) {
            var saveData = this.parentObject().saveData;

            if (_.has(this.parentObject().saveData, this.fieldId)) {
                delete saveData[this.fieldId];
            }

            if (hasChanged) {
                saveData[this.fieldId] = {
                    'entityId': this.productId,
                    'value': typeof this.value() != 'object' || this.value().length > 0 ? this.value() : null,
                    'colIndex': this.colIndex,
                    'rowIndex': this.rowIndex
                };
            }

            this.parentObject().saveData = saveData;

            if (!this.parentObject().isMultiEditing && hasChanged) {
                this.parentObject().save();
            }
        },

        updateSaveState: function () {
            var editor = this,
                hasActive = false;

            _.each(editor.elems(), function (elem) {
                if (elem.visible() && !hasActive && editor.isMultiEditing) {
                    hasActive = true;
                }
            });

            this.hasActive(hasActive);

            this.canSave(hasActive || _.keys(this.saveData).length > 0);
        },

        startEdit: function (rowIndex, colIndex) {
            return this.edit(rowIndex, colIndex);
        },

        getId: function (rowIndex, colIndex) {
            return rowIndex + '_' + colIndex;
        },

        edit: function (rowIndex, colIndex) {
            var field,
                colData,
                regExp = /[^\d\.\,\s]+/gm;

            if (this.isEditableColumn(colIndex)) {
                field = this.getField(this.rowsData[rowIndex].entity_id, colIndex);

                colData = this.rowsData[rowIndex][colIndex];

                if (_.contains(this.columnNames, colIndex) && colData) {
                    this.rowsData[rowIndex][colIndex] = colData.replace(regExp, '');
                }

                if (!field) {
                    this.initField(rowIndex, colIndex);
                } else {
                    field.visible(true);
                    field.value(this.rowsData[rowIndex][colIndex]);
                }
            }

            return this;
        },

        initField: function (rowIndex, colIndex) {
            var field = this.buildField(rowIndex, colIndex);

            layout([ field ]);

            return this;
        },

        buildField: function (rowIndex, colIndex) {
            var fields = this.templates.fields,
                column = this.columns().getChild(colIndex),
                field = column.editor,
                rowData = this.rowsData[rowIndex],
                newField;

            if (_.isObject(field) && field.editorType) {
                field = utils.extend({}, fields[field.editorType], field);
            } else if (_.isString(field)) {
                field = fields[field];
            }

            field = utils.extend({}, fields.base, field);

            newField = utils.template(field, {
                editor: this,
                column: column,
                productId: rowData.entity_id,
                rowIndex: rowIndex
            }, true, true);

            newField.fieldId = this.getId(rowData.entity_id, colIndex);
            newField.productId = rowData.entity_id;
            newField.colIndex = colIndex;
            newField.onKeyUp = this.onInputKeyUp;

            return newField;
        },

        initElement: function (field) {
            var editor = this;

            if (field.reloadOnUpdate) {
                field.on('update', this.onFieldUpdated.bind(field));
            }

            if (field.reloadOnFocused) {
                field.on('focused', function (focused) {
                    if (!focused) {
                        editor.onFieldUpdated.call(field, true);
                    }
                });
            }

            field.focused(true);
        },

        getField: function (productId, colIndex) {
            return this.elems.findWhere({
                fieldId: this.getId(productId, colIndex)
            });
        },

        isEditableColumn: function (colIndex) {
            var column = this.columns().getChild(colIndex);

            return column.ampgrid && column.ampgrid.editable;
        },

        isEditable: function (productId, colIndex) {
            var elem = this.getField(productId, colIndex),
                visible = !elem || elem.visible() !== false;

            return this.columns().getChild(colIndex) &&
                this.columns().getChild(colIndex).editor && visible;
        },

        /**
         * Handles successful save request.
         */
        onDataSaved: function (deferred, data) {
            var msg = {
                type: 'success',
                message: this.successMsg
            };

            if (data.ajaxExpired) {
                document.location.href = data.ajaxRedirect;

                return;
            }

            this.amMessage.addMessage(msg);
            this.source().onReload(data.grid);
            this.client().busy = false;
            this.hasActive(false);
            window.isPgridEditable = false;

            if (deferred) {
                deferred.resolve();
            }
        },

        clearSaveData: function () {
            var editor = this;

            _.each(editor.elems(), function (elem) {
                elem.visible(false);
            });

            this.saveData = {};

            return this;
        },

        /**
         * Handles failed save request.
         *
         * @param {(Array|Object)} errors - List of errors or a single error object.
         */
        onSaveError: function (errors) {
            this.amMessage.addMessage(errors)
                .columns('hideLoader');
            this.client().busy = false;
        },

        hasMessages: function () {
            return this.messages().length;
        },

        save: function () {
            return saveAction(this);
        },

        cancel: function () {
            this.clearSaveData();
            this.amMessage.clearMessages();

            return this;
        }
    });
});

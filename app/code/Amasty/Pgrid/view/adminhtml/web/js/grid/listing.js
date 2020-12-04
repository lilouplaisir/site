/**
 * Pgrid Listing Component
 */
define([
    'Magento_Ui/js/grid/listing',
    'uiLayout'
], function (Listing, layout) {
    'use strict';

    return Listing.extend({
        defaults: {
            amastyEditorConfig: {
                name: '${ $.name }_amasty_editor',
                component: 'Amasty_Pgrid/js/grid/editing/editor',
                columnsProvider: '${ $.name }',
                dataProvider: '${ $.provider }',
                enabled: false
            }
        },

        initialize: function () {
            this._super()
                .initAmastyEditor();

            return this;
        },

        initAmastyEditor: function () {
            if (this.amastyEditorConfig.enabled) {
                layout([ this.amastyEditorConfig ]);
            }
        },

        unshiftElement: function (index) {
            var elems = this.elems(),
                firstElem = elems.shift(),
                item = elems.splice(index, 1)[0];

            elems.unshift(item);
            elems.unshift(firstElem);

            this.elems(elems);
        }
    });
});

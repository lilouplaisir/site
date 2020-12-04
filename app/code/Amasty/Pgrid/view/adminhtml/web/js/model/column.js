define([
    'underscore'
], function (_) {
    'use strict';

    return {
        initObservable: function () {
            this._super().observe([
                'ampgrid_marker',
                'ampgrid_editable'
            ]);

            return this;
        },

        initFieldClass: function () {
            _.extend(this.fieldClass, {
                _dragging: this.dragging,
                'ampgrid-marker': this.ampgrid_marker
            });

            return this;
        }
    };
});

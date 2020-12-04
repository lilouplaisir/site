define([
    'underscore',
    'Amasty_Pgrid/js/action/messages'
], function (_, amMessage) {
    'use strict';

    return function (editor, deferred) {
        var valid = true,
            message = amMessage(editor),
            newValue,
            data = editor.source().get('params');

        data.amastyItems = {};
        data.store_id = editor.filters.store_id;

        _.each(_.values(editor.saveData), function (item) {
            if (!valid && editor.getField(item.entityId, item.colIndex).validate().valid) {
                valid = false;

                return;
            }

            newValue = undefined === item.value ? '' : item.value;

            if (!_.has(data.amastyItems, item.entityId)) {
                data.amastyItems[item.entityId] = {};
            }

            data.amastyItems[item.entityId][item.colIndex] = newValue;
        });

        if (valid && editor.client().busy !== true) {
            editor.client().busy = true;

            editor.columns('showLoader');

            message.clearMessages();

            editor.client()
                .save(data)
                .done(editor.onDataSaved.bind(editor, deferred))
                .fail(editor.onSaveError);
        }
    };
});

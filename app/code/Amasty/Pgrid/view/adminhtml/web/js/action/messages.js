define([], function () {
    'use strict';

    return function (self) {
        return {
            addMessage: function (message) {
                const delay = 5000;

                var messages = self.messages();

                Array.isArray(message) ?
                    messages.push.apply(messages, message) :
                    messages.push(message);

                self.messages(messages);
                setTimeout(this.clearMessages, delay);

                return self;
            },

            clearMessages: function () {
                self.messages.removeAll();

                return self;
            }
        };
    };
});

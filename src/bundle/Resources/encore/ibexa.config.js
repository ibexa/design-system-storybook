const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ibexa-design-system-storybook-css', [
        path.resolve(__dirname, '../public/scss/storybook.scss'),
    ]);
};

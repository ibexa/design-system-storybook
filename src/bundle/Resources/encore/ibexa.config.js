const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ibexa-design-system-storybook-js', [
        path.resolve('./public/bundles/ibexadesignsystemtwig/ts/init_components.ts'),
    ]);
    Encore.addEntry('ibexa-design-system-storybook-css', [path.resolve(__dirname, '../public/scss/storybook.scss')]);
};

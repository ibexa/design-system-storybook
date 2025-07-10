const path = require('path');

module.exports = (Encore) => {
    Encore.addEntry('ibexa-design-system-storybook-js', [
        path.resolve('./vendor/ibexa/design-system-twig/src/bundle/Resources/public/ts/init_components.ts')]
    );
    Encore.addEntry('ibexa-design-system-storybook-css', [path.resolve(__dirname, '../public/scss/storybook.scss')]);
};

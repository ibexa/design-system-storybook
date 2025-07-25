import config from '@ibexa/eslint-config/prettier';

export default {
    ...config,
    overrides: [
        {
            files: ['*.twig'],
            options: {
                parser: 'twig',
                plugins: ['@zackad/prettier-plugin-twig'],
                twigAlwaysBreakObjects: false,
                twigOutputEndblockName: true,
            },
        },
    ],
};

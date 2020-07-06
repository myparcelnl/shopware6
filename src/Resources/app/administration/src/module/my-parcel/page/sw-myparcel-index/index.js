import template from './sw-myparcel-index.html.twig';

import { Component } from 'src/core/shopware';

Component.register('sw-myparcel-index', {
    template: template,

    data() {
        return {
            isLoading: false,
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle()
        };
    },
});
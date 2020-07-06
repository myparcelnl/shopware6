import template from './sw-myparcel-shipping-methods.html.twig';

import { Component } from 'src/core/shopware';

Component.register('sw-myparcel-shipping-methods', {
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
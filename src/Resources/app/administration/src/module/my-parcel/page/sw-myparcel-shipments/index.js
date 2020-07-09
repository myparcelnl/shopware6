import template from './sw-myparcel-shipments.html.twig';
import { Component } from 'src/core/shopware';

Component.register('sw-myparcel-shipments', {
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
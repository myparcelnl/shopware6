import template from './sw-order-list.html.twig';
const { Component } = Shopware;

// Uitleg via Bjorn https://gist.github.com/bheijink/b8ff3b98818a2cc6db995ecfb2bb06ea

Component.override('sw-order-list', {
    template,

    methods: {
        getOrderColumns() {
            var thisOrderColumns = this.$super('getOrderColumns');
            thisOrderColumns.splice(1, 0, {
                property: 'afbeelding',
                label: 'Afbeelding',
                routerLink: 'sw.order.detail',
                allowResize: true,
                isImage: true,
                primary: true
            }, {
                property: 'download',
                label: 'Download',
                routerLink: 'sw.order.detail',
                allowResize: true,
                primary: true
            }, {
                property: 'quantitycount',
                label: 'Totaal aantal',
                routerLink: 'sw.order.detail',
                allowResize: true,
                primary: true
            });
            return thisOrderColumns;
        },
        // getOrderActions() {
        //     var thisOrderActions = this.$super('getOrderActions');
        //     thisOrderActions.splice(1, 0, {
        //         property: 'quantitycount',
        //         label: 'Totaal aantal',
        //         routerLink: 'sw.order.detail',
        //         allowResize: true,
        //         primary: true
        //     } );
        //     return thisOrderActions;
        // }
    }
});
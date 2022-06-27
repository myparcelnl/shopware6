import './page/sw-myparcel-orders';
import './page/sw-myparcel-consignments';

import nlNL from './snippet/nl-NL.json';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const {Module} = Shopware;

Module.register('sw-myparcel', {
    type: 'plugin',
    name: 'MyParcel',
    title: 'sw-myparcel.general.mainMenuItemGeneral',
    description: 'sw-myparcel.general.descriptionTextModule',
    version: '1.0.0',
    targetVersion: '1.0.0',
    color: '#0f5c47',
    icon: 'default-action-settings',

    snippets: {
        'nl-NL': nlNL,
        'de-DE': deDE,
        'en-GB': enGB
    },

    routes: {
        orders: {
            component: 'sw-myparcel-orders',
            path: 'orders'
        },
        consignments: {
            component: 'sw-myparcel-consignments',
            path: 'consignments/:orderId?',
            meta: {
                parentPath: 'sw.myparcel.orders'
            }
        },
    },

    navigation: [
        {
            id: 'sw-myparcel-orders',
            label: 'sw-myparcel.general.ordersMenuItemGeneral',
            color: '#0f5c47',
            path: 'sw.myparcel.orders',
            parent: 'sw-order'
        },
        {
            id: 'sw-myparcel-consignments',
            label: 'sw-myparcel.general.consignmentsMenuItemGeneral',
            color: '#0f5c47',
            path: 'sw.myparcel.consignments',
            parent: 'sw-order'
        },

    ]
});

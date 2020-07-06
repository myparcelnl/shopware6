import './page/sw-myparcel-index';
import './page/sw-myparcel-shipments';
import './page/sw-myparcel-shipping-methods';
import './extension/sw-settings-index';

import nlNL from './snippet/nl-NL.json';
import deDE from './snippet/de-DE.json';
import enGB from './snippet/en-GB.json';

const { Module } = Shopware;

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
        index: {
            component: 'sw-myparcel-index',
            path: 'index'
        },
        shipments: {
            component: 'sw-myparcel-shipments',
            path: 'shipments',
            meta: {
                parentPath: 'sw.myparcel.index'
            }
        },
        shippingMethods: {
            component: 'sw-myparcel-shipping-methods',
            path: 'shipping/methods',
            meta: {
                parentPath: 'sw.myparcel.index'
            }
        }
    },

    navigation: [
        {
            id: 'sw-myparcel',
            label: 'sw-myparcel.general.mainMenuItemGeneral',
            color: '#0f5c47',
            path: 'sw.myparcel.index',
            icon: 'default-shopping-paper-bag-product',
            position: 100
        },
        {
            id: 'sw-myparcel-shipments',
            path: 'sw.myparcel.shipments',
            label: 'sw-myparcel.general.shipmentsMenuItemGeneral',
            color: '#0f5c47',
            position: 100,
            parent: 'sw-myparcel'
        },
        {
            id: 'sw-myparcel-shipping-methods',
            path: 'sw.myparcel.shippingMethods',
            label: 'sw-myparcel.general.shippingMethodsMenuItemGeneral',
            color: '#0f5c47',
            position: 100,
            parent: 'sw-myparcel'
        }
    ]
});
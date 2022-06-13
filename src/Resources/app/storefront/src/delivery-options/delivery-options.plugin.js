import Plugin from "src/plugin-system/plugin.class";
import HttpClient from 'src/service/http-client.service';
import ElementReplaceHelper from 'src/helper/element-replace.helper';

import '@myparcel'


export default class DeliveryOptionsPlugin extends Plugin {
    static options = {
        /**
         * Put options here
         */
        address: {
            cc: '',
            city: '',
            postalCode: '',
            number: ''
        },
        config: {},
        price:{}
    };


    init() {
        // Start the HTTP client
        this._client = new HttpClient();
        // Init npm package here
        this._configure();
        this._addListeners();

        //Set address
        window.MyParcelConfig.address = this.options.address;

        // Tell the plugin to re-render
        document.dispatchEvent(new Event('myparcel_update_delivery_options'));
    };

    _configure() {
        window.MyParcelConfig = {config: {}};
        window.MyParcelConfig.config = this.options.config;
    };

    _addListeners() {
        document.addEventListener('myparcel_updated_delivery_options', (event) => {
            const data = this._getRequestData();
            data['myparcel'] = JSON.stringify(event.detail);
            // console.log(event.detail);
            this._client.post(this.options.url, JSON.stringify(data), content => {
                // Retry on error?
                this._procesShippingCostsPage(JSON.parse(content));
            });
        });
    };

    _getRequestData() {
        const data = {};

        if (window.csrf.enabled && window.csrf.mode === 'twig') {
            data['_csrf_token'] = this.options.csrfToken;
        }

        return data;
    }

    _procesShippingCostsPage(html){

        ElementReplaceHelper.replaceFromMarkup(html.content,'.checkout-aside-summary-container');
    }
}

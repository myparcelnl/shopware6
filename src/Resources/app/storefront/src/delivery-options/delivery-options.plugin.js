import Plugin from "src/plugin-system/plugin.class";
import HttpClient from 'src/service/http-client.service';
import ElementReplaceHelper from 'src/helper/element-replace.helper';
import DomAccess from 'src/helper/dom-access.helper';

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
        translations:{},
        config: {}
    };

    /*
    The button will be disabled through the template
     */
    init() {
        // Start the HTTP client
        this._client = new HttpClient();
        // Init npm package here
        this._configure();
        this._addListeners();
        this._registerElements();

        //Set address
        window.MyParcelConfig.address = this.options.address;

        // Tell the plugin to re-render
        document.dispatchEvent(new Event('myparcel_update_delivery_options'));
    };

    _configure() {
        window.MyParcelConfig = {config: {}};
        window.MyParcelConfig.config = this.options.config;
        window.MyParcelConfig.strings = this.options.translations;
    };

    _addListeners() {
        document.addEventListener('myparcel_updated_delivery_options', (event) => {
            this._submitToCart();
        });
    };

    _registerElements() {
        //Alert block
        this.myparcelWarningAlert = DomAccess.querySelector(document, '#myparcel-alert');
    };

    _submitToCart() {
        const data = this._getRequestData();
        data['myparcel'] = JSON.stringify(event.detail);
        this._disableButton(true);
        this._client.post(this.options.url, JSON.stringify(data), (content, request) => {
            // Retry on error?
            if (request.status === 200) {
                this._showWarningAlert("");
                this._disableButton(false);
                this._procesShippingCostsPage(JSON.parse(content));
            }else{
                this._showWarningAlert(this.options.translations.refreshMessage);
            }
        });
    }

    _getRequestData() {
        const data = {};

        if (window.csrf.enabled && window.csrf.mode === 'twig') {
            data['_csrf_token'] = this.options.csrfToken;
        }
        return data;
    }

    _procesShippingCostsPage(html) {
        ElementReplaceHelper.replaceFromMarkup(html.content, '.checkout-aside-summary-container');
    }

    _disableButton(disable) {
        //Get the submit button
        const submitButton = DomAccess.querySelector(document, '#confirmFormSubmit');
        submitButton.disabled = disable;
    }

    _showWarningAlert(innerHTML) {
        if (innerHTML === "") {
            this.myparcelWarningAlert.setAttribute('hidden', 'hidden');
            this.myparcelWarningAlert.querySelector('.alert-content').innerHTML = innerHTML
        } else {
            this.myparcelWarningAlert.removeAttribute('hidden');
            this.myparcelWarningAlert.querySelector('.alert-content').innerHTML = innerHTML
        }
    }
}

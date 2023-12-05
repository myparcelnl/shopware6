import Plugin from "src/plugin-system/plugin.class";
import HttpClient from 'src/service/http-client.service';
import ElementReplaceHelper from 'src/helper/element-replace.helper';
import DomAccess from 'src/helper/dom-access.helper';

export default class DeliveryOptionsPlugin extends Plugin {
    static options = {
        /**
         * Put options here
         */
        address: {
            cc: '',
            city: '',
            postalCode: '',
            street: ''
        },
        translations: {},
        config: {}
    };

    /*
    The button will be disabled through the template
     */
    init() {
        //Register elements
        this._registerElements();

        //Add mutation listener
        this._addMutationListener();

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

    _disableButton(disable) {
        //Get the submit button
        const submitButton = DomAccess.querySelector(document, '#confirmFormSubmit');
        submitButton.disabled = disable;
    }

    _addMutationListener() {
        //Enable button to allow non NL-BE address
        const shippingMethod = DomAccess.querySelector(document, '.shipping-methods');
        // Options for the observer (which mutations to observe)
        const config = {attributes: true, childList: true, subtree: true, attributeOldValue: true};

        // Create an observer instance linked to the callback function
        const observer = new MutationObserver((mutationList, observer) => {
            // Use traditional 'for loops' for IE 11
            for (const mutation of mutationList) {
                //Check for added nodes because we are going to search for myparcel-delivery-options
                for (const addedNode of mutation.addedNodes) {
                    if ("classList" in addedNode) {
                        // If myparcel-delivery-options come by, it has been loaded
                        if (addedNode.classList.contains('myparcel-delivery-options')) {
                            //Check if NL or BE
                            if (this.options.address.cc!=='NL'&&this.options.address.cc!=='BE') {
                                //Choose a standard shipping method based on the sender country
                                let carrier = "";
                                if (this.options.config.platform==="myparcel"){
                                    carrier= "postnl";
                                }else{
                                    carrier = "bpost";
                                }

                                const tomorrow = new Date();
                                tomorrow.setUTCHours(0, 0, 0, 0);
                                tomorrow.setUTCDate(tomorrow.getUTCDate() + 1);

                                const data = {};
                                data['myparcel'] = JSON.stringify({
                                    "date": tomorrow.toISOString(),
                                    "carrier": carrier,
                                    "isPickup": false,
                                    "deliveryType": "standard"
                                });

                                this._submitMyparcelData(data);
                                //Disable the button if delivery options was added
                                const submitButton = DomAccess.querySelector(document, '#confirmFormSubmit');
                                submitButton.disabled = false;
                            }
                        }
                    }
                }
            }
        });

        // Start observing the target node for configured mutations
        observer.observe(shippingMethod, config);
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
        const data = {};
        data['myparcel'] = JSON.stringify(event.detail);
        this._disableButton(true);
        this._submitMyparcelData(data);
    }

    _submitMyparcelData(data) {
        this._client.post(this.options.url, JSON.stringify(data), (content, request) => {
            // Retry on error?
            if (request.status < 400) {
                this._showWarningAlert("");
                this._disableButton(false);
                this._procesShippingCostsPage(JSON.parse(content));
            } else {
                this._showWarningAlert(this.options.translations.refreshMessage);
            }
        });
    }

    _procesShippingCostsPage(html) {
        ElementReplaceHelper.replaceFromMarkup(html.content, '.checkout-aside-summary-container');
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

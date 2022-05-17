import Plugin from "src/plugin-system/plugin.class";
import HttpClient from 'src/service/http-client.service';
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
        config: {}
    };


    init() {
        // Start the HTTP client
        this._client = new HttpClient();
        // Init npm package here
        this._configure();
        this._addListeners();
        console.log(this.options);
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
            console.log(JSON.stringify(event.detail))
            const data = this._getRequestData();
            // data['pickupPointLocationCode'] = e.target.value;

            console.log(data, this.options);

            this._client.post(this.options.url, JSON.stringify(data), content => this._parseRequest(JSON.parse(content)));
        });
    };

    _parseRequest(data) {
        console.log(data);
    }

    _getRequestData() {
        const data = {};

        if (window.csrf.enabled && window.csrf.mode === 'twig') {
            data['_csrf_token'] = this.options.csrfToken;
        }

        return data;
    }
}

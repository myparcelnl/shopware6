import Plugin from "src/plugin-system/plugin.class";
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
        config: {
            allowRetry: true,
            carrierSettings: {
                postnl: {
                    allowShowDeliveryDate: true,
                    allowSignature: true,
                },
                dhl: {
                    allowDeliveryOptions: true,
                    allowShowDeliveryDate: true
                },
                instabox: {
                    allowDeliveryOptions: true,
                    allowShowDeliveryDate: true
                }
            },
        }
    };


    init() {
        // Init npm package here
        this._configure();
        this._addListeners();

        window.MyParcelConfig.address = this.options.address;
        // Tell the plugin to re-render
        document.dispatchEvent(new Event('myparcel_update_delivery_options'));
        console.log(window.MyParcelConfig);
    };

    _configure() {
        console.log(window.MyParcelConfig);
        window.MyParcelConfig = {config: {}};
        window.MyParcelConfig.config = this.options.config;
    };

    _addListeners() {
        document.addEventListener('myparcel_updated_delivery_options', (event) => {
            console.log(JSON.stringify(event.detail))
        });
    };
}

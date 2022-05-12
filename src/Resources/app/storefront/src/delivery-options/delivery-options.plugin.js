import Plugin from "src/plugin-system/plugin.class";
import '@myparcel'

export default class DeliveryOptionsPlugin extends Plugin {
    static options = {
        /**
         * Put options here
         */
    };


    init() {
        // Init npm package here
        console.log('Loaded DeliveryOptionsPlugin');
    }

}

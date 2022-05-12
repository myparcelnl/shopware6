console.log('Main.js');
import DeliveryOptionsPlugin from "./delivery-options/delivery-options.plugin";

const PluginManager = window.PluginManager;
PluginManager.register('DeliveryOptionsPlugin',DeliveryOptionsPlugin,'[data-delivery-options-plugin]');

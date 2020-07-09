import MyParcelShippingOptionsService
    from '../core/service/api/myparcel-shipping-options.service';

import MyParcelShipmentService
    from '../core/service/api/myparcel-shipment.service';

const { Application } = Shopware;

Application.addServiceProvider('MyParcelShippingOptionsService', (container) => {
    const initContainer = Application.getContainer('init');

    return new MyParcelShippingOptionsService(initContainer.httpClient, container.loginService);
});

Application.addServiceProvider('MyParcelShipmentService', (container) => {
    const initContainer = Application.getContainer('init');

    return new MyParcelShipmentService(initContainer.httpClient, container.loginService);
});
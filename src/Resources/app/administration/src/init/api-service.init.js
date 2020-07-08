import MyParcelShippingOptionService
    from '../core/service/api/myparcel-shipping-option.service';

const { Application } = Shopware;

Application.addServiceProvider('MyParcelShippingOptionService', (container) => {
    const initContainer = Application.getContainer('init');

    return new MyParcelShippingOptionService(initContainer.httpClient, container.loginService);
});
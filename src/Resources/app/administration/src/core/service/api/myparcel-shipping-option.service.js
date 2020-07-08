const ApiService = Shopware.Classes.ApiService;

class MyParcelShippingOptionService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myparcel') {
        super(httpClient, loginService, apiEndpoint);
    }

    getShippingOptions() {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .get(
                `_action/${this.getApiBasePath()}/shipping-options`,
                {
                    headers: headers
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default MyParcelShippingOptionService;
const ApiService = Shopware.Classes.ApiService;
const { Application } = Shopware;

class MyparcelDropOffService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myparcel') {
        super(httpClient, loginService, apiEndpoint);
    }

    getDropOffLocation(data = { apiKey: null}) {
        const headers = this.getBasicHeaders();
        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/profile/get-drop-off`,
                JSON.stringify(data),
                {
                    headers: headers
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            })
            .catch(error => Promise.reject(ApiService.handleResponse(error.response)))
    }
}

Application.addServiceProvider('myParcelDropOffService', (container) => {
    const initContainer = Application.getContainer('init');
    return new MyparcelDropOffService(initContainer.httpClient, container.loginService);
});

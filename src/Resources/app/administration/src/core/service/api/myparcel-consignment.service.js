const ApiService = Shopware.Classes.ApiService;

class MyParcelConsignmentService extends ApiService {
    constructor(httpClient, loginService, apiEndpoint = 'myparcel') {
        super(httpClient, loginService, apiEndpoint);
    }

    createConsignments(data = { order_ids: null, label_positions: null, package_type: null, shipment_id: null }) {
        const headers = this.getBasicHeaders();

        return this.httpClient
            .post(
                `_action/${this.getApiBasePath()}/myparcel/consignment/create-consignments`,
                JSON.stringify(data),
                {
                    headers: headers
                }
            )
            .then((response) => {
                return ApiService.handleResponse(response);
            });
    }
}

export default MyParcelConsignmentService;
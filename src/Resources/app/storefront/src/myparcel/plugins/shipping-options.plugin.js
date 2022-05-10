import HttpClient from 'src/service/http-client.service';
import Plugin from 'src/plugin-system/plugin.class';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';
import DomAccess from 'src/helper/dom-access.helper';

export default class MyParcelShippingOptions extends Plugin {

    static options = {
        cookieName: 'myparcel-cookie-key',
        shippingForm: '.myparcel_shipping_form',
        confirmOrderForm: 'form#confirmOrderForm',
        currentShipping: 'p.confirm-shipping-current'
    };

    // get shipping form
    init() {
        const me = this;

        const methods = document.querySelectorAll('[name="shippingMethodId"]');

        if (methods.length <= 0) {
            return;
        }

        me.loadMethodOptions(me);

        methods.forEach(function (method,) {
            method.addEventListener('change', (event) => {
                me.loadMethodOptions(me);
            });
        });
    }

    loadMethodOptions(me) {
        const httpClient = new HttpClient(window.accessKey, window.contextToken);
        const methodOptionsUrl = window.router['myparcel.delivery_options'];
        const selectedMethod = document.querySelector("input[name=shippingMethodId]:checked");
        const selectedMethodValue = selectedMethod.value;
        const optionsDiv = document.querySelector('[data-shipping-method-id="' + selectedMethodValue + '"]');


        if (!optionsDiv) {
            return;
        }

        if (optionsDiv.getAttribute('data-options-loaded') == 'true') {
            let deliveryAddress = document.querySelector('[data-shipping-method-id="' + selectedMethodValue + '"] .myparcel_delivery_options');
            deliveryAddress.remove();
        }
        httpClient.get(methodOptionsUrl + '?method=' + selectedMethodValue, function (response) {
            if (!response){
                return;
            }
            //check if we have json as response, if so, then it's an error
            if (me.checkIfJsonString(response)) {
                const response = JSON.parse(response);

                if (response.success == false && response.code == '422') {
                    console.log('error 422');
                    return;
                }

                if (response.success == false) {
                    //TODO error about unsuccesfull retrievement of shipping options
                    console.log('no options retrieved');
                    return;
                }
                return
            }

            //we have html, let's append it to the correct method
            optionsDiv.innerHTML = response;
            optionsDiv.setAttribute('data-options-loaded', 'true');

            //Hidden fields for form
            const confirmOrderForm = document.querySelector(me.options.confirmOrderForm);

            const confirmDeliveryDate = confirmOrderForm.querySelector('input[name="myparcel[delivery_date]"]');
            const confirmDeliveryType = confirmOrderForm.querySelector('input[name="myparcel[delivery_type]"]');
            const confirmSignature = confirmOrderForm.querySelector('input[name="myparcel[requires_signature]"]');
            const confirmOnlyRecipient = confirmOrderForm.querySelector('input[name="myparcel[only_recipient]"]');
            const confirmDeliveryLocation = confirmOrderForm.querySelector('input[name="myparcel[delivery_location]"]');
            const confirmPickupLocation = confirmOrderForm.querySelector('input[name="myparcel[pickup_point_data]"]');

            //Event listeners for type
            const deliveryTypeCheckboxes = optionsDiv.querySelectorAll('input[name^="myparcel_delivery_type_"]');
            deliveryTypeCheckboxes.forEach(value => {
                value.classList.add('shipping-method-input');
                value.addEventListener('change', evt => {
                    confirmDeliveryType.value = evt.target.value;
                });
            });

            //Event listener for Location
            const deliveryLocationCheckboxes = optionsDiv.querySelectorAll('input[name="delivery_location"]');
            deliveryLocationCheckboxes.forEach(deliveryLocationCheckbox => {
                deliveryLocationCheckbox.addEventListener('change', evt => {
                    confirmDeliveryLocation.value = evt.target.value;
                    if (evt.target.value === "pickup") {
                        //Get all delivery pickups
                        const pickupLocationCheckboxes = optionsDiv.querySelectorAll('input[name="pickup_point"]');
                        let checked = false;
                        pickupLocationCheckboxes.forEach((pickupLocationCheckbox) => {
                            checked = checked || pickupLocationCheckbox.checked;
                            pickupLocationCheckbox.addEventListener('change', event => {
                                confirmPickupLocation.value = event.target.getAttribute('data-pickuppoint_data');
                            });
                        });
                        //Default check if no options
                        if (!checked && pickupLocationCheckboxes[0]) {
                            pickupLocationCheckboxes[0].checked = true;
                            confirmPickupLocation.value = pickupLocationCheckboxes[0].getAttribute('data-pickuppoint_data');
                        }
                    }else{

                    }
                });
            });

            //Event listener for signature
            const signatureCheckbox = optionsDiv.querySelector('input[name="myparcel_requires_signature"]');
            if (signatureCheckbox) {

                signatureCheckbox.addEventListener('change', evt => {
                    confirmSignature.value = evt.target.checked;
                });
            }

            //Event listener for only recipient
            const onlyRecipientCheckbox = optionsDiv.querySelector('input[name="myparcel_only_recipient"]');
            if (onlyRecipientCheckbox) {
                onlyRecipientCheckbox.addEventListener('change', evt => {
                    confirmOnlyRecipient.value = evt.target.checked;
                });
            }

            //EventListener for the date select
            const dateSelect = optionsDiv.querySelector('.date_select select')
            dateSelect.addEventListener('change', (event) => {

                //Set the hidden field for submit
                confirmDeliveryDate.value = event.target.value

                const dateOptionsNotActive = optionsDiv.querySelectorAll('.delivery_options');
                dateOptionsNotActive.forEach((element) => {
                    element.classList.add("d-none");
                });

                const dateOptionsDiv = optionsDiv.querySelector('[data-date="' + event.target.value + '"]');
                dateOptionsDiv.classList.remove("d-none");

                const deliveryOptions = dateOptionsDiv.querySelectorAll('input[name^="myparcel_delivery_type_"]');

                function checkNeighbors(type) {
                    const onlyRecipientCheckbox = optionsDiv.querySelector('input[name="myparcel_only_recipient"]');
                    if (onlyRecipientCheckbox) {
                        if (type === "1" || type === "3") {
                            onlyRecipientCheckbox.checked = true;
                            confirmOnlyRecipient.value = true;
                            onlyRecipientCheckbox.disabled = "disabled";
                        } else {
                            onlyRecipientCheckbox.disabled = false;
                        }
                    }
                }

                deliveryOptions.forEach((element) => {
                    if (element.checked) {
                        confirmDeliveryType.value = element.value;
                        checkNeighbors(element.value);
                    }
                    element.addEventListener('change', (event) => {
                        checkNeighbors(event.target.value);
                    });
                });

            });

            const event = new Event('change');
            dateSelect.dispatchEvent(event);
        });
    }

    checkIfJsonString(str) {
        try {
            JSON.parse(str);
        } catch (e) {
            return false;
        }
        return true;
    }
}

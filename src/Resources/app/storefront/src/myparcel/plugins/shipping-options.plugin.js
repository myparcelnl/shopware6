import HttpClient from 'src/service/http-client.service';
import Plugin from 'src/plugin-system/plugin.class';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';

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
        console.log('init myparcel');
        me.loadMethodOptions(me);

        const methods = document.querySelectorAll('[name="shippingMethodId"]')
        methods.forEach(function(method, ) {
            method.addEventListener('change', (event) => {
                me.loadMethodOptions(me);
            });
        });

        const shippingForms = document.querySelectorAll(me.options.shippingForm);

        /* Get cookie value and set some vars  */
        const cookieMyParcel = CookieStorage.getItem(me.options.cookieName);
        if(!cookieMyParcel) {
            return false;
        }

        const cookieSet = cookieMyParcel.split('_');
        const shippingMethodId  = cookieSet[0];
        const myparcel_delivery_type =  cookieSet[1];
        const myparcel_requires_signature =  cookieSet[2];
        const myparcel_only_recipient =  cookieSet[3];

        /* Is myParcel shippingform and is delivery type selected? */
        if (shippingForms && myparcel_delivery_type > 0) {
            /* Set delivery type */
            const shippingForm = document.querySelector('div[data-shipping-method-id="' + shippingMethodId + '"]');
            const deliveryOptionInputs = shippingForm.querySelector('input[name="myparcel_delivery_type"][value="' + myparcel_delivery_type + '"]');
            deliveryOptionInputs.checked = true;
            let shippingSelectedTxt = '';

            /* Set signature checkbox */
            if(myparcel_requires_signature > 0) {
                const requiresSignatureInput = shippingForm.querySelector('input[name="myparcel_requires_signature"]');
                const requiresSignatureLabel = shippingForm.querySelector('label[for="'+ requiresSignatureInput.id + '"]').firstChild.textContent;
                requiresSignatureInput.checked = true;
                shippingSelectedTxt = shippingSelectedTxt + requiresSignatureLabel;
                if(myparcel_only_recipient > 0) {
                    shippingSelectedTxt = shippingSelectedTxt + ', ';
                }
            }

            /* Set recipient checkbox */
            if(myparcel_only_recipient > 0) {
                const onlyRecipientInput = shippingForm.querySelector('input[name="myparcel_only_recipient"]');
                const onlyRecipientLabel = shippingForm.querySelector('label[for="'+ onlyRecipientInput.id + '"]').firstChild.textContent;
                onlyRecipientInput.checked = true;
                shippingSelectedTxt = shippingSelectedTxt + onlyRecipientLabel;
            }

            /* Enclose shipment text */
            if(myparcel_only_recipient > 0 || myparcel_requires_signature > 0) {
                shippingSelectedTxt = ' (' + shippingSelectedTxt + ')';
            }

            /* Set and place text */
            const deliveryOptionId = deliveryOptionInputs.id;
            const deliveryOptionLabel = shippingForm.querySelector('label[for="'+ deliveryOptionId + '"]').firstChild.textContent;
            const shippingSelected = document.querySelector(me.options.currentShipping);
            shippingSelected.innerHTML += '<br/><small>' + deliveryOptionLabel + shippingSelectedTxt + '</small>';

            /* Set orderform fields */
            const confirmOrderForm = document.querySelector(me.options.confirmOrderForm);
            const confirmShippingMethod = confirmOrderForm.querySelector('input[name="myparcel[shipping_method_id]"]');
            const confirmDeliveryType = confirmOrderForm.querySelector('input[name="myparcel[delivery_type]"]');
            const confirmSignature = confirmOrderForm.querySelector('input[name="myparcel[requires_signature]"]');
            const confirmOnlyRecipient = confirmOrderForm.querySelector('input[name="myparcel[only_recipient]"]');

            /* Set orderform values */
            confirmShippingMethod.value = shippingMethodId;
            confirmDeliveryType.value = myparcel_delivery_type;
            confirmSignature.value = myparcel_requires_signature;
            confirmOnlyRecipient.value = myparcel_only_recipient;
        }
    }

    loadMethodOptions(me){
        const httpClient = new HttpClient(window.accessKey, window.contextToken);
        const methodOptionsUrl = '/myparcel/delivery_options';
        const selectedMethod = document.querySelector("input[name=shippingMethodId]:checked");
        const selectedMethodValue = selectedMethod.value;
        const optionsDiv = document.querySelector('[data-shipping-method-id="'+selectedMethodValue+'"]');

        //TODO rewrite check so we don't load everytime we change methods
        if(!optionsDiv || optionsDiv.getAttribute('data-options-loaded') == 'true') {
            return
        }

        httpClient.get(methodOptionsUrl+'?method='+selectedMethodValue, function(response) {
            //check if we have json as response, if so, then it's a error
            if(me.checkIfJsonString(response)){
                const response = JSON.parse(response);

                if(response.success == false && response.code == '422'){
                    return;
                }

                if(response.success == false){
                    //TODO error about unsuccesfull retrievement of shipping options
                    return;
                }
                return
            }

            //we have html, let's append it to the correct method
            optionsDiv.innerHTML = response;
            optionsDiv.setAttribute('data-options-loaded', 'true');

            //EventListener for the date select
            const dateSelect = optionsDiv.querySelector('.date_select select')
            dateSelect.addEventListener('change', (event)=>{

                const dateOptionsNotActive = optionsDiv.querySelectorAll('.delivery_options');
                dateOptionsNotActive.forEach((element)=>{
                    element.classList.add("d-none");
                });

                const dateOptionsDiv = optionsDiv.querySelector('[data-date="'+event.target.value+'"]');
                dateOptionsDiv.classList.remove("d-none");

                const deliveryOptions = dateOptionsDiv.querySelectorAll('[name="myparcel_delivery_type"]');
                deliveryOptions.forEach((element)=>{
                    element.addEventListener('change', (event)=>{

                        let type = event.target.value

                        if(event.target.checked){
                            const recipient_option = optionsDiv.querySelector('[name="myparcel_only_recipient"]');
                            if(type == 1 || type == 3){
                                recipient_option.checked = true;
                            }else{
                                recipient_option.checked = false;
                            }
                        }
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

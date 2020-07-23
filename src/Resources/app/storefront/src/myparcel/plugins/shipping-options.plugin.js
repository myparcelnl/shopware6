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
        const shippingForms = document.querySelectorAll(me.options.shippingForm);

        /* Set costs per delivery type */
        this.setDeliveryTypeCosts();

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
                onlyRecipientInput .checked = true;
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

    setDeliveryTypeCosts() {
        /* Get delivery costs */
        const morningDeliveryCost = document.querySelector('input[name="myparcel_delivery_type_cost_morning"]');
        const standardDeliveryCost = document.querySelector('input[name="myparcel_delivery_type_cost_standard"]');
        const eveningDeliveryCost = document.querySelector('input[name="myparcel_delivery_type_cost_evening"]');
        const pickupDeliveryCost = document.querySelector('input[name="myparcel_delivery_type_cost_pickup"]');

        for (let i = 1; i < 5; i++) {
            let label = document.querySelector('label[for^="myparcel_delivery_type_' + i + '"]');
            let cost;

            if (label !== undefined && label !== null) {
                cost = label.querySelector('span.cost');
            }

            if (cost !== undefined && cost !== null) {
                if (i === 1 && morningDeliveryCost.value > 0) {
                    cost.innerHTML = '+&euro; ' + morningDeliveryCost.value;
                }

                if (i === 2 && standardDeliveryCost.value > 0) {
                    cost.innerHTML = '+&euro; ' + standardDeliveryCost.value;
                }

                if (i === 3 && eveningDeliveryCost.value > 0) {
                    cost.innerHTML = '+&euro; ' + eveningDeliveryCost.value;
                }

                if (i === 4 && pickupDeliveryCost.value > 0) {
                    cost.innerHTML = '+&euro; ' + pickupDeliveryCost.value;
                }
            }
        }
    }
}

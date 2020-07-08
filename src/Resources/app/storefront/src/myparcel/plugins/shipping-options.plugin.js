import Plugin from 'src/plugin-system/plugin.class';

export default class ShippingOptions extends Plugin {
    init() {
        // get shipping form
        const shippingForms = document.querySelectorAll('form[name="myparcel_shipping_form"]');

        if (!!shippingForms) {
            shippingForms.forEach(function (shippingForm) {
                const shippingMethodId = shippingForm.getAttribute('data-shipping-method-id');
                const deliveryOptionInputs = shippingForm.querySelectorAll('input[name="myparcel_delivery_type"]');
                const requiresSignatureInput = shippingForm.querySelector('input[name="myparcel_requires_signature"]');
                const onlyRecipientInput = shippingForm.querySelector('input[name="myparcel_only_recipient"]');

                console.log(shippingMethodId);

                deliveryOptionInputs.forEach(function (deliveryOptionInput) {
                    deliveryOptionInput.addEventListener('change', function() {
                        let targetName = deliveryOptionInput.getAttribute('data-target');
                        console.log(targetName);
                    });
                });

                requiresSignatureInput.addEventListener('change', function() {
                    let targetName = requiresSignatureInput.getAttribute('data-target');
                    console.log(targetName);
                });

                onlyRecipientInput.addEventListener('change', function() {
                    let targetName = onlyRecipientInput.getAttribute('data-target');
                    console.log(targetName);
                });
            });
        }
    }
}
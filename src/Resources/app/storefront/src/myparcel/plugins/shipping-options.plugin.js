import Plugin from 'src/plugin-system/plugin.class';
import CookieStorage from 'src/helper/storage/cookie-storage.helper';

export default class MyParcelShippingOptions extends Plugin {

    static options = {

        /**
         * cookie set to determine if cookies were accepted or denied
         */
        cookieName: 'myparcel-cookie-key'
        // cookieName: 'cookie.groupRequiredDescription'

        /**
         * container selector
         */
        // targetContainer: '.js-cookie-permission-button'
    };

    // get shipping form
    init() {
        const me = this;

        const shippingForms = document.querySelectorAll('.myparcel_shipping_form');
        // CookieStorage.setItem(me.options.cookieName, 'test');
        // CookieStorage.setItem('allowCookie', '');
        const cookiePermission = CookieStorage.getItem(me.options.cookieName);
        console.log('Cookie: ' + cookiePermission);
        // CookieStorage.setItem(cookieName, '1', cookieExpiration);

        if (shippingForms) {
            shippingForms.forEach(function (shippingForm) {
                const shippingMethodId = shippingForm.getAttribute('data-shipping-method-id');
                // const deliveryOptionInputs = shippingForm.querySelectorAll('input[name="myparcel_delivery_type"]');
                // const requiresSignatureInput = shippingForm.querySelector('input[name="myparcel_requires_signature"]');
                // const onlyRecipientInput = shippingForm.querySelector('input[name="myparcel_only_recipient"]');

                console.log(shippingMethodId + ' - v3');
                // deliveryOptionInputs.forEach(function (deliveryOptionInput) {
                //     deliveryOptionInput.addEventListener('change', function() {
                //         const targetName = deliveryOptionInput.getAttribute('data-target');
                //         document.querySelector(targetName).value(deliveryOptionInput.value);
                //         console.log('del option val ' + deliveryOptionInput.value + ' target ' + targetName);
                //     });
                // });
                //
                // requiresSignatureInput.addEventListener('change', function() {
                //     const targetName = requiresSignatureInput.getAttribute('data-target');
                //     console.log(targetName);
                // });
                //
                // onlyRecipientInput.addEventListener('change', function() {
                //     const targetName = onlyRecipientInput.getAttribute('data-target');
                //     console.log(targetName);
                // });
            });

            // document.querySelectorAll('.myparcel_shipping_form').addEventListener('change', function() {
            //     console.log('Changed!');
            // });
            // // document.getElementById("select").onchange = function() { console.log("Changed!"); }
            //
            // // document.addEventListener('click', function (event) {
            // //
            // //     // If the clicked element doesn't have the right selector, bail
            // //     if (!event.target.matches('.click-me')) return;
            // //
            // //     // Don't follow the link
            // //     event.preventDefault();
            // //
            // //     // Log the clicked element in the console
            // //     console.log(event.target);
            // //
            // // }, false);
        }
    }
}

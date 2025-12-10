// js/device-tracker.js

(function($) {
    'use strict';

    const DEVICE_KEY = 'wel_device_token';
    const COOKIE_EXPIRY_DAYS = 730; // 2 years

    // --- Cookie Utility Functions ---
    const setCookie = (name, value, days) => {
        let expires = "";
        if (days) {
            const date = new Date();
            date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
            expires = "; expires=" + date.toUTCString();
        }
        // Secure, SameSite=Lax is crucial for modern browsers
        document.cookie = name + "=" + value + expires + "; path=/; Secure; SameSite=Lax";
    };

    const getCookie = (name) => {
        const nameEQ = name + "=";
        const ca = document.cookie.split(';');
        for(let i = 0; i < ca.length; i++) {
            let c = ca[i];
            while (c.charAt(0) === ' ') c = c.substring(1, c.length);
            if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
        }
        return null;
    };

    // --- Core Device Token Logic ---
    const getDeviceToken = async () => {
        // 1. Check if token already exists in cookie
        let token = getCookie(DEVICE_KEY);

        if (token) {
            return token;
        }

        // 2. Generate new token using FingerprintJS (FPJS is loaded via enqueue)
        try {
            // Wait for FingerprintJS to be available
            if (typeof FingerprintJS === 'undefined') {
                console.error('FingerprintJS library not loaded.');
                return 'fp-load-fail'; // Fallback token
            }
            
            const fp = await FingerprintJS.load();
            const result = await fp.get();
            token = result.visitorId;

            // 3. Save the new token as a long-lived cookie
            setCookie(DEVICE_KEY, token, COOKIE_EXPIRY_DAYS);
            
            return token;
        } catch (e) {
            console.error("Fingerprint generation failed:", e);
            // Fallback token
            token = `fallback-${new Date().getTime()}`;
            setCookie(DEVICE_KEY, token, 1); 
            return token;
        }
    };

    // --- WooCommerce Checkout Integration ---
const attachDeviceTokenToForm = async () => {
        const $checkoutForm = $('form.checkout, form.wc-block-checkout__form');

        // 1. Load the token once the page is ready (proactive loading)
        const initialToken = await getDeviceToken();
        if (initialToken) {
             // Attach the token immediately to the form for other scripts to access
             $checkoutForm.append('<input type="hidden" name="wel_device_token" value="' + initialToken + '" />');
        }
        
        // 2. Hook into the submit event to ensure the token is up-to-date and present
        $checkoutForm.on('submit', async function(e) {
            
            const currentToken = await getDeviceToken(); // Re-fetch the current token
            
            // If the token is missing from the form or needs updating
            let $hiddenField = $('[name="wel_device_token"]', this);

            if ($hiddenField.length === 0) {
                // If the field is totally missing, prevent default, add, and re-submit
                e.preventDefault(); 
                
                $(this).append('<input type="hidden" name="wel_device_token" value="' + currentToken + '" />');
                
                // Manually submit the form (critical step for async functions)
                $(this).off('submit').submit(); 
                return false;
            } 
            
            // If the field exists, ensure its value is current
            if ($hiddenField.val() !== currentToken) {
                 $hiddenField.val(currentToken);
            }

            // If we reached here, the token is up-to-date. Allow submission.
            return true;
        });
    };

    // Run the function when the document is ready and the form is available
    $(document).ready(function() {
        attachDeviceTokenToForm();
    });

})(jQuery);
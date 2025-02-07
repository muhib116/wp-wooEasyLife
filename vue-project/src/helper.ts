import { format, parseISO } from 'date-fns'

export const getContrastColor = (hexColor: string) => {
    // Remove the hash symbol if present
    hexColor = hexColor.replace('#', '')

    // If shorthand hex, convert to full form
    if (hexColor.length === 3) {
        hexColor = hexColor
            .split('')
            .map(char => char + char)
            .join('')
    }

    // Parse RGB values
    const r = parseInt(hexColor.substring(0, 2), 16)
    const g = parseInt(hexColor.substring(2, 4), 16)
    const b = parseInt(hexColor.substring(4, 6), 16)

    // Calculate relative luminance
    const luminance = (0.299 * r + 0.587 * g + 0.114 * b) / 255

    // Return white (#ffffff) for dark backgrounds, black (#000000) for light backgrounds
    return luminance > 0.5 ? '#000000' : '#ffffff'
}
export const hslToHex = (h, s, l) => {
    // Adjust lightness for darker shades to make them slightly lighter
    if (l < 25) {
        l = l + (25 - l) * 0.5; // Increase darkness by 50% towards mid-lightness
    }
    
    l /= 100;
    const a = s * Math.min(l, 1 - l) / 100;
    const f = n => {
        const k = (n + h / 30) % 12;
        const color = l - a * Math.max(Math.min(k - 3, 9 - k, 1), -1);
        return Math.round(255 * color).toString(16).padStart(2, '0');
    };
    
    return `#${f(0)}${f(8)}${f(4)}`;
}

export const generateSlug = (title: string) => {
    return title
        .toLowerCase() // Convert to lowercase
        .trim() // Remove leading and trailing whitespace
        .replace(/&/g, 'and') // Replace '&' with 'and'
        .replace(/[^a-z0-9 -]/g, '') // Remove invalid characters
        .replace(/\s+/g, '-') // Replace spaces with hyphens
        .replace(/-+/g, '-') // Collapse multiple hyphens into one
        .replace(/^-+|-+$/g, ''); // Remove leading and trailing hyphens
}

export const validateBDPhoneNumber = (phoneNumber: string) => {
    // Remove spaces and non-numeric characters except for "+"
    phoneNumber = phoneNumber.replace(/[^\d+]/g, '');

    // Define valid patterns
    const patterns = [
        /^\+8801[3-9]\d{8}$/,  // +880 format
        /^8801[3-9]\d{8}$/,    // 880 format
        /^01[3-9]\d{8}$/       // 01 format
    ];

    // Check if the phone number matches any of the valid patterns
    for (const pattern of patterns) {
        if (pattern.test(phoneNumber)) {
            return true;
        }
    }

    // Return an error message for invalid phone numbers
    return false;
}

export const printDate = (dateString: string) => {
    const date = parseISO(dateString.replace(' ', 'T'))
    const formattedDate = format(date, 'MMMM dd, yyyy hh:mm a')
    return formattedDate
}


export const calculateSMSDetails = (props:string) => {
    const GSM_7BIT = "GSM_7BIT";
    const GSM_7BIT_EX = "GSM_7BIT_EX";
    const UTF16 = "UTF16";

    const gsm7bitExChar = "\\^{}\\\\\\[~\\]|â‚¬\n";
    const gsm7bitChars = "@Â£$Â¥Ã¨Ã©Ã¹Ã¬Ã²Ã‡\\nÃ˜Ã¸\\rÃ…Ã¥Î_Î¦ÎÎ›Î©Î Î¨Î£Î˜ÎžÃ†Ã¦ÃŸÃ‰ !\\\"#Â¤%&'()*+,-./0123456789:;<=>?Â¡ABCDEFGHIJKLMNOPQRSTUVWXYZÃ„ÃÃ‘ÃœÂ§Â¿abcdefghijklmnopqrstuvwxyzÃ¤Ã¶Ã±Ã¼Ã ";

    const messageLength = { GSM_7BIT: 160, GSM_7BIT_EX: 160, UTF16: 70 };
    const multiMessageLength = { GSM_7BIT: 153, GSM_7BIT_EX: 153, UTF16: 67 };
    const gsm7bitRegExp = RegExp("\n^[" + gsm7bitChars + "]*$");
    const gsm7bitExRegExp = RegExp("^[" + gsm7bitChars + gsm7bitExChar + "]*$");
    const gsm7bitExOnlyRegExp = RegExp("^[\\" + gsm7bitExChar + "]*$");

    function detectEncoding(text:string) {
        switch (false) {
            case text.match(gsm7bitRegExp) == null:
                return GSM_7BIT;
            case text.match(gsm7bitExRegExp) == null:
                return GSM_7BIT_EX;
            default:
                return UTF16
        }
    }

    function countGsm7bitEx(text:string) {
        var char2, chars; chars = function () {
            var _i, _len, _results; _results = [];

            for (_i = 0, _len = text.length; _i < _len; _i++) {
                char2 = text[_i];
                if (char2.match(gsm7bitExOnlyRegExp) != null) {
                    _results.push(char2)
                }
            }
            return _results
        }.call(this);
        return chars.length
    }

    props = props.replace(/(\r\n|\n|\r)/gm, " ");
    var encoding = detectEncoding(props);
    let length = props.length, per_message, messages, remaining;
    if (encoding === GSM_7BIT_EX) {
        length += countGsm7bitEx(props);
    }
    per_message = messageLength[encoding];
    if (length > per_message) {
        per_message = multiMessageLength[encoding];
    }
    messages = Math.ceil(length / per_message);
    remaining = per_message * messages - length;

    if (remaining == 0 && messages == 0) {
        remaining = per_message;
    }

    var setSmsCharacterCount = length;
    var setSmsRemainingCount = remaining;
    var setSmsPartCount = messages;

    return {
        encoding,
        totalCharacter: setSmsCharacterCount,
        remainingCharacter: setSmsRemainingCount,
        totalSMS: setSmsPartCount
    }
}

export const normalizePhoneNumber = (phone: string): string => {
    // Remove all non-digit characters
    let normalized = phone.replace(/\D/g, '');

    // Check if the number starts with the country code '880' and replace it with '0'
    if (normalized.startsWith('880')) {
        normalized = '0' + normalized.slice(3); // Remove '880' and prepend '0'
    }

    return normalized;
}


export const  detectInternetState = (callback) => {
    function updateStatus() {
        if (!navigator.onLine) {
            callback({
                type: "warning",
                title: 'You are currently offline. Check your internet connection.'
            });
            return;
        }

        if ('connection' in navigator) {
            const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
            const effectiveType = connection.effectiveType;

            if (effectiveType === "3g") {
                callback({
                    type: "warning",
                    title: 'Slow internet connection.'
                })
            }else if(effectiveType != "4g") {
                callback({
                    type: "danger",
                    title: 'Poor internet connection.'
                })
            }
        }
    }

    // Initial check
    updateStatus();

    // Listen for online/offline events
    window.addEventListener("online", updateStatus);
    window.addEventListener("offline", updateStatus);

    // Listen for connection changes if supported
    if ('connection' in navigator) {
        navigator.connection.addEventListener("change", updateStatus);
    }
}

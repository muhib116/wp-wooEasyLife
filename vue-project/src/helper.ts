import { format, parseISO } from 'date-fns'
import { toast, type ToastPosition } from 'vue3-toastify';

/**
 * Show a notification toast with optional HTML support.
 */
export const showNotification = (
    alertMsg: { type: 'success' | 'info' | 'warning' | 'danger'; message: string },
    closeTime: number | false = 3000,
    supportHtml: boolean = true,
    position: ToastPosition = "bottom-right"
): void => {
    if (alertMsg && !alertMsg.type || !alertMsg.message) return
    const toastType = alertMsg.type === 'danger' ? 'error' : alertMsg.type;

    toast[toastType](supportHtml ? htmlOneLiner(alertMsg.message) : alertMsg.message, {
        autoClose: closeTime,
        position: position,
        dangerouslyHTMLString: supportHtml, // Enable HTML rendering if true
    });
}

export const filterOrderById = (id: number, orders: any[]) => {
    if (!id) return {}
    return orders.find((order: any) => order.id == id)

}

/**
 * Converts multi-line HTML into a single-line, well-formatted HTML string.
 */
export const htmlOneLiner = (html: string): string => {
    return html
        .replace(/\n/g, " ")          // Replace new lines with spaces
        .replace(/\s+/g, " ")         // Collapse multiple spaces into a single space
        .replace(/>\s+</g, "><")      // Remove spaces between HTML tags
        .trim();                      // Trim leading & trailing spaces
}



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
export const hslToHex = (h: number, s: number, l: number): string => {
    // Adjust lightness for darker shades to make them slightly lighter
    if (l < 25) {
        l = l + (25 - l) * 0.5; // Increase darkness by 50% towards mid-lightness
    }

    l /= 100;
    const a = s * Math.min(l, 1 - l) / 100;
    const f = (n: number): string => {
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




/**
 * SMS Counter start here
 */
// --- SMS Encoding Constants and Types (Private to the module) ---

const SMS_ENCODING = {
    GSM_7BIT: 'GSM_7BIT',
    GSM_7BIT_EXT: 'GSM_7BIT_EXT',
    UTF16: 'UTF16'
} as const;

type SMSEncoding = typeof SMS_ENCODING[keyof typeof SMS_ENCODING];

// Character limits per SMS part (single message)
const MESSAGE_LENGTH: Record<SMSEncoding, number> = {
    GSM_7BIT: 160,
    GSM_7BIT_EXT: 160,
    UTF16: 70
};

// Character limits per SMS part (multi-part message)
const MULTI_MESSAGE_LENGTH: Record<SMSEncoding, number> = {
    GSM_7BIT: 153,
    GSM_7BIT_EXT: 153,
    UTF16: 67
};

// GSM 7-bit character sets
const GSM_7BIT_CHARS = "@£$¥èéùìòÇ\nØø\rÅåΔ_ΦΓΛΩΠΨΣΘΞ\x1BÆæßÉ !\"#¤%&'()*+,-./0123456789:;<=>?¡ABCDEFGHIJKLMNOPQRSTUVWXYZÄÖÑÜ§¿abcdefghijklmnopqrstuvwxyzäöñüà";
const GSM_7BIT_EXT_CHARS = "^{}\\[~]|€";

// --- Single Exported Function ---

/**
 * Calculates comprehensive SMS details for a given text, including:
 * - The required encoding (GSM_7BIT, GSM_7BIT_EXT, or UTF16)
 * - The total character count based on SMS technical standards (counting code units for UTF-16)
 * - The number of SMS parts (totalSMS)
 * - The remaining characters in the current part
 * 
 * Supports: English, Bangla, and Emojis according to 3GPP standards.
 * 
 * @param {string} text The input text message.
 * @returns {{encoding: SMSEncoding, totalCharacter: number, remainingCharacter: number, totalSMS: number}} SMS details.
 */
export const calculateSMSDetails = (text: string) => {
    
    // --- Helper Function 1: Detect Encoding ---
    function detectEncoding(input: string): SMSEncoding {
        let hasExtendedGSM = false;

        for (let i = 0; i < input.length; i++) {
            const char = input[i];
            
            // Check if character is in extended GSM set
            if (GSM_7BIT_EXT_CHARS.includes(char)) {
                hasExtendedGSM = true;
                continue;
            }
            
            // Check if character is in basic GSM set
            if (GSM_7BIT_CHARS.includes(char)) {
                continue;
            }
            
            // Character not in any GSM sets (e.g., Bangla, most Emojis) - must use UTF-16
            return SMS_ENCODING.UTF16;
        }

        return hasExtendedGSM ? SMS_ENCODING.GSM_7BIT_EXT : SMS_ENCODING.GSM_7BIT;
    }


    // --- Helper Function 2: Calculate Length ---
    function calculateLength(input: string, encoding: SMSEncoding): number {
        if (encoding === SMS_ENCODING.UTF16) {
            // For UTF-16 (Bangla, most Emojis): SMS technical standard counts UTF-16 code units.
            // This means:
            // - Basic char (ক) = 1
            // - Compound char (কি) = 2 (consonant + combining mark)
            // - Simple Emoji (👋) = 2 (surrogate pair)
            return input.length;

        } else if (encoding === SMS_ENCODING.GSM_7BIT_EXT) {
            // For GSM 7-bit Extended: Extended chars count as 2 (1 char + 1 escape char)
            let extendedCount = 0;
            for (let i = 0; i < input.length; i++) {
                if (GSM_7BIT_EXT_CHARS.includes(input[i])) {
                    extendedCount++;
                }
            }
            return input.length + extendedCount;

        } else {
            // For basic GSM 7-bit: Simple length (all characters count as 1)
            return input.length;
        }
    }

    // --- Main Logic Execution ---

    // Handle invalid input
    if (typeof text !== 'string') {
        return {
            encoding: SMS_ENCODING.UTF16,
            totalCharacter: 0,
            remainingCharacter: MESSAGE_LENGTH.UTF16,
            totalSMS: 0
        };
    }

    // Normalize line breaks to the single GSM 7-bit Line Feed character (\n)
    const normalizedText = text.replace(/(\r\n|\n|\r)/gm, "\n");

    // 1. Detect encoding type
    const encoding = detectEncoding(normalizedText);
    
    // 2. Calculate character length based on encoding rules
    const length = calculateLength(normalizedText, encoding);

    // 3. Determine message length limit
    // Use multi-part limit if the length exceeds the single-part limit.
    const perMessage = length <= MESSAGE_LENGTH[encoding] && length > 0
        ? MESSAGE_LENGTH[encoding] 
        : MULTI_MESSAGE_LENGTH[encoding];

    // 4. Calculate SMS parts
    const messages = length === 0 ? 0 : Math.ceil(length / perMessage);
    
    // 5. Calculate remaining characters
    const effectiveLimit = messages > 1 ? MULTI_MESSAGE_LENGTH[encoding] : MESSAGE_LENGTH[encoding];
    const remaining = messages === 0 
        ? effectiveLimit // Full limit if no message
        : (effectiveLimit * messages) - length;

    return {
        encoding,
        totalCharacter: length,
        remainingCharacter: remaining,
        totalSMS: messages
    };
};
/**
 * SMS counter end here
 */


/**
 * Counts the actual human-perceived characters (graphemes) in a string
 * and checks if the total count exceeds a specified VARCHAR limit.
 * 
 * This is suitable for most modern database systems' VARCHAR counting, 
 * which is typically based on characters, not bytes or code units.
 * 
 * @param {string} text The string to analyze.
 * @param {number} limit The maximum allowed character length (the VARCHAR limit, e.g., 255).
 * @returns {{totalCharacter: number, isOverLimit: boolean, remainingCharacter: number}} The count and limit status.
 */
export const countVarcharCharacters = (text: string, limit: number) => {
    // 1. Handle invalid/non-string input
    if (typeof text !== 'string') {
        return {
            totalCharacter: 0,
            isOverLimit: false,
            remainingCharacter: limit
        };
    }

    // 2. Calculate the character count (Grapheme Count)
    // Array.from(text) correctly splits the string into human-perceived characters 
    // (graphemes), handling multi-unit characters like complex emojis and 
    // compound characters (e.g., "কি" or "👨‍👩‍👧‍👦") as a single unit.
    const totalCharacter = Array.from(text).length;

    // 3. Determine the status
    const isOverLimit = totalCharacter > limit;
    
    // 4. Calculate remaining characters
    const remainingCharacter = Math.max(0, limit - totalCharacter);

    return {
        totalCharacter,
        isOverLimit,
        remainingCharacter
    };
};

// --- Examples ---
// const VARCHAR_LIMIT = 255;
// const TEXT_LIMIT = 10;
// 
// // English (Simple)
// console.log(countVarcharCharacters("Hello", TEXT_LIMIT)); 
// // Output: { totalCharacter: 5, isOverLimit: false, remainingCharacter: 5 }
// 
// // Bangla (Grapheme Count)
// // 'কি' is 1 perceived character (2 code units, but counted as 1 grapheme)
// console.log(countVarcharCharacters("কি", TEXT_LIMIT)); 
// // Output: { totalCharacter: 1, isOverLimit: false, remainingCharacter: 9 }
// 
// // Emoji (Complex, ZWJ Sequence)
// // '👨‍👩‍👧‍👦' is 1 perceived character (multiple code units, but counted as 1 grapheme)
// console.log(countVarcharCharacters("👨‍👩‍👧‍👦", TEXT_LIMIT)); 
// // Output: { totalCharacter: 1, isOverLimit: false, remainingCharacter: 9 }
// 
// // Over Limit
// console.log(countVarcharCharacters("1234567890X", 10)); 
// // Output: { totalCharacter: 11, isOverLimit: true, remainingCharacter: 0 }

export const normalizePhoneNumber = (phone: string): string => {
    // Remove all non-digit characters
    let normalized = phone.replace(/\D/g, '');

    // Check if the number starts with the country code '880' and replace it with '0'
    if (normalized.startsWith('880')) {
        normalized = '0' + normalized.slice(3); // Remove '880' and prepend '0'
    }

    return validateAndFormatPhoneNumber(normalized);
}

export const validateAndFormatPhoneNumber = (phone: string) => {
    // Remove any non-numeric characters
    phone = phone.replace(/\D/g, '');

    // Check if the number has exactly 10 digits and starts with '1'
    if (/^1\d{9}$/.test(phone)) {
        // Prepend '0' to make it a valid 11-digit Bangladeshi phone number
        return '0' + phone;
    }

    return phone;
}


export const detectInternetState = (callback: (msg: { type: string; message: string }) => void) => {
    // Handler for all events
    const updateStatus = () => {
        if (!navigator.onLine) {
            callback({
                type: "danger",
                message: 'You are currently offline. Check your internet connection.'
            });
            return;
        }

        const conn = (navigator as any).connection || (navigator as any).mozConnection || (navigator as any).webkitConnection;
        if (conn && conn.effectiveType) {
            const messages: Record<string, { type: string; message: string }> = {
                "2g": { type: "warning", message: "Slow internet connection." },
                "slow": { type: "danger", message: "Poor internet connection." }
            };
            if (messages[conn.effectiveType]) {
                callback(messages[conn.effectiveType]);
            }
        }
    };

    // Initial check
    updateStatus();

    // Remove previous listeners if any (optional, for SPA/hot reload safety)
    window.removeEventListener("online", updateStatus);
    window.removeEventListener("offline", updateStatus);

    if ('connection' in navigator && (navigator as any).connection?.removeEventListener) {
        (navigator as any).connection.removeEventListener("change", updateStatus);
    }

    // Add listeners
    window.addEventListener("online", updateStatus);
    window.addEventListener("offline", updateStatus);
    if ('connection' in navigator && (navigator as any).connection?.addEventListener) {
        (navigator as any).connection.addEventListener("change", updateStatus);
    }

    // Return a cleanup function for use in Vue's onUnmounted, if needed
    return () => {
        window.removeEventListener("online", updateStatus);
        window.removeEventListener("offline", updateStatus);
        if ('connection' in navigator && (navigator as any).connection?.removeEventListener) {
            (navigator as any).connection.removeEventListener("change", updateStatus);
        }
    };
};


export const checkImageLoad = (imgUrl: string, callback: (isLoaded: boolean) => void) => {
    let img = new Image();
    img.onload = function () {
        callback(true); // Image loaded successfully
    };
    img.onerror = function () {
        callback(false); // Image failed to load
    };
    img.src = imgUrl;
}

/**
 * Generate invoice prefix from domain name
 * Examples:
 * - example.com -> EXM
 * - my-store.com -> MST
 * - shop-bangladesh.com -> SBD
 */
export const getInvoicePrefix = (): string => {
    try {
        // Get the current domain
        const domain = window.location.hostname;

        // Remove common TLDs and split by separators
        const cleanDomain = domain
            .replace(/\.(com|net|org|bd|io|co|app|shop)$/i, '') // Remove TLD
            .replace(/^www\./i, ''); // Remove www prefix

        // Split by dots and hyphens
        const parts = cleanDomain.split(/[.\-_]/);

        // If single word, take first 3 letters
        if (parts.length === 1) {
            const word = parts[0];
            return word.substring(0, 3).toUpperCase();
        }

        // If multiple words, take first letter of each (max 3)
        const prefix = parts
            .slice(0, 3)
            .map(part => part.charAt(0))
            .join('')
            .toUpperCase();

        return prefix || 'ORD'; // Fallback to 'ORD' if empty

    } catch (error) {
        console.error('Error generating invoice prefix:', error);
        return 'ORD'; // Default fallback
    }
};



/**
 * Format invoice with prefix
 * Examples:
 * - 12345 -> EXM-12345
 */
export const formatInvoice = (orderId: number | string): string => {
    const prefix = getInvoicePrefix();
    return `${prefix}-${orderId}`;
};

export const handlePrint = () => {
    window.print();
}

export const printProductDetails = (order: any, cb: () => void, invoice_logo: string) => {
    const qrData = order.courier_data.consignment_id
    const qrUrl = `https://quickchart.io/qr?text=${qrData}&size=100`; // Third-party QR generator

    const printWindow = window.open("", "");
    if (printWindow) {
        printWindow.document.write(`
            <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <title>Document</title>
                    <style>
                        *, ::before, ::after {
                            box-sizing: border-box;
                            margin: 0;
                            padding: 0;
                        }
                        @page {
                            size: 3in 2in landscape;
                            padding: 10px;
                        }
                    </style>
                </head>
                <body>
                    <div style="
                        width: 2.78in; 
                        height: 1.8in; 
                        padding: 4px 4px; 
                        border-radius: 4px;
                        border: 1px solid;
                        font-family: poppins, sans-serif;
                        font-size: 14px;
                        display: flex; 
                        align-items: center;
                        justify-content: space-between;
                        margin-left: 4px;
                    ">
                        <div style="display: grid; gap: 4px;">
                            <img src="${invoice_logo || 'https://api.wpsalehub.com/app-logo'}" alt="Logo" style="height: 40px; max-width: 130px; object-fit: contain; margin-bottom: 4px;" />
                            <p style="margin:0;"><strong>ID: <span style="font-size: 20px">${order.courier_data.consignment_id}</span></strong></p>
                            <p style="margin:0;"><strong>COD:</strong> ${order.total}${order.currency_symbol}</p>
                            <p style="margin:0; word-break: break-all;"><strong>Name:</strong> ${order.customer_name}</p>
                            <p style="margin:0;"><strong>Phone:</strong> ${order.billing_address.phone}</p>
                        </div>
                        <div>
                            <img src="${qrUrl}" alt="QR Code" style="width: 100px; height: 100px;margin-right: -8px;margin-top: -8px;margin-bottom: -8px;" />
                        </div>
                    </div>
                </body>
            </html>
        `);

        printWindow.document.close();
        // Wait for the print job to complete before closing and calling the callback
        printWindow.onafterprint = () => {
            printWindow.close();
            if (typeof cb === "function") {
                cb();
            }
        };

        checkImageLoad(qrUrl, (isLoaded) => {
            if (isLoaded) {
                setTimeout(() => {
                    printWindow.print();
                }, 100); // Delay to ensure the image is loaded
            }
        });
    }
}
<?php
namespace WooEasyLife\Frontend;

class WhatsAppButton {
    private $whatsapp_phone;
    private $whatsapp_default_message;

    public function __construct() {
        $option_key = __PREFIX . 'config'; // Ensure correct prefix
        $existing_config = decode_json_if_string(get_option($option_key));

        $this->whatsapp_phone = $existing_config['whatsapp_phone'];
        $this->whatsapp_default_message = $existing_config['whatsapp_default_message'];

        if (!empty($this->whatsapp_phone)) {
            add_action('wp_footer', [$this, 'simple_whatsapp_floating_button']);
        }

        // Register shortcodes
        add_shortcode('whatsapp_button', [$this, 'whatsapp_button_shortcode']);
        add_shortcode('whatsapp_url', [$this, 'whatsapp_url_shortcode']);
        add_shortcode('whatsapp_number', [$this, 'whatsapp_number_shortcode']);
    }

    public function simple_whatsapp_floating_button() {
        $phone = esc_attr($this->whatsapp_phone);
        $message = urlencode($this->whatsapp_default_message);

        echo "
            <style>
                .whatsapp-float {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background-color: #25D366;
                    color: white;
                    border-radius: 50%;
                    width: 60px;
                    height: 60px;
                    text-align: center;
                    font-size: 30px;
                    box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
                    z-index: 1000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: transform 0.3s ease;
                }
                .whatsapp-float:hover {
                    transform: scale(1.1);
                }
                .whatsapp-float img {
                    width: 35px;
                    height: 35px;
                }
            </style>
            <a href='https://wa.me/{$phone}?text={$message}' target='_blank' class='whatsapp-float' title='Chat with us on WhatsApp'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg' alt='WhatsApp'>
            </a>
        ";
    }

    public function whatsapp_button_shortcode() {
        $phone = esc_attr($this->whatsapp_phone);
        $message = urlencode($this->whatsapp_default_message);

        $style = "
            <style>
                .whatsapp-float {
                    position: fixed;
                    bottom: 20px;
                    right: 20px;
                    background-color: #25D366;
                    color: white;
                    border-radius: 50%;
                    width: 60px;
                    height: 60px;
                    text-align: center;
                    font-size: 30px;
                    box-shadow: 2px 2px 10px rgba(0,0,0,0.3);
                    z-index: 1000;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    transition: transform 0.3s ease;
                }
                .whatsapp-float:hover {
                    transform: scale(1.1);
                }
                .whatsapp-float img {
                    width: 35px;
                    height: 35px;
                }
            </style>
        ";

        $button = "
            <a href='https://wa.me/{$phone}?text={$message}' target='_blank' class='whatsapp-float' title='Chat with us on WhatsApp'>
                <img src='https://upload.wikimedia.org/wikipedia/commons/6/6b/WhatsApp.svg' alt='WhatsApp'>
            </a>
        ";

        return $style . $button;
    }

    // New shortcode function for just phone number
    public function whatsapp_url_shortcode() {
        $phone = esc_attr($this->whatsapp_phone);
        $message = urlencode($this->whatsapp_default_message);

        $whatsapp_url = "https://wa.me/{$phone}?text={$message}";

        // Return the URL as plain text (escaped)
        return esc_html($whatsapp_url);
    }
    public function whatsapp_number_shortcode() {
        return esc_html($this->whatsapp_phone);
    }

}

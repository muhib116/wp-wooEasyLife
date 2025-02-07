<?php

namespace WooEasyLife\API\Frontend;

use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

class OTPHandlerAPI extends WP_REST_Controller
{
    private $otp_length = 4;
    private $otp_expiry = 10 * MINUTE_IN_SECONDS; // 10 minutes expiry
    private $resend_cooldown = 2 * MINUTE_IN_SECONDS; // 2 minutes cooldown

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_routes']);
    }

    /**
     * Register REST API routes for OTP handling.
     */
    public function register_routes()
    {
        register_rest_route('wooeasylife/v1', '/otp/send', [
            'methods'             => 'POST',
            'callback'            => [$this, 'send_otp'],
            'permission_callback' => api_permission_check(),
        ]);

        register_rest_route('wooeasylife/v1', '/otp/resend', [
            'methods'             => 'POST',
            'callback'            => [$this, 'resend_otp'],
            'permission_callback' => api_permission_check(),
        ]);

        register_rest_route('wooeasylife/v1', '/otp/validate', [
            'methods'             => 'POST',
            'callback'            => [$this, 'validate_otp'],
            'permission_callback' => api_permission_check(),
        ]);
    }

    /**
     * Send OTP to a phone number.
     */
    public function send_otp(WP_REST_Request $request)
    {
        $site_title = get_bloginfo('name');
        $phone_number = sanitize_text_field($request->get_param('phone_number'));

        if (empty($phone_number)) {
            return new WP_Error('missing_phone', 'Phone number is required.', ['status' => 400]);
        }

        // Generate OTP
        $otp = $this->generate_otp($phone_number);
        $otp_msg = "Your $site_title OTP is $otp";

        $sms_response = send_sms($phone_number, $otp_msg);

        return new WP_REST_Response([
            'sms_response' => $sms_response,
            'status'  => 'success',
            'message' => 'OTP sent successfully.',
            'expiry'  => $this->otp_expiry / 60 . ' minutes',
        ], 201);
    }

    /**
     * Resend OTP to a phone number.
     */
    public function resend_otp(WP_REST_Request $request)
    {
        $site_title = get_bloginfo('name');
        $phone_number = sanitize_text_field($request->get_param('phone_number'));

        if (empty($phone_number)) {
            return new WP_Error('missing_phone', 'Phone number is required.', ['status' => 400]);
        }

        // Check resend cooldown
        $last_resend_time = get_transient('otp_resend_' . $phone_number);
        if ($last_resend_time && (time() - $last_resend_time < $this->resend_cooldown)) {
            $remaining_time = $this->resend_cooldown - (time() - $last_resend_time);
            return new WP_Error('cooldown_active', "Please wait $remaining_time seconds before requesting a new OTP.", ['status' => 429]);
        }

        // Generate OTP
        $otp = $this->generate_otp($phone_number);
        $otp_msg = "Your $site_title OTP is $otp";

        // Store resend cooldown
        set_transient('otp_resend_' . $phone_number, time(), $this->resend_cooldown);

        // TODO: Integrate with your SMS API
        $sms_response = send_sms($phone_number, $otp_msg);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'OTP resent successfully.',
            'sms_response' => $sms_response,
            'expiry'  => $this->otp_expiry / 60 . ' minutes',
            'cooldown' => $this->resend_cooldown / 60 . ' minutes',
        ], 200);
    }

    /**
     * Validate OTP for a phone number.
     */
    public function validate_otp(WP_REST_Request $request)
    {
        $phone_number = sanitize_text_field($request->get_param('phone_number'));
        $otp = sanitize_text_field($request->get_param('otp'));

        if (empty($phone_number) || empty($otp)) {
            return new WP_Error('missing_parameters', 'Both phone number and OTP are required.', ['status' => 400]);
        }

        // Retrieve the stored OTP
        $stored_otp = get_transient('otp_' . $phone_number);

        if (!$stored_otp) {
            return new WP_Error('otp_expired', 'OTP has expired or does not exist.', ['status' => 400]);
        }

        if ($stored_otp != $otp) {
            return new WP_Error('invalid_otp', 'Invalid OTP. Please try again.', ['status' => 400]);
        }

        // OTP is valid, clear transient
        delete_transient('otp_' . $phone_number);

        return new WP_REST_Response([
            'status'  => 'success',
            'message' => 'OTP validated successfully.',
        ], 200);
    }

    /**
     * Generate and store OTP for a phone number.
     */
    private function generate_otp($phone_number)
    {
        $otp = rand(pow(10, $this->otp_length - 1), pow(10, $this->otp_length) - 1);
        set_transient('otp_' . $phone_number, $otp, $this->otp_expiry);
        return $otp;
    }
}
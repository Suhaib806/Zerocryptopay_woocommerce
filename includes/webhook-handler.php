<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Zerocryptopay_Webhook_Handler {
    private $debug;

    public function __construct() {
        $this->init();
    }

    public function init() {
        add_action('woocommerce_api_zerocryptopay_webhook', array($this, 'handle_webhook'));
        
        $gateway_settings = get_option('woocommerce_zerocryptopay_settings', array());
        $this->debug = isset($gateway_settings['debug']) && $gateway_settings['debug'] === 'yes';
    }

    public function handle_webhook() {
        if ($this->debug) {
            error_log('Zerocryptopay Webhook - Received callback');
            error_log('Zerocryptopay Webhook - POST data: ' . print_r($_POST, true));
        }

        // Verify the webhook data
        if (!isset($_POST['order_id']) || !isset($_POST['signature'])) {
            if ($this->debug) {
                error_log('Zerocryptopay Webhook - Missing required parameters');
            }
            wp_die('Invalid webhook data', 'Zerocryptopay', array('response' => 400));
        }

        $order_id = sanitize_text_field($_POST['order_id']);
        $signature = sanitize_text_field($_POST['signature']);
        $status = isset($_POST['status']) ? sanitize_text_field($_POST['status']) : '';

        // Extract WooCommerce order ID from the Zerocryptopay order ID (remove 'wc_' prefix)
        $wc_order_id = str_replace('wc_', '', $order_id);
        $order = wc_get_order($wc_order_id);

        if (!$order) {
            if ($this->debug) {
                error_log('Zerocryptopay Webhook - Order not found: ' . $wc_order_id);
            }
            wp_die('Order not found', 'Zerocryptopay', array('response' => 404));
        }

        // Verify the order is in a valid state to be updated
        if ($order->get_status() === 'completed' || $order->get_status() === 'cancelled') {
            if ($this->debug) {
                error_log('Zerocryptopay Webhook - Order already processed: ' . $wc_order_id);
            }
            wp_die('Order already processed', 'Zerocryptopay', array('response' => 200));
        }

        // Get gateway settings
        $gateway_settings = get_option('woocommerce_zerocryptopay_settings', array());
        $token = isset($gateway_settings['token']) ? $gateway_settings['token'] : '';

        // Verify signature (implementation depends on Zerocryptopay documentation)
        // This is an example - adjust according to actual signature verification method
        $expected_signature = hash('sha256', $order_id . $status . $token);
        
        if ($signature !== $expected_signature) {
            if ($this->debug) {
                error_log('Zerocryptopay Webhook - Invalid signature');
                error_log('Expected: ' . $expected_signature);
                error_log('Received: ' . $signature);
            }
            wp_die('Invalid signature', 'Zerocryptopay', array('response' => 403));
        }

        // Process payment status
        switch ($status) {
            case 'completed':
            case 'paid':
                $order->payment_complete();
                $order->add_order_note(__('Payment completed via Zerocryptopay.', 'wc-zerocryptopay'));
                break;

            case 'pending':
                // Order might already be on-hold, but add a note
                $order->update_status('on-hold');
                $order->add_order_note(__('Payment pending via Zerocryptopay.', 'wc-zerocryptopay'));
                break;

            case 'failed':
            case 'cancelled':
                $order->update_status('cancelled', __('Payment cancelled or failed via Zerocryptopay.', 'wc-zerocryptopay'));
                break;

            default:
                if ($this->debug) {
                    error_log('Zerocryptopay Webhook - Unknown status: ' . $status);
                }
                $order->add_order_note(sprintf(__('Received unknown payment status: %s', 'wc-zerocryptopay'), $status));
                break;
        }

        // Save additional transaction data if provided
        if (isset($_POST['transaction_id'])) {
            $order->update_meta_data('_zerocryptopay_transaction_id', sanitize_text_field($_POST['transaction_id']));
        }

        $order->save();

        // Respond to the webhook
        wp_die('Webhook processed successfully', 'Zerocryptopay', array('response' => 200));
    }
}

// Initialize the webhook handler
new WC_Zerocryptopay_Webhook_Handler();
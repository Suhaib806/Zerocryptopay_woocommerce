<?php
if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Zerocryptopay extends WC_Payment_Gateway {
    public function __construct() {
        $this->id                 = 'zerocryptopay';
        $this->icon               = '';
        $this->method_title       = __('Zerocryptopay', 'wc-zerocryptopay');
        $this->method_description = __('Secure crypto payments with Zerocryptopay.', 'wc-zerocryptopay');
        $this->supports           = ['products'];

        $this->init_form_fields();
        $this->init_settings();

        $this->title       = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->login       = $this->get_option('login');
        $this->token       = $this->get_option('token');
        $this->key       = $this->get_option('key');
        $this->direct_url  = $this->get_option('direct_url', 'https://zerocryptopay.com/pay.php');
        $this->webhook_url = $this->get_option('webhook_url');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);

        $this->debug = true; // Always enable debugging for now
    }

    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable/Disable', 'wc-zerocryptopay'),
                'type'    => 'checkbox',
                'label'   => __('Enable Zerocryptopay', 'wc-zerocryptopay'),
                'default' => 'yes'
            ],
            'title' => [
                'title'   => __('Title', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => __('Zerocryptopay', 'wc-zerocryptopay')
            ],
            'description' => [
                'title'   => __('Description', 'wc-zerocryptopay'),
                'type'    => 'textarea',
                'default' => __('Pay securely via cryptocurrency.', 'wc-zerocryptopay')
            ],
            'login' => [
                'title'   => __('Zerocryptopay Login', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => ''
            ],
            'token' => [
                'title'   => __('API Token', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => ''
            ],
            'key' => [
                'title'   => __('Secret Key', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => ''
            ],
            'direct_url' => [
                'title'   => __('Direct Payment URL', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => 'https://zerocryptopay.com/pay.php'
            ],
            'webhook_url' => [
                'title'   => __('Webhook URL', 'wc-zerocryptopay'),
                'type'    => 'text',
                'default' => site_url('?wc-api=zerocryptopay_webhook')
            ],
            'debug' => [
                'title'       => __('Debug Log', 'wc-zerocryptopay'),
                'type'        => 'checkbox',
                'label'       => __('Enable logging', 'wc-zerocryptopay'),
                'default'     => 'yes'
            ]
        ];
    }

  /**
     * Store debug log content
     * @var string
     */
    private $debug_log_content = '';

    /**
     * Log debug information to a string
     * 
     * @param string $message The message to log
     * @param string $type The type of log entry (request, response, etc.)
     * @param int $order_id The order ID
     */
    private function log_debug($message, $type, $order_id) {
        // Get the parent directory
        $log_dir = WP_CONTENT_DIR . '/plugins/woocommerce-zerocryptopay/includes';
        
        // Define the log file path
        $log_file = $log_dir . '/webhook.txt';
        
        // Format the log entry with a timestamp, divider, and message type
        $log_entry = "\n\n" . str_repeat("=", 50) . "\n";
        $log_entry .= "TYPE: " . strtoupper($type) . " | ORDER ID: " . $order_id . " | TIME: " . date('Y-m-d H:i:s') . "\n";
        $log_entry .= str_repeat("=", 50) . "\n";
        $log_entry .= $message . "\n";
        
         $this->debug_log_content .= $log_entry;
         
           // Write the entire log content (replacing any existing content)
        file_put_contents($log_file, $this->debug_log_content);
    }

    public function process_payment($order_id) {
        
          $this->debug_log_content = "";
          
        $order = wc_get_order($order_id);
        $amount = number_format($order->get_total(), 2, '.', ''); // Format amount properly
        $currency = $order->get_currency();
        $customer_email = $order->get_billing_email();
        $order_id_unique = $order->get_id();
        
        // Add detailed debugging for signature
        $debug_log = "Amount: '" . $amount . "' (length: " . strlen($amount) . ")\n";
        $debug_log .= "Token: '" . $this->token . "' (length: " . strlen($this->token) . ")\n";
        $debug_log .= "Order ID: '" . $order_id_unique . "' (length: " . strlen($order_id_unique) . ")\n";
        $debug_log .= "Login: '" . $this->login . "' (length: " . strlen($this->login) . ")\n";
        $debug_log .= "Key: '" . $this->key . "' (length: " . strlen($this->key) . ")\n";
        
        // Log the signature debugging info
        $this->log_debug($debug_log, 'signature_debug', $order_id);
        
        $signature_string2 = $amount . $this->key . $order_id_unique . $this->login;
        $signature = hash('sha256', $signature_string2);
        
        // Continue with the rest of your original code...
        $request_body = [
            'login' => $this->login,
            'amount' => $amount,
            'token' => $this->token,
            'order_id' => $order_id_unique,
            'signature' => $signature
        ];
        
        // Log the request details
        $request_log = "Order ID: " . $order_id . "\n";
        $request_log .= "Order ID Unique: " . $order_id_unique . "\n";
        $request_log .= "Amount: " . $amount . "\n";
        $request_log .= "Currency: " . $currency . "\n";
        $request_log .= "Login: " . $this->login . "\n";
        $request_log .= "Signature: " . $signature . "\n";
        $request_log .= "API Endpoint: https://zerocryptopay.com/pay/newtrack/\n";
        $request_log .= "Request Body: " . print_r($request_body, true) . "\n";
        
        $this->log_debug($request_log, 'api_request', $order_id);

        // Make the API request
        $response = wp_remote_post('https://zerocryptopay.com/pay/newtrack/', [
            'body' => $request_body,
            'timeout' => 30,
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ]
        ]);

        // Prepare response log
        $response_log = "";
        
        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $response_log .= "WP_ERROR: " . $error_message . "\n";
            
            $this->log_debug($response_log, 'api_error', $order_id);
            
            wc_add_notice('Payment error: ' . $error_message, 'error');
            return;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);

        $response_log .= "Status Code: " . $status_code . "\n\n";
        $response_log .= "Response Body (Raw):\n" . $body . "\n\n";

        // Try to parse JSON response
        $parsed_response = json_decode($body, true);
        if ($parsed_response) {
            $response_log .= "Response Body (Parsed):\n" . print_r($parsed_response, true) . "\n";
        } else {
            $response_log .= "JSON Parse Error: " . json_last_error_msg() . "\n";
        }

        // Log the API response
        $this->log_debug($response_log, 'api_response', $order_id);

        // Check for errors
        if (!$parsed_response || isset($parsed_response['error'])) {
            $error_message = isset($parsed_response['error']) ? $parsed_response['error'] : 'Invalid response from payment gateway';
            wc_add_notice('Payment error: ' . $error_message, 'error');
            return;
        }

        // Store payment information in order meta
        $order->update_meta_data('_zerocryptopay_order_id', $order_id_unique);
        if (isset($parsed_response['id'])) {
            $order->update_meta_data('_zerocryptopay_transaction_id', $parsed_response['id']);
        }
        if (isset($parsed_response['hash_trans'])) {
            $order->update_meta_data('_zerocryptopay_hash_trans', $parsed_response['hash_trans']);
        }
        $order->save();

        $order->update_status('on-hold', __('Awaiting Zerocryptopay payment', 'wc-zerocryptopay'));

        // Set payment URL based on API response
        if (isset($parsed_response['url_to_pay']) && !empty($parsed_response['url_to_pay'])) {
            // Use the URL directly provided in the response
            $payment_url = $parsed_response['url_to_pay'];
        } else if (isset($parsed_response['id']) && isset($parsed_response['hash_trans'])) {
            // Construct URL from ID and hash_trans (based on your example)
            $payment_url = "https://zerocryptopay.com/pay/{$parsed_response['id']}/{$parsed_response['hash_trans']}";
        } else {
            // Fallback to direct URL with parameters (legacy approach)
            $success_url = urlencode($this->get_return_url($order));
            $cancel_url = urlencode($order->get_cancel_order_url());
            $callback_url = urlencode($this->webhook_url);
            
            $payment_url = add_query_arg([
                'login'       => $this->login,
                'amount'      => $amount,
                'currency'    => $currency,
                'order_id'    => $order_id_unique,
                'email'       => $customer_email,
                'signature'   => $signature,
                'success_url' => $success_url,
                'cancel_url'  => $cancel_url,
                'callback'    => $callback_url 
            ], $this->direct_url);
        }

        // Log the final payment URL
        $this->log_debug("Final Payment URL: " . $payment_url, 'payment_url', $order_id);

        WC()->cart->empty_cart();
 
        return [
            'result'   => 'success',
            'redirect' => $payment_url
        ];
    }
}
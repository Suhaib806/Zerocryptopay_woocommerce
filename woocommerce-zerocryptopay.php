<?php
/**
 * Plugin Name: WooCommerce Zerocryptopay Gateway
 * Plugin URI: https://www.zerocryptopay.com/
 * Description: Integrates Zerocryptopay payment gateway with WooCommerce.
 * Version: 1.0.0
 * Author: Suhaib Nazir
 * Author URI: https://www.suhaibnazir.me/
 * Text Domain: wc-zerocryptopay
 */

if (!defined('ABSPATH')) {
    exit;
}

// Check if WooCommerce is active before loading
function wc_zerocryptopay_check_woocommerce() {
    if (!class_exists('WooCommerce')) {
        add_action('admin_notices', function () {
            echo '<div class="error"><p><strong>WooCommerce Zerocryptopay</strong> requires WooCommerce to be installed and active.</p></div>';
        });
        return false;
    }
    return true;
}

// Load gateway class if WooCommerce is active
function wc_zerocryptopay_init() {
    if (!wc_zerocryptopay_check_woocommerce()) {
        return;
    }

    require_once plugin_dir_path(__FILE__) . 'includes/class-zerocryptopay.php';
    require_once plugin_dir_path(__FILE__) . 'includes/webhook-handler.php';

    add_filter('woocommerce_payment_gateways', function ($methods) {
        $methods[] = 'WC_Gateway_Zerocryptopay';
        return $methods;
    });
}
add_action('plugins_loaded', 'wc_zerocryptopay_init');

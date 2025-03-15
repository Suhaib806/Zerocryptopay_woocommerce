# WooCommerce Zerocryptopay Gateway

A WooCommerce payment gateway extension that allows you to accept cryptocurrency payments via Zerocryptopay.

## Description

The WooCommerce Zerocryptopay Gateway plugin integrates Zerocryptopay's cryptocurrency payment processing services with your WooCommerce store. This allows your customers to pay for products using various cryptocurrencies in a secure and efficient manner.

## Features

- Accept cryptocurrency payments directly on your WooCommerce store
- Automatic order status updates
- Secure payment processing
- Detailed transaction logging for troubleshooting
- Customizable payment gateway title and description
- Support for webhook notifications

## Requirements

- WordPress 5.0 or higher
- WooCommerce 4.0 or higher
- PHP 7.2 or higher
- SSL Certificate installed on your website (recommended for security)
- Zerocryptopay merchant account

## Installation

1. Download the plugin ZIP file.
2. Go to WordPress Admin → Plugins → Add New.
3. Click on the "Upload Plugin" button at the top of the page.
4. Choose the plugin ZIP file and click "Install Now".
5. After installation, click "Activate Plugin".

## Configuration

1. Go to WooCommerce → Settings → Payments.
2. Click on "Zerocryptopay" to configure the payment gateway.
3. Configure the following settings:
   - **Enable/Disable**: Enable or disable the payment gateway.
   - **Title**: The title displayed to customers during checkout.
   - **Description**: The description displayed to customers during checkout.
   - **Zerocryptopay Login**: Your Zerocryptopay merchant login.
   - **API Token**: Your Zerocryptopay API token.
   - **Secret Key**: Your Zerocryptopay secret key used for signature generation.
   - **Direct Payment URL**: The URL to the Zerocryptopay payment page (default: https://zerocryptopay.com/pay.php).
   - **Webhook URL**: The URL that Zerocryptopay will use to send payment notifications to your store (auto-generated).
   - **Debug Log**: Enable/disable transaction logging for debugging purposes.
4. Click "Save changes".

## How It Works

1. Customer selects Zerocryptopay as their payment method during checkout.
2. The plugin creates a payment request to Zerocryptopay with order details.
3. Customer is redirected to Zerocryptopay to complete their payment.
4. After payment, Zerocryptopay sends a notification to your store via webhook.
5. The order status is updated automatically.

## Debugging

The plugin includes a comprehensive debugging system that logs all transaction details to a file named `webhook.txt` in the `wp-content/plugins/woocommerce-zerocryptopay/includes/` directory. This file is overwritten with each new transaction, so it will only contain the most recent payment processing information.

The log includes:
- Signature generation details
- API request information
- API response data
- Final payment URL

## Troubleshooting

Common issues and their solutions:

1. **Payment not redirecting properly**:
   - Verify your Zerocryptopay merchant credentials
   - Ensure the Direct Payment URL is correct
   
2. **Webhook notifications not working**:
   - Check that your website is accessible from the internet
   - Verify the webhook URL in your Zerocryptopay merchant dashboard matches the one in plugin settings

3. **Orders not updating after payment**:
   - Enable Debug Log and check webhook.txt for errors
   - Ensure your server allows incoming connections from Zerocryptopay servers

## Frequently Asked Questions

**Q: Which cryptocurrencies are supported?**

A: The plugin supports all cryptocurrencies offered by Zerocryptopay. Please refer to their website for the most current list.

**Q: Is this plugin compatible with my theme?**

A: The plugin should work with any properly coded WordPress theme and WooCommerce installation.

**Q: Where can I get support?**

A: For plugin-related issues, please use the plugin support forum. For Zerocryptopay account or API issues, please contact Zerocryptopay directly.

## Changelog

### 1.0.0
- Initial release

## Author

Developed by Suhaib Nazir (https://www.suhaibnazir.me/)

## License

This plugin is licensed under the GPL v2 or later.

Copyright © 2025 Suhaib Nazir

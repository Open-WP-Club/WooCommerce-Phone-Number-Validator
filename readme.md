# WooCommerce Phone Number Validator

## Description

WooCommerce Phone Number Validator is a WordPress plugin that enhances the WooCommerce checkout process by validating phone numbers based on country-specific patterns. It allows store owners to ensure that customers enter valid phone numbers for their respective countries.

## Features

- Validates phone numbers for 200+ countries
- Easy-to-use interface for selecting countries to validate
- Option to validate all countries at once (Worldwide option)
- Custom regex support for special cases
- Test mode for admin and shop manager roles
- Bulk validation tool for existing user phone numbers
- Seamless integration with WooCommerce settings

## Installation

1. Download the plugin files and upload them to your `/wp-content/plugins/woocommerce-phone-validator` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress.
3. Use the WooCommerce -> Settings -> Phone Validator screen to configure the plugin.

## Usage

1. Go to WooCommerce -> Settings -> Phone Validator.
2. Select the countries you want to validate phone numbers for, or choose "Worldwide" to validate all countries.
3. Optionally, enter a custom regex pattern if needed.
4. Enable or disable Test Mode as required.
5. Save your settings using the "Save Changes" button.
6. Use the Bulk Validation tool at the bottom of the settings page to check existing user phone numbers.

## Bulk Validation

The Bulk Validation tool allows you to check all existing user phone numbers in your WooCommerce store. To use this feature:

1. Navigate to the Phone Validator settings page.
2. Scroll down to the "Bulk Phone Number Validation" section.
3. Click the "Start Bulk Validation" button.
4. The plugin will process all user phone numbers and display a summary of valid, invalid, and missing numbers.
5. If there are any invalid or missing numbers, a table will show details for each affected user.

## Test Mode

When Test Mode is enabled, phone number validation will only be applied to admin and shop manager user roles. This allows you to test the validation process without affecting regular customer checkouts.

## Custom Regex

If you need to use a custom regex pattern for phone number validation, you can enter it in the Custom Regex field. This will override the default country-specific patterns for all selected countries.

## Support

If you encounter any issues or have questions, please open an issue on the plugin's GitHub repository.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This plugin is licensed under the GPL v2 or later.

## Credits

Developed by Open WP Club

<?php

/**
 * Plugin Name:             WooCommerce Phone Number Validator
 * Plugin URI:              https://github.com/Open-WP-Club/WooCommerce-Phone-Number-Validator
 * Description:             Adds country-specific phone number validation to WooCommerce checkout
 * Version:                 1.1.0
 * Author:                  Open WP Club
 * Author URI:              https://openwpclub.com
 * License:                 GPL-2.0 License
 * Requires Plugins:        woocommerce
 * Requires at least:       6.0
 * Requires PHP:            7.4
 * Tested up to:            6.6.2
 */


if (!defined('ABSPATH')) {
  exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'countries.php';

class WC_Phone_Validator
{
  private $countries;

  public function __construct()
  {
    $this->countries = new WC_Phone_Validator_Countries();
    add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
    add_action('woocommerce_settings_tabs_phone_validator', array($this, 'settings_tab'));
    add_action('woocommerce_update_options_phone_validator', array($this, 'update_settings'));
    add_action('woocommerce_checkout_process', array($this, 'validate_phone_number'));
    add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    add_action('admin_menu', array($this, 'add_bulk_validation_menu'));
    add_action('wp_ajax_bulk_validate_phone_numbers', array($this, 'bulk_validate_phone_numbers'));
  }

  public function add_settings_tab($settings_tabs)
  {
    $settings_tabs['phone_validator'] = __('Phone Validator', 'woocommerce');
    return $settings_tabs;
  }

  public function settings_tab()
  {
    woocommerce_admin_fields($this->get_settings());
  }

  public function update_settings()
  {
    woocommerce_update_options($this->get_settings());
  }

  public function get_settings()
  {
    return array(
      'section_title' => array(
        'name'     => __('Phone Number Validator Settings', 'woocommerce'),
        'type'     => 'title',
        'desc'     => '',
        'id'       => 'wc_phone_validator_section_title'
      ),
      'countries' => array(
        'name'     => __('Countries to Validate', 'woocommerce'),
        'type'     => 'multiselect',
        'options'  => $this->get_country_options(),
        'desc'     => __('Select countries for phone number validation. Choose "Worldwide" to validate all countries.', 'woocommerce'),
        'id'       => 'wc_phone_validator_countries',
        'class'    => 'wc-enhanced-select',
      ),
      'custom_regex' => array(
        'name'     => __('Custom Regex', 'woocommerce'),
        'type'     => 'text',
        'desc'     => __('Enter a custom regex pattern to use for validation (optional)', 'woocommerce'),
        'id'       => 'wc_phone_validator_custom_regex'
      ),
      'test_mode' => array(
        'name'     => __('Test Mode', 'woocommerce'),
        'type'     => 'checkbox',
        'desc'     => __('Enable test mode. When active, phone validation will only be applied to admin and shop manager user roles.', 'woocommerce'),
        'id'       => 'wc_phone_validator_test_mode'
      ),
      'section_end' => array(
        'type' => 'sectionend',
        'id' => 'wc_phone_validator_section_end'
      )
    );
  }

  private function get_country_options()
  {
    $countries = WC()->countries->get_countries();
    $options = array('WORLDWIDE' => __('Worldwide (All Countries)', 'woocommerce'));
    return array_merge($options, $countries);
  }

  public function validate_phone_number()
  {
    $test_mode = get_option('wc_phone_validator_test_mode', 'no');

    if ($test_mode === 'yes' && !current_user_can('manage_woocommerce')) {
      return; // Skip validation for non-admin users when in test mode
    }

    $billing_phone = $_POST['billing_phone'];
    $billing_country = $_POST['billing_country'];

    $valid_countries = get_option('wc_phone_validator_countries', array());
    $custom_regex = get_option('wc_phone_validator_custom_regex', '');

    if (in_array('WORLDWIDE', $valid_countries) || in_array($billing_country, $valid_countries)) {
      $valid = $this->is_valid_phone_number($billing_phone, $billing_country, $custom_regex);

      if (!$valid) {
        $country_name = WC()->countries->countries[$billing_country];
        wc_add_notice(sprintf(__('Please enter a valid phone number for %s.', 'woocommerce'), $country_name), 'error');
      }
    }
  }

  private function is_valid_phone_number($phone, $country, $custom_regex)
  {
    if (!empty($custom_regex)) {
      return preg_match($custom_regex, $phone);
    }

    $pattern = $this->countries->get_country_pattern($country);
    return $pattern ? preg_match($pattern, $phone) : true;
  }

  public function enqueue_admin_scripts($hook)
  {
    if ('woocommerce_page_wc-settings' !== $hook) {
      return;
    }
    wp_enqueue_script('wc-phone-validator-admin', plugins_url('admin.js', __FILE__), array('jquery'), '1.2', true);
  }

  public function add_bulk_validation_menu()
  {
    add_submenu_page(
      'woocommerce',
      __('Bulk Phone Validation', 'woocommerce'),
      __('Bulk Phone Validation', 'woocommerce'),
      'manage_woocommerce',
      'wc-bulk-phone-validation',
      array($this, 'bulk_validation_page')
    );
  }

  public function bulk_validation_page()
  {
?>
    <div class="wrap">
      <h1><?php echo esc_html(__('Bulk Phone Number Validation', 'woocommerce')); ?></h1>
      <p><?php echo esc_html(__('Click the button below to validate all customer phone numbers.', 'woocommerce')); ?></p>
      <button id="bulk-validate-button" class="button button-primary">
        <?php echo esc_html(__('Start Bulk Validation', 'woocommerce')); ?>
      </button>
      <div id="bulk-validation-results"></div>
    </div>
    <script>
      jQuery(document).ready(function($) {
        $('#bulk-validate-button').click(function() {
          var $button = $(this);
          var $results = $('#bulk-validation-results');

          $button.prop('disabled', true);
          $results.html('<?php echo esc_js(__('Validating...', 'woocommerce')); ?>');

          $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
              action: 'bulk_validate_phone_numbers'
            },
            success: function(response) {
              $results.html(response);
              $button.prop('disabled', false);
            },
            error: function() {
              $results.html('<?php echo esc_js(__('An error occurred during validation.', 'woocommerce')); ?>');
              $button.prop('disabled', false);
            }
          });
        });
      });
    </script>
<?php
  }

  public function bulk_validate_phone_numbers()
  {
    $customers = get_users(array('role' => 'customer'));
    $results = array(
      'valid' => 0,
      'invalid' => 0,
      'invalid_numbers' => array()
    );

    foreach ($customers as $customer) {
      $phone = get_user_meta($customer->ID, 'billing_phone', true);
      $country = get_user_meta($customer->ID, 'billing_country', true);

      if ($this->is_valid_phone_number($phone, $country, '')) {
        $results['valid']++;
      } else {
        $results['invalid']++;
        $results['invalid_numbers'][] = array(
          'user_id' => $customer->ID,
          'phone' => $phone,
          'country' => $country
        );
      }
    }

    $output = sprintf(
      __('Validation complete. Valid numbers: %d, Invalid numbers: %d', 'woocommerce'),
      $results['valid'],
      $results['invalid']
    );

    if (!empty($results['invalid_numbers'])) {
      $output .= '<h3>' . __('Invalid Numbers:', 'woocommerce') . '</h3><ul>';
      foreach ($results['invalid_numbers'] as $invalid) {
        $output .= sprintf(
          '<li>User ID: %d, Phone: %s, Country: %s</li>',
          $invalid['user_id'],
          esc_html($invalid['phone']),
          esc_html($invalid['country'])
        );
      }
      $output .= '</ul>';
    }

    echo $output;
    wp_die();
  }
}

new WC_Phone_Validator();

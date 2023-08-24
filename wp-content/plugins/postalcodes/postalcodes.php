<?php

/**
 * Plugin Name: Postal codes list
 * Description: A comma separated list of postal codes
 * Version: 1.0
 * Author: Neven
 */


class PostalCodesPlugin{


    // Constructor function
     function __construct(){
        add_action('admin_menu', array($this, 'adminPage'));
        add_action('admin_init', array($this, 'setupSettings'));
        add_action('wp_head', array($this, 'validationFrontEnd'));
        add_action('woocommerce_checkout_process', array($this, 'validationBackEnd'));
    }

    // Creating custom options page in WP admin
     function adminPage(){
        add_options_page(
            'Postal Codes',
            'List of postal codes',
            'manage_options',
            'postal-code-settings',
            array($this, 'displayAdminPage')
        );
    }

    // Callback function for displaying the custom options page
    function displayAdminPage(){
        ?>
        <div class="wrap">
            <h2>Postal Code Settings</h2>
            <form method="post" action="options.php">
            <?php
            settings_fields('postal_code_section');
            do_settings_sections('postal-code-settings');
            submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    // Settings API
     function setupSettings() {
        add_settings_section(
            'postal_code_section', 
            'Postal Code Settings', 
            array($this, 'sectionTitle'), 
            'postal-code-settings');

         add_settings_field(
            'postal_codes', 
            'Enter Postal Codes', 
            array($this, 'inputCodes'), 
            'postal-code-settings', 
            'postal_code_section');

        register_setting(
            'postal_code_section', 
            'postal_codes');
    }
    
    // Callback function for add_settings_section()
     function sectionTitle() {
        echo 'Enter a comma-separated list of postal codes.';
    }

    // Callback function for add_settings_field()
     function inputCodes() {
        $postalCodes = get_option('postal_codes');
        echo '<input type="text" id="postal_codes" name="postal_codes" value="' . esc_attr($postalCodes) . '" />';
    }   


    /* -- VALIDATION -- */

    // Front-End checkout validation
    function validationFrontEnd() {
        $postalCodes = get_option('postal_codes');
        $codesArray = explode(',', $postalCodes);
   
        ?>
        <script>
          jQuery(document).ready(function ($) {
             $("form.checkout").on("submit", function (e) {
            const userPostalCode = $("#billing_postcode").val();
            const allowedPostalCodes = <?php echo json_encode($codesArray); ?>;
            const allowedFin = [];
        
            allowedPostalCodes.forEach((code) => {
                let codeFin = code.trim();
                allowedFin.push(codeFin);

            });
            
            if (!allowedFin.includes(userPostalCode)) {
                     e.preventDefault();
                    alert("Invalid postal code. Please enter a valid postal code.");
                 }
             });
        });
        </script>
        <?php
    }

    // Back-End checkout validation
    // Server-side validation
    function validateCode($enteredCode) {
        $postalCodes = get_option('postal_codes');
        $codesArray = explode(',', $postalCodes);
        $enteredCode = sanitize_text_field(trim($enteredCode));

        return in_array($enteredCode, $codesArray);
    }


    // Hook validation into Woocommerce
    function validationBackEnd() {
        global $woocommerce;
        $billingPostcode = isset($_POST['billing_postcode']) ? sanitize_text_field($_POST['billing_postcode']) : '';
    
        if (!($this->validateCode($billingPostcode))) {
            wc_add_notice('Invalid postal code. Please enter a valid postal code.', 'error');
        }
    }

}

$postalCodesPlugin = new PostalCodesPlugin();


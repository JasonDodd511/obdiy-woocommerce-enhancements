<?php

/*
Plugin Name: OBDIY WooCommerce Enhancements
Plugin URI: https://github.com/JasonDodd511/obdiy-woocommerce-enhancements
Description: Plugin to house WooCommerce snippets.
Version: 1.0
Author: Jason Dodd
Author URI: https://cambent.com
License: GPL2
GitHub Plugin URI: https://github.com/JasonDodd511/obdiy-woocommerce-enhancements
GitHub Branch:     master
GitHub Languages:
*/

/**
 * Login/Logout Link
 *
 * Meant to be used in conjuction with the "Shortcode In Menus" plugin
 * to create a WP Account longin/logout link that you place in one
 * or more menus.
 *
 * use: [login-logout-link]
 */

function loginLogoutLink(){
	if ( is_user_logged_in() ) {
		$link = "<a href='/my-account/customer-logout'>Log Out</a>";
	} else {
		$link = "<a href='/my-account'>Log In</a>";
	}
	return $link;
}

add_shortcode( 'login-logout-link' , loginLogoutLink );

/**
 * 'My Account' Admin Link
 *
 * Meant to be used in conjuction with the "Shortcode In Menus" plugin
 * to create a WooCommerce My Account link that you place in one
 * or more menus.
 *
 * use: [my-account-link]
 */

function myAccountLink(){
	global $current_user;
	get_currentuserinfo();

	if ( is_user_logged_in() ) {
		$link = "<a href='/my-account'>Logged in as: " . $current_user->user_firstname . " " . $current_user->user_lastname . "</a>";
	} else {
		$link = '';
	}

	return $link;
}

add_shortcode( 'my-account-link' , myAccountLink );

/**
 * Auto Complete all WooCommerce orders.
 *
 * If I ever sell a shipable product I may have to come back here and make this a bit more robust...
 */
add_action( 'woocommerce_thankyou', 'fmpm_custom_woocommerce_auto_complete_order' );
function fmpm_custom_woocommerce_auto_complete_order( $order_id ) {
	if ( ! $order_id ) {
		return;
	}

	$order = wc_get_order( $order_id );
	$order->update_status( 'completed' );
}

/**
 * Woocommerce - Allow Guest Checkout on Certain products
 *
 */

// Display Guest Checkout Field
add_action( 'woocommerce_product_options_general_product_data', 'woo_add_custom_general_fields' );
function woo_add_custom_general_fields() {
	global $woocommerce, $post;

	echo '<div class="options_group">';

	// Checkbox
	woocommerce_wp_checkbox(
		array(
			'id'            => '_allow_guest_checkout',
			'wrapper_class' => 'show_if_simple',
			'label'         => __('Checkout', 'woocommerce' ),
			'description'   => __('Allow Guest Checkout', 'woocommerce' )
		)
	);

	echo '</div>';
}

// Save Guest Checkout Field
add_action( 'woocommerce_process_product_meta', 'woo_add_custom_general_fields_save' );
function woo_add_custom_general_fields_save( $post_id ){
	$woocommerce_checkbox = isset( $_POST['_allow_guest_checkout'] ) ? 'yes' : 'no';
	update_post_meta( $post_id, '_allow_guest_checkout', $woocommerce_checkbox );
}

// Enable Guest Checkout on Certain products
add_filter( 'pre_option_woocommerce_enable_guest_checkout', 'enable_guest_checkout_based_on_product' );
function enable_guest_checkout_based_on_product( $value ) {

	if ( WC()->cart ) {
		$cart = WC()->cart->get_cart();
		foreach ( $cart as $item ) {
			if ( get_post_meta( $item['product_id'], '_allow_guest_checkout', true ) == 'yes' ) {
				$value = "yes";
			} else {
				$value = "no";
				break;
			}
		}
	}

	return $value;
}
/**
 * GDPR Compliance - add a privacy policy checkbox to the checkout form
 *  
 */
 
add_action( 'woocommerce_review_order_before_submit', 'obdiy_add_checkout_privacy_policy', 9 );
   
function obdiy_add_checkout_privacy_policy() {
  
woocommerce_form_field( 'privacy_policy', array(
    'type'          => 'checkbox',
    'class'         => array('form-row privacy'),
    'label_class'   => array('woocommerce-form__label woocommerce-form__label-for-checkbox checkbox'),
    'input_class'   => array('woocommerce-form__input woocommerce-form__input-checkbox input-checkbox'),
    'required'      => true,
    'label'         => 'Your personal data will help us create your account and support your user experience on the 
    			PM Perspective website.  Please readn adnad accept our <a href="/privacy-policy">Privacy Policy</a> document
			where you can find more information on how we use your personal data.',
)); 
  
}
  
// Show notice if customer does not check the box
   
add_action( 'woocommerce_checkout_process', 'obdiy_not_approved_privacy' );
  
function bodiy_not_approved_privacy() {
    if ( ! (int) isset( $_POST['privacy_policy'] ) ) {
        wc_add_notice( __( 'Please acknowledge the Privacy Policy' ), 'error' );
    }
}

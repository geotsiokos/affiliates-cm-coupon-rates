<?php
/**
 * affiliates-cm-coupon-rates.php
 *
 * Copyright (c) 2015 www.netpad.gr
 *
 * This code is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * This header and all notices must be kept intact.
 *
 * @author gtsiokos
 * @package affiliates-cm-coupon-rates
 * @since 1.0.0
 *
 * Plugin Name: Affiliates Custom Method Coupon Rates
 * @todo change plugin URI
 * Plugin URI: http://www.itthinx.com/plugins/affiliates-cm-coupon-rates/
 * Description: Set different affiliate rate according to coupon code, compatible with Affiliates Pro and Affiliates Enterprise by <a href="http://www.itthinx.com" target="_blank">itthinx</a> and <a href="http://www.woothemes.com/woocommerce" target="_blank">WooCommerce</a>.
 * Author: gtsiokos
 * Author URI: http://www.netpad.gr/
 * Version: 1.0.0
 */

if ( !defined('ABSPATH' ) ) {
	exit;
}

class Affiliates_CM_Coupon_Rates {

	const PLUGIN_OPTIONS 			= 'affiliates_cm_coupon_rates';
	const REFERRAL_RATE 			= 'referral-rate';
	const REFERRAL_RATE_DEFAULT 	= '0';
	const COUPON_PREFIX				= 'coupon_prefix';
	const COUPON_PREFIX_DEFAULT		= '';
	const NONCE 					= 'aff_cm_coupons_admin_nonce';
	const SET_ADMIN_OPTIONS 		= 'set_admin_options';

	

	private static $admin_messages = array();

	/**
	 * Prints admin notices.
	 */
	public static function admin_notices() {
		if ( !empty( self::$admin_messages ) ) {
			foreach ( self::$admin_messages as $msg ) {
				echo $msg;
			}
		}
	}

	/**
	 * Checks dependencies and sets up actions and filters.
	 */
	public static function init() {

		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );

		$verified = true;
		$disable = false;
		$active_plugins = get_option( 'active_plugins', array() );
		$affiliates_is_active = in_array( 'affiliates-pro/affiliates-pro.php', $active_plugins ) || in_array( 'affiliates-enterprise/affiliates-enterprise.php', $active_plugins );
		$woocommerce_is_active = in_array( 'woocommerce/woocommerce.php', $active_plugins );		

		if ( !$affiliates_is_active ) {
			self::$admin_messages[] = "<div class='error'>" . __( 'The <strong>Affiliates Custom Method Coupon Rates</strong> plugin requires one of the Affiliates Pro or Affiliates Enterprise by <a href="http://www.itthinx.com" target="_blank">itthinx</a> to be installed and activated', 'affiliates-cm-coupon-rates' ) . "</div>";
		}		
		if ( !$woocommerce_is_active ) {
			self::$admin_messages[] = "<div class='error'>" . __( 'The <strong>Affiliates Custom Method Coupon Rates</strong> plugin requires <a href="http://www.woothemes.com/woocommerce" target="_blank">WooCommerce</a> plugin to be installed and activated.', 'affiliates-cm-coupon-rates' ) . "</div>";
		}
		if ( !$affiliates_is_active || !$woocommerce_is_active ) {
			if ( $disable ) {
				include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
				deactivate_plugins( array( __FILE__ ) );
			}
			$verified = false;
		}

		if ( $verified ) {
			add_action( 'affiliates_admin_menu', array( __CLASS__, 'affiliates_admin_menu' ) );
			include_once( 'class-affiliates-cr-method.php' );
		}
	}
	
	/**
	 * Adds a submenu item to the Affiliates menu for the Affiliates CM Coupon Rates options.
	 */
	public static function affiliates_admin_menu() {
		$page = add_submenu_page(
				'affiliates-admin',
				__( 'Affiliates CM Coupon Rates', 'affiliates-cm-coupon-rates' ),
				__( 'Affiliates CM Coupon Rates', 'affiliates-cm-coupon-rates' ),
				AFFILIATES_ADMINISTER_OPTIONS,
				'affiliates-cm-coupon-rates',
				array( __CLASS__, 'affiliates_admin_em_light' )
		);
		$pages[] = $page;
		add_action( 'admin_print_styles-' . $page, 'affiliates_admin_print_styles' );
		add_action( 'admin_print_scripts-' . $page, 'affiliates_admin_print_scripts' );
	}
	
	/**
	 * Affiliates CM Coupon Rates : admin section.
	 */
	public static function affiliates_admin_em_light() {
		$output = '';
		if ( !current_user_can( AFFILIATES_ADMINISTER_OPTIONS ) ) {
			wp_die( __( 'Access denied.', 'affiliates-cm-coupon-rates' ) );
		}
		$options = get_option( self::PLUGIN_OPTIONS , array() );
		if ( isset( $_POST['submit'] ) ) {
			if ( wp_verify_nonce( $_POST[self::NONCE], self::SET_ADMIN_OPTIONS ) ) {
				$options[self::REFERRAL_RATE]  = floatval( $_POST[self::REFERRAL_RATE] );
				if ( $options[self::REFERRAL_RATE] > 1.0 ) {
						$options[self::REFERRAL_RATE] = 1.0;
				} else if ( $options[self::REFERRAL_RATE] < 0 ) {
						$options[self::REFERRAL_RATE] = 0.0;
				}
				if ( strlen( $_POST[self::COUPON_PREFIX] ) > 3 ) {
					$options[self::COUPON_PREFIX] = substr( $_POST[self::COUPON_PREFIX], 0, 3 );
				} else {
					$options[self::COUPON_PREFIX] = strtolower( $_POST[self::COUPON_PREFIX] );
				}
				
			}
			update_option( self::PLUGIN_OPTIONS, $options );
		}
		
		$referral_rate = isset( $options[self::REFERRAL_RATE] ) ? $options[self::REFERRAL_RATE] : self::REFERRAL_RATE_DEFAULT;
		$coupon_prefix = isset( $options[self::COUPON_PREFIX] ) ? $options[self::COUPON_PREFIX] : self::COUPON_PREFIX_DEFAULT;
		
		$output .=
		'<div>' .
		'<h2>' .
		__( 'Affiliates CM Coupon Rates', 'affiliates-cm-coupon-rates' ) .
		'</h2>' .
		'</div>';
				
		
		$output .= '<div style="padding:1em 2em 1em 1em;margin-right:1em;">';
		$output .= '<form action="" name="options" method="post">';
		$output .= '<div>';
		
		$output .= '<p>';
		$output .= __( 'Set the referral rate that will be applied when a coupon is used.', 'affiliates-cm-coupon-rates' );
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label for="' . self::REFERRAL_RATE . '">' . __( 'Referral rate', 'affiliates-cm-coupon-rates') . '</label>';
		$output .= '&nbsp;';
		$output .= '<input name="' . self::REFERRAL_RATE . '" type="text" value="' . esc_attr( $referral_rate ) . '"/>';
		$output .= '</p>';
		
		$output .= '<p class="description">';
		$output .= __( 'Example: Set the referral rate to <strong>0.1</strong> if you want your affiliates to get a <strong>10%</strong> commission on each sale.', 'affiliates-cm-coupon-rates' );
		$output .= '</p>';
		
		$output .= '<hr>';
		
		$output .= '<p>';
		$output .= __( 'Type the coupon prefix which the referral rate will be applied for.', 'affiliates-cm-coupon-rates' );
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= '<label for="' . self::COUPON_PREFIX . '">' . __( 'Coupon prefix', 'affiliates-cm-coupon-rates') . '</label>';
		$output .= '&nbsp;';
		$output .= '<input name="' . self::COUPON_PREFIX . '" type="text" value="' . esc_attr( $coupon_prefix ) . '"/>';
		$output .= '</p>';
		
		$output .= '<p class="description">';
		$output .= __( 'Example: Set the coupon prefix to <strong>aff</strong> if you want your affiliates to get <strong>' . esc_attr( $referral_rate ) . '</strong> commission whenever a coupon code starting with <strong>aff</strong> is applied on checkout.', 'affiliates-cm-coupon-rates' );
		$output .= '</p>';
		
		$output .= '<p>';
		$output .= wp_nonce_field( self::SET_ADMIN_OPTIONS, self::NONCE, true, false );
		$output .= '<input class="button-primary" type="submit" name="submit" value="' . __( 'Save', 'affiliates-cm-coupon-rates' ) . '"/>';
		$output .= '</p>';
		
		$output .= '</div>';
		$output .= '</form>';
		$output .= '</div>';
		
		echo $output;
	}
	
} Affiliates_CM_Coupon_Rates::init();
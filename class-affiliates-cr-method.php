<?php
class Affiliates_CR_Method {
	public static function init() {
		if ( method_exists( 'Affiliates_Referral', 'register_referral_amount_method' ) ) {
			Affiliates_Referral::register_referral_amount_method( array( __CLASS__, 'coupon_rates' ) );
		}
	}

	/**
	 * Calculate referral amount
	 * based on the coupon preffix
	 *
	 * @param int $affiliate_id
	 * @param array $parameters
	 * @return float
	 */
	public static function coupon_rates( $affiliate_id = null, $parameters = null ) {
		$result = "0";
		
		if ( isset( $parameters['post_id'] ) ) {
			if ( class_exists( 'WC_Order' ) ) {
				$order = new WC_Order();
			} else {
				$order = new woocommerce_order();
			}
			if( isset( $parameters['base_amount'] ) ) {
				$order_total = floatval( $parameters['base_amount'] );
			} else {
				$order_total = $order->get_total(); 
			}
			if ( $order->get_order( $order_id ) ) {
				foreach( $order->get_used_coupons() as $coupon) {
					$coupon = strtolower( $coupon );
					if ( null != Affiliates_Attributes_WordPress::get_affiliate_for_coupon( $coupon ) ) {
						$affiliate_id = Affiliates_Attributes_WordPress::get_affiliate_for_coupon( $coupon );
						$options = get_option( Affiliates_CM_Coupon_Rates::PLUGIN_OPTIONS , array() );
						$referral_rate = isset( $options[Affiliates_CM_Coupon_Rates::REFERRAL_RATE] ) ? $options[Affiliates_CM_Coupon_Rates::REFERRAL_RATE] : Affiliates_CM_Coupon_Rates::REFERRAL_RATE_DEFAULT;
						$coupon_prefix = isset( $options[Affiliates_CM_Coupon_Rates::COUPON_PREFIX] ) ? $options[Affiliates_CM_Coupon_Rates::COUPON_PREFIX] : Affiliates_CM_Coupon_Rates::COUPON_PREFIX_DEFAULT;
						if ( strlen( $coupon ) > 3 ) {
							$coupon = substr( $coupon, 0, 3 );
						}
						if ( $coupon == $coupon_prefix ) {
							$result = floatval( $referral_rate ) * $order_total;
						}						
						break;
					}
				}
			}
		} 
		
		return $result;
	}
} add_action( 'init', array( 'Affiliates_CR_Method', 'init' ) );
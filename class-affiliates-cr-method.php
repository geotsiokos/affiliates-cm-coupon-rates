<?php
class Affiliates_CR_Method {
	public static function init() {
		if ( class_exists( 'Affiliates_Referral' ) ) {
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
			$result = self::calculate( intval( $parameters['post_id'] ), floatval( $parameters['base_amount'] ) );
		}		
		return $result;
	}
	
	/**
	 * Calculate helper function
	 * 
	 * @param string $order_id
	 * @param float $base_amount
	 * @return float
	 */
	public static function calculate( $order_id, $base_amount ) {
		$sum = '0';
		if ( function_exists( 'wc_get_order' ) ) {
			$order = wc_get_order( $order_id );		 
			if ( $order ) {
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
							$sum = floatval( $referral_rate ) * $base_amount;
						}						
						break;
					}
				}
			}
		}		
		return $sum;
	}
} add_action( 'init', array( 'Affiliates_CR_Method', 'init' ) );
<?php 
/*
Plugin Name: Woocommerce Free Gifts
Plugin URI: 
Description: Gives everyone free gives!
Version: 0.0.1
Author: Gerrg  
Author URI: http://gerrg.com
Text Domain: gerrg
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

add_action( 'wp', 'msp_programically_add_to_cart' );
function msp_programically_add_to_cart(){
	/**
	 * Looks for hard coded ID's in $targets array, check if ID meets product quantity and adds reward if so
	 */
	if( is_admin() ) return;

	// setup needles
	$targets = array( 54, 49 );

	// free gift ID
	$reward = 57;

	// key for quick searching in cart
	$reward_item_key = WC()->cart->find_product_in_cart( msp_get_product_cart_key( $reward ) );

	foreach( WC()->cart->get_cart() as $cart_item ){

		// get real ID
		$id = ( $cart_item['variation_id'] === 0 ) ? $cart_item['product_id'] : $cart_item['variation_id'];

		// loop needles
		foreach( $targets as $target ){

			// if needle in haystack, quantity over 20 && reward not in cart
			if( $target === $id && $cart_item['quantity'] >= 20 && ! $reward_item_key ){

				// Add reward
				WC()->cart->add_to_cart( $reward );
			}
		}
	}
}

add_action('woocommerce_cart_calculate_fees', 'msp_discount_free_gift', 100, 1 );

function msp_discount_free_gift( $cart ){
    /**
     * Look for free gift in cart, discount item if in cart.
     * @param WC_Cart
     */

    $targets = array( 54, 49 );
	$reward = 57;
	$product = wc_get_product( $reward );

	if( ! $product ) return;

	$reward_item_key = WC()->cart->find_product_in_cart( msp_get_product_cart_key( $reward ) );

	foreach( WC()->cart->get_cart() as $cart_item ){
		$id = ( $cart_item['variation_id'] === 0 ) ? $cart_item['product_id'] : $cart_item['variation_id'];
		foreach( $targets as $target ){
			if( $target === $id && $cart_item['quantity'] >= 20 && $reward_item_key ){
				// Meets all condititions? Add discount (fee).
				$cart->add_fee( 'Free Gift', ( $product->get_price() * -1), true  );
			}
		}
	}

}


function msp_get_product_cart_key( $product_id ){
	$product = wc_get_product( $product_id );

	if( ! $product ) return;

	return ( $product->is_type( 'variation' ) ) ? WC()->cart->generate_cart_id( $product->get_parent_id(), $product->get_id() ) : 
												  WC()->cart->generate_cart_id( $product->get_id() );
}



<?php 
/*
Plugin Name: Woocommerce Free Gifts
Plugin URI: http://gerrg.com/how-to-create-a-wordpress-plugin/
Description: Gives everyone free gifts!
Version: 0.0.5
Author: Gerrg  
Author URI: http://gerrg.com
Text Domain: gerrg
Domain Path: /languages
*/

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );


class Gerrg_Woocommerce_Free_Gifts{

	// The product ID's you wish to target
	protected $targets = array(
		558, 559, 560, 561
	);

	// the required target's quantity needed to earn the free gift. 
	protected $required_qty = 3;
	
	// Free gift
	protected $reward = 562;

	// setup hooks
	function __construct(){
		// needs to run on every page
		add_action( 'wp', array( $this, 'add_to_cart' ) );

		// this is where fees are calculated
		add_action('woocommerce_cart_calculate_fees', array( $this, 'discount_free_gift' ) );

	}

	private function get_product_cart_key( $product_id ){
		$product = wc_get_product( $product_id );
	
		if( ! $product ) return;
	
		return ( $product->is_type( 'variation' ) ) ? WC()->cart->generate_cart_id( $product->get_parent_id(), $product->get_id() ) : 
													  WC()->cart->generate_cart_id( $product->get_id() );
	}


	function add_to_cart(){
		/**
		 * Looks for hard coded ID's in $targets array, check if ID meets product quantity and adds reward if so
		 */

		if( is_admin() ) return;

		// key for quick searching in cart
		$reward_item_key = WC()->cart->find_product_in_cart( $this->get_product_cart_key( $this->reward ) );
	
		// loop cart
		foreach( WC()->cart->get_cart() as $cart_item ){
	
			// get real ID
			$id = ( $cart_item['variation_id'] === 0 ) ? $cart_item['product_id'] : $cart_item['variation_id'];
	
			// loop needles
			foreach( $this->targets as $target ){
	
				// if needle in haystack, quantity over 20 && reward not in cart
				if( $target === $id && $cart_item['quantity'] >= $this->required_qty && ! $reward_item_key ){
	
					// Add reward
					WC()->cart->add_to_cart( $this->reward );
				}
			}
		}
	}

	function discount_free_gift( $cart ){
		/**
		 * Look for free gift in cart, discount item if in cart.
		 * @param WC_Cart
		 */
	
		$product = wc_get_product( $this->reward );
	
		if( ! $product ) return;
		
		$reward_item_key = WC()->cart->find_product_in_cart( $this->get_product_cart_key( $this->reward ) );
	
		foreach( WC()->cart->get_cart() as $cart_item ){

			$id = ( $cart_item['variation_id'] === 0 ) ? $cart_item['product_id'] : $cart_item['variation_id'];

			foreach( $this->targets as $target ){

				if( $target === $id && $cart_item['quantity'] >= 20 && $reward_item_key ){

					// Meets all condititions? Add discount (fee).
					$cart->add_fee( 'Free Gift', ( $product->get_price() * -1), true  );
				}
			}
		}
	}
}


/**
 * Check if WooCommerce is active
 **/
if ( 
	in_array( 
	  'woocommerce/woocommerce.php', 
	  apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) 
	) 
  ) {
	  // Put your plugin code here
	  new Gerrg_Woocommerce_Free_Gifts();
  }

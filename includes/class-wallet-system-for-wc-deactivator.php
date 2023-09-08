<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 * @author     Rakesh <aryanbokde@gmail.com>
 */
class Wallet_System_For_Wc_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function deactivate() {
		$product_id = get_option( 'wswc_wallet_recharge_product_id');
		
		if ( ! empty( $product_id ) ) {
			delete_option( 'wswc_wallet_recharge_product_id' );
			wp_delete_post( $product_id, true );
		}
	}

}

<?php

/**
 * Fired during plugin activation
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 * @author     Rakesh <aryanbokde@gmail.com>
 */
require_once WALLET_SYSTEM_FOR_WC_DIR_PATH . 'includes/class-wallet-system-for-wc-helper.php';


class Wallet_System_For_Wc_Activator {

	
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public function activate() {
		
		$this->wswc_create_wallet_recharge_product_and_transaction_table();
		$this->wswc_create_qr_code_for_every_user();
	}

	// Function to create a wallet recharge product programmatically
	public function wswc_create_wallet_recharge_product_and_transaction_table() {

		// Check if WooCommerce is active
		if (!class_exists('WooCommerce')) {
			return;
		}

		// Check if the product already exists by its title
		$product_title = 'Wallet Recharge'; // Customize the product title as needed
		
		if ( ! wc_get_product( get_option( 'wswc_wallet_recharge_product_id' ) ) ) {
			// Load WooCommerce functions
			include_once(WC()->plugin_path() . '/includes/admin/wc-admin-functions.php');

			// Create the product
			$product = new WC_Product();

			$product->set_name($product_title);
			$product->set_status('draft'); 
			$product->set_regular_price(100.00); // Customize the recharge amount as needed
			$product->set_sku('wswc-wallet-recharge'); // Customize the SKU as needed
			$product->set_virtual(true); // Set as 'true' for a virtual product
			// $product->set_downloadable(true); // Set as 'true' for a downloadable product

			// Add a download file (optional for downloadable products)
			// $download_file_url = 'https://example.com/download/wallet-recharge-file.zip'; // Customize the download file URL as needed
			// $product->set_downloads(array(array(
			//     'name' => $product_title,
			//     'file' => $download_file_url,
			// )));

			$wallet_recharge_product_id = $product->save();
			update_option( 'wswc_wallet_recharge_product_id', $wallet_recharge_product_id );
		}


		// create custom table named wp-db-prefix_wps_wsfw_wallet_transaction.
		global $wpdb;
		$table_name = $wpdb->prefix . 'wswc_wallet_transaction';
		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) !== $table_name ) {
			$table_name   = $wpdb->prefix . 'wswc_wallet_transaction';
			$wpdb_collate = $wpdb->collate;
			$sql          = "CREATE TABLE IF NOT EXISTS {$table_name} (
				id bigint(20) unsigned NOT NULL auto_increment,
				user_id bigint(20) unsigned NULL,
				amount double,
				currency varchar( 20 ) NOT NULL,
				transaction_type varchar(200) NULL,
				transaction_type_1 varchar(200) NULL,
				payment_method varchar(50) NULL,
				transaction_id varchar(50) NULL,
				note varchar(500) Null,
				date datetime,
				PRIMARY KEY  (Id),
				KEY user_id (user_id)
				)
				COLLATE {$wpdb_collate}";

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		}


		// create product named as wallet topup.
		// if ( ! wc_get_product( get_option( 'wswc_rechargeable_product_id' ) ) ) {
		// 	$product = array(
		// 		'post_title'   => 'Rechargeable Wallet Product',
		// 		'post_content' => 'This is the custom wallet topup product.',
		// 		'post_type'    => 'product',
		// 		'post_status'  => 'private',
		// 		'post_author'  => 1,
		// 	);

		// 	$product_id = wp_insert_post( $product );
		// 	// update price and visibility of product.
		// 	if ( $product_id ) {
		// 		update_post_meta( $product_id, '_regular_price', 120 );
		// 		update_post_meta( $product_id, '_price', 100 );
		// 		update_post_meta( $product_id, '_visibility', 'hidden' );
		// 		update_post_meta( $product_id, '_virtual', 'yes' );

		// 		$productdata = wc_get_product( $product_id );
		// 		$productdata->set_catalog_visibility( 'hidden' );
		// 		$productdata->save();

		// 		update_option( 'wswc_rechargeable_product_id', $product_id );

		// 	}
		// }
	}

	// Generate QR code for every user when plugin activate
	public function wswc_create_qr_code_for_every_user(){
		
		$helper = new Wallet_System_For_Wc_Helper();

		ob_start();    
		// Retrieve users
		$users = get_users();
		foreach ($users as $user) {

			$user_id = $user->ID; // Replace 123 with the actual user ID        
			$user_meta = get_user_meta($user_id); // Get user all metadata
			$user_qr_url = get_user_meta($user_id, 'user_qr_url', true);// metadata by specifying

			// Check if the meta value is empty or null
			if ( empty($user_qr_url) && $user_qr_url == null ) {

				$user_qr_url = $helper->wallet_system_for_wc_generate_qrcode($user_id);
				$parts = explode('/', rtrim($user_qr_url, '/'));
				$qr_code_file_name = array_pop($parts);
				
				update_user_meta($user->ID, 'user_qr_url', $user_qr_url);
				update_user_meta($user->ID, 'user_qr_file_name', $qr_code_file_name);
				update_user_meta($user->ID, 'user_qr_wallet', 0);				
			}

		}    
		return ob_get_clean();
	}

	

}

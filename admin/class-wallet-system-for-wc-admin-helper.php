<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/admin
 * @author     Rakesh <aryanbokde@gmail.com>
 */
class Wallet_System_For_Wc_Admin_Helper {

	public function insert_transaction_data_in_table( $transactiondata ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'wswc_wallet_transaction';

		// Check if table exists.
		if ( $wpdb->get_var( 'show tables like "' . $wpdb->prefix . 'wswc_wallet_transaction"' ) != $table_name ) :

			// if not, create the table.
			$sql = 'CREATE TABLE ' . $table_name . ' (
            (...)
            ) ENGINE=InnoDB;';

			require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			dbDelta( $sql );
		else :

			$insert_array = array(
				'user_id'          => $transactiondata['user_id'],
				'amount'           => apply_filters( 'wps_wsfw_convert_to_base_price', $transactiondata['amount'] ),
				'currency'         => $transactiondata['currency'],
				'transaction_type' => $transactiondata['transaction_type'],
				'payment_method'   => $transactiondata['payment_method'],
				'transaction_id'   => $transactiondata['order_id'],
				'note'             => $transactiondata['note'],
				'date'             => gmdate( 'Y-m-d H:i:s' ),
				'transaction_type_1'   => $transactiondata['transaction_type_1'],
			);

			$results = $wpdb->insert(
				$table_name,
				$insert_array
			);
			$transaction_id = $wpdb->insert_id;
			if ( $results ) {
				return $transaction_id;
			} else {
				return false;
			}

		endif;
	}


	
	

	

}
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


 require_once WALLET_SYSTEM_FOR_WC_DIR_PATH . 'admin/class-wallet-system-for-wc-admin-helper.php';

class Wallet_System_For_Wc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallet_System_For_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallet_System_For_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wallet-system-for-wc-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wallet_System_For_Wc_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wallet_System_For_Wc_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wallet-system-for-wc-admin.js', array( 'jquery' ), $this->version, false );

	}

	// Function to display user meta fields
	public function wallet_system_for_wc_user_profile_fields($user) {
		if(current_user_can('edit_user_metadata')){
		?>
		<h3><?php _e('QR User', 'text-domain'); ?></h3>
		<?php 
		global  $woocommerce;
		$currency   = get_woocommerce_currency_symbol();
		$wallet_bal = get_user_meta( $user->ID, 'user_qr_wallet', true );
		?>
		<h2>
		<?php
		esc_html_e( 'Wallet Balance: ', 'wallet-system-for-woocommerce' );
		echo wp_kses_post( wc_price( $wallet_bal ) );
		?>
		</h2>
			<table class="form-table">
				<tr>
					<th><label for="user_qr_wallet"><?php esc_html_e( 'Amount', 'wallet-system-for-woocommerce' ); ?></label></th>
					<td>
						<input type="number" step="0.01" name="user_qr_wallet" id="user_qr_wallet">
						<span class="description"><?php esc_html_e( 'Add/deduct money to/from wallet', 'wswc' ); ?></span>
					<p class="error" ></p>
					</td>
				</tr>
				<tr>
					<th><label for="wswc_edit_wallet_action"><?php esc_html_e( 'Action', 'wallet-system-for-woocommerce' ); ?></label></th>
					<td>
						<select name="wswc_edit_wallet_action" id="wswc_edit_wallet_action">
							<option><?php esc_html_e( 'Select any', 'wallet-system-for-woocommerce' ); ?></option>
							<option value="credit"><?php esc_html_e( 'Credit', 'wallet-system-for-woocommerce' ); ?></option>
							<option value="debit"><?php esc_html_e( 'Debit', 'wallet-system-for-woocommerce' ); ?></option>
						</select>
						<span class="description"><?php esc_html_e( 'Whether want to add amount or deduct it from wallet', 'wallet-system-for-woocommerce' ); ?></span>
					</td>
				</tr>

				<?php 
					$nonce = wp_create_nonce('update_user_metadata');
					echo '<input type="hidden" name="update_user_nonce" value="' . esc_attr($nonce) . '" />';
				?>
				<!-- <tr>
					<th><label for="user_qr_code"><?php _e('QR Code', 'text-domain'); ?></label></th>
					<td>
						<?php 
							$upload_dir = wp_upload_dir();
							//$upload_basedir = $upload_dir['basedir']."/qrcode/";
							$upload_baseurl = $upload_dir['baseurl']."/qrcode/";
							
							$user_qr_file_url = $upload_baseurl . get_the_author_meta('user_qr_file_name', $user->ID);
						?>
						<img class="img user_qr_url" src="<?php //echo esc_attr($user_qr_file_url); ?>"
							style="width:100%; max-width:150px;" />
						<p class="description"><?php //_e('Unique Qr Code.', 'text-domain'); ?></p>
						<button type="button" class="button">Regenerate Wallet QR Code</button>

					</td>
				</tr>
				<tr>
					<th><label for="user_qr_wallet"><?php _e('Wallet Balance', 'text-domain'); ?></label></th>
					<td>
						<input type="number" class="regular-text" name="user_qr_wallet"
							value="<?php //echo esc_attr(get_the_author_meta('user_qr_wallet', $user->ID)); ?>"
							<?php //echo $variable = (current_user_can('edit_user_metadata')) ? "" : "readonly"; ?> />
						<p class="description"><?php // _e('Wallet Balance.', 'text-domain'); ?></p>
					</td>
				</tr> -->
			</table>
		<?php
		}
	}

	// Function to save user meta fields
	public function wallet_system_for_wc_save_user_field($user_id) {
		$currency  = get_woocommerce_currency();

		$admin_hepler = new Wallet_System_For_Wc_Admin_Helper();
		
		if ( current_user_can( 'edit_user', $user_id ) ) {
			$update        = true;
			if ( empty( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_wpnonce'] ) ), 'update-user_' . $user_id ) ) {
				return;
			}
			$wallet_amount = ( isset( $_POST['user_qr_wallet'] ) ) ? sanitize_text_field( wp_unslash( $_POST['user_qr_wallet'] ) ) : '';
			$action        = ( isset( $_POST['wswc_edit_wallet_action'] ) ) ? sanitize_text_field( wp_unslash( $_POST['wswc_edit_wallet_action'] ) ) : '';
			if ( empty( $action ) || 'Select any' === $action || empty( $wallet_amount ) ) {
				$update = false;
			}




			if ( $update ) {
				// $wallet_payment_gateway = new Wallet_System_For_Woocommerce();
				$wallet_balance = get_user_meta( $user_id, 'user_qr_wallet', true );
				
				$wallet_balance = ( ! empty( $wallet_balance ) ) ? $wallet_balance : 0;
				if ( 'credit' === $action ) {
					$wallet_balance = floatval( $wallet_balance ) + floatval( $wallet_amount );
					$transaction_type = esc_html__( 'Credited by admin', 'wallet-system-for-woocommerce' );
					$balance   = $currency . ' ' . $wallet_amount;
					$mail_message     = __( 'Merchant has credited your wallet by ', 'wallet-system-for-woocommerce' ) . esc_html( $balance );
					// if ( key_exists( 'wps_wswp_wallet_credit', WC()->mailer()->emails ) ) {
					// 	$customer_email = WC()->mailer()->emails['wps_wswp_wallet_credit'];
					// 	if ( ! empty( $customer_email ) ) {
					// 		$user       = get_user_by( 'id', $user_id );
					// 		$balance_mail = $currency . ' ' . $wallet_amount;
					// 		$user_name       = $user->first_name . ' ' . $user->last_name;
					// 		$customer_email->trigger( $user_id, $user_name, $balance_mail, '' );
					// 	}
					// }
				} elseif ( 'debit' === $action ) {
					if ( $wallet_balance < $wallet_amount ) {
						$wallet_balance = 0;
					} else {
						$wallet_balance = floatval( $wallet_balance ) - floatval( $wallet_amount );
					}
					$transaction_type = esc_html__( 'Debited by admin', 'wallet-system-for-woocommerce' );
					$balance   = $currency . ' ' . $wallet_amount;
					$mail_message     = __( 'Merchant has deducted ', 'wallet-system-for-woocommerce' ) . esc_html( $balance ) . __( ' from your wallet.', 'wallet-system-for-woocommerce' );

					// if ( key_exists( 'wps_wswp_wallet_debit', WC()->mailer()->emails ) ) {

					// 	$customer_email = WC()->mailer()->emails['wps_wswp_wallet_debit'];
					// 	if ( ! empty( $customer_email ) ) {
					// 		$user       = get_user_by( 'id', $user_id );
					// 		$currency  = get_woocommerce_currency();
					// 		$balance_mail = $currency . ' ' . $wallet_amount;
					// 		$user_name       = $user->first_name . ' ' . $user->last_name;
					// 		$customer_email->trigger( $user_id, $user_name, $balance_mail, '' );
					// 	}
					// }
				}
				update_user_meta( $user_id, 'user_qr_wallet', abs( $wallet_balance ) );

				$send_email_enable = get_option( 'wps_wsfw_enable_email_notification_for_wallet_update', '' );

				// if ( key_exists( 'wps_wswp_wallet_debit', WC()->mailer()->emails ) || key_exists( 'wps_wswp_wallet_credit', WC()->mailer()->emails ) ) {

				// 	$customer_email_credit = WC()->mailer()->emails['wps_wswp_wallet_credit'];
				// 	$customer_email_debit = WC()->mailer()->emails['wps_wswp_wallet_debit'];

				// 	if ( empty( $customer_email_credit ) || empty( $customer_email_debit ) ) {

				// 		if ( isset( $send_email_enable ) && 'on' === $send_email_enable ) {
				// 			$user       = get_user_by( 'id', $user_id );
				// 			$name       = $user->first_name . ' ' . $user->last_name;
				// 			$mail_text  = esc_html__( 'Hello ', 'wallet-system-for-woocommerce' ) . esc_html( $name ) . ",\r\n";
				// 			$mail_text .= $mail_message;
				// 			$to         = $user->user_email;
				// 			$from       = get_option( 'admin_email' );
				// 			$subject    = __( 'Wallet updating notification', 'wallet-system-for-woocommerce' );
				// 			$headers    = 'MIME-Version: 1.0' . "\r\n";
				// 			$headers   .= 'Content-Type: text/html;  charset=UTF-8' . "\r\n";
				// 			$headers   .= 'From: ' . $from . "\r\n" .
				// 				'Reply-To: ' . $to . "\r\n";

				// 			$wallet_payment_gateway->send_mail_on_wallet_updation( $to, $subject, $mail_text, $headers );
				// 		}
				// 	}
				// }
				$transaction_data = array(
					'user_id'          => $user_id,
					'amount'           => $wallet_amount,
					'currency'         => get_woocommerce_currency(),
					'payment_method'   => esc_html__( 'Manually By Admin', 'wallet-system-for-woocommerce' ),
					'transaction_type' => $transaction_type,
					'transaction_type_1' => $action,
					'order_id'         => '',
					'note'             => '',

				);

				$admin_hepler->insert_transaction_data_in_table( $transaction_data );
			}
		}




		// if (isset($_POST['update_user_nonce']) && wp_verify_nonce($_POST['update_user_nonce'], 'update_user_metadata') && current_user_can('edit_user_metadata') ) {
		   
		// 	$user_qr_wallet = (int)($_POST['user_qr_wallet']);    
		// 	if (!empty($user_qr_wallet)) {
		// 		update_user_meta($user_id, 'user_qr_wallet', $user_qr_wallet);
		// 	}
		// 	if (is_int($user_qr_wallet) && $user_qr_wallet <= 0) {
		// 		update_user_meta($user_id, 'user_qr_wallet', 0);
		// 	}

		// }
		
	}
	

	// Function to add custom column to user table 
	public function wallet_system_for_wc_user_column( $columns ) {
		$columns['wallet_balance'] = 'Wallet Balance';
		return $columns;
	}
	
	
	// Function to add custom column content to user table 
	public function wallet_system_for_wc_user_column_content( $value, $column_name, $user_id ) {
		if ( 'wallet_balance' === $column_name ) {
			$user_qr_wallet = get_user_meta($user_id, 'user_qr_wallet', true);        
			return $user_qr_wallet;
		}
		return $value;
	}
	
	// Add this code to your theme's functions.php or a custom plugin.
	
	public function check_payment_status_and_update_user_meta($order_id, $old_status, $new_status, $order) {

		$wallet_payment_gateway = new Wallet_System_For_Wc_Admin_Helper();
		$payment_method = $order->get_payment_method();
		$payment_method_title = $order->get_payment_method_title();
		$customer_id = $order->get_user_id();
		$product_id = get_option('wswc_wallet_recharge_product_id');
		$current_currency = apply_filters( 'wps_wsfw_get_current_currency', $order->get_currency() );

		// Check if the new order status is 'completed'.
		if ($new_status === 'completed') {



			// Initialize a flag to check if the SKU is found.
			$sku_found = false;
			$subtotal = 0;

			// Loop through the order items to check if the SKU exists.
			foreach ($order->get_items() as $item_id => $item) {
				// Get the product object for the item.
				$product = $item->get_product();
				$wallet_product = wc_get_product($product_id);
				
				// Check if the SKU of the product matches the one you want to find.
				if ( $product && $product->get_sku() === $wallet_product->get_sku() ) {

					$subtotal = $item->get_subtotal();				
					// Get the quantity for the item.
					$quantity = $item->get_quantity();

					// Add the details to the results array.
					$results[] = array(
						'product_name' => $product->get_name(),
						'subtotal' => $subtotal,
						'quantity' => $quantity,
					);

					$sku_found = true;
					break; // Exit the loop when the SKU is found.
				}
			}

			// If the SKU is found in the order, take action here.
			if ($sku_found) {
				// Perform actions like updating user meta or sending notifications.
				// For example, you can update a user's meta data:
				$user_id = $order->get_user_id();

				

				if ($user_id) {

					$user_wallet_balance = get_user_meta($user_id, 'user_qr_wallet', true);			
					$user_update_wallet = $user_wallet_balance + $subtotal;
					$update_wallet_balance = update_user_meta($user_id, 'user_qr_wallet', $user_update_wallet);
					
					if ($update_wallet_balance) {
						$transaction_type = __( $payment_method_title, 'wswc' ) . ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '" >#' . $order_id . '</a>';

						$transaction_data = array(
							'user_id'          => $customer_id,
							'amount'           => $subtotal,
							'currency'         => $current_currency,
							'payment_method'   => $payment_method_title,
							'transaction_type' => htmlentities( $transaction_type ),
							'transaction_type_1' => 'credit',
							'order_id'         => $order_id,
							'note'             => '',
						);
						$wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );

						
					}
					
					

				}
				// Or send a notification:
				// send_notification_to_admin('SKU found in order');
			}
			
			// if ($payment_method == 'wswc_wallet_payment_gateway') {
				
			// 	$check_product_id = false;
			// 	$wallet_recharge_amount = 0;

			// 	// Loop through the order items to check if the SKU exists.
			// 	foreach ($order->get_items() as $item_id => $item) {
			// 		// Get the product object for the item.
			// 		$product = $item->get_product();
			// 		$wallet_product = wc_get_product($product_id);
					
			// 		// Check if the SKU of the product matches the one you want to find.
			// 		if ( $product && $product->get_sku() === $wallet_product->get_sku() ) {

			// 			$wallet_recharge_amount = $item->get_subtotal();	
			// 			$check_product_id = true;
			// 			break; // Exit the loop when the SKU is found.
			// 		}
			// 	}

			// 	if ($check_product_id) {

			// 		$user_id = $order->get_user_id();
			// 		if ($user_id) {

			// 			$user_wallet_balance = get_user_meta($user_id, 'user_qr_wallet', true);
			// 			$user_update_wallet = $user_wallet_balance + $wallet_recharge_amount;
			// 			update_user_meta($user_id, 'user_qr_wallet', $user_update_wallet);

			// 			$transaction_type = __( $payment_method_title, 'wswc' ) . ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '" >#' . $order_id . '</a>';

			// 			$transaction_data = array(
			// 				'user_id'          => $customer_id,
			// 				'amount'           => $wallet_recharge_amount,
			// 				'currency'         => $current_currency,
			// 				'payment_method'   => $payment_method_title,
			// 				'transaction_type' => htmlentities( $transaction_type ),
			// 				'transaction_type_1' => 'credit',
			// 				'order_id'         => $order_id,
			// 				'note'             => '',
			// 			);
			// 			$wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );
			// 			}

				 	

			// 	}

			// }else{
			
				

			// }
			
		}
		// End Check if the new order status is 'completed'.
		
	}


}
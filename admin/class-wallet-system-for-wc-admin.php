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
			<table class="form-table">
				<?php 
					$nonce = wp_create_nonce('update_user_metadata');
					echo '<input type="hidden" name="update_user_nonce" value="' . esc_attr($nonce) . '" />';
				?>
				<tr>
					<th><label for="user_qr_code"><?php _e('QR Code', 'text-domain'); ?></label></th>
					<td>
						<?php 
							$upload_dir = wp_upload_dir();
							//$upload_basedir = $upload_dir['basedir']."/qrcode/";
							$upload_baseurl = $upload_dir['baseurl']."/qrcode/";
							
							$user_qr_file_url = $upload_baseurl . get_the_author_meta('user_qr_file_name', $user->ID);
						?>
						<img class="img user_qr_url" src="<?php echo esc_attr($user_qr_file_url); ?>"
							style="width:100%; max-width:150px;" />
						<p class="description"><?php _e('Unique Qr Code.', 'text-domain'); ?></p>
						<button type="button" class="button">Regenerate Wallet QR Code</button>

					</td>
				</tr>
				<tr>
					<th><label for="user_qr_wallet"><?php _e('Wallet Balance', 'text-domain'); ?></label></th>
					<td>
						<input type="number" class="regular-text" name="user_qr_wallet"
							value="<?php echo esc_attr(get_the_author_meta('user_qr_wallet', $user->ID)); ?>"
							<?php echo $variable = (current_user_can('edit_user_metadata')) ? "" : "readonly"; ?> />
						<p class="description"><?php _e('Wallet Balance.', 'text-domain'); ?></p>
					</td>
				</tr>
			</table>
		<?php
		}
	}

	// Function to save user meta fields
	public function wallet_system_for_wc_save_user_field($user_id) {

		if (isset($_POST['update_user_nonce']) && wp_verify_nonce($_POST['update_user_nonce'], 'update_user_metadata') && current_user_can('edit_user_metadata') ) {
		   
			$user_qr_wallet = (int)($_POST['user_qr_wallet']);    
			if (!empty($user_qr_wallet)) {
				update_user_meta($user_id, 'user_qr_wallet', $user_qr_wallet);
			}
			if (is_int($user_qr_wallet) && $user_qr_wallet <= 0) {
				update_user_meta($user_id, 'user_qr_wallet', 0);
			}
		}
		
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

		// Check if the new order status is 'completed'.
		if ($new_status === 'completed') {

			// Replace 'YOUR_SKU' with the SKU you want to check for.
			$product_id = get_option('wswc_wallet_recharge_product_id');
			$sku_to_check = 'wallet-recharge';

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
					update_user_meta($user_id, 'user_qr_wallet', $user_update_wallet);              
				}

				// Or send a notification:
				// send_notification_to_admin('SKU found in order');
			}
		}
		
	}


}
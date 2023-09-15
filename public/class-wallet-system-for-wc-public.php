<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/public
 * @author     Rakesh <aryanbokde@gmail.com>
 */
class Wallet_System_For_Wc_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wallet-system-for-wc-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		wp_enqueue_script('jquery');

		// Enqueue your custom JavaScript file
		wp_enqueue_script( 'wallet-script', WALLET_SYSTEM_FOR_WC_DIR_URL . 'public/js/wallet.js', array('jquery'), time(), true);

		// Pass the AJAX URL to the script
		wp_localize_script('wallet-script', 'ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
	

	}


	// Hook to create woocommerce account menu for wallet
	public function wallet_system_for_wc_account_menu_items($items) {

		if (!class_exists('WooCommerce')) {
			return;
		}
		$desired_key = "wallet";
		// Check if the desired key exists in the array
		if (is_array($items) && !array_key_exists($desired_key, $items)) {
	
			$new_items = array(
				'wallet' => 'Wallet', // Label for the custom item
			);
		
			// Merge the custom menu items after the "Dashboard" menu item
			$position = array_search('dashboard', array_keys($items));
			if ($position !== false) {
				$items = array_slice($items, 0, $position + 1, true) +
					$new_items +
					array_slice($items, $position + 1, null, true);
			} else {
				// If "Dashboard" is not found, simply add the custom item at the end
				$items = $items + $new_items;
			}
	
		}
		return $items;
	}


	// register permalink endpoint
	public function wallet_system_for_wc_add_endpoint() {
		add_rewrite_endpoint( 'wallet', EP_PAGES );
		flush_rewrite_rules();
	}

	// content for the new page in My Account, woocommerce_account_{ENDPOINT NAME}_endpoint
	public function wallet_system_for_wc_my_account_endpoint_content() { 
		if (is_user_logged_in()) {

			$user = wp_get_current_user();
			$user_id = $user->ID;
			$user_username = $user->user_login;
			$user_email = $user->user_email;
	
			$upload_dir = wp_upload_dir();
			//$upload_basedir = $upload_dir['basedir']."/qrcode/";
			$upload_baseurl = $upload_dir['baseurl']."/qrcode/";			
			$user_qr_file_url = $upload_baseurl . get_user_meta($user->ID, 'user_qr_file_name', true);			


			global  $woocommerce;
			$currency   = get_woocommerce_currency_symbol();
			$user_qr_wallet = get_user_meta( $user_id, 'user_qr_wallet', true );	


		?>
	
			<div class="custom-my-account">
				<p>Welcome to your wallet <strong><?php echo esc_html($user->display_name); ?>!</strong></p>
				
				<div class="wallet">
					<table class="table table-border">
						<thead>
							<tr>
								<th scope="col">Label</th>
								<th scope="col">Value</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td>Qr Code</td>
								<td>
									<p><img src="<?php echo $user_qr_file_url; ?>" class="img" style="width:100%; max-width:120px;"/></p>
									<button type="button" class="button">Regenerate Wallet QR Code</button>
									<button type="button" class="button" id="your-custom-button-id">Recharge Wallet</button>
								</td>
							</tr>
							<tr>
								<td>Wallet Balance</td>
							
								<td><p> <?php echo wp_kses_post( wc_price( $user_qr_wallet ) ); ?> </p></td>
							</tr>
						</tbody>
					</table>
				</div>
	
				<form id="custom-account-form" method="post">
					<h3>Transfer Wallet balance to Any User using user name</h3>
					
					<?php 
						$this->display_form_errors();
					?>
	
					<?php 
						if (isset($_GET['success']) && $_GET['success'] === '1') {
							echo '<div class="woocommerce-message success">Your wallet balance has been successfully transfered.</div>';
						}
					?>
					<div class="form-group mb-3">
						<label for="username">Username</label>                
						<input type="text" class="form-control" id="username" name="username" placeholder="Enter Username/Email">
					</div>
					<div class="form-group mb-3">
						<label for="wallet_amt">Wallet Amount</label>                
						<input type="number" class="form-control" id="wallet_amt" name="wallet_amt" placeholder="Wallet Amount">
					</div>
					<button type="submit" class="btn btn-primary">Submit</button>
				</form>
				
			</div>
		<?php
		} else {
			echo 'Please log in to access your account.';
		}
	
	}

    public function wallet_balance_transfer_form_validation() {
        $errors = array();
    
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            $current_user_id = get_current_user_id();
            $current_user_info = get_userdata($current_user_id);
            $current_user_login = $current_user_info->user_login;
            $current_user_email = $current_user_info->user_email;

            $current_user_wallet_amount = get_user_meta($current_user_id, 'user_qr_wallet', true);

            $username = $_POST['username'];
            $amt_to_transfer = $_POST['wallet_amt'];
           
           
            if ($username === $current_user_login) {
                $errors['username'] = "You can not transfer wallet balance to your account. " .$username;
            }
    
            if ($username === $current_user_email) {
                $errors['username'] = "You can not transfer wallet balance to your account. " .$username;
            }
    
            if (empty($username)) {
                $errors['username'] = "Username is required.";
            }
    
            if ( is_email( $username ) ) {
                $user = get_user_by('email', $username);
            } else {
                $user = get_user_by('login', $username);
            }
    
            if (empty($user)) {
                $errors['username'] = "Invalid username or email";
            }
            
            if (empty($amt_to_transfer) || !is_numeric($amt_to_transfer) || $amt_to_transfer < 0) {
                $errors['amount'] = "Please enter the valid wallet amount";
            }
    
            $current_user_wallet_amount = get_user_meta($current_user_id, 'user_qr_wallet', true);
            if ($amt_to_transfer <= $current_user_wallet_amount) {
                
            }else{
                $errors['amount'] = "Your tranferable amount greater then your wallet amount";
            }
    
            $user_id_to_tranfer_wallet_amount = $user->ID;
    
            
            if (empty($errors)) {
                $current_user_wallet_amount = get_user_meta($current_user_id, 'user_qr_wallet', true);
                $deposit_user_wallet_amount = get_user_meta($user_id_to_tranfer_wallet_amount, 'user_qr_wallet', true);
               
                $current_user_wallet_balance = $current_user_wallet_amount - $amt_to_transfer; 
				$beneficial_user_wallet_balance = $deposit_user_wallet_amount + $amt_to_transfer;
    
                update_user_meta($current_user_id, 'user_qr_wallet', $current_user_wallet_balance);
                update_user_meta($user_id_to_tranfer_wallet_amount, 'user_qr_wallet', $beneficial_user_wallet_balance);               
                
                $submission_success = true;
                wp_redirect(add_query_arg('success', $submission_success, wc_get_account_endpoint_url('wallet')));
                exit;   
    
            }else{
               
                session_start();
                $_SESSION['form_errors'] = $errors;
               
            }      
           
        }
    }
     
    public function display_form_errors() {
        session_start();
        if (isset($_SESSION['form_errors'])) {
            $errors = $_SESSION['form_errors'];
            foreach ($errors as $field => $message) {
                echo '<p class="error" style="color:red;">' . $message . '</p>';
            }
            unset($_SESSION['form_errors']);
        }
    }

	public function wswc_wallet_recharge() {
		// Check if the user is logged in.
		if (is_user_logged_in()) {
			// Get the current user's ID.
			$current_user_id = get_current_user_id();
			$current_user = wp_get_current_user();
			$billing_address = [
				'first_name' => $current_user->first_name,
				'last_name' => $current_user->last_name,
				'email' => $current_user->user_email,
				'phone' => $current_user->billing_phone,
				'address_1' => $current_user->billing_address_1,
				'address_2' => $current_user->billing_address_2,
				'city' => $current_user->billing_city,
				'state' => $current_user->billing_state,
				'postcode' => $current_user->billing_postcode,
				'country' => $current_user->billing_country,
			];
	
			// User is logged in, create a new WooCommerce order and add the wallet recharge product.
			$order = wc_create_order();			
			$product_id = get_option( 'wswc_wallet_recharge_product_id'); // Replace with the actual product ID for the wallet recharge product.
			$quantity = 1; // Adjust the quantity as needed.
			$order->add_product(wc_get_product($product_id), $quantity);
	
			// Calculate totals and save the order.
			$order->calculate_totals();
			$order->save();
	
			// Set billing details for the order.
			$order->set_address($billing_address, 'billing');
			// Associate the order with the current user.
			update_post_meta($order->get_id(), '_customer_user', $current_user_id);
	
			// Get the checkout URL for the new order.
			$checkout_url = $order->get_checkout_payment_url();
	
			// Return the checkout URL as JSON.
			wp_send_json(['checkout_url' => $checkout_url]);
		} else {
			// User is not logged in, return an error message.
			wp_send_json_error('User is not logged in.');
		}
	}


	/**
	 * Returns converted price of wallet balance.
	 *
	 * @param float $wallet_bal wallet balance.
	 * @return float
	 */
	public function wswc_show_converted_price( $wallet_bal ) {

		if ( class_exists( 'WOOCS' ) ) {
			global $WOOCS; // phpcs:ignore issues due to plugin compatibility.

			$amount = $WOOCS->woocs_exchange_value( $wallet_bal ); // phpcs:ignore issues due to plugin compatibility.

			return $amount;
		} else if ( function_exists( 'wmc_get_price' ) ) {

			$wallet_bal = wmc_get_price( $wallet_bal );

			return $wallet_bal;
		} else {
			return $wallet_bal;
		}

	}


	/**
	 * Convert the amount into base currency amount.
	 *
	 * @param string $price price.
	 * @return string
	 */
	public function wswc_convert_to_base_price( $price ) {

		$wps_sfw_active_plugins = get_option( 'active_plugins' );
		if ( in_array( 'woocommerce-currency-switcher/index.php', $wps_sfw_active_plugins ) ) {

			if ( class_exists( 'WOOCS' ) ) {
				global $WOOCS; // phpcs:ignore issues due to plugin compatibility.
				$amount = '';
				if ( $WOOCS->is_multiple_allowed ) { // phpcs:ignore issues due to plugin compatibility.
					 $currrent = $WOOCS->current_currency; // phpcs:ignore issues due to plugin compatibility.
					if ( $currrent != $WOOCS->default_currency ) { // phpcs:ignore issues due to plugin compatibility.
						$currencies = $WOOCS->get_currencies(); // phpcs:ignore issues due to plugin compatibility.
						$rate = $currencies[ $currrent ]['rate'];
						$amount = $price / ( $rate );
						return $amount;
					} else {
						return $price;
					}
				}
			}
		}

		if ( function_exists( 'wmc_revert_price' ) ) {

			$price = wmc_revert_price( $price );
			return $price;
		}

		return $price;
	}


	public function wps_wsfw_get_current_currency($current){
		$wps_sfw_active_plugins = get_option( 'active_plugins' );
		if ( in_array( 'woocommerce-currency-switcher/index.php', $wps_sfw_active_plugins ) ) {

			if ( class_exists( 'WOOCS' ) ) {
				global $WOOCS; // phpcs:ignore issues due to plugin compatibility.
			
				if ( $WOOCS->is_multiple_allowed ) { // phpcs:ignore issues due to plugin compatibility.
					 return $current = $WOOCS->current_currency; // phpcs:ignore issues due to plugin compatibility.
					
				}
			}
		}else{
			return $current;
		}
	}

	// Hook into WooCommerce checkout process
	






	
}



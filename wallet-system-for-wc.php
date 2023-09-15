<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/aryanbokde
 * @since             1.0.0
 * @package           Wallet_System_For_Wc
 *
 * @wordpress-plugin
 * Plugin Name:       Wallet System for Wc
 * Plugin URI:        https://github.com
 * Description:       Wallet System for WooCommerce is a digital wallet plugin where users can add or delete balances in bulk, give refunds and earn cashback.
 * Version:           1.0.0
 * Author:            Rakesh
 * Author URI:        https://github.com/aryanbokde
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wallet-system-for-wc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WALLET_SYSTEM_FOR_WC_VERSION', '1.0.0' );
define( 'WALLET_SYSTEM_FOR_WC_DIR_URL', plugin_dir_url( __FILE__ ) );
define( 'WALLET_SYSTEM_FOR_WC_DIR_PATH', plugin_dir_path( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-wallet-system-for-wc-activator.php
 */
function activate_wallet_system_for_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallet-system-for-wc-activator.php';
	$activator = new Wallet_System_For_Wc_Activator();
	$activator->activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-wallet-system-for-wc-deactivator.php
 */
function deactivate_wallet_system_for_wc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-wallet-system-for-wc-deactivator.php';
	$activator = new Wallet_System_For_Wc_Deactivator();
	$activator->deactivate();
}

register_activation_hook( __FILE__, 'activate_wallet_system_for_wc' );
register_deactivation_hook( __FILE__, 'deactivate_wallet_system_for_wc' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-wallet-system-for-wc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_wallet_system_for_wc() {

	$plugin = new Wallet_System_For_Wc();
	$plugin->run();

}
run_wallet_system_for_wc();



// Make sure WooCommerce is active.
// $active_plugins = get_option('active_plugins', array());
// //$active_plugins = (array) get_option( 'active_plugins', array() );


// if ( ! array_key_exists('woocommerce/woocommerce.php', $active_plugins) || ! in_array('woocommerce/woocommerce.php', $active_plugins)) {
// 	return;
// }


function wswc_wallet_payment_gateway_id($gateways){
	$customer_id = get_current_user_id();
	if ( $customer_id > 0 ) {
		$gateways[] = 'WC_Misha_Gateway';
	}
	return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wswc_wallet_payment_gateway_id');


/*
 * The class itself, please note that it is inside plugins_loaded action hook
 */
add_action( 'plugins_loaded', 'misha_init_gateway_class', 10, 1 );
function misha_init_gateway_class() {

	class WC_Misha_Gateway extends WC_Payment_Gateway {

 		/**
 		 * Class constructor, more about it in Step 3
 		 */
		  public function __construct() {

			$this->id = 'wswc_wallet_payment_gateway'; // payment gateway plugin ID
			$this->icon = ''; // URL of the icon that will be displayed on checkout page near your gateway name
			$this->has_fields = false;
			$this->method_title = __('Wallet Payment', 'wswc');
			$this->method_description = __('This payment method is used for user who want to make payment from their Wallet.', 'wswc');
		
			// gateways can support subscriptions, refunds, saved payment methods,
			// but in this tutorial we begin with simple payments
			$this->supports = array(
				'products'
			);
		
			// Method with all the options fields
			$this->init_form_fields();
		
			// Load the settings.
			//Load The Settings
			$this->init_form_fields();
			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');
			$this->instructions = $this->get_option('instructions', $this->description);
			$this->enabled = $this->get_option('enabled');
		
			// This action hook saves the settings
			//action hook to register option and save to database
			add_action('woocommerce_update_options_payment_gateways_'. $this->id, array($this, 'process_admin_options'));
			
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );


		 }
		

		/**
 		 * Plugin options, we deal with it in Step 3 too
 		 */
		public function init_form_fields(){

			$this->form_fields = array(
				'enabled' => array(
					'title'       => __( 'Enable/Disable', 'wswc' ),
					'label'       => __( 'Enable Wallet Payment', 'wswc' ),
					'type'        => 'checkbox',
					// 'description' => 'Enable to pay with wallet',
					'default'     => 'no'
				),
				'title' => array(
					'title' => __('Title', 'wswc'),
					'type'	=> 'safe_text',
					'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wswc'),
					'default'     => __( 'Wallet Payment', 'wswc' ),
					'desc_tip'    => true,
				),
				'description'  => array(
					'title'       => __( 'Description', 'wswc' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wswc' ),
					'default'     => __( 'Your amount will be deduct from your wallet.', 'wswc' ),
					'desc_tip'    => true,
				),
				'instructions'       => array(
					'title'       => __( 'Instructions', 'woocommerce' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page.', 'woocommerce' ),
					'default'     => __( 'Your amount is deducted from your wallet.', 'woocommerce' ),
					'desc_tip'    => true,
				)
				
			);
		}
	

		/**
		 * Current Wallet Balance.
		 */
		public function get_icon() {
			$customer_id = get_current_user_id();
			if ( $customer_id > 0 ) {
				$walletamount = get_user_meta( $customer_id, 'user_qr_wallet', true );
				$walletamount = empty( $walletamount ) ? 0 : $walletamount;
				$walletamount = apply_filters( 'wswc_show_converted_price', $walletamount );
				return '<b>' . __( '[Your Amount :', 'wswc' ) . ' ' . wc_price( $walletamount ) . ']</b>';
			}
		}

		/**
		 * Output for the order received page.
		 */
		public function thankyou_page() {
			if ( $this->instructions ) {
				$allowed_html = array(
					'p' => array(
						'class' => 'woocommerce-error',
					),
					
				);
				echo wp_kses( wpautop( wptexturize( $this->instructions ) ), $allowed_html );
			}
		}

		  /**
		   * Process a refund if supported.
		   *
		   * @param  int    $order_id Order ID.
		   * @param  float  $amount Refund amount.
		   * @param  string $reason Refund reason.
		   * @throws Exception Exception.
		   * @return bool|WP_Error
		   */
		// public function process_refund( $order_id, $amount = null, $reason = '' ) {
		// 	$order = wc_get_order( $order_id );
		// 	$refund_reason = $reason ? $reason : __( 'Wallet refund #', 'wallet-system-for-woocommerce' ) . $order->get_order_number();

		// 	if ( ! $transaction_id ) {
		// 		throw new Exception( __( 'Refund not credited to customer', 'wallet-system-for-woocommerce' ) );
		// 	}
		// 	do_action( 'wps_wallet_order_refund_actioned', $order, $amount, $transaction_id );
		// 	return true;
		// }

		/**
		 * Process the payment and return the result
		 *
		 * @param int $order_id order id.
		 * @return array
		 */
		public function process_payment( $order_id ) {

			$order       = wc_get_order( $order_id );
			$payment_method = $order->payment_method;
			if ( 'wswc_wallet_payment_gateway' === $payment_method ) {
				$payment_method = esc_html__( 'Wallet Payment', 'wswc' );
			}
			$order_total = $order->get_total();
			if ( $order_total < 0 ) {
				$order_total = 0;
			}
		
			
			$debited_amount   = apply_filters( 'wswc_convert_to_base_price', $order_total );
						
			//custom function to get currency
			$current_currency = apply_filters( 'wps_wsfw_get_current_currency', $order->get_currency() );

			
			$customer_id      = get_current_user_id();
			$is_auto_complete = get_option( 'wsfw_wallet_payment_order_status_checkout', '' );
			// $is_auto_complete = "on";
			$is_auto_complete_bool = true;
			$walletamount = get_user_meta( $customer_id, 'user_qr_wallet', true );
			$walletamount = empty( $walletamount ) ? 0 : $walletamount;
			if ( $debited_amount <= $walletamount ) {

				$wallet_payment_gateway = new Wallet_System_For_Wc_Admin_Helper();
			 	$walletamount          -= $debited_amount;
			 	$update_wallet          = update_user_meta( $customer_id, 'user_qr_wallet', abs( $walletamount ) );

			 	// if ( $update_wallet ) {
			 	// 	//$send_email_enable = get_option( 'wps_wsfw_enable_email_notification_for_wallet_update', '' );

			 	// 	$balance   = $current_currency . ' ' . $order_total;
				// 	if ( isset( $send_email_enable ) && 'on' === $send_email_enable ) {
				// 		$user       = get_user_by( 'id', $customer_id );
				// 		$name       = $user->first_name . ' ' . $user->last_name;
				// 		$mail_text  = esc_html__( 'Hello ', 'wallet-system-for-woocommerce' ) . esc_html( $name ) . ",\r\n";
				// 		$mail_text .= __( 'Wallet debited by ', 'wallet-system-for-woocommerce' ) . esc_html( $balance ) . __( ' from your wallet through purchasing.', 'wallet-system-for-woocommerce' );
				// 		$to         = $user->user_email;
				// 		$from       = get_option( 'admin_email' );
				// 		$subject    = __( 'Wallet updating notification', 'wallet-system-for-woocommerce' );
				// 		$headers    = 'MIME-Version: 1.0' . "\r\n";
				// 		$headers   .= 'Content-Type: text/html;  charset=UTF-8' . "\r\n";
				// 		$headers   .= 'From: ' . $from . "\r\n" .
				// 			'Reply-To: ' . $to . "\r\n";

				// 		if ( key_exists( 'wps_wswp_wallet_debit', WC()->mailer()->emails ) ) {

				// 			$customer_email = WC()->mailer()->emails['wps_wswp_wallet_debit'];
				// 			if ( ! empty( $customer_email ) ) {
				// 				$user       = get_user_by( 'id', $customer_id );
				// 				$currency  = get_woocommerce_currency();
				// 				$balance_mail = $balance;
				// 				$user_name       = $user->first_name . ' ' . $user->last_name;
				// 				$email_status = $customer_email->trigger( $customer_id, $user_name, $balance_mail, '' );
				// 			}
				// 		} else {

				// 			$wallet_payment_gateway->send_mail_on_wallet_updation( $to, $subject, $mail_text, $headers );
				// 		}
				// 	}
				// }

				$transaction_type = __( 'Wallet debited through purchasing ', 'wswc' ) . ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '" >#' . $order_id . '</a>';
				$transaction_data = array(
					'user_id'          => $customer_id,
					'amount'           => $order_total,
					'currency'         => $current_currency,
					'payment_method'   => $payment_method,
					'transaction_type' => htmlentities( $transaction_type ),
					'transaction_type_1' => 'debit',
					'order_id'         => $order_id,
					'note'             => '',
				);
				$wallet_payment_gateway->insert_transaction_data_in_table( $transaction_data );
				if ( isset( $is_auto_complete ) && 'on' == $is_auto_complete ) {

					// Mark as on-hold (we're awaiting the payment).
					$order->update_status( 'completed', __( 'Wallet payment completed', 'wallet-system-for-woocommerce' ) );
					// Reduce stock levels.
					$order->reduce_order_stock();
					// Remove cart.
					WC()->cart->empty_cart();
					$is_auto_complete_bool = false;

				}

				if ( $is_auto_complete_bool ) {
					// Mark as on-hold (we're awaiting the payment).
					$order->update_status( 'processing', __( 'Awaiting Wallet payment', 'wallet-system-for-woocommerce' ) );

					// Reduce stock levels.
					$order->reduce_order_stock();

					// Remove cart.
					WC()->cart->empty_cart();

				}
			} else {
				$order->update_status( 'failed', __( 'Do not have sufficient amount in wallet.', 'wallet-system-for-woocommerce' ) );

			}
			// Return thankyou redirect.
			return array(
				'result'   => 'success',
				'redirect' => $this->get_return_url( $order ),
			);
		}
 	}
}



//add_action('woocommerce_checkout_process', 'custom_checkout_validation');

function custom_checkout_validation() {
    // Define the product ID you want to check for
    $product_id_to_check = get_option('wswc_wallet_recharge_product_id'); // Replace with your product ID;

    // Get the chosen payment method ID from the $_POST data
    $chosen_payment_method = isset($_POST['payment_method']) ? sanitize_text_field($_POST['payment_method']) : '';

    // Check if the chosen payment method is the one you want to target
    if ($chosen_payment_method === 'wswc_wallet_payment_gateway') {
        // Check if the cart contains the specific product
        $cart_contains_product = false;

        foreach (WC()->cart->get_cart() as $cart_item) {
            if ($cart_item['product_id'] === $product_id_to_check) {
                $cart_contains_product = true;
                break; // Exit the loop if the product is found
            }
        }

        if ($cart_contains_product) {
            // If the product is not in the cart, show an error message
            wc_add_notice(__('You can not add the wallet product to your cart to use this payment method.', 'woocommerce'), 'error');
        }


    }
}




//add_filter('woocommerce_available_payment_gateways', 'disable_payment_method_for_specific_product');

function disable_payment_method_for_specific_product($available_gateways) {
    // Define the product ID you want to check for
    $product_id_to_check = get_option('wswc_wallet_recharge_product_id'); // Replace with your product ID

    // Check if the specific product is in the cart
    $product_in_cart = false;
    
    foreach (WC()->cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] == $product_id_to_check) {
            $product_in_cart = true;
            break;
        }
    }
    
    // Define the payment method(s) you want to disable
    $disabled_payment_methods = array('wswc_wallet_payment_gateway');

    // If the specific product is in the cart, disable the payment method(s)
    if ($product_in_cart) {
        foreach ($disabled_payment_methods as $method) {
            if (isset($available_gateways[$method])) {
                unset($available_gateways[$method]);
            }
        }
    }

    return $available_gateways;
}




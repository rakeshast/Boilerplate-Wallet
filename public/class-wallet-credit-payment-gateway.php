<?php 


if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Make sure WooCommerce is active.
$active_plugins = get_option('active_plugins', array());
//$active_plugins = (array) get_option( 'active_plugins', array() );

if ( ! array_key_exists('woocommerce/woocommerce.php', $active_plugins) || ! in_array('woocommerce/woocommerce.php', $active_plugins)) {
	return;
}

function wswc_wallet_payment_gateway_id($gateways){
	$customer_id = get_current_user_id();
	if ( $customer_id > 0 ) {
		$gateways[] = 'Wallet_Credit_Payment_Gateway';
	}
	return $gateways;
}
add_filter('woocommerce_payment_gateways', 'wswc_wallet_payment_gateway_id', 10, 1);


function wswc_wallet_payment_gateway_init(){

	class Wallet_Credit_Payment_gayeway extends WC_Payment_Gateway {
		public function __construct()
		{
			$this->id = 'wswc_wallet_payment_gateway';
			$this->icon = $this->get_icon();
			$this->has_fields = false;
			$this->method_title = __('Wallet Payment', 'wswc');
			$this->method_description = __('This payment method is used for user who want to make payment from their Wallet.', 'wswc');

			//Load The Settings
			$this->init_form_fields();
			$this->title = $this->get_option('title');
			$this->description = $this->get_option('description');
			$this->instructions = $this->get_option('instructions', $this->description);
			$this->enabled = $this->get_option('enabled');


			//action hook to register option and save to database
			add_action('woocommerce_update_options_payment_gateways_'. $this->id, array($this, 'process_admin_options'));
			add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );


		}

		public function init_form_fields(){
			$this->form_fileds = array(
				'enabled' => array(
					'title'   => __( 'Enable/Disable', 'wswc' ),
					'type'    => 'checkbox',
					'label'   => __( 'Enable Wallet Payment', 'wswc' ),
					'default' => 'yes',
				),
				'title' => array(
					'title' => __('Title', 'wswc'),
					'type'	=> 'text',
					'description' => __('This controls the title for the payment method the customer sees during checkout.', 'wswc'),
					'default'     => __( 'Wallet Payment', 'wswc' ),
					'desc_tip'    => true,
				),
				'description'  => array(
					'title'       => __( 'Description', 'wswc' ),
					'type'        => 'textarea',
					'description' => __( 'Payment method description that the customer will see on your checkout.', 'wswc' ),
					'default'     => __( 'Your amount is deducted from your wallet.', 'wswc' ),
					'desc_tip'    => true,
				),
				'instructions' => array(
					'title'       => __( 'Instructions', 'wswc' ),
					'type'        => 'textarea',
					'description' => __( 'Instructions that will be added to the thank you page and emails.', 'wswc' ),
					'default'     => '',
					'desc_tip'    => true,
				),
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
						'class' => '',
					),
				);
				echo wp_kses( wpautop( wptexturize( $this->instructions ) ), $allowed_html );
			}
		}

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
			$debited_amount   = apply_filters( 'wps_wsfw_convert_to_base_price', $order_total );
			$current_currency = apply_filters( 'wps_wsfw_get_current_currency', $order->get_currency() );
			$customer_id      = get_current_user_id();
			$is_auto_complete = get_option( 'wsfw_wallet_payment_order_status_checkout', '' );
			$is_auto_complete_bool = true;
				$walletamount = get_user_meta( $customer_id, 'wps_wallet', true );
				$walletamount = empty( $walletamount ) ? 0 : $walletamount;
			if ( $debited_amount <= $walletamount ) {

				$wallet_payment_gateway = new Wallet_System_For_Woocommerce();
				$walletamount          -= $debited_amount;
				$update_wallet          = update_user_meta( $customer_id, 'wps_wallet', abs( $walletamount ) );

				if ( $update_wallet ) {
					$send_email_enable = get_option( 'wps_wsfw_enable_email_notification_for_wallet_update', '' );
					$balance   = $current_currency . ' ' . $order_total;
					if ( isset( $send_email_enable ) && 'on' === $send_email_enable ) {
						$user       = get_user_by( 'id', $customer_id );
						$name       = $user->first_name . ' ' . $user->last_name;
						$mail_text  = esc_html__( 'Hello ', 'wallet-system-for-woocommerce' ) . esc_html( $name ) . ",\r\n";
						$mail_text .= __( 'Wallet debited by ', 'wallet-system-for-woocommerce' ) . esc_html( $balance ) . __( ' from your wallet through purchasing.', 'wallet-system-for-woocommerce' );
						$to         = $user->user_email;
						$from       = get_option( 'admin_email' );
						$subject    = __( 'Wallet updating notification', 'wallet-system-for-woocommerce' );
						$headers    = 'MIME-Version: 1.0' . "\r\n";
						$headers   .= 'Content-Type: text/html;  charset=UTF-8' . "\r\n";
						$headers   .= 'From: ' . $from . "\r\n" .
							'Reply-To: ' . $to . "\r\n";

						if ( key_exists( 'wps_wswp_wallet_debit', WC()->mailer()->emails ) ) {

							$customer_email = WC()->mailer()->emails['wps_wswp_wallet_debit'];
							if ( ! empty( $customer_email ) ) {
								$user       = get_user_by( 'id', $customer_id );
								$currency  = get_woocommerce_currency();
								$balance_mail = $balance;
								$user_name       = $user->first_name . ' ' . $user->last_name;
								$email_status = $customer_email->trigger( $customer_id, $user_name, $balance_mail, '' );
							}
						} else {

							$wallet_payment_gateway->send_mail_on_wallet_updation( $to, $subject, $mail_text, $headers );
						}
					}
				}

				$transaction_type = __( 'Wallet debited through purchasing ', 'wallet-system-for-woocommerce' ) . ' <a href="' . admin_url( 'post.php?post=' . $order_id . '&action=edit' ) . '" >#' . $order_id . '</a>';
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


add_action('plugins_loaded', 'wswc_wallet_payment_gateway_init');



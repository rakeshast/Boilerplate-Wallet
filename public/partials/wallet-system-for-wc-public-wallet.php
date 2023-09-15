<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/public/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->


<?php 
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

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
    <h3>Transaction History</h3>           
    <div class='content active'>
	<div class="wps-wallet-transaction-container">
		<table class="wps-wsfw-wallet-field-table " id="transactions_table">
			<thead>
				<tr>
					<th>#</th>
					<th><?php esc_html_e( 'Transaction Id', 'wallet-system-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Amount', 'wallet-system-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Details', 'wallet-system-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Method', 'wallet-system-for-woocommerce' ); ?></th>
					<th><?php esc_html_e( 'Date', 'wallet-system-for-woocommerce' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php
				global $wpdb;

				$table_name   = $wpdb->prefix . 'wswc_wallet_transaction';
				$transactions = $wpdb->get_results( $wpdb->prepare( 'SELECT * FROM ' . $wpdb->prefix . 'wswc_wallet_transaction WHERE user_id = %s ORDER BY `Id` DESC', $user_id ) );
				if ( ! empty( $transactions ) && is_array( $transactions ) ) {
					$i = 1;
					foreach ( $transactions as $transaction ) {
						$user           = get_user_by( 'id', $transaction->user_id );
						$transaction_id = $transaction->id;
						$tranasction_symbol = '';
						if ( 'credit' == $transaction->transaction_type_1 ) {
							$tranasction_symbol = '+';
						} elseif ( 'debit' == $transaction->transaction_type_1 ) {
							$tranasction_symbol = '-';
						}
						?>
						<tr>
							<td><?php echo esc_html( $i ); ?></td>
							<td>
							<?php
								$date = date_create( $transaction->date );
								echo esc_html( $date->getTimestamp() . $transaction->id );

							?>
							</td>
							<td class='wps_wallet_<?php echo esc_attr( $transaction->transaction_type_1 ); ?>' ><?php echo esc_html( $tranasction_symbol ) . wp_kses_post( wc_price( $transaction->amount, array( 'currency' => $transaction->currency ) ) ); ?></td>
							<td class="details" ><?php echo wp_kses_post( html_entity_decode( $transaction->transaction_type ) ); ?></td>
							<td>
							<?php
							$payment_methods = WC()->payment_gateways->payment_gateways();
							foreach ( $payment_methods as $key => $payment_method ) {
								if ( $key == $transaction->payment_method ) {
									$method = esc_html__( 'Online Payment', 'wallet-system-for-woocommerce' );
								} else {
									$method = $transaction->payment_method;
								}
								break;
							}
							echo esc_html( $method );
							?>
							</td>
							<td>
							<?php
							$date_format = get_option( 'date_format', 'm/d/Y' );
							$date        = date_create( $transaction->date );
							$wps_wsfw_time_zone = get_option( 'timezone_string' );
							if ( ! empty( $wps_wsfw_time_zone ) ) {
								$date = date_create( $transaction->date );
								echo esc_html( date_format( $date, $date_format ) );
								// extra code.( need validation if require).
								$date->setTimezone( new DateTimeZone( get_option( 'timezone_string' ) ) );
								// extra code.
								echo ' ' . esc_html( date_format( $date, 'H:i:s' ) );
							} else {

								$date_format = get_option( 'date_format', 'm/d/Y' );
								$date        = date_create( $transaction->date );
								echo esc_html( date_format( $date, $date_format ) );
								echo ' ' . esc_html( date_format( $date, 'H:i:s' ) );
							}
							?>
							</td>
						</tr>
						<?php
						$i++;
					}
				}

				?>
			</tbody>
		</table>
	</div>

	<?php
	// including regular expression jquery.
	wp_enqueue_script( 'anchor-tag', WALLET_SYSTEM_FOR_WOOCOMMERCE_DIR_URL . 'public/src/js/wallet-system-for-woocommerce-anchor.js', array(), $this->version, 'all' );
	?>

	<!-- removing the anchor tag href attibute using regular expression -->	
	<script>
	jQuery( "#transactions_table tr td" ).each(function( index ) {
		var details = jQuery( this ).html();
		var patt = new RegExp("<a");
		var res = patt.test(details);
		if ( res ) {
			jQuery(this).children('a').removeAttr("href");
		}
	});
	</script>
</div>  



<?php
} else {
    echo 'Please log in to access your account.';
}




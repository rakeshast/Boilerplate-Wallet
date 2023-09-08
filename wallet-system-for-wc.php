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

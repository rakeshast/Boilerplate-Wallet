<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/aryanbokde
 * @since      1.0.0
 *
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wallet_System_For_Wc
 * @subpackage Wallet_System_For_Wc/includes
 * @author     Rakesh <aryanbokde@gmail.com>
 */
class Wallet_System_For_Wc {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wallet_System_For_Wc_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WALLET_SYSTEM_FOR_WC_VERSION' ) ) {
			$this->version = WALLET_SYSTEM_FOR_WC_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wallet-system-for-wc';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_helper_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wallet_System_For_Wc_Loader. Orchestrates the hooks of the plugin.
	 * - Wallet_System_For_Wc_i18n. Defines internationalization functionality.
	 * - Wallet_System_For_Wc_Admin. Defines all hooks for the admin area.
	 * - Wallet_System_For_Wc_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallet-system-for-wc-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wallet-system-for-wc-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wallet-system-for-wc-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wallet-system-for-wc-public.php';

		//The class responsible for defining all common functions related to plugin.
		require_once WALLET_SYSTEM_FOR_WC_DIR_PATH . 'includes/class-wallet-system-for-wc-helper.php';

		$this->loader = new Wallet_System_For_Wc_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wallet_System_For_Wc_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wallet_System_For_Wc_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wallet_System_For_Wc_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		// Hook to display fields on user profile edit page
		$this->loader->add_action( 'show_user_profile', $plugin_admin, 'wallet_system_for_wc_user_profile_fields' );
		$this->loader->add_action( 'edit_user_profile', $plugin_admin, 'wallet_system_for_wc_user_profile_fields' );

		// Hook to display fields on user profile edit page and validation
		$this->loader->add_action( 'personal_options_update', $plugin_admin, 'wallet_system_for_wc_save_user_field' );
		$this->loader->add_action( 'edit_user_profile_update', $plugin_admin, 'wallet_system_for_wc_save_user_field' );

		//Display custom column for user 
		$this->loader->add_filter( 'manage_users_columns', $plugin_admin, 'wallet_system_for_wc_user_column' );
		$this->loader->add_action( 'manage_users_custom_column', $plugin_admin, 'wallet_system_for_wc_user_column_content', 10, 3 );
		
		//Update user wallet meta balance on order completed
		$this->loader->add_action( 'woocommerce_order_status_changed', $plugin_admin, 'check_payment_status_and_update_user_meta', 10, 4 );

		

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wallet_System_For_Wc_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		//add menu name parameter
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'wallet_system_for_wc_account_menu_items' );
		//menu init
		$this->loader->add_action( 'init', $plugin_public, 'wallet_system_for_wc_add_endpoint' );
		//menu content
		$this->loader->add_action( 'woocommerce_account_wallet_endpoint', $plugin_public, 'wallet_system_for_wc_my_account_endpoint_content' );
		//validation
		$this->loader->add_action( 'init', $plugin_public, 'wallet_balance_transfer_form_validation' );

		//wallet recharge hook 
		$this->loader->add_action( 'wp_ajax_wswc_wallet_recharge', $plugin_public, 'wswc_wallet_recharge' );
		$this->loader->add_action( 'wp_ajax_nopriv_wswc_wallet_recharge', $plugin_public, 'wswc_wallet_recharge' );



	}

	/**
	 * Register all of the hooks related to the helper functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_helper_hooks() {

		$plugin_helper = new Wallet_System_For_Wc_Helper( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'user_register', $plugin_helper, 'wallet_system_for_wc_user_registration_hook' );
		
		$this->loader->add_action( 'delete_user', $plugin_helper, 'wallet_system_for_wc_user_and_meta' );

		$this->loader->add_action( 'init', $plugin_helper, 'wallet_system_for_wc_custom_capabilities' );
		
		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wallet_System_For_Wc_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}

<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
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
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Sandbox_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'wp-sandbox';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_multisite_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WP_Sandbox_Loader. Orchestrates the hooks of the plugin.
	 * - WP_Sandbox_i18n. Defines internationalization functionality.
	 * - WP_Sandbox_Admin. Defines all hooks for the admin area.
	 * - WP_Sandbox_Public. Defines all hooks for the public side of the site.
	 * = WP_Sandbox_Database_Management. Manages the creation and destruction of database tables.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-sandbox-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-sandbox-public.php';

		/**
		 * The class responsible for managing the tables in the plugin
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-database-management.php';

		/**
		 * The class responsible for checking valid testing.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-check-valid-testing.php';

		/**
		 * The class responsible for access rules
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-rules.php';

		/**
		 * The class responsible for settings
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-settings.php';

		/**
		 * The class responsible for default settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-default-settings.php';

		/**
		 * The class responsible for managing subnets
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/access/class-wp-sandbox-subnet.php';

		/**
		 * The class responsible for managing authenticated users.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/access/class-wp-sandbox-authenticated-users.php';

		/**
		 * The class responsible for managing ip addresses
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/access/class-wp-sandbox-ip.php';

		/**
		 * The class responsible for managing ip address ranges
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/access/class-wp-sandbox-ip-range.php';

		/**
		 * The class responsible for the preview url
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/access/class-wp-sandbox-preview-url.php';

		/**
		 * The class responsible for the network admin pages
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-network-admin-pages.php';

		/**
		 * The class responsible for the admin pages
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-sandbox-admin-pages.php';

		$this->loader = new WP_Sandbox_Loader();
	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Sandbox_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new WP_Sandbox_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	private function define_multisite_hooks(){
		$networkAdminPages = new WP_Sandbox_Network_Admin_Pages();
		$defaultSettings = new WP_Sandbox_Default_Settings();

		$this->loader->add_action( 'network_admin_menu', $networkAdminPages, 'add_network_admin_menu_pages' );
		$this->loader->add_action( 'wpmu_new_blog', $defaultSettings, 'add_new_blog' );
		$this->loader->add_action( 'delete_blog', $defaultSettings, 'delete_blog' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$pluginAdmin 		= new WP_Sandbox_Admin( $this->get_plugin_name(), $this->get_version() );
		$accessRules		= new WP_Sandbox_Rules();
		$previewURL 		= new WP_Sandbox_Preview_URL();
		$authenticatedUsers = new WP_Sandbox_Authenticated_Users();
		$adminPages 		= new WP_Sandbox_Admin_Pages();
		$settings 			= new WP_Sandbox_Settings();

		$this->loader->add_action( 'admin_init', $authenticatedUsers, 'save_valid_login');

		$this->loader->add_action( 'admin_enqueue_scripts', $pluginAdmin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $pluginAdmin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $adminPages, 'add_admin_menu' );

		$this->loader->add_action( 'wp_ajax_wp_sandbox_add_rule', $accessRules, 'wp_sandbox_add_rule');
		$this->loader->add_action( 'wp_ajax_wp_sandbox_remove_rule', $accessRules, 'wp_sandbox_remove_rule');
		
		$this->loader->add_action( 'wp_ajax_wp_sandbox_regenerate_url', $previewURL, 'regenerate_preview_url');
		
		$this->loader->add_action( 'wp_ajax_wp_sandbox_save_settings', $settings, 'save_settings' );
		$this->loader->add_action( 'wp_ajax_wp_sandbox_enable_disable_blogs', $settings, 'enable_disable_blogs' );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$pluginPublic = new WP_Sandbox_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $pluginPublic, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $pluginPublic, 'enqueue_scripts' );
		$this->loader->add_action( 'wp_before_admin_bar_render', $pluginPublic, 'wp_before_admin_bar_render' );

		$checkValidTesting = new WP_Sandbox_Check_Valid_Testing();

		$this->loader->add_action( 'init', $checkValidTesting, 'check_valid_testing' );

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
	 * @return    Wp_Sandbox_Loader    Orchestrates the hooks of the plugin.
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

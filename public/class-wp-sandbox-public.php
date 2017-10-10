<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/public
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Public {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-sandbox-public.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-sandbox-public.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Puts a badge on the admin bar to show the user if Sandbox is activated or not.
	 *
	 * @since    1.0.0
	 */
	public function wp_before_admin_bar_render(){
		/*
			If the user is not a network admin, we add the badge.
		*/
		if( !is_network_admin() ){
			/*
				Gets the plugin status.
			*/
			$pluginStatus = WP_Sandbox_Settings::get_plugin_status();

			/*
				If the plugin is on, show the enabled badge
			*/
			if( $pluginStatus != '0' ){
				/*
					Get the global admin bar
				*/
				global $wp_admin_bar;

				/*
					Add the menu badge link that WP Sandbox is enabled
				*/
				$wp_admin_bar->add_menu( array(
					'parent' => false, 
					'id' => 'wp-sandbox-admin-bar-notification',
					'title' => __( 'WP Sandbox Enabled' ),
					'href' => admin_url( 'options-general.php?page=wp_sandbox_settings' ),
					'meta' => array( 'class' => 'ab-top-secondary wp-sandbox-admin-bar-enabled' )
				));
			}else{
				/*
					Get the global admin bar
				*/
				global $wp_admin_bar;

				/*
					Add the menu badge link that WP Sandbox is disabled
				*/
				$wp_admin_bar->add_menu( array(
					'parent' => false, 
					'id' => 'wp-sandbox-admin-bar-notification',
					'title' => __( 'WP Sandbox Disabled' ),
					'href' => admin_url( 'options-general.php?page=wp_sandbox_settings' ),
					'meta' => array( 'class' => 'ab-top-secondary wp-sandbox-admin-bar-disabled' )
				));
			}
		}
	}
}

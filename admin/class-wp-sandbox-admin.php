<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    Wp_Sandbox
 * @subpackage Wp_Sandbox/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/admin
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Admin {

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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-sandbox-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( 'colorpicker-styles', plugin_dir_url( __FILE__ ) . 'css/colorpicker.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-sandbox-admin.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( 'colorpicker-js', plugin_dir_url( __FILE__ ) . 'js/colorpicker.js', array('jquery'), $this->version, false );
		wp_enqueue_media();
	}
}

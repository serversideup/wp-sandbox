<?php
/*
	Plugin Name: WP Sandbox
	Plugin URI: https://521dimensions.com/open-source/wp-sandbox
	Description: Conveniently blocks out users during development without interfering with testing
	Author: 521 Dimensions
	Version: 1.0
	Author URI: https://521dimensions.com
*/


	/*------------------------------------------------
		Loads all of the required classes.
	------------------------------------------------*/
	require 'classes/class.wpsdatabasemanagement.php';
	require 'classes/class.wpsdefaultsettings.php';
	require 'classes/class.wpspreviewurl.php';
	require 'classes/class.wpssettings.php';
	require 'classes/class.wpsauthenticatedusers.php';
	require 'classes/class.wpsip.php';
	require 'classes/class.wpsiprange.php';
	require 'classes/class.wpssubnet.php';
	require 'classes/class.wpsadmindisplay.php';
	require 'classes/class.wpsupdate.php';

	/*
		Plugin version.
	*/
	global $wpsVersion;
	$wpsVersion = '1.0';

	class WPSandbox{
		/*
			Sets up the WPSandbox plugin to work within Wordpress
		*/
		public function __construct(){
			/*------------------------------------------------
				Plugin configuration functionality
			------------------------------------------------*/
			/* Activation and de-activation hooks */
			register_activation_hook(__FILE__, array( $this, 'wpsInstall' ) );
			register_deactivation_hook(__FILE__, array( $this, 'wpsUninstall' ) );

			/* Checks for update on plugins loaded */
			add_action( 'plugins_loaded', array( $this, 'wpsCheckUpdate' ) );

			/* Adds the necessary styles and scripts for the plugin to use */
			add_action('admin_init', array( $this, 'wpsEnqueueAdminScripts' ) );
			add_action('admin_init', array( $this, 'wpsEnqueueAdminStyles' ) );

			/* Front end styles for the admin bar */
			add_action('init', array( $this, 'wpsEnqueueStyles' ) );

			/* Configures the admin menu for the plugin */
			add_action( 'admin_menu', array ($this, 'wpsPluginSettings' ) );

			/*------------------------------------------------
				Plugin functionality actions
			------------------------------------------------*/
			/* Saves the admin user's IP if it's not already in the database */
			add_action( 'admin_init', array( $this, 'wpsSaveValidLogin' ) );

			/* Runs the core functionality of the plugin. */
			add_action( 'init', array( $this, 'wpsCheckValidTesting' ) );

			/* Adds the notification to the admin bar if the plugin is activated. */
			add_action( 'wp_before_admin_bar_render', array( $this, 'wpsAdminBarNotification' ) );

			/*------------------------------------------------
				Multisite specific functionality
			------------------------------------------------*/
			/* Configures the network admin menu for the plugin */
			add_action( 'network_admin_menu', array( $this, 'wpsNetworkPluginSettings' ) );

			/* Sets default settings for the new site creation */
			add_action( 'wpmu_new_blog', array( 'WPSDefaultSettings', 'addNewBlog' ) );

			/* Deletes settings for a blog that has been deleted */
			add_action( 'delete_blog', array( 'WPSDefaultSettings', 'deleteBlog' ) );

			/*------------------------------------------------
				Registers all the outlets for AJAX functions
			------------------------------------------------*/
			/* Action handler for adding a rule */
			add_action( 'wp_ajax_wps_add_rule', array( $this, 'wpsAddRule' ) );

			/* Action handler for removing rules */
			add_action( 'wp_ajax_wps_remove_rule', array( $this, 'wpsRemoveRule' ) );

			/* Action handler for saving admin settings */
			add_action( 'wp_ajax_wps_save_settings', array( 'WPSSettings', 'saveSettings' ) );

			/* Action handler for regenerating the preview url */
			add_action( 'wp_ajax_wps_regenerate_url', array( $this, 'wpsRegenerateURL' ) );

			/* MULTISITE SPECIFIC: Enables and disables selected blogs */
			add_action( 'wp_ajax_wps_enable_disable_blogs', array( 'WPSSettings', 'enableDisableBlogs' ) );
		}

		//-------------------------------------------------------//
		/* Plugin configuration functionality */

		/*------------------------------------------------
			Installs the plugin by building the necessary tables, 
			then adds admin to the database as a valid user.
		------------------------------------------------*/
		public function wpsInstall(){
			global $wpsVersion;

			$wpsInstalledVersion = get_option( "wp_sandbox_version" );

			if( $wpsInstalledVersion == '' ){

				$databaseManagement = new WPSDatabaseManagement();
				$defaultSettings = new WPSDefaultSettings();

				/*
					Multi-site tables contain blog_id field
					to specify settings particular to that blog
				*/
				if( is_multisite() ){
					$databaseManagement->buildTablesMultisite();
					$defaultSettings->setDefaultSettingsMultisite();
				}else{
					$databaseManagement->buildTables();
					$defaultSettings->setDefaultSettings();
				}

				/*
					Adds the option for the version for future
					user.
				*/
				add_option( 'wp_sandbox_version', $wpsVersion );
			}

			if( $wpsInstalledVersion != '' && $wpsInstalledVersion != $wpsVersion ){
				$wpsUpdate = new WPSUpdate();
				$wpsUpdate->determineUpdate( $wpsInstalledVersion, $wpsVersion );

				update_option( 'wp_sandbox_version', $wpsVersion );
			}

		}

		/*------------------------------------------------
			Uninstalls the plugin by removing the tables 
			created.
		------------------------------------------------*/
		public function wpsUninstall(){
			$databaseManagement = new WPSDatabaseManagement();
			$databaseManagement->destroyTables();
		}

		/*------------------------------------------------
			Checks for updates in the plugin.
		------------------------------------------------*/
		public function wpsCheckUpdate(){
			global $wpsVersion;

			$wpsInstalledVersion = get_option( "wp_sandbox_version" );

			if( $wpsInstalledVersion != '' && $wpsInstalledVersion != $wpsVersion ){
				$wpsUpdater = new WPSUpdate();
				$wpsUpdater->determineUpdate( $wpsInstalledVersion, $wpsVersion );

				update_option( 'wp_sandbox_version', $wpsVersion );
			}

		}

		//-------------------------------------------------------//
		/* SCRIPT AND STYLE SET UP FUNCTIONS */

		/*------------------------------------------------
			Sets up scripts for the admin backend used by 
			the plugin.
		------------------------------------------------*/
		public function wpsEnqueueAdminScripts(){
			wp_enqueue_script('wps-admin-scripts', plugins_url().'/wp-sandbox/js/wp-sandbox-admin-scripts.js', array('jquery'));
		}

		/*------------------------------------------------
			Sets up styles for the admin backend used by the plugin.
		------------------------------------------------*/
		public function wpsEnqueueAdminStyles(){
			wp_enqueue_style('wps-admin-styles', plugins_url().'/wp-sandbox/css/wp-sandbox-admin-styles.css');
		}

		/*------------------------------------------------
			Sets up styles for the front end (admin bar only).
		------------------------------------------------*/
		public function wpsEnqueueStyles(){
			wp_enqueue_style('wps-styles', plugins_url().'/wp-sandbox/css/wp-sandbox-styles.css');
		}

		/*------------------------------------------------
			Sets the plugin status on the admin bar
		------------------------------------------------*/
		public function wpsAdminBarNotification() {
			if( !is_network_admin() ){
				$pluginStatus = WPSSettings::getPluginStatus();

				if( $pluginStatus != '0' ){
					global $wp_admin_bar;

					$wp_admin_bar->add_menu( array(
						'parent' => false, 
						'id' => 'wp-sandbox-admin-bar-notification',
						'title' => __( 'WP Sandbox Enabled' ),
						'href' => admin_url( 'admin.php?page=wp_sandbox' ),
						'meta' => array( 'class' => 'ab-top-secondary wps-admin-bar-enabled' )
					));
				}else{
					global $wp_admin_bar;

					$wp_admin_bar->add_menu( array(
						'parent' => false, 
						'id' => 'wp-sandbox-admin-bar-notification',
						'title' => __( 'WP Sandbox Disabled' ),
						'href' => admin_url( 'admin.php?page=wp_sandbox' ),
						'meta' => array( 'class' => 'ab-top-secondary wps-admin-bar-disabled' )
					));
				}
			}
		}

		//-------------------------------------------------------//
		/* ADMIN MENU SET UP */
		
		/*------------------------------------------------
			Adds the plugin administration menu to the admin backend under Settings->WP Sandbox
		------------------------------------------------*/
		public function wpsPluginSettings() {
			add_menu_page('WP Sandbox', 'WP Sandbox', 'manage_options', 'wp_sandbox', array( $this, 'wpsSettingsPage' ), plugins_url().'/wp-sandbox/images/wp-sandbox-logo.png');
			add_submenu_page('wp_sandbox', 'Access', 'Access', 'manage_options', 'wps_access', array ( $this, 'wpsAccessPage' ) );
		}

		/*------------------------------------------------
			Displays the sandbox settings page
		------------------------------------------------*/
		public function wpsSettingsPage(){
			$settings = WPSSettings::getAllSettings();

			/*
				Define setting placeholders.
			*/
			$defaultPage 			= '';
			$defaultExpirationTime 	= '';
			$previewHash 			= '';
			$enabled 				= '';

			/*
				Define settings. We have to loop through and grab the
				actual settings from the array since there is no explicit
				connection between the database columns and setting value.
			*/
			foreach( $settings as $setting ){
				switch( $setting['setting_name'] ){
					case 'Default Page':
						$defaultPage = $setting['setting_value'];
					break;
					case 'Default Expiration Time':
						$defaultExpirationTime = $setting['setting_value'];
					break;
					case 'Preview Hash':
						$previewHash = $setting['setting_value'];
					break;
					case 'Enabled':
						$enabled = $setting['setting_value'];
					break;
				}
			}

			/*
				Get all of the site's pages for the default page 
				setting.
			*/
			$pages = get_pages();

			$version = get_option( "wp_sandbox_version" );

			WPSAdminDisplay::displaySingleSiteSettingsScreen( $version, $defaultPage, $defaultExpirationTime, $previewHash, $enabled, $pages );
		}

		/*------------------------------------------------
			Displays the sandbox access page
		------------------------------------------------*/
		public function wpsAccessPage(){
			$previewHash 			= WPSPreviewURL::getPreviewHash();
			$defaultExpirationTime 	= WPSSettings::getDefaultExpirationTime();
			
			$authenticatedUsers 	= WPSAuthenticatedUsers::getAuthenticatedUsers();
			$ips 					= WPSIP::getIPs();
			$ipRanges 				= WPSIPRange::getIPRanges();
			$subnets 				= WPSSubnet::getSubnets();
			
			$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

			$version = get_option( "wp_sandbox_version" );

			/* 
				Displays the single site access screen.
			*/
			WPSAdminDisplay::displaySingleSiteAccessScreen( $version, $previewURL, $defaultExpirationTime, $authenticatedUsers, $ips, $ipRanges, $subnets );
		}
		
		//-------------------------------------------------------//
		/* PLUGIN FUNCTIONALITY */

		/*------------------------------------------------
			AJAX Action: wp_ajax_wps_add_rule
			Organizes and adds a rule to the database.
		------------------------------------------------*/
		public function wpsAddRule(){
			/*
				Defines the variables to pass to the function
				that would add to the database.
			*/
			$type 			= $_POST['type'];
			$rule 			= $_POST['rule'];
			$expiration 	= $this->wpsGetExpirationTime( $_POST['expiration'] );

			$user 			= get_userdata( get_current_user_id() );

			/*
				Determines type and adds the
				rule.
			*/
			switch( $type ){
				case 'single':
					$ruleID = WPSIP::addIP( $rule, $expiration, $user->ID );
				break;
				case 'range':
					$ipParts = explode( '-', $rule );

					$ruleID = WPSIPRange::addRange( $ipParts[0], $ipParts[1], $expiration, $user->ID );
				break;
				case 'subnet':
					$subnetParts = explode( '/', $rule );

					$ruleID = WPSSubnet::addSubnet( $subnetParts[0], $subnetParts[1], $expiration, $user->ID );
				break;
			}

			$typeDisplay 	= $this->wpsGetTypeDisplay( $type );
			/*
				Returns the new information so it
				can be added to the table.
			*/
			wp_send_json( array( 
				'type' => $typeDisplay, 
				'rule' => $rule, 
				'expiration' => $expiration != '' ? date('m-d-Y H:i:s', strtotime( $expiration ) ) : 'Never', 
				'added_by' =>  $user->user_login,
				'rule_id' => $ruleID ) );

			die();
		}

		/*------------------------------------------------
			AJAX Action: wp_ajax_wps_remove_rule
			Removes a rule from the database.
		------------------------------------------------*/
		public function wpsRemoveRule(){
			$type 		= $_POST['type'];
			$ruleID 	= $_POST['rule'];

			/*
				Calls appropriate remove function for
				the type of rule.
			*/
			switch( $type ){
				case 'user':
					WPSAuthenticatedUsers::deleteAuthenticatedUser( $ruleID );
				break;
				case 'single':
					WPSIP::deleteIP( $ruleID );
				break;
				case 'range':
					WPSIPRange::deleteRange( $ruleID );
				break;
				case 'subnet':
					WPSSubnet::deleteSubnet( $ruleID );
				break;
			}

			wp_send_json( array(
				'success' => true
			) );
		}

		/*------------------------------------------------
			AJAX Action: wps_regenerate_url
			Regenerates the preview URL
		------------------------------------------------*/
		public function wpsRegenerateURL(){
			$previewHash = WPSPreviewURL::createNewPreviewURL();

			$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

			wp_send_json( array(
				'preview_url' => $previewURL
			) );
		}

		/*------------------------------------------------
			Saves the valid login to the database.  
			The default expiration time is also added
			for the user.
		------------------------------------------------*/
		public function wpsSaveValidLogin(){
			global $wpdb;
			/*
				Checks if plugin is enabled
			*/
			$pluginStatus =  WPSSettings::getPluginStatus();

			if( $pluginStatus == '1' ){
				/*
					Checks if the user is
					logged in.
				*/
				if( is_user_logged_in() ){

					global $current_user;
					get_currentuserinfo();

					$userID = $current_user->ID;
					
					/* 
						Gets the IP for the user 
					*/
					$ip = $this->wpsGetIP();

					/*
						If the IP is not in any ranges or networks or
						not individually added anywhere, then we add
						it because the user has authentication rights.
					*/
					if( !WPSAuthenticatedUsers::checkValidIP( $ip ) && !WPSIP::checkValidIP( $ip )  && !WPSIPRange::checkIPValidRange( $ip ) && !WPSSubnet::checkIPSubnet( $ip ) ){
						$defaultExpirationTime = WPSSettings::getDefaultExpirationTime();

						$expirationTime = $this->wpsGetExpirationTime( $defaultExpirationTime );

						WPSAuthenticatedUsers::addAuthenticatedUser( $userID, $ip, $expirationTime );
					}
				}
			}
		}

		/*------------------------------------------------
			Checks to see if the front end user is capable 
			of viewing the site. The core function of the
			plugin. 
			
			@return bool if valid and builds the coming
			soon page if not valid.
		------------------------------------------------*/
		public function wpsCheckValidTesting(){
			global $wpdb;
			/*
				Removes expired rules before
				allowing access.
			*/
			$this->wpsRemoveExpiredRules();

			/*
				If the user is logged in, they can view the site.
			*/
			if( !is_user_logged_in() ){
				/*
					If the user is at the login, the login page will display.
				*/
				if( !$this->wpsCheckIfLogInPage() ){
					/*
						Checks if plugin is enabled
					*/
					$pluginStatus =  WPSSettings::getPluginStatus();

					if( $pluginStatus == '1' ){
						
						/*
							Gets the IP we will be testing.
						*/
						$ip = $this->wpsGetIP();

						/*
							Checks valid preview URL
						*/
						if( WPSPreviewURL::checkValidPreviewURL() ){
							return true;
						}

						/*
							Checks if valid cookie
						*/
						if( WPSPreviewURL::checkValidCookie() ){
							return true;
						}

						/*
							Check if the IP is valid
							for an authenticated user.
						*/
						if( WPSAuthenticatedUsers::checkValidIP( $ip ) ){
							return true;
						}

						/*
							Check if the IP is valid
						*/
						if( WPSIP::checkValidIP( $ip ) ){
							return true;
						}

						/*
							Check if the IP is in a valid range
						*/
						if( WPSIPRange::checkIPValidRange( $ip ) ){
							return true;
						}

						/*
							Check if the IP is in a subnet
						*/
						if( WPSSubnet::checkIPSubnet( $ip ) ){
							return true;
						}

						/*
							Only gets here if nothing is
							returned and displays the coming
							soon page from settings defined.
							It is either a page or blank screen.
						*/
						$this->wpsDisplayComingSoon();
					}
				}
			}
		}

		//-------------------------------------------------------//
		/* HELPER FUNCTIONS */

		/*------------------------------------------------
			Returns the expiration time based on the
			length of time specified by the user.
		------------------------------------------------*/
		private function wpsGetExpirationTime( $expiration ){
			if( get_option('timezone_string') != '' ){
				date_default_timezone_set( get_option('timezone_string') );
			}
			
			switch( $expiration ){
				case 'day':
					return date('Y-m-d G:i:s', time() + '86400');
				break;
				case 'week':
					return date('Y-m-d G:i:s', time() + '604800');
				break;
				case 'twoweeks':
					return date('Y-m-d G:i:s', time() + '1209600');
				break;	
				case 'month':
					return date('Y-m-d G:i:s', time() + '2592000');
				break;
				case 'never':
					return '';
				break;
			}
		}

		/*------------------------------------------------
			Returns the pretty display for the addition
			of a new rule.
		------------------------------------------------*/
		private function wpsGetTypeDisplay( $type ){
			switch( $type ){
				case 'single':
					return 'Single IP';
				break;
				case 'range':
					return 'IP Range';
				break;
				case 'subnet':
					return 'Network';
				break;
			}
		}

		/*------------------------------------------------
			Checks if the page is a log in page on Wordpress. 
			Allows front-end users to log in.
			@return bool
		------------------------------------------------*/
		private function wpsCheckIfLogInPage(){
			if( is_admin() ){
				return true;
			}else{
    			return in_array( $GLOBALS['pagenow'], array( 'wp-login.php', 'wp-register.php' ) );
    		}
		}

		/*------------------------------------------------
			Checks the database for expired rules.  If the rule in the database has an expiration
			date before the current time it is removed.

			The exception being when the date is == '0000-00-00 00:00:00' that means the rule 
			never expires.
		------------------------------------------------*/
		private function wpsRemoveExpiredRules(){
			global $wpdb;

			if( is_multisite() ){
				$currentBlogID = get_current_blog_id();

				/*
					Gets expired users
				*/
				$users = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_coming_soon
					 WHERE expires < CURDATE()
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );
				
				/*
					Gets expired IPs
				*/
				$ips = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_ips 
					 WHERE expires < CURDATE() 
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				/*
					Gets expired IP Ranges
				*/
				$ipRanges = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_ip_ranges
					 WHERE expires < CURDATE()
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );

				/*
					Gets expired Subnets
				*/
				$subnets = $wpdb->get_results( $wpdb->prepare(
					"SELECT *
					 FROM ".$wpdb->prefix."wps_subnets
					 WHERE expires < CURDATE()
					 AND blog_id = '%d'",
					 $currentBlogID
				), ARRAY_A );
			}else{
				/*
					Gets expired users
				*/
				$users = $wpdb->get_results(
					"SELECT *
					 FROM ".$wpdb->prefix."wps_coming_soon
					 WHERE expires < CURDATE()",
					 ARRAY_A );

				/*
					Gets expired IPs
				*/
				$ips = $wpdb->get_results(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_ips 
					 WHERE expires < CURDATE()",
					 ARRAY_A );

				/*
					Gets expired IP Ranges
				*/
				$ipRanges = $wpdb->get_results(
					"SELECT * 
					 FROM ".$wpdb->prefix."wps_ip_ranges
					 WHERE expires < CURDATE()",
					 ARRAY_A );

				/*
					Gets expired Subnets
				*/
				$subnets = $wpdb->get_results(
					"SELECT *
					 FROM ".$wpdb->prefix."wps_subnets
					 WHERE expires < CURDATE()",
				 	 ARRAY_A );
			}
			
			/*
				Removes expired users
			*/
			foreach( $users as $user ){
				if( $user['expires'] != '0000-00-00 00:00:00' ){
					WPSAuthenticatedUsers::deleteAuthenticatedUser( $user['id'] );
				}
			}

			/*
				Removes expired IPs
			*/
			foreach( $ips as $ip ){
				if( $ip['expires'] != '0000-00-00 00:00:00' ){
					WPSIP::deleteIP( $ip['id'] );
				}
			}
			
			/*
				Removes expired IP Ranges
			*/
			foreach( $ipRanges as $ipRange ){
				if( $ipRange['expires'] != '0000-00-00 00:00:00' ){
					WPSIPRange::deleteRange( $ipRange['id'] );
				}
			}

			/*
				Removes expired subnets
			*/
			foreach( $subnets as $subnet ){
				if( $subnet['expires'] != '0000-00-00 00:00:00' ){
					WPSSubnet::deleteSubnet( $subnet['id'] );
				}
			}
		}

		/*------------------------------------------------
			Gets the IP address to be tested
		------------------------------------------------*/
		private function wpsGetIP(){
			/*
				Checks for a CloudFlare IP first.
			*/
			if( isset( $_SERVER['HTTP_CF_CONNECTING_IP'] ) && $_SERVER['HTTP_CF_CONNECTING_IP'] != '' ){
				return $_SERVER['HTTP_CF_CONNECTING_IP'];
			}

			/*
				Returns the IP address of the user.
			*/
			return $_SERVER['REMOTE_ADDR'];
		}

		/*------------------------------------------------
			Displays the coming soon page of the Admin's 
			choice.
		------------------------------------------------*/
		private function wpsDisplayComingSoon(){
			global $wpdb;

			$defaultPage = WPSSettings::getDefaultPage();

			/*
				If 404 is the default, search for the 404 template.  
				If there is no template, throw a 404 error.
			*/
			if( $defaultPage == '404' ){
				header("HTTP/1.0 404 Not Found - Archive Empty");
				$locate_template = locate_template( '404.php' );

				if ( !empty( $locate_template ) ){
					require TEMPLATEPATH.'/404.php';
				}

				exit;
			}

			/*
				If the setting value is blank, throw a blank page.
			*/
			if( $defaultPage == 'blank'){
				header("HTTP/1.0 404 Not Found - Archive Empty");
				exit;
			}

			/*
				If a page is set, display the page, and throw a 'wps' 
				parameter in the GET so there are no infinite redirects.
			*/
			if( !isset($_GET['wps'] ) || $_GET['wps'] != 'true' ){
				$url = $checkDefaultWPSPage[0]['setting_value'].'?wps=true';
				wp_redirect( $url );
				exit;
			}
		}

		//-------------------------------------------------------//
		/* MULTISITE FUNCTIONALITY */

		/*------------------------------------------------
			Function to add WP Sandbox management screen
			specifically to the network management
			screen.
		------------------------------------------------*/
		public function wpsNetworkPluginSettings(){
			add_menu_page( 'WP Sandbox', 'WP Sandbox', 'manage_network', 'wp_sandbox', array( $this, 'wpsNetworkMenu' ), plugins_url().'/wp-sandbox/images/wp-sandbox-logo.png' );
		}

		/*------------------------------------------------
			Display's network admin menu
		------------------------------------------------*/
		public function wpsNetworkMenu(){
			$sites = WPSSettings::getSitesStatus();

			$authenticatedUsers = WPSAuthenticatedUsers::getNetworkAuthenticatedUsers();
			$ips 				= WPSIP::getNetworkAuthenticatedIPs();
			$ipRanges 			= WPSIPRange::getNetworkAuthenticatedIPRanges();
			$subnets 			= WPSSubnet::getNetworkAuthenticatedSubnets();

			$version = get_option( "wp_sandbox_version" );

			WPSAdminDisplay::displayNetworkAdminScreen( $version, $sites, $authenticatedUsers, $ips, $ipRanges, $subnets );
		}
	}
	$wpSandbox = new WPSandbox();
?>
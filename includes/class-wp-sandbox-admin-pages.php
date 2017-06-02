<?php

class WP_Sandbox_Admin_Pages{
	public function add_admin_menu(){
		add_menu_page('WP Sandbox', 'WP Sandbox', 'manage_options', 'wp_sandbox', array( $this, 'wpsSettingsPage' ), plugins_url().'/wp-sandbox/admin/images/wp-sandbox-logo.png');
		add_submenu_page('wp_sandbox', 'Access', 'Access', 'manage_options', 'wps_access', array ( $this, 'wpsAccessPage' ) );
	}

			/*------------------------------------------------
			Displays the sandbox settings page
		------------------------------------------------*/
		public function wpsSettingsPage(){
			$settings = WP_Sandbox_Settings::getAllSettings();

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

			include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-admin-display.php' );
		}

		/*------------------------------------------------
			Displays the sandbox access page
		------------------------------------------------*/
		public function wpsAccessPage(){
			$previewHash 			= WP_Sandbox_Preview_URL::getPreviewHash();
			$defaultExpirationTime 	= WP_Sandbox_Settings::getDefaultExpirationTime();
			
			$authenticatedUsers 	= WP_Sandbox_Authenticated_Users::getAuthenticatedUsers();
			$ips 					= WP_Sandbox_IP::getIPs();
			$ipRanges 				= WP_Sandbox_IP_Range::getIPRanges();
			$subnets 				= WP_Sandbox_Subnet::getSubnets();
			
			$previewURL = home_url('/').'?wp-sandbox-preview='.$previewHash;

			$version = get_option( "wp_sandbox_version" );

			include( WP_SANDBOX_PATH.'/admin/partials/wp-sandbox-admin-access-display.php' );
		}
}
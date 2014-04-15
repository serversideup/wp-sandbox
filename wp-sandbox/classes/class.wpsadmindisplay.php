<?php
	class WPSAdminDisplay{
		public function __construct(){

		}
		public function __destruct(){

		}
		public function wps_display_network_admin_screen(){
			global $wpdb;

			$checkWPSEnabledQuery = "SELECT blog_id, setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
			$checkWPSEnabled = $wpdb->get_results($checkWPSEnabledQuery, ARRAY_A);

			$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
			$getIPRangesQuery = "SELECT * FROM ".$wpdb->prefix."wps_ip_ranges";
			$getSubnetsQuery = "SELECT * FROM ".$wpdb->prefix."wps_subnets";

			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);
			$ipRanges = $wpdb->get_results($getIPRangesQuery, ARRAY_A);
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);

			echo '<h1>WP Sandbox Network Settings and Access</h1>';

			echo '<div id="wps-network-admin-tab-container">';
				echo '<div class="wps-network-admin-tab" id="wps-network-site-status-tab" onclick="wps_network_display_site_status_tab()">';
					echo 'Site Status';
				echo '</div>';
				echo '<div class="wps-network-admin-tab wps-network-admin-tab-inactive" id="wps-network-network-access-tab" onclick="wps_network_display_network_access_tab()">';
					echo 'Network Access';
				echo '</div>';
			echo '</div>';

			echo '<div id="wps-network-site-status-tab-display">';
				echo '<h3>Plugin Status (Network View)</h3>';
				echo '<div id="wps-network-enable-alert" class="updated">';

				echo '</div>';
				echo '<table id="wps-plugin-status-network-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th><input id="wps-network-enable-all-checkboxes" type="checkbox"/> Status</th>';
							echo '<th>Site Name</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody>';
						foreach($checkWPSEnabled as $enabledStatus){
							$blogInfo = get_blog_details($enabledStatus['blog_id']);
							echo '<tr>';
								if($enabledStatus['setting_value'] == 1){
									echo '<td><input type="checkbox" class="wps-network-enable-checkbox" data-attr-blog-id="'.$enabledStatus['blog_id'].'" name="wps-network-enable-blog" id="wps-network-enable-blog" checked="checked"></td>';
								}else{
									echo '<td><input type="checkbox" class="wps-network-enable-checkbox" data-attr-blog-id="'.$enabledStatus['blog_id'].'" name="wps-network-enable-blog" id="wps-network-enable-blog"></td>';
								}
								echo '<td>http://'.$blogInfo->domain.'</td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
				echo '<br>';
				echo '<a class="button-primary" id="wps_save_enabled_settings" onclick="wps_network_enable()">Save Changes</a>';
			echo '</div>';

			echo '<div id="wps-network-access-tab-display">';
				echo '<table id="wps-network-global-access-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Site Name</th>';
							echo '<th>Type</th>';
							echo '<th>Network/IPs</th>';
							echo '<th>Added By</th>';
							echo '<th>Expires</th>';
							echo '<th></th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-network-global-access-table-body">';
						foreach($allValidatedUsers as $validatedUser){
							$user = get_userdata( $validatedUser['user_id']);
							$blogInfo = get_blog_details($validatedUser['blog_id']);
							echo '<tr>';
								echo '<td>http://'.$blogInfo->domain.'</td>';
								echo '<td>Single</td>';
								echo '<td>'.$validatedUser['ip'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($validatedUser['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$validatedUser['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_network_remove_user(\''.$validatedUser['blog_id'].'\', \''.$validatedUser['user_id'].'\', \''.$validatedUser['ip'].'\')">&times;</div></td>';
							echo '</tr>';
						}
						foreach($ipRanges as $ipRange){
							$user = get_userdata( $ipRange['added_by']);
							$blogInfo = get_blog_details($ipRange['blog_id']);
							echo '<tr>';
								echo '<td>http://'.$blogInfo->domain.'</td>';
								echo '<td>Range</td>';
								echo '<td>'.$ipRange['start_ip'].' - '.$ipRange['end_ip'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($ipRange['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$ipRange['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_network_remove_range(\''.$ipRange['blog_id'].'\', \''.$ipRange['start_ip'].'\', \''.$ipRange['end_ip'].'\')">&times;</div></td>';
							echo '</tr>';
						}
						foreach($subnets as $subnet){
							$user = get_userdata($subnet['added_by']);
							$blogInfo = get_blog_details($subnet['blog_id']);
							echo '<tr>';
								echo '<td>http://'.$blogInfo->domain.'</td>';
								echo '<td>Network</td>';
								echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($subnet['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$subnet['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_network_remove_subnet(\''.$subnet['blog_id'].'\', \''.$subnet['start_ip'].'\', \''.$subnet['subnet'].'\')">&times;</div></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
			echo '</div>';
		}
		public function wps_display_single_site_settings_screen(){
			global $wpdb;

			if(is_multisite()){
				global $switched;
				$currentBlogID = get_current_blog_id();
				switch_to_blog(1);

				$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page' AND blog_id = '".$currentBlogID."'";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time' AND blog_id = '".$currentBlogID."'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled' AND blog_id = '".$currentBlogID."'";
				$checkCloudFlareEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'CloudFlare' AND blog_id = '".$currentBlogID."'";
			}else{
				$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page'";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
				$checkCloudFlareEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'CloudFlare'";
			}

			$checkDefaultWPSExpire = $wpdb->get_results($checkDefaultWPSExpireQuery, ARRAY_A);
			$checkDefaultWPSPage = $wpdb->get_results($checkDefaultWPSPageQuery, ARRAY_A);
			$checkDefaultEnabled = $wpdb->get_results($checkDefaultEnabledQuery, ARRAY_A);
			$checkCloudFlare = $wpdb->get_results($checkCloudFlareEnabledQuery, ARRAY_A);

			if(is_multisite()){
				restore_current_blog();
			}

			if($checkDefaultEnabled[0]['setting_value'] == '0'){
				echo '<div class="wps-disable-banner">';
					echo 'WP Sandbox is currently <strong>DISABLED</strong>. Public users are able to access '.home_url('/');
				echo '</div>';
			}else{
				echo '<div class="wps-disable-banner" style="display:none">';
					echo 'WP Sandbox is currently <strong>DISABLED</strong>. Public users are able to access '.home_url('/');
				echo '</div>';
			}
			echo '<div id="wps-settings-saved" class="updated">';
				echo 'WP Sandbox settings saved';
			echo '</div>';
			echo '<h1>WP Sandbox Settings</h1>';

			echo '<div class="wps-display-row">';
				echo '<div class="wps-display-label">Limit public access to website: </div>';
				echo '<ul class="wps-toggle" id="wps-enable-disable-switch">';
					if($checkDefaultEnabled[0]['setting_value'] == '0'){
						echo '<li class="on" data-setting="off"><a href="#">OFF</a></li>';
    					echo '<li data-setting="on"><a href="#">ON</a></li>';
					}else{
						echo '<li data-setting="off"><a href="#">OFF</a></li>';
    					echo '<li class="on" data-setting="on"><a href="#">ON</a></li>';
					}
				echo '</ul>';
			echo '</div>';

			echo '<div class="wps-display-row">';
				echo '<div class="wps-display-label">CloudFlare Enabled: </div>';
				echo '<ul class="wps-toggle" id="wps-cloud-flare-enable-disable-switch">';
					if($checkCloudFlare[0]['setting_value'] == '0'){
						echo '<li class="on" data-setting="off"><a href="#">OFF</a></li>';
						echo '<li data-setting="on"><a href="#">ON</a></li>';
					}else{
						echo '<li data-setting="off"><a href="#">OFF</a></li>';
						echo '<li class="on" data-setting="on"><a href="#">ON</a></li>';
					}
				echo '</ul>';
			echo '</div>';

			echo '<div class="wps-display-row">';
				echo '<div class="wps-display-label">Page for Unauthorized Users: </div>';
				echo '<div class="wps-display-setting">';
					echo '<select name="wps-default-page" id="wps-default-page">'; 
		 				echo '<option value="">'.esc_attr( __( 'Select page' ) ).'</option>';
		 					
	 					if($checkDefaultWPSPage[0]['setting_value'] == 'blank'){
							echo '<option value="blank" selected>Blank</option>';
						}else{
							echo '<option value="blank">Blank</option>';
						}
	 
	  					$pages = get_pages(); 
						foreach ( $pages as $page ) {
							$link = get_page_link($page->ID);

							if($link == $checkDefaultWPSPage[0]['setting_value']){
								$option = '<option value="' . get_page_link( $page->ID ) . '" selected>';
							}else{
						  		$option = '<option value="' . get_page_link( $page->ID ) . '">';
						  	}

							$option .= $page->post_title;
							$option .= '</option>';
							echo $option;
						 }

					echo '</select>';
				echo '</div>';
			echo '</div>';
			echo '<div class="wps-display-row">';
				echo '<div class="wps-display-label">Default access expiration time: </div>';
				echo '<div class="wps-display-setting">';
					echo '<select name="wps-default-expire-time" id="wps-default-expire-time">';
						if($checkDefaultWPSExpire[0]['setting_value'] == 'day'){
							echo '<option value="day" selected>Day</option>';
						}else{
							echo '<option value="day">Day</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'week'){
							echo '<option value="week" selected>Week</option>';
						}else{
							echo '<option value="week">Week</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'twoweeks'){
							echo '<option value="twoweeks" selected>Two Weeks</option>';
						}else{
							echo '<option value="twoweeks">Two Weeks</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'month'){
							echo '<option value="month" selected>Month</option>';
						}else{
							echo '<option value="month">Month</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'never'){
							echo '<option value="never" selected>Never Expire</option>';
						}else{
							echo '<option value="never">Never Expire</option>';
						}
					echo '</select>';
				echo '</div>';
			echo '</div>';
			echo '<div class="wps-display-row">';
				echo '<p>When a user logs into Wordpress, their IP will be authenticated for future<br>
						access. You can control how long this IP should remain authenticated for.</p>';
				echo '<a class="button button-primary" onclick="wps_save_settings()"/>Save Changes</a>';
			echo '</div>';
		}
		public function wps_display_single_site_access_screen(){
			global $wpdb;

			if(is_multisite()){
				global $switched;
				$currentBlogID = get_current_blog_id();
				switch_to_blog(1);

				$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon WHERE blog_id = '".$currentBlogID."'";
				$getPreviewHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash' AND blog_id = '".$currentBlogID."'";
				$getIPRangesQuery = "SELECT added_by, start_ip, end_ip, expires FROM ".$wpdb->prefix."wps_ip_ranges WHERE blog_id = '".$currentBlogID."'";
				$getSubnetsQuery = "SELECT added_by, start_ip, subnet, expires FROM ".$wpdb->prefix."wps_subnets WHERE blog_id = '".$currentBlogID."'";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time' AND blog_id = '".$currentBlogID."'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled' AND blog_id = '".$currentBlogID."'";
			}else{
				$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
				$getPreviewHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash'";
				$getIPRangesQuery = "SELECT start_ip, end_ip, expires FROM ".$wpdb->prefix."wps_ip_ranges";
				$getSubnetsQuery = "SELECT start_ip, subnet, expires FROM ".$wpdb->prefix."wps_subnets";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
			}
			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);
			$previewHash = $wpdb->get_results($getPreviewHashQuery, ARRAY_A);
			$ipRanges = $wpdb->get_results($getIPRangesQuery, ARRAY_A);
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);
			$checkDefaultEnabled = $wpdb->get_results($checkDefaultEnabledQuery, ARRAY_A);
			$checkDefaultWPSExpire = $wpdb->get_results($checkDefaultWPSExpireQuery, ARRAY_A);

			if($checkDefaultEnabled[0]['setting_value'] == '0'){
				echo '<div class="wps-disable-banner">';
					echo 'WP Sandbox is currently <strong>DISABLED</strong>. Public users are able to access '.home_url('/');
				echo '</div>';
			}
			echo '<div id="wps-ip-added-alert" class="updated">';
				
			echo '</div>';

			echo '<div id="wps-ip-range-alert" class="updated">';

			echo '</div>';

			echo '<div id="wps-subnet-alert" class="updated">';

			echo '</div>';
			echo '<h2>Access Control</h2>';
			echo '<div class="wps-left">';
				echo '<h3>Add Single IP Address</h3>';

				echo '<label class="wps-label">IP Address: </label><input id="wps-allowed-ip" name="wps-allowed-ip" /><br>';
				echo '<label class="wps-label">Expiration: </label>';
				echo '<select id="wps-add-ip-address-expiration">';
					if($checkDefaultWPSExpire[0]['setting_value'] == 'day'){
							echo '<option value="day" selected>Day</option>';
						}else{
							echo '<option value="day">Day</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'week'){
							echo '<option value="week" selected>Week</option>';
						}else{
							echo '<option value="week">Week</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'twoweeks'){
							echo '<option value="twoweeks" selected>Two Weeks</option>';
						}else{
							echo '<option value="twoweeks">Two Weeks</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'month'){
							echo '<option value="month" selected>Month</option>';
						}else{
							echo '<option value="month">Month</option>';
						}
						
						if($checkDefaultWPSExpire[0]['setting_value'] == 'never'){
							echo '<option value="never" selected>Never Expire</option>';
						}else{
							echo '<option value="never">Never Expire</option>';
						}
				echo '</select>';
				echo '<br>';
				echo '<a class="button button-primary" onclick="wps_allow_ip()"/>Add IP</a>';

				echo '<h3>Add IP Range</h3>';

				echo '<label class="wps-label">From IP: </label><input id="wps-ip-range-start" name="wps-ip-range-start" /><br>';
				echo '<label class="wps-label">To IP: </label><input id="wps-ip-range-end" name="wps-ip-range-end" /><br>';
				echo '<label class="wps-label">Expiration: </label>';
				echo '<select id="wps-add-ip-range-address-expiration">';
					if($checkDefaultWPSExpire[0]['setting_value'] == 'day'){
						echo '<option value="day" selected>Day</option>';
					}else{
						echo '<option value="day">Day</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'week'){
						echo '<option value="week" selected>Week</option>';
					}else{
						echo '<option value="week">Week</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'twoweeks'){
						echo '<option value="twoweeks" selected>Two Weeks</option>';
					}else{
						echo '<option value="twoweeks">Two Weeks</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'month'){
						echo '<option value="month" selected>Month</option>';
					}else{
						echo '<option value="month">Month</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'never'){
						echo '<option value="never" selected>Never Expire</option>';
					}else{
						echo '<option value="never">Never Expire</option>';
					}
				echo '</select>';
				echo '<br>';
				echo '<a class="button button-primary" onclick="wps_add_ip_range()"/>Add IP Range</a>';

				echo '<h3>Add Network</h3>';
				echo '<label class="wps-label">Network: </label><input id="wps-subnet-network" name="wps-subnet-network" size="13"/> / <input id="wps-subnet-network-subnet" name="wps-subnet-network-subnet" size="2"/><br>';
				echo '<label class="wps-label">Expiration: </label>';
				echo '<select id="wps-add-network-expiration">';
					if($checkDefaultWPSExpire[0]['setting_value'] == 'day'){
						echo '<option value="day" selected>Day</option>';
					}else{
						echo '<option value="day">Day</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'week'){
						echo '<option value="week" selected>Week</option>';
					}else{
						echo '<option value="week">Week</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'twoweeks'){
						echo '<option value="twoweeks" selected>Two Weeks</option>';
					}else{
						echo '<option value="twoweeks">Two Weeks</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'month'){
						echo '<option value="month" selected>Month</option>';
					}else{
						echo '<option value="month">Month</option>';
					}
					
					if($checkDefaultWPSExpire[0]['setting_value'] == 'never'){
						echo '<option value="never" selected>Never Expire</option>';
					}else{
						echo '<option value="never">Never Expire</option>';
					}
				echo '</select>';
				echo '<br>';
				echo '<a class="button button-primary" onclick="wps_add_network()"/>Add Network</a>';
			echo '</div>';
			echo '<div class="wps-right">';
				echo '<span class="wps-header">Share URL: </span><input id="wps-share-url" name="wps-share-url" value="'.home_url('/').'?wp-sandbox-preview='.$previewHash[0]['setting_value'].'"/><a class="button button-primary" onclick="wps_update_preview_hash()">Regenerate URL</a><br>';
				echo '<p class="wps-description">Copy the URL above to share with users who need access without IP authentication. NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</p>';
				echo '<table id="wps-access-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Type</th>';
							echo '<th>Network/IPs</th>';
							echo '<th>Added By</th>';
							echo '<th>Expires</th>';
							echo '<th>Remove</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-access-table-body">';
						foreach($allValidatedUsers as $validatedUser){
							$user = get_userdata( $validatedUser['user_id']);
							echo '<tr>';
								echo '<td>Single IP</td>';
								echo '<td>'.$validatedUser['ip'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($validatedUser['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$validatedUser['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_remove_user(\''.$validatedUser['user_id'].'\', \''.$validatedUser['ip'].'\')">&times;</div></td>';
							echo '</tr>';
						}
						foreach($ipRanges as $ipRange){
							$user = get_userdata( $ipRange['added_by']);
							echo '<tr>';
								echo '<td>IP Range</td>';
								echo '<td>'.$ipRange['start_ip'].' - '.$ipRange['end_ip'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($ipRange['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$ipRange['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_remove_range(\''.$ipRange['start_ip'].'\', \''.$ipRange['end_ip'].'\')">&times;</div></td>';
							echo '</tr>';
						}
						foreach($subnets as $subnet){
							$user = get_userdata( $ipRange['added_by']);
							echo '<tr>';
								echo '<td>Network</td>';
								echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
								echo '<td>'.$user->user_login.'</td>';
								if($ipRange['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$subnet['expires'].'</td>';
								}
								echo '<td><div class="wps-remove" onclick="wps_remove_subnet(\''.$subnet['start_ip'].'\', \''.$subnet['subnet'].'\')">&times;</div></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
			echo '</div>';
		}
	}
?>
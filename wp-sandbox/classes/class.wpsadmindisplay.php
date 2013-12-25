<?php
	class WPSAdminDisplay{
		public function __construct(){

		}
		public function __destruct(){

		}
		public function wps_display_admin_screen(){
			global $wpdb;

			if(is_multisite()){
				global $switched;
				$currentBlogID = get_current_blog_id();
				switch_to_blog(1);

				$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page' AND blog_id = '".$currentBlogID."'";
				$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon WHERE blog_id = '".$currentBlogID."'";
				$getPreviewHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash' AND blog_id = '".$currentBlogID."'";
				$getIPRangesQuery = "SELECT start_ip, end_ip FROM ".$wpdb->prefix."wps_ip_ranges WHERE blog_id = '".$currentBlogID."'";
				$getSubnetsQuery = "SELECT start_ip, subnet FROM ".$wpdb->prefix."wps_subnets WHERE blog_id = '".$currentBlogID."'";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time' AND blog_id = '".$currentBlogID."'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled' AND blog_id = '".$currentBlogID."'";
			}else{
				$checkDefaultWPSPageQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Page'";
				$getAllValidatedUsersQuery = "SELECT * FROM ".$wpdb->prefix."wps_coming_soon";
				$getPreviewHashQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Preview Hash'";
				$getIPRangesQuery = "SELECT start_ip, end_ip FROM ".$wpdb->prefix."wps_ip_ranges";
				$getSubnetsQuery = "SELECT start_ip, subnet FROM ".$wpdb->prefix."wps_subnets";
				$checkDefaultWPSExpireQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Default Expiration Time'";
				$checkDefaultEnabledQuery = "SELECT setting_value FROM ".$wpdb->prefix."wps_coming_soon_settings WHERE setting_name = 'Enabled'";
			}

			
			$checkDefaultWPSPage = $wpdb->get_results($checkDefaultWPSPageQuery, ARRAY_A);
			$allValidatedUsers = $wpdb->get_results($getAllValidatedUsersQuery, ARRAY_A);
			$previewHash = $wpdb->get_results($getPreviewHashQuery, ARRAY_A);
			$ipRanges = $wpdb->get_results($getIPRangesQuery, ARRAY_A);
			$subnets = $wpdb->get_results($getSubnetsQuery, ARRAY_A);
			$checkDefaultWPSExpire = $wpdb->get_results($checkDefaultWPSExpireQuery, ARRAY_A);
			$checkDefaultEnabled = $wpdb->get_results($checkDefaultEnabledQuery, ARRAY_A);

			if(is_multisite()){
				restore_current_blog();
			}
			echo '<h1>WP Sandbox Settings</h1>';
			echo '<div id="wps-left">';
				echo '<div id="wps-settings-saved">Settings Saved!</div>';
				echo '<div class="wps-settings"><p>Please select a page to redirect users to who don\'t have permissions to view your site. If no page is selected, the default 404 page will show.</p>';
				echo '<strong>Page for Unauthorized Users: </strong><br><select name="wps-default-page" id="wps-default-page"> 
	 					<option value="">'.esc_attr( __( 'Select page' ) ).'</option>';
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
				echo '</select><br>';
				echo '<button id="wps-save-default-page-button" onclick="wps_save_default_page_setting()">Save Default Page</button></div>';

				echo '<div class="wps-settings">';
					echo '<strong>Set Default Expiration Time: </strong><br>';
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
					echo '</select><br>';
					echo '<button id="wps-save-default-expire-time-button" onclick="wps_save_default_expire_time()">Save Default Expiration Time</button>';
				echo '</div>';
				echo '<div class="wps-settings">';
					echo '<div id="wps-allow-ip">';
						echo '<strong>Allow this IP: </strong><br><input type="text" id="wps-allowed-ip" name="wps-allowed-ip"/><br>';
						echo '<strong>For: </strong><select name="wps-ip-allowed-expire-time" id="wps-ip-allowed-expire-time">';
							echo '<option value="day">One Day</option>';
							echo '<option value="week">One Week</option>';
							echo '<option value="twoweeks">Two Weeks</option>';
							echo '<option value="month">One Month</option>';
							echo '<option value="never">Never Expire</option>';
						echo '</select>';
						echo '<button onclick="wps_allow_ip()" id="wps-allow-ip-button">Allow Access</button>';
					echo '</div>';
				echo '</div>';
			echo '</div>';

			echo '<div id="wps-right">';
                echo '<span class="switch-label"><strong>Enabled</strong></span>';
                echo '<span class="switch-off">OFF</span>';
                echo '<div class="onoffswitch">';
                	if($checkDefaultEnabled[0]['setting_value'] == '1'){
                		echo '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="sandbox-enabled" checked>';
                	}else{
                		echo '<input type="checkbox" name="onoffswitch" class="onoffswitch-checkbox" id="sandbox-enabled">';
                	}
	                echo '<label class="onoffswitch-label" for="sandbox-enabled">';
	                    echo '<div class="onoffswitch-inner"></div>';
	                    echo '<div class="onoffswitch-switch"></div>';
	                echo '</label>';
                echo '</div>';
                echo '<span class="switch-on">ON</span><br>';

                //IP Ranges
				echo '<div id="wps-ip-range">';
					echo '<h3>IP Ranges</h3>';
					echo '<div class="ip-range-row">';
						echo '<div class="inner-left-range">';
							echo '<strong>Starting IP: </strong><br><input type="text" name="wps-starting-ip[]"/>';
						echo '</div>';
						echo '<div class="inner-middle-range">';
							echo 'to';
						echo '</div>';
						echo '<div class="inner-right-range">';
							echo '<strong>Ending IP: </strong><br><input type="text" name="wps-ending-ip[]"/>';
						echo '</div>';
					echo '</div>';
				echo '</div>';
				echo '<div id="wps-additional-ip-range">';

				echo '</div>';
				
				echo '<button onclick="wps_add_ip_range()" id="wps-add-ip-button">Add IP Range</button>';
				echo '<button onclick="wps_save_ip_ranges()" id="wps-save-ip-ranges-button">Save IP Ranges</button>';

				//Subnets
				echo '<div id="wps-subnets">';
					echo '<h3>Subnets</h3>';
					echo '<div class="wps-subnet-row">';
						echo '<div class="inner-left-subnet">';
							echo '<strong>IP: </strong><br><input type="text" name="wps-subnet-ip[]"/>';
						echo '</div>';
						echo '<div class="inner-middle-subnet">';
							echo '/';
						echo '</div>';
						echo '<div class="inner-right-subnet">';
							echo '<strong>Subnet</strong><br><input type="text" name="wps-subnet-subnet[]"/>';
						echo '</div>';
					echo '</div>';
					echo '<div id="wps-additional-subnets">';

					echo '</div>';

					echo '<button onclick="wps_add_subnet()" id="wps-add-subnet-button">Add Subnet</button>';
					echo '<button onclick="wps_save_subnets()" id="wps-save-subnets-button">Save Subnets</button>';
				echo '</div>';
				echo '<div id="wps-preview-hash-div">';
					echo '<p><strong>Copy the URL below to share with users who need access without IP authentication. NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</strong></p><br>';
					echo '<strong>Copy this URL</strong>: <input type="text" id="wps-preview-hash" name="wps-preview-hash" onClick="this.select()" value="'.home_url('/').'?wp-sandbox-preview='.$previewHash[0]['setting_value'].'">';
					echo '<button onclick="wps_update_preview_hash()" id="wps-update-preview-hash-button">Update Preview Hash</button>';
				echo '</div>';
				echo '<div id="wps-user-removed">User Removed!</div>';
				echo '<div id="wps-ip-added">IP Added</div>';
				echo '<p>These IPs are auhtenticated to browse the entire site (that includes other machines on the same network)</p>';
				echo '<table id="wps-validated-users">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Added By</th><th>IP</th><th>Last Login</th><th>Expires</th><th>Remove User</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-users-body">';
						foreach($allValidatedUsers as $user){
							$userInfo = get_userdata($user['user_id']);
							echo '<tr>';
								echo '<td>'.$userInfo->user_login.'</td>';
								echo '<td>'.$user['ip'].'</td>';
								echo '<td>'.$user['last_login'].'</td>';
								
								if($user['expires'] == '0000-00-00 00:00:00'){
									echo '<td>Never</td>';
								}else{
									echo '<td>'.$user['expires'].'</td>';
								}

								echo '<td><span class="wps-remove" onclick="wps_remove_user('.$user['user_id'].', \''.$user['ip'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
				echo '<table id="wps-ip-ranges">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Start IP</th><th>End IP</th><th>Remove Range</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-ip-ranges-body">';
						foreach($ipRanges as $ipRange){
							echo '<tr>';
								echo '<td>'.$ipRange['start_ip'].'</td>';
								echo '<td>'.$ipRange['end_ip'].'</td>';
								echo '<td><span class="wps-remove" onclick="wps_remove_range(\''.$ipRange['start_ip'].'\', \''.$ipRange['end_ip'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</tbody>';
				echo '</table>';
				echo '<table id="wps-subnets-table">';
					echo '<thead>';
						echo '<tr>';
							echo '<th>Subnet</th><th>Remove Subnet</th>';
						echo '</tr>';
					echo '</thead>';
					echo '<tbody id="wps-subnets-table-body">';
						foreach($subnets as $subnet){
							echo '<tr>';
								echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
								echo '<td><span class="wps-remove" onclick="wps_remove_subnet(\''.$subnet['start_ip'].'\', \''.$subnet['subnet'].'\')"></span></td>';
							echo '</tr>';
						}
					echo '</table>';
				echo '</table>';
			echo '</div>';
		}
		public function wps_display_network_admin_screen(){

		}
	}
?>
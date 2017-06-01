<img class="wps-header-logo" src="'.plugins_url().'/wp-sandbox/images/wp-sandbox-logo-large.png"/>

<br>Version: <?php echo $version; ?><br>

/* Start sites enabled/disabled banner */
echo '<div id="wps-settings-saved" class="updated">';
	echo 'WP Sandbox changes saved';
echo '</div>';
/* End sites enabled/disabled banner */

/* Begin Enabled Sites Panel */
echo '<div class="wrap">';
	echo '<div class="wps-panel">';
		echo '<h3>Enabled Sites</h3>';
		echo '<hr>';

		/* Begin Enabled Sites Table */
		echo '<table class="wps-table" id="wps-enabled-sites-table">';
			echo '<thead>';
				echo '<tr>';
					echo '<td><input type="checkbox" id="wps-toggle-select-deselect-all"/> Enabled</td>';
					echo '<td>Site Name</td>';
					echo '<td>Site URL</td>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				foreach( $sites as $site ){
					$blogInfo = get_blog_details( $site['blog_id'] );
					echo '<tr>';
						if( $site['setting_value'] == 1 ){
							echo '<td>';
								echo '<input type="checkbox" class="wps-network-enable-checkbox" value="'.$site['blog_id'].'" checked="checked">';
							echo '</td>';
						}else{
							echo '<td>';
								echo '<input type="checkbox" class="wps-network-enable-checkbox" value="'.$site['blog_id'].'">';
							echo '</td>';
						}
						echo '<td>';
							echo $blogInfo->blogname;
						echo '</td>';
						echo '<td>';
							echo $blogInfo->siteurl;
						echo '</td>';
					echo '</tr>';
				}
			echo '</tbody>';
		echo '</table>';
		/* End Enabled Sites Table */

		echo '<br>';

		echo '<button id="wps-network-enable-sites-save" class="button button-primary">Save Changes</button>';
	echo '</div>';
echo '</div>';
/* End Enabled Sites Panel */

/* Begin Network Access Panel */
echo '<div class="wrap">';
	echo '<div class="wps-panel">';
		echo '<h3>Network Access</h3>';
		echo '<hr>';

		/* Begin Network Access Table */
		echo '<table class="wps-table" id="wps-network-access-table">';
			echo '<thead>';
				echo '<tr>';
					echo '<td>Type</td>';
					echo '<td>Site</td>';
					echo '<td>Network/IPs</td>';
					echo '<td>Added By</td>';
					echo '<td>Expires</td>';
					echo '<td></td>';
				echo '</tr>';
			echo '</thead>';
			echo '<tbody>';
				/*
					Authenticated Users
				*/
				foreach( $authenticatedUsers as $authenticatedUser ){
					$user = get_userdata( $authenticatedUser['user_id'] );
					$blogInfo = get_blog_details( $authenticatedUser['blog_id'] );

					echo '<tr id="user-'.$authenticatedUser['id'].'">';
						echo '<td>User ('.$user->user_login.')</td>';
						echo '<td>'.$blogInfo->blogname.'</td>';
						echo '<td>'.$authenticatedUser['ip'].'</td>';
						echo '<td>-</td>';
						echo '<td>'.( $authenticatedUser['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $authenticatedUser['expires'] ) ) ).'</td>';
						echo '<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="'.$authenticatedUser['id'].'">Remove Access</a></td>';
					echo '</tr>';
				} 
				/*
					IPs
				*/
				foreach( $ips as $ip ){
					$user = get_userdata( $ip['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );

					echo '<tr id="single-'.$ip['id'].'">';
						echo '<td>Single IP</td>';
						echo '<td>'.$blogInfo->blogname.'</td>';
						echo '<td>'.$ip['ip'].'</td>';
						echo '<td>'.$user->user_login.'</td>';
						echo '<td>'.( $ip['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ip['expires'] ) ) ).'</td>';
						echo '<td><a class="wps-remove-access" data-attr-type="single" data-attr-id="'.$ip['id'].'">Remove Access</a></td>';
					echo '</tr>';
				}
				/*
					IP Range
				*/
				foreach( $ipRanges as $ipRange ){
					$user = get_userdata( $ipRange['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );

					echo '<tr id="range-'.$ipRange['id'].'">';
						echo '<td>IP Range</td>';
						echo '<td>'.$blogInfo->blogname.'</td>';
						echo '<td>'.$ipRange['start_ip'].'-'.$ipRange['end_ip'].'</td>';
						echo '<td>'.$user->user_login.'</td>';
						echo '<td>'.( $ipRange['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ipRange['expires'] ) ) ).'</td>';
						echo '<td><a class="wps-remove-access" data-attr-type="range" data-attr-id="'.$ipRange['id'].'">Remove Access</a></td>';
					echo '</tr>';
				}

				/*
					Networks
				*/
				foreach( $subnets as $subnet ){
					$user = get_userdata( $subnet['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );

					echo '<tr id="subnet-'.$subnet['id'].'">';
						echo '<td>Network</td>';
						echo '<td>'.$blogInfo->blogname.'</td>';
						echo '<td>'.$subnet['start_ip'].'/'.$subnet['subnet'].'</td>';
						echo '<td>'.$user->user_login.'</td>';
						echo '<td>'.( $subnet['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $subnet['expires'] ) ) ).'</td>';
						echo '<td><a class="wps-remove-access" data-attr-type="subnet" data-attr-id="'.$subnet['id'].'">Remove Access</a></td>';
					echo '</tr>';
				}
			echo '</tbody>';
		echo '</table>';
		/* End Network Access Table */
	echo '</div>';
echo '</div>';
/* End Network Access Panel */
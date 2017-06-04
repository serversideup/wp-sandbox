<img class="wps-header-logo" src="'.plugins_url().'/wp-sandbox/images/wp-sandbox-logo-large.png"/>

<br>Version: <?php echo $version; ?><br>


<div id="wps-settings-saved" class="updated">
	WP Sandbox changes saved
</div>

<div class="wrap">
	<div class="wps-panel">
		<h3>Enabled Sites</h3>
		<hr>

		<table class="wps-table" id="wps-enabled-sites-table">
			<thead>
				<tr>
					<td><input type="checkbox" id="wps-toggle-select-deselect-all"/> Enabled</td>
					<td>Site Name</td>
					<td>Site URL</td>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach( $sites as $site ){
					$blogInfo = get_blog_details( $site['blog_id'] );
			?>
					<tr>
					<?php
						if( $site['setting_value'] == 1 ){
					?>
							<td>
								<input type="checkbox" class="wps-network-enable-checkbox" value="<?php echo $site['blog_id']; ?>" checked="checked">
							</td>
					<?php
						}else{
					?>
							<td>
								<input type="checkbox" class="wps-network-enable-checkbox" value="<?php echo $site['blog_id']; ?>">
							</td>
					<?php
						}
					?>
						<td>
							<?php echo $blogInfo->blogname; ?>
						</td>
						<td>
							<?php echo $blogInfo->siteurl; ?>
						</td>
					</tr>
			<?php
				}
			?>
			</tbody>
		</table>

		<br>

		<button id="wps-network-enable-sites-save" class="button button-primary">Save Changes</button>
	</div>
</div>

<div class="wrap">
	<div class="wps-panel">
		<h3>Network Access</h3>
		<hr>

		<table class="wps-table" id="wps-network-access-table">
			<thead>
				<tr>
					<td>Type</td>
					<td>Site</td>
					<td>Network/IPs</td>
					<td>Added By</td>
					<td>Expires</td>
					<td></td>
				</tr>
			</thead>
			<tbody>
			<?php
				foreach( $authenticatedUsers as $authenticatedUser ){
					$user = get_userdata( $authenticatedUser['user_id'] );
					$blogInfo = get_blog_details( $authenticatedUser['blog_id'] );
			?>
					<tr id="user-<?php echo $authenticatedUser['id']; ?>">
						<td>User (<?php echo $user->user_login; ?>)</td>
						<td><?php echo $blogInfo->blogname; ?></td>
						<td><?php echo $authenticatedUser['ip']; ?></td>
						<td>-</td>
						<td><?php echo ( $authenticatedUser['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $authenticatedUser['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="<?php echo $authenticatedUser['id']; ?>">Remove Access</a></td>
					</tr>
			<?php
				} 
			?>

			<?php
				foreach( $ips as $ip ){
					$user = get_userdata( $ip['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );
			?>
					<tr id="single-'.$ip['id'].'">
						<td>Single IP</td>
						<td><?php echo $blogInfo->blogname; ?></td>
						<td><?php echo $ip['ip']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $ip['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ip['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="single" data-attr-id="<?php echo $ip['id']; ?>">Remove Access</a></td>
					</tr>';
			<?php
				}
			?>

			<?php
				foreach( $ipRanges as $ipRange ){
					$user = get_userdata( $ipRange['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );
			?>
					<tr id="range-<?php echo $ipRange['id']; ?>">
						<td>IP Range</td>
						<td><?php echo $blogInfo->blogname; ?></td>
						<td><?php echo $ipRange['start_ip'].'-'.$ipRange['end_ip']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $ipRange['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ipRange['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="range" data-attr-id="<?php echo $ipRange['id']; ?>">Remove Access</a></td>
					</tr>
			<?php
				}
			?>

			<?php
				foreach( $subnets as $subnet ){
					$user = get_userdata( $subnet['added_by'] );
					$blogInfo = get_blog_details( $ip['blog_id'] );
			?>
					<tr id="subnet-'.$subnet['id'].'">';
						<td>Network</td>
						<td><?php echo $blogInfo->blogname; ?></td>
						<td><?php echo $subnet['start_ip'].'/'.$subnet['subnet']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $subnet['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $subnet['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="subnet" data-attr-id="<?php echo $subnet['id']; ?>">Remove Access</a></td>
					</tr>
			<?php
				}
			?>
			</tbody>
		</table>
	</div>
</div>
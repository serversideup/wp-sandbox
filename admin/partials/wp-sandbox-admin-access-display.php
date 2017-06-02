<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<img class="wps-header-logo" src="<?php echo plugins_url(); ?>/wp-sandbox/images/wp-sandbox-logo-large.png"/>
<br>Version: <?php echo $version; ?><br>

<div class="wrap">
	<div class="wps-panel">
		<h3>Private URL</h3>
		<hr>
		<label class="wps-label">Share URL: </label>
		<input type="text" class="wps-text" name="wps-share-url" id="wps-share-url" value="<?php echo $previewURL; ?>" readonly/>
		
		<p>Copy the URL above to share with users who need access without IP authentication. NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</p>
		
		<button id="wps-regenerate-url" class="button button-primary">Regenerate URL</button>
	</div>
</div>

<div class="wrap">
	<div class="wps-panel">
		<h3>Access List</h3>
		<hr>
		<label class="wps-label">Access Rule: </label>
		<input type="text" class="wps-text" name="wps-access-rule" id="wps-access-rule" value=""/>

		<br>

		<label class="wps-label">Expires: </label>
		<select id="wps-expiration" name="wps-expiration">
			<option value="day" <?php echo ( $defaultExpirationTime == 'day' ? 'selected="selected"' : '' ); ?>'>Day</option>
			<option value="week" <?php echo ( $defaultExpirationTime == 'week' ? 'selected="selected"' : '' ); ?>'>Week</option>
			<option value="twoweeks" <?php echo ( $defaultExpirationTime == 'twoweek' ? 'selected="selected"' : '' ); ?>'>Two Weeks</option>
			<option value="never" <?php echo ( $defaultExpirationTime == 'never' ? 'selected="selected"' : '' ); ?>'>Never</option>
		</select>

		<br><br>
		<div id="wps-access-rule-validation" class="validation">Please enter a valid access rule.</div>
		<button id="wps-add-access-rule" class="button button-primary">Add Access Rule</button>
	
		<p>You can add an IP address, IP range, or a subnet by using the input box above. The following methods are supported: <br><br>
		<strong>Single IP Address:</strong> 192.168.1.100<br>
		<strong>IP Address Range:</strong> 192.168.1.100-192.168.1.199<br>
		<strong>Network Address:</strong> 192.168.1.0/24<br>
	</div>
</div>

<div class="wrap">
	<div class="wps-panel">
		<h3>Current Site Access</h3>
		<hr>
		<table class="wps-table" id="wps-current-site-access">
			<thead>
				<tr>
					<td>Type</td>
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
			?>
					<tr id="user-<?php echo $authenticatedUser['id']; ?>">
						<td>User (<?php echo $user->user_login; ?>)</td>
						<td><?php echo $authenticatedUser['ip']; ?></td>
						<td>-</td>
						<td><?php echo ( $authenticatedUser['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $authenticatedUser['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="user" data-attr-id="<?php echo $authenticatedUser['id']; ?>">Remove Access</a></td>
					</tr>
			<?php } ?> 

			<?php
				foreach( $ips as $ip ){
					$user = get_userdata( $ip['added_by'] );
			?>
					<tr id="single-<?php echo $ip['id']; ?>">
						<td>Single IP</td>
						<td><?php echo $ip['ip']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $ip['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ip['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="single" data-attr-id="<?php echo $ip['id']; ?>">Remove Access</a></td>
					</tr>
			<?php } ?>

			<?php
				foreach( $ipRanges as $ipRange ){
					$user = get_userdata( $ipRange['added_by'] );
			?>
					<tr id="range-'.$ipRange['id'].'">
						<td>IP Range</td>
						<td><?php echo $ipRange['start_ip'].'-'.$ipRange['end_ip']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $ipRange['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $ipRange['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="range" data-attr-id="<?php echo $ipRange['id']; ?>">Remove Access</a></td>
					</tr>
			<?php } ?>

			<?php
				foreach( $subnets as $subnet ){
					$user = get_userdata( $subnet['added_by'] );
			?>
					<tr id="subnet-'.$subnet['id'].'">
						<td>Network</td>
						<td><?php echo $subnet['start_ip'].'/'.$subnet['subnet']; ?></td>
						<td><?php echo $user->user_login; ?></td>
						<td><?php echo ( $subnet['expires'] == '0000-00-00 00:00:00' ? 'never' : date('m-d-Y H:i:s', strtotime( $subnet['expires'] ) ) ); ?></td>
						<td><a class="wps-remove-access" data-attr-type="subnet" data-attr-id="<?php echo $subnet['id']; ?>">Remove Access</a></td>
					</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
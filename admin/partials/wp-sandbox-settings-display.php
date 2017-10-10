<div id="wp-sandbox-disabled" class="error" <?php echo $settings[0]['enabled'] == 1 ? 'style="display: none;"' : '' ?>>
	WP Sandbox is <strong><u>disabled.</u></strong> Anonymous users are able to visit your site. Enable WP Sandbox to protect your site.
</div>

<div class="wp-sandbox-panel" id="wp-sandbox-header">
	<div class="row">
		<div class="half">
			<img class="wp-sandbox-logo" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/wp-sandbox-logo-large.png"/>
			<a href="https://github.com/521dimensions/wp-sandbox" id="wp-sandbox-github-link" target="_blank">Visit Us on Github</a>
		</div>
		<div class="half">
			<div id="wp-sandbox-on-off-container">
				<div class="wp-sandbox-on-off-switch">
					<input type="checkbox" name="wp-sandbox-on-off-switch" class="wp-sandbox-on-off-switch-checkbox" id="wp-sandbox-on-off-switch-checkbox" <?php echo $settings[0]['enabled'] == 1 ? 'checked' : '' ?>>
						<label class="wp-sandbox-on-off-switch-label" for="wp-sandbox-on-off-switch-checkbox">
						<span class="wp-sandbox-on-off-switch-inner"></span>
						<span class="wp-sandbox-on-off-switch-switch"></span>
					</label>
				</div>
				<span class="sub-title" id="site-enabled-status">Your site is locked down!</span>
			</div>
		</div>
	</div>
</div>

<div class="wp-sandbox-panel" id="wp-sandbox-settings-nav">
	<div class="full">
		<span class="wp-sandbox-settings-link wp-sandbox-active-settings-link" id="wp-sandbox-access-link">Access</span>
		<span class="wp-sandbox-settings-link" id="wp-sandbox-design-link">Design</span>
	</div>
</div>

<div id="wp-sandbox-access-list-page">
	<h2 class="wp-sandbox-settings-heading">Private URL</h2>
	<h3 class="wp-sandbox-settings-sub-heading">Share this URL with anyone who should have access to your site.</h3>

	<div class="wp-sandbox-panel">
		<h4 class="wp-sandbox-panel-title">Share URL</h4>

		<input type="text" class="wp-sandbox-share-url" name="wp-sandbox-share-url" id="wp-sandbox-share-url" value="<?php echo $previewURL; ?>" readonly/>

		<button id="wp-sandbox-regenerate-url" class="button button-primary">Regenerate URL</button>

		<p>NOTE: Any user with this URL will be able to access the site unless the URL is regenerated.</p>
	</div>

	<h2 class="wp-sandbox-settings-heading">Allow by IP address</h2>
	<h3 class="wp-sandbox-settings-sub-heading">Allow a single IP address, or an entire network.</h3>

	<div class="wp-sandbox-panel" id="wp-sandbox-add-access-container">
		<h4 class="wp-sandbox-panel-title">Current Access List</h4>

		<p>You can add an IP address, IP range, or a subnet by using the input box above. The following methods are supported: </p>

		<div>
			<span class="access-example">Single IP Address: </span><span class="access-example-format">192.168.1.100</span><br>
			<span class="access-example">IP Address Range: </span><span class="access-example-format">192.168.1.100-192.168.1.199</span><br>
			<span class="access-example">Network Address: </span><span class="access-example-format">192.168.1.0/24</span><br>

			<input type="text" class="wp-sandbox-new-access-rule" name="wp-sandbox-new-access-rule" id="wp-sandbox-new-access-rule" placeholder="IP Address, Range, or Network"/>

			<select id="wp-sandbox-access-rule-expires" name="wp-sandbox-access-rule-expires">
				<option value="">Expires</option>
				<option value="day">Day</option>
				<option value="week">Week</option>
				<option value="twoweeks">Two Weeks</option>
				<option value="never">Never</option>
			</select>

			<button id="wp-sandbox-add-access-rule" class="button button-primary">Add Access Rule</button>
		</div>
		<div id="wp-sandbox-access-rule-validation" class="validation">Please enter a valid access rule.</div>
	</div>

	<div class="wp-sandbox-rules-table-container">
		<table id="wp-sandbox-access-rule-table">
			<thead>
				<tr>
					<th>Type</th>
					<th>Network/IPs</th>
					<th>Added By</th>
					<th>Expires</th>
					<th></th>
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
							<td><a class="wp-sandbox-remove-access-rule" data-attr-type="user" data-attr-id="<?php echo $authenticatedUser['id']; ?>">Remove Access</a></td>
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
							<td><a class="wp-sandbox-remove-access-rule" data-attr-type="single" data-attr-id="<?php echo $ip['id']; ?>">Remove Access</a></td>
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
							<td><a class="wp-sandbox-remove-access-rule" data-attr-type="range" data-attr-id="<?php echo $ipRange['id']; ?>">Remove Access</a></td>
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
							<td><a class="wp-sandbox-remove-access-rule" data-attr-type="subnet" data-attr-id="<?php echo $subnet['id']; ?>">Remove Access</a></td>
						</tr>
				<?php } ?>
			</tbody>
		</table>
	</div>
</div>

<div id="wp-sandbox-design-page">
	<h2 class="wp-sandbox-settings-heading">Customize Design</h2>
	<h3 class="wp-sandbox-settings-sub-heading">Manage how the landing page looks for unauthorized visitors.</h3>

	<div class="wp-sandbox-panel">
		<div id="wp-sandbox-coming-soon-template-preview">
			<div id="wp-sandbox-coming-soon-template-container">
				<img id="wp-sandbox-coming-soon-template-preview-logo" src="<?php echo $settings[0]['logo'] != '' ? $settings[0]['logo'] : ''; ?>"/>

				<h2 id="wp-sandbox-coming-soon-template-preview-header"><?php echo trim( $settings[0]['main_title'] ) != '' ? $settings[0]['main_title'] : 'This awesome site is coming soon!'; ?></h2>
				<h3 id="wp-sandbox-coming-soon-template-preview-sub-header"><?php echo trim( $settings[0]['sub_title'] ) != '' ? $settings[0]['sub_title'] : 'Please excuse the dust, we will be launching soon.'; ?></h3>

				<div id="wp-sandbox-social-icon-container">
					<img id="twitter" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/twitter.svg" <?php echo trim( $settings[0]['twitter_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="instagram" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/instagram.svg" <?php echo trim( $settings[0]['instagram_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="google-plus" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/google-plus.svg" <?php echo trim( $settings[0]['google_plus_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="dribbble" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/dribbble.svg" <?php echo trim( $settings[0]['dribbble_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="vimeo" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/vimeo.svg" <?php echo trim( $settings[0]['vimeo_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="youtube" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/youtube.svg" <?php echo trim( $settings[0]['youtube_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
					<img id="facebook" src="<?php echo plugins_url(); ?>/wp-sandbox/admin/images/facebook.svg" <?php echo trim( $settings[0]['facebook_url'] ) != '' ? 'style="display: inline-block;"' : ''; ?>/>
				</div>

				<span id="wp-sandbox-coming-soon-template-preview-login" <?php echo $settings[0]['show_login_link'] == '0' ? 'style="display: none;"' : ''; ?>>Do you have exclusive access? <a href="">Login here.</a></span>
			</div>
		</div>
		<h2 class="wp-sandbox-settings-heading">Design & Content</h2>

		<div class="design-form-row">
			<label>Logo</label>
			<input type="text" name="wp-sandbox-design-logo" id="wp-sandbox-design-logo" value="<?php echo $settings[0]['logo']; ?>"/>
			<input id="upload-image-button" type="button" class="button" value="Upload Image" />
			<input type="hidden" name="wp-sandbox-image-attachment-url" id="wp-sandbox-image-attachment-url" value="">
			<div id="image-preview-wrapper" <?php echo $settings[0]['logo'] == '' ? 'style="display: none;"' : '' ?>>
				<img id="image-preview" src="<?php echo $settings[0]['logo'] == '' ? '' : $settings[0]['logo']; ?>" height="100" style="max-height: 100px;">
			</div>
		</div>

		<div class="design-form-row">
			<label>Main Title</label>
			<input type="text" name="wp-sandbox-design-main-title" id="wp-sandbox-design-main-title" value="<?php echo $settings[0]['main_title']; ?>"/>
		</div>

		<div class="design-form-row">
			<label>Sub Title</label>
			<input type="text" name="wp-sandbox-design-sub-title" id="wp-sandbox-design-sub-title" value="<?php echo $settings[0]['sub_title']; ?>"/>
		</div>

		<div class="design-form-row">
			<label>Show Login Link?</label>
			<div id="wp-sandbox-show-login-link-switch-container">
				<input type="checkbox" name="wp-sandbox-show-login-switch" class="wp-sandbox-show-login-switch-checkbox" id="wp-sandbox-show-login-switch-checkbox" <?php echo $settings[0]['show_login_link'] == 1 ? 'checked' : '' ?>>
				<label class="wp-sandbox-show-login-switch-label" for="wp-sandbox-show-login-switch-checkbox">
				<span class="wp-sandbox-show-login-switch-inner"></span>
				<span class="wp-sandbox-show-login-switch-switch"></span>
			</div>
		</div>

		<div class="design-form-row">
			<label>Background Color 1</label>
			<div id="background-color-1">
				<img src="<?php echo plugins_url().'/wp-sandbox/admin/images/select.png'; ?>"/>
			</div>
		</div>

		<div class="design-form-row">
			<label>Background Color 2</label>
			<div id="background-color-2">
				<img src="<?php echo plugins_url().'/wp-sandbox/admin/images/select.png'; ?>"/>
			</div>
		</div>

		<h2 class="wp-sandbox-settings-heading">Links to Social Profiles</h2>

		<div class="design-form-row">
			<label>Twitter</label>
			<input type="text" name="wp-sandbox-design-twitter" id="wp-sandbox-design-twitter" value="<?php echo $settings[0]['twitter_url']; ?>"/>
			<div id="twitter-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>Facebook</label>
			<input type="text" name="wp-sandbox-design-facebook" id="wp-sandbox-design-facebook" value="<?php echo $settings[0]['facebook_url']; ?>"/>
			<div id="facebook-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>Google Plus</label>
			<input type="text" name="wp-sandbox-design-google-plus" id="wp-sandbox-design-google-plus" value="<?php echo $settings[0]['google_plus_url']; ?>"/>
			<div id="google-plus-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>Instagram</label>
			<input type="text" name="wp-sandbox-design-instagram" id="wp-sandbox-design-instagram" value="<?php echo $settings[0]['instagram_url']; ?>"/>
			<div id="instagram-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>Vimeo</label>
			<input type="text" name="wp-sandbox-design-vimeo" id="wp-sandbox-design-vimeo" value="<?php echo $settings[0]['vimeo_url']; ?>"/>
			<div id="vimeo-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>Dribbble</label>
			<input type="text" name="wp-sandbox-design-dribbble" id="wp-sandbox-design-dribbble" value="<?php echo $settings[0]['dribbble_url']; ?>"/>
			<div id="dribbble-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-form-row">
			<label>YouTube</label>
			<input type="text" name="wp-sandbox-design-youtube" id="wp-sandbox-design-youtube" value="<?php echo $settings[0]['youtube_url']; ?>"/>
			<div id="youtube-url-validation" class="validation">The text entered is not a valid URL.</div>
		</div>

		<div class="design-settings-updated">
			Design Settings Updated Successfully!
		</div>
		<button id="wp-sandbox-update-design" class="button button-primary">Save Changes</button>
	</div>
</div>

<script type="text/javascript">
	var settings = <?php echo json_encode( $settings[0] ); ?>;
</script>

/*
	Enables and disables the plugin
*/
jQuery(function(){
    jQuery("ul#wps-enable-disable-switch li").click(function(){
        jQuery("ul#wps-enable-disable-switch li").removeClass("on");
        jQuery(this).addClass("on"); 

        if(jQuery(this).attr('data-setting') == 'on'){
			var data = {
				action: 'wps_enable_plugin',
				enabled: '1'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){
				jQuery('.wps-disable-banner').hide();
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').removeClass('wps-admin-bar-disabled');
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').addClass('wps-admin-bar-enabled');
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification .ab-item').html('WP Sandbox Enabled');

			});
        }else{
        	var data = {
				action: 'wps_enable_plugin',
				enabled: '0'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){
				jQuery('.wps-disable-banner').show();
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').removeClass('wps-admin-bar-enabled');
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').addClass('wps-admin-bar-disabled');
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification .ab-item').html('WP Sandbox Disabled');
			});
        }
    });
});

/*
	Enables and disables CloudFlare
*/
jQuery(function(){
	jQuery("ul#wps-cloud-flare-enable-disable-switch li").click(function(){
		jQuery("ul#wps-cloud-flare-enable-disable-switch li").removeClass("on");
		jQuery(this).addClass("on");

		if(jQuery(this).attr('data-setting') == 'on'){
			var data = {
				action: 'wps_enable_cloud_flare',
				cloud_flare_enabled: '1'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){

			});
		}else{
			var data = {
				action: 'wps_enable_cloud_flare',
				cloud_flare_enabled: '0'
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){

			});
		}
	});
});

jQuery(document).ready(function(){
	jQuery('#wps-network-enable-all-checkboxes').click(function(){
		if(jQuery('#wps-network-enable-all-checkboxes').is(':checked')){
			jQuery('input[type=checkbox]').prop('checked', 'checked');
		}else{
			jQuery('input[type=checkbox]').prop('checked', '');
		}
	});
});

function wps_network_enable(){
	var wps_enable_blogs = []
	jQuery('input[name=wps-network-enable-blog').each(function() {
		if(jQuery(this).is(':checked')){
      		wps_enable_blogs.push(jQuery(this).attr('data-attr-blog-id'));
      	}
    });
    
    var data = {
    	action: 'wps_network_enable_blogs',
    	enabled_sites: wps_enable_blogs
    }

    jQuery.ajax({
    	type: 'POST',
    	url: ajaxurl,
    	data: data,
    	async: false
    }).done(function(response){
    	jQuery('#wps-network-enable-alert').html('Sites Enabled!');
    	jQuery('#wps-network-enable-alert').show();
    	jQuery('#wps-network-enable-alert').fadeOut(3000);
    });
}
function wps_network_display_site_status_tab(){
	jQuery('#wps-network-site-status-tab').removeClass('wps-network-admin-tab-inactive');
	jQuery('#wps-network-network-access-tab').addClass('wps-network-admin-tab-inactive');

	jQuery('#wps-network-site-status-tab-display').show();
	jQuery('#wps-network-access-tab-display').hide();
}
function wps_network_display_network_access_tab(){
	jQuery('#wps-network-site-status-tab').addClass('wps-network-admin-tab-inactive');
	jQuery('#wps-network-network-access-tab').removeClass('wps-network-admin-tab-inactive');

	jQuery('#wps-network-site-status-tab-display').hide();
	jQuery('#wps-network-access-tab-display').show();
}
function wps_save_settings(){
	var data = {
		action: 'wps_save_admin_settings',
		default_page: jQuery('#wps-default-page').val(),
		default_expire_time: jQuery('#wps-default-expire-time').val()
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-settings-saved').show();
		}
	});
}
/* 
	Saves the default page settings through AJAX
*/
function wps_save_default_page_setting(){
	var data = {
		action: 'wps_save_admin_settings',
		default_page: jQuery('#wps-default-page').val(),
		setting: 'default_page'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false

	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-settings-saved').show();
			jQuery('#wps-settings-saved').fadeOut(3000);
			wps_reload_access_table();
		}
	});
}
/*
	Removes a valid user through AJAX
*/
function wps_remove_user(user_id, ip){
	var wps_remove_user_confirm = confirm("Are you sure you want to remove this user?");

	if(wps_remove_user_confirm == true){
		var data = {
			action: 'wps_remove_user',
			wps_user_id: user_id,
			wps_ip: ip
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_access_table();
		})
	}
}
function wps_network_remove_user(blog_id, user_id, ip){
	var wps_remove_user_confirm = confirm("Are you sure you want to remove this user?");

	if(wps_remove_user_confirm == true){
		var data = {
			action: 'wps_network_remove_user',
			wps_user_id: user_id,
			wps_ip: ip,
			wps_blog: blog_id
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_network_access_table();
		})
	}
}
function wps_reload_access_table(){
	var data = {
		action: 'wps_reload_access_table'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-access-table-body').html(response);
	});
}
function wps_reload_network_access_table(){
	var data = {
		action: 'wps_reload_network_access_table'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-network-global-access-table-body').html(response);
	});
}

/*
	Saves the default expiration time for users
*/
function wps_save_default_expire_time(){
	var data = {
		action: 'wps_save_admin_settings',
		default_expire_time: jQuery('#wps-default-expire-time').val(),
		setting: 'default_expire_time'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		if(response == 'true'){
			jQuery('#wps-settings-saved').show();
			jQuery('#wps-settings-saved').fadeOut(3000);
			wps_reload_access_table();
		}
	})
}

/*
	Allows a static IP
*/
function wps_allow_ip(){
	if(wps_check_valid_ip(jQuery('#wps-allowed-ip').val())) {
		jQuery('#wps-allowed-ip').css('border', '1px solid green');
		var data = {
			action: 'wps_allow_ip',
			ip: jQuery('#wps-allowed-ip').val(),
			expires: jQuery('#wps-add-ip-address-expiration').val()
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			if(response == 'true'){
				jQuery('#wps-ip-added-alert').html('IP Address Added');
				jQuery('#wps-ip-added-alert').removeClass('error');
				jQuery('#wps-ip-added-alert').addClass('updated');
				jQuery('#wps-ip-added-alert').show();
				jQuery('#wps-ip-added-alert').fadeOut(3000);
				wps_reload_access_table();
			}
			if(response == 'false'){
				jQuery('#wps-ip-added-alert').html('IP Already Exists');
				jQuery('#wps-ip-added-alert').removeClass('updated');
				jQuery('#wps-ip-added-alert').addClass('error');
				jQuery('#wps-ip-added-alert').show();
				jQuery('#wps-ip-added-alert').fadeOut(3000);
			}

			if(response != 'false' && response != 'true'){
				jQuery('#wps-ip-added-alert').html(response);
				jQuery('#wps-ip-added-alert').removeClass('updated');
				jQuery('#wps-ip-added-alert').addClass('error');
				jQuery('#wps-ip-added-alert').show();
				jQuery('#wps-ip-added-alert').fadeOut(3000);
			}
		});
	}else{
		jQuery('#wps-allowed-ip').css('border', '1px solid red');
		jQuery('#wps-ip-added-alert').html('IP Invalid');
		jQuery('#wps-ip-added-alert').removeClass('updated');
		jQuery('#wps-ip-added-alert').addClass('error');
		jQuery('#wps-ip-added-alert').show();
		jQuery('#wps-ip-added-alert').fadeOut(3000);
	}
}

function wps_check_valid_ip(ip_address){
	var ip_parts = ip_address.split('.');
	if(ip_parts.length == 4){
		if((ip_parts[0] >= 0) && (ip_parts[0] <= 255)){
			if((ip_parts[1] >= 0) && (ip_parts[1] <= 255)){
				if((ip_parts[2] >= 0) && (ip_parts[2] <= 255)){
					if((ip_parts[3] >= 0) && (ip_parts[3] <= 255)){
						return true;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}else{
				return false;
			}
		}else{
			return false;
		}
	}else{
		return false;
	}
}

function wps_check_valid_range(ip_start, ip_end){
	var ip_start_parts = ip_start.split('.');
	var ip_end_parts = ip_end.split('.');
	if(ip_end_parts[0] > ip_start_parts[0]){
		return true;
	}else{
		if((ip_end_parts[0] == ip_start_parts[0]) && (ip_end_parts[1] > ip_start_parts[1])){
			return true;
		}else{
			if((ip_end_parts[0] == ip_start_parts[0]) && (ip_end_parts[1] == ip_start_parts[1]) && (ip_end_parts[2] > ip_start_parts[2])){
				return true;
			}else{
				if((ip_end_parts[0] == ip_start_parts[0]) && (ip_end_parts[1] == ip_start_parts[1]) && (ip_end_parts[2] == ip_start_parts[2]) && (ip_end_parts[3] > ip_start_parts[3])){
					return true;
				}else{
					return false;
				}
			}
		}
	}
}

function wps_check_valid_subnet(subnet){
	if((subnet >= 0) && (subnet <= 32) && (subnet != '')){
		return true;
	}else{
		return false;
	}
}
/*
	Updates a valid preview hash
*/
function wps_update_preview_hash(){
	var data = {
		action: 'wps_generate_preview_hash_url'
	}
	jQuery.ajax({
		type: 'POST',
		url: ajaxurl,
		data: data,
		async: false
	}).done(function(response){
		jQuery('#wps-share-url').val(response);
		wps_reload_access_table();
	});
}


/*
	Saves IP Ranges
*/
function wps_add_ip_range(){
	var start = jQuery('#wps-ip-range-start').val();
	var end = jQuery('#wps-ip-range-end').val();
	var expire_time = jQuery('#wps-add-ip-range-address-expiration').val();
	if(wps_check_valid_ip(start)){
		jQuery('#wps-ip-range-start').css('border', '1px solid green');
		if(wps_check_valid_ip(end)){
			jQuery('#wps-ip-range-end').css('border', '1px solid green');
			if(wps_check_valid_range(start, end)){
				var data = {
					action: 'wps_save_ip_ranges',
					start_range: start,
					end_range: end,
					expires: expire_time
				}
				jQuery.ajax({
					type: 'POST',
					url: ajaxurl,
					data: data,
					async: false
				}).done(function(response){
					jQuery('#wps-ip-range-alert').html('IP Range Added!');
					jQuery('#wps-ip-range-alert').show();
					jQuery('#wps-ip-range-alert').fadeOut(3000);
					wps_reload_access_table();
				});
			}else{
				jQuery('#wps-ip-range-start').css('border', '1px solid red');
				jQuery('#wps-ip-range-end').css('border', '1px solid red');

				jQuery('#wps-ip-range-alert').html('Invalid Range');
				jQuery('#wps-ip-range-alert').removeClass('updated');
				jQuery('#wps-ip-range-alert').addClass('error');
				jQuery('#wps-ip-range-alert').show();
				jQuery('#wps-ip-range-alert').fadeOut(3000);
			}
		}else{
			jQuery('#wps-ip-range-end').css('border', '1px solid red');
			jQuery('#wps-ip-range-alert').html('IP Invalid');
			jQuery('#wps-ip-range-alert').removeClass('updated');
			jQuery('#wps-ip-range-alert').addClass('error');
			jQuery('#wps-ip-range-alert').show();
			jQuery('#wps-ip-range-alert').fadeOut(3000);
		}
	}else{
		jQuery('#wps-ip-range-start').css('border', '1px solid red');
		jQuery('#wps-ip-range-alert').html('IP Invalid');
		jQuery('#wps-ip-range-alert').removeClass('updated');
		jQuery('#wps-ip-range-alert').addClass('error');
		jQuery('#wps-ip-range-alert').show();
		jQuery('#wps-ip-range-alert').fadeOut(3000);
	}
}

/*
	Removes an IP Range
*/
function wps_remove_range(start_ip, end_ip){
	var wps_remove_range_confirm = confirm("Are you sure you want to remove this IP Range?");

	if(wps_remove_range_confirm == true){
		var data = {
			action: 'wps_delete_ip_range',
			start: start_ip,
			end: end_ip
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_access_table();
		});
	}
}

function wps_network_remove_range(blog, start_ip, end_ip){
	var wps_remove_range_confirm = confirm("Are you sure you want to remove this IP Range?");

	if(wps_remove_range_confirm == true){
		var data = {
			action: 'wps_network_delete_ip_range',
			start: start_ip,
			end: end_ip,
			blog_id: blog
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_network_access_table();
		});
	}
}

/*
	Saves Subnets
*/
function wps_add_network(){
	if(wps_check_valid_ip(jQuery('#wps-subnet-network').val())){
		jQuery('#wps-subnet-network').css('border', '1px solid green');
		if(wps_check_valid_subnet(jQuery('#wps-subnet-network-subnet').val())){
			jQuery('#wps-subnet-network-subnet').css('border', '1px solid green');
			var data = {
				action: 'wps_save_subnets',
				ip: jQuery('#wps-subnet-network').val(),
				subnet: jQuery('#wps-subnet-network-subnet').val(),
				expiration: jQuery('#wps-add-network-expiration').val()
			}
			jQuery.ajax({
				type: 'POST',
				url: ajaxurl,
				data: data,
				async: false
			}).done(function(response){
				jQuery('#wps-subnet-alert').html('Subnet Added!');
				jQuery('#wps-subnet-alert').show();
				jQuery('#wps-subnet-alert').fadeOut(3000);
				wps_reload_access_table();
			});
		}else{
			jQuery('#wps-subnet-network-subnet').css('border', '1px solid red');
			jQuery('#wps-subnet-alert').html('Subnet Invalid');
			jQuery('#wps-subnet-alert').removeClass('updated');
			jQuery('#wps-subnet-alert').addClass('error');
			jQuery('#wps-subnet-alert').show();
			jQuery('#wps-subnet-alert').fadeOut(3000);
		}
	}else{
		jQuery('#wps-subnet-network').css('border', '1px solid red');
		jQuery('#wps-subnet-alert').html('IP Invalid');
		jQuery('#wps-subnet-alert').removeClass('updated');
		jQuery('#wps-subnet-alert').addClass('error');
		jQuery('#wps-subnet-alert').show();
		jQuery('#wps-subnet-alert').fadeOut(3000);
	}
}

/*
	Removes a Subnet
*/
function wps_remove_subnet(ip, subnet){
	var wps_remove_subnet_confirm = confirm("Are you sure you want to remove this network?");

	if(wps_remove_subnet_confirm == true){
		var data = {
			action: 'wps_remove_subnet',
			start_ip: ip,
			subnet_extension: subnet
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_access_table();
		});
	}
}
function wps_network_remove_subnet(blog, ip, subnet){
	var wps_remove_subnet_confirm = confirm("Are you sure you want to remove this network?");

	if(wps_remove_subnet_confirm == true){
		var data = {
			action: 'wps_network_remove_subnet',
			start_ip: ip,
			subnet_extension: subnet,
			blog_id: blog
		}
		jQuery.ajax({
			type: 'POST',
			url: ajaxurl,
			data: data,
			async: false
		}).done(function(response){
			wps_reload_network_access_table();
		});
	}
}
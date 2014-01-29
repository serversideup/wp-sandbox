/*
	Enables and disables the plugin
*/
jQuery(function(){
    jQuery("ul.wps-toggle li").click(function(){
        jQuery("ul.wps-toggle li").removeClass("on");
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
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').show();
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
				jQuery('#wp-admin-bar-wps-sandbox-admin-bar-notification').hide();
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
function wps_network_remove_user(blog_id, user_id, ip){
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
	})
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
}

/*
	Removes an IP Range
*/
function wps_remove_range(start_ip, end_ip){
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

function wps_network_remove_range(blog, start_ip, end_ip){
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

/*
	Saves Subnets
*/
function wps_add_network(){
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
}

/*
	Removes a Subnet
*/
function wps_remove_subnet(ip, subnet){
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
function wps_network_remove_subnet(blog, ip, subnet){
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
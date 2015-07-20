## Overview
WP-Sandbox allows Wordpress developers and designers to block access to their site during the site's construction.  WP-Sandbox enables the user to allow access to their site by IP address, IP Ranges, Networks, and a Preview URL.  Users who have an account on Wordpress automatically have their IP whitelisted allowing them to view the site during development WITHOUT being logged in AND while blocking the public.  WP-Sandbox is great for development teams to keep their clients in the loop on the progress without giving full access to the public before the site is ready for launch.  It also helps with UX testing to see what the site would look like without being logged in. You can also allow access for callbacks from APIs if you know thier IP or Network making development really easy to develop while blocking out the average user.

NOTE: Version 1.0 of WP-Sandbox does NOT support IPv6

## Installation
All you have to do is activate the plugin in your Wordpress install and the plugin will determine what type of install Wordpress is and create the tables.
Once installed, you will see a link on the admin bar where you can adjust your settings and allow access via access rules. If your install is multisite, you will see a different link on the Network Admin view on the left bar.  This allows you to enable and disable sites in bulk.  You can also delete access rules for all of the sites on your network.  NOTE: All settings and access rules are specific to the site and are managed independantly allowing for full control by the admin. On a single site install there is only one view to manage settings and access rules.

## Settings
This screen allows you to manage all of the settings for your site.

### Public Access
*DEFAULT: Allowed (Public can view the site)*

You can have this plugin installed and allow public access to the site.  If this is set to 'Allowed', the public will be able to access the site, essentially by-passing any functionality from the plugin. If this is set to 'Blocked', all requests get filtered for access by the plugin using the defined access rules for the site.

### Default Page
*DEFAULT: 404 (Uses the 404 HTTP code and shows the 404 page template)*

If a user is NOT granted access to your site, this page will show. The dropdown consists of all pages your site contains. It also includes a 404 option which finds the 404 page template and displays that.  This allows you to customize a page possibly with the theme you are developing that explains to the user that the site is under construction.

### Default Expiration
*DEFAULT: Never (Access rules never expire by default)*

The default expiration is how long each access rule has to live.  If it's never, the access rule will never expire.  If default expiration is a day then you can add an access rule and it will be granted access for 1 day. After that day is up, it will be removed from the database and anyone with that would have fit that access rule the day before would not be granted access.

## Access Rules
There are 5 different ways to add access rules. The Preview URL is the only one that doesn't have a set expiration time.  However if you regenerate the Preview URL it will block access to anyone who previously had access. From the 'Access' page the user is able to copy the Preview URL and regenerate it if necessary. They can also add and delete access rules. With IP access rules, CloudFlare works right out of the box! If you implement CloudFlare during the development stage, WP-Sandbox grabs the referring IP (the specific IP to the user) and uses that to determine access.

### Preview URL
This URL can be copied and sent to whoever you want to have access to the front end of the site.  When the user views this URL it sets a cookie with the preview ID and gives that user access to view the site.  When the Preview URL is regenerated this cookie becomes invalid and the person using the existing URL will be blocked out of the site.  The cookie is set to last forever (technically 10 years) so unless the URL is regenerated the cookie will still work.

### User Access
If a user has admin access, their IP is snatched upon login and stored.  It is assumed that anyone working on the site can visit the site while not logged in (since they can already visit it with their account).

### Access per IP
You can add a spcific IP to have access to the site during development.  If a user hits the site with this IP, they will be able to view the site. To add an IP just enter a single IP in the Access Rule box and click 'Add Access Rule'. WP-Sandbox is smart enough to determine the type.

### Access per IP Range
As an admin you can also add a range of IP addresses.  Like the single IP, if a user comes to your site with an IP in the range of IP Addresses they will be granted access.  To add an IP Range, just add an Access Rule formatted like: {Start IP}-{End IP} and WP-Sandbox can determine the type.

### Access per Network
If you want to add access to an entire network (say the network of the company you are building the site for), you can do that as well and any IP coming from that network will be granted access. To add a network, just add an Access Rule in CIDR notation (formatted as {IP}/{Subnet Mask} or 192.168.1.0/24) and WP-Sandbox will grant access to that network.

## Multisite Features
WP-Sandbox also supports multisite functionality allowing admins to allow access to selected sites.  Each site's settings and access rules are specific to that site allowing it to be managed independantly of others. Each site will get the same display as a single site install and will operate the same. The Network Admin will gain the functionality to Enable/Disable any sites and remove certain access rules.

### Enabling/Disabling Sites
As the Network Admin you can enable and disable any site. To do this, navigate to the network administration screen and under the WP-Sandbox link, you can check all of the sites you want to enable or uncheck all the sites you want to disable and click "Save Changes".  This will enable and disable the sites you wish.

### Removing Network Access Rules
You can also remove any rule from any site in the Network Admin menu. This functionality gives you full control over who can access what parts of your network. If you want to add rules, navigate to the dashboard of the site you want to add the rule to and you can add the appropriate rule.
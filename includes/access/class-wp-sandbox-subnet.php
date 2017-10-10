<?php

/**
 * Handles the CRUD for Subnets
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * Handles the CRUD for Subnets
 *
 * All CRUD methods for Subnets are in this class including checking if an
 * Subnets is in a valid range.
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes/access
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Subnet{
	/**
	 * Retrieves all current subnets
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function get_subnets(){
		global $wpdb;

		/*
			If the site install is multi site, we get the
			Subnets for the specific site.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			/*
				Switch to the top level blog to query the table.
			*/
			global $switched;
			switch_to_blog(1);

			/*
				Retrieves all of the subnets with
				respect to the current site.
			*/
			$subnets = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM ".$wpdb->prefix."wps_subnets
				WHERE blog_id = '%d'",
				$currentBlogID
			), ARRAY_A );

			restore_current_blog();
		}else{
			/*
				Retrieves all of the subnets.
			*/
			$subnets = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_subnets", ARRAY_A );
		}

		return $subnets;
	}

	/**
	 * Adds a Subnet
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		string 		$startIP 			The beginning IP of the network
	 * @var 	   		string 		$network			The network being added
	 * @var 	   		string 		$expiration 	The time the rule expires
	 * @var 	   		int 			$userID 			The ID of the user adding the range.
	 */
	public static function add_subnet( $ip, $network, $expiration, $userID ){
		global $wpdb;

		/*
			Checks if it's multisite and grabs
			the blog ID if needed.
		*/
		if( is_multisite() ){
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Clenses and adds the IP Network
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_subnets
				( blog_id, added_by, start_ip, subnet, expires )
				VALUES ( %d, %d, %s, %s, '".$expiration."')",
				$currentBlogID,
				$userID,
				$ip,
				$network
			) );

			/*
				Gets the ID of the subnet
			*/
			$subnetID = $wpdb->insert_id;

			/*
				Restores the current blog
			*/
			restore_current_blog();
		}else{
			/*
				Clenses and adds the IP Network
				to the database.
			*/
			$wpdb->query( $wpdb->prepare(
				"INSERT INTO ".$wpdb->prefix."wps_subnets
				( added_by, start_ip, subnet, expires )
				VALUES ( %d, %s, %s, '".$expiration."')",
				$userID,
				$ip,
				$network
			) );

			/*
				Gets the ID of the subnet
			*/
			$subnetID = $wpdb->insert_id;
		}

		/*
			Returns the newly added Subnet.
		*/
		return $subnetID;
	}

	/**
	 * Deletes a subnet
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		int			$subnetID 		The ID of the subnet being deleted
	 */
	public static function delete_subnet( $subnetID ){
		global $wpdb;

		/*
			If multisite, we switch to the top level blog.
		*/
		if( is_multisite() ){
			global $switched;
			switch_to_blog(1);

			/*
				Delets the Subnet by ID
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_subnets
				WHERE id = '%d'",
				$subnetID
			) );

			restore_current_blog();
		}else{
			/*
				Delets the Subnet by ID
			*/
			$wpdb->query( $wpdb->prepare(
				"DELETE FROM ".$wpdb->prefix."wps_subnets
				WHERE id = '%d'",
				$subnetID
			) );
		}
	}

	/**
	 * Checks to see if an IP is in an allowed subnet
	 *
	 * @since      	1.0.0
	 * @access     	public
	 * @var 	   		int			$ipAddress 		The ip address being tested.
	 */
	public static function check_valid_ip_subnet( $ipAddress ){
		global $wpdb;

		/*
			Checks if the site is multisite.
		*/
		if( is_multisite() ){
			/*
				Gets the current blog ID
			*/
			$currentBlogID = get_current_blog_id();

			global $switched;
			switch_to_blog(1);

			/*
				Grabs allowed subnets from the
				current blog.
			*/
			$subnets = $wpdb->get_results( $wpdb->prepare(
				"SELECT *
				 FROM ".$wpdb->prefix."wps_subnets
				 WHERE blog_id = '%d'",
				 $currentBlogID
			), ARRAY_A );

			/*
				Restores the current blog
			*/
			restore_current_blog();
		}else{
			/*
				Gets all of the subnets
			*/
			$subnets = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix."wps_subnets", ARRAY_A );
		}

		/*
			Iterates through subnets and checks to see
			if the IP address is within the range.
		*/
		foreach( $subnets as $subnet ){
			/*
				Divides into subnet parts
			*/
			$subnetParts 	= explode( '.', $subnet['start_ip'] );

			/*
				Define octets 1 - 4
			*/
			$firstOctet 	= $subnetParts[0];
			$secondOctet 	= $subnetParts[1];
			$thirdOctet 	= $subnetParts[2];
			$fourthOctet 	= $subnetParts[3];

			/*
				Initialize new octets
			*/
			$newFirstOctet 	= '';
			$newSecondOctet	= '';
			$newThirdOctet 	= '';
			$newFourthOctet = '';

			/*
				Checks from the largest networks
				first down to the smallest.
			*/
			if( $subnet['subnet'] <= 8 ){
				if( ( $firstOctet + $subnet['subnet'] ) > 255 ){
					$newFirstOctet = 255;
				}else{
					$newFirstOctet = $firstOctet + $subnet['subnet'];
				}

				/*
					Get the integer values for the subnet
				*/
				$min 	= ip2long( $subnet['start_ip'] );
				$max 	= ip2long( $newFirstOctet.'.255.255.254' );
				$needle = ip2long( $ipAddress );

				/*
					Returns true if the IP is in a range.
				*/
				if( ( $needle >= $min ) AND ( $needle <= $max ) ){
					return true;
				}
			}else if( ( $subnet['subnet'] > 8 ) && ( $subnet['subnet'] <= 16 ) ){
				/*
					Check the second Octet
				*/
				if( ( $secondOctet + $subnet['subnet'] ) > 255 ){
					$newScondOctet = ( $secondOctet + $subnet['subnet'] ) - 255;
					$newFirstOctet = $firstOctet + 1;
				}else{
					$newFirstOctet = $firstOctet;
					$newSecondOctet = $secondOctet + $subnet['subnet'];
				}

				/*
					Get the integer values for the subnet
				*/
				$min    = ip2long( $subnet['start_ip'] );
				$max    = ip2long( $newFirstOctet.'.255.255.254' );
				$needle = ip2long( $ipAddress );

				/*
					Returns true if the IP is in a range.
				*/
				if( ( $needle >= $min ) AND ( $needle <= $max ) ){
					return true;
				}
			}else if( ( $subnet['subnet'] > 16 ) && ( $subnet['subnet'] <= 24 ) ){
				/*
					Check the third Octet
				*/
				if( ( $thirdOctet + $subnet['subnet'] ) > 255 ){
					$newThirdOctet 	= ( $thirdOctet + $subnet['subnet'] ) - 255;
					$newSecondOctet = $secondOctet + 1;
					$newFirstOctet 	= $firstOctet;
				}else{
					$newSecondOctet = $secondOctet;
					$newThirdOctet 	= $thirdOctet + $subnet['subnet'];
					$newFirstOctet 	= $firstOctet;
				}

				/*
					Get the integer values for the subnet
				*/
				$min    = ip2long( $subnet['start_ip'] );
        $max    = ip2long( $newFirstOctet.'.255.255.254' );
        $needle = ip2long( $ipAddress );

        /*
					Returns true if the IP is in a range.
				*/
    		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
    			return true;
    		}
			}else if( $subnet['subnet'] > 24 ){
				/*
					Check the fourth Octet
				*/
				if( ( $fourthOctet + $subnet['subnet'] ) > 255 ){
					$newFourthOctet = ( $fourthOctet + $subnet['subnet'] ) - 255;
					$newThirdOctet 	= $thirdOctet + 1;
					$newSecondOctet = $secondOctet;
					$newFirstOctet 	= $firstOctet;
				}else{
					$newFourthOctet = $fourthOctet + $subnet['subnet'];
					$newSecondOctet = $secondOctet;
					$newThirdOctet 	= $thirdOctet;
					$newFirstOctet 	= $firstOctet;
				}

				/*
					Get the integer values for the subnet
				*/
				$min    = ip2long( $subnet['start_ip'] );
        $max    = ip2long( $newFirstOctet.'.255.255.254' );
        $needle = ip2long( $ipAddress );

        /*
					Returns true if the IP is in a range.
				*/
    		if( ( $needle >= $min ) AND ( $needle <= $max ) ){
    			return true;
    		}
			}
		}

		/*
			Only reachable if nothing was returned
			meaning that the IP is NOT in the subnet.
		*/
		return false;
	}

	/**
	 * Gets all network authenticated subnets. This is only called from a
	 * multisite instance so we know it's multisite.
	 *
	 * @since      1.0.0
	 * @access     public
	 */
	public static function get_network_authenticated_subnets(){
		global $wpdb;
		global $switched;

		/*
			Switch to the top level blog
		*/
		switch_to_blog(1);

		/*
			Gets the authenticated subnets for the sites
		*/
		$authenticatedSubnets = $wpdb->get_results(
			"SELECT *
			 FROM ".$wpdb->prefix."wps_subnets",
		ARRAY_A );

		/*
			Restores the current blog
		*/
		restore_current_blog();

		/*
			Returns the authenticated subnets
		*/
		return $authenticatedSubnets;
	}
}

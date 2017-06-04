<?php

/**
 * Handles the CRUD for the rules
 *
 * @link       https://521dimensions.com
 * @since      1.0.0
 *
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 */

/**
 * All rules are managed through this class. Handles all of the addition
 * and deletion of access rules
 *
 * @since      1.0.0
 * @package    WP_Sandbox
 * @subpackage WP_Sandbox/includes
 * @author     521 Dimensions <dan@521dimensions.com>
 */
class WP_Sandbox_Rules {
	/**
	 * Adds an access rule to the plugin.
	 *
	 * @since    1.0.0
	 * @access   public
	 */
	public function wp_sandbox_add_rule(){
		/*
			Defines the variables to pass to the function
			that would add to the database.
		*/
		$type 			= $_POST['type'];
		$rule 			= $_POST['rule'];
		$expiration 	= WP_Sandbox_Settings::get_expiration_time( $_POST['expiration'] );

		/*
			Gets the authenticated user
		*/
		$user 			= get_userdata( get_current_user_id() );

		/*
			Determines type and adds the
			rule.
		*/
		switch( $type ){
			case 'single':
				$ruleID = WP_Sandbox_IP::add_ip( $rule, $expiration, $user->ID );
			break;
			case 'range':
				$ipParts = explode( '-', $rule );

				$ruleID = WP_Sandbox_IP_Range::add_range( $ipParts[0], $ipParts[1], $expiration, $user->ID );
			break;
			case 'subnet':
				$subnetParts = explode( '/', $rule );

				$ruleID = WP_Sandbox_Subnet::add_subnet( $subnetParts[0], $subnetParts[1], $expiration, $user->ID );
			break;
		}

		/*
			Get the type of display which is the name of the rule.
		*/
		$typeDisplay 	= $this->get_type_display( $type );

		/*
			Returns the new information so it
			can be added to the table.
		*/
		wp_send_json( array( 
			'type' => $typeDisplay, 
			'rule' => $rule, 
			'expiration' => $expiration != '' ? date('m-d-Y H:i:s', strtotime( $expiration ) ) : 'Never', 
			'added_by' =>  $user->user_login,
			'rule_id' => $ruleID ) );

		die();
	}

	/**
	 * Returns the pretty name of the rule
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param 	 string 	$type 	The type of rule we are getting the display for.
	 */
	private function get_type_display( $type ){
		switch( $type ){
			case 'single':
				return 'Single IP';
			break;
			case 'range':
				return 'IP Range';
			break;
			case 'subnet':
				return 'Network';
			break;
		}
	}

	/**
	 * Removes a rule from the plugin
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	public function wp_sandbox_remove_rule(){
		/*
			Get the rule type and rule ID
		*/
		$type 		= $_POST['type'];
		$ruleID 	= $_POST['rule'];

		/*
			Calls appropriate remove function for
			the type of rule.
		*/
		switch( $type ){
			case 'user':
				WP_Sandbox_Authenticated_Users::delete_authenticated_user( $ruleID );
			break;
			case 'single':
				WP_Sandbox_IP::delete_ip( $ruleID );
			break;
			case 'range':
				WP_Sandbox_IP_Range::delete_range( $ruleID );
			break;
			case 'subnet':
				WP_Sandbox_Subnet::delete_subnet( $ruleID );
			break;
		}

		/*
			Returns success when the rule is removed.
		*/
		wp_send_json( array(
			'success' => true
		) );
	}
}
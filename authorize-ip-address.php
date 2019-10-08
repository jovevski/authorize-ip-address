<?php
/*
 Plugin Name: Authorize IP Address
 Plugin URI: http://wordpress.org/extend/plugins/authorize-ip-address/
 Description: Allows users to setup IP whitelists for logging in and sends them an email with an activation key if the IP address is not on the whitelist.
 Version: 1.0.1
 Author: Toni Jovevski
 Author URI: http://www.finagg.com/
 License: GPL2
 */

/*  Copyright 2016  Toni Jovevski  (email : jovevskitoni@gmail.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

*/

$GLOBALS["wpaip_url"]=home_url( '/' );
define('WPAIP_VERSION', '1.0.1');
define('WPAIP_DATE_FORMAT', 'm-d-Y h:i');

$wpaip_options['whitelist'] = "authorizeipaddress_ip_whitelist";
$wpaip_options['options']   = "authorizeipaddress";

register_activation_hook(__FILE__,'wpaip_activate_plugin');

// Add relevant actions

add_action('wp_login', 'wpaip_admin_init',1,2);
add_action('init', 'wpaip_init');
add_action( 'login_form', 'wpaip_login_form' );
add_action( 'admin_init', 'wpaip_options_init' );
add_action( 'admin_menu', 'wpaip_options_add_page');

// Custom wp-login actions
add_action( 'login_form_unknownip' ,  'wpaip_login_action' );
add_action( 'login_form_registerip' , 'wpaip_login_action' );
add_action( 'login_form_invalidkey' , 'wpaip_login_action' );

// Include necessary libraries

include(dirname(__FILE__) . '/wpaip-options-page.php');

function wpaip_init(){
	if(in_array($GLOBALS['pagenow'], array('wp-login.php')) ){
		session_start();
	}
}

function wpaip_admin_init($user_login, $user){
	wpaip_main($user_login, $user);
}

function wpaip_activate_plugin() {
	global $wpaip_options, $current_user;
	session_start();
	unset( $_SESSION['wpaip_ipkey'] );

	// Set default values
	$activate_options['enabled'] = 1;
	$activate_options['notify_both'] = 0;
	update_option( $wpaip_options['options'] , $activate_options);

	$user = $current_user->user_login;
	$ip = $_SERVER['REMOTE_ADDR'];
	$known = get_option( $wpaip_options['whitelist'] );
	$known[ $user ][ $ip ]['date_requested'] = date(WPAIP_DATE_FORMAT);
	$known[ $user ][ $ip ]['date_activated'] = date(WPAIP_DATE_FORMAT);
	$known[ $user ][ $ip ]['activated'] = true;
	update_option( $wpaip_options['whitelist'] , $known );
}

function wpaip_main($user_login, $user) {
	global $wpaip_options;
	$_options = get_option( $wpaip_options['options'] );

	
	if ( !isset( $_options['enabled'] ) ) {
		wpaip_activate_plugin();
		return false;

	} elseif ( $_options['enabled'] == 0) {
		return false;
	}

	wpaip_new_ip_check($user_login);
}

function wpaip_new_ip_check($user_login) {
	
	global $wpaip_options;
	session_start();
	$known = get_option( $wpaip_options['whitelist'] );
	$user = $user_login;
	$ip = $_SERVER['REMOTE_ADDR'];
	

	// First check for known and activated IP
	if(!is_array($known[ $user ])){


	$_data_firsttime = get_option( $wpaip_options['whitelist'] );
	$_data_firsttime[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'date_activated' ] = date(WPAIP_DATE_FORMAT);
	$_data_firsttime[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'activated' ] = TRUE;
	$_data_firsttime[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'ipkey' ] = "FIRSTLOGIN";
	update_option( $wpaip_options['whitelist'] , $_data_firsttime );
		return FALSE; // First Time User Login
	}elseif	 ( is_array($known) && $known[ $user ][ $ip ]['activated'] == true ) {

		return false; // This is a known IP
	}

	 
	// Then check for IPKEY indicating  registration
	elseif (isset($_SESSION['wpaip_ipkey']) ) {

		$ipkey = $_SESSION['wpaip_ipkey'];
		if ( $known[ $user ][ $ip ][ 'ipkey' ] == $ipkey ) {  
			// Key valid.  Login.
			
			$known[ $user ][ $ip ]['date_activated'] = date(WPAIP_DATE_FORMAT);
			$known[ $user ][ $ip ]['activated'] = true;
			unset( $_SESSION['wpaip_ipkey'] );
			
			update_option( $wpaip_options['whitelist'] , $known );

			if ( $_options['notify_both'] ) {
				wpaip_notify_blog_admin($user_login);
			}
			return false;

		} else {  

			$GLOBALS["wpaip_url"]=site_url('wp-login.php?action=invalidkey') ;
			
			 wp_logout();

			exit;
		}
	}
	// If not known or registering, notify of new IP
	else {
		wpaip_send_activation($user_login);
		$GLOBALS["wpaip_url"]=site_url('wp-login.php?action=unknownip');
		wp_logout();

		exit;
	}
	 
}

add_action( 'wp_logout', 'wpaipp_logout',1,1);
function wpaipp_logout(){
  wp_redirect($GLOBALS["wpaip_url"]);

  
  exit();
}

function wpaip_send_activation($user_login) {
	global $wpaip_options, $wpdb;
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$ipkey = md5(mt_rand());
	$message = "Someone has logged in with the below information from an IP we haven't seen before.\n\nUser: $user_login\nIP: {$_SERVER['REMOTE_ADDR']}\nURL: $url";
	$message .= "\n\nTo authorize this IP address, please click the following link: ".site_url('wp-login.php?action=registerip&wpaip_ipkey='.$ipkey);
	$subject = sprintf( __('[%s] WP Login Security Alert  Autorize IP Address'), get_option('blogname') );

	// Get User Email By Username
	$username = $user_login;
	$email = $wpdb->get_var( $wpdb->prepare("
	SELECT user_email 
	FROM $wpdb->users 
	WHERE user_login = %s
	",
	$username
	) );
	wp_mail($email, $subject, $message );
		 
	// Set activation key
	$_data = get_option( $wpaip_options['whitelist'] );
	$_data[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'date_requested' ] = date(WPAIP_DATE_FORMAT);
	$_data[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'activated' ] = false;
	$_data[ $user_login ][ $_SERVER['REMOTE_ADDR'] ][ 'ipkey' ] = $ipkey;
	update_option( $wpaip_options['whitelist'] , $_data );
}

function wpaip_notify_blog_admin($user_login) {

	$message = "New IP Registration\nUser: $user_login\nIP: {$_SERVER['REMOTE_ADDR']}";
	$subject = sprintf( __('[%s] WP Login Security Alert'), get_option('blogname') );
	wp_mail( get_option('admin_email'), $subject, $message );
}

function wpaip_login_action() {
	global $error, $action;

	switch($action) {
		case "registerip":
			$error = "Please login to validate IP address.";
			break;
		case "unknownip":
			$error = "Login from an unrecognized IP address.  A one time password has been sent to the email address on record for your accont.";
			break;
		case "invalidkey":
			$error = "Invalid Key!";
			break;
	}

}

function wpaip_login_form() {
	global $action;
	if ($action == "registerip") {

		$_SESSION['wpaip_ipkey'] = $_GET['wpaip_ipkey'];
	}
}


?>

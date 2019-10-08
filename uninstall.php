<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) )
	exit ();

	
delete_option('authorizeipaddress_ip_whitelist');
delete_option('authorizeipaddress');

?>
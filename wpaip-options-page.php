<?php

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

 

 
add_action( 'admin_init' , 'wpaip_options_init' );
add_action( 'admin_menu' , 'wpaip_options_add_page');


//remove outstanding data
function remove_outstanding(){
global $wpaip_options, $current_user;
	$known = get_option( $wpaip_options['whitelist'] );
	// Remove oustanding data.
	foreach($known as $field=>$topVal){
		foreach($topVal as $key=>$val){
			if($val['activated']==1){
					$arr[$field][$key]['date_requested']=$val['date_requested'];
					$arr[$field][$key]['activated']=$val['activated'];
					$arr[$field][$key]['ipkey']=$val['ipkey'];
					$arr[$field][$key]['date_activated']=$val['date_activated'];
				
			}
		}
	}
	update_option( $wpaip_options['whitelist'] , $arr );
}

//Remove whitlisted ip.
function remove_whitlisted_ip(){
global $wpaip_options, $current_user;
	
	$user = $current_user->user_login;
	$ip = $_SERVER['REMOTE_ADDR'];
	$known[ $user ][ $ip ]['date_requested'] = date(WPAIP_DATE_FORMAT);
	$known[ $user ][ $ip ]['date_activated'] = date(WPAIP_DATE_FORMAT);
	$known[ $user ][ $ip ]['activated'] = true;
	update_option( $wpaip_options['whitelist'] , $known );

}

// Init plugin options to white list our options
function wpaip_options_init(){
	register_setting( 'authorizeipaddress_options', 'authorizeipaddress', 'wpaip_options_validate');
}

// Add menu page
function wpaip_options_add_page() {
	add_options_page('Authorize IP Address Options', 'Authorize IP Address', 'manage_options', 'authorizeipaddress', 'wpaip_options_do_page');
}

// Draw the menu page itself
function wpaip_options_do_page() {
	global $wpaip_options;
		if(isset($_POST['outstanding'])){
			remove_outstanding();
		}
		if(isset($_POST['whitelist'])){
			remove_whitlisted_ip();
		}
	?>
	<div class="wrap">
		<div class="icon32" id="icon-users"></div>
		<h2>Authorize IP Address</h2>
		<p>Authorize IP Address allows each user to maintain a whitelist of IP addresses allowed to login to the site.</p>
		<form method="post" action="options.php">
			<?php settings_fields('authorizeipaddress_options'); ?>
			<?php $options = get_option('authorizeipaddress'); ?>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">
						Enable Authorize IP Address?
					</th>
					<td>
						<input name="authorizeipaddress[enabled]" type="checkbox" value="1" <?php checked('1', $options['enabled']); ?> />
					</td>
				</tr>
				<tr valign="top">
					<th scope="row">
						Notify Both Blog Admin & User?
					</th>
					<td>
						<input name="authorizeipaddress[notify_both]" type="checkbox" value="1" "<?php checked('1', $options['notify_both']); ?>" />
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<div><div style="float:left;"><h3>Whitelisted IP Addresses</h3></div><div style="float:left; margin-left:47px; padding-top:10px;"> 
		<form method="post" action="">
			
		<input type="submit" value="Clear List" name="whitelist" class="button-primary" /></form></div>
		<div style='clear:both'></div>
		</div>
		<?php $whitelist = get_option($wpaip_options['whitelist']); ?>
		<table class="widefat">
			<thead>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Date Activated</th>
				<th>Remove Manual</th>
			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Date Activated</th>
				<th>Remove Manual</th>
			</tr>
			</tfoot>
			<tbody>
			<?php
				foreach ($whitelist as $user => $arr1) { foreach ($arr1 as $ip => $arr2) {
					if ($arr2['activated'] == true) {
						echo "<tr><td class='dusername'>$user</td><td class='dip'>$ip</td>";
						echo "<td>{$arr2['date_activated']}</td>
							<td><button  class='deletem' >Remove</button></td>
						</tr>";
					}
				}}
			?>
			</tbody>
		</table>
		<div><div style="float:left;"><h3>Outstanding IP Activations</h3></div><div style="float:left; margin-left:40px; padding-top:10px;"> 
		<form method="post" action="">
			
		<input type="submit" value="Clear List" name="outstanding" class="button-primary" /></form></div>
		<div style='clear:both'></div>
		</div>
		<table class="widefat">
			<thead>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Request Date</th>
				<th>Activation Key</th>
				<th>Approve IP Address</th>

			</tr>
			</thead>
			<tfoot>
			<tr>
				<th>Username</th>
				<th>IP Address</th>
				<th>Request Date</th>
				<th>Activation Key</th>
				<th>Approve IP Address</th>
			</tr>
			</tfoot>
			<tbody>

			<?php
				foreach ($whitelist as $user => $arr1) { foreach ($arr1 as $ip => $arr2) { if ($arr2['activated'] == false) {
						echo "<tr class='caprove'><td class='cusername'>$user</td><td class='cip'>$ip</td><td>{$arr2['date_requested']}</td><td class='cipkey'>{$arr2['ipkey']}</td>
						<td><button  class='approvem' >Approve</button></td>
						</tr>";
				}}}
			?>

			</tbody>
		</table>
		<h3>About Authorize IP Address</h3>
		<div style="width: 1000px; text-align: center; margin: 0 auto;">

			<span style="display: block;">This plugin is maintained by <a href="http://www.finagg.com/">Toni Jovevski</a>.</span>

		</div>
		
	</div>
	
	<script>
	
  	jQuery(document).ready(function(){
		jQuery('.approvem').click(function(){
		
	
		
		jQuery.ajax({
        url: ajaxurl,
        data: {
            'action':'manual_approve',
            'user' : jQuery(this).closest('tr').find('.cusername').text(),
            'ip':jQuery(this).closest('tr').find('.cip').text(),
            'ipkey':jQuery(this).closest('tr').find('.cipkey').text()
        },
        success:function(data) {
            
            location.reload();
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });  
	});
	
	jQuery('.deletem').click(function(){
		
	
		
		jQuery.ajax({
        url: ajaxurl,
        data: {
            'action':'manual_deletewhitelist',
            'user' : jQuery(this).closest('tr').find('.dusername').text(),
            'ip':jQuery(this).closest('tr').find('.dip').text()
            
        },
        success:function(data) {
           
            location.reload();
        },
        error: function(errorThrown){
            console.log(errorThrown);
        }
    });  
	});
    
     });

	</script>
	
	<?php
}

function manual_approve()
{
	
	global $wpaip_options;
	$user=$_REQUEST['user'];
	$ip=$_REQUEST['ip'];
	$ipkey=$_REQUEST['ipkey'];
	$known = get_option( $wpaip_options['whitelist'] );
	
	if ( $known[ $user ][ $ip ][ 'ipkey' ] == $ipkey ) {  
			
			$known[ $user ][ $ip ]['date_activated'] = date("m-d-Y h:i");
			$known[ $user ][ $ip ]['activated'] = true;
			
			
			update_option( $wpaip_options['whitelist'] , $known );
	}
	 
	die( );
}

add_action( 'wp_ajax_manual_approve', 'manual_approve' );
function manual_deletewhitelist()
{
	
	global $wpaip_options, $current_user;
	$known = get_option( $wpaip_options['whitelist'] );
	
	foreach($known as $field=>$topVal){
		foreach($topVal as $key=>$val){
			if($key!=$_REQUEST['ip'] && $field!=$_REQUEST['user']){
						
						
				
					$arr[$field][$key]['date_requested']=$val['date_requested'];
					$arr[$field][$key]['activated']=$val['activated'];
					$arr[$field][$key]['ipkey']=$val['ipkey'];
					$arr[$field][$key]['date_activated']=$val['date_activated'];
				
			}
		}
	}
	
	update_option( $wpaip_options['whitelist'] , $arr );
	
}

add_action( 'wp_ajax_manual_deletewhitelist', 'manual_deletewhitelist' );

// Sanitize and validate input. Accepts an array, return a sanitized array.
function wpaip_options_validate($input) {
	
	$input['enabled'] = ( $input['enabled'] == 1 ? 1 : 0 );
	$input['notify_both'] = ( $input['notify_both'] == 1 ? 1 : 0 );
	return $input;
}



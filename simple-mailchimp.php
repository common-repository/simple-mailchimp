<?php
/*
Plugin Name: Simple MailChimp - by Wonder
Plugin URI: http://WeAreWonder.dk/wp-plugins/simple-mailchimp/
Description: Add a simple MailChimp form to any page, post or template file using shortcodes. Quick and easy!
Version: 1.2.1
Author: Wonder
Author URI: http://WeAreWonder.dk
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=5B6TDUTW2JVX8
License: GPL2
	
	Copyright 2024 Wonder  (email : tobias@WeAreWonder.dk)
	
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.
	
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
	
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
	
*/

function simple_mailchimp_load_textdomain() {
	load_plugin_textdomain( 'simple-mailchimp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'simple_mailchimp_load_textdomain' );

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo __( 'Hi there! I\'m just a plugin, not much I can do when called directly.', 'simple-mailchimp' );
	exit;
}

// Allow redirection, even if my theme starts to send output to the browser
add_action('init', 'simple_mailchimp_output_buffer');
function simple_mailchimp_output_buffer() {
	ob_start();
}

function simple_mailchimp_activate() {
	update_option('simple_mailchimp_subscribe_status', __( 'subscribed', 'simple-mailchimp' ));
	update_option('simple_mailchimp_success_message',  __( 'You have been subscribed!', 'simple-mailchimp' ));
	update_option('simple_mailchimp_error_message',    __( 'An error occured. You have not been subscribed!', 'simple-mailchimp' ));
}
register_activation_hook(__FILE__, 'simple_mailchimp_activate');

add_action('admin_menu', 'simple_mailchimp_admin_menu');
function simple_mailchimp_admin_menu() {
	add_submenu_page('options-general.php', __( 'Simple MailChimp settings', 'simple-mailchimp' ), __( 'Simple MailChimp', 'simple-mailchimp' ), 'manage_options', 'simple-mailchimp-settings', 'simple_mailchimp_admin_page');
}

add_action('admin_head', 'simple_mailchimp_head');
function simple_mailchimp_head() {
	echo '<link rel="stylesheet" type="text/css" href="' . plugin_dir_url( __FILE__ ) . 'style.css">';
}

add_filter('plugin_action_links', 'simple_mailchimp_plugin_action_links', 10, 2);
function simple_mailchimp_plugin_action_links($links, $file) {
	static $this_plugin;
	
	if (!$this_plugin) {
		$this_plugin = plugin_basename(__FILE__);
	}
	
	// check to make sure we are on the correct plugin
	if ($file == $this_plugin) {
		$settings_link = '<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AHE8UEBKSYJCA">' . __( 'Donate', 'simple-mailchimp' ) . '</a>';
		array_unshift($links, $settings_link);
		
		$settings_link = '<a href="' . get_bloginfo('wpurl') . '/wp-admin/options-general.php?page=simple-mailchimp-settings.php">' . __( 'Settings', 'simple-mailchimp' ) . '</a>';
		array_unshift($links, $settings_link);
	}
	
	return $links;
}

function simple_mailchimp_register_session(){
	if ( !session_id() ) {
		session_start();
	}
}
add_action('init', 'simple_mailchimp_register_session');

function simple_mailchimp_admin_page() {
	
	$simple_mailchimp_submit           = @$_POST['submit'];
	$simple_mailchimp_api_key          = @$_POST['api_key'];
	$simple_mailchimp_default_list_id  = @$_POST['default_list_id'];
	$simple_mailchimp_subscribe_status = @$_POST['subscribe_status'];
	$simple_mailchimp_success_message  = @$_POST['success_message'];
	$simple_mailchimp_error_message    = @$_POST['error_message'];
	
	if (
		isset($simple_mailchimp_submit)
	) {
		update_option('simple_mailchimp_api_key',          $simple_mailchimp_api_key);
		update_option('simple_mailchimp_default_list_id',  $simple_mailchimp_default_list_id);
		update_option('simple_mailchimp_subscribe_status', $simple_mailchimp_subscribe_status);
		update_option('simple_mailchimp_success_message',  $simple_mailchimp_success_message);
		update_option('simple_mailchimp_error_message',    $simple_mailchimp_error_message);
		
		wp_redirect('?page=' . $_GET['page'] . '&msg=saved');
		
		exit();
	}
	?>
	
	<style type="text/css">
	.simple-mailchimp-donate-box {
		float: right;
		width: 150px;
		padding: 25px;
		margin:  25px;
		border: 2px solid #bbb;
		background-color: #e7e7e7;
	}
	
	.simple-mailchimp-donate-box h2 {
		margin-top: 0;
	}
	
	.simple-mailchimp-donate-box hr {
		height: 1px;
		border-width: 2px 0 0 0;
		border-style: solid;
		border-color: #bbb;
	}
	
	h2 img {
		float: left;
		margin-right: 5px;
	}
	h2 small {
		display: block;
		margin-left: 38px;
		font-size: 12px;
	}
	</style>
	
	<div class="simple-mailchimp-donate-box">
		<h2><?php echo __( 'Donate', 'simple-mailchimp' ); ?></h2>
		
		<p>
			<?php echo __( 'Enjoying this plugin? Your support helps us continue enhancing and maintaining it. Consider a $5 contribution to keep the updates coming!', 'simple-mailchimp' ); ?>
		</p>
		
		<p>
			<a title="<?php echo __( 'Donate', 'simple-mailchimp' ); ?>" href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=AHE8UEBKSYJCA"><?php echo __( 'Donate', 'simple-mailchimp' ); ?> &raquo;</a>
		</p>
		
		<hr>
		
		<h2><?php echo __( 'See also…', 'simple-mailchimp' ); ?></h2>
		
		<p>
			<a title="Embed Image Links plugin" href="http://wordpress.org/plugins/embed-image-links/">Embed Image Links plugin</a><br>
			<a title="Queue Posts plugin" href="http://wordpress.org/plugins/queue-posts/">Queue Posts plugin</a>
		</p>
	</div>
	
	<div class="wrap">
		
		<h2>
			<img alt="Settings" src="<?php echo plugin_dir_url( __FILE__ ); ?>img/settings-icon.png" width="32" height="32" style="margin-bottom: -7px;">
			<?php echo __( 'Settings for') . ' Simple MailChimp'; ?>
			<small><?php echo __( 'by', 'simple-mailchimp' ); ?> <a href="http://wearewonder.dk" target="_blank">Wonder</a></small>
		</h2>
		
		<form method="post" action="">
			
			<p style="margin: 15px 0;">
				<label for="simple-mailchimp-api-key">
					<?php echo __( 'MailChimp API Key', 'simple-mailchimp' ); ?>:
				</label>
				
				<br>
				
				<input id="simple-mailchimp-api-key" name="api_key" type="text" value="<?php echo get_option('simple_mailchimp_api_key'); ?>">
			</p>
			
			<p style="margin: 15px 0;">
				<label for="simple-mailchimp-default-list">
					<?php echo __( 'MailChimp list ID to use as default', 'simple-mailchimp' ); ?>:
				</label>
				
				<br>
				
				<input id="simple-mailchimp-default-list" name="default_list_id" type="text" value="<?php echo get_option('simple_mailchimp_default_list_id'); ?>">
			</p>
			
			<p style="margin: 15px 0;">
				<label for="simple-mailchimp-subscribe-status">
					<?php echo __( 'Status for new subscribers', 'simple-mailchimp' ); ?>:
				</label>
				
				<br>
				
				<select id="simple-mailchimp-subscribe-status" name="subscribe_status">
					<option value="subscribed"<?php if ( get_option('simple_mailchimp_subscribe_status') == 'subscribed' ) : ?> selected="selected"<?php endif; ?>><?php echo __( 'Subscribe immediately', 'simple-mailchimp' ); ?></option>
					<option value="pending"<?php if ( get_option('simple_mailchimp_subscribe_status') == 'pending' ) : ?> selected="selected"<?php endif; ?>><?php echo __( 'Confirm via e-mail', 'simple-mailchimp' ); ?></option>
				</select>
			</p>
			
			<p style="margin: 15px 0;">
				<label for="simple-mailchimp-success-message">
					<?php echo __( 'Message to upon subscription', 'simple-mailchimp' ); ?>:
				</label>
				
				<br>
				
				<input id="simple-mailchimp-success-message" name="success_message" type="text" value="<?php echo get_option('simple_mailchimp_success_message'); ?>">
			</p>
			
			<p style="margin: 15px 0;">
				<label for="simple-mailchimp-error-message">
					<?php echo __( 'Message to upon error'); ?>:
				</label>
				
				<br>
				
				<input id="simple-mailchimp-error-message" name="error_message" type="text" value="<?php echo get_option('simple_mailchimp_error_message'); ?>">
			</p>
			
			<p>
				<input name="submit" type="submit" value="<?php echo __( 'Save', 'simple-mailchimp' ); ?>">
			</p>
			
		</form>
		
		<hr>
		
		<h2><?php echo __( 'Help', 'simple-mailchimp' ); ?></h2>
		
		<p>
			<b><?php echo __( 'Shortcode', 'simple-mailchimp' ); ?> (<?php echo __( 'page', 'simple-mailchimp' ); ?>, <?php echo __( 'post', 'simple-mailchimp' ); ?> <?php echo __( 'or', 'simple-mailchimp' ); ?> <?php echo __( 'custom post type', 'simple-mailchimp' ); ?>):</b>
		</p>
		
		<p>
			[simple_mailchimp]
		</p>
		
		<p>
			<b><?php echo __( 'Shortcode', 'simple-mailchimp' ); ?> (PHP):</b>
		</p>
		
		<p>
			&lt;?php do_shortcode(&#39;[simple_mailchimp]&#39;); ?&gt;
		</p>
		
		<p>
			<b><?php echo __( 'Custom HTML form', 'simple-mailchimp' ); ?></b>
		</p>
		
		<p>
			<pre><?php echo htmlentities( get_simple_mailchimp_form_code() ); ?></pre>
		</p>
		
	</div>
	
<?php }

function simple_mailchimp_subscribe($data) {
	
	$api_key = get_option('simple_mailchimp_api_key');
	
	if ( !@$data['list_id'] ) {
		$data['list_id'] = get_option('simple_mailchimp_default_list_id');
	}
	
	if ( !@$data['status'] ) {
		$data['status'] = 'subscribed';
	}
	
	// Abort if not enough info has been provided
	if ( !@trim($api_key) || !@$data['list_id'] ) {
		return 400;
	}
	
	$memberId   = md5(strtolower($data['email']));
	$dataCenter = substr($api_key,strpos($api_key,'-')+1);
	
	$url = 'https://' . $dataCenter . '.api.mailchimp.com/3.0/lists/' . $data['list_id'] . '/members/' . $memberId;
	
	$json = json_encode(array(
		'status'        => $data['status'], // "subscribed","unsubscribed","cleaned","pending"
		'email_address' => $data['email'],
		'merge_fields'  => array(
			'FNAME'     => $data['firstname'],
			'LNAME'     => $data['lastname'],
		)
	));
	
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_USERPWD,        'user:' . $api_key);
	curl_setopt($ch, CURLOPT_HTTPHEADER,     array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT,        10);
	curl_setopt($ch, CURLOPT_CUSTOMREQUEST,  'PUT');
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS,     $json);
	
	$result   = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	curl_close($ch);
	
	return $httpCode;
	
}

$simple_mailchimp_success_code = 0;
function simple_mailchimp_form_submitted() {
	
	if (
		@trim($_POST['simple_mailchimp_firstname']) != ''
		&&
		@trim($_POST['simple_mailchimp_lastname'] ) != ''
		&&
		@trim($_POST['simple_mailchimp_email']    ) != ''
	) {
		
		$data = array(
		    'status'    => get_option('simple_mailchimp_subscribe_status'),
		    'email'     => @trim($_POST['simple_mailchimp_email']),
		    'firstname' => @trim($_POST['simple_mailchimp_firstname']),
		    'lastname'  => @trim($_POST['simple_mailchimp_lastname']),
			'list_id'   => @trim($_POST['simple_mailchimp_list_id']),
		);
		
		global $simple_mailchimp_success_code;
		$simple_mailchimp_success_code = simple_mailchimp_subscribe($data);
		
	}
}
simple_mailchimp_form_submitted();

function get_simple_mailchimp_form_code() {
	
	$return = '<form method="post" action="">
	<p>
		<label for="simple_mailchimp_email">' . __( 'Email', 'simple-mailchimp' ) . ' *</label>
		<input type="text" id="simple_mailchimp_email" name="simple_mailchimp_email"' . ($_POST['simple_mailchimp_email'] ? ' value="' . $_POST['simple_mailchimp_email'] . '"' : '') . '>
	</p>
	<p>
		<label for="simple_mailchimp_firstname">' . __( 'First name', 'simple-mailchimp' ) . ' *</label>
		<input type="text" id="simple_mailchimp_firstname" name="simple_mailchimp_firstname"' . ($_POST['simple_mailchimp_firstname'] ? ' value="' . $_POST['simple_mailchimp_firstname'] . '"' : '') . '>
	</p>
	<p>
		<label for="simple_mailchimp_lastname">' . __( 'Last name', 'simple-mailchimp' ) . ' *</label>
		<input type="text" id="simple_mailchimp_lastname" name="simple_mailchimp_lastname"' . ($_POST['simple_mailchimp_lastname'] ? ' value="' . $_POST['simple_mailchimp_lastname'] . '"' : '') . '>
	</p>
	<p>
		<input type="submit" id="simple_mailchimp_submit" value="' . __( 'Subscribe', 'simple-mailchimp' ) . '">
	</p>
</form>';
	
	return $return;
	
}

function do_simple_mailchimp_shortcode($atts) {
	global $simple_mailchimp_success_code;
	
	$success_code = @intval( $simple_mailchimp_success_code );
	$return       = '';
	
	if ( $success_code == 200 ) {
		$_POST   = array(); // Clear $_POST array
		$return .= '<div class="msg simple-mailchimp-msg">' . get_option('simple_mailchimp_success_message') . '</div>';
	} else if ( $success_code > 0 ) {
		$return .= '<div class="msg error simple-mailchimp-msg">' . get_option('simple_mailchimp_error_message') . '</div>';
	}
	
	$return .= get_simple_mailchimp_form_code();
	
	return $return;
	
}
add_shortcode('simple_mailchimp', 'do_simple_mailchimp_shortcode');

?>
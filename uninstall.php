<?php

//if uninstall not called from WordPress exit
if ( !defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit();
}

delete_option('simple_mailchimp_api_key');
delete_option('simple_mailchimp_default_list');

?>
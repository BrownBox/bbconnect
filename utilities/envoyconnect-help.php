<?php

function envoyconnect_help_screens() {
	
	$screen = get_current_screen();

	switch( $screen->id ) {
	
		case 'toplevel_page_envoyconnect_options' : 
		
			$screen->add_help_tab( array(
			    'id' 		=> 'op_general',
			    'title'		=> PAUPRESS_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://envoyconnect.com/support/documentation/envoyconnect-plugin-documentation/">envoyconnect.com/support/documentation</a></h4>',
			) );
		
		break;
		
		case 'envoyconnect-options_page_envoyconnect_inbounds' : 
		
			$screen->add_help_tab( array(
			    'id' 		=> 'ib_general',
			    'title'		=> PAUPRESS_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://envoyconnect.com/support/documentation/envoyconnect-plugin-documentation/envoyconnect-options/inbound-communications/">envoyconnect-plugin-documentation/envoyconnect-options/inbound-communications</a></h4>',
			) );
		
		break;
	
		case 'users_page_envoyconnect_reports' : 
		
			$screen->add_help_tab( array(
			    'id' 		=> 'rp_search',
			    'title'		=> PAUPRESS_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://envoyconnect.com/support/documentation/envoyconnect-plugin-documentation/users/user-reports/">envoyconnect-plugin-documentation/users/user-reports</a></h4>',
			) );
			
		break;
		
		case 'users_page_envoyconnect_edit_user' : 
		case 'toplevel_page_envoyconnect_edit_user_profile' : 
		case 'users_page_envoyconnect_new_user' : 
			
			$screen->add_help_tab( array(
			    'id' 		=> 'up_profile',
			    'title'		=> PAUPRESS_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://envoyconnect.com/support/documentation/envoyconnect-plugin-documentation/users/my-profile/">envoyconnect-plugin-documentation/users/my-profile</a></h4>',
			) );
			
		break;
	
		case 'envoyconnect-options_page_envoyconnect_meta_options' :
		
			$screen->add_help_tab( array( 
				'id' 		=> 'mf_general',
		        'title'		=> PAUPRESS_SUPPORT_DOCS,
		        'content'	=> '<h4><a href="http://envoyconnect.com/support/documentation/envoyconnect-plugin-documentation/envoyconnect-options/manage-fields/">envoyconnect-plugin-documentation/envoyconnect-options/manage-fields</a></h4>', 
		    ) );
		
		break;
	
	}
}

?>
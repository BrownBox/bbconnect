<?php

function bbconnect_help_screens() {

	$screen = get_current_screen();

	switch( $screen->id ) {

		case 'toplevel_page_bbconnect_options' :

			$screen->add_help_tab( array(
			    'id' 		=> 'op_general',
			    'title'		=> BBCONNECT_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://brownbox.net.au/support/">brownbox.net.au/support/</a></h4>',
			) );

		break;

		case 'users_page_bbconnect_reports' :

			$screen->add_help_tab( array(
			    'id' 		=> 'rp_search',
			    'title'		=> BBCONNECT_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://brownbox.net.au/support/">brownbox.net.au/support/</a></h4>',
			) );

		break;

		case 'users_page_bbconnect_edit_user' :
		case 'toplevel_page_bbconnect_edit_user_profile' :
		case 'users_page_bbconnect_new_user' :

			$screen->add_help_tab( array(
			    'id' 		=> 'up_profile',
			    'title'		=> BBCONNECT_SUPPORT_DOCS,
			    'content'	=> '<h4><a href="http://brownbox.net.au/support/">brownbox.net.au/support/</a></h4>',
			) );

		break;

		case 'bbconnect-options_page_bbconnect_meta_options' :

			$screen->add_help_tab( array(
				'id' 		=> 'mf_general',
		        'title'		=> BBCONNECT_SUPPORT_DOCS,
		        'content'	=> '<h4><a href="http://brownbox.net.au/support/">brownbox.net.au/support/</a></h4>',
		    ) );

		break;

	}
}

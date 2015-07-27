<?php

add_filter( 'bbconnect_options_tabs', 'bbconnectpanels_settings_trans', 19, 1 );
function bbconnectpanels_settings_trans( $navigation ) {
	if ( array_key_exists( 'bbconnect_panel_settings', $navigation ) ) {
		unset( $navigation['bbconnect_panel_settings'] );
	}
	return $navigation;
}

function bbconnect_panel_settings_trans() {

	
	return apply_filters( 'bbconnect_panel_settings', array(
	
		array( 'meta' => array( 
								'source' => 'bbconnectforms', 
								'meta_key' => 'bbconnectpanels_embed_title', 
								'name' => __( 'Embed Options', 'bbconnect' ), 
								'help' => '', 
								'description' => __( 'If you would rather have your forms processed on the page, this is for you', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnectforms', 
								'meta_key' => 'bbconnectpanels_embed', 
								'name' => __( 'Embed panels on page', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnectforms', 
								'meta_key' => 'bbconnectpanels_embed_height', 
								'name' => __( 'Default minimum height(px) for the forms', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'text', 
													'class' => 'input-short', 
													'req' => false, 
													'public' => false, 
													'choices' => '200', 
								) 
		) ),
		
	));
	
}
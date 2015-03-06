<?php

add_filter( 'envoyconnect_options_tabs', 'envoyconnectpanels_settings_trans', 19, 1 );
function envoyconnectpanels_settings_trans( $navigation ) {
	if ( array_key_exists( 'envoyconnect_panel_settings', $navigation ) ) {
		unset( $navigation['envoyconnect_panel_settings'] );
	}
	return $navigation;
}

function envoyconnect_panel_settings_trans() {

	
	return apply_filters( 'envoyconnect_panel_settings', array(
	
		array( 'meta' => array( 
								'source' => 'envoyconnectforms', 
								'meta_key' => 'envoyconnectpanels_embed_title', 
								'name' => __( 'Embed Options', 'envoyconnect' ), 
								'help' => '', 
								'description' => __( 'If you would rather have your forms processed on the page, this is for you', 'envoyconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnectforms', 
								'meta_key' => 'envoyconnectpanels_embed', 
								'name' => __( 'Embed panels on page', 'envoyconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'envoyconnectforms', 
								'meta_key' => 'envoyconnectpanels_embed_height', 
								'name' => __( 'Default minimum height(px) for the forms', 'envoyconnect' ), 
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
<?php

function bbconnect_pro_settings() {
	
	$paupro_settings = apply_filters( 'paupro_settings', array(
						
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'paupro_license_title', 
								'name' => __( 'PauPro License Information', 'bbconnect' ), 
								'help' => '', 
								'description' => __( 'Please enter your license details below to enable automatic updates. Please note that you have to download and install the Pro version before you are able to take advantage of the Pro features.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'title',
													'req' => false, 
													'public' => false, 
													'choices' => false
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'paupro_license_key', 
								'name' => __( 'Your license key', 'bbconnect' ), 
								'help' =>'', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => false,  
								) 
		) ),
			
	));
	
	
	$paupack_settings = apply_filters( 'paupack_settings', array() );
	
	return array_merge( $paupro_settings, $paupack_settings );
	
}
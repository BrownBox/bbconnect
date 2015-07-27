<?php

function bbconnect_pro_settings() {
	
	$bbcpro_settings = apply_filters( 'bbcpro_settings', array(
						
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'bbcpro_license_title', 
								'name' => __( 'BBCPro License Information', 'bbconnect' ), 
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
								'meta_key' => 'bbcpro_license_key', 
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
	
	
	$bbcpack_settings = apply_filters( 'bbcpack_settings', array() );
	
	return array_merge( $bbcpro_settings, $bbcpack_settings );
	
}
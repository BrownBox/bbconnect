<?php

	function envoyconnect_forms_settings() {
		
		return apply_filters( 'envoyconnect_forms_settings', array(
						
			array( 'meta' => array( 
									'source' => 'envoyconnect', 
									'meta_key' => '_envoyconnect_forms_setting_title', 
									'name' => __( 'General Forms', 'envoyconnect' ), 
									'help' => '', 
									'description' => __( 'Forms allow you to receive information from your site visitors. You can arrange and place selected user fields for data collection in addition to standard fields like "subject", "message" and the option for a visitor to copy themselves on the messsage. All incoming responses are sent to you and then logged.', 'envoyconnect' ), 
									'options' => array( 
														'field_type' => 'title',
														'req' => false, 
														'public' => false, 
														'choices' => false
									) 
			) ),
			
			array( 'meta' => array( 
									'source' => 'envoyconnect', 
									'meta_key' => '_envoyconnect_user_forms', 
									'name' => __( 'Create and Edit Forms', 'envoyconnect' ), 
									'help' => '', 
									'options' => array( 
														'field_type' => 'plugin',
														'req' => false, 
														'public' => false, 
														'choices' => 'envoyconnect_form_manager'
									) 
			) )
		
		));
		
	}

?>
<?php

	function bbconnect_forms_settings() {
		
		return apply_filters( 'bbconnect_forms_settings', array(
						
			array( 'meta' => array( 
									'source' => 'bbconnect', 
									'meta_key' => '_bbconnect_forms_setting_title', 
									'name' => __( 'General Forms', 'bbconnect' ), 
									'help' => '', 
									'description' => __( 'Forms allow you to receive information from your site visitors. You can arrange and place selected user fields for data collection in addition to standard fields like "subject", "message" and the option for a visitor to copy themselves on the messsage. All incoming responses are sent to you and then logged.', 'bbconnect' ), 
									'options' => array( 
														'field_type' => 'title',
														'req' => false, 
														'public' => false, 
														'choices' => false
									) 
			) ),
			
			array( 'meta' => array( 
									'source' => 'bbconnect', 
									'meta_key' => '_bbconnect_user_forms', 
									'name' => __( 'Create and Edit Forms', 'bbconnect' ), 
									'help' => '', 
									'options' => array( 
														'field_type' => 'plugin',
														'req' => false, 
														'public' => false, 
														'choices' => 'bbconnect_form_manager'
									) 
			) )
		
		));
		
	}

?>
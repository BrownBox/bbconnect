<?php

if( !class_exists('PPPLUGINAPI') ) {
    class PPPLUGINAPI {
    
        var $api_url;
    	var $plugin_id;
    	var $plugin_path;
    	var $plugin_slug;
    	var $plugin_key;
    
        function __construct( $api_url, $plugin_id, $plugin_path, $plugin_key ) {
    		$this->api_url = $api_url;
    		$this->plugin_id = $plugin_id;
    		$this->plugin_path = $plugin_path;
    		$this->plugin_key = $plugin_key;
    		if ( strstr( $plugin_path, '/' ) ) list ($t1, $t2) = explode( '/', $plugin_path ); 
    		else $t2 = $plugin_path;
    		$this->plugin_slug = str_replace('.php', '', $t2);
    
    		add_filter( 'pre_set_site_transient_update_plugins', array(&$this, 'bbconnect_api_plugin_check') );
    		add_filter( 'plugins_api', array(&$this, 'bbconnect_api_plugin_information'), 10, 3);
    		add_action( 'in_plugin_update_message-' . $this->plugin_path, array( $this, 'bbconnect_api_plugin_update_row' ), 10, 2 );
    		
    	}
    	
    	function bbconnect_api_plugin_check( $transient ) {
    		
    		// CHECK IF THERE'S A LICENSE KEY
    		if ( false !== $this->plugin_key && !get_option( $this->plugin_slug.'_license_key' )  )
    			return $transient;
    			
    	    if ( empty( $transient->checked ) )
    	        return $transient;

    	    $args = array(
    	     	'bbconnect' => 'wppu', 
    	        'pp_action' => 'plugin-update',
    	        'pp_item' => $this->plugin_id,
     	        'pp_slug' => $this->plugin_slug,
    	        'pp_version' => $transient->checked[$this->plugin_path],
    	        'pp_key' => get_option( $this->plugin_slug.'_license_key' ), 
    	        'pp_site' => get_option( 'siteurl' ), 
    	    );
    	    
			$response = $this->bbconnect_api_plugin_request( $this->api_url, $args );
			if ( false !== $response )
    	        $transient->response[$this->plugin_path] = $response;
    	    
    	    return $transient;
    	    
    	}
    	
    	function bbconnect_api_plugin_information( $false, $action, $args ) {

			// CHECK IF THERE'S A LICENSE KEY
			if ( false !== $this->plugin_key && !get_option( $this->plugin_slug.'_license_key' )  )
				return $false;
    	
    	    if ( !isset( $args->slug ) || $args->slug != $this->plugin_slug )
    	        return $false;
    	        	    	
    	    $transient = get_site_transient( 'update_plugins' );
    	        
    	    $args = array(
    	    	'bbconnect' => 'wppu', 
    	        'pp_action' => 'plugin-information',
    	        'pp_item' => $this->plugin_id,
    	        'pp_slug' => $this->plugin_slug,
    	        'pp_version' => $transient->checked[$this->plugin_path],
    	        'pp_key' => get_option( $this->plugin_slug.'_license_key' ), 
    	        'pp_site' => get_option( 'siteurl' ),  
    	    );
    	    
    	    $response = $this->bbconnect_api_plugin_request( $this->api_url, $args );
    	    
    	    return $response;
    	    
    	}
    	
    	function bbconnect_api_plugin_request( $url, $args ) {

    	    $request = wp_remote_post( $url, array( 'body' => $args ) );
    	    
    	    if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 )
    	    	return false;
    	    
    	    $response = maybe_unserialize( wp_remote_retrieve_body( $request ) );
    	    if ( !is_object( $response ) || empty( $response ) )
    	        return false;
    	    
    	    return $response;
    	    
    	}
    	
    	function bbconnect_api_plugin_update_row( $plugin_data, $r ) {
    		if ( empty( $r->package ) ) {
    			echo ' <a href="' . admin_url() . '/plugin-install.php?tab=plugin-information&plugin='. $this->plugin_path .'&section=notice&TB_iframe=true&width=640&height=419" class="thickbox">';
    			echo __( ' find out why and how to fix it!', 'bbconnect' );
    			echo '</a>';
    		}
    	}

    }
}

?>
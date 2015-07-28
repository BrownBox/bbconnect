<?php // last updated 19/07/2014

// 1. find'n replace Savedsearchs & Savedsearch (as cpt). *Remember to preseve case
// 2. add to the $includes array() in functions.php
// 3. add custom fields to savedsearch_metabox_content

function savedsearch_metabox() {
	add_meta_box( 'cpt_savedsearch_box', __( 'Saved Search Meta', '' ), 'savedsearch_metabox_content', 'savedsearch', 'side', 'high' );
}
add_action( 'add_meta_boxes', 'savedsearch_metabox' );

function savedsearch_metabox_content( $post ) {
	if( !is_array( $post_fields) ) $post_fields = array();
	wp_nonce_field( plugin_basename( __FILE__ ), 'savedsearch_metabox_content_nonce' );

	// custom fields here! new line for each field
	// example #1 array_push( $post_fields, bb_new_meta( 'title=title2&name=name2&size=50%&type=text' ) );
	// example #2 array_push( $post_fields, bb_new_meta( array( 'title' => 'title3', 'type' => 'text' ) ) );
	array_push( $post_fields, bb_new_field( 'title=Private&field_name=private&size=50%&type=checkbox' ) );

	set_transient( 'savedsearch_fields', serialize( $post_fields ), 3600 );
}

function savedsearch_metabox_save( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return;
	if ( !wp_verify_nonce( $_POST['savedsearch_metabox_content_nonce'], plugin_basename( __FILE__ ) ) ) 	return;
	if ( 'page' == $_POST['post_type'] && ( !current_user_can( 'edit_page', $post_id ) || !current_user_can( 'edit_post', $post_id ) ) ) return;
	$savedsearch_fields = unserialize( get_transient( 'savedsearch_fields' ) );
	foreach( $savedsearch_fields as $meta_field ) update_post_meta( $post_id, $meta_field, sanitize_text_field( $_POST[$meta_field] ) );
}
add_action( 'save_post', 'savedsearch_metabox_save' );

if (!function_exists('bb_new_field')) {
    function bb_new_field( $args ){
    	// last updated 19/07/2014

    	is_array( $args ) ? extract( $args ) : parse_str( $args );
    	// $args example: "url="http://techn.com.au&target=_blank&output=echo"

    	//set defaults
    	if( !$title && !$field_name) return;
    	if( !$title && $field_name) $title = $field_name; $title = ucfirst( strtolower( $title ) );
    	if( !$field_name && $title ) $field_name = $title; $field_name = strtolower( str_replace( ' ', '_', $field_name ) );
    	if( !$size ) $size = '100%'; // accepts valid css for all types expect textarea. Text array expects an array where [0] is width and [1] is height
    	if( !$max_width ) $max_width = '100%';
    	if( !$type ) $type = 'text'; // accepts 'text', 'textarea', 'checkox', 'select'

    	$script = substr( $_SERVER['PHP_SELF'], strrpos( $_SERVER['PHP_SELF'], '/')+1 );
    	$source = ( $script == 'post.php' || $script == 'post-new.php' ) ? 'meta' : 'option';
    	if ( !$source == 'option' && !$group ) return;
    	$field_name = ( $source == 'option' ) ? $group."[".$name."]" : $field_name;

    	echo ' <div style="display:block;width:100%;padding-bottom:5px;">'."\n";
    	switch ($type) {

    		case 'checkbox':
    			$checked = ( $source == 'meta' && get_post_meta( $_GET[post], $field_name, true ) == 'true') ? 'checked="checked"' : '' ;
    			if( $source == 'option' ) {
    				$option = get_option( $group );
    			 	$value = $option[$name];
    			 	$checked = ( $value == 'true' ) ? 'checked="checked"' : '';
    			 }
    			echo '   <input type="checkbox" name="'.$field_name.'" value="true" '.$checked.' style="margin: 0 5px 0px 0;"/><label style="color:rgba(0,0,0,0.75);">'.$title.'</label>'."\n";
    			break;

    		case 'textarea':
    			if( $source == 'meta' ) $value = get_post_meta( $_GET[post], $field_name, true );
    			if( $source == 'option' ) {
    				$option = get_option( $group );
    			 	$value = $option[$name];
    			 }
    		 	if( $default && !$value ) $value = $default;
    			echo '	<label for="'.$field_name.'">'."\n";
    			echo '   	<sub style="color:rgba(0,0,0,0.75);display:block;">'.$title.'</sub>'."\n";
    			echo '   </label>'."\n";
    			if( !is_array( $size ) ) $size = explode(',', $size);
    			$style = 'width:'.$size[0].';height:'.$size[1].';max-width:'.$max_width.';';
    			echo '   <textarea id="'.$field_name.'" name="'.$field_name.'" style="'.$style.';" placeholder="'.$placeholder.'" >'.esc_attr( $value ).'</textarea>'."\n";
    			break;

    		case 'select':
    		// expects an $options array of arrays as follows
    		// $options = array (
    		//		array ( 'label' => 'aaa', 'value' => '1' ),
    		//		array ( 'label' => 'aaa', 'value' => '1' ),
    		//		);
    			$current = get_post_meta( $_GET[post], $field_name, true ) ;
    			echo '	<sub style="color:rgba(0,0,0,0.75);display:block;width:100%;max-width:'.$max_width.';">'.$title.'</sub>'."\n";
    			echo '  	<select name="'.$field_name.'" id="'.$field_name.'">'."\n";
    			foreach( $options as $option ) echo '		<option value="'.$option['value'].'" '.selected( $option['value'], $current, false ).'>'.$option['label'].'</option>'."\n";
    			echo '	</select>'."\n";
    			break;

    		case 'color-picker':
    			echo '	<label for="meta-color" class="prfx-row-title" style="display:block;width:100%;max-width:'.$max_width.';">'.$title.'</label>'."\n";
        		echo '	<input name="'.$field_name.'" type="text" value="'.get_post_meta( $_GET[post], $field_name, true ).'" class="meta-color" />'."\n";
    			break;

    		case 'wp-editor':
    			if( $source == 'meta' ) $value = get_post_meta( $_GET[post], $field_name, true );
    			if( $source == 'option' ) {
    				$option = get_option( $group );
    			 	$value = $option[$name];
    			 }
    		 	if( $default && !$value ) $value = $default;
    			wp_editor( $value, $field_name, $settings );
    			break;

    		case 'text':
    		default:
    			if( $source == 'meta' ) $value = get_post_meta( $_GET[post], $field_name, true );
    			if( $source == 'option' ) {
    				$option = get_option( $group );
    			 	$value = $option[$name];
    			 }
    		 	if( $default && !$value ) $value = $default;
    			echo '	<label for="'.$field_name.'">'."\n";
    			echo '		<sub style="color:rgba(0,0,0,0.75);display:block;">'.$title.'</sub>'."\n";
    			echo '	</label>'."\n";
    			echo '   <input type="'.$type.'" id="'.$field_name.'" name="'.$field_name.'" style="display:block;max-width:'.$max_width.';width:'.$size.';" placeholder="'.$placeholder.'" value="'.esc_attr( $value ).'" />'."\n";
    			break;

    	}
    	if( $description ) echo '   <div style="position:relative;top:-3px;display:block;width:100%;color:#ddd;font-size:0.8em;">'.$description.'</div>'."\n";
    	echo ' </div>'."\n";
    	return $field_name;

    }
}

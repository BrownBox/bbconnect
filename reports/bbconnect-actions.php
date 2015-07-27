<?php


function bbconnect_reports_actions( $display = '' ) {
?>
	<div id="group-action" class="drawer"<?php if ( 'open' == $display ) { echo ' style="display: block;"'; } ?>>
		
		<select id="action-select">
			<option value=""><?php _e( 'Select an Action', 'bbconnect' ); ?></option>
			<optgroup label="<?php _e( 'Actions', 'bbconnect' ); ?>">
				<option value="bbc_export"><?php _e( 'Export', 'bbconnect' ); ?></option>
				<?php do_action( 'bbconnect_call_select_actions' ); ?>
			</optgroup>
		</select>

		<div id="action-form">
			<?php 	
				bbc_export();
				do_action( 'bbconnect_show_selected_actions' );
			?>
		</div>
	
	</div>
	
<?php
}


function bbc_export() {
?>						
	<div id="bbc_export" class="action-holder">
		<form id="bbc_export_form" class="report-form" enctype="multipart/form-data" action="<?php echo admin_url( 'users.php?page=bbconnect_reports' ); ?>" method="POST">
			<div class="inside">
				<div class="column_holder">
				
					<h3><?php _e( 'New Export', 'bbconnect' ); ?></h3>
					<p><?php _e( 'What would you like to call this export?', 'bbconnect' ); ?><br />
					<input type="text" name="export_title" class="regular-text" value="" /></p>
					
					<h3><?php _e( 'User Options', 'bbconnect' ); ?></h3>
					<p><input type="checkbox" name="_grexport[all_users]" value="1" /> <?php _e( 'Select all users for this query', 'bbconnect' ); ?></p>
					<p><input type="checkbox" name="_grexport[all_fields]" value="1" /> <?php _e( 'Select all possible fields', 'bbconnect' ); ?></p>
					<?php do_action( 'bbconnect_expo_user_opts' ); ?>
					
					<h3><?php _e( 'Actions Options', 'bbconnect' ); ?></h3>
					<?php do_action( 'bbconnect_expo_acts_opts' ); ?>
					
					<div class="tright">
						<input type="submit" name="export_members" value="<?php _e( 'Export', 'bbconnect' ); ?>" class="button-primary report-go grexappeal" />
					</div>
					<input type="hidden" name="action[bbconnect_export_process]" value="" />
					
				</div>
				<div class="column_holder"></div>
			</div>
		</form>
	</div>
<?php
}


function bbconnect_export_process() {

	// EXTEND EXECUTION TIME
	set_time_limit( 3600 );
	
	// SET LOCAL GLOBALS
	global $wpdb;
	$blog_prefix = $wpdb->get_blog_prefix( get_current_blog_id() );
	
	// SET A WPR TEMPORARY ARRAY
	$wpr_trans = array( 'email', 'url' );

	$ret_array = array();
	$exp_array = array();
		
	// BYPASS PAGINATION AND GET ALL USERS FROM THIS QUERY
	if ( isset( $_POST['_grexport']['all_users'] ) ) {
		$users = explode( '|', $_POST['_grexport']['all_search'] );
		unset($_POST['_grexport']['all_search']);
	
	} else if ( isset( $_POST['gredit_users'] ) ) {
		$users = array_unique( $_POST['gredit_users'] );
		
	} else {
		$users = false;
	
	}
	
	// SET UP THE DATA ARRAY
	foreach ( $_POST['_grexport'] as $key => $val ) {
		$exp_array[$key] = maybe_unserialize( urldecode( $val ) );
	}
	$exp_array['return'] = true;
	
	
	if ( false != $users ) {
	
		// IF WE WANT ALL FIELDS, DO THAT NOW, ELSE, TAKE WHAT WE'VE SEARCHED FOR
		if ( isset( $_POST['_grexport']['all_fields'] ) ) {
			
			$headers_prep = bbconnect_get_user_metadata();
			foreach ( $headers_prep as $header ) {
				$user_meta = get_option( $header );
				if ( 'taxonomy' == $user_meta['options']['field_type'] ) {
					$headers[$user_meta['meta_key']] = array();
				} else {
					if ( 'wpr' == $user_meta['source'] ) {
						if ( in_array( $user_meta['meta_key'], $wpr_trans ) ) {
							$wpr_key = 'user_'.$user_meta['meta_key'];
						} else if ( 'role' == $user_meta['meta_key'] ) {
							$wpr_key = $blog_prefix.'capabilities';
						} else {
							$wpr_key = $user_meta['meta_key'];
						}
						$headers[$wpr_key] = $user_meta['meta_key'];
					} else if ( 'wp' == $user_meta['source'] || 'user' == $user_meta['source'] ) {
						$headers[$user_meta['meta_key']] = $user_meta['meta_key'];
					} else {
						$headers['bbconnect_'.$user_meta['meta_key']] = $user_meta['meta_key'];
					}
				}
			}
			
			// REPLACE THE FIELD SELECTIONS WITH ALL FIELDS
			$exp_array['table_body'] = $headers;
			$exp_array['post_vars'] = false;
			unset( $headers );
		}
		
		// IF WE HAVE ACTIONS, PROCESS THEM
		if ( isset( $_POST['gredit_actions'] ) )
			$exp_array['action_array'] = apply_filters( 'bbconnect_export_action_array', $_POST['gredit_actions'] );
		
		foreach ( $users as $user_id ) {
			
			// ADD THE CURRENT USER IN
			$exp_array['user_id'] = $user_id;
			$cur_user = bbconnect_rows( $exp_array );
			if ( isset( $cur_user['action'] ) ) {
				if ( isset( $_POST['_grexport']['all_actions'] ) || isset( $_POST['gredit_actions'] ) ) {
					$curact_array = apply_filters( 'bbconnect_export_action_return', $cur_user['action'] );
					unset( $cur_user['action'] );
					foreach( $curact_array as $akey => $aval ) {
						$ret_array[] = $cur_user + $aval;
					}
				} else {
					unset( $cur_user['action'] );
					$ret_array[] = $cur_user;
				}
			} else {
				$ret_array[] = $cur_user;
			}
		}
				
		// APPLY A HEADER
		array_unshift( $ret_array, array_keys( $ret_array[0] ) );
		
		// FIX THAT DAMN MICROSOFT BUG!
		if ( 'ID' == $ret_array[0][0] )
			$ret_array[0][0] = 'user_id';
	
		if ( isset( $_POST['export_title'] ) ) {
			$exp_title = $_POST['export_title'];
		} else { 
			$exp_title = 'export';
		}
		$inc_file = bbconnect_export_filename( sanitize_title_with_underscores( $exp_title ) );
		$ret_file = bbconnect_export_file( $inc_file, $ret_array );
		if ( false != $ret_file ) {
			$export_results = '<div id="progress"><a href="' . $ret_file . '">' . __( 'Your file', 'bbconnect' ) . '</a></div>';
		} else {
			$export_results = __( 'I was not able to complete the export.', 'bbconnect' );
		}
				
	} else {
		$export_results = __( 'Please make a selection', 'bbconnect' );
	}

	$ret_arr = array( 'all_search' => false, 'member_search' => false, 'action_search' => false, 'user_display' => false, 'export_array' => $ret_array, 'max_num_pages' => false, 'post_vars' => false, 'users_per_page' => false, 'users_count' => false, 'export_results' => $export_results );
	
	return $ret_arr;

}

function bbconnect_export_filename( $exp_name, $extension = true ) {
	$exp_today =  date( 'Y-m-d H:i:s', mktime(date('H')+get_option('gmt_offset'), date('i'), date('s'), date('m'), date('d'), date('Y') ) );
	$exp_filename = '';
	$exp_filename .= $exp_name.'_' . date( 'Y-m-d_H-i-s', strtotime( $exp_today ) );
	
	if ( false != $extension )
		$exp_filename .= '.csv';
		
	return $exp_filename;
}

function bbconnect_export_file( $exp_filename, $ret_array ) {
	
	// CREATE THE FILE NAME AND SET THE PATH TO THE CURRENT UPLOADS DIRECTORY
	//global $filepath, $exp_filename, $csv_filename, $exp_today;
	$exp_today =  date( 'Y-m-d H:i:s', mktime(date('H')+get_option('gmt_offset'), date('i'), date('s'), date('m'), date('d'), date('Y') ) );
	$filepath = wp_upload_dir( date( 'Y/m', strtotime( $exp_today ) ) );
	//$filename = "/".$exp_type."_" . date( 'Y-m-d_H-i-s', strtotime( $exp_today ) ) . ".csv";
	$csv_filename = $filepath['path'] . "/" . $exp_filename;
	
	// SAVE THE FILE
	// SET $fp AS THE FILE POINTER TO FILE $filename
	$fp = fopen( $csv_filename,"a" );
			
	if ( $fp ) {
		// WRITE THE FILE
		foreach ( $ret_array as $fields ) {
			fputcsv( $fp, $fields, ',', '"' );
		}
			
			// CLOSE THE FILE
		fclose($fp);
		
		// NOTIFY THE USER
		return $filepath['url'] . '/' . $exp_filename;
		
	} else {
		// HARD STOP
		return false;
		
	}
}
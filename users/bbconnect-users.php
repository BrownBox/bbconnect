<?php

/**
 * Receives BBCONNECT-specific data and prepares it for WP insertion.
 * On insertion scenarios, WORDPRESS takes the lead.
 * On update scenarios BBCONNECT takes the lead.
 *
 * @since 1.0.2
 *
 * @param arr $ivals Optional. The passed data. Default is a $_POST array.
 * @param bool $update Optional. Whether or not to update.
 * @param str $match Optional. The user field to match on, can use metadata but carefully...
 * @param bool $data_handler Optional. Default is to overwrite existing data .
 * @param bool $no_log Optional. Prevents creation of a post to log the event.
 * @param str $log_type Optional. The type of BBCONNECT action to document the source of the insertion.
 * @param str $log_code Optional. The source code of the BBCONNECT action.
 * @param str $title Optional. The title of the BBCONNECT action.
 * @param str $content Optional. The content of the BBCONNECT action.
 * @param int $agent Optional. The ID of the user performing the action.
 *
 * @return int/arr The ID if insertion was successful, otherwise a WP_Error.
 */
function bbconnect_insert_user( $args = '' ) {

	global $current_user, $pppass;

	/* SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
		- Need to remove the POST default
		- reset the type to be a default post type,
		- add other arg to capture the source and insert as meta
		-- possibly mode the 'no_log' logic to not add a note if source is false
		- note the 'private' status now of insertions
		- need to add hooks to check if we should trigger other actions like subscribe
		- perhaps a flag to note those subscribed without their buy-in
	*/
	$defaults = array(
					'ivals' => false,
					'update' => false,
					'match' => false,
					'data_handler' => 'overwrite',
					'no_log' => false,
					'log_type' => 'admin_registration',
					'log_code' => false,
					'title' => 'Registration',
					'content' => '',
					'agent' => $current_user->ID
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( false === $ivals )
		return false;

	// SCRUB!
	$ivals = bbconnect_scrub( 'bbconnect_sanitize', $ivals );

	// SET THE USERDATA ARRAY
	$userdata = array();

	// IF THIS IS AN UPDATE, SET A USER OBJECT TO TEST AGAINST
	if ( false != $update && false != $match ) {

		// SET THE DEFAULT MATCHES
		$wp_match_reserve = array( 'slug', 'email', 'id', 'login' );

		// IF WE DON'T HAVE A DEFAULT, TRY AND EXTRACT THE USER ID
		// REGARDLESS, DELIVER A USER OBJECT
		if ( !in_array( $match, $wp_match_reserve ) ) {
			//$wpdb->flush();
			global $wpdb;
			$match_value = $wpdb->get_results( $wpdb->prepare( "SELECT $wpdb->usermeta.user_id FROM $wpdb->usermeta WHERE $wpdb->usermeta.meta_value = %s", $ivals['bbconnect_user_meta'][$match] ), ARRAY_N );
			$wpdb->flush();
			if ( empty( $match_value ) || !isset( $match_value[0] ) || empty( $match_value[0] ) ) {
				$user = false;
			} else {
				if ( 1 < count( $match_value[0] ) ) {
					$user_id = new WP_Error('sorry', 'I found multiple users matching this field -- so I did not do anything');
					return $user_id;
				} else {
					$match_single = array_shift( $match_value[0] );
					$user = get_user_by( 'id', $match_single );
				}
			}
		} else {
			$user = get_user_by( $match, $ivals[$match] );
		}

	} else {
		$user = false;
	}

	// SET THE USER LOGIN WITH A RANDOM STRING IF NEED BE
	if ( !empty( $ivals['user_login'] ) ) {
		$userdata['user_login'] = $ivals['user_login'];
	} else if ( !$user ) {
		$username_prefix = get_option( '_bbconnect_username_prefix' );
		$upre = '';
		if ( false != $username_prefix ) {
			if ( '%y%' == $username_prefix ) {
				$upre = date( 'Y' );
			} else {
				$upre = $username_prefix;
			}
		}
		$userdata['user_login'] = bbconnect_random( array( 'name' => $upre, 'compact' => true ) );
	}

	// SET THE USER EMAIL WITH A RANDOM STRING IF NEED BE
	if ( !empty( $ivals['email'] ) ) {
		$userdata['user_email'] = $ivals['email'];
	} else if ( !$user ) {
		$userdata['user_email'] = $userdata['user_login'] . '@noreply.invalid';
	}
	/*
	if ( false == is_email( $userdata['user_email'] ) ) {
		$user_id = new WP_Error('sorry', 'This email is incomplete.');
		return $user_id;
	}
	*/

	// SET THE DISPLAY NAME
	if ( !empty( $ivals['display_name'] ) ) {
		$userdata['display_name'] = $ivals['display_name'];
	} else if ( !$user ) {
		$dname = '';
		if ( isset( $ivals['bbconnect_user_meta']['first_name'] ) || isset( $ivals['bbconnect_user_meta']['last_name'] ) ) {
			if ( isset( $ivals['bbconnect_user_meta']['first_name'] ) )
				$dname .= $ivals['bbconnect_user_meta']['first_name'] . ' ';

			if ( isset( $ivals['bbconnect_user_meta']['last_name'] ) )
				$dname .= $ivals['bbconnect_user_meta']['last_name'];

		} else if ( isset( $ivals['bbconnect_user_meta']['organization'] ) ) {
			$dname .= $ivals['bbconnect_user_meta']['organization'];
		}
		$userdata['display_name'] = trim( $dname );
	}

	// SET THE NICKNAME
	if ( !empty( $ivals['bbconnect_user_meta']['nickname'] ) ) {
		$userdata['nickname'] = $ivals['bbconnect_user_meta']['nickname'];
	} else if ( !$user ) {
		if ( isset( $ivals['bbconnect_user_meta']['first_name'] ) ) {
			$fname = $ivals['bbconnect_user_meta']['first_name'];
		} else {
			$fname = '';
		}
		$userdata['nickname'] = $fname;
	}

	// OPTIONALLY SET THE ROLE IF DESIRED -- WILL OTHERWISE DEFAULT TO WP SETTINGS
	if ( !empty( $ivals['role'] ) ) {
		$userdata['role'] = $ivals['role'];
	}

	// OPTIONALLY SET THE REGISTRATION DATE IF DESIRED -- WILL OTHERWISE DEFAULT TO WP SETTINGS
	if ( !empty( $ivals['user_registered'] ) ) {
		$userdata['user_registered'] = $ivals['user_registered'];
	}

	// OPTIONALLY SET THE PASSWORD -- ALL ERROR CHECKING SHOULD BE DONE PRIOR
	// MAKE THE PASSWORD GLOBAL FOR NOTIFICATION PURPOSES
	if ( !empty( $ivals['pass1'] ) ) {
		$pppass = $ivals['pass1'];
		$userdata['user_pass'] = $pppass;
	} else if ( !$user ) {
		$pppass = wp_generate_password();
		$userdata['user_pass'] = $pppass;
	}

	// LASTLY, SET THE URL!
	if ( !empty( $ivals['url'] ) ) {
		$userdata['user_url'] = $ivals['url'];
	}

	// SET THE USER EMAIL WITH A RANDOM STRING IF NEED BE
	if ( !empty( $ivals['show_admin_bar_front'] ) ) {
		$userdata['show_admin_bar_front'] = $ivals['show_admin_bar_front'];
	} else {
		$sabf = bbconnect_get_option( 'show_admin_bar_front' );
		$userdata['show_admin_bar_front'] = $sabf['options']['choices'];
	}

	// MAKE THE INSERTION. IF WE'RE UPDATING, DO SO AFTER THE META UPDATE
	if ( !$user ) {
		$user_id = wp_insert_user( $userdata );
	} else {
		$userdata['ID'] = $user->ID;
		$user_id = $user->ID;
	}

	// IF WE GOT AN ERROR, RETURN THE ERROR
	if ( is_wp_error( $user_id ) )
		return $user_id;

	// UPDATE THE USER META AND TAXONOMIES
	bbconnect_update_user_metadata( array( 'user_id' => $user_id, 'uvals' => $ivals, 'data_handler' => $data_handler ) );

	// IF WE'RE UPDATING, DO IT NOW
	if ( $user && isset( $userdata['ID'] ) )
		$user_id = wp_update_user( $userdata );

	// IF WE GOT AN ERROR, RETURN THE ERROR
	if ( is_wp_error( $user_id ) )
		return $user_id;

	// DOCUMENT THE SOURCE OF THE USER'S INSERTION
	if ( false == $no_log ) {
		$postdata['post_title'] = $title;
		$postdata['post_content'] = $content;
		$postdata['post_status'] = 'private';
		$postdata['post_author'] = $user_id;
		$postdata['post_type'] = 'pp_log';

		$post_id = wp_insert_post( $postdata, true );

		// UPDATE THE META
		if ( intval( $post_id ) ) {
			update_post_meta( $post_id, '_pp_log_code', $log_code );
			update_post_meta( $post_id, '_pp_log_type', $log_type );
			if ( 0 !== $agent ) {
				update_post_meta( $post_id, '_pp_agent', $agent );
				$ins_log = array( array( 'id' => $agent, 'date' => time() ) );
				update_post_meta( $post_id, '_pp_log', $ins_log );
			}
		}
	}

	return $user_id;

}


/**
 * Receives BBCONNECT-specific data and prepares it for WP updates. On update scenarios, BBCONNECT takes the lead.
 *
 * @since 1.0.2
 *
 * @param int $id Required. The ID of the target user.
 * @param arr $uvals Required. The passed data. Default is a $_POST array.
 *
 * @return int/arr The ID if insertion was successful, otherwise a WP_Error.
 */
function bbconnect_update_user_metadata( $args = '' ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'user_id' => false,
					'uvals' => false,
					'source' => false,
					'data_handler' => 'overwrite',
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( empty( $uvals ) || empty( $user_id ) )
		return false;

	// APPLY AN ACTION IF CONDITIONS ARE PRESENT
	// IN ALL CASES EXCEPT NEW USERS, BBCONNECT LEADS
	if ( false != $source ) {
		do_action( 'bbconnect_trigger_user_metadata', $user_id, $uvals, $source );
	}

	// SANITIZE THE DATA
	$uvals = bbconnect_scrub( 'bbconnect_sanitize', $uvals );

	// PROCESS THE USER META / MERGE TAGS
	// IF LOGIC PREVENTS NASTY NOTICES IF THEY ACCIDENTALLY HIT 'EDIT'
	if ( isset( $uvals['bbconnect_user_meta'] ) ) {

		// GO AHEAD AND PROCESS THE FULL SUBMISSION
		foreach ( $uvals['bbconnect_user_meta'] as $key => $value ) {

			$bbconnect_{$key} = bbconnect_get_option( $key );
			if ( empty( $bbconnect_{$key} ) )
				continue;

			// IF THIS IS A SECTION, SKIP IT
			if ( 'section' == $bbconnect_{$key}['options']['field_type'] )
				continue;

			if ( is_array( $value ) ) {

				// FOR MULTI-TEXT LOOP THROUGH TO MAKE SURE NULL VALUES ARE REMOVED FROM PROFILE EDITS
				if ( 'multitext' == $bbconnect_{$key}['options']['field_type'] ) {
					$new_value = array();
					foreach ( $value as $subvalue ) {
						if ( !empty( $subvalue['value'] ) ) {
							$new_value[] = $subvalue;
						}
					}
					$value = $new_value;
				}

				// FOR TAXONOMIES
				if ( 'taxonomy' == $bbconnect_{$key}['options']['field_type'] ) {

					if ( isset( $uvals['bbconnect_user_taxonomy_options'] ) )
						$data_handler = $uvals['bbconnect_user_taxonomy_options'][$key];

					if ( 'append' == $data_handler || 'append_no_overwrite' == $data_handler ) {
						$pre_array = get_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), true );
						if ( is_array( $pre_array ) ) {
							$new_array = array();
							foreach ( $value as $subvalue ) {
								if ( !in_array( $subvalue, $pre_array ) )
									$new_array[] = $subvalue;
							}
							$value = array_merge( $pre_array, $new_array );
							//update_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), $merged );
						} else {
							//update_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), $value );
						}

					} else if ( 'remove' == $data_handler ) {
						$pre_array = get_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), true );
						if ( is_array( $pre_array ) ) {
							$new_array = array();
							foreach ( $value as $subvalue ) {
								$pre_key = array_search( $subvalue, $pre_array );
								if ( false === $pre_key ) {
								} else {
									unset( $pre_array[$pre_key] );
								}
							}
							$value = $pre_array;
						}
					}

					$value = array_filter( $value );
				}
			}

			if ( 'wpr' == $bbconnect_{$key}['source'] && 'url' == $key ) {

				wp_update_user( array ( 'ID' => $user_id, 'user_url' => $value ) );

			} else {

				if ( false !== strpos( $data_handler, 'no_overwrite' ) ) {
					${$key} = get_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), true );
					if ( '' != ${$key} )
						continue;
				}

				if ( empty( $value ) ) {
					delete_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ) );
				} else {
					update_user_meta( $user_id, bbconnect_key( $key, $bbconnect_{$key}['source'] ), $value );
				}

			}
			/*} elseif ( 'bbconnect' != $bbconnect_{$key}['source'] ) {

				// CHECK TO MAKE SURE WE'RE ALLOWING OVERWRITES
				if ( isset( $_POST['no_overwrite'] ) ) {
					${$key} = get_user_meta( $user_id, $key, true );
					if ( '' != ${$key} )
						continue;
				}

				if ( '' != $value || !empty( $value ) ) {
					update_user_meta( $user_id, $key, $value );
				} else {
					delete_user_meta( $user_id, $key );
				}
			} elseif ( 'bbconnect' == $bbconnect_{$key}['source'] ) {

				// CHECK TO MAKE SURE WE'RE ALLOWING OVERWRITES
				if ( isset( $_POST['no_overwrite'] ) ) {
					${$key} = get_user_meta( $user_id, 'bbconnect_'.$key, true );
					if ( '' != ${$key} )
						continue;
				}

				if ( '' != $value || !empty( $value ) ) {
					update_user_meta( $user_id, 'bbconnect_'.$key, $value );
				} else {
					delete_user_meta( $user_id, 'bbconnect_'.$key );
				}
			}*/

		}

	}

	/*
	// PROCESS THE USER TAXONOMIES / INTEREST GROUPINGS
	if ( isset( $uvals['bbconnect_user_taxonomy'] ) ) {
		foreach ( $uvals['bbconnect_user_taxonomy'] as $key => $value ) {

			// IF THIS IS A GROUP ACTION, EVALUATE IF THIS IS AN OVERWRITE OR AN APPEND (DEFAULT)
			if ( isset( $uvals['bbconnect_user_taxonomy_options'] ) ) {


			} else {
				if ( false !== array_search( 'falsetto', $value ) ) {
					$f_key = array_search( 'falsetto', $value );
					unset( $value[$f_key] );
				}

				// CHECK TO MAKE SURE WE'RE ALLOWING OVERWRITES
				if ( isset( $_POST['no_overwrite'] ) ) {
					${$key} = get_user_meta( $user_id, 'bbconnect_'.$key, true );
					if ( '' != ${$key} )
						continue;
				}

				if ( !empty( $value ) ) {
					update_user_meta( $user_id, 'bbconnect_'.$key, $value );
				} else {
					delete_user_meta( $user_id, 'bbconnect_'.$key );
				}
			}
		}
	}
	*/
	// ALLOW PLUGINS TO PERFORM ANY ACTIONS BEFORE THE USER TABLE IS UPDATED
	do_action( 'bbconnect_update_user_metadata_ext', $user_id, $uvals );

}


/**
 * General function for determining which fields to show within other functions. Default to hiding fields unless explicitly asked for inclusion.
 *
 * @since 1.0.0
 * @param $meta arr. The structured array of the field
 * @param $bbconnect_cap str. The bbconnect-specific capability
 * @param $plu
 * @param $param
 * @param $param
 * @return return type
 */

function bbconnect_hide_meta( $args = '' ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'meta' => array(),
					'bbconnect_cap' => '',
					'group_override' => false,
					'action' => false
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	// EXIT GRACEFULLY IF NEED BE
	if ( false == $meta )
		return false;

	// MAKE SURE ADMINS AT LEAST, CAN SEE IT
	if ( false == $meta['options']['admin'] )
		return true;

	// SHOW THE ROLE ONLY TO ADMINS WORKING ON THE BACKEND
	if ( 'role' == $meta['meta_key'] ) {
		if ( is_admin() && current_user_can( 'list_users' ) ) {
			return false;
		} else {
			return true;
		}
	}

	// HANDLE FIELDS BASED ON THE PRESENT ACTION
	if ( '-bulk-edit' == $action || 'bulk-edit' == $action ) {

		$allow = get_option( '_bbconnect_bulk_edit_allow' );
		if ( false == $allow )
			$allow = array();

		if ( 'user_login' == $meta['meta_key'] || 'email' == $meta['meta_key'] )
			return true;

		if ( 'display_name' == $meta['meta_key'] && !in_array( 'display_name', $allow ) )
			return true;

		if ( 'pass' == $meta['meta_key'] && !in_array( 'pass' == $meta['meta_key'], $allow ) )
			return true;

		if ( 'plugin' == $meta['meta_key'] && !in_array( 'plugin' == $meta['meta_key'], $allow ) )
			return true;

		return false;

	// FOR REGISTRATION, LET THE ADMINS DETERMINE WHAT IS SHOWN
	// REGARDLESS IF ONLY ADMINS CAN SEE IT OR NOT.
	// THIS IS GENERALLY USEFUL FOR EITHER SLIMMING DOWN THE PROFILE OR EXPANDING IT FOR ONE-TIME QUESTIONS
	} else if ( 'register' == $action ) {

		// IF IT'S A GROUP, EVALUATE IT
		if ( 'group' == $meta['options']['field_type'] && false == $group_override )
			return false;

		if ( false != $meta['options']['signup'] )
			return false;

		return true;

	// THIS IS MOST LIKELY AN EDIT SCENARIO BUT LEAVE IT OPEN AS A CATCH-ALL
	} else {

		// IF IT'S A GROUP, EVALUATE IT
		if ( 'group' == $meta['options']['field_type'] && false == $group_override )
			return false;

		// HIDE IT FROM USERS IF SO SELECTED
		if ( false != $meta['options']['user'] )
			return false;

		// HIDE IT FROM USERS IF SO SELECTED
		if ( false == $meta['options']['user'] && current_user_can( 'edit_users' ) )
			return false;

		return true;

		/* IF IT'S REQUIRED, MAKE IT VISIBLE
		if ( false != $meta['options']['req'] )
			return false;
		*/
	}

	return true;
}

/*
function bbconnect_get_field_status( $val, $key, $args ) {
	//if ( isset( $args[$key] ) && $val === $args[$key] )
	if ( key( $args ) === $key ) {
		if ( is_bool( $args[$key] ) && !empty( $args[$key] ) && false != $val )
			return true;
		}
}


function bbconnect_get_field_break( $meta_key, $user_meta, $args = array(), $attribute ) {
	if ( empty( $args ) ) {
		return $meta_key;

	} else {
		foreach ( $args as $key => $val ) {
			if ( 'value' != $attribute ) {
				if ( array_walk_recursive( $user_meta, 'bbconnect_get_field_status', $val ) )
					return $meta_key;
			} else {
				if ( in_array_r( $val, $user_meta ) )
					return $meta_key;
			}
		}
	}

	return false;
}
*/
/**
 * Parses the stored arrangement of user fields and returns the meta_keys of all fields.
 *
 * @since 1.0.0
 * @param $section_break bool. Returns all fields at or above the Section Level.
 * @param $group_break bool. Returns all fields at or above the Group Level.
 * @param $field_break bool. Returns all fields matching certain criteria.
 * @param $attribute str. What value to return key | value.
 * @return return type
 */

function bbconnect_get_user_metadata( $args = '' ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	// DEFAULT SHOWS EACH FIELD REGARDLESS OF ORGANIZATION
	// BUT EXCLUDES FIELDS LABELED AS GROUP OR SECTION
	$defaults = array(
					'section_break' => false, // DOES NOT SHOW FIELDS INSIDE SECTIONS AND IGNORES GROUP FIELD BUT DOES SHOW SECTION FIELD
					'group_break' => false, // DOES NOT SHOW FIELDS INSIDE GROUPS AND IGNORES SECTION FIELDS BUT DOES SHOW GROUP FIELD
					'include' => array(), // WILL RETURN ONLY FIELDS MATCHING THIS TYPE
					'exclude' => array(), // WILL RETURN ONLY FIELDS NOT MATCHING THIS TYPE
					'return_val' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	$bbconnect_user_meta = get_option( '_bbconnect_user_meta' );
	$bbconnect_user_meta_arr = array();

	foreach ( $bbconnect_user_meta as $key => $val ) {

		// IF IT'S IN A COLUMN
		if ( is_array( $val ) ) {
			foreach ( $val as $subkey => $subval )
				$bbconnect_user_meta_arr = bbconnect_get_user_metadata_val( $bbconnect_user_meta_arr, $subval, $args );

		// IF IT'S FREE
		} else {
			$bbconnect_user_meta_arr = bbconnect_get_user_metadata_val( $bbconnect_user_meta_arr, $val, $args );
		}

	}

	// FILTER OUT SPECIFIC FIELDS
	if ( !empty( $include ) ) {
		foreach ( $bbconnect_user_meta_arr as $akey => $aval ) {
			if ( !in_array( $aval['options']['field_type'], $include ) )
				unset( $bbconnect_user_meta_arr[$akey] );

		}
	}

	if ( !empty( $exclude ) ) {
		foreach ( $bbconnect_user_meta_arr as $akey => $aval ) {
			if ( in_array( $aval['options']['field_type'], $exclude ) )
				unset( $bbconnect_user_meta_arr[$akey] );

		}
	}

	return array_filter( $bbconnect_user_meta_arr );

}


// LOCALLY FILTER THE ARRAY
function bbconnect_get_user_metadata_val( $bbconnect_user_meta_arr, $val, $args ) {

	// GRAB THE META DATA
	$user_meta = bbconnect_get_option( $val );

	// IF IT'S A SECTION
	if ( 'section' == $user_meta['options']['field_type'] ) {

		// RETURN ITS DETAILS
		if ( false != $args['section_break'] ) {
			$bbconnect_user_meta_arr[] = bbconnect_val( $user_meta, $args['return_val'] );
		} else {
			// LOOP THROUGH ITS CHILDREN
			if ( isset( $user_meta['options']['choices'] ) && !empty( $user_meta['options']['choices'] ) ) {
				foreach ( $user_meta['options']['choices'] as $section_key => $section_val ) {

					// GRAB THE META DATA
					$section_meta = bbconnect_get_option( $section_val );

					// IF IT'S A GROUP
					if ( 'group' == $section_meta['options']['field_type'] ) {

						// RETURN ITS DETAILS
						if ( false != $args['group_break'] ) {
							$bbconnect_user_meta_arr[] = bbconnect_val( $section_meta, $args['return_val'] );
						} else {
							// LOOP THROUGH ITS CHILDREN
							foreach ( $section_meta['options']['choices'] as $group_key => $group_val ) {
								$bbconnect_user_meta_arr[] = bbconnect_val( bbconnect_get_option( $group_val ), $args['return_val'] );
							}
						}

					} else {

						$bbconnect_user_meta_arr[] = bbconnect_val( $section_meta, $args['return_val'] );

					}

				}
			}
		}

	// IF IT'S A GROUP
	} elseif ( 'group' == $user_meta['options']['field_type'] ) {

		// RETURN ITS DETAILS
		if ( false != $args['group_break'] ) {
			$bbconnect_user_meta_arr[] = bbconnect_val( $user_meta, $args['return_val'] );
		} else {
			// LOOP THROUGH ITS CHILDREN
			foreach ( $user_meta['options']['choices'] as $group_key => $group_val ) {
				$bbconnect_user_meta_arr[] = bbconnect_val( bbconnect_get_option( $group_val ), $args['return_val'] );
			}
		}

	} else {

		$bbconnect_user_meta_arr[] = bbconnect_val( $user_meta, $args['return_val'] );

	}

	return $bbconnect_user_meta_arr;

}




function bbconnect_primary_marker( $meta, $user_id = '' ) {

	if ( isset( $meta['group_type'] ) && 'address' == $meta['group_type'] ) {

		$prim_test = get_user_meta( $user_id, 'bbconnect_pp_primary', true );

		$key = $meta['meta_key'];

		// SET THE SAVED PRIMARY INFORMATION
		if ( $key == $prim_test ) {
			$p_check = ' checked';
		} else if ( empty( $prim_test ) && $key == 'address_1' ) {
			$p_check = ' checked';
		} else {
			$p_check = '';
		}

		?>
			<span class="bbconnect-qualifier">
				<input type="radio" name="bbconnect_user_meta[pp_primary]" class="bbconnect-primary" value="<?php echo $key; ?>"<?php echo $p_check; ?> /> <span><?php _e( 'primary', 'bbconnect' ); ?></span>
			</span>
		<?php
	}

}

?>
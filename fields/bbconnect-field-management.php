<?php

function bbconnect_profile_fields_update() {
	if ( isset( $_POST['data'] ) ) {
		if ( !check_ajax_referer( 'bbconnect-admin-nonce', 'bbconnect_admin_nonce', false ) ) {
			wp_die( __( 'that was an illegal action. no data was saved.', 'bbconnect' ) );
		}

		$post_data = stripslashes_deep( maybe_unserialize( rawurldecode( $_POST['data'] ) ) );
		$post_vars = explode( '&bbconnect_user_', $post_data );

		$post_vars = explode('&bbconnect_user_meta_options', $post_data);
		$post_proc = array();
		$test_proc = array();
		foreach( $post_vars as $var ) {
			$pairs = explode('=', $var);
			preg_match_all( "/\[.*?\]/", $pairs[0], $matches );
			$match = $matches[0];
			if ( 1 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])] = urldecode( $pairs[1] );
			} else if ( 2 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])] = urldecode( $pairs[1] );
			} else if ( 3 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])] = urldecode( $pairs[1] );
			} else if ( 4 == count( $match ) ) {
				if ( '' == str_replace(array('[',']'),'',$match[3]) ) {
					$newtry = bbconnect_random();
				} else {
					$newtry = str_replace(array('[',']'),'',$match[3]);
				}
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])][$newtry] = urldecode( $pairs[1] );
			} else if ( 5 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])][str_replace(array('[',']'),'',$match[3])][str_replace(array('[',']'),'',$match[4])] = urldecode( $pairs[1] );
			} else if ( 6 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])][str_replace(array('[',']'),'',$match[3])][str_replace(array('[',']'),'',$match[4])][str_replace(array('[',']'),'',$match[5])] = urldecode( $pairs[1] );
			} else if ( 7 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])][str_replace(array('[',']'),'',$match[3])][str_replace(array('[',']'),'',$match[4])][str_replace(array('[',']'),'',$match[5])][str_replace(array('[',']'),'',$match[6])] = urldecode( $pairs[1] );
			} else if ( 8 == count( $match ) ) {
				$post_proc[str_replace(array('[',']'),'',$match[0])][str_replace(array('[',']'),'',$match[1])][str_replace(array('[',']'),'',$match[2])][str_replace(array('[',']'),'',$match[3])][str_replace(array('[',']'),'',$match[4])][str_replace(array('[',']'),'',$match[5])][str_replace(array('[',']'),'',$match[6])][str_replace(array('[',']'),'',$match[7])] = urldecode( $pairs[1] );
			}
		}
		bbconnect_meta_options_form_save( $post_proc );
	} else {
		_e( 'There seems to be a problem.', 'bbconnect' );
	}
	die();

}


function bbconnect_meta_options_form_save( $post_arr ) {

	// LOOP THROUGH THE POST VARS AND ACCESS THE OPTIONS
	foreach ( $post_arr as $key => $value ) {

		if ( !is_array( $value ) )
			continue;

		if ( !isset( $value['meta_key'] ) || empty( $value['meta_key'] ) )
			continue;

		// MAKE SURE ANY NEW ELEMENTS ARE PROPERLY STRUCTURED
		if ( isset( $value['status'] ) && 'new_ele' == $value['status'] ) {
			$value['meta_key'] = sanitize_title_with_underscores( $value['meta_key'] );
			$key = sanitize_title_with_underscores( $value['meta_key'] );
			unset( $value['status'] );
		}

		// MAKE SURE ALL ELEMENTS ARE PROPERLY SCOPED, OTHERWISE, DELETE THEM
		if ( false == $key || strlen( $key ) <= 2 || false !== strpos( $key, '_bbcdel_' ) || !isset( $value['source'] ) ) {

			if ( isset( $value['source'] ) && 'wpr' == $value['source'] ) {
			} else {

				if ( isset( $value['source'] ) && 'taxonomy' == $value['options']['field_type'] ) {
					if ( 'bbconnect' == $value['source'] || 'user' == $value['source'] )
						bbconnect_delete_wp_taxonomy( $value['meta_key'] );
				}
				unset( $post_arr[$key] );
				$exclude = true;

			}

		}

		// IF THIS IS A BBCONNECT-INITIATED TAXONOMY, UPDATE THE INTERNAL ARRAY
		// IN ORDER TO INSTANTIATE THIS AS A TAXONOMY ON INIT
		if ( isset( $value['source'] ) && 'taxonomy' == $value['options']['field_type'] && !isset( $exclude ) ) {
			if ( 'bbconnect' == $value['source'] || 'user' == $value['source'] ) {
				if ( isset( $value['options']['post_types'] ) ) {
					$pargs = $value['options']['post_types'];
				} else {
					$pargs = array();
				}
				$tax_args = array( 'tax' => $value['meta_key'], 'single' => $value['name'], 'location' => $pargs );
				if ( false === bbconnect_create_wp_taxonomy( $tax_args ) ) {
					unset( $post_arr[$key] );
					$exclude = true;
				}
			}
		}

		// STEP THROUGH THE CHOICES AND SAVE THEM
		if (
			'group' == $value['options']['field_type'] ||
			'password' == $value['options']['field_type'] ||
			'wpr' == $value['source'] ||
			'wp' == $value['source'] ||
			isset( $value['group'] ) ||
			'segment_id' == $value['meta_key']
			) {

			if ( isset( $value['options']['choices'] ) )
				$value['options']['choices'] = maybe_unserialize( stripslashes_deep( $value['options']['choices'] ) );

		} else {
			if ( 'select' == $value['options']['field_type'] || 'radio' == $value['options']['field_type'] || 'multitext' == $value['options']['field_type'] ) {
				// RESET THE ARRAY
				$pro_choices = array();
				foreach( $value['options']['choices'] as $ckey => $cvalue ) {
					if	( '' == $cvalue['key'] ) { $cvalue['key'] = $cvalue['value']; }
					$pro_choices[$cvalue['key']] = $cvalue['value'];
				}
				$value['options']['choices'] = $pro_choices;
			} else if ( 'section' == $value['options']['field_type'] ) {
				if ( isset($value['options']['choices']) ) {
					$value['options']['choices'] == $value['options']['choices'];
				}
			} else {
				if ( isset( $value['options']['choices'] ) )
					$value['options']['choices'] = maybe_unserialize( urldecode( $value['options']['choices'] ) );
			}
		}

		// IF THESE FIELDS ARE NOT PART OF AN ADDRESS OR OTHER GROUP...
		if ( isset( $value['group'] ) || 'segment_id' == $value['meta_key']) {
			$exclude = true;
		}

		// IF THE FIELD IS PART OF A SECTION, WE WANT TO EXCLUDE IT
		// OTHERWISE, WE WANT TO REMOVE THE REFERENCE
		if (
			isset( $value['section'] ) &&
			!empty( $value['section'] ) &&
			!isset( $post_arr['_bbcdel_'.$value['section']] )
		) {
			$exclude = true;
		} else {
			unset( $value['section'] );
		}

		$exclude = apply_filters('bbconnect_meta_options_exclude', $exclude, $value);

		if ( !isset( $exclude ) ) {

			// FIND THE COLUMN IF APPLICABLE
			$columns = array( 'column_1', 'column_2', 'column_3' );
			if ( !empty( $value['column'] ) && in_array( $value['column'], $columns ) ) {
				$col = $value['column'];
			} else {
				$col = 'column_1';
				$value['column'] = 'column_1';
			}

			$new_bbconnect_user_meta[$col][] = bbconnect_get_option( $key, true );

		} else {
			unset( $exclude );
		}

		// UPDATE THE OPTION AND THE INDEX KEY
		if ( update_option( bbconnect_get_option( $key, true ), $value ) ) {
			$bbc_update = true;
		}

		// ADD ACTION FOR PLUGINS TO OPERATE ON
		do_action( 'bbconnect_addon_process_user_meta', $value );

	}

	// UPDATE THE INDEX
	if ( update_option( '_bbconnect_user_meta', $new_bbconnect_user_meta ) || isset( $bbc_update ) ) {
		_e( 'Fields updated!', 'bbconnect' );
	} else {
		_e( 'There were no fields to update.', 'bbconnect' );
	}

}


// PRINT OUT THE OPTIONS FORM
function bbconnect_meta_options_form() {

	// STOP THEM IF THEY SHOULDN'T BE HERE
	if ( !current_user_can('list_users' ) )
		wp_die( __('You do not have sufficient permissions to access this page.') );

	//bbconnect_meta_options_form_save();

	// SET GLOBAL VARIABLES
	global $current_user, $tax_lookup;

	// SET THE USER INFORMATION
	get_currentuserinfo();

	// SET THE BASE VARIABLES
	$bbconnect_user_meta = get_option( '_bbconnect_user_meta' );
	$tax_lookup = array();

?>

	<div id="bbconnect" class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
		<h2><?php _e( 'Manage Fields', 'bbconnect' ); ?> <span id="nebula"><a class="button" id="tim" title="meta"><?php _e( 'Add a Field', 'bbconnect' ); ?></a></span></h2>

		<form id="profile-fields" enctype="multipart/form-data" action="" method="POST">
		<input type="hidden" name="save_options" value="1" />
		<?php
			wp_nonce_field('bbconnect-meta-nonce');
			//$pluginFolder = get_bloginfo('wpurl') . '/wp-content/plugins/' . dirname( plugin_basename( __FILE__ ) ) . '/';
		?>

		<div id="manage-fields" class="options-panel">
			<fieldset>
				<div class="options-field"><div class="inside" style="display: block;">

					<div id="column_1_holder">
						<h2><?php _e( 'Column One', 'bbconnect' ); ?></h2>
						<div id="new-holder"></div>
						<ul id="column_1" class="option-sortable connected-option-sortable primary-list column">
						<?php
							// LOOP THROUGH ALL OF THE FIELDS REGISTERED WITH THE SYSTEM
							// RETRIEVE THEIR VALUES FOR DISPLAY
							// IF IT'S A GROUP, MAKE A SUBLIST
							// WE'LL USE DRAG & DROP FOR SORTING
							if ( isset( $bbconnect_user_meta['column_1'] ) ) {
								foreach ( $bbconnect_user_meta['column_1'] as $key => $value ) {
									$user_meta = get_option( $value );
									if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) )
										continue;

									bbconnect_user_meta_list( $user_meta );
								}
								// PREP FOR STRAGGLERS, UNSET THE COLUMN
								unset( $bbconnect_user_meta['column_1'] );

							} else {
								foreach ( $bbconnect_user_meta as $key => $value ) {
									$user_meta = get_option( $value );
									if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) )
										continue;

									bbconnect_user_meta_list( $user_meta );
								}
							}

						?>

						</ul>
					</div>

					<div id="column_2_holder">
						<h2><?php _e( 'Column Two', 'bbconnect' ); ?></h2>
						<ul id="column_2" class="option-sortable connected-option-sortable secondary-list column">
						<?php
							if ( isset( $bbconnect_user_meta['column_2'] ) ) {
								foreach ( $bbconnect_user_meta['column_2'] as $key => $value ) {
									$user_meta = get_option( $value );
									if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) )
										continue;

									bbconnect_user_meta_list( $user_meta );
								}
								// PREP FOR STRAGGLERS, UNSET THE COLUMN
								unset( $bbconnect_user_meta['column_2'] );

							}

						?>
						</ul>
					</div>

				</div></div>
				<div class="options-field">
					<h2><?php _e( 'Unused fields', 'bbconnect' ); ?></h2>
					<div class="inside t-panel unused-drawer" style="display: block;">

					<div id="column_3_holder">
						<ul id="column_3" class="option-sortable connected-option-sortable secondary-list column">
						<?php
							if ( isset( $bbconnect_user_meta['column_3'] ) ) {
								foreach ( $bbconnect_user_meta['column_3'] as $key => $value ) {
									$user_meta = get_option( $value );
									if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) )
										continue;

									bbconnect_user_meta_list( $user_meta );
								}
								// PREP FOR STRAGGLERS, UNSET THE COLUMN
								unset( $bbconnect_user_meta['column_3'] );

							}

							// SPECIAL CASE FOR STRAGGLERS
							if ( !empty( $bbconnect_user_meta ) ) {
								foreach ( $bbconnect_user_meta as $key => $value ) {
									$user_meta = get_option( $value );
									if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) )
										continue;

									bbconnect_user_meta_list( $user_meta );
								}
							}

							// RUN A LOOKUP AGAINST WORDPRESS
							$taxonomies = get_taxonomies( '', 'objects' );
							if ( $taxonomies ) {
								foreach ( $taxonomies as $taxonomy ) {
									bbconnect_user_meta_list( null, $taxonomy );
								}
							}

						?>
						</ul>
					</div>

				</div></div>
			</fieldset>
		</div>

		<div class="submit right">
			<input type="submit" id="save_options" name="save_options" value="Save Your Options" class="button-primary" />
		</div>

		</form>
	</div>
<?php
}


function bbconnect_user_meta_list( $user_meta = null, $taxonomy = null ) {

	// IF NO PARAMETERS ARE CALLED, BAIL
	if ( empty( $user_meta ) && empty( $taxonomy ) )
		return false;

	// SET SOME DEFAULTS MANUALLY
	$admin = $user_meta['options']['admin'];
	$user = $user_meta['options']['user'];
	$reports = $user_meta['options']['reports'];
	$public = $user_meta['options']['public'];
	$signup = $user_meta['options']['signup'];
	$li_id = '';

	// IF TAXONOMY IS EMPTY BUT USER META IS NOT, LOOK UP THE FIELD TYPE
	// IF IT'S A TAXONOMY,
	if ( empty( $taxonomy ) && 'taxonomy' == $user_meta['options']['field_type'] ) {

		$name = $user_meta['meta_key'];
		$name_arr = array( 'name' => $name );
		$taxonomies = get_taxonomies( $name_arr, 'objects' );
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				$taxonomy = $taxonomy;
			}
		}

	}

	// CHECK FOR ANY ERRORS ACQUIRED FROM THE HOOKS OR PROCESSING
	if ( isset( $user_meta['errors'] ) && !empty( $user_meta['errors'] ) ) {
		$e_class = ' class="error"';
		$e_messages = '<p>' . join( ', ', $user_meta['errors'] ) . '</p>';
	} else {
		$e_class = ' style="display: none;"';
		$e_messages = '';
	}

	// SPECIAL EXCEPTION FOR TAXONOMIES REGISTERED OUTSIDE OF BBCONNECT
	if ( is_object( $taxonomy ) && !empty( $taxonomy ) ) {

		if ( isset( $user_meta['source'] ) && 'wp' != $user_meta['source'] ) {
			$meta_key = $user_meta['meta_key'];
			$name = $user_meta['name'];
			if ( !isset( $user_meta['options']['field_type'] ) ) {
				$field_type = 'taxonomy';
			} else {
				$field_type = $user_meta['options']['field_type'];
			}
		} else {
			$meta_key = $taxonomy->name;
			$name = $taxonomy->labels->name;
			$wp_tax = true;
			$field_type = 'taxonomy';
		}

		// EXCLUDE SOME WP DEFAULTS
		$tax_exclusions = array( 'nav_menu', 'post_format', 'link_category' );
		if ( in_array( $meta_key, $tax_exclusions ) )
			return false;

	} else {
		$meta_key = $user_meta['meta_key'];
		$name = $user_meta['name'];
		$no_tax = true;
		$field_type = $user_meta['options']['field_type'];
	}

	// PULL THE GLOBAL FOR TAXONOMY LOOKUP AND PUSH TO IT
	global $tax_lookup;
	if ( is_array( $tax_lookup ) && in_array( $meta_key, $tax_lookup ) )
		return false;

	$tax_lookup[] = $meta_key;

	// SET UP MODIFIERS FOR IDENTIFYING DIFFERENT PANELS
	$wrap_array = array();
	$icon_array = array();
	if ( 'section' == $field_type ) {
		$wrap_array[] = 'section';
		$li_id = ' li-section';
	}

	if ( isset( $user_meta['status'] ) && 'new_ele' == $user_meta['status'] )
		$wrap_array[] = 'newele';

	if ( false == $admin && false == $user )
		$wrap_array[] = 'appear-disabled';

	if ( false != $admin && false == $user )
		$icon_array[] = '<a class="icon-admin" title="' . __( 'Only Admins can see this.', 'bbconnect' ) . '">&nbsp;</a>';

	if ( false != $public )
		$icon_array[] = '<a class="icon-public" title="' . __( 'This field is publicly visible.', 'bbconnect' ) . '">&nbsp;</a>';

	if ( false != $signup )
		$icon_array[] = '<a class="icon-signup" title="' . __( 'This field appears at signup.', 'bbconnect' ) . '">&nbsp;</a>';

	$bbconnect_reserves = get_option( '_bbconnect_reserved_fields' );
	if ( false === $bbconnect_reserves )
		$bbconnect_reserves = array();

?>

	<li id="<?php echo $meta_key; ?>-ele" class="<?php echo $li_id; ?>">

		<div class="t-wrapper <?php echo implode( ' ', $wrap_array ); ?>">

			<div class="t-title">
				<span class="handle"></span>
				<span class="t-trigger" title="t-<?php echo $meta_key; ?>"><?php echo stripslashes( $name ); ?> <span class="example-text">(<?php echo $field_type; ?>)</span></span>
				<span class="right">
				<?php echo implode( ' ', $icon_array ); ?>
				<?php if ( 'user' == $user_meta['source'] ) { ?>
					<a class="delete" rel="<?php echo $meta_key; ?>" title="<?php _e( 'Delete this field', 'bbconnect' ); ?>"<?php if ( 'section' == $field_type ) echo ' data-ohno="' . __('Please remove the fields in this section first!', 'bbconnect' ) . '"'; ?>>&nbsp;</a>
					<a class="undo" rel="<?php echo $meta_key; ?>" title="<?php _e( 'Undo', 'bbconnect' ); ?>">&nbsp;</a>
				<?php } ?>
				</span>
			</div>

			<div class="t-panel" id="t-<?php echo $meta_key; ?>">
				<span class="bbconnect-label option-label bbconnect-<?php echo $field_type; ?>">
					<span class="field-header"><?php _e( 'Labels', 'bbconnect' ); ?></span><br />
					<?php /* SET THE LABEL */ ?>
					<?php if ( isset( $wp_tax ) ) { ?>
						<?php _e( 'Name', 'bbconnect' ); ?><a class="help" title="<?php _e( 'This cannot be changed.', 'bbconnect' ); ?>">&nbsp;</a><br />
						<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][name]" value="<?php echo $name; ?>" />
						<input type="text" value="<?php echo $name; ?>" disabled /><br />
					<?php } else { ?>
						<?php _e( 'Label', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Change the title of this field.', 'bbconnect' ); ?>">&nbsp;</a><br />
						<input class="new-meta-title" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][name]" value="<?php echo stripslashes( $name ); ?>" /><br />
					<?php } ?>

					<?php /* SET THE META KEY */ ?>
					<?php if ( isset( $user_meta['status'] ) && 'new_ele' == $user_meta['status'] ) { ?>
						<span class="required"><?php _e( 'Key (Required)', 'bbconnect' ); ?></span><a class="help" title="<?php _e( 'This is the unique key for this field in the database. It must be a unique name, contain only lowercase characters and no spaces -- use underscores or dashes.', 'bbconnect' ); ?>">&nbsp;</a><br /><span id="message-<?php echo $meta_key; ?>"></span><br />
						<input id="<?php echo $meta_key; ?>" type="text" value="" class="new-meta-key" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][meta_key]" title="<?php echo $meta_key; ?>" /><br />
					<?php } else { ?>
						<span class="required"><?php _e( 'Key (Required)', 'bbconnect' ); ?></span><a class="help" title="<?php _e( 'This is the unique key for this field in the database and cannot be changed.', 'bbconnect' ); ?>">&nbsp;</a><br />
						<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][meta_key]" value="<?php echo $meta_key; ?>" />
						<input type="text" value="<?php echo $meta_key; ?>" disabled /><br />
					<?php } ?>

					<?php /* SET THE TAG FOR SYNCING */ ?>
					<?php if ( isset( $no_tax ) ) { ?>
					<?php _e( 'Tag', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Uppercase key for syncing externally.', 'bbconnect' ); ?>">&nbsp;</a><br />
					<input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][tag]" value="<?php if ( isset( $user_meta['tag'] ) ) { echo $user_meta['tag']; } ?>" /><br />
					<?php } ?>

					<?php /* SET THE HELP TEXT */ ?>
					<?php _e( 'Help Text', 'bbconnect' ); ?><a class="help" title="<?php _e( 'Text you enter here will appear alongside the field through a similar icon.', 'bbconnect' ); ?>">&nbsp;</a><br />
					<input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][help]" value="<?php if ( isset( $user_meta['help'] ) ) { echo $user_meta['help']; } ?>" />

					<?php /* SET THE SOURCE */ ?>
					<?php if ( isset( $wp_tax ) ) { ?>
					<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][source]" value="wp" />
					<?php } else { ?>
					<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][source]" value="<?php echo $user_meta['source']; ?>" />
					<?php } ?>

					<?php /* POSITION THE FIELD */ ?>
					<?php if ( !isset( $user_meta['group'] ) ) { ?>
						<input type="hidden" class="column-input" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][column]" value="<?php if ( isset( $user_meta['column'] ) && !empty( $user_meta['column'] ) ) { echo $user_meta['column']; } else { echo 'column_1'; } ?>" />
						<input type="hidden" class="section-input" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][section]" value="<?php if ( isset( $user_meta['section'] ) && !empty( $user_meta['section'] ) ) { echo $user_meta['section']; } ?>" />
					<?php } ?>

					<?php /* SPECIAL EXCEPTION FOR TARGETING */ ?>
					<?php if ( isset( $user_meta['section'] ) && '' != $user_meta['section'] ) { ?>
						<input id="<?php echo $user_meta['section'] . '-' . $meta_key . '-ele'; ?>" class="section-output" type="hidden" name="bbconnect_user_meta_options[<?php echo $user_meta['section']; ?>][options][choices][]" value="<?php echo $meta_key; ?>" />
					<?php } ?>

					<?php /* TRANSIENT STATUS MARKER */ ?>
					<?php if ( isset( $user_meta['status'] ) ) { ?>
						<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key ?>][status]" value="<?php echo $user_meta['status']; ?>" />
					<?php } ?>

				</span>
				<span class="bbconnect-field">
					<ul class="field-options">
						<li>
							<span class="field-header"><?php _e( 'Options', 'bbconnect' ); ?></span><br />
							<span class="example-text"><?php _e( 'Control how and where this field will behave and appear.', 'bbconnect' ); ?></span><br />
							<a class="umt admin <?php if ( true == $user_meta['options']['admin'] ) { echo 'on'; } else { echo 'off'; } ?>" title="admin_<?php echo $meta_key; ?>"><input type="hidden" id="admin_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][admin]" value="<?php echo $user_meta['options']['admin']; ?>" /><?php _e( 'Admin', 'bbconnect' ); ?></a>
							<?php if ( 'role' == $meta_key ) { $user_umt = 'umtf'; } else { $user_umt = 'umt'; } ?>
							<a class="<?php echo $user_umt; ?> user <?php if ( true == $user_meta['options']['user'] ) { echo 'on'; } else { echo 'off'; } ?>" title="user_<?php echo $meta_key; ?>"><input type="hidden" id="user_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][user]" value="<?php echo $user_meta['options']['user']; ?>" /><?php _e( 'User', 'bbconnect' ); ?></a>
							<?php if ( 'role' == $meta_key ) { $signup_umt = 'umtf'; } else { $signup_umt = 'umt'; } ?>
							<a class="<?php echo $signup_umt; ?> signup <?php if ( isset( $user_meta['options']['signup'] ) && true == $user_meta['options']['signup'] ) { echo 'on'; } else { echo 'off'; } ?>" title="signup_<?php echo $meta_key; ?>"><input type="hidden" id="signup_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][signup]" value="<?php if ( isset( $user_meta['options']['signup'] ) && '' != $user_meta['options']['signup'] ) { echo $user_meta['options']['signup']; } ?>" /><?php _e( 'Signup', 'bbconnect' ); ?></a>
							<a class="umt reports <?php if ( true == $user_meta['options']['reports'] ) { echo 'on'; } else { echo 'off'; } ?>" title="reports_<?php echo $meta_key; ?>"><input type="hidden" id="reports_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][reports]" value="<?php echo $user_meta['options']['reports']; ?>" /><?php _e( 'Reports & Forms', 'bbconnect' ); ?></a>
							<a class="umt requ <?php if ( true == $user_meta['options']['req'] ) { echo 'on'; } else { echo 'off'; } ?>" title="required_<?php echo $meta_key; ?>"><input type="hidden" id="required_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][req]" value="<?php echo $user_meta['options']['req']; ?>" /><?php _e( 'Required', 'bbconnect' ); ?></a>
							<a class="umt public <?php if ( true == $user_meta['options']['public'] ) { echo 'on'; } else { echo 'off'; } ?>" title="public_<?php echo $meta_key; ?>"><input type="hidden" id="public_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][public]" value="<?php echo $user_meta['options']['public']; ?>" /><?php _e( 'Public', 'bbconnect' ); ?></a>
							<?php if ( 'text' == $field_type ) { ?>
							<a class="umt unique <?php if ( isset( $user_meta['options']['unique'] ) && true == $user_meta['options']['unique'] ) { echo 'on'; } else { echo 'off'; } ?>" title="unique_<?php echo $meta_key; ?>"><input type="hidden" id="unique_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][unique]" value="<?php if ( isset( $user_meta['options']['unique'] ) ) echo $user_meta['options']['unique']; ?>" /><?php _e( 'Unique', 'bbconnect' ); ?></a>
							<?php } ?>
							<?php
								// APPEND ELEMENTS FOR CONTROLLING DISPLAY OF FIELDS
								do_action( 'bbconnect_extend_meta_options', $user_meta, $taxonomy );
							?>
						</li>
						<li>
							<?php
								// LOCK THE FIELD TYPE FOR THE FOLLOWING ELEMENTS
								if ( 'group' == $field_type || 'password' == $field_type ) {
									$restricted_field = true;
									$restricted_choices = true;
								}

								if ( 'plugin' == $field_type ) {
									$restricted_field = true;
									$restricted_choices = true;
								}

								if ( 'wpr' == $user_meta['source'] ){
									$restricted_field = true;
									$restricted_choices = true;
								}

								if ( 'wp' == $user_meta['source'] ){
									$restricted_field = true;
									if ( 'checkbox' != $field_type )
										$restricted_choices = true;
								}

								if ( isset( $wp_tax ) ) {
									$restricted_field = true;
									$restricted_choices = false;
								}

								if ( isset( $user_meta['group'] ) ){
									$restricted_field = true;
									$restricted_choices = true;
								}

								if ($meta_key == 'segment_id') {
                                    $restricted_field = true;
                                    $restricted_choices = true;
                                }

                                $restricted_field = apply_filters('bbconnect_restricted_field', $restricted_field, $meta_key, $field_type);
                                $restricted_choices = apply_filters('bbconnect_restricted_choices', $restricted_choices, $meta_key, $field_type);
							?>
							<span class="field-header"><?php _e( 'Field Type', 'bbconnect' ); ?></span><br />

							<?php
								if ( isset( $restricted_field ) ) {
									$field_select = ' disabled="disabled"';
							?>
								<span class="example-text"><?php _e( 'This field is restricted and cannot be modified.', 'bbconnect' ); ?></span><br />
								<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][field_type]" value="<?php echo $field_type; ?>" />
								<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices]" value="<?php echo urlencode( maybe_serialize( $user_meta['options']['choices'] ) ); ?>" />

								<?php /* SET THE GROUP FOR GROUPED FIELDS */ ?>
								<?php if ( isset( $user_meta['group'] ) ) { ?>
								<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][group]" value="<?php echo $user_meta['group']; ?>" />
								<?php } ?>

								<?php /* SET THE GROUP TYPE FOR GROUPED FIELDS */ ?>
								<?php if ( isset( $user_meta['group_type'] ) ) { ?>
								<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][group_type]" value="<?php echo $user_meta['group_type']; ?>" />
								<?php } ?>
							<?php
								} else {
								$field_select = '';
							?>
								<span class="example-text"><?php _e( 'Please make a selection.', 'bbconnect' ); ?></span><br />

								<select class="field-select" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][field_type]" title="<?php echo $meta_key; ?>"<?php echo $field_select; ?>>
									<option value="">...</option>
									<option value="section" <?php selected( $field_type, 'section' ); ?>><?php _e( 'Section', 'bbconnect' ); ?></option>
									<option value="text" <?php selected( $field_type, 'text' ); ?>><?php _e( 'Text', 'bbconnect' ); ?></option>
									<option value="textarea" <?php selected( $field_type, 'textarea' ); ?>><?php _e( 'Text Area', 'bbconnect' ); ?></option>
									<option value="checkbox" <?php selected( $field_type, 'checkbox' ); ?>><?php _e( 'Single Checkbox', 'bbconnect' ); ?></option>
									<option value="taxonomy" <?php selected( $field_type, 'taxonomy' ); ?>><?php _e( 'Taxonomy (checkboxes)', 'bbconnect' ); ?></option>
									<option value="radio" <?php selected( $field_type, 'radio' ); ?>><?php _e( 'Radio Button', 'bbconnect' ); ?></option>
									<option value="select" <?php selected( $field_type, 'select' ); ?>><?php _e( 'Select List', 'bbconnect' ); ?></option>
									<option value="multitext" <?php selected( $field_type, 'multitext' ); ?>><?php _e( 'Multi-Text', 'bbconnect' ); ?></option>
									<option value="date" <?php selected( $field_type, 'date' ); ?>><?php _e( 'Date' ); ?></option>
									<option value="number" <?php selected( $field_type, 'number' ); ?>><?php _e( 'Number', 'bbconnect' ); ?></option>
									<?php
									/*
									<option value="plugin" <?php selected( $field_type, 'plugin' ); ?> disabled="disabled"><?php _e( 'Plugin', 'bbconnect' ); ?></option>
									<option value="group" <?php selected( $field_type, 'group' ); ?> disabled="disabled"><?php _e( 'Group', 'bbconnect' ); ?></option>
									<option value="password" <?php selected( $field_type, 'password' ); ?> disabled="disabled"><?php _e( 'Password', 'bbconnect' ); ?></option>
									*/
									do_action( 'bbconnect_field_select', $field_type );
									?>
								</select><br />
								<span class="example-text" style="width: 50px;"><?php _e( 'Data integrity may be compromised if changing the field type after you have recorded data.', 'bbconnect' ); ?></span>
							<?php } ?>
						</li>
					<?php if ( !isset( $restricted_choices ) || false == $restricted_choices ) { ?>
						<li id="<?php echo $meta_key; ?>-choices" >
							<?php bbconnect_meta_choices( $user_meta, $taxonomy ); ?>
						</li>
					<?php } ?>

						<?php
							// EXTEND CUSTOM OPTIONS FOR EACH FIELD
							do_action( 'bbconnect_extend_meta_panels', $user_meta, $taxonomy );
						?>
					</ul>
				</span>
				<?php /* NEST THE DISPLAY OF GROUP ELEMENTS WITHIN THE WRAPPER */ ?>
				<?php if ( isset( $user_meta['group_type'] ) && 'address' == $user_meta['group_type'] ) { ?>
				<ul class="group">
					<?php
					foreach ( $user_meta['options']['choices'] as $subkey => $subvalue ) {
						$user_meta = get_option( $subvalue );
						bbconnect_user_meta_list( $user_meta );
					}
					?>
				</ul>
				<?php } ?>
			</div>
		</div>

		<?php /* DISPLAY ANY MESSAGES ACQUIRED DURING PROCESSING */ ?>
		<div<?php echo $e_class; ?>>
			<?php echo $e_messages; ?>
		</div>

		<?php /* FOR SECTIONS, APPEND THE ARRANGED FIELDS */ ?>
		<?php if ( 'section' == $field_type ) { ?>
			<ul id="section_<?php echo $meta_key; ?>" class="option-sortable connected-option-sortable section-list" style="background-color: #FFF; padding: 10px 5px; border: 1px dotted #CCC;">

			<?php
				if ( isset( $user_meta['options']['choices'] ) && !empty( $user_meta['options']['choices'] ) ) {

					$add_del = array();

					foreach ( $user_meta['options']['choices'] as $subkey => $subvalue ) {
						$section_meta = bbconnect_get_option( $subvalue );
						if ( isset( $section_meta['group'] ) )
							continue;

						if ( isset( $section_meta['group_type'] ) && 'address' == $section_meta['group_type'] ) {
							if ( in_array( $section_meta['meta_key'], $add_del ) ) {
								continue;
							} else {
								$add_del[] = $section_meta['meta_key'];
							}
						}
						bbconnect_user_meta_list( $section_meta );
					}
				}
			?>
			</ul>
		<?php } ?>
	</li>
<?php
}

function bbconnect_retrieve_question_data(){
    $questions = array(
            'fun' => array(),
            'fact' => array(),
    );
    $umo = get_option('_bbconnect_user_meta');
    foreach ($umo as $uk => $uv) {
        foreach ($uv as $suk => $suv) {
            $maybe_section = get_option($suv);
            if (!empty($maybe_section['options']['question_type'])) {
                $questions[$maybe_section['options']['question_type']][] = $maybe_section;
                continue;
            }

            if (is_array($maybe_section['options']['choices'])) {
                foreach ($maybe_section['options']['choices'] as $ck => $cv) {
                    $field = get_option('bbconnect_'.$cv);
                    if (!empty($field['options']['question_type'])) {
                        $questions[$field['options']['question_type']][] = $field;
                    }
                }
            }
        }
    }
    return $questions;
}

function bbconnect_meta_choices( $user_meta, $taxonomy = null ) {

	// SPECIAL EXCEPTION FOR TAXONOMIES REGISTERED OUTSIDE OF BBCONNECT
	if ( !empty( $taxonomy ) ) {
		if ( isset( $user_meta['source'] ) && 'bbconnect' == $user_meta['source'] ) {
			$meta_key = $user_meta['meta_key'];
			$name = $user_meta['name'];
			if ( !isset( $user_meta['options']['field_type'] ) ) {
				$field_type = 'taxonomy';
			} else {
				$field_type = $user_meta['options']['field_type'];
			}
		} else {
			$meta_key = $taxonomy->name;
			$name = $taxonomy->labels->name;
			$wp_tax = true;
			$field_type = 'taxonomy';
		}
	} else {
		$meta_key = $user_meta['meta_key'];
		if ( isset( $user_meta['name'] ) )
			$name = $user_meta['name'];

		$no_tax = true;
		$field_type = $user_meta['options']['field_type'];
	}

	$question_choices = '
            <p>Use this as a get to know you question?</p>
			<label><input type="radio" name="bbconnect_user_meta_options['.$meta_key.'][options][question_type]" value="" '.checked($user_meta['options']['question_type'], '', false).'> No</label>
			<label><input type="radio" name="bbconnect_user_meta_options['.$meta_key.'][options][question_type]" value="fun" '.checked($user_meta['options']['question_type'], 'fun', false).'> Fun Question</label>
			<label><input type="radio" name="bbconnect_user_meta_options['.$meta_key.'][options][question_type]" value="fact" '.checked($user_meta['options']['question_type'], 'fact', false).'> Fact Question</label>';

	switch( $field_type ) :

		case 'taxonomy' :
			?>
			<ul>
				<?php if ( !empty( $taxonomy ) ) { ?>
				<li>
					<?php _e( 'Terms', 'bbconnect' ); ?><br />
					<span class="example-text"><?php _e( 'View, add and delete terms', 'bbconnect' ); ?></span><br />
					<a href="<?php echo admin_url(); ?>/edit-tags.php?taxonomy=<?php echo $meta_key; ?>&TB_iframe=true&height=450&width=920" class="thickbox primary button"><?php _e( 'Open editor', 'bbconnect' ); ?></a>
				</li>
				<?php } ?>

				<li>
					<?php _e( 'Heirarchical Options', 'bbconnect' ); ?><br />
					<span class="example-text"><?php _e( 'Include/exclude child terms and choose sort order' ); ?></span><br />
					<a class="umt children <?php if ( isset( $user_meta['options']['children'] ) && true == $user_meta['options']['children'] ) { echo 'on'; } else { echo 'off'; } ?>" title="children_<?php echo $meta_key; ?>"><input type="hidden" id="children_<?php echo $meta_key; ?>"  name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][children]" value="<?php if ( isset( $user_meta['options']['children'] ) ) { echo $user_meta['options']['children']; } else { echo '0'; } ?>" /><?php _e( 'Children', 'bbconnect' ); ?>
						<span>
							<select id="<?php echo $meta_key; ?>_sort" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][sort_by]">
							<?php if ( isset( $user_meta['options']['sort_by'] ) ) { $sort_by = $user_meta['options']['sort_by']; } else { $sort_by = ''; } ?>
								<option value="" <?php selected( $sort_by, '' ); ?>><?php _e( 'Sort order', 'bbconnect' ); ?></option>
								<option value="id" <?php selected( $sort_by, 'id' ); ?>><?php _e( 'Order Added', 'bbconnect' ); ?></option>
								<option value="name" <?php selected( $sort_by, 'name' ); ?>><?php _e( 'Name', 'bbconnect' ); ?></option>
							</select>
						</span>
					</a>
				</li>

				<?php if ( isset( $user_meta['source'] ) && 'wp' == $user_meta['source'] ) { } else { ?>
				<li>
					<?php _e( 'Content', 'bbconnect' ); ?><br />
					<span class="example-text"><?php _e( 'Which post types will this also be attached to?', 'bbconnect' ); ?></span><br />
					<?php
						$args = array( 'public' => true );
						$post_types = get_post_types( $args, 'names' );
						foreach ( $post_types as $post_type ) {
							if ( $post_type != 'attachment' && $post_type != 'revision' && $post_type != 'nav_menu_item' ) {
					?>
						<input type="checkbox" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][post_types]" value="<?php echo $post_type; ?>" <?php if ( isset( $user_meta['options']['post_types'] ) ) { checked( $user_meta['options']['post_types'], $post_type ); } ?>/> <?php echo $post_type; ?>
					<?php
							}
						}
					?>
				</li>
			<?php
			}
			?>
			</ul>
			<?php

			break;

		case 'checkbox' :

			if ( !isset( $user_meta['options']['choices'] ) || empty( $user_meta['options']['choices'] ) )
				$user_meta['options']['choices'] = ''; // || !is_array( $user_meta['options']['choices'] )

			if ( is_array( $user_meta['options']['choices'] ) && isset( $user_meta['options']['choices'][0] ) )
				$user_meta['options']['choices'] = $user_meta['options']['choices'][0];
			?>
				Choices<br /><span class="example-text">Select an option below.</span><br />
				<select name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices]">
					<option value="true" <?php selected( $user_meta['options']['choices'], 'true' ); ?>>true</option>
					<option value="false" <?php selected( $user_meta['options']['choices'], 'false' ); ?>>false</option>
				</select>
			<?php
			break;

		case 'select' :
		case 'radio' :
		    echo $question_choices;
		case 'multitext' :

			if ( !isset( $user_meta['options']['choices'] ) || !is_array( $user_meta['options']['choices'] ) || empty( $user_meta['options']['choices'] ) )
				$user_meta['options']['choices'] = array( '' => '' );

			$uvar = $meta_key;
			$count_{$uvar} = 0;
			$sum_{$uvar} = count( $user_meta['options']['choices'] );
			printf( __( 'Choices%1$sOptionally enter pre-selected qualities for each row.%2$s', 'bbconnect' ), '<br /><span class="example-text">', '</span><br />' );
			echo '<ul id="'.$uvar.'_choices">';
			foreach ( $user_meta['options']['choices'] as $key => $value ) {
				// LEGACY CODE FOR RADIOS
				if ( is_array( $value ) && isset( $value['value'] ) ) {
					?>
					<li id="<?php echo $uvar; ?>_choices_<?php echo $count_{$uvar}; ?>" class="multilist">
						<?php _e('key:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][key]" value="<?php echo $value['value']; ?>" style="width: 6em;" /> <?php _e('label:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][value]" value="<?php echo $value['label']; ?>" style="width: 8em;" />
						<?php if ( $count_{$uvar} == ( $sum_{$uvar} - 1 ) ) { ?>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-add" class="button add" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
						<?php } elseif ( $count_{$uvar} == 0 ) { ?>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>" style="display:none;">&nbsp;</a>
						<?php } else { ?>
							<a class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
						<?php } ?>
					</li>
					<?php
					$count_{$uvar}++;
				} else if ( is_array( $value ) && !isset( $value['value'] ) ) {
					foreach ( $value as $skey => $svalue ) {
					?>
					<li id="<?php echo $uvar; ?>_choices_<?php echo $count_{$uvar}; ?>" class="multilist">
						<?php _e('key:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][key]" value="<?php echo $skey; ?>" style="width: 6em;" /> <?php _e('label:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][value]" value="<?php echo $svalue; ?>" style="width: 8em;" />
						<?php if ( $count_{$uvar} == ( $sum_{$uvar} - 1 ) ) { ?>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-add" class="button add" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
						<?php } elseif ( $count_{$uvar} == 0 ) { ?>
							<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>" style="display:none;">&nbsp;</a>
						<?php } else { ?>
							<a class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
						<?php } ?>
					</li>
					<?php
					$count_{$uvar}++;
					}
				} else {
				?>
				<li id="<?php echo $uvar; ?>_choices_<?php echo $count_{$uvar}; ?>" class="multilist">
					<?php _e('key:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][key]" value="<?php echo $key; ?>" style="width: 6em;" /> <?php _e('label:','bbconnect'); ?> <input class="regular-text" type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices][<?php echo $count_{$uvar}; ?>][value]" value="<?php echo $value; ?>" style="width: 8em;" />
					<?php if ( $count_{$uvar} == ( $sum_{$uvar} - 1 ) ) { ?>
						<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
						<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-add" class="button add" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
					<?php } elseif ( $count_{$uvar} == 0 ) { ?>
						<a id="<?php echo $key; ?>-<?php echo $count_{$uvar}; ?>-sub" class="button sub" title="<?php echo $count_{$uvar}; ?>" style="display:none;">&nbsp;</a>
					<?php } else { ?>
						<a class="button sub" title="<?php echo $count_{$uvar}; ?>">&nbsp;</a>
					<?php } ?>
				</li>
				<?php
				$count_{$uvar}++;
				}

			}
			echo '</ul>';

			break;

		case 'section' :
			printf( __( '%1$s Drag other fields into the area below to group them %2$s', 'bbconnect' ), '<div>', '</div>' );
			break;

		case 'plugin' :
			printf( __( '%1$s Supply the name of the function you wish to call %2$s', 'bbconnect' ), '<div>', '</div>' );
			?>
			<input type="text" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices]" value="<?php echo urlencode( maybe_serialize( $user_meta['options']['choices'] ) ); ?>" />
			<?php
			break;

        case 'number':
?>
            <input type="checkbox" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][is_currency]" value="1" <?php if (isset($user_meta['options']['is_currency'])) { checked($user_meta['options']['is_currency'], 1); } ?>> Format as Currency?
<?php
            echo $question_choices;
            break;

		case '' :
			if ( false == $user_meta['meta_key'] || empty( $user_meta['meta_key'] ) ) {
				printf( __( '%1$s Please choose a key first before selecting a field type or dragging into place. %2$s', 'bbconnect' ), '<div class="required">', '</div>' );
			} else {
				 _e( 'Please make a selection', 'bbconnect' );
			}
			break;

		case 'text' :
		case 'textarea' :
	    case 'date' :
		    echo $question_choices;
		    break;
		case 'group' :
		case 'password' :
		default :
			printf( __( '%1$s There are no choices for this field type %2$s', 'bbconnect' ), '<div>', '</div>' );
			if ( isset( $user_meta['options']['choices'] ) ) {
				$pre_choices = urlencode( maybe_serialize( $user_meta['options']['choices'] ) );
			} else {
				$pre_choices = '';
			}
			?>
			<input type="hidden" name="bbconnect_user_meta_options[<?php echo $meta_key; ?>][options][choices]" value="<?php echo $pre_choices; ?>" />
			<?php
			break;

	endswitch;
}


function bbconnect_new_elements_forms() {

	if (!wp_verify_nonce($_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce')) {
		die(__('Failed security check. Usually this means you left the page sitting open for too long without doing anything. Please refresh the page and try again.', 'bbconnect'));
    }

	if ( isset( $_POST['data'] ) ) {
		$args = array(
						'meta_key' => $_POST['data'],
						'source' => 'user',
					);

		bbconnect_new_elements( $args );

	} else {
		echo 'error';
	}

	die();
}

function bbconnect_new_elements( $args = '' ) {
	$defaults = array(
					'nonce' => false,
					'output' => 'echo',
					'source' => 'bbconnect',
					'meta_key' => false,
					'tag' => '',
					'name' => '',
					'column' => '',
					'section' => '',
					'admin' => true,
					'user' => false,
					'signup' => false,
					'reports' => false,
					'public' => false,
					'req' => false,
					'field_type' => '',
					'choices' => false,
					'post_types' => false,
					'help' => '',
					'status' => 'new_ele'
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	$new_user_meta = array( 'source' => $source, 'meta_key' => $meta_key, 'tag' => $tag, 'name' => $name, 'column' => $column, 'section' => $section, 'options' => array( 'admin' => $admin, 'user' => $user, 'signup' => $signup, 'reports' => $reports, 'public' => $public, 'req' => $req, 'field_type' => $field_type, 'choices' => $choices, 'post_types' => $post_types ), 'help' => $help, 'status' => $status );

	if ( 'return' == $output ) {
		return $new_user_meta;
	} else {
		bbconnect_user_meta_list( $new_user_meta );
	}

}


function bbconnect_element_choices_forms() {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	if ( isset( $_POST['meta_key'] ) ) {
		$args = array(
						'nonce' => $_POST['bbconnect_element_choices_nonce'],
						'meta_key' => $_POST['meta_key'],
						'field_type' => $_POST['field_type']
					);

		bbconnect_element_choices( $args );

	} else {
		echo 'error';
	}

	die();

}

function bbconnect_element_choices( $args = '' ) {

	$defaults = array(
					'nonce' => false,
					'meta_key' => false,
					'field_type' => false
				);


	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	// GET THE OPTION
	$user_meta = bbconnect_get_option( $meta_key );
	if ( !isset( $user_meta['meta_key'] ) ) {
		if ( strlen( $meta_key ) <= 2 ) {
			_e( 'Please choose a key for this field first!', 'bbconnect' );
			die();
		} else {
			$user_meta['meta_key'] = $meta_key;
		}
	}

	// MODIFY THE ARRAY FOR A NEW TYPE
	$user_meta['options']['field_type'] = $field_type;

	// RETURN THE RESULTS
	bbconnect_meta_choices( $user_meta );

	//die();
}

/* -----------------------------------------------------------
	USER TAXONOMIES AND GROUPINGS
   ----------------------------------------------------------- */

// CUSTOM TAXONOMIES
function bbconnect_wp_taxonomies() {

	// SET THE LOCAL VARIABLES
	$bbconnect_wp_taxonomies = get_option( '_bbconnect_wp_taxonomies' );
	if ( false === $bbconnect_wp_taxonomies )
		$bbconnect_wp_taxonomies = array();

	foreach ( $bbconnect_wp_taxonomies as $taxonomy ) {

		$ctax = $taxonomy['tax'];
		$stax = $taxonomy['single'];
		$ptax = $taxonomy['single'] . '(s)';
		//$ptax = $taxonomy['plural'];
		$ltax = $taxonomy['location'];

		$labels = array();
	    $labels['name'] = $ptax;
	    $labels['singular_name'] = $stax;
	    $labels['search_items'] = 'Search '.$ptax;
	    $labels['all_items'] = 'All '.$ptax;
	    $labels['parent_item'] = 'Parent '.$stax;
	    $labels['parent_item_colon'] = 'Parent '.$stax.':';
	    $labels['edit_item'] = 'Edit '.$stax;
	    $labels['update_item'] = 'Update '.$stax;
	    $labels['add_new_item'] = 'Add New '.$stax;
	    $labels['new_item_name'] = 'New '.$stax.' Name';

		$args = array();
	    $args['hierarchical'] = true;
	    $args['labels'] = $labels;
	    $args['show_ui'] = true;
	    $args['query_var'] = true;
	    $args['rewrite'] = array( 'slug' => $ctax );

		register_taxonomy( $ctax, $ltax, $args );

	}
}


function bbconnect_create_wp_taxonomy( $tax_info ) {

	// SET THE LOCAL VARIABLES
	$bbconnect_wp_taxonomies = get_option( '_bbconnect_wp_taxonomies' );
	if ( false == $bbconnect_wp_taxonomies )
		$bbconnect_wp_taxonomies = array();

	$key_key = $tax_info['tax'];

	// CHECK FOR RESERVED TERMS
	if ( in_array( $key_key, bbconnect_get_reserved_terms() ) )
		return false;

	// SET THE KEY TO UPDATE OR ADD
	$bbconnect_wp_taxonomies[$key_key] = $tax_info;

	// DETACH THE TERMS IF ANY
	// $tax_terms = array_pop( $tax_info );

	// APPEND THE NEW TAXONOMY AND SAVE IT
	update_option( '_bbconnect_wp_taxonomies', $bbconnect_wp_taxonomies );

	/* AND INSERT THE TERMS
	if ( !empty( $tax_terms ) ) {
		foreach ( $tax_terms as $term ) {
			wp_insert_term( $term, $tax_info['tax'] );
		}
	}*/

	return $key_key;

}

function bbconnect_delete_wp_taxonomy( $meta_key ) {

	// SET THE LOCAL VARIABLES
	$bbconnect_wp_taxonomies = get_option( '_bbconnect_wp_taxonomies' );

	// REMOVE THE ELEMENT
	unset( $bbconnect_wp_taxonomies[$meta_key] );
	update_option( '_bbconnect_wp_taxonomies', $bbconnect_wp_taxonomies );

}

function bbconnect_get_reserved_terms() {
	return apply_filters( 'bbconnect_reserved_terms',
			array(
					'attachment',
					'attachment_id',
					'author',
					'author_name',
					'calendar',
					'cat',
					'category',
					'category__and',
					'category__in',
					'category__not_in',
					'category_name',
					'comments_per_page',
					'comments_popup',
					'cpage',
					'day',
					'debug',
					'error',
					'exact',
					'feed',
					'hour',
					'link_category',
					'm',
					'minute',
					'monthnum',
					'more',
					'name',
					'nav_menu',
					'nopaging',
					'offset',
					'order',
					'orderby',
					'p',
					'page',
					'page_id',
					'paged',
					'pagename',
					'pb',
					'perm',
					'post',
					'post__in',
					'post__not_in',
					'post_format',
					'post_mime_type',
					'post_status',
					'post_tag',
					'post_type',
					'posts',
					'posts_per_archive_page',
					'posts_per_page',
					'preview',
					'robots',
					's',
					'search',
					'second',
					'sentence',
					'showposts',
					'static',
					'subpost',
					'subpost_id',
					'tag',
					'tag__and',
					'tag__in',
					'tag__not_in',
					'tag_id',
					'tag_slug__and',
					'tag_slug__in',
					'taxonomy',
					'tb',
					'term',
					'type',
					'w',
					'withcomments',
					'withoutcomments',
					'year'
		)
	);
}

?>
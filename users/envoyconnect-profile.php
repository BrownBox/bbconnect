<?php
/**
 * Creates a new user. If a user is successfully created, returns false and runs the edit user function.
 *
 * @since 1.0.0
 * @param none
 * @return html
 */

function envoyconnect_new_user() {

	// STOP THEM IF THEY SHOULDN'T BE HERE
	if ( !current_user_can( 'add_users' ) ) {
	    	wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// SET GLOBAL VARIABLES
	global $current_user, $envoyconnect_cap;
	if ( current_user_can( 'list_users' ) ) {
		$envoyconnect_cap = 'admin';
	} else {
		$envoyconnect_cap = 'user';
	}
	define( 'IS_PROFILE_PAGE', false );

	// SET THE USER INFORMATION
	get_currentuserinfo();

	// PROCESS THE FORM
	if ( isset( $_POST['new_user'] ) ) {

		// SECURITY CHECK
		check_admin_referer( 'envoyconnect-nonce' );

		$envoyconnect_success = envoyconnect_insert_user( array( 'ivals' => $_POST ) );

		if ( !is_wp_error( $envoyconnect_success ) ) {
			?>
				<div id="message" class="updated">
					<p><strong><?php _e( 'User created!', 'envoyconnect' ) ?></strong></p>
				</div>
			<?php
			envoyconnect_edit_user( $envoyconnect_success );
			return false;

		} else {
		?>
			<div id="message" class="error">
				<p><strong><?php echo $envoyconnect_success->get_error_message(); ?></strong></p>
			</div>
		<?php
		}

	}

?>

		<div id="envoyconnect" class="wrap">
		<div id="icon-users" class="icon32"><br /></div>
			<h2><?php _e( 'Add New User', 'envoyconnect' ); ?></h2>
			<form id="user-form" enctype="multipart/form-data" action="<?php echo admin_url( 'users.php?page=envoyconnect_new_user' ); ?>" autocomplete="off" method="POST">

			<?php wp_nonce_field('envoyconnect-nonce'); ?>

			<div>
				<?php envoyconnect_profile_user_meta( array( 'envoyconnect_cap' => $envoyconnect_cap, 'action' => 'edit' ) ); ?>
			</div>
			<div class="tright">
				<input id="profile-submission" type="submit" name="new_user" value="<?php _e( 'Add Them!', 'envoyconnect' ); ?>" class="button-primary" />
			</div>

			</form>
		</div>
<?php
}


function envoyconnect_update_user() {

	// PROCESS THE UPDATE IF APPLICABLE
	if ( !empty( $_POST['update'] ) && isset( $_POST['edit_user_profile'] ) ) {

		// SECURITY CHECK
		check_admin_referer( 'envoyconnect-edit-user-nonce' );

		$user_id = $_POST['user_id'];
		global $errors, $updated, $current_user;

		//if ( !current_user_can('edit_user', $user_id) )
		if ( !envoyconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) ) )
			wp_die(__('You do not have permission to edit this user.'));

		/* PRESERVE THE WORDPRESS HOOK TO UPDATE USER META
		if ( IS_PROFILE_PAGE )
			do_action('personal_options_update', $user_id);
		else
			do_action('edit_user_profile_update', $user_id);
		*/
		if ( 'meta' == $_POST['update'] ) {
			envoyconnect_update_user_metadata( array( 'user_id' => $user_id, 'uvals' => $_POST, 'source' => 'back' ) );

			// IF THEY'RE EMAIL IS NULLIFIED, ZERO IT
			if ( empty( $_POST['email'] ) ) {
				$temp_user = get_user_by( 'id', $user_id );
				$_POST['email'] = $temp_user->user_login . '@noreply.invalid';
			}

			// UPDATE THE WORDPRESS PROFILE DEFAULTS
			$errors = edit_user($user_id);

			if ( !is_wp_error( $errors ) )
				$updated = __( 'Profile Updated.', 'envoyconnect' );
			/*
			$current_user = wp_get_current_user();
			if (
				!is_wp_error( $errors ) &&
				$current_user->ID == $user_id &&
				isset( $_POST['pass1'] ) &&
				!empty( $_POST['pass1'] ) &&
				isset( $_POST['pass2'] ) &&
				!empty( $_POST['pass2'] ) &&
				$_POST['pass1'] === $_POST['pass2']
			) {
				wp_redirect( 'wp-login.php' );
				exit;
			}
			*/

		} else if ( 'actions' == $_POST['update'] ) {
			$updated = __( 'History updated.', 'envoyconnect' );
		}

	}

}


/**
 * The Admin-facing user edit screen
 *
 * @since 1.0.0
 * @param $user_id int. The user ID
 * @return html
 */

function envoyconnect_edit_user( $user_id = '' ) {

	if ( '' ==  $user_id && isset( $_POST['uuid'] ) )
		$user_id = $_POST['uuid'];

	if ( '' != $user_id )
		$_GET['user_id'] = $user_id;

	// WORDPRESS SETUP
	wp_reset_vars(array('action', 'redirect', 'profile', 'user_id', 'wp_http_referer'));

	if ( empty( $_GET['user_id'] ) )
		define('IS_PROFILE_PAGE', true );

	// SET THE USER INFORMATION
	global $user_id;
	$user_id = (int) $user_id;
	$current_user = wp_get_current_user();
	if ( ! defined( 'IS_PROFILE_PAGE' ) )
		define( 'IS_PROFILE_PAGE', ( $user_id == $current_user->ID ) );

	if ( ! $user_id && IS_PROFILE_PAGE )
		$user_id = $current_user->ID;
	elseif ( ! $user_id && ! IS_PROFILE_PAGE )
		wp_die(__( 'Invalid user ID.' ) );
	elseif ( ! get_userdata( $user_id ) )
		wp_die( __('Invalid user ID.') );

	//if ( !current_user_can('edit_user', $user_id) )
	if ( !envoyconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) ) )
		wp_die(__('You do not have permission to edit this user.'));

	$profileuser = get_user_to_edit( $user_id );

	global $errors, $updated;
	if ( isset( $updated ) ) {
?>
	<div id="message" class="updated">
		<p><strong><?php echo $updated; ?></strong></p>
	</div>
<?php
	}

	if ( isset( $errors ) && is_wp_error( $errors ) ) {
?>
	<div class="error"><p><?php echo implode( "</p>\n<p>", $errors->get_error_messages() ); ?></p></div>
<?php
	}

	// SET PAUPRESS POSITIONS
	// SET GLOBAL VARIABLES
	global $current_user, $envoyconnect_cap;
	if ( current_user_can( 'list_users' ) ) {
		$envoyconnect_cap = 'admin';
		$formdes = admin_url( 'users.php?page=envoyconnect_edit_user&user_id=' . $user_id );
	} else {
		$envoyconnect_cap = 'user';
		$formdes = admin_url( 'admin.php?page=envoyconnect_edit_user_profile&user_id=' . $user_id );
	}

	$tabs = apply_filters( 'envoyconnect_user_tabs', array(

		'meta' => array(
									'title' => __( 'Profile', 'envoyconnect' ),
									'subs' => false,
							),

		'actions' => array(
									'title' => __( 'History', 'envoyconnect' ),
									'subs' => false,
							),

	) );
	if ( isset( $_GET['tab'] ) ) {
		$active = $_GET['tab'];
	} else {
		$active = current( array_keys( $tabs ) );
	}
	$tab_nav = '';
	foreach ( $tabs as $key => $val ) {
		if ( $active == $key ) { $act_tab = ' nav-tab-active'; } else { $act_tab = ''; }
		$tab_nav .= '<a href="' . $formdes . '&tab=' . $key . '" class="nav-tab' . $act_tab . '">' . $val['title'] . '</a>';
	}

	do_action( 'envoyconnect_pre_admin_profile' );

?>

	<div id="envoyconnect" class="wrap">
	<div id="icon-users" class="icon32"><br /></div>
		<h2><?php echo envoyconnect_get_username( $user_id ); ?></h2>
		<h2 class="nav-tab-wrapper"><?php echo $tab_nav; ?></h2>
		<form id="user-form" class="envoyconnect-form" enctype="multipart/form-data" action="<?php echo $formdes . '&tab=' . $active; ?>" autocomplete="off" method="POST">

		<?php wp_nonce_field( 'envoyconnect-edit-user-nonce' ); ?>
		<div>
		<?php
			switch( $active ) {

				case 'meta' :
					do_action( 'envoyconnect_pre_admin_profile_fields' );
					envoyconnect_profile_user_meta( array( 'user_id' => $user_id, 'envoyconnect_cap' => $envoyconnect_cap, 'action' => 'edit' ) );

					/* THIS IS HERE FOR TEMPORARY HISTORICAL REFERENCES
						if ( IS_PROFILE_PAGE )
							do_action( 'show_user_profile', $profileuser );
						else
							do_action( 'edit_user_profile', $profileuser );
					*/
					?>
						<input type="hidden" name="update" value="<?php echo $active; ?>" />
						<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
						<div class="tright">
							<input id="profile-submission" type="submit" name="edit_user_profile" value="<?php _e( 'Update!', 'envoyconnect' ); ?>" class="button-primary" />
						</div>
						<?php if ( !current_user_can( 'edit_users' ) ) : ?>
						<script type="text/javascript">
							jQuery(document).ready(function(){
								jQuery('#envoyconnect').on('click', '#profile-submission', check_profile);
							});
						</script>
						<?php endif; ?>
					<?php

					break;

				case 'actions' :
					envoyconnect_actions_editor( array( 'user_id' => $user_id, 'envoyconnect_cap' => $envoyconnect_cap, 'action' => 'edit' ) );
					break;
			}
		?>
		</div>

		</form>
	</div>
<?php
}


/**
 * Displays the profile fields for both the public and private views.
 *
 * @since 1.0.0
 *
 * @param arr $user_id optional. User id integer -- if not present, the function won't
 * attempt to fill in the data.
 *
 * @return html formatted profile fields.
 */
function envoyconnect_profile_user_meta( $args = '' ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'user_id' => '',
					'envoyconnect_cap' => '',
					'group_override' => false,
					'action' => false,
					'envoyconnect_user_meta' => get_option( '_envoyconnect_user_meta' ),
					'post_arr' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	// EXAMINE THE DISPLAY GRID
	if ( isset( $envoyconnect_user_meta['column_2'] ) && !empty( $envoyconnect_user_meta['column_2'] ) ) {
		$column_two = true;
		$column_one = '';
	} else {
		$column_one = ' style="width: 99%;"';
	}

	// IF WE HAVE POST VARS, FLATTEN THE ARRAY FOR PROCESSING
	if ( false != $post_arr ) {
		foreach ( $post_arr as $key => $val ) {
			if ( 'envoyconnect_user_meta' == $key ) {
				foreach ( $val as $pkey => $pval )
					$post_vars[$pkey] = $pval;
			} else {
				$post_vars[$key] = $val;
			}
		}
	}

	?>
	<div class="envoyconnect-fields">
		<div id="column_1_holder"<?php echo $column_one; ?>>
			<ul id="column_1">
			<?php
				foreach ( $envoyconnect_user_meta['column_1'] as $key => $value ) {

					// GET THE OPTION
					$user_meta = envoyconnect_get_option( $value );

					$args = array(
									'meta' => $user_meta,
									'envoyconnect_cap' => $envoyconnect_cap,
									'group_override' => $group_override,
									'action' => $action
								);
					$hide_meta = envoyconnect_hide_meta( $args );
					if ( $hide_meta )
						continue;

					$post_val = false;
					if ( isset( $post_vars ) ) {
						// FOR SECTIONS & GROUPS
						if ( in_array( $user_meta['options']['field_type'], array( 'group', 'section' ) ) ) {
							$post_val = $post_vars;
						} else if ( isset( $post_vars[$user_meta['meta_key']] ) ) {
							$post_val = $post_vars[$user_meta['meta_key']];
						}
					}

					envoyconnect_get_field( array( 'meta' => $user_meta, 'id' => $user_id, 'envoyconnect_cap' => $envoyconnect_cap, 'action' => $action, 'type' => 'user', 'post_val' => $post_val ) );
				}
			?>
			</ul>
		</div>

		<?php if ( isset( $column_two ) ) { ?>

		<div id="column_2_holder">
			<ul id="column_2">
			<?php
				foreach ( $envoyconnect_user_meta['column_2'] as $key => $value ) {

					// GET THE OPTION
					$user_meta = envoyconnect_get_option( $value );

					$args = array(
									'meta' => $user_meta,
									'envoyconnect_cap' => $envoyconnect_cap,
									'group_override' => $group_override,
									'action' => $action
								);
					$hide_meta = envoyconnect_hide_meta( $args );
					if ( $hide_meta )
						continue;

					$post_val = false;
					if ( isset( $post_vars ) ) {
						// FOR SECTIONS & GROUPS
						if ( in_array( $user_meta['options']['field_type'], array( 'group', 'section' ) ) ) {
							$post_val = $post_vars;
						} else if ( isset( $post_vars[$user_meta['meta_key']] ) ) {
							$post_val = $post_vars[$user_meta['meta_key']];
						}
					}

					envoyconnect_get_field( array( 'meta' => $user_meta, 'id' => $user_id, 'envoyconnect_cap' => $envoyconnect_cap, 'action' => $action, 'type' => 'user', 'post_val' => $post_val ) );
				}
			?>
			</ul>
		</div>

		<?php } ?>

	</div>
		<?php

}


function envoyconnect_actions_editor( $args = null ) {

	global $pagenow, $current_user;

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'user_id' => false,
					'envoyconnect_cap' => false,
					'action' => false,
					'list_only' => false,
					'action_only' => false,
					'embed' => false,
					'embed_id' => false,
					'cid' => 'post_content',
					'get_actions' => 'envoyconnect_get_user_actions',
					'type' => 'user',
					'ok_edit' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( false == $envoyconnect_cap || false == $action )
		return false;

	if ( false != $embed && false == $embed_id )
		return false;

	//if ( isset( $_GET['user_id'] ) ) && $user_id == false ) {

	if ( $action_only ) {
		if ( is_int( $action_only ) ) {
			$one_style = ' style="width: '.(96 - $action_only).'%;"';
			$two_style = ' style="width: '.$action_only.'%;"';
		} else {
			$one_style = ' style="display: none;"';
			$two_style = ' style="width: 96%;"';
		}
	} else if ( $list_only ) {
		if ( is_int( $list_only ) ) {
			$two_style = ' style="width: '.(96 - $list_only).'%;"';
			$one_style = ' style="width: '.$list_only.'%;"';
		} else {
			$two_style = ' style="display: none;"';
			$one_style = ' style="width: 96%;"';
		}
	} else {
		$two_style = '';
		$one_style = '';
	}

	// CHECK TO SEE IF THE CURRENT USER CAN EDIT THE TARGET USER
	if ( false == $ok_edit )
		$ok_edit = envoyconnect_user_can( 'edit_user', array( 'one' => $current_user->ID, 'two' => $user_id ) );

	$envoyconnect_actions = call_user_func( $get_actions );

	if ( !empty( $envoyconnect_actions ) ) {

		// PULL ALL POSSIBLE ACTIONS AND COMPILE THE LAUNCHER
		$envoyconnect_action_types = array();
		$buttons = '';
		$envoyconnect_action_launcher = array();

		foreach ( $envoyconnect_actions as $key => $value ) {

			if ( 'user' == $envoyconnect_cap && false == $value['options']['user']  )
				continue;

			if ( 'admin' == $envoyconnect_cap && false == $value['options']['admin']  )
				continue;

			/*
			$value = apply_filters( 'envoyconnect_prempt_actions', $value, $user_bypass );
			print_r($value);
			if ( !isset( $value['options'][$envoyconnect_cap] ) || false == $value['options'][$envoyconnect_cap] )
				continue;
			*/
			// HIDES PRIVATE POST TYPES
			//if ( isset( $value['options']['public'] ) && false == $value['options']['public'] )
				//continue;

			// ONLY LET ADMINS SEE THE PAUPRESS DEFAULT ACTIONS
			/* BUT LET PLUGINS MODIFY THE VIEWS
			if ( 'admin' == $envoyconnect_cap && false == $value['options']['admin']  ) {
				continue;

			} else {
				if ( false == $value['options']['user']  )
					continue;

			}
			*/

			if ( 'edit' == $action ) {

				// ONLY LET ADMINS SEE THE PAUPRESS DEFAULT ACTIONS
				// BUT LET PLUGINS MODIFY THE VIEWS
				do_action( $get_actions.'_edit', $value );

			} else if ( 'view' == $action ) {

				// IF THIS ISN'T PUBLIC, STILL LET THE OWNER SEE IT
				if ( false == $value['options']['public'] && false == $ok_edit )
					continue;

			}
			/* CHECK TO SEE IF WE HAVE OPTIONS TO CONSIDER THEN PRINT OUT THE OPTIONS
			if ( isset( $value['options']['choices'] ) && false != $value['options']['choices'] ) {
				foreach( $value['options']['choices'] as $ckey => $cval ) {
					$a_launch .= '<option value="' . $ckey . '">' . $cval . '</option>';
				}
			} else {
			*/
				$envoyconnect_action_launcher[$value['type']] = $value['single'];
			/*
			}
			*/

			// PULL THE ARRAY FOR QUERYING
			$envoyconnect_action_types[] = $value['type'];

			/* CONCATENATE THE BUTTONS
			$buttons .= '<span id="button-'.$value['type'].'" class="a-button"><a href="'.get_bloginfo("wpurl").'/wp-admin/post-new.php?post_type='.$value['type'].'&uid='.$user_id.'&TB_iframe=true&height=450&width=920&modal=true" class="thickbox button-primary">New '.$value['plural'].'</a><a class="button inline-post-create" rel="'.$value['type'].'" title="editorcontentid">inline</a></span>';
			*/
		}

		?>
		<div class="envoyconnect-fields">

		<?php if ( !empty( $envoyconnect_action_types ) ) { ?>

		<?php
			if ( false != $embed )
				echo '<form id="'.$embed_id.'" class="envoyconnectpanels-form" method="post" action="">';
		?>

			<div class="column_holder actions-history-holder"<?php echo $one_style; ?>>
				<ul>
					<li>
						<span class="envoyconnect-label"><h3><?php _e( 'Date', 'envoyconnect' ); ?></h3></span>
						<span class="envoyconnect-field">
							<h3><?php _e( 'Title &amp; Excerpt', 'envoyconnect' ); ?></h3>
							<div style="text-align: right;">
								<select class="profile-actions-filter">
									<option value="all"><?php _e( 'All', 'envoyconnect' ); ?></option>
									<?php
										$envoyconnect_ae_launcher_left = apply_filters( 'envoyconnect_ae_launcher_left', $envoyconnect_action_launcher, $embed_id );
										foreach ( $envoyconnect_ae_launcher_left as $key => $val )
											echo '<option value="' . $key . '">' . $val . '</option>';
									?>
								</select>
							</div>
						</span>
					</li>
				</ul>
				<ul class="envoyconnect actions-history-list">
				<?php
					if ( false != $user_id ) {

						// MAKE THE QUERY
						$action_query = new WP_Query( array( 'post_type' => $envoyconnect_action_types, 'author' => $user_id, 'posts_per_page' => -1, 'post_status' => array( 'publish', 'private' ) ) );

						// LOOP THE ITEMS
						while ( $action_query->have_posts() ) : $action_query->the_post();
							envoyconnect_profile_action_item( array(
																'act' => $action_query->post,
																'type' => $type,
																'user' => $user_id,
																'ok_edit' => $ok_edit,
																'action' => $action,
																'envoyconnect_cap' => $envoyconnect_cap
							) );
						endwhile;
					} else {
						do_action( 'envoyconnect_ae_query', $envoyconnect_action_types, $embed_id );
					}
				?>
				</ul>
			</div>

			<div class="column_holder actions-editor"<?php echo $two_style; ?>>
				<ul>
					<li>
						<span class="envoyconnect-label"><h3>&nbsp;</h3></span>
						<span class="envoyconnect-field">
							<h3>&nbsp;</h3>
						<?php
							// IF THE USER CAN EDIT THE TARGET USER, START THE UI
							if ( false != $ok_edit || 'bulk-edit' == $action ) {

								if ( ! envoyconnect_is_panels() && 'user' == $envoyconnect_cap ) {
									// IF THEY ARE A USER ON THE BACKEND, SHOW THEM NOTHING
								} else {
									$envoyconnect_ae_launcher_right = apply_filters( 'envoyconnect_ae_launcher_right', $envoyconnect_action_launcher, $embed_id, $action, $envoyconnect_cap );

						?>
							<div style="text-align: right;">
								<?php
									if ( 'bulk-edit' == $action ) {
										echo '<a class="rui off" title="actions-bulk-edit">Select a type to edit: </a>';
										$e_title = __( 'Edit Actions', 'envoyconnect' );
									} else {
										$e_title = __( 'Create or Edit Actions', 'envoyconnect' );
									}
								?>
								<select class="actions-launcher <?php echo $action; ?> <?php echo $type; ?>" title="<?php echo $user_id; ?>">
									<option value=""><?php echo $e_title; ?></option>
									<?php
										foreach ( $envoyconnect_ae_launcher_right as $key => $val ) {
											if ( $pagenow == 'admin-ajax.php' ) {
												echo '<option value="rel=pauContent&amp;pau_type=' . $key . '&amp;uid=' . $user_id. '">' . $val . '</option>';
											} else {
												echo '<option value="' . $key . '">' . $val . '</option>';
											}
										}
									?>
								</select>
							</div>
						<?php
								}
							}
						?>
						</span>
					</li>
				</ul>
				<ul>
					<li>
						<div style="display: block;">

							<div id="<?php echo $cid; ?>_viewer" class="envoyconnect-viewer inside">
								<div style="display:none;">
								<?php
									if ( $pagenow != 'admin-ajax.php' ) {
										if ( '3.9' > get_bloginfo( 'version' ) ) {
											wp_editor( '', $cid, array( 'tinymce' => true, 'textarea_name' => 'post_content', 'teeny' => false, 'quicktags' => true ) );
										}
									}
								?>
								</div>
							</div>

						</div>
					</li>
				</ul>
			</div>

		<?php
			if ( false != $embed )
				echo '</form>';
		?>
			<?php /* if ( $pagenow != 'admin-ajax.php' ) { ?>
			<div id="launcher" style="clear: both;">

				<span id="a-buttons">
					<?php echo $buttons; ?>
				</span>
			</div>
			<script type="text/javascript">
				jQuery(document).ready(function() {
					jQuery('#TB_closeWindowButton').click(function() {
						jQuery(self.parent.tb_remove());
						alert('ohai');
					});
				});
			</script>
			<?php } */ ?>
		<?php } ?>
		</div>
		<?php
	}
}

/**
 * function description
 *
 * @author your name
 * @param $act obj. Post object.
 * @param $type str. A string. Either 'user' or 'post' as a reference for where this editor is active.
 * @param $new bool. Whether or not this is a new object.
 * @return return type
 */
function envoyconnect_profile_action_item( $args = null ) {

	global $pagenow, $current_user;

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'act' => false,
					'type' => 'user',
					'new' => false,
					'class' => array(),
					'ok_edit' => false,
					'action' => 'view',
					'envoyconnect_cap' => 'user',
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( !is_object( $act ) )
		return false;

	$class = implode( ' ', $class );
	?>
	<?php
		if ( $new ) {
			echo '<li style="display:none;" class="new-post ' . $act->post_type . ' tn-wrapper ' .$class. '">';
		} else {
			echo '<li class="' . $act->post_type . ' tn-wrapper ' .$class. '">';
		}
	?>
		<div class="envoyconnect-view" rel="<?php echo $act->ID; ?>">
		<span class="envoyconnect-label"><?php echo date( 'Y-m-d', strtotime( $act->post_date ) ); ?></span>
		<span class="envoyconnect-field">
			<a class="<?php echo implode( ' ', apply_filters( 'envoyconnect_ai_class_filter', array( 'envoyconnect-icon', $act->post_type ), $act ) ); ?>"><?php echo $act->post_title; ?></a>
		</span>
		</div>
		<?php
			if ( $new ) {
				echo '<div title="post-' . $act->ID . '" style="display:none;">';
			} else {
				echo '<div id="post-' . $act->ID . '" style="display:none;">';
			}

			if ( defined( 'DOING_AJAX') && DOING_AJAX ) {
				if ( 'pp_log' == $act->post_type || 'pp_interaction' == $act->post_type ) {
					$title = '<h2>'.$act->post_title.'</h2>';
				} else {
					$title = '<h2><a href="'.get_permalink( $act->ID ).'">'.$act->post_title.'</a></h2>';
				}
			} else {
				$title = '<h2>'.$act->post_title.'</h2>';
			}
			$date = '<div class="action-date">'.date( 'F j, Y', strtotime( $act->post_date ) ).'</div>';
			$content = apply_filters( 'the_content', stripslashes( $act->post_content ) );

			$title = apply_filters( 'envoyconnect_title_filter', $title, $act );
			$date = apply_filters( 'envoyconnect_date_filter', $date, $act );
			$content = apply_filters( 'envoyconnect_content_filter', $content, $act );
				// PLUGINS CAN UNSET THE CONTENT, OVERRIDING THE DEFAULT EXCERPT DISPLAY.

				// IF THE FILTER WAS APPLIED, THEN THE DISPLAY IS OPEN TO FULLY DISPLAYING
				// A COMPLETE POST OBJECT IN ALL IT'S GLORY! NICE IF YOU HAVE PARTICULAR
				/* POST FORMATS IN MIND FOR DISPLAY.
				if ( false == $content ) {
					do_action( 'envoyconnect_content_output', $act );
				} else if ( is_object( $content ) ) {
					echo ;
				}*/
			echo $title;
			echo $date;
			echo $content;

			$bt = __( 'Edit', 'envoyconnect' );

			// RETURN THE RIGHT LINK FOR THE ENVIRONMENT
			if ( ! envoyconnect_is_panels() ) {
				if ( 'user' == $envoyconnect_cap ) {
					$button = '';
				} else {
					$button = '<a class="button-primary profile-actions-edit '.$type.'" title="edit-'.$act->ID.'">'.$bt.'</a>';
				}
			} else {
				$button = '<a class="button envoyconnectpanels-toggle" href="' . home_url( '/envoyconnect/?rel=pauContent&amp;pau_type='.$act->post_type.'&amp;uid='.$act->post_author.'&amp;pid='.$act->ID ) . '">'.$bt.'</a>';
			}

			if ( false != $ok_edit ) {
				$inline_button = apply_filters( 'envoyconnect_inline_action_button', array( 'edit' => $button ), $act, $type, $action, $envoyconnect_cap );
				echo '<div class="tright">';
				if ( is_array( $inline_button ) ) {
					echo implode( ' ', $inline_button );
				} else {
					echo $inline_button;
				}
				echo '</div>';
			}
		?>
		</div>
	</li>
	<?php
}


function envoyconnect_get_username( $u = false, $display = false ) {
	if ( empty( $u ) ) return false;
	if ( !is_object( $u ) ) {
		$u = get_user_by( 'id', $u );
	}

	$default = get_option( '_envoyconnect_get_username' );
	if ( empty( $display ) ) {
		$display = $default;
	}

	switch ( $display ) {

		case 'fullname' :
			$output = $u->first_name . ' ' . $u->last_name;
		break;

		case 'organization' :
			$output = $u->organization;
		break;

		case 'username' :
		default :
			$output = $u->display_name;
		break;

	}

	return $output;

}
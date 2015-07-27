<?php
/**
 * function This is a field API plugin that manages the creation of all sorts of forms.
 *
 * @author your name
 * @param $param
 * @return return type
 */
function bbconnect_form_manager( $args = null ) {
					
	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'fdata' => false, 
					'fvalue' => false, 
					'faction' => false, 
					'ftype' => false, 
					'show_menu' => true, 
					'show_msg' => true, 
					'show_notify' => true, 
					'show_links' => true, 
					'column_1' => array(), 
					'column_2' => array(), 
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );
	
	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	if ( false == $fvalue )
		$fvalue = array();
	
	// POST PROCESS ARGUMENTS FROM SYSTEM PLUGINS	
	if ( isset( $fdata['options']['args'] ) ) {
		foreach( $fdata['options']['args'] as $fk => $fv )
			${$fk} = $fv;
	}
				
	if ( false != $show_menu ) {
		if ( isset( $_POST['_pp_option'] ) ) {
			reset( $_POST['_pp_option'] );
			$first_key = key( $_POST['_pp_option'] );
			$default = str_replace( '_bbconnect_form_', '', $first_key );
		} else {
			$default = false;
		}

?>
		<div id="list-forms">
			<?php bbconnect_list_forms( $fvalue, $default ); ?>
		</div>
		<div id="add-new-form" style="display: none;">
			<input id="new-form" type="text" value="" style="width: 60%;" /> 
			<a id="add-form" class="button-primary" title="new-form"><?php _e( 'Confirm', 'bbconnect' ); ?></a>
		</div>
<?php
	} else { 
		$default = 'default_' . $fdata['meta_key'];
	}
?>
	<div id="show-form" class="options-panel">
		<?php bbconnect_show_form( array( 'pid' => $default, 'show_msg' => $show_msg, 'show_notify' => $show_notify, 'show_links' => $show_links, 'column_1' => $column_1, 'column_2' => $column_2 ) ); ?>
	</div>
<?php
}

function bbconnect_list_forms( $forms = array(), $selection = null ) {
?>
	<select id="form-switch" class="regular-text">
		<option value=""><?php _e( 'Please make a selection', 'bbconnect' ); ?></option>
		<optgroup label="<?php _e( 'Choose a form to edit', 'bbconnect' ); ?>">
		<?php foreach ( $forms as $key => $val ) { ?>
			<option value="<?php echo $key; ?>"<?php echo selected( $key, $selection ); ?>><?php echo esc_attr( $val ); ?></option>
		<?php } ?>
		</optgroup>
		<optgroup label="<?php _e( 'Create a New Form', 'bbconnect' ); ?>">
			<option value="new_form"><?php _e( '+ Add Form', 'bbconnect' ); ?></option>
		</optgroup>
		
	</select>
<?php
}

function bbconnect_new_form() {

	if ( ! wp_verify_nonce( $_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce' ) )
		die(  __( 'terribly sorry.', 'bbconnect' ) );
	
	if ( !isset( $_POST['data'] ))
		return false;
		
	$cid = bbconnect_sanitize( $_POST['data'] );
	
	$forms = get_option( '_bbconnect_user_forms' );
	if ( false == $forms )
		$forms = array();
	
	// MAKE A KEY OUT OF THE USER-SUBMITTED TITLE
	$scid = sanitize_title_with_underscores( $cid );
	
	// IF THE KEY IS UNIQUE, PROCEED
	if ( !isset( $forms[$scid] ) ) {
		
		$forms[$scid] = $cid;
		$update = update_option( '_bbconnect_user_forms', $forms );
		if ( false == $update )
			$uerror = __( 'New form failed', 'bbconnect' );
		
	} else {
	
		$update = false;
		$uerror = __( 'Duplicate form. Please try another name.', 'bbconnect' );
		
	}
	
	bbconnect_list_forms( get_option( '_bbconnect_user_forms' ) );
	
	if ( false != $update ) {
		echo '<div id="response" class="bbconnect_notice">' . __( 'New form successfully created', 'bbconnect' ) . '</div>';
	} else {
		echo '<div id="response" class="bbconnect_error">' . $uerror . '</div>';
	}
	
	die();
	
}

function bbconnect_load_form() {

	if ( ! wp_verify_nonce( $_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce' ) )
		die(  __( 'terribly sorry.', 'bbconnect' ) );

	if ( !isset( $_POST['data'] ))
		return false;
		
	$pid = $_POST['data'];
	
	bbconnect_show_form( array( 'pid' => $pid ) );
	
	die();

}

function bbconnect_delete_form() {

	if ( ! wp_verify_nonce( $_POST['bbconnect_admin_nonce'], 'bbconnect-admin-nonce' ) )
			die(  __( 'terribly sorry.', 'bbconnect' ) );
	
	if ( !isset( $_POST['data'] ))
		return false;
		
	$pid = $_POST['data'];
	$option = '_bbconnect_form_' . $pid;
	$forms = get_option( '_bbconnect_user_forms' );
	
	if ( isset( $forms[$pid] ) )
		unset( $forms[$pid] );
	
	if ( false != update_option( '_bbconnect_user_forms', $forms ) )
		$delete = delete_option( $option );
	
	bbconnect_list_forms( get_option( '_bbconnect_user_forms' ) );
	
	if ( !isset( $delete ) ) {
		echo '<div id="response" class="bbconnect_error">' . __( 'Delete failed', 'bbconnect' ) . '</div>';
	} else {
		echo '<div id="response" class="bbconnect_notice">' . __( 'Form successfully deleted', 'bbconnect' ) . '</div>';
		
	}
	
	die();

}


function bbconnect_form_notify( $form_name = null ) {
	
	return apply_filters( 'bbconnect_form_notify', array(
		 
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'notify_enable', 
								'name' => __( 'Enable Notifications', 'bbconnect' ),
								'help' => __( 'If enabled, email notifications will be sent using the information below.', 'bbconnect' ), 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'default' => true, 
													'choices' => 'true',  
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'notify', 
								'name' => sprintf( __( '%1$sSend To%2$s', 'bbconnect' ), '<strong>', ':</strong>' ), 
								'help' => __( 'Which email addresses should receive notifications. Multiple emails should be separated by commas.', 'bbconnect' ), 
								'description' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => get_option( 'admin_email' ), 
								) 
		) ),
			
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'notify_from', 
								'name' => sprintf( __( '%1$sFrom%2$s', 'bbconnect' ), '<strong>', ':</strong>' ), 
								'help' => __( 'If blank, this will be the user\'s email address.', 'bbconnect' ), 
								'description' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => false, 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'notify_from_name', 
								'name' => sprintf( __( '%1$sFrom Name%2$s', 'bbconnect' ), '<strong>', ':</strong>' ), 
								'help' => __( 'If blank, this will be the user\'s name.', 'bbconnect' ), 
								'description' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => false, 
								) 
		) ),
		
		array( 'meta' => array( 
								'source' => 'bbconnect', 
								'meta_key' => 'notify_subject', 
								'name' => sprintf( __( '%1$sSubject%2$s', 'bbconnect' ), '<strong>', ':</strong>' ), 
								'help' => __( 'The default subject line unless you allow the user to write their own.', 'bbconnect' ), 
								'description' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => sprintf( __( '%1$s Submission', 'bbconnect' ), $form_name ), 
								) 
		) ),

	) );	
}

function bbconnect_show_form( $args = null ) {
					
	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'pid' => false, 
					'show_msg' => true, 
					'show_notify' => true, 
					'show_links' => true, 
					'column_1' => array(), 
					'column_2' => array(),  
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );
	
	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	$option = '';
	$delete = __( 'Delete this field', 'bbconnect' );
	$undo = __( 'Undo', 'bbconnect' );

	if ( false == $pid )
		return false;
		
		$option = '_bbconnect_form_' . $pid;
		$form = get_option( $option );
		
		$forms = get_option( '_bbconnect_user_forms' );
		$form_name = $forms[$pid];
							
	?>
	
	<div id="form-data">
	<?php if ( false != $show_msg ) { ?>
		<div class="options-field">
			<h3><?php _e( 'Messages', 'bbconnect' ); ?></h3>
			<p><?php printf( __( 'Add a %1$sgreeting%2$s or instructions to the form.', 'bbconnect' ), '<strong>', '</strong>' ); ?></p>
			<textarea style="height: 100px;" name="_pp_option[<?php echo $option; ?>][msg]"><?php if ( isset( $form['msg'] ) ) echo bbconnect_scrub( 'bbconnect_esc_html', $form['msg'] ); ?></textarea>
			
			<p><?php printf( __( 'Add a %1$sthank you%2$s or instructions to the form.', 'bbconnect' ), '<strong>', '</strong>' ); ?></p>
			<textarea style="height: 100px;" name="_pp_option[<?php echo $option; ?>][confirm]"><?php if ( isset( $form['confirm'] ) ) echo bbconnect_scrub( 'bbconnect_esc_html', $form['confirm'] ); ?></textarea>
		</div>
	<?php } ?>
	
	<?php 
		if ( false != $show_notify ) { 
			
			$fields = bbconnect_form_notify( $form_name );
			echo '<div class="options-field">';
			echo '<h3>' . __( 'Notifications', 'bbconnect' ) . '</h3>';
			echo '<ul>';
			foreach ( $fields as $field ) { 
						
				$args['meta'] = $field['meta'];
				$args['type'] = 'option';
				$args['action'] = 'edit';
				$args['swap_name'] = array( $option );
				if ( is_array( $form ) && isset( $form[$field['meta']['meta_key']] ) ) {
					$args['post_val'] = $form[$field['meta']['meta_key']];
				} else {
					$args['post_val'] = false;
				}
	
				bbconnect_get_field( $args );
			}
			echo '</ul>';
			echo '</div>';
		}
			/*	
	?>
		<div class="options-field">
			<h3><?php _e( 'Notifications', 'bbconnect' ); ?></h3>
			<p><?php printf( __( 'Decide which email addresses should be %1$snotified%2$s after the form is submitted. Multiple emails should be separated by commas.', 'bbconnect' ), '<strong>', '</strong>' ); ?></p>
			<p><input type="text" class="regular-text" name="_pp_option[<?php echo $option; ?>][notify]" value="<?php if ( isset( $form['notify'] ) ) { echo bbconnect_scrub( 'bbconnect_esc_html', $form['notify'] ); } else { echo get_option( 'admin_email' ); } ?>" /></p>
			
			<p><?php printf( __( 'Set the default %1$ssubject%2$s', 'bbconnect' ), '<strong>', '</strong>.' ); ?></p>
			<p><input type="text" class="regular-text" name="_pp_option[<?php echo $option; ?>][subject]" value="<?php if ( isset( $form['subject'] ) ) { echo bbconnect_scrub( 'bbconnect_esc_html', $form['subject'] ); } else { printf( __( '%1$s Submission', 'bbconnect' ), $form_name ); } ?>" /></p>
		</div>
	<?php } */
	?>
	
		<div class="options-field">
			<h3><?php _e( 'Add fields', 'bbconnect' ); ?></h3>
			<?php bbconnect_user_data_select( array( 'id' => 'form-field-select', 's_context' => 'form_api', 'address_unlock' => true ) ); ?> 
			<a class="button" id="add-form-field" rel="form-field-select"><?php _e( '+ Add Field', 'bbconnect' ); ?></a>
		</div>
		
		<div class="options-field">
			<div class="inside t-panel" style="display: block;">

			<?php
				// IF IT'S A SYSTEM-GENERATED FORM
				if ( 'bbconnect' == $form['source'] || false !== strpos( $pid, 'default' ) ) {
					echo '<input type="hidden" name="_pp_option['.$option.'][source]" value="bbconnect" />';
				} else {
					echo '<input type="hidden" name="_pp_option['.$option.'][source]" value="user" /></div>';
				}

				// PULL THE SPECIAL FORM FIELDS
				$form_fields = bbconnect_form_api_fields();
			?>
				<div id="column_1_holder">
					<ul id="column_1" title="<?php echo $option; ?>" class="forms-sortable connected-forms-sortable primary-list column">
					<?php 
						// LOOP THROUGH ALL OF THE FIELDS REGISTERED WITH THE SYSTEM
						// RETRIEVE THEIR VALUES FOR DISPLAY
						// IF IT'S A GROUP, MAKE A SUBLIST
						// WE'LL USE DRAG & DROP FOR SORTING
						if ( isset( $form['column_1'] ) && !empty( $form['column_1'] ) )
							$column_1 = $form['column_1'];
						
						foreach ( $column_1 as $key => $value ) { 
							if ( isset( $form_fields[$value] ) ) {
								$val_title = esc_attr( $form_fields[$value]['name'] );
							} else {
								$val_arr = bbconnect_get_option( $value );
								$val_title = esc_attr( $val_arr['name'] );
							}
						?>
						<li>
							<div class="t-wrapper">
								<div class="t-title">
									<span><?php echo $val_title; ?></span>
									<span class="right">
										<a class="delete" rel="<?php echo $key; ?>" title="<?php echo $delete; ?>">&nbsp;</a>
										<a class="undo" rel="<?php echo $key; ?>" title="<?php echo $undo; ?>">&nbsp;</a>
									</span>
									<input class="column-input" type="hidden" id="<?php echo $key; ?>" name="_pp_option[<?php echo $option; ?>][column_1][]" value="<?php echo $value; ?>" />
								</div>
							</div>
						</li>
						<?php
						} 
					?>
					</ul>
				</div>
				
				<div id="column_2_holder">
					<ul id="column_2" title="<?php echo $option; ?>" class="forms-sortable connected-forms-sortable primary-list column">
					<?php
						if ( isset( $form['column_2'] ) && !empty( $form['column_2'] ) )
							$column_2 = $form['column_2'];
							
						foreach ( $column_2 as $key => $value ) { 
							if ( isset( $form_fields[$value] ) ) {
								$val_title = esc_attr( $form_fields[$value]['name'] );
							} else {
								$val_arr = bbconnect_get_option( $value );
								$val_title = esc_attr( $val_arr['name'] );
							}
						?>
						<li>
							<div class="t-wrapper">
								<div class="t-title">
									<span><?php echo $val_title; ?></span>
									<span class="right">
										<a class="delete" rel="<?php echo $key; ?>" title="<?php echo $delete; ?>">&nbsp;</a>
										<a class="undo" rel="<?php echo $key; ?>" title="<?php echo undo; ?>">&nbsp;</a>
									</span>
									<input class="column-input" type="hidden" id="<?php echo $key; ?>" name="_pp_option[<?php echo $option; ?>][column_2][]" value="<?php echo $value; ?>" />
								</div>
							</div>
						</li>
						<?php
						} 
					?>
					</ul>
				</div>
			</div>
		</div>
		
		<?php if ( false != $show_links ) { ?>
			<div class="options-field">
				<h3><?php _e( 'Display this form', 'bbconnect' ); ?></h3>
				<?php
					echo '<p>' . sprintf( __( 'Use the link, shortcode or menu options below to link to this form. You can replace %1$s with your own text.', 'bbconnect' ), '<strong>' . $form_name . '</strong>' ) . '</p>';
					
					echo '<p class="code-help"><strong>' . __( 'Embed Shortcode (embeds the form. make sure you have turned on form embedding on the panels tab)', 'bbconnect' ) . '</strong><br /><textarea readonly class="fin-btn">[bbconnectf id="' . $pid . '"]</textarea></p>';
					
					echo '<p class="code-help"><strong>' . __( 'Link Shortcode (generates a link)', 'bbconnect' ) . '</strong><br /><textarea readonly class="fin-btn">[ppf_link id="' . $pid . '" text="' . $form_name . '"]</textarea></p>';					
					echo '<p class="code-help"><strong>' . __( 'Local Link', 'bbconnect' ) . '</strong><br /><textarea readonly class="fin-btn">' . htmlentities('<a class="bbconnectpanels-toggle" title="' . $pid . '" href="' . home_url() . '/bbconnect/?rel=contact&amp;pau_form='.$pid.'">') . $form_name . htmlentities('</a>') . '</textarea></p>';
					
					$bbconnectref = urlencode( serialize( array( 'rel' => 'contact', 'pau_form' => $pid ) ) );
					echo '<p class="code-help"><strong>' . __( 'Direct Link', 'bbconnect' ) . '</strong><br /><textarea readonly class="fin-btn">' . home_url() . '?bbconnectref=' . $bbconnectref . '</textarea></p>';
										
					bbconnect_add_to_nav_menu( $form_name, $pid, '/bbconnect/?rel=contact&amp;pau_form='.$pid );
				?>		
			</div>
		<?php
			} 
		?>
		
		<?php
			// IF IT'S A SYSTEM-GENERATED FORM
			if ( isset( $form['source'] ) && 'bbconnect' != $form['source'] ) {
				if ( false === strpos( $pid, 'default' ) ) {
		?>
		<div class="options-field">
			<h3><?php _e( 'Delete this form', 'bbconnect' ); ?></h3>
			<div class="tright inside"><a class="button" id="delete-form" rel="<?php echo $pid; ?>"><?php _e( 'Delete This Form', 'bbconnect' ); ?></a></div>
		</div>
		<?php 
				}
			}
		?>
			
		<?php do_action( 'bbconnect_form_ext_msg', $pid ); ?>
			
	</div>
		
	
	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(document).ready(function(){
			jQuery('#wpfooter').hide();
			jQuery('#show-form').on('click', '.delete', function(){
				jQuery(this).closest('li').remove();
			});
			// SORTING FUNCTION FOR LISTS
			jQuery(function() {
				jQuery('.forms-sortable').sortable({
					connectWith: '.connected-forms-sortable', 
					appendTo: document.body, 
					placeholder: 'pp-ui-highlight', 
					forcePlaceholderSize: true, 
					forceHelperSize: true,  
					update: function(event, ui) { 
						var cid = jQuery(this).attr('id');
						var oid = jQuery(this).attr('title');
						var fid = ui.item.attr('id');
						ui.item.find('.column-input').attr('name','_pp_option['+oid+']['+cid+'][]');
					}
				}).disableSelection();
			});
			jQuery('#add-form-field').click(function(){
				//var cref = jQuery(this).previous('select');
				var fid = jQuery('#show-form select').val();
				var fna = jQuery('#show-form select option:selected').text();
				jQuery('<li><div class="t-wrapper"><div class="t-title">'+fna+'<span></span><span class="right"><a class="delete" rel="'+fid+'" title="<?php echo $delete; ?>">&nbsp;</a><a class="undo" rel="'+fid+'" title="<?php echo $undo; ?>">&nbsp;</a></span><input class="column-input" type="hidden" id="'+fid+'" name="_pp_option[<?php echo $option; ?>][column_1][]" value="'+fid+'" /></div></div></li>').appendTo('#column_1');
				return false;
			});
		});
	</script>
	<?php
}


// ADDITIONAL FORM FIELDS
function bbconnect_form_api_fields() {
	
	return apply_filters( 'bbconnect_form_api_fields', array(
						
		'_pp_form_subject' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_pp_form_subject', 
								'name' => __( 'Subject', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'text',
													'req' => false, 
													'public' => false, 
													'choices' => false, 
								) 
		),
		
		'_pp_form_message' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_pp_form_message', 
								'name' => __( 'Message', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'textarea',
													'req' => true, 
													'public' => false, 
													'choices' => false, 
								) 
		),
		
		'_pp_form_cc' => array( 
								'source' => 'bbconnect', 
								'meta_key' => '_pp_form_cc', 
								'name' => __( 'Send me a copy', 'bbconnect' ), 
								'help' => '', 
								'options' => array( 
													'field_type' => 'checkbox',
													'req' => false, 
													'public' => false, 
													'choices' => false, 
								) 
		),
		
	));

}

add_action( 'bbconnect_user_data_select_form_api', 'bbconnect_user_data_select_form_api_ops', 10, 2 );
function bbconnect_user_data_select_form_api_ops( $meta_key, $wpr ) {
?>
	<optgroup label="<?php _e( 'Standard Fields', 'bbconnect' ); ?>">
	<?php
		$bbconnect_api_fields = bbconnect_form_api_fields();
		foreach ( $bbconnect_api_fields as $key => $val )
			echo '<option value="' . $val['meta_key'] . '"' . selected( $meta_key, $val['meta_key'] ) . '>' . $val['name'] . '</option>';
	?>
	</optgroup>
<?php
}


function bbconnect_add_to_nav_menu( $name, $title, $link ) {
	
		
	echo '<p class="code-help"><a class="help" title="' . __( 'If you wish to remove this menu item, you must delete it from the actual menu instead of unchecking the box(es) below.', 'bbconnect' ) . '">&nbsp;</a><strong>' . __( 'Add to Menu', 'bbconnect' ) . '</strong><br />';
	if ( isset( $_POST['wppp_menu'][$title] ) ) {
		foreach( $_POST['wppp_menu'][$title] as $menu ) {
			if ( is_nav_menu( $menu ) ) {
				$items = wp_get_nav_menu_items( $menu );
				$checked = false;
				foreach( $items as $item ) {
					if ( false !== strpos( $item->post_name, sanitize_title_with_dashes( $name ) ) )
					$checked = true;
				}
				if ( false == $checked )
					wp_update_nav_menu_item( $menu, 0, array(
						'menu-item-attr-title' => $title, 
						'menu-item-title' =>  $name, 
						'menu-item-classes' => 'bbconnectpanels-toggle', 
						'menu-item-url' => home_url( $link ), 
						'menu-item-status' => 'publish'));
			}
		}
	}
	
	$menus = wp_get_nav_menus();
	echo '<ul>';
	foreach ( $menus as $menu ) {
		$items = wp_get_nav_menu_items( $menu->term_id );
		$checked = false;
		foreach( $items as $item ) {
			if ( false !== strpos( $item->post_name, sanitize_title_with_dashes( $name ) ) )
			$checked = ' checked="checked"';
		}
		echo '<li><input type="checkbox" name="wppp_menu['.$title.'][]" value="'.$menu->term_id.'"'.$checked.' /> '.$menu->name . '</li>';
	}
	echo '</ul>';
	echo '</p>';

}

?>
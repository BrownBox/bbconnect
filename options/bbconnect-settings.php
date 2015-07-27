<?php

/**
 * This is the wrapper function for the EnvoyConnect Options API. Your function might be called {prefix}_options.
 *
 * @since 1.0.2
 *
 * @param none This should take no parameters but should call the general bbconnect_options_form.
 *
 * @return html outputs the options page with the activated option tab + contents.
 */

function bbconnect_options() {

	// THE NAVIGATION
	// THE KEYS SHOULD BE NAMED PLACEHOLDERS FOR THE FUNCTION THAT RETURNS AN ARRAY OF FIELD PARAMETERS
	$navigation = apply_filters( 'bbconnect_options_tabs', array( 
		
		'bbconnect_general_settings' => array( 
									'title' => __( 'General', 'bbconnect' ), 
									'subs' => false, 
							),
		
		'bbconnect_pro_settings' => array( 
									'title' => __( 'Pro', 'bbconnect' ), 
									'subs' => false, 
							),
							
		'bbconnect_user_settings' => array( 
									'title' => __( 'Users', 'bbconnect' ), 
									'subs' => false, 
							),
		
		'bbconnect_actions_settings' => array( 
									'title' => __( 'Actions', 'bbconnect' ), 
									'subs' => false, 
							),
							
		'bbconnect_system_settings' => array( 
									'title' => __( 'System', 'bbconnect' ), 
									'subs' => false, 
							),
							
		'bbconnect_forms_settings' => array( 
									'title' => __( 'Forms', 'bbconnect' ), 
									'subs' => false, 
							),
									
		'bbconnect_panel_settings_trans' => array( 
									'title' => __( 'Panels', 'bbconnect' ), 
									'subs' => false, 
							),
	) );
	
	bbconnect_options_form( __( 'EnvoyConnect Options', 'bbconnect' ), $navigation );

}


/**
 * This function saves EnvoyConnect Options data at init to catch taxonomic options.
 *
 * @since 1.0.2
 *
 * @param none
 *
 * @return var sets the global notification.
 */

function bbconnect_options_save() {	

	if ( isset( $_POST['_bbc_option'] ) ) { 
		
		// SECURITY CHECK
		check_admin_referer( 'bbconnect-nonce' );
				
		// SANITIZE ALL INPUT
		bbconnect_scrub( 'bbconnect_sanitize', $_POST );
		
		// LET PLUGINS MANIPULATE THEIR OWN OPTIONS
		do_action( 'bbconnect_options_save_ext' );
		
		global $notice, $bbconnect_flush;
		$notice = array();
		$bbconnect_flush = apply_filters( 'bbconnect_flush_permalinks', false, $_POST );

		foreach( $_POST['_bbc_option'] as $key => $value ) {
			if ( update_option( $key, $value ) )
				$notice[] = $key;
		}
					
	}

}


/**
 * This is the main function for the EnvoyConnect Options API.
 *
 * @since 1.0.2
 *
 * @param str The title for your page.
 * @param arr The navigation elements.
 *
 * @return html outputs the options page with the activated option tab + contents.
 */

function bbconnect_options_form( $form_title, $tabs = array() ) {	

	// STOP THEM IF THEY SHOULDN'T BE HERE
	if ( !current_user_can('list_users' ) )
	    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	
	global $notice;
		
	if ( !empty( $notice ) )
		echo '<p class="bbconnect_notice">' . __( 'Settings Updated', 'bbconnect' ) . ': ' . implode( ', ', $notice ) . '</p>';
		
?>
	<div id="bbconnect" class="wrap">
	<div id="icon-options-general" class="icon32"><br /></div>
		<h2><?php echo $form_title; ?></h2>

		<form enctype="multipart/form-data" action="" method="POST">
			<?php 
				$nav_tab = '';
				$nav_sel = '';
				if ( isset( $_GET['tab'] ) ) { 
					$active = $_GET['tab']; 
				} else { 
					$active = current( array_keys( $tabs ) );
				}
				foreach ( $tabs as $key => $val ) { 
					$pre = '';
					if ( isset( $_GET['post_type'] ) ) { $pre .= 'post_type=' . $_GET['post_type'] . '&'; }
					if ( $active == $key ) { 
						$act_tab = ' nav-tab-active';
						$selected = ' selected="selected"';
					} else { 
						$act_tab = '';
						$selected = '';
					}
					
					$nav_sel .= '<option value="?' . $pre . 'page=' . $_GET['page'] . '&tab=' . $key . '"' . $selected . '>' . $val['title'] . '</option>';
					$nav_tab .= '<a href="?' . $pre . 'page=' . $_GET['page'] . '&tab=' . $key . '" class="nav-tab' . $act_tab . '">' . $val['title'] . '</a>';
				} 
			?>
			<h2 class="nav-tab-wrapper non-responsive"><?php echo $nav_tab; ?></h2>
			<div class="nav-tab-wrapper responsive"><select class="nav-tab-select"><?php echo $nav_sel; ?></select></div>
			
			<?php 
				// SET THE NONCE
				wp_nonce_field('bbconnect-nonce');
				
				// DISPLAY ANY SUB NAVIGATION
				if ( !empty( $tabs[$active]['subs'] ) ) {
					$sub_nav = bbconnect_options_sub_nav( $active, $tabs[$active]['title'], $tabs[$active]['subs'] );
					echo $sub_nav['nav'];
					$active = $sub_nav['active'];
				}
				
				// FLUSH THE PERMALINKS IF WE NEED TO DO ANY REWRITES
				global $bbconnect_flush;
				if ( false != $bbconnect_flush ) {
					flush_rewrite_rules();					
				}
				
				// DO ANY PRE OPTION KINDA THINGS
				do_action( 'bbconnect_options_pre', $active );
			?>
			<div class="options-panel">
				<div class="options-field"><div class="inside">
					<fieldset>
						<ul>
						<?php
							$options = call_user_func( $active );
							if ( !empty( $options ) ) {
								foreach ( $options as $setting ) {
									$setting['type'] = 'option';
									$setting['action'] = 'edit';
									if ( isset( $append ) )
										$setting['swap_name'] = $setting['meta']['meta_key'] . '_' . $append;
									bbconnect_get_field( $setting );
								}
							}
						?>
						</ul>
					</fieldset>
				</div></div>
			</div>
			<?php
				// DO ANY POST OPTION KINDA THINGS
				do_action( 'bbconnect_options_post', $active );
			?>
			<p class="submit"><input type="submit" name="save_options" value="<?php _e( 'Save Your Options', 'bbconnect ' ); ?>" class="button-primary" /></p>
			
		</form>
	</div>							
<?php
}


/**
 * Displays any sub-navigation elements.
 *
 * @since 1.0.2
 *
 * @param str The active tab.
 * @param arr The sub-navigation elements.
 *
 * @return arr Holds the html for the menu and the function placeholder to call for the fields.
 */

function bbconnect_options_sub_nav( $active, $active_title, $subs = array() ) {
	$sub_nav = array();
	$sublinks = array();
	if ( isset( $_GET['sub'] ) ) { 
		$subactive = $_GET['sub'];
		$sublinks[] = '<a href="?page=' . $_GET['page'] . '&tab=' . $active . '">' . $active_title . '</a>'; 
	} else { 
		$subactive = $active; 
		$sublinks[] = $active_title;
	}
	
	foreach ( $subs as $key => $val ) { 
		$subclass = '';
		if ( $subactive == $key )
			$subclass = 'sub-active'; 
		
		$sublinks[] = '<a class="'.$subclass.'" href="?page=' . $_GET['page'] . '&tab=' . $active . '&sub=' . $key . '">' . $val . '</a>'; 
	}
	$sub_nav['active'] = $subactive;
	$sub_nav['nav'] = '<h3 class="sub-tab-wrapper">' . implode( ' | ', $sublinks ) . '</h3>';
	
	return $sub_nav; 
}

// FORM FIELD COMPONENT
add_action( 'wp_ajax_bbconnect_ffc_new_option', 'bbconnect_ffc_new_option' );
function bbconnect_ffc_new_option() {
	$context = $_POST['context'];
	$v = $_POST['v'];
	do_action( 'bbconnect_ffc_option', $context, $v, false );
	die();
}
function bbconnect_ffc( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array( 
					'context' => false, // MUST BE THE OPTION NAME THE COMPONENT IS ASSOCIATED WITH
					'c_array' => false, // MUST BE THE OPTION VALUE THE COMPONENT IS ASSOCIATED WITH
					'fields' => array(), 
					'options' => false, 
					'columns' => 1, 
					'title' => __( 'Add fields', 'bbconnect' ), 
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	if ( false === $context )
		return false;
		
	if ( !is_array( $c_array ) )
		$c_array = get_option( $context );
				
	// SET ADDITIONAL PRESETS
	$delete = __( 'Delete', 'bbconnect' );
	$undo = __( 'Undo', 'bbconnect' );
?>

	<div id="<?php echo $context; ?>_select" class="options-field options-selector">
		<?php if ( false != $title ) echo '<h3>' . $title . '</h3>'; ?>
		<select id="<?php echo $context; ?>_select_field">
			<option value=""><?php _e( 'Make A Selection', 'bbconnect' ); ?></option>
			<?php
				$fields_display = array();
				foreach ( $fields as $k => $v ) {
					$disabled = '';
					if ( is_array( $v ) ) {
						echo '<optgroup label="' . $k . '">';
						foreach ( $v as $gk => $gv ) {
							if ( in_array_r( $k, $c_array ) )
								$disabled = ' disabled="disabled"';
								
							echo '<option value="' . $gk . '"' . $disabled . '>' . $gv . '</option>';
							$fields_display[$gk] = $gv;	
						}
					} else {
						if ( in_array_r( $k, $c_array ) )
							$disabled = ' disabled="disabled"';
							
						echo '<option value="' . $k . '"' . $disabled . '>' . $v . '</option>';
						$fields_display[$k] = $v;
					}
				}
			?>
		</select>
		<a id="<?php echo $context; ?>_add_field" class="button add-ffc-field"><?php _e( '+ Add', 'bbconnect' ); ?></a>
	</div>
			
	<div id="<?php echo $context; ?>-sort" class="options-field">
		<div class="inside t-panel" style="display: block;">
			<input type="hidden" name="_bbc_option[<?php echo $context; ?>][e]" value="1" />
			<div class="column-holder<?php if ( (int) $columns == 1 ) echo ' full'; ?>">
				<ul data-col="column_1" id="<?php echo $context; ?>-one" title="<?php echo $context; ?>" class="<?php echo $context; ?>-sortable connected-<?php echo $context; ?>-sortable primary-list column">
				<?php 
					// LOOP THROUGH ALL OF THE FIELDS
					// RETRIEVE THEIR VALUES FOR DISPLAY
					// IF IT'S A GROUP, MAKE A SUBLIST
					// WE'LL USE DRAG & DROP FOR SORTING
					if ( isset( $c_array['column_1'] ) && !empty( $c_array['column_1'] ) ) {
						$column_1 = $c_array['column_1'];
					
					
					foreach ( $column_1 as $k => $v ) { 
						if ( false !== strpos( $v, 'error' ) ) {
							$alt_error = ' error-wash';
						} else {
							$alt_error = '';
						}
					?>
					<li>
						<div class="t-wrapper<?php echo $alt_error; ?>">
							<span>
								<span class="handle"></span>
								<span class="t-trigger" title="option-<?php echo $context.$v; ?>">
									<?php echo $fields_display[$v]; ?>
								</span>
							</span>
							<span class="right">
								<a class="delete" title="<?php echo $delete; ?>" rel="<?php echo $v; ?>">&nbsp;</a>
								<a class="undo" title="<?php echo $undo; ?>">&nbsp;</a>
							</span>
							<div id="option-<?php echo $context.$v; ?>" class="inside t-panel" style="display: none;">
								<input class="column-input" type="hidden" id="<?php echo $k; ?>" name="_bbc_option[<?php echo $context; ?>][column_1][]" value="<?php echo $v; ?>" />
								<div id="option-<?php echo $context.$v; ?>-field">
									<?php do_action( 'bbconnect_ffc_option', $context, $v, $c_array ); ?>
								</div>
							</div>
						</div>
					</li>
					<?php
					} 
					}
				?>
				</ul>
			</div>
			<?php if ( (int) $columns > 1 ) { ?>
			<div class="column-holder">
				<ul data-col="column_2" id="<?php echo $context; ?>-two" title="<?php echo $context; ?>" class="<?php echo $context; ?>-sortable connected-<?php echo $context; ?>-sortable primary-list column">
				<?php 
					// LOOP THROUGH ALL OF THE FIELDS
					// RETRIEVE THEIR VALUES FOR DISPLAY
					// IF IT'S A GROUP, MAKE A SUBLIST
					// WE'LL USE DRAG & DROP FOR SORTING
					if ( isset( $c_array['column_2'] ) && !empty( $c_array['column_2'] ) ) {
						$column_2 = $c_array['column_2'];
					
					foreach ( $column_2 as $k => $v ) { 
						if ( false !== strpos( $v, 'error' ) ) {
							$alt_error = ' error-wash';
						} else {
							$alt_error = '';
						}
					?>
					<li>
						<div class="t-wrapper<?php echo $alt_error; ?>">
							<span>
								<span class="handle"></span>
								<span class="t-trigger" title="option-<?php echo $context.$v; ?>">
									<?php echo $fields_display[$v]; ?>
								</span>
							</span>
							<span class="right">
								<a class="delete" title="<?php echo $delete; ?>" rel="<?php echo $v; ?>">&nbsp;</a>
								<a class="undo" title="<?php echo $undo; ?>">&nbsp;</a>
							</span>
							<div id="option-<?php echo $context.$v; ?>" class="inside t-panel" style="display: none;">
								<input class="column-input" type="hidden" id="<?php echo $k; ?>" name="_bbc_option[<?php echo $context; ?>][column_2][]" value="<?php echo $v; ?>" />
								<div id="option-<?php echo $context.$v; ?>-field">
									<?php do_action( 'bbconnect_ffc_option', $context, $v, $c_array ); ?>
								</div>
							</div>
						</div>
					</li>
					<?php
					} 
					}
				?>
				</ul>
			</div>
			<?php } ?>
		</div>
	</div>
	<script type="text/javascript">
		jQuery.noConflict();
		jQuery(document).ready(function() {
			jQuery('#<?php echo $context; ?>-sort').on('click', '.delete', function(){
				var fid = jQuery(this).attr('rel');
				jQuery('#<?php echo $context; ?>_select_field option[value='+fid+']').removeAttr('disabled'); // NOT DONE!
				jQuery(this).closest('li').remove();
			});
			// SORTING FUNCTION FOR LISTS
			jQuery(function() {
				jQuery('.<?php echo $context; ?>-sortable').sortable({
					connectWith: '.connected-<?php echo $context; ?>-sortable', 
					handle: '.handle', 
					appendTo: document.body, 
					placeholder: 'pp-ui-highlight', 
					forcePlaceholderSize: true, 
					forceHelperSize: true,  
					update: function(event, ui) { 
						var cid = jQuery(this).data('col');
						var oid = jQuery(this).attr('title');
						var fid = ui.item.attr('id');
						ui.item.find('.column-input').attr('name','_bbc_option['+oid+']['+cid+'][]');
					}
				}).disableSelection();
			});
			jQuery('#<?php echo $context; ?>_add_field').click(function(){
				//var cref = jQuery(this).previous('select');
				var fid = jQuery('#<?php echo $context; ?>_select_field').closest('select').val();
				
				if ( fid.length == 0 ) {
					return false;
				}
				
				var fna = jQuery('#<?php echo $context; ?>_select_field option:selected').text();
				var selected = jQuery('#<?php echo $context; ?>_select_field option:selected');
				jQuery('#<?php echo $context; ?>_select_field option:selected').attr('disabled','disabled');
				jQuery('#<?php echo $context; ?>_select_field').closest('select').val('');
				//alert('yeah');
				jQuery('<li><div class="t-wrapper"><span><span class="handle"></span><span class="t-trigger open" title="option-<?php echo $context; ?>'+fid+'">'+fna+'</span></span><span class="right"><a class="delete" title="<?php echo $delete; ?>" rel="'+fid+'">&nbsp;</a><a class="undo" title="<?php echo $undo; ?>">&nbsp;</a></span><div id="option-<?php echo $context; ?>'+fid+'" class="inside t-panel" style="display: none;"><input class="column-input" type="hidden" id="'+fid+'" name="_bbc_option[<?php echo $context; ?>][column_1][]" value="'+fid+'" /><div id="option-<?php echo $context; ?>'+fid+'-field"></div></div></div></li>').appendTo('#<?php echo $context; ?>-one');
				
				jQuery('#option-<?php echo $context; ?>'+fid).each(function(){
	
					// SHOW THE OPTION PANEL
					jQuery(this).show();
					
					// PLAY THE LOADER
					jQuery('#option-<?php echo $context; ?>'+fid+'-field').empty().html('<div style="padding:10px 0;"><img src="'+bbconnectAdminAjax.ajaxload+'" /></div>');
						 
					jQuery.post( 
						bbconnectAdminAjax.ajaxurl, 
						{ action : 'bbconnect_ffc_new_option', v : fid, context : '<?php echo $context; ?>', bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce },
					    function( response ) {
					        // DISPLAY THE RESPONSE
					        jQuery('#option-<?php echo $context; ?>'+fid+'-field').empty().html(response);
					        //jQuery('.chzn-select').chosen();
					    }
					);
				});
				
				
				//attr('name','_bbc_option[<?php echo $context; ?>][ffc_opts]['+fid+']');
				//jQuery('.temp').removeClass('temp'); // formerly had list with class of temp
				return false;
			});
		});
	</script>
<?php
}
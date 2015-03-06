<?php

function envoyconnect_filter_profile( $args = '' ) {
	$defaults = array( 
					'display' => 'open', 
					'search' => array( array() ),
					'order_by' => 'ID', 
					'order' => 'DESC', 
					'mod_results' => 'AND',
					'users_per_page' => '25',
					'action' => array()
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	global $current_user, $ret_res;
?>
	
<div id="filter" class="drawer"<?php if ( 'open' === $display ) { echo ' style="display: block;"'; } ?>>
	<div class="inside">
		<?php 
			$r_subnav = array();
			if ( false != get_option( '_envoyconnect_' . $current_user->ID . '_current' ) )
				$r_subnav[] = '<a href="' . admin_url( 'users.php?page=envoyconnect_reports' ) . '">' . __( 'Re-load last search', 'envoyconnect' ) . '</a>';
			
			$r_subnav[] = '<a href="' . admin_url( 'users.php?page=envoyconnect_reports&reset=true' ) . '">' . __( 'Reset current search', 'envoyconnect' ) . '</a>';
				
			echo implode( ' | ', $r_subnav );
		?>
	</div>
	<form id="filter-form" class="report-form" enctype="multipart/form-data" action="<?php echo admin_url( 'users.php' ); ?>" method="POST">
	
		<div id="filter-profile"><div class="inside">
			<?php $envoyconnect_user_queries = get_option( '_envoyconnect_user_queries' ); ?>
			<div id="toggle-user-meta" class="m-panel">
				<ul id="filter-list" class="query-list sortable">
				<?php
					$ac = count( $search );
					$lc = 1;
					foreach( $search as $key => $val ) {
						$args = array();
						$args['skey'] = $lc;
						$args['sval'] = $val;
						if ( isset( $val['field'] ) )
							$args['meta_key'] = $val['field'];
							
						if ( $ac > $lc )
							$args['button'] = 'sub';					
						
						envoyconnect_search_row( $args );
						$lc++;
					}
				?>
				</ul>
			</div>
		</div></div>
		<div>
			<div style="padding: 15px;">
				<span class="filter-tab"><?php _e( 'Order', 'envoyconnect' ); ?>: <select name="order"><option value="DESC"<?php selected( $order, 'DESC' ); ?>><?php _e( 'Descending', 'envoyconnect' ); ?></option><option value="ASC"<?php selected( $mod_results, 'ASC' ); ?>><?php _e( 'Ascending', 'envoyconnect' ); ?></option></select></span><span class="filter-tab"><?php 
											_e( 'Order by', 'envoyconnect' );
											echo ': ';
											$ob_ppum = array( 'envoyconnect_ID', 'envoyconnect_user_registered', 'envoyconnect_first_name', 'envoyconnect_last_name', 'envoyconnect_user_login', 'envoyconnect_role' );
											envoyconnect_user_data_select( array( 'meta_key' => $order_by, 'value' => $order_by, 'name' => 'order_by', 'ppum' => $ob_ppum, 'chosen' => false, 's_context' => 'orderby' ) ); 
									?></span>

				<span><?php _e( 'Results Per Page', 'envoyconnect' ); ?>: <input class="input-short small-text" type="text" name="users_per_page" value="<?php echo $users_per_page; ?>" /></span>
				<?php /* <p><input class="input-short small-text" type="checkbox" name="view_all" value="1" /> View all records regardless of search criteria</p> */ ?>
				<input id="page_num" type="hidden" name="page_num" value="1" />
				<input type="hidden" name="reorder_by" value="1" />
				<span style="padding: 15px 15px 15px 0; margin-left: 5px; background-color: #DDD;">
					<span class="filter-tab"><?php _e( 'Match', 'envoyconnect' ); ?>: <select name="mod_results"><option value="AND"<?php selected( $mod_results, 'AND' ); ?>><?php _e( 'All', 'envoyconnect' ); ?></option><option value="OR"<?php selected( $mod_results, 'OR' ); ?>><?php _e( 'Any', 'envoyconnect' ); ?></option></select></span>
					<input type="submit" name="filter-search-go" value="Search" class="button-primary report-go" />
				</span>
			</div>
		</div>
		<?php
			$puq_title = '';
			if ( isset( $_GET['query'] ) ) {
				$envoyconnect_user_queries = get_option( '_envoyconnect_user_queries' );
				foreach( $envoyconnect_user_queries as $qk => $qv ) {
					if ( $qv['query'] == $_GET['query'] )
						$puq_title = $qv['title'];
				}
			}
		?>
		<input type="hidden" id="get_param" title="<?php echo $puq_title; ?>" />
		<input type="hidden" name="action[envoyconnect_filter_process]" value="" />
	</form>
</div>
<?php
}


function envoyconnect_add_search_row() {
	
	if ( isset( $_POST['data'] ) )
		envoyconnect_search_row( array( 'skey' => $_POST['data'] ) );	
	
	die();
	
}


function envoyconnect_search_row( $args = '' ) {
	
	$defaults = array( 
						'skey' => 0, // THE SEARCH KEY
						'sval' => array(), // THE VALUE OF THE SEARCH KEY IF PASSED
						'meta_key' => false, // THE FIELD'S META KEY
						'button' => 'add' // WHICH BUTTON TO SHOW
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
?>

	<li id="<?php echo $skey; ?>" class="query-block query_multilist tn-wrapper">
						
		<span class="query-selections">
			<span class="handle"></span>
			<span id="<?php echo $skey; ?>-parent" class="query-parent-holder" title="<?php echo $skey; ?>">
				<?php envoyconnect_user_data_select( array( 
														'class' => 'query-parent', 
														'meta_key' => $meta_key, 
														'value' => $sval 
												) ); ?>
			</span>
			
			<span id="<?php echo $skey; ?>-child" class="query-child-holder">
				<?php envoyconnect_search_form( array( 
													'fid' => $meta_key, 
													'key' => $skey, 
													'query' => $sval 
											) );
				?>
			</span>
		</span>
	
		<span class="query-buttons">
			<?php if ( 'add' != $button ) { $a_style = ' style="display:none;"'; } else { $a_style = ''; } ?>
			<?php if ( 1 == $skey ) { $s_style = ' style="display:none;"'; } else { $s_style = ''; } ?>
			<a id="<?php echo $skey; ?>-query-loader" class="query-loader" >&nbsp;</a>
			<a id="<?php echo $skey; ?>-query-sub" class="query-sub" rel="<?php echo $skey; ?>"<?php echo $s_style; ?>>&nbsp;</a>
			<a id="<?php echo $skey; ?>-query-add" class="query-add" rel="<?php echo $skey; ?>"<?php echo $a_style; ?>>&nbsp;</a>
		</span>
	
	</li>

<?php
}


function envoyconnect_user_data_select( $args = '' ) {
	$defaults = array( 
					'id' => '', 
					'class' => '', 
					'meta_key' => '', // THE FIELD'S META_KEY
					'value' => '', 
					'name' => false, 
					'address_unlock' => false, 
					's_context' => 'search',
					'ppum' => envoyconnect_get_user_metadata( array( 'group_break' => true ) ), 
					'wpr' => false, 
					'meta_only' => false, 
					'chosen' => ' chzn-select', 
					'multiple' => false, 
					'multi_meta_key' => false, 
				);
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	// SPECIAL LOCK FOR ADDRESSES
	// IF AN ADDRESS IS LOCKED, ONLY THE FIRST FIELD OPTION WILL BE DISPLAYED
	$address_lock = false;
	
	// FOR THE SELECT, USE THE $NAME VARIABLE TO DETERMINE WETHER OR NOT THE VALUES SHOULD BE SUBMITTED.
	?>
	<select class="<?php echo $class . $chosen; ?>"<?php if ( false !== $name ) { echo ' name="' . $name . '"'; } if ( false !== $multiple ) { echo ' multiple="multiple"'; } ?>>
		<option value=""><?php _e( 'Make a selection', 'envoyconnect' ); ?></option>
		<optgroup label="<?php _e( 'Profile Fields', 'envoyconnect' ); ?>">
		<?php
			foreach ( $ppum as $ukey => $uvalue ) {
				
				// RETRIEVE THE OPTION AND EVALUATE
				$user_meta = get_option( $uvalue );
				
				// RESTRICT TO ONLY USER TABLE FIELDS
				if ( false != $wpr && 'wpr' != $user_meta['source'] )
					continue;
					
				// RESTRICT TO ONLY USER TABLE FIELDS
				if ( false != $meta_only && 'wpr' == $user_meta['source'] )
					continue;
					
				// OTHERWISE, SET THE KEY
				$utkey = $user_meta['meta_key'];
			
				// IF THIS IS A GROUP, GET THE GROUP ELEMENTS AND LOG THEM
				if ( 'group' === $user_meta['options']['field_type'] || 'section' === $user_meta['options']['field_type'] ) {
				
					// SET THE VARS
					$prefix = stripslashes( $user_meta['name'] ) . ': ';
				
					// IF THIS IS AN ADDRESS FIELD, SHOW THE FIRST FIELD
					// PREVENT SUCCESSIVE LIKE FIELDS FROM DISPLAYING
					if ( 'address' === $user_meta['group_type'] && false == $address_lock ) {
					
						// IF WE'RE LOCKING SUCCESSIVE FIELDS, DO SO HERE
						if ( false == $address_unlock ) {
							
							$address_lock = true;
							$prefix = '';
							
							// IF WE'RE LOCKED, DEFAULT TO THE FIRST ADDRESS GROUP
							if ( '1' != strrchr( $user_meta['meta_key'], '_' ) ) {
								$ext = count( strrchr( $user_meta['meta_key'], '_' ) );
								$add_app = substr( $user_meta['meta_key'], 0, -$ext );
								$fid = $add_app . '1';
								$user_meta = get_option( 'envoyconnect_'.$fid );
							}
							
						}
					
					// IF ADDRESSES ARE LOCKED, SKIP SUCCESSIVE FIELDS
					} elseif ( 'address' === $user_meta['group_type'] && false != $address_lock ) {
					
						continue;
					
					}

					foreach ( $user_meta['options']['choices'] as $group_key => $group_value ) {
						
						// RETRIEVE THE OPTION AND EVALUATE
						if ( 'section' === $user_meta['options']['field_type'] ) {
							$prefix = '';
							$group_meta = get_option( 'envoyconnect_'.$group_value );
						} else {
							$group_meta = get_option( $group_value );
						}
						
						// IF WE'VE PASSED AN ARRAY AS A VALUE, CONVERT IT TO THE META KEY VALUE
						if ( is_array( $multi_meta_key ) ) {
							if ( in_array( $group_meta['meta_key'], $multi_meta_key ) )
								$meta_key = $group_meta['meta_key'];
						}
						
						if ( false != $group_meta['options']['reports'] )
							$fput[$group_meta['meta_key']] = '<option class="orderable" value="' . $group_meta['meta_key'] . '"' . selected( $meta_key, $group_meta['meta_key'] ) . '>' . $prefix . stripslashes( $group_meta['name'] ) . '</option>';
					}
					
				} else {
				
					if ( false != $user_meta['options']['reports'] ) {
						$fselected = '';
						// IF WE'VE PASSED AN ARRAY AS A VALUE, CONVERT IT TO THE META KEY VALUE
						if ( is_array( $multi_meta_key ) ) {
							if ( in_array( $utkey, $multi_meta_key ) )
								$meta_key = $utkey;
						}
						if ( $meta_key === $utkey ) { $fselected = ' selected'; }
						$fput[$user_meta['meta_key']] = '<option class="orderable" value="' . $user_meta['meta_key'] . '"' . $fselected . '>' . stripslashes( $user_meta['name'] ) . '</option>';
					}
				
				}
			}
			$fput = apply_filters( "envoyconnect_user_data_select_f_{$s_context}", $fput );
			echo implode( '', $fput );
		?>
		</optgroup>
		<?php
			// ADD OPTIONS BASED ON CONTEXT
			if ( false != $s_context )
				do_action( "envoyconnect_user_data_select_{$s_context}", $meta_key, $wpr );
		?>
	</select>
 <?php
}


/**
 * Display the search attributes for filtering.
 *
 * @since 3.0.0 ibid.
 *
 * @param mix $args Can be an array or URL query-style to override the following defaults...
 *
 * @return html The query for display.
 */

function envoyconnect_search_form( $args = '' ) {
	
	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	if ( isset( $_POST['fid'] ) ) {
		$defaults = array( 
						'nonce' => $_POST['envoyconnect_search_nonce'], 
						'fid' => $_POST['fid'], 
						'key' => $_POST['key'], 
						'query' => array(),
						'ajax' => true 
					);
	} else {
		$defaults = array( 
						'nonce' => false, 
						'fid' => false, 
						'key' => false, 
						'query' => array(),
						'order_by' => '' 
					);
					
	}
	
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );
	
	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	global $querystr;
	if ( !empty( $query ) ) {
		$querystr = $query;
	}
		
	// MINI CONSTANTS FOR TRANSLATION
	$_is = __( 'is', 'envoyconnect' );
	$_not = __( 'not', 'envoyconnect' );
	$_like = __( 'like', 'envoyconnect' );
	$_unlike = __( 'not like', 'envoyconnect' );
	$_null = __( 'empty', 'envoyconnect' );
	$_notnull = __( 'not empty', 'envoyconnect' );
	$_any = __( 'any', 'envoyconnect' );
	$_all = __( 'all', 'envoyconnect' );
	$_show = __( 'show', 'envoyconnect' );
					
	// DIFFERENTIATE BETWEEN META AND ACTION
	$user_meta = envoyconnect_get_option( $fid );
	$user_actions = envoyconnect_get_user_actions();
	foreach ( $user_actions as $arr ) {
		if ( $fid == $arr['type'] ) {
			$user_action = $fid;
			break;
		} 
	}
	
	// ACTION
	if ( isset( $user_action ) ) {
		?>
		<input class="i-type" type="hidden" name="search[<?php echo $key; ?>][type]" value="action" />
		<input class="i-field" type="hidden" name="search[<?php echo $key; ?>][field]" value="<?php echo $fid; ?>" />
		<span class="panel">
			<span class="op">
				<select name="search[<?php echo $key; ?>][operator]">
					<option value="min"<?php if ( isset( $query['operator'] ) && in_array( $query['operator'], array( 'is', 'min' ) ) ) { echo ' selected="selected"'; } ?>><?php _e( '>=', 'envoyconnect' ); ?></option>
					<option value="max"<?php if ( isset( $query['operator'] ) && in_array( $query['operator'], array( 'not', 'max' ) ) ) { echo ' selected="selected"'; } ?>><?php _e( '<=', 'envoyconnect' ); ?></option>
				</select>
				<input type="text" name="search[<?php echo $key; ?>][limit]" value="<?php if ( isset( $query['limit'] ) ) { echo $query['limit']; } else { echo '1'; } ?>" style="width: 25px;" />
			</span>
			<span class="op">
				<select name="search[<?php echo $key; ?>][coperator]" style="width: 55px;">
					<option value="like"<?php if ( isset( $query['coperator'] ) ) selected( $query['coperator'], 'like' ); ?>><?php echo $_is; ?></option>
					<option value="notlike"<?php if ( isset( $query['coperator'] ) ) selected( $query['coperator'], 'notlike' ); ?>><?php echo $_not; ?></option>
				</select>
				<input type="text" placeholder="<?php _e( 'Title or Body', 'envoyconnect' ); ?>" name="search[<?php echo $key; ?>][title]" value="<?php if ( isset( $query['title'] ) ) { echo $query['title']; } ?>" style="width: 200px;" />
			</span>
		
			<select class="date-toggle" id="<?php echo $key; ?>_dtoggle" name="search[<?php echo $key; ?>][date][toggle]">
				<option value=""><?php _e( 'Date Options', 'envoyconnect' ); ?></option>
				<option value="<?php echo $key; ?>_ran"<?php if ( isset( $query['date']['toggle'] ) ) selected( $query['date']['toggle'], $key .'_ran' ); ?>><?php _e( 'Date Range', 'envoyconnect' ); ?></option>
				<option value="<?php echo $key; ?>_rel"<?php if ( isset( $query['date']['toggle'] ) ) selected( $query['date']['toggle'], $key .'_rel' ); ?>><?php _e( 'Relative Date', 'envoyconnect' ); ?></option>
			</select>
			<span id="<?php echo $key; ?>_ran" class="date_field"<?php if ( isset( $query['date']['toggle'] ) && $key.'_ran' == $query['date']['toggle'] ) echo ' style="display: inline-block"'; ?>>
				<input id="<?php echo $key; ?>_start" class="envoyconnect-date" type="text" placeholder="Start" name="search[<?php echo $key; ?>][date][range][start]" value="<?php if ( isset( $query['date']['range']['start'] ) ) { echo $query['date']['range']['start']; } ?>" style="width: 80px;" /> <input id="<?php echo $key; ?>_end" class="envoyconnect-date" type="text"  placeholder="End" name="search[<?php echo $key; ?>][date][range][end]" value="<?php if ( isset( $query['date']['range']['end'] ) ) { echo $query['date']['range']['end']; } ?>" style="width: 80px;" />
			</span>
			<span id="<?php echo $key; ?>_rel" class="date_field"<?php if ( isset( $query['date']['toggle'] ) && $key.'_rel' == $query['date']['toggle'] ) echo ' style="display: inline-block"'; ?>>
				<select name="search[<?php echo $key; ?>][date][rel][operator]">
					<option value="min"<?php if ( isset( $query['date']['rel']['operator'] ) && in_array( $query['date']['rel']['operator'], array( 'min' ) ) ) { echo ' selected="selected"'; } else if ( !isset( $query['date']['rel']['operator'] ) || empty( $query['date']['rel']['operator'] ) ) { echo ' selected="selected"'; } ?>><?php _e( '<=', 'envoyconnect' ); ?></option>
					<option value="is"<?php if ( isset( $query['date']['rel']['operator'] ) && in_array( $query['date']['rel']['operator'], array( 'is' ) ) ) { echo ' selected="selected"'; } ?>><?php _e( '=', 'envoyconnect' ); ?></option>
					<option value="max"<?php if ( isset( $query['date']['rel']['operator'] ) && in_array( $query['date']['rel']['operator'], array( 'max' ) ) ) { echo ' selected="selected"'; } ?>><?php _e( '>=', 'envoyconnect' ); ?></option>
				</select>
				<input class="input-micro" type="text" placeholder="units" name="search[<?php echo $key; ?>][date][rel][units]" value="<?php if ( isset( $query['date']['rel']['units'] ) ) { echo $query['date']['rel']['units']; } ?>" style="width: 37px;" />
				<select name="search[<?php echo $key; ?>][date][rel][context]">
					<option value=""><?php _e( 'Time', 'envoyconnect' ); ?></option>
					<option value="days"<?php if ( isset( $query['date']['rel']['context'] ) ) selected( 'days', $query['date']['rel']['context'] ); ?>><?php _e( 'days', 'envoyconnect' ); ?></option>
					<option value="weeks"<?php if ( isset( $query['date']['rel']['context'] ) ) selected( 'weeks', $query['date']['rel']['context'] ); ?>><?php _e( 'weeks', 'envoyconnect' ); ?></option>
					<option value="months"<?php if ( isset( $query['date']['rel']['context'] ) ) selected( 'months', $query['date']['rel']['context'] ); ?>><?php _e( 'months', 'envoyconnect' ); ?></option>
					<option value="years"<?php if ( isset( $query['date']['rel']['context'] ) ) selected( 'years', $query['date']['rel']['context'] ); ?>><?php _e( 'years', 'envoyconnect' ); ?></option>
				</select> 
				<select name="search[<?php echo $key; ?>][date][rel][frame]">
					<option value=""><?php _e( 'Frame', 'envoyconnect' ); ?></option>
					<option value="before"<?php if ( isset( $query['date']['rel']['frame'] ) ) selected( 'before', $query['date']['rel']['frame'] ); ?>><?php _e( 'before', 'envoyconnect' ); ?></option>
					<option value="after"<?php if ( isset( $query['date']['rel']['frame'] ) ) selected( 'after', $query['date']['rel']['frame'] ); ?>><?php _e( 'after', 'envoyconnect' ); ?></option>
				</select> 
				<select class="timeframe-select" title="<?php echo $key; ?>_timeframe-rel-abs" name="search[<?php echo $key; ?>][date][rel][pin]">
					<option value=""><?php _e( 'When', 'envoyconnect' ); ?></option>
					<option value="now"<?php if ( isset( $query['date']['rel']['pin'] ) ) selected( 'now', $query['date']['rel']['pin'] ); ?>><?php _e( 'now', 'envoyconnect' ); ?></option>
					<option value="date"<?php if ( isset( $query['date']['rel']['pin'] ) ) selected( 'date', $query['date']['rel']['pin'] ); ?>><?php _e( 'date', 'envoyconnect' ); ?></option>
				</select>
				<input id="<?php echo $key; ?>_timeframe-rel-abs" class="envoyconnect-date s_date_field" type="text" name="search[<?php echo $key; ?>][date][rel][pindate]" value="<?php if ( isset( $query['date']['rel']['pindate'] ) ) { echo $query['date']['rel']['pindate']; } ?>" style="width: 70px;" />
			</span>
		</span>
		<span class="sub-panel"><?php do_action( 'envoyconnect_action_search_meta', $fid, $key, $query ); ?></span>
		<?php

	// META
	} else if ( !empty( $user_meta ) ) {
		?>
		<input class="i-type"  type="hidden" name="search[<?php echo $key; ?>][type]" value="user" />
		<?php
			// SPECIAL EXCEPTION FOR ADDRESSES
			if ( isset( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' ) ) {
				$ext = count( strrchr( $fid, '_' ) );
				$add_app = substr( $fid, 0, -$ext );
				$fid = $add_app . '1';
				//$fid = $add_app;
			}
		?>
		<input class="i-field"  type="hidden" name="search[<?php echo $key; ?>][field]" value="<?php echo $fid; ?>" />
		<span class="multi-op">
			<select name="search[<?php echo $key; ?>][operator]" class="chzn-select" style="width: 100px;">
			<?php
			// SHOW THE MODIFIER
			if ( 'multitext' == $user_meta['options']['field_type'] ) {
			} else {
			?>	
				<option value="is"<?php if ( isset( $query['operator'] ) ) selected( $query['operator'], 'is' ); ?>><?php echo $_is; ?></option>
				<option value="not"<?php if ( isset( $query['operator'] ) ) selected( $query['operator'], 'not' ); ?>><?php echo $_not; ?></option>
	
			<?php
			}
			if ( 
				'taxonomy' == $user_meta['options']['field_type'] || 
				'select' == $user_meta['options']['field_type'] || 
				'radio' == $user_meta['options']['field_type'] || 
				'checkbox' == $user_meta['options']['field_type'] || 
				'role' == $user_meta['meta_key']
			) { } else {
			?>		
				<option value="like"<?php if ( isset( $query['operator'] ) ) selected( $query['operator'], 'like' ); ?>><?php echo $_like; ?></option>
				<option value="notlike"<?php if ( isset( $query['operator'] ) ) selected( $query['operator'], 'notlike' ); ?>><?php echo $_unlike; ?></option>
			<?php
			}
			?>
				<option value=""<?php if ( isset( $query['operator'] ) ) selected( $query['operator'], '' ); ?>><?php echo $_show; ?></option>
			</select>
		</span>
		
		<?php
		
		if ( 'taxonomy' == $user_meta['options']['field_type'] ) { 
		//  || isset( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' )
		?>
			<span class="multi-op">
				<select name="search[<?php echo $key; ?>][sub_operator]">
					<option value="any"<?php if ( isset( $query['sub_operator'] ) ) selected( $query['sub_operator'], 'any' ); ?>><?php echo $_any; ?></option>
					<option value="all"<?php if ( isset( $query['sub_operator'] ) ) selected( $query['sub_operator'], 'all' ); ?>><?php echo $_all; ?></option>
				</select>
			</span>
		<?php
		}
		/* IT'S A TAXONOMY!
		if ( 'taxonomy1' == $user_meta['options']['field_type'] ) {
			$args = array( 'name' => $user_meta['meta_key'] );
			$taxonomies = get_taxonomies( $args, 'objects' );
			foreach ( $taxonomies as $taxonomy ) {
				
				// GIVE THIS A PANEL TO HOLD IT
				?>
					<span id="panel_<?php echo $key; ?>" class="multi-panel">
				<?php
				
				// CHECK FOR WHAT TO DO WITH THE CHILDREN
				if ( false == $user_meta['options']['children'] ) {
					$children = '&parent=0';
				} else {
					$children = '';
				}
				
				$tax_{$taxonomy->name} = get_terms( $taxonomy->name, 'hide_empty=0&orderby=slug'.$children );
				foreach ( $tax_{$taxonomy->name} as $term ) {
					echo '<span class="float-checkbox"><input type="checkbox" class="select-'.$taxonomy->name.'" name="search['.$taxonomy->name.'][query][]"  value="'.$term->term_id.'"';
					if ( !empty( $querystr['query'] ) ) { if ( in_array_r( $term->term_id, $querystr['query'] ) ) { echo ' checked'; } }
					echo '/> '.$term->name.'</span>';
				}
				
				?>
					<p style="clear:both;"></p>
					</span>
				<?php
				
			}
								
		// IT'S A META!
		} else {
		*/	
			// IF THIS IS AN ADDRESS, LET'S PRESET SOME OPTIONS
			if ( isset( $user_meta['group'] ) && false !== strpos( $user_meta['group'], 'address' ) ) {
			?>
				<span class="multi-op">
					<select data-placeholder="All" name="search[<?php echo $key; ?>][post_ops][]" multiple="multiple" class="chzn-select" style="width: 100px;">
			<?php 
				$post_ops_arr = apply_filters( 'envoyconnect_post_ops_arr', array( 'pp_primary' => __( 'Primary', 'envoyconnect' ) ) );
				foreach ( $post_ops_arr as $pops => $pop ) {
					echo '<option value="'.$pops.'"';
					if ( isset( $query['post_ops'] ) && is_array( $query['post_ops'] ) && in_array( $pops, $query['post_ops'] ) ) echo ' selected';
					echo '>'.$pop.'</option>';
				}
			?>
					</select>
				</span>
			<?php
			}
			?>

			<span class="panel">
				<?php 
					$args = array();
					$args['meta'] = $user_meta;
					$args['swap_name'] = array( $key );
					if ( isset( $query['query'] ) )
						$args['query'] = $query['query'];
					envoyconnect_search_field_manipulation( $args ); 
				?>
			</span>
			
	<?php
		//}
				
	}
	if ( isset( $ajax ) ) {
		die();
	} else {
	}
	
}


function envoyconnect_search_field_manipulation( $args = null ) {
	
	$defaults = array( 
						'meta' => false, 
						'type' => 'search', 
						'swap_name' => array(), 
						'action' => 'search', 
						'query' => false
					);
						
	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );
	
	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );
	
	if ( false == $meta )
		return false;
		
	$args = array( 
					'meta' => $meta, 
					'action' => $action, 
					'type' => $type, 
					'swap_name' => $swap_name, 
					'swap_id' => $meta['meta_key'] . '_' . $swap_name[0], 
					'post_val' => false 
	);
			
	if ( 'multitext' == $meta['options']['field_type'] || 'text' == $meta['options']['field_type'] ) {
		$args['meta']['options']['field_type'] = 'text';
		if ( !empty( $query ) && is_array( $query ) && 'user_registered' != $meta['meta_key']) {
			$args['post_val'] = $query[0];
		} else {
			$args['post_val'] = $query;
		}
	}
	
	if ( 'radio' == $meta['options']['field_type'] ) {
		$args['meta']['options']['field_type'] = 'select';
		if ( !empty( $query ) )
			$args['post_val'] = $query;
	}
	if ( 'display_name' == $meta['meta_key'] ) {
		$args['meta']['options']['field_type'] = 'text';
		if ( !empty( $query ) ) 
			$args['post_val'] = $query;
	}
	if ( 'textarea' == $meta['options']['field_type'] ) {
		$args['meta']['options']['field_type'] = 'text';
		if ( !empty( $query ) ) 
			$args['post_val'] = $query;
	}
	
	if ( 
			'taxonomy' == $meta['options']['field_type'] || 
			'select' == $meta['options']['field_type']  || 
			'multiselect' == $meta['options']['field_type'] || 
			'checkbox' == $meta['options']['field_type']|| 
			'date' == $meta['options']['field_type'] 
	)
		$args['post_val'] = $query;
	
	envoyconnect_get_field( $args );
}


?>
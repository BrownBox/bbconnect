<?php

/**
 * A recursive function for checking values in an array
 *
 * @since 1.0.0
 *
 * @param str $needle Required. The value to search for.
 * @param arr $haystack Required. The array to search in.
 * @param str $retval Optional. Whether or not to return the array key.
 *
 * @return boolean|str True/False by default. If $retval is true, return the array key.
 */
function in_array_r($needle, $haystack, $retval = false) {
    foreach ($haystack as $key => $value) {
        if ($value == $needle || (is_array($value) && in_array_r($needle, $value))) {
            if ($retval === false) {
            	return true;
            } else {
            	return $key;
            }
        }
    }
    return false;
}

/*
function isset_r($needle, $haystack, $retval = false) {
    foreach ($haystack as $key => $value) {
        if ($key === $needle || is_array($value) && $value == isset_r($needle, $value, true) ) {
            if ($retval === false) {
            	return true;
            } else {
            	return $value;
            }
        }
    }
    return false;
}
*/
function isset_r($needle, $haystack, $retval = false) {
    $return = false;
    foreach ($haystack as $key => $value) {
        if ($key === $needle ) {
        	$return = $value;
        } else if ( is_array($value) && $return == isset_r($needle, $value, true) ) {
        	$return = $return;
        }

        if ($retval === false) {
        	return true;
        } else {
        	return $return;
        }

    }
    return false;
}


/**
 * Generate random strings. Primarily for usernames but can be used for any instance where a random string is needed.
 *
 * @since 1.0.0
 *
 * @param This takes no parameters.
 *
 * @return str Returns a random username.
 */

function bbconnect_random( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'name' => false,
					'length' => 7,
					'underscores' => 0,
					'compact' => false,
					'bytime' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

    $p = '';

	// CONCATENATE THE FIRST NAME
    if ( false != $name ) {
    	$p .= sanitize_title_with_underscores( $name );

    	if ( false == $compact ) {
    		$p .=  '_';
    	}
    }

    // LOOP TO BUILD THE RANDOM STRING
    for ( $i = 0; $i < $length; $i++ ) {

        $c = mt_rand( 1, 7 );

        switch ($c) {

            case ($c<=2):
                $p .= mt_rand(0,9); // NUMBER
            	break;

            case ($c<=4):
                $p .= chr(mt_rand(65,90)); // UPPERCASE LETTER
            	break;

            case ($c<=6):
                $p .= chr(mt_rand(97,122)); // LOWERCASE LETTER
            	break;

            case 7:
            	$len = strlen($p);
                if ( $underscores > 0 && $len > 0 && $len < ( $length - 1 ) && $p[$len-1] != "_" ) {
                    $p .= "_";
                    $underscores--;
                } else {
                    $i--;
    				continue;
                }
            	break;
        }
    }

    if ( false == $bytime )	{

    	if ( false == $compact ) {
    		$p .=  '_';
    	}

    	$p .= time();

    }

    return $p;

}



function bbconnect_admin_fixes() {
	//wp_tiny_mce( true );
	// FIX IE 8 ISSUES
	?>
	<!--[if lt IE 9]>
	<script>
		jQuery.noConflict();
		var el;
		jQuery("select")
		.each(function() {
	       el = jQuery(this);
	       el.data("origWidth", el.outerWidth()) // IE 8 can haz padding
	     })
	     .mouseenter(function(){
	       jQuery(this).css("width", "auto");
	     })
	     .bind("blur change", function(){
	       el = jQuery(this);
	       el.css("width", el.data("origWidth"));
	     });
	</script>
	<![endif]-->
	<?php
}



// REMOVE THE WORDPRESS ADMIN MENU FOR NOTES AND SUCH
function bbconnect_minified_admin() {
	// FIND OUT WHAT PAGE THEY'RE REQUESTING
	global $pagenow;

	// IF IT'S AN ADMIN REQUEST
	if ( is_admin() ) {

		// IF THEY'RE TRYING TO ACCESS THE ORIGINAL PROFILE PAGE, REDIRECT THEM
		if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' || 'users.php' == $pagenow ) {
	    	add_action( 'admin_head', 'bbconnect_minify_admin' );
	    }

	}

}

function bbconnect_minify_admin() {
	global $post, $pagenow;

	$minify = false;
	if ( $pagenow == 'post.php' || $pagenow == 'post-new.php' ) {
		$bbconnect_user_actions = bbconnect_get_user_actions();
		foreach ( $bbconnect_user_actions as $key => $value ) {
			$action_types[] = $value['type'];
		}
		if ( in_array( $post->post_type, $action_types ) ) {
			$minify = true;
		}
	} else if ( 'users.php' == $pagenow && isset( $_GET['page'] ) && 'bbconnect_modal_action' == $_GET['page'] ) {
		$minify = true;
	}



	if ( $minify ) {
		//if ( isset( $_GET['uid'] ) || isset( $_GET['pid'] ) || isset( $_POST['bbconnect_iframe'] ) ) {
		?>
		<script type="text/javascript">
			var iframe = (window.location != window.parent.location) ? true : false;
			if ( iframe ) {
				jQuery.noConflict();
				jQuery('head').append('<style type="text/css" media="screen">html.wp-toolbar { padding-top: 0; } #adminmenuback, #adminmenuwrap, #wphead, #wpfooter, #footer, #wpadminbar, .update-nag { display: none; } #wpcontent, #wpfooter { margin-left: 10px !important; }</style>');
			}
		</script>
		<?php
		//}

	}
}

function is_assoc( $array ) {
  return (bool)count( array_filter( array_keys( $array ), 'is_string' ) );
}


/*
if (!wp_next_scheduled('my_daily_function_hook')) {
	wp_schedule_event( time(), 'daily', 'my_daily_function_hook' );
}
add_action( 'my_daily_function_hook', 'my_daily_function' );


function my_daily_function() {

}
*/

/**
 * Setup query variables for the webhook listener.
 *
 * @since 1.0.0
 */
function bbconnect_query_vars( $qvars ) {
	$bbconnect_okget = apply_filters( 'bbconnect_okget', array( 'bbconnect', 'bbc_key', 'bbc_ref', 'bbc_action' ) );
	foreach ( $bbconnect_okget as $okgets )
		array_push( $qvars, $okgets );

	return $qvars;
}


/**
 * Basic rewrite rule that says, if we have a subset of BB Connect, apply our matching and set bbconnect as a key.
 * If this is a top-level BB Connect request, let the parse request take over and set bbconnect as the value.
 *
 * @since 1.0.0
 */
function bbconnect_rewrite_rules( $wp_rewrite ) {
	$new_rules = array(
						'bbconnect/([^/]*)?$' => 'index.php?bbconnect=$matches[1]',
						'bbconnect/([^/]*)/([^/]*)/([^/]*)?$' => 'index.php?bbconnect=$matches[1]&bbc_key=$matches[2]&bbc_ref=$matches[3]',
	);
	$wp_rewrite->rules = $new_rules + $wp_rewrite->rules;
}

function bbconnect_flush_rules(){

	// GET THE STORED RULES
	$rules = get_option( 'rewrite_rules' );

	if ( !isset( $rules['bbconnect/([^/]*)/([^/]*)/([^/]*)?$'] ) || !isset( $rules['bbconnect/([^/]*)?$'] ) ) {
		global $wp_rewrite;
	   	$wp_rewrite->flush_rules();
	}

}


/**
 * Calls the various functions to deal with webhook events.
 *
 * @since 1.0.0
 */
function bbconnect_parse_request( $wp ) {

	// DO WE HAVE BBCONNECT AS AN EXPLICIT VALUE?
	$bbc_top = in_array_r( 'bbconnect', $wp->query_vars, true );

	// DO WE HAVE BBCONNECT AS AN INVISIBLE REFERENCE?
	if ( isset( $wp->query_vars['bbconnectref'] ) ) {
		if ( 'true' == get_option( 'bbconnectpanels_embed' ) && false == $bbc_top ) {
			$bbc_top = 'pagename';
			$wp->query_vars['pagename'] = 'bbconnect';
		}
	}

	// DO WE HAVE BBCONNECT AS A PROXY FOR WP RESETS?
	if ( isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
		if ( 'true' == get_option( 'bbconnectpanels_embed' ) && false == $bbc_top ) {
			$bbc_top = 'pagename';
			$wp->query_vars['pagename'] = 'bbconnect';
		}
		$wp->query_vars['bbc_wp_key'] = $_GET['key'];
		$wp->query_vars['bbc_wp_login'] = $_GET['login'];
	}

    // HERE, WE ARE LOOKING FOR A SUBROUTINE OF BBCONNECT
    if ( array_key_exists( 'bbconnect', $wp->query_vars ) ) {
		switch( $wp->query_vars['bbconnect'] ) {

			// EXTEND THE HOOK SYSTEM
			default :
				do_action( 'bbconnect_parse_switch', $wp );
			break;

		}

	// HERE, WE ARE DEALING WITH BBCONNECT TOP-LEVEL FUNCTIONALITY
	// PROCESS EMBEDS AND REDIRECTS
	} else if ( false != $bbc_top ) {
		//if ( 'pagename' == $bbc_top || 'name' == $bbc_top ) {
			//unset( $wp->query_vars['pagename'] );
			add_filter( 'wp_title', 'bbconnect_page_title' );
			do_action( 'bbconnect_system_page', $wp );
		//}
	}

}


function bbconnect_page_title() {
	global $wp_query;
	return sprintf( __( '%1$s', 'bbconnect' ), ucfirst( $wp_query->query_vars['rel'] ) );
}

function bbconnect_error_log( $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
					'date' => current_time( 'timestamp' ),
					'app' => 'bbconnect',
					'type' => 'user',
					'id' => false,
					'error' => false,
				);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	$error_log = get_option( '_bbconnect_error_log' );
	if ( !is_array( $error_log ) )
		$error_log = array();

	array_push( $error_log, $args );

	return update_option( '_bbconnect_error_log', $error_log );

}

function bbconnect_hello_dolly( $id, $args = null ) {

	// SET THE DEFAULTS TO BE OVERRIDDEN AS DESIRED
	$defaults = array(
						'post_data' => array(),
						'meta_data' => array(),
	);

	// PARSE THE INCOMING ARGS
	$args = wp_parse_args( $args, $defaults );

	// EXTRACT THE VARIABLES
	extract( $args, EXTR_SKIP );

	// CLONE THE CURRENT POST
	$current_post = get_post( $id, ARRAY_A );
	$_cloned_post = array();
	$_cloned_vars = array(
							'menu_order',
							'comment_status',
							'ping_status',
							'post_author',
							'post_content',
							'post_date',
							'post_date_gmt',
							'post_excerpt',
							'post_parent',
							'post_password',
							'post_status',
							'post_title',
							'post_type',
	);

	// START THE CLONER
	foreach ( $current_post as $k => $v ) {
		if ( in_array( $k, $_cloned_vars ) ) {
			if ( isset( $post_data[$k] ) ) {
				$_cloned_post[$k] = $post_data[$k];
			} else {
				$_cloned_post[$k] = $v;
			}
		}
	}

	// CLONE IT!
	$_hello_dolly = wp_insert_post( $_cloned_post );

	// CLONE THE METADATA TOO... MWAAHAAHAA...
	$current_meta = get_post_custom( $id );
	foreach ( $current_meta as $k => $v ) {
		if ( isset( $meta_data[$k] ) ) {
			$v = $meta_data[$k];
		} else {
			$v = maybe_unserialize( $v[0] );
		}
		update_post_meta( $_hello_dolly, $k, $v );
	}

	return $_hello_dolly;

}
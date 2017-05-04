jQuery.noConflict();

// AJAX TEST FOR ERRORS
function bbconnectpanels_errors() {

	// RESET THE ERRORS
	jQuery('*').removeClass('halt');
	jQuery('.errors').remove();
	
	// RUN A FRESH CHECK
	jQuery('#bbconnect .required').siblings().find(':input').each(function(){
	
		if ( jQuery(this).closest('li.meta-item').is(':visible') ) {
		
	    	if ( '' == jQuery(this).val() || '0' == jQuery(this).val() || 'false' == jQuery(this).val()  ) {
	    		// SPECIAL EXEMPTION FOR CHOSEN
	    		if ( jQuery(this).closest('li.meta-item').hasClass('chzn-req') ) {
	    		} else {
	    			jQuery(this).closest('li.meta-item').addClass('halt');
	    		}
	    	}
	    	
	    	if ( jQuery(this).is(':radio') ) {
	    		if ( jQuery(this).is(':checked') ) {
	    			jQuery(this).closest('li.meta-item').removeClass('halt');
	    			return false;
	    		} else if ( !jQuery(this).closest('li.meta-item').hasClass('halt') ) {
	    			jQuery(this).closest('li.meta-item').addClass('halt');
	    		}
	    	}
	    		    	
	    	// SPECIAL CASE FOR CHOSEN
	    	if ( jQuery(this).hasClass('chzn-select') ) {
	    		
	    		// RESET THE CONTEXT
	    		jQuery(this).closest('li.meta-item').removeClass('halt').addClass('chzn-req');
	    		//mychzn = jQuery(this).attr('id');
	    		//jQuery(this).closest('li.meta-item').addClass(mychzn);
	    		jQuery(this).siblings().find('.chzn-results').each(function(){
	    			if ( jQuery('li',this).hasClass('result-selected') ) {
	    				jQuery(this).closest('li.meta-item').removeClass('halt');
	    				return false;
	    			}
	    			jQuery(this).closest('li.meta-item').addClass('halt');
	    		});
	    		
	    	}
	    	
	    }
		
	});
	
	jQuery('#bbconnect .required').siblings().find(':checkbox').each(function(){
		if ( jQuery(this).is(':checked') ) {
			jQuery(this).closest('li.meta-item').removeClass('halt');
			return false;
		}
		jQuery(this).closest('li.meta-item').addClass('halt');
	});
	
	// CHECK EMAIL ADDRESSES
	if ( jQuery('#email').length ) {
		var email = jQuery('#email').val();
		var etest = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
		if ( false == etest.test(email) ) {
			jQuery('#email').closest('li.meta-item').addClass('halt');
		}
	}
	
	if ( jQuery('.halt').length != 0 ) {
		// TAKE ME HOME, SCOTTY!
		if ( !jQuery('#errors').length ) {
			jQuery('#bbconnect').prepend('<div class="errors">'+bbconnectpanelsAjax.errMsg+'</div>');
		}
		jQuery('html, body').animate({ scrollTop: jQuery('#bbconnect').offset().top }, 'slow');
		  return true;
	}
	
	return false;

}

// AJAX FORM SUBMISSION
function bbconnectpanels_submit() { 

    // DISABLE THE FORM & THE BUTTON TO PREVENT MULTIPLE SUBMISSIONS
    jQuery(this).closest('form.bbconnectpanels-form').submit( function() { return false; } );
    if ( !jQuery(this).hasClass('sub-select') ) {
    	jQuery(this).attr('disabled','disabled');
    }
    
    // CHECK FOR ERRORS
    var errors = bbconnectpanels_errors();
    
    // IF THERE ARE ERRORS, RE-INSTATE THE BUTTON AND BAIL
    if ( errors ) {
    	jQuery(this).removeAttr('disabled');
    	return false;
    }
        
    // CDATA TO AVOID VALIDATION ERRORS
    //<![CDATA[
    var str = jQuery(this).closest('.bbconnectpanels-form').serialize();
    // ]]>
    
    // EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
    jQuery('#bbconnect').empty().html('<div style="padding:100px 0;"><img src="'+bbconnectpanelsAjax.ajaxload+'" /></div>');
    
    // SUBMIT THE FORM DATA
    jQuery.post( 
    	
    	bbconnectpanelsAjax.ajaxurl, { 
    		action : 'bbconnectpanels', 
    		data : str, 
    		bbconnectpanels_nonce : bbconnectpanelsAjax.bbconnectpanels_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#bbconnect').html(response);
			bbconnect_bind_events();
			if ( jQuery('#bbconnect form .required').length ) {
				jQuery('#bbconnect form').prepend('<div class="bbconnectpanels-req-notify">'+bbconnectpanelsAjax.reqMsg+'</div>');
			}
			var pheight = jQuery('#bbconnectpanel').height();
			var wheight = jQuery(window).height();
			if ( pheight > wheight ) {
				jQuery('#close-top').prependTo('#bbconnect').fadeIn('slow');
			}
        }
    );
    
    // TAKE ME HOME, SCOTTY!
    if ( !bbconnectpanelsAjax.panel_embed ) {
    	jQuery('html, body').animate({ scrollTop: jQuery('.bbconnectf-frame').offset().top }, 'slow');
    }
    return false;
     
	    
}


// CHECK EMAIL FOR AJAX LOGIN FORM
function check_email( email ) { 
  var pattern = /^([a-zA-Z0-9_\.\-\+])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
  return pattern.test(email);
}


// AJAX FORM TOGGLE
function bbconnectpanels_toggle(ref,title,action) {
	
	// GATHER THE VARIABLES FROM THE TARGETING ANCHOr
	if (!ref) ref = '';
	if (!title) title = '';
	if (!action) action = '';
	
	// IF THE PANEL IS OPEN AND THE TARGETING ANCHOR AND PANELS MATCH, THE INTENTION IS TO CLOSE THE PANEL
	if ( jQuery('#bbconnectpanel').is(':visible') && jQuery('#bbconnectpanel').hasClass(title) ) {
		
		/* RESET ABSOLUTELY POSITIONED ITEMS
		jQuery('*').each(function() {
			if ( jQuery(this).css('position') == 'absolute' && jQuery(this).hasClass('bbconnect-temp') ) {
				var bbconnect = jQuery('#bbconnect').height();
				var paddingTop = jQuery(this).css('padding-top');
				jQuery(this).hide();
				jQuery(this).css('padding-top','-='+bbconnect);
				jQuery(this).removeClass('bbconnect-temp');
				jQuery(this).fadeIn('fast');
			}
		});
		*/
		if ( !bbconnectpanelsAjax.panel_embed ) {
			jQuery('#bbconnect').empty();
			jQuery('#bbconnectpanel').removeClass(title);
			jQuery('#bbconnectpanel').slideToggle('fast');
			jQuery('#'+title+'-tab').toggleClass('exposed');
		}
		
	// IF THEY DON'T MATCH, THE INTENTION IS TO SELECT ANOTHER ACTION	
	} else {
	
		if ( jQuery('#bbconnectpanel').is(':visible') && !jQuery('#bbconnectpanel').hasClass(title) ){
			
			var bbconnectCurrent = jQuery('#bbconnect').height();
		
			// CLOSE THE PANEL AND GET THE PANEL'S REFERENCE TO THE PREVIOUS TARGETING ANCHOR
			var tid = jQuery('#bbconnectpanel').attr('class');
			jQuery('#bbconnectpanel').removeClass();//.slideToggle('fast')
			
			// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
			jQuery('#bbconnect').empty().html('<div style="padding:100px 0;"><img src="'+bbconnectpanelsAjax.ajaxload+'" /></div>');
			
			// OPEN THE PANEL AND SET THE REFERENCE TO THE CURRENT TARGETING ANCHOR
			jQuery('#bbconnectpanel').addClass(title);//.slideToggle('fast')
			
			// TOGGLE THE STATES OF THE RELATED AND REVERSED
			jQuery('#'+tid+'-tab').toggleClass('exposed');
			jQuery('#'+title+'-tab').toggleClass('exposed');
			
		} else {
			/*
			jQuery('*').each(function() {
				if ( jQuery(this).css('position') == 'absolute' && jQuery(this).is(':visible') ) {
					jQuery(this).addClass('bbconnect-temp');
					jQuery(this).fadeOut('fast');
				}
			});
			*/
			// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
			jQuery('#bbconnect').empty().html('<div style="padding:100px 0;"><img src="'+bbconnectpanelsAjax.ajaxload+'" /></div>');
		
			// OPEN THE PANEL AND SET THE REFERENCE TO THE CURRENT TARGETING ANCHOR
			if ( 'close' == action ) {
				jQuery('#bbconnectpanel').addClass(title)
			} else {
				jQuery('#bbconnectpanel').addClass(title).slideToggle('fast');
			}
			
			// TOGGLE THE STATES OF THE RELATED
			// AND EXPOSE THEM IF THEY ARE HIDDEN
			jQuery('#'+title+'-tab').toggleClass('exposed');
			if ( jQuery('#'+title+'-tab').not(':visible') ) {
				jQuery('#'+title+'-tab').show();
			}
			
		}
			
		// SUBMIT THE CONTEXTUAL ELEMENTS TO RETRIEVE THE APPROPRIATE FORM
		jQuery.post( 
			
			bbconnectpanelsAjax.ajaxurl, { 
				action : 'bbconnectpanels', 
				rel : ref, 
				bbconnectpanels_nonce : bbconnectpanelsAjax.bbconnectpanels_nonce 
			},
		    function( response ) {
		        // DISPLAY THE RESPONSE
		        jQuery('#bbconnect').html(response);
		        /* MOVE ABSOLUTELY POSITIONED ITEMS
		        jQuery('*').each(function() {
		        	if ( jQuery(this).css('position') == 'absolute' && jQuery(this).hasClass('bbconnect-temp') ) {
		        		var bbconnect = jQuery('#bbconnect').height();
		        		if ( bbconnectCurrent ) {
			        		if ( bbconnectCurrent < bbconnect ) {
			        			jQuery(this).css('padding-top','+='+bbconnect);
			        		} else if ( bbconnectCurrent > bbconnect ) {
			        			jQuery(this).css('padding-top','+='-bbconnect);
			        		}
			        	} else {
		        			jQuery(this).css('padding-top','+='+bbconnect);
		        		}
		        		jQuery(this).fadeIn('fast');
		        	}
		        });
		        */
		        jQuery('.bbconnect-view').on('click',bbconnect_action_view);
		        var pheight = jQuery('#bbconnectpanel').height();
		        var wheight = jQuery(window).height();
		        //SET SUPPORT FOR CHOSEN
		        bbconnect_bind_events();
		        if ( jQuery('#bbconnect form .required').length ) {
		        	jQuery('#bbconnect form').prepend('<div class="bbconnectpanels-req-notify">'+bbconnectpanelsAjax.reqMsg+'</div>');
		        }
		        if ( pheight > wheight ) {
		        	jQuery('#close-top').prependTo('#bbconnect').fadeIn('slow');
		        }
		    }
		);
		
	}
	
	// TAKE ME HOME, SCOTTY!
	if ( 'close' == action ) {
		return false;
	} else {
		if ( !bbconnectpanelsAjax.panel_embed ) {
			jQuery('html, body').animate({ scrollTop: jQuery('.'+title).offset().top }, 'slow');
		}
	  	return false;
	}
}


// TOGGLE THE PREFERENCE PANE
function r_toggle() {
	var raction = jQuery(this).attr('title');
	jQuery(this).parents('.login-action').slideToggle('fast');
	jQuery('#'+raction).slideToggle('fast');
	jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
	  return false;
}

function required_toggle() {
	jQuery(this).css({'background-image': 'none'}).removeClass('halt');
	if ( jQuery('.halt').length != 0 ) {
		jQuery('#errors').hide();
	}
}


/* MAIN LOGOUT FUNCTION
function bbconnectpanels_logout() { 
	
	var logout = true;
	 
	jQuery.post( 
    	bbconnectAjax.ajaxurl, 
    	{ action : 'bbconnect_login', post_logout : logout, bbconnect_login_nonce: bbconnectAjax.bbconnect_login_nonce },
        function( response ) {
            jQuery(".login-action").hide();
            jQuery("#login-success").html(response).slideToggle("fast");
            jQuery('#login-success .return').click(r_toggle);
            jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
              return false;
        }
    );
	    
}
*/

function bbconnectpanels_process(e){ 
	
	if ( jQuery(this).hasClass( 'disabled' ) ) { return false; }
	
	if ( jQuery(this).attr('href') ) {
		e.preventDefault();
		var ref = jQuery(this).attr('href').split('?');
		var ref = ref[1];
	} else if ( jQuery(e.target).is('select') ) {
		var ref = jQuery(this).val();
	} else {
		var ref = jQuery(this).attr('rel'); // where we are going
	}
	if ( jQuery(this).hasClass('pp-close') ) {
		var action = 'close';
	} else {
		var action = '';
	}
	var title = jQuery(this).attr('title'); // relationship of element to itself for targeting
	bbconnectpanels_toggle(ref,title,action); 
	
}

function bbconnectpanels_pass(rel){ 
	
	if ( !rel ) { return false; }
	
	if ( jQuery(this).attr('href') ) {
		e.preventDefault();
		var ref = jQuery(this).attr('href').split('?');
		var ref = ref[1];
	} else if ( jQuery(e.target).is('select') ) {
		var ref = jQuery(this).val();
	} else {
		var ref = jQuery(this).attr('rel'); // where we are going
	}
	if ( jQuery(this).hasClass('pp-close') ) {
		var action = 'close';
	} else {
		var action = '';
	}
	var title = jQuery(this).attr('title'); // relationship of element to itself for targeting
	bbconnectpanels_toggle(ref,title,action); 
	
}

function bbconnectpanels_countdown(){
    var newcount = parseInt(jQuery('#tschuss-redirect').attr('title')) - 1;
	if ( 0 >= newcount ) {
        jQuery('#signcounter').html('');
	} else {
        jQuery('#signcounter').html(newcount);
        jQuery('#tschuss-redirect').attr('title',newcount);
		setTimeout('bbconnectpanels_countdown()', 1500);
	}
}

// WHEN THE DOM IS READY...
jQuery(document).ready(function () {

	// PREPEND THE AJAX LOGIN FORM
	jQuery(bbconnectpanelsAjax.panel_pre).prepend(jQuery('#bbconnectpanels-frame'));

	if ( bbconnectpanelsAjax.panel_embed ) {
		if ( jQuery('#bbconnectpanels-wrapper').is(':visible') ) {
			// TOGGLE THE AJAX LOGIN FORM
			jQuery(document).on('click', '.bbconnectf-wrap a.bbconnectpanels-toggle', bbconnectpanels_process);
			//jQuery('li.bbconnectpanels-toggle').on('click', 'a', bbconnectpanels_process);
		} else {
			//jQuery(document).on('click', 'a.bbconnectpanels-toggle', function(){
				//window.location.href = bbconnectpanelsAjax.ajaxhome+'/bbconnect/?rel='+jQuery(this).attr('rel');
			//});
		}
	} else {
		// TOGGLE THE AJAX LOGIN FORM
		jQuery(document).on('click', 'a.bbconnectpanels-toggle', bbconnectpanels_process);
		jQuery('li.bbconnectpanels-toggle').on('click', 'a', bbconnectpanels_process);
		
		jQuery('*').each(function() {
			if ( jQuery(this).css('position') == 'fixed' ) {
				//var item = jQuery(this)[0].nodeName + jQuery(this).attr('id');
				var position = jQuery(this).position();
				var height = jQuery(this).height();
				//alert( item + "left: " + position.left + ", top: " + position.top );
				if ( position.top == 0 && jQuery(this).is(':visible') ) {
					jQuery('#bbconnect').css('padding-top',height + 50);
				}
			}
		});
	}
	
	
	// TOGGLE THE WP ADMIN BAR
	jQuery('.bbconnectpanels-toggle-wp-admin').click(function() {
		jQuery('#wp-admin-bar-my-account').toggleClass('hover');
	});
	
	// AJAX FORM LOGIN 
	jQuery('.bbconnectf-frame').on('click', 'input:submit', bbconnectpanels_submit);
	jQuery('.bbconnectf-frame').on('change', '.sub-select', bbconnectpanels_submit);
	//jQuery('#bbconnectpanels-frame-embed').on('click', 'input:submit', bbconnectpanels_submit);
	//jQuery('#bbconnectpanels-frame-embed').on('change', '.sub-select', bbconnectpanels_submit);
	jQuery('#bbconnect').on('keypress','.bbconnectpanels-form-exceptions input', function (evt) {
		var charCode = evt.charCode || evt.keyCode;
		if (charCode  == 13) { //Enter key's keycode
			return false;
		}
	});
		
	jQuery('#pp-menu').hover(function(){ jQuery('.pp-sub-menu').toggle(); });
	
	jQuery(document).on('click', '.bbconnectpanels-close', function(){
		jQuery(this).attr('title',jQuery('#bbconnectpanel').attr('class'));
		jQuery('#bbconnect').empty();
		jQuery('#bbconnectpanel').removeClass().slideToggle('fast');
		// TAKE ME HOME, SCOTTY!
		jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
		  return false;
	});
	
	//SET SUPPORT FOR CHOSEN
	bbconnect_bind_events();
	
	// OPEN THE PANELS
	if ( jQuery.cookie( 'bbconnectpanels' ) ) {
		var action = jQuery.cookie( 'bbconnectpanels' );
		if ( jQuery('#bbconnectpanel').not(':visible') ) {
			bbconnectpanels_toggle(action,'hereto','');
		}
	}
	
	if ( jQuery('#pp-title').is(':hidden') && jQuery('#pp-notify').is(':empty') ) {
		jQuery('#bbconnectpanels-tabs').css('height', '0');
	}
	
	// PROFILE ACTIONS
//	jQuery('#bbconnect').on('change', '.actions-launcher', bbconnectpanels_process);
		
});

jQuery.noConflict();

function profile_fields() { 

    // DISABLE THE FORM
    jQuery('#profile-fields').submit( function() { return false; } );
    
    // CDATA TO AVOID VALIDATION ERRORS
    //<![CDATA[
    var str = jQuery('#profile-fields').serialize();
    // ]]>
    
    // EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
    jQuery('body').prepend('<div id="bbconnect-overlay"></div>');
	jQuery('#bbconnect-overlay').prepend('<div id="bbconnect-modal">'+bbconnectAdminAjax.oneMoment+'</div>');
    
    // SUBMIT THE FORM DATA
    jQuery.post( 
    	
    	bbconnectAdminAjax.ajaxurl, { 
    		action : 'bbconnect_profile_fields_update', 
    		data : str, 
    		bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#bbconnect-modal').html(response);
            //tb_show(null,'#TB_inline?height=500&width=630&inlineId=bbconnect-ajax-loading&modal=true',null);
            setTimeout(function() {
            	jQuery('#bbconnect-modal').fadeOut('slow');
            	//tb_remove();
            	window.location = '';
            },  3000);
        }
    );
    	    
}

function toggle_inputs() {
	jQuery('.default').each(function(){
		
		var defaultVal = jQuery(this).attr('title');
		
		jQuery(this).focus(function(){
			if (jQuery(this).val() == defaultVal){
				jQuery(this).removeClass('active').val('');
	      	}
	    })
	    
	    .blur(function(){
	      if (jQuery(this).val() == ''){
	        jQuery(this).addClass('active').val(defaultVal);
	      }
	    })
	    
	    .blur().addClass('active');
	    
	});
}
	
function clear_toggle_inputs() {
	jQuery('form').submit(function(){
	  	jQuery('.default').each(function(){
	  		var defaultVal = jQuery(this).attr('title');
	  		if (jQuery(this).val() == defaultVal){
	  			jQuery(this).val('');
	      	}
		});
	});
}

// 
function new_meta_assign(){
	var tmpkey = jQuery(this).val();
	var metakey = tmpkey.replace(/\s+/g,'_').toLowerCase();
	var oldkey = jQuery(this).attr('title');
	var conkey = jQuery(this).attr('id');
	
	// DO A CHECK TO MAKE SURE THEY ARE NOT CREATING A KEY THAT ALREADY EXISTS.
	// MAKE THIS AN AJAX CHECK TO INCLUDE ANY RESERVED TERMS FOR TAXONOMIES
	// SET A TIMEOUT TO MAKE THIS NICER...
	if ( jQuery('#'+metakey+'-ele').length ) {
		
		// LET THE USER KNOW
		if ( !jQuery('#message-'+conkey+' .bbconnect-mk-error').length ) {
			jQuery('#message-'+conkey).append('<span class="bbconnect-mk-error required example-text">'+bbconnectAdminAjax.metaKeyTaken+'</span>');
			jQuery('.new-meta-key').addClass('required');
		}
		
	} else {
	
		//jQuery(this).attr('disabled', 'disabled');
	
		if ( jQuery('#message-'+conkey+' .bbconnect-mk-error').length ) {
			jQuery('#message-'+conkey+' .bbconnect-mk-error').remove();
			jQuery('.new-meta-key').removeClass('required');
		}
		
		jQuery('#'+conkey+'-ele').attr('title',tmpkey);

		jQuery('#t-'+conkey+' .field-options [id]').each(function(i) {
			var nid = jQuery(this).attr('id'); 
			var newid = nid.replace(oldkey,metakey);
			jQuery(this).attr('id',newid);
		});
		jQuery('#t-'+conkey+' .field-options [title]').each(function(i) {
			var ntitle = jQuery(this).attr('title'); 
			var newtitle = ntitle.replace(oldkey,metakey);
			jQuery(this).attr('title',newtitle);
		});
		jQuery('#t-'+conkey+' [name*="bbconnect_user_meta_options"]').each(function(i) {
			var narr = jQuery(this).attr('name'); 
			var newarr = narr.replace('['+oldkey+']','['+metakey+']');
			jQuery(this).attr('name',newarr);
		});
		jQuery('#t-'+conkey+' [name*="bbconnect_user_taxonomy_options"]').each(function(i) {
			var narr = jQuery(this).attr('name'); 
			var newarr = narr.replace('['+oldkey+']','['+metakey+']');
			jQuery(this).attr('name',newarr);
		});
		
		jQuery('#'+conkey+'-ele .right .delete').attr('rel',metakey);
		jQuery('#'+conkey+'-ele .right .undo').attr('rel',metakey);
		jQuery(this).attr('title',metakey);
		jQuery('#'+conkey+'-ele .field-select').removeAttr('disabled');
		jQuery('#'+conkey+'-ele').prependTo('#column_1');
	}
	
}

function new_meta_title(){
	var metatitle = jQuery(this).val();
	var conkey = jQuery(this).parent().find('.new-meta-key').attr('id');
	jQuery('#'+conkey+'-ele .t-trigger').html(metatitle);
	
}

function remove_meta(){
	
	// PREVENT USERS FROM DELETING SECTIONS WITH FIELDS IN IT!
	if ( jQuery(this).closest('.t-wrapper').hasClass('section') && jQuery(this).closest('.section').siblings('.option-sortable').children().length != 0 ) {
		alert(jQuery(this).data('ohno'));
		return false;
	}
	
	var rvar = jQuery(this).attr('rel');
	jQuery(this).closest('.t-wrapper').find('[name*="bbconnect_user_meta_options"]').each(function(i) {
		var narr = jQuery(this).attr('name'); 
		var newarr = narr.replace('['+rvar+']','[_paudel_'+rvar+']');
		jQuery(this).attr('name',newarr);
	});
	jQuery(this).closest('.t-wrapper').find('[name*="bbconnect_user_taxonomy_options"]').each(function(i) {
		var narr = jQuery(this).attr('name'); 
		var newarr = narr.replace('['+rvar+']','[_paudel_'+rvar+']');
		jQuery(this).attr('name',newarr);
	});
	jQuery(this).closest('.t-wrapper').find('.section-output').attr('disabled', 'disabled');
	jQuery(this).next('.undo').show();
	jQuery(this).hide();
	jQuery(this).closest('.t-wrapper').addClass('deleted');
	
}
function restore_meta(){
	
	var rvar = jQuery(this).attr('rel');
	jQuery(this).closest('.t-wrapper').find('[name*="bbconnect_user_meta_options"]').each(function(i) {
		var narr = jQuery(this).attr('name'); 
		var newarr = narr.replace('[_paudel_'+rvar+']','['+rvar+']');
		jQuery(this).attr('name',newarr);
	});
	jQuery(this).closest('.t-wrapper').find('[name*="bbconnect_user_taxonomy_options"]').each(function(i) {
		var narr = jQuery(this).attr('name'); 
		var newarr = narr.replace('[_paudel_'+rvar+']','['+rvar+']');
		jQuery(this).attr('name',newarr);
	});
	jQuery(this).closest('.t-wrapper').removeClass('deleted');
	jQuery(this).prev('.delete').show();
	jQuery(this).hide();
	
}

function field_selection(){
	
	var meta_key = jQuery(this).attr('title');
	var field_type = jQuery(this).val();
	var primary = jQuery('#'+meta_key+'-choices').attr('id');
	
	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+primary).empty().html('<img src="'+bbconnectElemChoicesAjax.ajaxload+'" />');
	 
	jQuery.post( 
		bbconnectElemChoicesAjax.ajaxurl, 
		{ action : 'bbconnect_element_choices_form', meta_key : meta_key, field_type : field_type, bbconnect_element_choices_nonce : bbconnectElemChoicesAjax.bbconnect_element_choices_nonce },
	    function( response ) {
	        // DISPLAY THE RESPONSE
	        jQuery('#'+primary).html(response);
	        bbconnect_bind_events();	
	    }
	);
	
}

function rui_toggle() {
	var ruis = jQuery(this).attr('title');
	var ruli = jQuery(this).closest('ul').attr('id');
	if ( jQuery(this).hasClass('off') ) {
		//jQuery('#'+ruis).removeAttr('disabled');
		jQuery(this).closest('li').find('input, select, textarea').removeAttr('disabled');
		if ( jQuery(this).closest('li').find('.s-trigger').length ) {
			jQuery(this).closest('li').append('<input type="hidden" class="temp-include" name="'+ruli+'['+ruis+']" value="" />');
		}
	} else if ( jQuery(this).hasClass('on') ) {
		//jQuery('#'+ruis).attr('disabled', 'disabled');
		jQuery(this).closest('li').find('input, select, textarea').attr('disabled', 'disabled');
		if ( jQuery(this).closest('li').find('.s-trigger').length ) {
			jQuery(this).closest('li').find('.temp-include').remove();
		}
	}
	jQuery(this).toggleClass('on off');
}

/*	------------------------------------------------------- 
	FORM FUNCTIONS
	------------------------------------------------------- */

function load_form(){
	var cid = jQuery(this).val();
	
	if ( 'new_form' == cid ) {
		jQuery('#add-new-form').fadeIn();
		return false;
	} else {
		if ( jQuery('#add-new-form').is(':visible') ) {
			jQuery('#add-new-form').hide();
		}
		jQuery.post( 
	    	bbconnectAdminAjax.ajaxurl, { 
	    		action : 'bbconnect_load_form', 
	    		data : cid, 
	    		bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
	    	},
	        function( response ) {
	            // DISPLAY THE RESPONSE
	            jQuery('#show-form').hide();
	            jQuery('#show-form').empty().html(response);
	            jQuery('#show-form').fadeIn('slow');
				bbconnect_bind_events();
	            /*jQuery('#show-form').find('script').each(function(i) {
	            	eval(jQuery(this).text());
	            });*/
	        }
	    );
    }
}

function new_form() {
	var cref = jQuery(this).attr('title');
	var cid = jQuery('#'+cref).val();
	
	jQuery.post( 
		bbconnectAdminAjax.ajaxurl, { 
			action : 'bbconnect_new_form', 
			data : cid, 
	    	bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
		},
	    function( response ) {
	    	jQuery('#show-form').empty();
	        jQuery('#list-forms').html(response);
	        jQuery('#'+cref).val('');
	        setTimeout(function() {
	        	jQuery('#response').fadeOut();
	        	jQuery('#add-new-form').fadeOut();
	        }, 2000);
	    }
	);
}

function delete_form() {
	var cid = jQuery(this).attr('rel');
	var answer = confirm( bbconnectAdminAjax.confirmDelete );
	if (answer) {
		jQuery.post( 
			bbconnectAdminAjax.ajaxurl, { 
				action : 'bbconnect_delete_form', 
				data : cid, 
		    	bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
			},
		    function( response ) {
		        jQuery('#show-form').empty();
		        jQuery('#list-forms').html(response);
		        setTimeout(function() {
		        	jQuery('#response').fadeOut();
		        }, 2000);
		    }
		);
	}
	return false;
}

function check_profile(event) {
	// DISABLE THE FORM
	//jQuery(this).closest('form').submit( function() { return false; } );
	//event.preventDefault();
	
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
	
	if ( jQuery('.halt').length != 0 ) {
		// TAKE ME HOME, SCOTTY!
		if ( !jQuery('#errors').length ) {
			jQuery('#user-form').prepend('<div class="errors">'+bbconnectAdminAjax.errMsg+'</div>');
		}
		jQuery('html, body').animate({ scrollTop: jQuery('#bbconnect').offset().top }, 'slow');
		  return false;
	} else {
		console.log('trying...');
		return true;
	}
	
	return false;
	
}


/*	------------------------------------------------------- 
	ACTIONS EDITOR
	------------------------------------------------------- */

function bbconnect_get_post_to_edit() {
	var val = jQuery(this).val();
	var uid = jQuery(this).attr('title');
	var pclass = jQuery(this).attr('class').split(' ');
	var actung = pclass[1];
	var type = pclass[2];
	var fid = jQuery(this).closest('form').attr('id');
	var cid = jQuery('#'+fid+' .bbconnect-viewer').attr('id').split('_viewer')[0];

	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+fid+' .bbconnect-viewer').empty().html('<img src="'+bbconnectAdminAjax.ajaxload+'" />');
	 
	jQuery.post( 
    	bbconnectAdminAjax.ajaxurl, { 
    	action : 'bbconnect_get_post_to_edit', 
    	data : val, 
    	uid : uid, 
    	actung : actung, 
    	cid : cid, 
    	type : type, 
    	bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#'+fid+' .bbconnect-viewer').html(response);
            /*jQuery('#'+fid+' .bbconnect-viewer').find('script').each(function(i) {
            	eval(jQuery(this).text());
            });*/
            //quicktags({id : cid});
            if ( !bbconnectAdminAjax.firefox ) {
	            tinymce.init(tinyMCEPreInit.mceInit[cid]);
	            tinyMCE.triggerSave();
	        }
	        bbconnect_bind_events();
            jQuery('.disabled #'+fid+' .bbconnect-viewer').find('input[type="text"], select, textarea, input[type="radio"]').attr('disabled', 'disabled');
            //jQuery( 'input.bbconnect-date' ).datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });
        }
    );
 
}

function bbconnect_get_post_to_modify() {
	var val = jQuery(this).attr('title');
	var fid = jQuery(this).closest('form').attr('id');
	var cid = jQuery('#'+fid+' .bbconnect-viewer').attr('id').split('_viewer')[0];
	var pclass = jQuery(this).attr('class').split(' ');
	var type = pclass[2];
		
	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+fid+' .bbconnect-viewer').empty().html('<img src="'+bbconnectAdminAjax.ajaxload+'" />');
	 
	jQuery.post( 
    	bbconnectAdminAjax.ajaxurl, { 
    	action : 'bbconnect_get_post_to_edit', 
    	data : val, 
		cid : cid, 
		type : type, 
    	bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#'+fid+' .bbconnect-viewer').html(response);
            /*jQuery('#'+fid+' .bbconnect-viewer').find('script').each(function(i) {
            	eval(jQuery(this).text());
            });*/
            //quicktags({id : cid});
            if ( !bbconnectAdminAjax.firefox ) {
	            tinymce.init(tinyMCEPreInit.mceInit[cid]);
	            tinyMCE.triggerSave();
	        }
	        bbconnect_bind_events();
            jQuery('.disabled #'+fid+' .bbconnect-viewer').find('input[type="text"], select, textarea, input[type="radio"]').attr('disabled', 'disabled');
            //jQuery( 'input.bbconnect-date' ).datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });
        }
    );
 
}

function bbconnect_save_new_post() {
	
	jQuery(this).closest('form').submit( function() { return false; } );
	
	if ( !bbconnectAdminAjax.firefox ) {
		tinyMCE.triggerSave();
	}
	
	var sid = jQuery(this).closest('form').attr('id');
	var pclass = jQuery(this).attr('class').split(' ');
	var type = pclass[2];
	// CDATA TO AVOID VALIDATION ERRORS
	//<![CDATA[
	var str = jQuery(this).closest('form').serialize();
	// ]]>
	
	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+sid+' div.bbconnect-viewer').empty().html('<img src="'+bbconnectAdminAjax.ajaxload+'" />');
	
	jQuery.post( 
		bbconnectAdminAjax.ajaxurl, { 
		action : 'bbconnect_save_new_post', 
		data : str, 
		sid : sid,
		type : type,  
		bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce 
		}, 
		function( response ) {
		    // DISPLAY THE RESPONSE
		    jQuery('#'+sid+' div.bbconnect-viewer').html(response);
		    jQuery('#'+sid+' .actions-launcher').val('');
	        /*jQuery('#'+sid+' div.bbconnect-viewer').find('script').each(function(i) {
	        	eval(jQuery(this).text());
	        });*/
	    }
	);
}


/*	------------------------------------------------------- 
	DOCUMENT READY
	------------------------------------------------------- */

// when the DOM is ready...
jQuery(document).ready(function () {

	toggle_inputs();
	clear_toggle_inputs();
	
	// MODIFIED WP ADMIN SCREEN FUNCTIONS
	//jQuery('#icon-users').next('h2').children('.add-new-h2').replaceWith('');
	jQuery('#addtag .form-required').next('.form-field').hide();
	
	//SET SUPPORT FOR CHOSEN
	jQuery('.chzn-select').chosen({ allow_single_deselect: true });	
	
	// SORTING FUNCTION FOR LISTS
	jQuery(function() {
		jQuery('.sortable').sortable({
			connectWith: ".connectedSortable", 
			placeholder: "ui-state-highlight", 
			handle: ".handle",
		});
	});
	
	// SORTING FUNCTION FOR MANAGE FIELDS LISTS
	jQuery(function() {
		jQuery('.option-sortable').sortable({
			connectWith: '.connected-option-sortable', 
			handle: '.handle', 
			appendTo: document.body, 
			placeholder: 'pp-ui-highlight', 
			forcePlaceholderSize: true, 
			forceHelperSize: true, 
			start: function(event, ui) {
				if ( jQuery(ui.item).hasClass('li-section') ) {
					jQuery(ui.item).addClass('temp-collapse').find('.section-list').slideToggle('fast');
				}
			}, 
			beforeStop: function(event, ui) {
	            if ( jQuery(ui.item).hasClass('li-section') && jQuery(ui.placeholder).closest('ul').hasClass('section-list') ) {
	                jQuery(this).sortable('cancel');
	                if ( jQuery(ui.item).hasClass('temp-collapse') ) {
	                	jQuery(ui.item).removeClass('temp-collapse').find('.section-list').slideToggle('fast');
	                }
	            }
	        }, 
	        stop: function(event, ui) {
	        	if ( jQuery(ui.item).hasClass('li-section') ) {
	        		if ( jQuery(ui.item).hasClass('temp-collapse') ) {
	        	    	jQuery(ui.item).removeClass('temp-collapse').find('.section-list').slideToggle('fast');
	        	    }
	        	}
	        }, 
			update: function(event, ui) { 
			
			
				// REMOVE THE VALUE OF THE FIELD FROM THE SENDING SECTION
				var myuid = jQuery(ui.item).attr('id');
				var mytuid = myuid.substring(0, myuid.length - 4);
				jQuery(ui.item).find('#t-'+mytuid+' .section-input').val('');
				jQuery(ui.item).find('#t-'+mytuid+' .section-output').remove();
				
				// SET THE VALUE OF THE RECEIVING SECTION TO THE FIELD
				// SET THE VALUE OF THE FIELD TO THE RECEIVING SECTION
				if ( jQuery(ui.item).closest('ul').hasClass('section-list') ) {
					var sid = jQuery(ui.item).closest('.option-sortable').attr('id');
					var tsid = sid.substring(8);
					var uid = jQuery(ui.item).attr('id');
					var tuid = uid.substring(0, uid.length - 4);
					jQuery(ui.item).find('.section-input').val(sid);
					if ( !jQuery('#'+sid+'-'+uid).length ) {
						if ( jQuery(ui.item).hasClass('new-ele-key') ) {
							var tuid = jQuery(ui.item).attr('title');
						}
						jQuery(ui.item).find('.section-input').val(tsid).after('<input id="'+sid+'-'+uid+'" class="section-output" type="hidden" name="bbconnect_user_meta_options['+tsid+'][options][choices][]" value="'+tuid+'" />');
					}
				}
				
				var cid = jQuery(this).attr('id');
				jQuery(ui.item).find('#t-'+mytuid+' .column-input').val(cid);
				
				if ( jQuery(ui.item).hasClass('li-section') ) {
					var sectionid = jQuery(ui.item).find('.section-list').attr('id');
					if ( jQuery(ui.item).hasClass('temp-collapse') ) {
						jQuery(ui.item).removeClass('temp-collapse').find('#'+sectionid).slideToggle('fast');
					}
				}
			}
		});
	});
		
	// MODIFIED TOGGLE FUNCTION FOR REPORT FILTER
	jQuery('.s-trigger').change(function(){
		var taxname = jQuery(this).attr('title');
		var sFilter = jQuery(this).val();
		if ( sFilter === 'filter-by' ) {
			if ( jQuery(this).siblings('.t-temp').length ) { } else {
				jQuery('#'+taxname).slideToggle('fast');
				jQuery(this).after('<a class="t-trigger t-temp" title="'+taxname+'">close</a>');
				jQuery(this).closest('li').delegate('a.t-trigger', 'click', t_toggle);
			}
			jQuery(this).closest('li').find('.temp-include').remove();
		} else if ( sFilter === 'include-in' ) {
			var ruis = jQuery(this).attr('id');
			var ruli = jQuery(this).closest('ul').attr('id');
			if ( jQuery(this).siblings('.t-temp').length ) { 
				jQuery(this).closest('li').undelegate('a.t-trigger', 'click', t_toggle);
				jQuery(this).closest('li').find('a.t-temp').remove();
				jQuery('#'+taxname).slideToggle('fast');
			}
			jQuery(this).closest('li').append('<input type="hidden" class="temp-include" name="'+ruli+'['+ruis+']" value="" />');
		}
	});
	
	// SELECT ALL ELEMENTS IN META OR TAXONOMIES
	jQuery('.master-select').click(function() {
		jQuery(this).toggleClass('activated');
		var selectAll = jQuery(this).attr('title');
		if ( jQuery(this).hasClass('activated') ) {
			jQuery('.'+selectAll).attr('checked', 'checked');
		} else {
			jQuery('.'+selectAll+':checked').removeAttr('checked');
		}
	});
	
	// HEAVY-HANDED TOGGLES FOR QUERIES
	function form_disable() {
		jQuery('.disabled').find('input[type="text"], select, textarea, input[type="radio"]').attr('disabled', 'disabled');
	}
	jQuery(document).ready(form_disable);
	
	// DO IT...
	jQuery('#bbconnect').on('click', '.rui', rui_toggle);
		
	// ACTION SWITCH
	jQuery('#action-select').change(function() {
		
		// DISABLE ANY FIELDS
		jQuery('.action-active  input[type="text"]').attr('disabled', 'disabled');
		jQuery('.action-active  select').attr('disabled', 'disabled');
		jQuery('.action-active  textarea').attr('disabled', 'disabled');
		
		// DISABLE ANY OPEN HOLDERS
		jQuery('.action-holder').removeClass('action-active');
		
		// TOGGLE THE DISPLAY
		jQuery('.action-holder').slideUp('fast');
		
		// OPEN THE SELECTED HOLDER
		var action = jQuery(this).val();
		jQuery('#'+action).slideToggle('fast').addClass('action-active');
		jQuery('#'+action+' input[type="text"]').removeAttr('disabled');
		jQuery('#'+action+' select').removeAttr('disabled');
		jQuery('#'+action+' textarea').removeAttr('disabled');
			
	});
	
	
	// INJECT EMAIL ADDRESSES TO TEXT AREA
	jQuery('#insert-email').click(function(){
		jQuery('.gredit-user:checked').each(function(domEle) {
			if ( this.id ) { } else {
			var email = jQuery(this).next().val()+';';
			jQuery("#client-export").append(email);
			}
		});		
	});
		
	// ADD NEW FIELD BY CLONING
	jQuery('#tim').click(function(){
		
		var primary = jQuery('.primary-list').attr('id');
		var data = jQuery('.new-meta-key').length;
		
		jQuery.post( 
	    	bbconnectAdminAjax.ajaxurl, 
	    	{ action : 'bbconnect_new_elements_form', data : data, bbconnect_admin_nonce : bbconnectAdminAjax.bbconnect_admin_nonce },
	        function( response ) {
	            jQuery('#new-holder').prepend(response);
	            jQuery('#'+data+'-ele .field-select').attr('disabled', 'disabled');
	            jQuery('#'+data+'-ele').addClass('new-ele-key');
	        }
	    );
	    // TAKE ME HOME, SCOTTY!
	    jQuery('html, body').animate({ scrollTop: 0 }, 'slow');
	      return false;
		    	    	
	});	
		
	jQuery('#show-form').on('click', '#delete-form', delete_form);
	jQuery('#list-forms').on('change', '#form-switch', load_form);
	jQuery('#add-new-form').on('click', '#add-form', new_form);
	
	jQuery('#profile-fields').on('click', '#save_options', profile_fields);
	
	// ACTION EDITOR
	jQuery('#bbconnect').on('change', '.actions-launcher', bbconnect_get_post_to_edit);
	jQuery('#bbconnect').on('click', '.profile-actions-edit', bbconnect_get_post_to_modify);
	jQuery('#bbconnect').on('click', '.bbconnect-actions-save', bbconnect_save_new_post);
	
	// TOGGLE FIELD SELECTIONS AND CHOICES
	jQuery('#profile-fields').on('change', '.field-select', field_selection);
	
	// TOGGLE NEW FIELD META_KEY AND TITLE
	jQuery('#profile-fields').on('blur', '.new-meta-key', new_meta_assign);
	jQuery('#profile-fields').on('blur', '.new-meta-title', new_meta_title);
	
	// REMOVE AND RESTORE META
	jQuery('#profile-fields').on('click', '.delete', remove_meta);
	jQuery('#profile-fields').on('click', '.undo', restore_meta);
	
	// RESPONSIVE MENU TOGGLES
	jQuery('.nav-tab-wrapper').on('change', '.nav-tab-select', function(){
		window.location.href = jQuery(this).val();
	});
	
	jQuery('.color-option').wpColorPicker();

});
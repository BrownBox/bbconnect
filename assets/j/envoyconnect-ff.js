jQuery.noConflict();

// USER META TOGGLES
function umt_toggle() {
	var umts = jQuery(this).attr('title');
	if ( jQuery('#'+umts).val() == 1 ) {
		jQuery('#'+umts).val('0');
	} else {
		jQuery('#'+umts).val('1');
	}
	jQuery(this).toggleClass('on');
}

// USER PROFILE TOGGLES
function upt_toggle(){
	var upts = jQuery(this).attr('title');
	if ( jQuery('#'+upts).val() == 'true' ) {
		jQuery('#'+upts).val('false');
	} else {
		jQuery('#'+upts).val('true');
	}
	jQuery(this).toggleClass('on');
}

// USER WP-SPECIAL-CIRCUMSTANCES TOOGLES
function uwpt_toggle(){
	var uwpts = jQuery(this).attr('title');
	if ( jQuery(this).hasClass('on') ) {
		jQuery('#'+uwpts).removeAttr('name');
		jQuery(this).removeClass('on');
	} else {
		jQuery('#'+uwpts).attr('name', uwpts);
		jQuery(this).addClass('on');
	}
	//jQuery(this).toggleClass('on');
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

// CLONE MULTITEXT
function multi_clone() {
	
	// GET... THE ARRAY KEY
	var akid = jQuery(this).attr('title');
	// THE ARRAY KEY +1 
	var akplusid = parseInt(akid) + 1;
	// THE ABSOLUTE PARENT ELEMENT ID
	var pid = jQuery(this).closest('ul').attr('id');
	
	// PASS THE IMMEDIATE PARENT ID TO THE NEXT PARENT AND PASS THE NEXT KEY TO THE CHILD
	jQuery(this).parent('#'+pid+' li.multilist').clone(true).attr('id',pid+'-'+akplusid).appendTo('#'+pid);
	//jQuery(this).parent('#'+pid+' li.multilist').attr('id','');
	jQuery('#'+pid+'-'+akplusid+' a.button').attr('title',akplusid);
	jQuery('#'+pid+'-'+akplusid+' a.add').attr('id',pid+'-'+akplusid+'-add');
	jQuery('#'+pid+'-'+akplusid+' a.sub').attr('id',pid+'-'+akplusid+'-sub');
	jQuery('#'+pid+'-'+akplusid+' select').attr('id',pid+'-'+akplusid+'-select');
	jQuery('#'+pid+'-'+akplusid+' input[type="text"]').each(function(i) {
		var f = jQuery(this).attr('name'); 
		var newf = f.replace(akid,akplusid);
		jQuery(this).attr('name',newf);
	});
	jQuery('#'+pid+'-'+akplusid+' select').each(function(i) {
		var f = jQuery(this).attr('name'); 
		var newf = f.replace(akid,akplusid);
		jQuery(this).attr('name',newf);
	});
	//jQuery('#'+pid+'-'+akplusid+' select').attr('name','envoyconnect_user_meta['+pid+']['+akplusid+'][type]');
	//jQuery('#'+pid+'-'+akplusid+' input[type="text"]').attr('name','envoyconnect_user_meta['+pid+']['+akplusid+'][value]');
	jQuery('#'+pid+'-'+akplusid+' a.sub').show();
	
	// NEUTER THE BUTTON!
	jQuery(this).unbind('click');
	jQuery(this).remove();
	
	/* SEARCH AND REPLACE
	var f = jQuery('#temp span'); 
	var nakid = new RegExp('\\['+akid+'\\]', 'gi');
	f.html( f.html().replace(nakid,'['+akplusid+']') );
	*/
}
	
// REMOVE MULTITEXT
function anti_multi_clone() {
	// THE ABSOLUTE PARENT ELEMENT ID
	var pid = jQuery(this).closest('ul').attr('id');
	
	// THE NUMBER OF ELEMENTS
	var cid = jQuery('#'+pid+' li').length;
	
	// THE LAST ELEMENT
	var lid = cid - 1;
	
	// THE SECOND TO THE LAST ELEMENT
	var slid = cid - 2;
	
	if ( jQuery(this).parent('.multilist').prev().is('#'+pid+' li.multilist:first-child') && jQuery(this).parent('.multilist').is('#'+pid+' li.multilist:last-child') ) {
		jQuery(this).parent('.multilist').prev().append('<a id="'+pid+'-0-add" class="add" title="0">&nbsp;</a>');
		jQuery(this).parent('.multilist').prev().delegate('a.add', 'click', multi_clone);
		jQuery(this).parent('.multilist').remove();
	} else {
		if ( jQuery(this).parent('.multilist').is('#'+pid+' li.multilist:last-child') ) {
			jQuery(this).parent('.multilist').prev().append('<a id="'+pid+'-'+slid+'-add" class="add" title="'+slid+'">&nbsp;</a>');
			jQuery(this).parent('.multilist').prev().delegate('a.add', 'click', multi_clone);
		}
		jQuery(this).parent('.multilist').remove();
	}
}

function envoyconnect_action_view() {
	var views = jQuery(this).attr('rel');
	var view = jQuery('#post-'+views).html();
	var fid = jQuery(this).closest('form').attr('id');
	
	jQuery('#'+fid+' .envoyconnect-viewer').empty();
	jQuery('#'+fid+' .envoyconnect-viewer').html(view).fadeIn('fast');
}

function envoyconnect_icon_toggle() {
	var tar = jQuery(this).attr('rel');
	jQuery('.actions-history-list .'+tar).toggle();
}
function envoyconnect_select_toggle() {
	var tar = jQuery(this).val();
	var fid = jQuery(this).closest('form').attr('id');
	jQuery('#'+fid+' .actions-history-list li').each( function() {
		if ( 'all' == tar || jQuery(this).hasClass(tar) ) {
			jQuery(this).show();
		} else if ( !jQuery(this).hasClass(tar) ) {
			jQuery(this).hide();
		}
	});
}
function envoyconnect_get_post_to_edit() {
	var val = jQuery(this).val();
	var uid = jQuery(this).attr('title');
	var pclass = jQuery(this).attr('class').split(' ');
	var actung = pclass[1];
	var type = pclass[2];
	var fid = jQuery(this).closest('form').attr('id');
	var cid = jQuery('#'+fid+' .envoyconnect-viewer').attr('id').split('_viewer')[0];

	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+fid+' .envoyconnect-viewer').empty().html('<img src="'+envoyconnectAjax.ajaxload+'" />');
	 
	jQuery.post( 
    	envoyconnectAjax.ajaxurl, { 
    	action : 'envoyconnect_get_post_to_edit', 
    	data : val, 
    	uid : uid, 
    	actung : actung, 
    	cid : cid, 
    	type : type, 
    	envoyconnect_nonce : envoyconnectAjax.envoyconnect_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#'+fid+' .envoyconnect-viewer').html(response);
            /*jQuery('#'+fid+' .envoyconnect-viewer').find('script').each(function(i) {
            	eval(jQuery(this).text());
            });*/
            quicktags({id : cid});
            //tinymce.init(tinyMCEPreInit.mceInit[cid]);
            //tinyMCE.triggerSave();
            jQuery('.disabled #'+fid+' .envoyconnect-viewer').find('input[type="text"], select, textarea, input[type="radio"]').attr('disabled', 'disabled');
            jQuery( 'input.envoyconnect-date' ).datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });
        }
    );
 
}

function envoyconnect_get_post_to_modify() {
	var val = jQuery(this).attr('title');
	var fid = jQuery(this).closest('form').attr('id');
	var cid = jQuery('#'+fid+' .envoyconnect-viewer').attr('id').split('_viewer')[0];
	var pclass = jQuery(this).attr('class').split(' ');
	var type = pclass[2];
		
	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+fid+' .envoyconnect-viewer').empty().html('<img src="'+envoyconnectAjax.ajaxload+'" />');
	 
	jQuery.post( 
    	envoyconnectAjax.ajaxurl, { 
    	action : 'envoyconnect_get_post_to_edit', 
    	data : val, 
		cid : cid, 
		type : type, 
    	envoyconnect_nonce : envoyconnectAjax.envoyconnect_nonce 
    	},
        function( response ) {
            // DISPLAY THE RESPONSE
            jQuery('#'+fid+' .envoyconnect-viewer').html(response);
            /*jQuery('#'+fid+' .envoyconnect-viewer').find('script').each(function(i) {
            	eval(jQuery(this).text());
            });*/
            quicktags({id : cid});
            //tinymce.init(tinyMCEPreInit.mceInit[cid]);
            //tinyMCE.triggerSave();
            jQuery('.disabled #'+fid+' .envoyconnect-viewer').find('input[type="text"], select, textarea, input[type="radio"]').attr('disabled', 'disabled');
            jQuery( 'input.envoyconnect-date' ).datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });
        }
    );
 
}

function envoyconnect_save_new_post() {
	
	jQuery(this).closest('form').submit( function() { return false; } );
	
	//tinyMCE.triggerSave();
	
	var sid = jQuery(this).closest('form').attr('id');
	var pclass = jQuery(this).attr('class').split(' ');
	var type = pclass[2];
	// CDATA TO AVOID VALIDATION ERRORS
	//<![CDATA[
	var str = jQuery(this).closest('form').serialize();
	// ]]>
	
	// EMPTY OUT THE MAIN HOLDER'S ELEMENTS AND PLAY THE LOADER
	jQuery('#'+sid+' div.envoyconnect-viewer').empty().html('<img src="'+envoyconnectAjax.ajaxload+'" />');
	
	jQuery.post( 
		envoyconnectAjax.ajaxurl, { 
		action : 'envoyconnect_save_new_post', 
		data : str, 
		sid : sid,
		type : type,  
		envoyconnect_nonce : envoyconnectAjax.envoyconnect_nonce 
		}, 
		function( response ) {
		    // DISPLAY THE RESPONSE
		    jQuery('#'+sid+' div.envoyconnect-viewer').html(response);
		    jQuery('#'+sid+' .actions-launcher').val('');
	        /*jQuery('#'+sid+' div.envoyconnect-viewer').find('script').each(function(i) {
	        	eval(jQuery(this).text());
	        });*/
	    }
	);
}

function toggle_placeholder() {
	/*
	$(document).ready(function() {
	    if (! ("placeholder" in document.createElement("input"))) {
	        $('*[placeholder]').each(function() {
	            $this = $(this);
	            var placeholder = $(this).attr('placeholder');
	            if ($(this).val() === '') {
	                $this.val(placeholder);
	            }
	            $this.bind('focus',
	            function() {
	                if ($(this).val() === placeholder) {
	                    this.plchldr = placeholder;
	                    $(this).val('');
	                }
	            });
	            $this.bind('blur',
	            function() {
	                if ($(this).val() === '' && $(this).val() !== this.plchldr) {
	                    $(this).val(this.plchldr);
	                }
	            });
	        });
	        $('form#new_mail').bind('submit',
	        function() {
	            $(this).find('*[placeholder]').each(function() {
	                if ($(this).val() === $(this).attr('placeholder')) {
	                    $(this).val('');
	                }
	            });
	        });
	    }
	});
	*/
}


// when the DOM is ready...
jQuery(document).ready(function () {

	jQuery('.t-wrapper').hover(function(){
		//jQuery(this).('.toggle_msg').toggle();
	});
	
	jQuery(document).on('click', '.t-trigger', t_toggle);
	
	// DATEPICKER STUFF
	jQuery( 'input.envoyconnect-date' ).datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true });	
	
	// USER META TOOGLES
	jQuery(document).on('click', 'a.umt', umt_toggle);
	
	// USER PROFILE TOOGLES
	jQuery(document).on('click', 'a.upt', upt_toggle);
	jQuery(document).on('click', 'a.pmt', upt_toggle);
	
	// USER WP-SPECIAL-CIRCUMSTANCES TOOGLES
	jQuery(document).on('click', 'a.uwpt', uwpt_toggle);
	
	// CLONE MULTITEXT
	jQuery('.multilist').on('click', 'a.add', multi_clone);
	
	// REMOVE MULTITEXT
	jQuery('.multilist').on('click', 'a.sub', anti_multi_clone);
	
	// HELP TIPS
	jQuery('.help').tipTip({defaultPosition: 'top'});
	jQuery('.icon-admin').tipTip({defaultPosition: 'top'});
	jQuery('.icon-public').tipTip({defaultPosition: 'top'});
	jQuery('.icon-signup').tipTip({defaultPosition: 'top'});
	jQuery('.delete').tipTip({defaultPosition: 'top'});
	jQuery('.undo').tipTip({defaultPosition: 'top'});
	jQuery('.info').tipTip({defaultPosition: 'top'});
	jQuery('.link-link').tipTip({defaultPosition: 'top'});
	jQuery('.pmt').tipTip({defaultPosition: 'top', attribute: 'rel'});	
	
	// TABBED SECTIONS: SETUP
	jQuery('.envoyconnect-profile .section').hide();
	jQuery('.envoyconnect-profile .nav-tab:first').addClass('nav-tab-active');
	jQuery('.envoyconnect-profile .section:first').fadeIn('fast');
	
	//  TABBED SECTIONS: HANDLE TRANSITIONS
	jQuery(document).on('click', '.envoyconnect-profile a.nav-tab', function() {
		jQuery('.envoyconnect-profile .nav-tab').removeClass('nav-tab-active');
		jQuery(this).addClass('nav-tab-active');
		jQuery('.envoyconnect-profile .section').hide();
		var active = jQuery(this).attr('rel');
		jQuery('#'+active).fadeIn('fast');
		return false;
	});
		
	//SET SUPPORT FOR CHOSEN
	jQuery('.chzn-select').chosen({ allow_single_deselect: true });
	
	// THE PAUPRESSS ACTIONS LAUNCHER	
	jQuery('#a-launcher').change(function() {
		jQuery('.a-button').hide();
		var action = jQuery(this).val();
		jQuery('#button-'+action).slideToggle('fast');
	});
	
	toggle_inputs();
	clear_toggle_inputs();
	
	// PROFILE ACTIONS
	jQuery('#envoyconnect').on('click', '.envoyconnect-view', envoyconnect_action_view);
	jQuery('#envoyconnect').on('click', '.envoyconnect-icon', envoyconnect_icon_toggle);
	jQuery('#envoyconnect').on('change', '.profile-actions-filter', envoyconnect_select_toggle);
	jQuery('#envoyconnect').on('change', '.actions-launcher', envoyconnect_get_post_to_edit);
	jQuery('#envoyconnect').on('click', '.profile-actions-edit', envoyconnect_get_post_to_modify);
	jQuery('#envoyconnect').on('click', '.envoyconnect-actions-save', envoyconnect_save_new_post);
	
});


// BASIC ALL-PURPOSE TOGGLE
function t_toggle() {
	var taxname = jQuery(this).attr('title');
	jQuery('#'+taxname).slideToggle('fast');
	jQuery(this).toggleClass('open');
	if ( jQuery(this).hasClass('t-temp') ) {
		jQuery(this).parent('li').undelegate('a.t-trigger', 'click', t_toggle);
		jQuery(this).remove();
	}
}

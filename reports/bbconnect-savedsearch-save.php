<script type="text/javascript">
//AJAX FORM SUBMISSION FOR SAVE CRITERIA.
function bbconnect_save_submit(){
	//DISABLE SUBMIT BUTTON TO AVOID DUPLICATION
	jQuery(this).attr('disabled','disabled');
	// GET THE ID OF CURRENT FORM
	// FOR PAGINATION, TAKE THE EXTRA ARGUMENTS FOR LOCATION AND RETURN
	var fid = jQuery(this).closest('.save-form').attr('id');
	var privateV = false;
	var segment = false;
	var category = false;
	var query = <?php echo json_encode($last_search);?>;
	// DISABLE THE FORM
    jQuery('#'+fid).submit( function() { return false; } );
    if(jQuery('#private').is(':checked')) privateV = true;
    if(jQuery('#segment').is(':checked')) segment = true;
    if(jQuery('#category').is(':checked')) category = true;
    query.privateV = privateV;
    query.segment = segment;
    query.category = category;
    query.postTitle = jQuery('#post_title').val();

    // SUBMIT THE FORM DATA
        jQuery.ajax({
    		type: 'POST',
	    	url: bbconnectReportAjax.ajaxurl,
	    	data: {
	    		action : 'bbconnect_create_search_post',
	    		data : query,
	    		bbconnect_report_nonce : bbconnectReportAjax.bbconnect_report_nonce
	    	},
	        success: function( response ) {
	            jQuery('#saved-search-notice').html(response);
	            jQuery('#TB_window').fadeOut();
	            jQuery('#TB_overlay').fadeOut();
	            self.parent.tb_remove();
     			//e.preventDefault();
	        },
	        timeout: 60000
	    });

}
// WHEN THE DOM IS READY...
jQuery(document).ready(function () {
	jQuery('.save-go').on('click',  bbconnect_save_submit);
});
</script>
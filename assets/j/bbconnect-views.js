jQuery.noConflict();

/**
 * jQuery Cookie plugin
 *
 * Copyright (c) 2010 Klaus Hartl (stilbuero.de)
 * Dual licensed under the MIT and GPL licenses:
 * http://www.opensource.org/licenses/mit-license.php
 * http://www.gnu.org/licenses/gpl.html
 *
 */
jQuery.cookie = function (key, value, options) {

    // key and at least value given, set cookie...
    if (arguments.length > 1 && String(value) !== "[object Object]") {
        options = jQuery.extend({}, options);

        if (value === null || value === undefined) {
            options.expires = -1;
        }

        if (typeof options.expires === 'number') {
            var days = options.expires, t = options.expires = new Date();
            t.setDate(t.getDate() + days);
        }

        value = String(value);

        return (document.cookie = [
            encodeURIComponent(key), '=',
            options.raw ? value : encodeURIComponent(value),
            options.expires ? '; expires=' + options.expires.toUTCString() : '', // use expires attribute, max-age is not supported by IE
            options.path ? '; path=' + options.path : '',
            options.domain ? '; domain=' + options.domain : '',
            options.secure ? '; secure' : ''
        ].join(''));
    }

    // key and possibly options given, get cookie...
    options = value || {};
    var result, decode = options.raw ? function (s) { return s; } : decodeURIComponent;
    return (result = new RegExp('(?:^|; )' + encodeURIComponent(key) + '=([^;]*)').exec(document.cookie)) ? decode(result[1]) : null;
};

// THE VIEW TOGGLE
function inline_toggle_view() {
	
	// SET THE VARS
	var view = jQuery(this).attr('rev');
	
	// SET THE ICON AND CONTENT STATES
	//jQuery('#content-loader').toggle();
	jQuery('.content-view').removeClass('on');
	jQuery('.content-display').hide();
	
	// RESET THE ICON AND CONTENT STATES
	jQuery(this).addClass('on');
	jQuery('#'+view).show();
	//jQuery('#content-loader').toggle();
	
	// DROP THE COOKIE
	jQuery.cookie('view', view, { path: '/' });
	
}

// when the DOM is ready...
jQuery(document).ready(function () {

	// SET A COOKIE FOR THEIR TOGGLE
	jQuery('.toggle-view').on('click', '.content-view', inline_toggle_view);
	jQuery('.toggle-view a').tipTip({defaultPosition: 'top', edgeOffset: -15});	
		
});
/**
 * Admin JS
 * @package WordPress
 * @subpackage  Twitter Profile Followers
 * @author Shazzad Hossain Khan
 * @url https://shazzad.me
**/


(function($, twpf) {
	"use strict";
	
	var setUrlParameter = function(url, key, value) {
		var baseUrl = url.split('?').length === 2 ? url.split('?')[0] : url,
			urlQueryString = url.split('?').length === 2 ? '?' + url.split('?')[1] : '',
			newParam = key + '=' + value,
			params = '?' + newParam;

		// If the "search" string exists, then build params from it
		if (urlQueryString) {
			var updateRegex = new RegExp('([\?&])' + key + '[^&]*');
			var removeRegex = new RegExp('([\?&])' + key + '=[^&;]+[&;]?');

			if (typeof value === 'undefined' || value === null || value === '') { // Remove param if value is empty
				params = urlQueryString.replace(removeRegex, "$1");
				params = params.replace(/[&;]$/, "");

			} else if (urlQueryString.match(updateRegex) !== null) { // If param exists already, update it
				params = urlQueryString.replace(updateRegex, "$1" + newParam);

			} else { // Otherwise, add it to end of query string
				params = urlQueryString + '&' + newParam;
			}
		}

		// no parameter was set so we don't need the question mark
		params = params === '?' ? '' : params;
		return baseUrl + params;
	};

	$(document).ready(function(){
		/* confirm action */
		$(document.body).on('click', '.twpf_ca', function(){
			var d = $(this).data('confirm') || 'Are you sure you want to do this ?';
			if(! confirm(d)){
				return false;
			}
		});

		/* project forms */
		$(document.body).on('twpf_settings_form/done', function($form, r){
			if (r.success) {
				window.location.href = setUrlParameter(twpf.settingsUrl, 'message', r.message);
			}
		});
	});

})(jQuery, twpf);

jQuery(document).ready(function() {
	jQuery('#blog_domain').autocomplete({
		source: function( request, response ) {
			var data = {
				action: 'jcmst_get_blogs_domains_disabled',
				term: request.term,
			};
			jQuery.post(ajaxurl, data, response);
		},
	})
});
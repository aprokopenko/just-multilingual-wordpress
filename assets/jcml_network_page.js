jQuery(document).ready(function() {
	jQuery('#jcml_blog_domain').autocomplete({
		source: function( request, response ) {
			var data = {
				action: 'jcml_get_blogs_domains_disabled',
				term: request.term,
			};
			jQuery.post(ajaxurl, data, response);
		},
	})
});
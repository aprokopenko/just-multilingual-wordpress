//ajax language form submit
jQuery(document).ready(function() {
	jQuery('#add-language-form').on('submit', function() {
		var data = {
			action: 'jcml_add_language',
			language: jQuery(this).serialize(),
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#partial_network_languages').html(response['languages']);
			jQuery('.errors_and_messages').html(response['messages']);
			if (response['clear_form'])
				jQuery('#add-language-form')[0].reset();
		});

		return false;
	});

});

function detachLang(id) {
	if (confirm('Are you sure you want detach this language from site?')) {

		var data = {
			action: 'jcml_remove_language',
			id: id,
		};
		jQuery.post(ajaxurl, data, function(response) {
			jQuery('#partial_network_languages').html(response['languages']);
			jQuery('.errors_and_messages').html(response['messages']);
		});
	}

	return false;
}
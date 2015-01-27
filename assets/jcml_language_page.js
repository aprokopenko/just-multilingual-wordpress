//ajax language form submit
jQuery(document).ready(function() {

	jQuery('#add-language-form').on('submit', function() {
		var errors = false;
		jQuery('.regular-text').each(function() {
			if (!jQuery(this).val()) {
				fire_message('Please fill all required fields', 'error');
				errors = true;
			}
		});
		if (!errors) {
			var data = {
				action: 'jcml_add_language',
				language: jQuery(this).serialize(),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response['status'] == 1) {
					//refreshing parts of a page (errors, messages and language grid)
					var html;
					html = '<tr id="jcml_fieldset_lang_' + response['blog']['id'] + '">';
					html += '<td><a target="_blank" href="' + response['blog']['site_url'] + '">' + response['blog']['domain'] + '</a></td>';
					html += '<td>' + response['blog']['language'] + '</td>';
					html += '<td>' + response['blog']['alias'] + '</td>';
					html += '<td><span class=""><a href="#" id="edit_lang_' + response['blog']['id'] + '" class="edit_lang_button" ><span class="">edit</span></a></span>|';
					html += '<span class="delete"><a href="#" id="lang_' + response['blog']['id'] + '" class="detach_lang_button" ><span class="delete">detach</span></a></span>';
					html += '<img class="ajax-feedback " alt="" title="" src="' + response['blog']['bloginfo_url'] + '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
					html += '</td>';
					html += '</tr>';

					jQuery('#mapped_languages_table').append(html);
					fire_message("Language added", 'message');

					//clearing form if no errors
					jQuery('#add-language-form')[0].reset();
					jQuery('#add-form-holder').hide();
				} else {
					fire_message(response['errors'], 'error');
				}


			});
		}
		return false;
	});

	jQuery('#edit-language-form').on('submit', function() {
		var errors = false;
		jQuery('.regular-text-edit').each(function() {
			if (!jQuery(this).val()) {
				fire_message('Please fill all required fields', 'error');
				errors = true;
			}
		});
		if (!errors) {
			var data = {
				action: 'jcml_edit_language',
				language: jQuery(this).serialize(),
			};
			jQuery.post(ajaxurl, data, function(response) {
				if (response['status'] == 1) {
					//refreshing parts of a page (errors, messages and language grid)
					var html = generate_row(response);

					jQuery('#mapped_languages_table').append(html);
					fire_message("Language added", 'message');

					//clearing form if no errors
					jQuery('#add-language-form')[0].reset();
					jQuery('#add-form-holder').hide();
				} else {
					fire_message(response['errors'], 'error');
				}


			});
		}
		return false;
	});

	jQuery('a.detach_lang_button').live('click', function() {
		if (confirm('Are you sure you want detach this language from site?')) {
			var id = jQuery(this).attr('id');
			var data = {
				action: 'jcml_remove_language',
				id: id,
			};
			var loader = jQuery(this).parent().find('img.ajax-feedback');
			jcml_ajax(data, 'json', loader, function(response) {
				
				jQuery('#jcml_fieldset_' + id).remove();
				fire_message(response.message, 'message');
			});
		}

	});

	jQuery('a.edit_lang_button').live('click', function() {
		jQuery('#add-form-holder').hide();
		jQuery('#add-language-form')[0].reset();
		var row = jQuery(this).parents('tr');
		var domain = row.find('td:first').html();

		jQuery('#edit-domain-id').val(jQuery(this).attr('id'));
		jQuery('#domain_edited').html(domain);
		jQuery('#edit-form-holder').show();
		return false;
		var id = jQuery(this).attr('id');
		var data = {
			action: 'jcml_edit_language',
			id: id,
		};
		var loader = jQuery(this).parent().find('img.ajax-feedback');
		jcml_ajax(data, 'json', loader, function(response) {
			
			var html = generate_row(response);
			console.log(html);
			row.replaceWith(html);
			fire_message("Language updated", 'message');

			//clearing form if no errors
			jQuery('#edit-language-form')[0].reset();
			jQuery('#edit-form-holder').hide();
		});

	});

	jQuery('#new_lang_button').click(function() {
		jQuery('#edit-form-holder').hide();
		jQuery('#edit-language-form')[0].reset();
		jQuery('#add-form-holder').show();
	});

});

function generate_row(data) {
	var html;
	alert(111);
	html = '<tr id="jcml_fieldset_lang_' + data['blog']['id'] + '">';
	html += '<td><a target="_blank" href="' + data['blog']['site_url'] + '">' + data['blog']['domain'] + '</a></td>';
	html += '<td>' + data['blog']['language'] + '</td>';
	
	html += '<td>' + data['blog']['alias'] + '</td>';
	html += '<td><span class=""><a href="#" id="edit_lang_' + data['blog']['id'] + '" class="edit_lang_button" ><span class="">edit</span></a></span> | ';
	html += '<span class="delete"><a href="#" id="lang_' + data['blog']['id'] + '" class="detach_lang_button" ><span class="delete">detach</span></a></span>';
	html += '<img class="ajax-feedback " alt="" title="" src="' + data['blog']['bloginfo_url'] + '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
	html += '</td>';
	html += '</tr>';
	return html;
}

function fire_message(message, type) {
	var str = '';
	if (typeof message === 'string') {
		str = '<div class="updated below-h2" id="' + type + '"><p>' + message + '</p></div>';
	} else {
		for (var i in message) {
			str += '<div class="updated below-h2" id="' + type + '"><p>' + message[i] + '</p></div>';
		}
	}
	jQuery('.errors_and_messages').html(str);
}

function jcml_ajax(data, respType, loader, callback) {
	// save to local variables to have ability to call them inside ajax
	var _callback = callback;
	var _loader = loader;
	var _respType = respType;

	//pa('wp-ajax call: ' + data.action);

	// add post_type to data
	var post_type = jQuery('#jcf_post_type_hidden').val();
	if (typeof (data) == 'object') {
		data.post_type = post_type;
	}
	else if (typeof (data) == 'string') {
		data += '&post_type=' + post_type;
	}

	// if we have loader - show loader
	if (_loader && _loader.size)
		_loader.css('visibility', 'visible');

	// send ajax
	jQuery.post(ajaxurl, data, function(response) {
		//pa(response);

		// if we have loader - hide loader
		if (_loader && _loader.size)
			_loader.css('visibility', 'hidden');

		// if json - check for errors
		if (_respType == 'json' && response.status != '1') {
			alert(response.error);
			return;
		}

		// if no errors - call main callback
		_callback(response);
	})
}
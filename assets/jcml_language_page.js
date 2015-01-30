//ajax language form submit
jQuery(document).ready(function() {
    //submits add-language form
    jQuery('#jcml-add-language-form').on('submit', function() {
	var errors = false;
	jQuery('.regular-text').each(function() {
	    if (!jQuery(this).val()) {
		jcml_language_fire_message('Please fill all required fields', 'error');
		errors = true;
	    }
	});
	if (!errors) {


	    var data = {
		action: 'jcml_add_language',
		domain: document.forms["jcml-add-language-form"]["language[domain]"].value,
		language: document.forms["jcml-add-language-form"]["language[language]"].value,
		alias: document.forms["jcml-add-language-form"]["language[alias]"].value
	    };



	    jQuery.post(ajaxurl, data, function(response) {

		if (response['status'] == 1) {
		    //refreshing parts of a page (errors, messages and language grid)
		    var html = jcml_language_generate_row(response);

		    jQuery('#jcml_mapped_languages_table').append(html);
		    jcml_language_fire_message("Language added", 'message');

		    //clearing form if no errors
		    jcml_language_close_add_form();
		} else {
		    jcml_language_fire_message(response['errors'], 'error');
		}


	    });
	}
	return false;
    });
    //submits edit-language form
    jQuery('#jcml-edit-language-form').on('submit', function() {
	var errors = false;
	jQuery('.regular-text-edit').each(function() {
	    if (!jQuery(this).val()) {
		jcml_language_fire_message('Please fill all required fields', 'error');
		errors = true;
	    }
	});
	if (!errors) {
	    var data = {
		action: 'jcml_edit_language',
		id: document.forms["jcml-edit-language-form"]["language[id]"].value,
		language: document.forms["jcml-edit-language-form"]["language[language]"].value,
		alias: document.forms["jcml-edit-language-form"]["language[alias]"].value
	    };
	    jQuery.post(ajaxurl, data, function(response) {

		//refreshing parts of a page (errors, messages and language grid)
		var html = jcml_language_generate_row(response);
		jQuery('#jcml_row_lang_' + response['blog']['id']).replaceWith(html);
		jcml_language_fire_message("Language updated", 'message');

		jcml_language_close_edit_form();
	    });
	}
	return false;
    });

    //listener that deletes a language mapping on "delete" click
    jQuery('a.jcml_detach_lang_button').live('click', function() {
	if (confirm('Are you sure you want detach this language from site?')) {
	    var id = jQuery(this).parents('td').attr('id');
	    var data = {
		action: 'jcml_remove_language',
		id: id,
	    };
	    var loader = jQuery(this).parent().find('img.ajax-feedback');
	    jcml_ajax(data, 'json', loader, function(response) {
		//deleting table row and firing message
		jQuery('#jcml_row_' + id).remove();
		jcml_language_fire_message(response.message, 'message');
		//closing any forms that might be opened
		jcml_language_close_edit_form();
		jcml_language_close_add_form();
	    });
	}

    });

    //opens edit language form
    jQuery('a.jcml_edit_lang_button').live('click', function() {
	jcml_language_close_add_form();
	var id = jQuery(this).parents('td').attr('id');

	var row = jQuery(this).parents('tr');
	var data = {
	    action: 'jcml_get_mapping',
	    id: id,
	};
	var loader = jQuery(this).parent().find('img.ajax-feedback');
	jcml_ajax(data, 'html', loader, function(response) {
	    //filling form with default values
	    jQuery('input#jcml-edit-language').val(response.data['language']);
	    jQuery('input#jcml-edit-alias').val(response.data['alias']);
	    jQuery('#jcml_domain_edited').html(response.data['domain']);
	    jQuery('#jcml-edit-form-holder').show();
	    jQuery('#jcml-edit-domain-id').val(id);
	});
    });

    //opens add language form
    jQuery('#jcml_new_lang_button').click(function() {
	jcml_language_close_edit_form();
	jQuery('#jcml-add-form-holder').show();
    });

});

//closes edit language form
function jcml_language_close_edit_form() {
    jQuery('#jcml-edit-language-form')[0].reset();
    jQuery('#jcml-edit-form-holder').hide();

}

//closes add language form
function jcml_language_close_add_form() {
    jQuery('#jcml-add-language-form')[0].reset();
    jQuery('#jcml-add-form-holder').hide();
}

//generates new language row for table
function jcml_language_generate_row(data) {
    var html;
    html = '<tr id="jcml_row_lang_' + data['blog']['id'] + '">';
    html += '<td><a target="_blank" href="' + data['blog']['site_url'] + '">' + data['blog']['domain'] + '</a></td>';
    html += '<td class="map-lang">' + data['blog']['language'] + '</td>';
    html += '<td class="map-alias">' + data['blog']['alias'] + '</td>';
    html += '<td id="lang_' + data['blog']['id'] + '"><span ><a href="#"  class="jcml_edit_lang_button" ><span class="">edit</span></a></span> | ';
    html += '<span class="delete"><a href="#"  class="jcml_detach_lang_button" ><span class="delete">detach</span></a></span>';
    html += '<img class="ajax-feedback " alt="" title="" src="' + data['blog']['bloginfo_url'] + '/wp-admin/images/wpspin_light.gif" style="visibility: hidden;">';
    html += '</td>';
    html += '</tr>';
    return html;
}

//shows errors/messages after editing/adding/deleting language mappings
function jcml_language_fire_message(message, type) {
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

//ajax function wrapper
function jcml_ajax(data, respType, loader, callback) {
    // save to local variables to have ability to call them inside ajax
    var _callback = callback;
    var _loader = loader;
    var _respType = respType;

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
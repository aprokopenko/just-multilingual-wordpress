jQuery(document).ready(function(){
	
	// add click event to buttons to open hidden boxes 
	jQuery(document).on('click', 'div.jcmst-translate-actions input, #jcmst_cancel_rel_btn, #jcmst_cancel_trnsl_btn', function(){
		var $this = jQuery(this);
		var wrapper_id = $this.data('target');
		jQuery(wrapper_id).toggleClass('hide-if-js').find('span.error').remove();
	})
	
	// choose language events
	jQuery(document).on('change', '#jcmst_set_rel_language', function(){
		var $this = jQuery(this);
		var search_input = jQuery('#jcmst_set_rel_post_search');
		if( $this.val() == '' ){
			search_input
					.hide()
					.autocomplete('destroy');
			
		}
		else{
			// when new language chosen - init autocomplete
			search_input
					.show()
					.autocomplete({
						source: function( request, response ) {
							var data = {
								action: 'jcmst_post_search_by_title',
								term: request.term,
								post_type: search_input.data('post_type'),
								blog_id: $this.val()
							};
							jQuery.post(ajaxurl, data, response);
						},
						select: function(event, ui){
							jQuery(event.target).val(ui.item.label);
							jQuery('#jcmst_set_rel_post_id').val(ui.item.value);
							ui.item.value = ui.item.label;
						}
					});
		}
	})
	
	// validate post url function
	var jcmst_check_post_url = function(e){
		var $this = jQuery(this);
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jcmst_post_search_by_url',
				term: $this.val(),
				post_type: $this.data('post_type'),
			},
			success: function(response){
				$this.parent().find('span.error').remove();
				if( typeof(response) != 'object' ) return;

				if( response.status == 'notfound' ){
					$this.removeClass('highlight');
					$this.addClass('error');
					$this.after('<span class="error">specified url not found.</span>');
				}
				else if(response.status == 'ok'){
					$this.removeClass('error');
					$this.addClass('highlight');
				}
			}
		})
	}
	jQuery(document).on('keyup', '#jcmst_set_rel_post_url', jcmst_check_post_url);
	jQuery(document).on('change', '#jcmst_set_rel_post_url', jcmst_check_post_url);
	jQuery(document).on('blur', '#jcmst_set_rel_post_url', jcmst_check_post_url);
	
	// save btn click
	jQuery(document).on('click', '#jcmst_save_rel_btn', function(){
		var $this = jQuery(this);
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'jcmst_post_map_language',
				id: jQuery('#post_ID').val(), // origin post ID
				blog_id: jQuery('#jcmst_set_rel_language').val(),
				post_id: jQuery('#jcmst_set_rel_post_id').val(), // found
				post_url: jQuery('#jcmst_set_rel_post_url').val(),
				post_type: jQuery('#jcmst_set_rel_post_url').data('post_type')
			},
			success: function(response){
				$this.parent().find('span.error').remove();
				if( typeof(response) != 'object' ) return;

				if( response.status == 'failed' ){
					$this.prev().after('<span class="error">' + response.errors + '</span>');
				}
				else if(response.status == 'ok'){
					jcmst_render_available_translations(response.translations);
					
					jQuery('#jcmst_set_rel_language').val('');
					jQuery('#jcmst_set_rel_post_search').val('').hide();
					jQuery('#jcmst_set_rel_post_id').val('');
					jQuery('#jcmst_set_rel_post_url').val('');
					jQuery('#jcmst_set_relation_wrap').addClass('hide-if-js');
				}
			}
		})
	});
	
	// delete translation btn click
	jQuery(document).on('click', '#jcmst_available_translations a.jcmst-delete-translation', function(e){
		e.preventDefault();
		
		var $this = jQuery(this);
		var lang_alias = $this.parents('tr:first').find('td:first').text();
		var question = "Are you sure you want to detach " + lang_alias + " translation?\nThis action cannot be undone!";
		if( confirm(question) ){
			jQuery.ajax({
				url: ajaxurl,
				type: 'POST',
				data: {
					action: 'jcmst_post_detach_language',
					id: jQuery('#post_ID').val(), // origin post ID
					translation_id: $this.data('translation_id')
				},
				success: function(response){
					console.log(response);
					$this.parent().find('span.error').remove();
					if( typeof(response) != 'object' ) return;

					if(response.status == 'ok'){
						jcmst_render_available_translations(response.translations);
					}
				}
			})
		}
	})
	
});

function jcmst_render_available_translations( translation_info ){
	var chain = translation_info.chain;
	
	var content = '';
	for(var i=0; i < chain.length; i++){
		var trans = chain[i];
		var lang = trans.alias;
		content += '<tr><td class="language"><strong>' + lang + '</strong></td><td>';
		content += '<a href="'+trans.post.giud+'" target="_blank">' + jcmst_escapeHtmlEntities(trans.post.post_title) + '</a>';
		content += ' <a href="#" class="jcmst-delete-translation" data-translation_id="' + trans.translation_id + '">delete</a>';
		content += '</td></tr>';
	}
	
	jQuery('#jcmst_available_translations').html( content );
	
	if( chain.length ){
		jQuery('#jcmst_available_translations_wrapper').show();
	}
	else{
		jQuery('#jcmst_available_translations_wrapper').hide();
	}
}

function jcmst_escapeHtmlEntities (str) {
  if (typeof jQuery !== 'undefined') {
    // Create an empty div to use as a container,
    // then put the raw text in and get the HTML
    // equivalent out.
    return jQuery('<div/>').text(str).html();
  }

  // No jQuery, so use string replace.
  return str
    .replace(/&/g, '&amp;')
    .replace(/>/g, '&gt;')
    .replace(/</g, '&lt;')
    .replace(/"/g, '&quot;');
}
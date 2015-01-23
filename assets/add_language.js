//ajax language form submit
jQuery(document).ready(function() {
	jQuery('#add-language-form').on('submit', function() {
		jQuery.post("?page=jcmst-lang-settings#add-new-lang", {language: jQuery(this).serialize()}, function(data) {
			alert(data);			
		}, "json");
		return false;
	});
	
});
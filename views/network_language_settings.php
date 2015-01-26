
<div class="wrap" id="page_wrap">
	<h2>Language settings</h2>
	<p>You can control language mapping for your sites here.</p>
	<div class="errors_and_messages">
	<?php include('partials/_partial_errors_and_messages.php'); ?> 
	</div>
		<div id="partial_network_languages">
	<?php include('partials/_partial_network_languages.php'); ?> 
		</div>
	<br/>
	
	<table class="wp-list-table add-lng-form widefat fixed ">
		<form action="?page=jcmst-lang-settings#add-new-lang" method="post" class="add-lng-form" id="add-language-form" >
		<tr class="multilang-table-header">
			<th colspan="2"><h3 id="add-new-lang">Add new language</h3></th>
		</tr>
		<tr>
			<td colspan="2">
				<div class="errors_and_messages">
				<?php include('partials/_partial_errors_and_messages.php'); ?> 
				</div>
			</td>
		</tr>
		<tr class="form-field form-required">
			<td>Site Address</td>
			<td class="autocomplete-field">
				<input id="blog_domain" type="text" title="Domain" placeholder="Start typing the domain..." class="regular-text" name="language[domain]"
							   value="<?php echo esc_attr(@$input['domain']); ?>">
			</td>
		</tr>
		<tr class="form-field form-required">
			<td>Language code</td>
			<td>
				<input type="text" title="Language code" class="regular-text" name="language[language]"
							   value="<?php echo esc_attr(@$input['language']); ?>">
						<p class="hint">ex. 'en'</p>
			</td>
		</tr>
		<tr class="form-field">
			<td>Language Alias</td>
			<td>
				<input type="text" title="Language alias" class="regular-text" name="language[alias]"
							   value="<?php echo esc_attr(@$input['alias']); ?>">
						<p class="hint">ex. 'English'</p>
			</td>
		</tr>
		<tr>
			<td>
				<p class="submit"><input type="submit" value="Add Language" class="button button-primary" name="add-language"></p>	
			</td>
		</tr>
		</form>	
	</table>
</div>
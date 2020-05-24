jQuery(document).ready(function( $ ){
	function dd_recaptcha_admin_show_hide_fields(){
		var selected_value = $('#dd_recaptcha_admin_options_captcha_version').val();
		$( '.hidden' ).hide();
		$( '.duck-recaptcha-show-field-for-'+ selected_value ).show('slow');
	}
	if( $('#dd_recaptcha_admin_options_captcha_version').length ){
		dd_recaptcha_admin_show_hide_fields();
	}

	$('.form-table').on( "change", "#dd_recaptcha_admin_options_captcha_version", function() {
		dd_recaptcha_admin_show_hide_fields();
	});
});

function onLoadRecaptcha() {
	jQuery('[data-widget=recaptcha]').each(function() {
		var widget = jQuery(this);
		grecaptcha.render(this);
	});
}
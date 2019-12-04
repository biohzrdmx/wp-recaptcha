function onLoadRecaptcha() {
	jQuery('[data=recaptcha]').each(function() {
		var widget = jQuery(this);
		grecaptcha.render(this);
	});
}
# wp-recaptcha

Simple Google reCAPTCHA plugin for WordPress

## Overview

This is a very simple integration plugin for WordPress, to make it easy for developers to add reCAPTCHA to custom forms.

With this plugin you will have to manually add the widget where you want it to be shown _and_ validate the response on the appropiate place.

So, as you may have guessed, this is not an automagic plugin that adds reCAPTCHA into all of WP forms (comments, login, etc). This is a plugin for developers who want to easily output a reCAPTCHA widget and then validate its response, knowing what they are doing.

## Requirements

- WordPress 5.x
- PHP 5.6+

## Installation

Clone/download the repo and install the plugin by unziping the contents of the zip into the `wp-content/plugins` folder of your WP installation and activate it through the WordPress admin dashboard.

Once installed you will see a 'reCAPTCHA' entry on the left-side menu, click it and you will see the options page. There you will require to set the `site` and `secret` keys.

## Usage

If you don't have a `site` key, [click here](https://developers.google.com/recaptcha/) to get one.

Then you must show the widget using the `widget` function directly on the template:

```php
	if ( class_exists('ReCaptcha') ) {
		ReCaptcha::widget();
	}
```

_Tip: It is recommended that you check for the class before trying to use it, just in case the plugin had been disabled._

Once the widget is being shown, check the response on you form processing logic using the `validate` function:

```php
	if ( class_exists('ReCaptcha') ) {
		$recaptcha = get_item($_POST, 'g-recaptcha-response');
		$valid = ReCaptcha::validate($recaptcha);
		if (! $valid ) {
			// Not valid, abort processing
			echo 'You ARE a robot';
			die();
		}
	}
	// Otherwise continue as normal
```

As simple as that.

## Customization

You may customize the default widget by passing some parameters to the ```widget``` function:

- `theme` - The theme to use, either `light` or `dark` (default: `light`)
- `size` - The size of the widget, either `compact` or `normal` (default: `normal`)
- `tabindex` - The tab index property (default: `0`)
- `callback` - Callback for successful respone
- `expired-callback` - Callback for expired widget
- `error-callback` - Callback for error

You can find more info here: https://developers.google.com/recaptcha/docs/display#render_param

## Troubleshooting

The plugin will enqueue a `recaptcha` script with the path to the reCAPTCHA library. This may conflict with other reCAPTCHA plugins on the site.

Also, your server might block connections to the verification URL (https://www.google.com/recaptcha/api/siteverify) so please make sure you can reach it before submitting an issue.

Finally, WordPress might make all this fail without a clear cause, so if you are submitting an issue please provide as much info as possible about the site in question (server type, WP version, PHP version, etc).

## Licensing

MIT licensed

Author: biohzrdmx [<github.com/biohzrdmx>](https://github.com/biohzrdmx)
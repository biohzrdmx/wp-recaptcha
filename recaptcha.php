<?php
	/**
	 * Plugin Name: reCAPTCHA
	 * Plugin URI: github.com/biohzrdmx/wp-recaptcha
	 * Version: 1.5
	 * Author: biohzrdmx
	 * Description: Simple Google reCAPTCHA plugin for WordPress
	 * Plugin URI: http://github.com/biohzrdmx/
	 * Author URI: http://github.com/biohzrdmx/wp-recaptcha
	 */

	if( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	if( ! class_exists('reCAPTCHA') ) {

		class reCAPTCHA {

			public static function init() {
				$folder = dirname( plugin_basename(__FILE__) );
				$ret = load_plugin_textdomain('recaptcha', false, "{$folder}/lang");
			}

			public static function actionHead() {
				$dir = plugin_dir_url(__FILE__);
				wp_enqueue_script( 'recaptcha-js', "{$dir}recaptcha.js", ['jquery'], '1.0', true );
				wp_enqueue_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js?onload=onLoadRecaptcha', [], '1.0', true );
			}

			public static function adminSettingsLink($links, $file) {
				$folder = dirname( plugin_basename(__FILE__) );
				$links = (array) $links;
				if ( $file === "{$folder}/plugin.php" && current_user_can( 'manage_options' ) ) {
					$url = admin_url('admin.php?page=recaptcha');
					$link = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'recaptcha' ) );
					array_unshift($links, $link);
				}
				return $links;
			}

			public static function actionAdminMenu() {
				add_menu_page('reCAPTCHA', 'reCAPTCHA', 'manage_options', 'recaptcha', 'reCAPTCHA::callbackAdminPage', 'dashicons-yes');
			}

			public static function actionAdminInit() {
				register_setting( 'recaptcha', 'recaptcha_options' );
				add_settings_section( 'recaptcha_settings', __( 'General settings', 'recaptcha' ), 'reCAPTCHA::callbackSettings', 'recaptcha' );
				add_settings_field( 'recaptcha_field_site', __('Site key', 'recaptcha'), 'reCAPTCHA::fieldText', 'recaptcha', 'recaptcha_settings', [ 'label_for' => 'recaptcha_field_site', 'class' => 'recaptcha_row' ]);
				add_settings_field( 'recaptcha_field_secret', __('Secret key', 'recaptcha'), 'reCAPTCHA::fieldText', 'recaptcha', 'recaptcha_settings', [ 'label_for' => 'recaptcha_field_secret', 'class' => 'recaptcha_row' ] );
			}

			public static function adminSettingsLink($links, $file) {
				$links = (array) $links;
				if ( $file === 'wp-recaptcha/recaptcha.php' && current_user_can( 'manage_options' ) ) {
					$url = admin_url('admin.php?page=recaptcha');
					$link = sprintf( '<a href="%s">%s</a>', $url, __( 'Settings', 'recaptcha' ) );
					array_unshift($links, $link);
				}
				return $links;
			}

			public static function fieldText($args) {
				$options = get_option( 'recaptcha_options' );
				?>
					<textarea id="<?php echo esc_attr( $args['label_for'] ); ?>" rows="3" cols="50" name="recaptcha_options[<?php echo esc_attr( $args['label_for'] ); ?>]"><?php echo esc_html( isset( $options[ $args['label_for'] ] ) ? $options[ $args['label_for'] ] : '' ); ?></textarea>
				<?php
			}

			public static function callbackAdminPage() {
				if ( ! current_user_can( 'manage_options' ) ) {
					return;
				}
				if ( isset( $_GET['settings-updated'] ) ) {
					add_settings_error( 'recaptcha_messages', 'recaptcha_message', __( 'Settings Saved', 'recaptcha' ), 'updated' );
				}
				settings_errors( 'recaptcha_messages' );
				?>
					<div class="wrap">
						<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
						<form action="options.php" method="post">
							<?php
							settings_fields( 'recaptcha' );
							do_settings_sections( 'recaptcha' );
							submit_button( __('Save Settings', 'recaptcha') );
							?>
						</form>
					</div>
				<?php
			}

			public static function callbackSettings() {
				?>
					<p><?php esc_html_e('Configure here your reCAPTCHA parameters.', 'recaptcha'); ?> <?php esc_html_e('Don\'t have a site key?', 'recaptcha'); ?> <a href="https://developers.google.com/recaptcha/" target="_blank"><?php esc_html_e('Click here to get one.', 'recaptcha'); ?></a></p>
				<?php
			}

			public static function widget($params = [], $echo = true) {
				$ret = '';
				$data = [];
				$options = get_option( 'recaptcha_options' );
				$data['sitekey'] = isset( $options['recaptcha_field_site'] ) ? $options['recaptcha_field_site'] : '';
				$data['theme'] = isset( $params['theme'] ) ? $params['theme'] : 'light';
				$data['size'] = isset( $params['size'] ) ? $params['size'] : 'normal';
				$data['tabindex'] = isset( $params['tabindex'] ) ? $params['tabindex'] : '0';
				$data['callback'] = isset( $params['callback'] ) ? $params['callback'] : null;
				$data['expired-callback'] = isset( $params['expired_callback'] ) ? $params['expired_callback'] : null;
				$data['error-callback'] = isset( $params['error_callback'] ) ? $params['error_callback'] : null;
				$attrs = '';
				foreach ($data as $attr => $value) {
					if ( $value === null) continue;
					$attrs .= "data-{$attr}=\"{$value}\" ";
				}
				$attrs = rtrim($attrs);
				$ret = "<div data=\"recaptcha\" {$attrs}></div>";
				if ($echo) echo $ret;
				return $ret;
			}

			public static function validate($recaptcha) {
				$ret = false;
				$options = get_option( 'recaptcha_options' );
				$secret = isset( $options['recaptcha_field_secret'] ) ? $options['recaptcha_field_secret'] : '';
				$url = 'https://www.google.com/recaptcha/api/siteverify';
				$data = array(
					'secret' => $secret,
					'response' => $recaptcha
				);
				$options = array(
					'http' => array (
						'method' => 'POST',
						'content' => http_build_query($data)
					)
				);
				$context  = stream_context_create($options);
				$verify = file_get_contents($url, false, $context);
				$captcha_success = json_decode($verify);
				if ($captcha_success->success) {
					$ret = true;
				}
				return $ret;
			}
		}

		add_action( 'init', 'reCAPTCHA::init' );
		add_action( 'wp_head', 'reCAPTCHA::actionHead' );
		add_action( 'admin_init', 'reCAPTCHA::actionAdminInit' );
		add_action( 'admin_menu', 'reCAPTCHA::actionAdminMenu' );
		add_filter( 'plugin_action_links', 'reCAPTCHA::adminSettingsLink', 10, 5 );

	}

?>
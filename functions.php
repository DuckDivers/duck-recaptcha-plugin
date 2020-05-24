<?php

// Before all hooks.
add_action( 'init', 'dd_recaptcha_plugin_update', -15 );

function dd_recaptcha_plugin_update() {
	$prev_version = dd_recaptcha_get_option( 'version', '3.1' );
	if ( version_compare( $prev_version, DD_RECAPTCHA_PLUGIN_VERSION, '!=' ) ) {
		do_action( 'dd_recaptcha_plugin_update', $prev_version );
		dd_recaptcha_update_option( 'version', DD_RECAPTCHA_PLUGIN_VERSION );
	}
}

add_action( 'dd_recaptcha_plugin_update', 'dd_recaptcha_plugin_update_32' );

function dd_recaptcha_plugin_update_32( $prev_version ) {
	if ( version_compare( $prev_version, '3.2', '<' ) ) {
		if ( is_multisite() ) {
			$same_settings = apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false );
		} else {
			$same_settings = false;
		}
		if ( $same_settings ) {
			$options = get_site_option( 'dd_recaptcha_admin_options' );
		} else {
			$options = get_option( 'dd_recaptcha_admin_options' );
		}
		if ( ! $options || ! is_array( $options ) ) {
			return;
		}
		$options['error_message'] = str_replace( __( '<strong>ERROR</strong>: ', 'duck-recaptcha-plugin' ), '', dd_recaptcha_get_option( 'error_message' ) );

		$enabled_forms = [];
		if ( ! empty( $options['login'] ) ) {
			$enabled_forms[] = 'login';
		}
		if ( ! empty( $options['registration'] ) ) {
			$enabled_forms[] = 'registration';
		}
		if ( ! empty( $options['ms_user_signup'] ) ) {
			$enabled_forms[] = 'ms_user_signup';
		}
		if ( ! empty( $options['lost_password'] ) ) {
			$enabled_forms[] = 'lost_password';
		}
		if ( ! empty( $options['reset_password'] ) ) {
			$enabled_forms[] = 'reset_password';
		}
		if ( ! empty( $options['comment'] ) ) {
			$enabled_forms[] = 'comment';
		}
		if ( ! empty( $options['bb_new'] ) ) {
			$enabled_forms[] = 'bbp_new';
		}
		if ( ! empty( $options['bb_reply'] ) ) {
			$enabled_forms[] = 'bbp_reply';
		}
		if ( ! empty( $options['wc_checkout'] ) ) {
			$enabled_forms[] = 'wc_checkout';
		}
		$options['enabled_forms'] = $enabled_forms;

		unset( $options['login'], $options['registration'], $options['ms_user_signup'], $options['lost_password'], $options['reset_password'], $options['comment'], $options['bb_new'], $options['bb_reply'], $options['wc_checkout'] );

		dd_recaptcha_update_option( $options );
	}
}

add_action( 'dd_recaptcha_plugin_update', 'dd_recaptcha_plugin_update_51' );

function dd_recaptcha_plugin_update_51( $prev_version ) {
	if ( version_compare( $prev_version, '5.1', '<' ) ) {
		$options = [];
		if ( 'invisible' === dd_recaptcha_get_option( 'size' ) ) {
			$options['size']            = 'normal';
			$options['captcha_version'] = 'v2_invisible';
		}

		dd_recaptcha_update_option( $options );
	}
}

function dd_recaptcha_get_option( $option, $default = '', $section = 'dd_recaptcha_admin_options' ) {

	if ( is_multisite() ) {
		$same_settings = apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false );
	} else {
		$same_settings = false;
	}
	if ( $same_settings ) {
		$options = get_site_option( $section );
	} else {
		$options = get_option( $section );
	}

	if ( isset( $options[ $option ] ) ) {
		$value      = $options[ $option ];
		$is_default = false;
	} else {
		$value      = $default;
		$is_default = true;
	}
	return apply_filters( 'dd_recaptcha_get_option', $value, $option, $default, $is_default );
}

function dd_recaptcha_update_option( $options, $value = '', $section = 'dd_recaptcha_admin_options' ) {

	if ( $options && ! is_array( $options ) ) {
		$options = array(
			$options => $value,
		);
	}
	if ( ! is_array( $options ) ) {
		return false;
	}

	if ( is_multisite() ) {
		$same_settings = apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false );
	} else {
		$same_settings = false;
	}
	if ( $same_settings ) {
		update_site_option( $section, wp_parse_args( $options, get_site_option( $section ) ) );
	} else {
		update_option( $section, wp_parse_args( $options, get_option( $section ) ) );
	}

	return true;
}

function dd_recaptcha_is_form_enabled( $form ) {
	if ( ! $form ) {
		return false;
	}
	$enabled_forms = dd_recaptcha_get_option( 'enabled_forms', array() );
	if ( ! is_array( $enabled_forms ) ) {
		return false;
	}
	return in_array( $form, $enabled_forms, true );
}

function dd_recaptcha_translation() {
	// SETUP TEXT DOMAIN FOR TRANSLATIONS
	load_plugin_textdomain( 'duck-recaptcha-plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}

function dd_recaptcha_login_enqueue_scripts() {

	if ( ! dd_recaptcha_get_option( 'remove_css' ) && 'normal' === dd_recaptcha_get_option( 'size', 'normal' ) && 'v2_checkbox' === dd_recaptcha_get_option( 'captcha_version', 'v2_checkbox' ) ) {
		wp_enqueue_style( 'dd-recaptcha-login-style', DD_RECAPTCHA_PLUGIN_URL . 'assets/css/style.css' );
	}
}

function dd_recaptcha_include_require_files() {
	$fep_files = array(
		'main' => 'dd-recaptcha-captcha-class.php',
	);
	if ( is_admin() ) {
		$fep_files['settings'] = 'admin/settings.php';
	}

	$fep_files = apply_filters( 'dd_recaptcha_include_files', $fep_files );

	foreach ( $fep_files as $fep_file ) {
		require_once $fep_file;
	}
}
add_action( 'wp_footer', 'dd_recaptcha_wp_footer' );
add_action( 'login_footer', 'dd_recaptcha_wp_footer' );

function dd_recaptcha_wp_footer() {
	dd_recaptcha_class::init()->footer_script();
}

add_action(
	'dd_recaptcha_captcha_form_field', function() {
		dd_recaptcha_captcha_form_field( true );
	}
);
add_shortcode( 'dd-recaptcha-captcha', 'dd_recaptcha_captcha_form_field' );

function dd_recaptcha_captcha_form_field( $echo = false ) {
	if ( $echo ) {
		dd_recaptcha_class::init()->form_field();
	} else {
		return dd_recaptcha_class::init()->form_field_return();
	}

}

function dd_recaptcha_verify_captcha( $response = false ) {
	return dd_recaptcha_class::init()->verify( $response );
}

add_filter( 'shake_error_codes', 'dd_recaptcha_add_shake_error_codes' );

function dd_recaptcha_add_shake_error_codes( $shake_error_codes ) {
	$shake_error_codes[] = 'dd_recaptcha_error';

	return $shake_error_codes;
}

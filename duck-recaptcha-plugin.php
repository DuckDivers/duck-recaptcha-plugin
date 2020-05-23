<?php
/*
Plugin Name: Advanced noCaptcha & invisible Captcha
Description: Show noCaptcha or invisible captcha in Comment Form, bbPress, BuddyPress, WooCommerce, CF7, Login, Register, Lost Password, Reset Password. Also can implement in any other form easily.
Version: 1.0
Author: Howard Ehrenberg
GitHub Plugin URI: https://github.com/DuckDivers/duck-recaptcha-plugin
Text Domain: advanced-nocaptcha-recaptcha
License: GPLv2 or later
WC tested up to: 4.0.1
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class DD_RECAPTCHA {

	private static $instance;

	private function __construct() {
		if ( function_exists( 'dd_recaptcha_get_option' ) ) {
			if ( ! function_exists( 'deactivate_plugins' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin.php';
			}
			deactivate_plugins( 'duck-recaptcha-plugin/duck-recaptcha-plugin.php' );
			return;
		}
		$this->constants();
		$this->includes();
		$this->actions();
		// $this->filters();
	}

	public static function init() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function constants() {
		define( 'DD_RECAPTCHA_PLUGIN_VERSION', '1.0.0' );
		define( 'DD_RECAPTCHA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		define( 'DD_RECAPTCHA_PLUGIN_URL', plugins_url( '/', __FILE__ ) );
		define( 'DD_RECAPTCHA_PLUGIN_FILE', __FILE__ );
	}

	private function includes() {
		require_once DD_RECAPTCHA_PLUGIN_DIR . 'functions.php';
	}

	private function actions() {
		add_action( 'after_setup_theme', 'dd_recaptcha_include_require_files' );
		//add_action( 'init', 'dd_recaptcha_translation' );
		add_action( 'login_enqueue_scripts', 'dd_recaptcha_login_enqueue_scripts' );
	}
} //END Class

DD_RECAPTCHA::init();

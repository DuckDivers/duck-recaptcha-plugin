<?php

class DD_RECAPTCHA_Settings {

	private static $instance;

	public static function init() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	function actions_filters() {
		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'admin_init', array( $this, 'settings_save' ), 99 );
		add_filter( 'plugin_action_links_' . plugin_basename( DD_RECAPTCHA_PLUGIN_FILE ), array( $this, 'add_settings_link' ) );
		add_action('admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		if ( is_multisite() ) {
			$same_settings = apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false );
		} else {
			$same_settings = false;
		}
		if ( $same_settings ) {
			add_action( 'network_admin_menu', array( $this, 'menu_page' ) );
		} else {
			add_action( 'admin_menu', array( $this, 'menu_page' ) );
		}

	}

	function admin_enqueue_scripts() {
		wp_register_script( 'dd-recaptcha-admin', DD_RECAPTCHA_PLUGIN_URL . 'assets/js/admin.js', array( 'jquery' ), DD_RECAPTCHA_PLUGIN_VERSION, true );
	}

	function admin_init() {
		register_setting( 'dd_recaptcha_admin_options', 'dd_recaptcha_admin_options', array( $this, 'options_sanitize' ) );
		foreach ( $this->get_sections() as $section_id => $section ) {
			add_settings_section( $section_id, $section['section_title'], ! empty( $section['section_callback'] ) ? $section['section_callback'] : null, 'dd_recaptcha_admin_options' );
		}
		foreach ( $this->get_fields() as $field_id => $field ) {
			$args = wp_parse_args(
				$field, array(
					'id'         => $field_id,
					'label'      => '',
					'cb_label'   => '',
					'type'       => 'text',
					'class'      => 'regular-text',
					'section_id' => '',
					'desc'       => '',
					'std'        => '',
				)
			);
			add_settings_field( $args['id'], $args['label'], ! empty( $args['callback'] ) ? $args['callback'] : array( $this, 'callback' ), 'dd_recaptcha_admin_options', $args['section_id'], $args );
		}
	}

	function get_sections() {
		$sections = array(
			'google_keys' => array(
				'section_title'    => __( 'Google Keys', 'duck-recaptcha-plugin' ),
				'section_callback' => function() {
					printf( __( 'Get reCaptcha keys from <a href="%s">Google</a>. Make sure to get keys for your selected captcha version.', 'duck-recaptcha-plugin' ), 'https://www.google.com/recaptcha/admin' );
				},
			),
			'forms'       => array(
				'section_title' => __( 'Forms', 'duck-recaptcha-plugin' ),
			),
			'other'       => array(
				'section_title' => __( 'Other Settings', 'duck-recaptcha-plugin' ),
			),
		);
		return apply_filters( 'dd_recaptcha_settings_sections', $sections );
	}

	function get_fields() {
		$score_values = [];
		for ( $i = 0.0; $i <= 1; $i += 0.1 ) {
			$score_values[ "$i" ] = number_format_i18n( $i, 1 );
		}
		$fields = array(
			'captcha_version'            => array(
				'label'      => __( 'Version', 'duck-recaptcha-plugin' ),
				'section_id' => 'google_keys',
				'type'       => 'select',
				'class'      => 'regular',
				'std'        => 'v2_checkbox',
				'options'    => array(
					'v2_checkbox'  => __( 'V2 "I\'m not a robot"', 'duck-recaptcha-plugin' ),
					'v2_invisible' => __( 'V2 Invisible', 'duck-recaptcha-plugin' ),
					'v3'           => __( 'V3', 'duck-recaptcha-plugin' ),
				),
				'desc'       => __( 'Select your reCaptcha version. Make sure to use site key and secret key for your selected version.', 'duck-recaptcha-plugin' ),
			),
			'site_key'           => array(
				'label'      => __( 'Site Key', 'duck-recaptcha-plugin' ),
				'section_id' => 'google_keys',
			),
			'secret_key'         => array(
				'label'      => __( 'Secret Key', 'duck-recaptcha-plugin' ),
				'section_id' => 'google_keys',
			),
			'enabled_forms'      => array(
				'label'      => __( 'Enabled Forms', 'duck-recaptcha-plugin' ),
				'section_id' => 'forms',
				'type'       => 'multicheck',
				'class'      => 'checkbox',
				'options'    => array(
					'login'          => __( 'Login Form', 'duck-recaptcha-plugin' ),
					'registration'   => __( 'Registration Form', 'duck-recaptcha-plugin' ),
					'ms_user_signup' => __( 'Multisite User Signup Form', 'duck-recaptcha-plugin' ),
					'lost_password'  => __( 'Lost Password Form', 'duck-recaptcha-plugin' ),
					'reset_password' => __( 'Reset Password Form', 'duck-recaptcha-plugin' ),
					'comment'        => __( 'Comment Form', 'duck-recaptcha-plugin' ),
					'bbp_new'         => __( 'bbPress New topic', 'duck-recaptcha-plugin' ),
					'bbp_reply'       => __( 'bbPress reply to topic', 'duck-recaptcha-plugin' ),
					'bp_register'       => __( 'BuddyPress register', 'duck-recaptcha-plugin' ),
					'wc_checkout'    => __( 'WooCommerce Checkout', 'duck-recaptcha-plugin' ),
				),
				'desc'       => sprintf( __( 'For other forms see <a href="%s">Instructions</a>', 'duck-recaptcha-plugin' ), esc_url( admin_url( 'admin.php?page=dd-recaptcha-instruction' ) ) ),
			),
			'error_message'      => array(
				'label'      => __( 'Error Message', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'std'        => __( 'Please solve Captcha correctly', 'duck-recaptcha-plugin' ),
			),
			'language'           => array(
				'label'      => __( 'Captcha Language', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular',
				'options'    => array(
					''       => __( 'Auto Detect', 'duck-recaptcha-plugin' ),
					'ar'     => __( 'Arabic', 'duck-recaptcha-plugin' ),
					'bg'     => __( 'Bulgarian', 'duck-recaptcha-plugin' ),
					'ca'     => __( 'Catalan', 'duck-recaptcha-plugin' ),
					'zh-CN'  => __( 'Chinese (Simplified)', 'duck-recaptcha-plugin' ),
					'zh-CN'  => __( 'Chinese (Traditional)', 'duck-recaptcha-plugin' ),
					'hr'     => __( 'Croatian', 'duck-recaptcha-plugin' ),
					'cs'     => __( 'Czech', 'duck-recaptcha-plugin' ),
					'da'     => __( 'Danish', 'duck-recaptcha-plugin' ),
					'nl'     => __( 'Dutch', 'duck-recaptcha-plugin' ),
					'en-GB'  => __( 'English (UK)', 'duck-recaptcha-plugin' ),
					'en'     => __( 'English (US)', 'duck-recaptcha-plugin' ),
					'fil'    => __( 'Filipino', 'duck-recaptcha-plugin' ),
					'fi'     => __( 'Finnish', 'duck-recaptcha-plugin' ),
					'fr'     => __( 'French', 'duck-recaptcha-plugin' ),
					'fr-CA'  => __( 'French (Canadian)', 'duck-recaptcha-plugin' ),
					'de'     => __( 'German', 'duck-recaptcha-plugin' ),
					'de-AT'  => __( 'German (Austria)', 'duck-recaptcha-plugin' ),
					'de-CH'  => __( 'German (Switzerland)', 'duck-recaptcha-plugin' ),
					'el'     => __( 'Greek', 'duck-recaptcha-plugin' ),
					'iw'     => __( 'Hebrew', 'duck-recaptcha-plugin' ),
					'hi'     => __( 'Hindi', 'duck-recaptcha-plugin' ),
					'hu'     => __( 'Hungarain', 'duck-recaptcha-plugin' ),
					'id'     => __( 'Indonesian', 'duck-recaptcha-plugin' ),
					'it'     => __( 'Italian', 'duck-recaptcha-plugin' ),
					'ja'     => __( 'Japanese', 'duck-recaptcha-plugin' ),
					'ko'     => __( 'Korean', 'duck-recaptcha-plugin' ),
					'lv'     => __( 'Latvian', 'duck-recaptcha-plugin' ),
					'lt'     => __( 'Lithuanian', 'duck-recaptcha-plugin' ),
					'no'     => __( 'Norwegian', 'duck-recaptcha-plugin' ),
					'fa'     => __( 'Persian', 'duck-recaptcha-plugin' ),
					'pl'     => __( 'Polish', 'duck-recaptcha-plugin' ),
					'pt'     => __( 'Portuguese', 'duck-recaptcha-plugin' ),
					'pt-BR'  => __( 'Portuguese (Brazil)', 'duck-recaptcha-plugin' ),
					'pt-PT'  => __( 'Portuguese (Portugal)', 'duck-recaptcha-plugin' ),
					'ro'     => __( 'Romanian', 'duck-recaptcha-plugin' ),
					'ru'     => __( 'Russian', 'duck-recaptcha-plugin' ),
					'sr'     => __( 'Serbian', 'duck-recaptcha-plugin' ),
					'sk'     => __( 'Slovak', 'duck-recaptcha-plugin' ),
					'sl'     => __( 'Slovenian', 'duck-recaptcha-plugin' ),
					'es'     => __( 'Spanish', 'duck-recaptcha-plugin' ),
					'es-419' => __( 'Spanish (Latin America)', 'duck-recaptcha-plugin' ),
					'sv'     => __( 'Swedish', 'duck-recaptcha-plugin' ),
					'th'     => __( 'Thai', 'duck-recaptcha-plugin' ),
					'tr'     => __( 'Turkish', 'duck-recaptcha-plugin' ),
					'uk'     => __( 'Ukrainian', 'duck-recaptcha-plugin' ),
					'vi'     => __( 'Vietnamese', 'duck-recaptcha-plugin' ),
				),
			),
			'theme'              => array(
				'label'      => __( 'Theme', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden duck-recaptcha-show-field-for-v2_checkbox duck-recaptcha-show-field-for-v2_invisible',
				'std'        => 'light',
				'options'    => array(
					'light' => __( 'Light', 'duck-recaptcha-plugin' ),
					'dark'  => __( 'Dark', 'duck-recaptcha-plugin' ),
				),
			),
			'size'               => array(
				'label'      => __( 'Size', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden duck-recaptcha-show-field-for-v2_checkbox',
				'std'        => 'normal',
				'options'    => array(
					'normal'    => __( 'Normal', 'duck-recaptcha-plugin' ),
					'compact'   => __( 'Compact', 'duck-recaptcha-plugin' ),
				),
			),
			'badge'              => array(
				'label'      => __( 'Badge', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden duck-recaptcha-show-field-for-v2_invisible',
				'std'        => 'bottomright',
				'options'    => array(
					'bottomright' => __( 'Bottom Right', 'duck-recaptcha-plugin' ),
					'bottomleft'  => __( 'Bottom Left', 'duck-recaptcha-plugin' ),
					'inline'      => __( 'Inline', 'duck-recaptcha-plugin' ),
				),
				'desc'       => __( 'Badge shows for invisible captcha', 'duck-recaptcha-plugin' ),
			),
			'failed_login_allow' => array(
				'label'             => __( 'Failed login Captcha', 'duck-recaptcha-plugin' ),
				'section_id'        => 'other',
				'std'               => 0,
				'type'              => 'number',
				'class'             => 'regular-number',
				'sanitize_callback' => 'absint',
				'desc'              => __( 'Show login Captcha after how many failed attempts? 0 = show always', 'duck-recaptcha-plugin' ),
			),
			'v3_script_load'     => array(
				'label'      => __( 'v3 Script Load', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden duck-recaptcha-show-field-for-v3',
				'std'        => 'all_pages',
				'options'    => array(
					'all_pages'  => __( 'All Pages', 'duck-recaptcha-plugin' ),
					'form_pages' => __( 'Form Pages', 'duck-recaptcha-plugin' ),
				),
				'desc'       => __( 'Loading in All Pages help google for analytics', 'duck-recaptcha-plugin' ),
			),
			'score'              => array(
				'label'      => __( 'Captcha Score', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden duck-recaptcha-show-field-for-v3',
				'std'        => '0.5',
				'options'    => $score_values,
				'desc'       => __( 'Higher means more sensitive', 'duck-recaptcha-plugin' ),
			),
			'whitelisted_ips'              => array(
				'label'      => __( 'Whitelisted IPs', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'textarea',
				'class'      => 'regular',
				'desc'       => __( 'One per line', 'duck-recaptcha-plugin' ),
			),
			'loggedin_hide'      => array(
				'label'      => __( 'Logged in Hide', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox',
				'cb_label'   => __( 'Hide Captcha for logged in users?', 'duck-recaptcha-plugin' ),
			),
			'remove_css'         => array(
				'label'      => __( 'Remove CSS', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox hidden duck-recaptcha-show-field-for-v2_checkbox',
				'cb_label'   => __( "Remove this plugin's css from login page?", 'duck-recaptcha-plugin' ),
				'desc'       => __( 'This css increase login page width to adjust with Captcha width.', 'duck-recaptcha-plugin' ),
			),
			'no_js'              => array(
				'label'      => __( 'No JS Captcha', 'duck-recaptcha-plugin' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox hidden duck-recaptcha-show-field-for-v2_checkbox',
				'cb_label'   => __( 'Show captcha if javascript disabled?', 'duck-recaptcha-plugin' ),
				'desc'       => __( 'If JavaScript is a requirement for your site, we advise that you do NOT check this.', 'duck-recaptcha-plugin' ),
			),
		);

		return apply_filters( 'dd_recaptcha_settings_fields', $fields );
	}

	function callback( $field ) {
		$attrib = '';
		if ( ! empty( $field['required'] ) ) {
			$attrib .= ' required = "required"';
		}
		if ( ! empty( $field['readonly'] ) ) {
			$attrib .= ' readonly = "readonly"';
		}
		if ( ! empty( $field['disabled'] ) ) {
			$attrib .= ' disabled = "disabled"';
		}
		if ( ! empty( $field['minlength'] ) ) {
			$attrib .= ' minlength = "' . absint( $field['minlength'] ) . '"';
		}
		if ( ! empty( $field['maxlength'] ) ) {
			$attrib .= ' maxlength = "' . absint( $field['maxlength'] ) . '"';
		}

		$value = dd_recaptcha_get_option( $field['id'], $field['std'] );

		switch ( $field['type'] ) {
			case 'text':
			case 'email':
			case 'url':
			case 'number':
			case 'hidden':
			case 'submit':
				printf(
					'<input type="%1$s" id="dd_recaptcha_admin_options_%2$s" class="%3$s" name="dd_recaptcha_admin_options[%4$s]" placeholder="%5$s" value="%6$s"%7$s />',
					esc_attr( $field['type'] ),
					esc_attr( $field['id'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['id'] ),
					isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
					esc_attr( $value ),
					$attrib
				);
				break;
			case 'textarea':
					printf( '<textarea id="dd_recaptcha_admin_options_%1$s" class="%2$s" name="dd_recaptcha_admin_options[%3$s]" placeholder="%4$s" %5$s >%6$s</textarea>',
						esc_attr( $field['id'] ),
						esc_attr( $field['class'] ),
						esc_attr( $field['id'] ),
						isset( $field['placeholder'] ) ? esc_attr( $field['placeholder'] ) : '',
						$attrib,
						esc_textarea( $value )
					);
					break;
			case 'checkbox':
				printf( '<input type="hidden" name="dd_recaptcha_admin_options[%s]" value="" />', esc_attr( $field['id'] ) );
				printf(
					'<label><input type="%1$s" id="dd_recaptcha_admin_options_%2$s" class="%3$s" name="dd_recaptcha_admin_options[%4$s]" value="%5$s"%6$s /> %7$s</label>',
					'checkbox',
					esc_attr( $field['id'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['id'] ),
					'1',
					checked( $value, '1', false ),
					esc_attr( $field['cb_label'] )
				);
				break;
			case 'multicheck':
				printf( '<input type="hidden" name="dd_recaptcha_admin_options[%s][]" value="" />', esc_attr( $field['id'] ) );
				foreach ( $field['options'] as $key => $label ) {
					printf(
						'<label><input type="%1$s" id="dd_recaptcha_admin_options_%2$s_%5$s" class="%3$s" name="dd_recaptcha_admin_options[%4$s][]" value="%5$s"%6$s /> %7$s</label><br>',
						'checkbox',
						esc_attr( $field['id'] ),
						esc_attr( $field['class'] ),
						esc_attr( $field['id'] ),
						esc_attr( $key ),
						checked( in_array( $key, (array) $value ), true, false ),
						esc_attr( $label )
					);
				}
				break;
			case 'select':
				printf(
					'<select id="dd_recaptcha_admin_options_%1$s" class="%2$s" name="dd_recaptcha_admin_options[%1$s]">',
					esc_attr( $field['id'] ),
					esc_attr( $field['class'] ),
					esc_attr( $field['id'] )
				);
				foreach ( $field['options'] as $key => $label ) {
					printf(
						'<option value="%1$s"%2$s>%3$s</option>',
						esc_attr( $key ),
						selected( $value, $key, false ),
						esc_attr( $label )
					);
				}
				printf( '</select>' );
				break;
			case 'html':
				echo $field['std'];
				break;

			default:
				printf( __( 'No hook defined for %s', 'duck-recaptcha-plugin' ), esc_html( $field['type'] ) );
				break;
		}
		if ( ! empty( $field['desc'] ) ) {
			printf( '<p class="description">%s</p>', $field['desc'] );
		}
	}

	function options_sanitize( $value ) {
		if ( ! $value || ! is_array( $value ) ) {
			return $value;
		}
		$fields = $this->get_fields();

		foreach ( $value as $option_slug => $option_value ) {
			if ( isset( $fields[ $option_slug ] ) && ! empty( $fields[ $option_slug ]['sanitize_callback'] ) ) {
				$value[ $option_slug ] = call_user_func( $fields[ $option_slug ]['sanitize_callback'], $option_value );
			}
		}
		return $value;
	}

	function menu_page() {
		add_options_page( __( 'Advanced noCaptcha & invisible captcha Settings', 'duck-recaptcha-plugin' ), __( 'Advanced noCaptcha & invisible captcha', 'duck-recaptcha-plugin' ), 'manage_options', 'dd-recaptcha-admin-settings', array( $this, 'admin_settings' ) );
		add_submenu_page( 'dd-recaptcha-non-exist-menu', 'Advanced noCaptcha reCaptcha - ' . __( 'Instruction', 'duck-recaptcha-plugin' ), __( 'Instruction', 'duck-recaptcha-plugin' ), 'manage_options', 'dd-recaptcha-instruction', array( $this, 'instruction_page' ) );

	}

	function settings_save() {
		if ( current_user_can( 'manage_options' ) && isset( $_POST['dd_recaptcha_admin_options'] ) && isset( $_POST['action'] ) && 'update' === $_POST['action'] && isset( $_GET['page'] ) && 'dd-recaptcha-admin-settings' === $_GET['page'] ) {
			check_admin_referer( 'dd_recaptcha_admin_options-options' );

			$value = wp_unslash( $_POST['dd_recaptcha_admin_options'] );
			if ( ! is_array( $value ) ) {
				$value = [];
			}
			dd_recaptcha_update_option( $value );

			wp_safe_redirect( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings&updated=true' ) );
			exit;
		}
	}

	function admin_settings() {
		wp_enqueue_script( 'dd-recaptcha-admin' );
		?>
		<div class="wrap">
			<div id="mainSettings">
				<h2><?php _e( 'Duck Diver reCaptcha & invisible reCaptcha Settings', 'duck-recaptcha-plugin' ); ?></h2>
				<div id="post-body" class="columns-2">
					<div id="post-body-content">
						<div id="tab_container">
							<?php settings_errors( 'dd_recaptcha_admin_options' ); ?>
							<form method="post" action="<?php echo esc_attr( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) ); ?>">
								<?php
								settings_fields( 'dd_recaptcha_admin_options' );
								do_settings_sections( 'dd_recaptcha_admin_options' );
								do_action( 'dd_recaptcha_admin_setting_form' );
								submit_button();
								?>
							</form>
						</div><!-- #tab_container-->
					</div><!-- #post-body-content-->
				</div><!-- #post-body -->
				<br class="clear" />
			</div><!-- #poststuff -->
		</div><!-- .wrap -->
		<?php
	}

	function instruction_page() {
		?>
		<div class="wrap">
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-2">
					<h2><?php _e( 'Advanced noCaptcha reCaptcha Setup Instruction', 'duck-recaptcha-plugin' ); ?></h2>
					<!-- main content -->
					<div id="post-body-content">
						<div class='postbox'>
							<div class='inside'>
								<div><?php printf( __( 'Get your site key and secret key from <a href="%s" target="_blank">GOOGLE</a> if you do not have already.', 'duck-recaptcha-plugin' ), esc_url( 'https://www.google.com/recaptcha/admin' ) ); ?></div>
								<div><?php printf( __( 'Goto %s page of this plugin and set up as you need. and ENJOY...', 'duck-recaptcha-plugin' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) ) . '">' . esc_html__( 'Settings', 'duck-recaptcha-plugin' ) . '</a>' ); ?></div>

								<h3><?php _e( 'Implement noCaptcha in Contact Form 7', 'duck-recaptcha-plugin' ); ?></h3>
								<div><?php printf( __( 'To show noCaptcha use %s', 'duck-recaptcha-plugin' ), '<code>[dd_recaptcha g-recaptcha-response]</code>' ); ?></div>

								<h3><?php _e( 'Implement noCaptcha in WooCommerce', 'duck-recaptcha-plugin' ); ?></h3>
								<div><?php _e( 'If Login Form, Registration Form, Lost Password Form, Reset Password Form is selected in SETTINGS page of this plugin they will show and verify Captcha in WooCommerce respective forms also.', 'duck-recaptcha-plugin' ); ?></div>

								<h3><?php _e( 'If you want to implement noCaptcha in any other custom form', 'duck-recaptcha-plugin' ); ?></h3>
								<div><?php printf( __( 'To show noCaptcha in a form use %1$s OR %2$s', 'duck-recaptcha-plugin' ), "<code>do_action( 'dd_recaptcha_captcha_form_field' )</code>", '<code>[dd-recaptcha-captcha]</code>' ); ?></div>
								<div><?php printf( __( 'To verify use %s. It will return true on success otherwise false.', 'duck-recaptcha-plugin' ), '<code>dd_recaptcha_verify_captcha()</code>' ); ?></div>
								<div><?php printf( __( 'For paid support pleasse visit <a href="%s" target="_blank">Advanced noCaptcha reCaptcha</a>', 'duck-recaptcha-plugin' ), esc_url( 'https://www.shamimsplugins.com/hire/' ) ); ?></div>
							</div>
						</div>
						<div><a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) ); ?>"><?php esc_html_e( 'Back to Settings', 'duck-recaptcha-plugin' ); ?></a></div>
                    </div>
				</div>
				<br class="clear" />
			</div>
		</div>
		<?php
	}


	function add_settings_link( $links ) {
		// add settings link in plugins page
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) . '">' . __( 'Settings', 'duck-recaptcha-plugin' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}


} //END CLASS

add_action( 'wp_loaded', array( DD_RECAPTCHA_Settings::init(), 'actions_filters' ) );

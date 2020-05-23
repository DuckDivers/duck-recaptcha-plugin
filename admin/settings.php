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
				'section_title'    => __( 'Google Keys', 'advanced-nocaptcha-recaptcha' ),
				'section_callback' => function() {
					printf( __( 'Get reCaptcha keys from <a href="%s">Google</a>. Make sure to get keys for your selected captcha version.', 'advanced-nocaptcha-recaptcha' ), 'https://www.google.com/recaptcha/admin' );
				},
			),
			'forms'       => array(
				'section_title' => __( 'Forms', 'advanced-nocaptcha-recaptcha' ),
			),
			'other'       => array(
				'section_title' => __( 'Other Settings', 'advanced-nocaptcha-recaptcha' ),
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
				'label'      => __( 'Version', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'google_keys',
				'type'       => 'select',
				'class'      => 'regular',
				'std'        => 'v2_checkbox',
				'options'    => array(
					'v2_checkbox'  => __( 'V2 "I\'m not a robot"', 'advanced-nocaptcha-recaptcha' ),
					'v2_invisible' => __( 'V2 Invisible', 'advanced-nocaptcha-recaptcha' ),
					'v3'           => __( 'V3', 'advanced-nocaptcha-recaptcha' ),
				),
				'desc'       => __( 'Select your reCaptcha version. Make sure to use site key and secret key for your selected version.', 'advanced-nocaptcha-recaptcha' ),
			),
			'site_key'           => array(
				'label'      => __( 'Site Key', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'google_keys',
			),
			'secret_key'         => array(
				'label'      => __( 'Secret Key', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'google_keys',
			),
			'enabled_forms'      => array(
				'label'      => __( 'Enabled Forms', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'forms',
				'type'       => 'multicheck',
				'class'      => 'checkbox',
				'options'    => array(
					'login'          => __( 'Login Form', 'advanced-nocaptcha-recaptcha' ),
					'registration'   => __( 'Registration Form', 'advanced-nocaptcha-recaptcha' ),
					'ms_user_signup' => __( 'Multisite User Signup Form', 'advanced-nocaptcha-recaptcha' ),
					'lost_password'  => __( 'Lost Password Form', 'advanced-nocaptcha-recaptcha' ),
					'reset_password' => __( 'Reset Password Form', 'advanced-nocaptcha-recaptcha' ),
					'comment'        => __( 'Comment Form', 'advanced-nocaptcha-recaptcha' ),
					'bbp_new'         => __( 'bbPress New topic', 'advanced-nocaptcha-recaptcha' ),
					'bbp_reply'       => __( 'bbPress reply to topic', 'advanced-nocaptcha-recaptcha' ),
					'bp_register'       => __( 'BuddyPress register', 'advanced-nocaptcha-recaptcha' ),
					'wc_checkout'    => __( 'WooCommerce Checkout', 'advanced-nocaptcha-recaptcha' ),
				),
				'desc'       => sprintf( __( 'For other forms see <a href="%s">Instruction</a>', 'advanced-nocaptcha-recaptcha' ), esc_url( admin_url( 'admin.php?page=dd-recaptcha-instruction' ) ) ),
			),
			'error_message'      => array(
				'label'      => __( 'Error Message', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'std'        => __( 'Please solve Captcha correctly', 'advanced-nocaptcha-recaptcha' ),
			),
			'language'           => array(
				'label'      => __( 'Captcha Language', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular',
				'options'    => array(
					''       => __( 'Auto Detect', 'advanced-nocaptcha-recaptcha' ),
					'ar'     => __( 'Arabic', 'advanced-nocaptcha-recaptcha' ),
					'bg'     => __( 'Bulgarian', 'advanced-nocaptcha-recaptcha' ),
					'ca'     => __( 'Catalan', 'advanced-nocaptcha-recaptcha' ),
					'zh-CN'  => __( 'Chinese (Simplified)', 'advanced-nocaptcha-recaptcha' ),
					'zh-CN'  => __( 'Chinese (Traditional)', 'advanced-nocaptcha-recaptcha' ),
					'hr'     => __( 'Croatian', 'advanced-nocaptcha-recaptcha' ),
					'cs'     => __( 'Czech', 'advanced-nocaptcha-recaptcha' ),
					'da'     => __( 'Danish', 'advanced-nocaptcha-recaptcha' ),
					'nl'     => __( 'Dutch', 'advanced-nocaptcha-recaptcha' ),
					'en-GB'  => __( 'English (UK)', 'advanced-nocaptcha-recaptcha' ),
					'en'     => __( 'English (US)', 'advanced-nocaptcha-recaptcha' ),
					'fil'    => __( 'Filipino', 'advanced-nocaptcha-recaptcha' ),
					'fi'     => __( 'Finnish', 'advanced-nocaptcha-recaptcha' ),
					'fr'     => __( 'French', 'advanced-nocaptcha-recaptcha' ),
					'fr-CA'  => __( 'French (Canadian)', 'advanced-nocaptcha-recaptcha' ),
					'de'     => __( 'German', 'advanced-nocaptcha-recaptcha' ),
					'de-AT'  => __( 'German (Austria)', 'advanced-nocaptcha-recaptcha' ),
					'de-CH'  => __( 'German (Switzerland)', 'advanced-nocaptcha-recaptcha' ),
					'el'     => __( 'Greek', 'advanced-nocaptcha-recaptcha' ),
					'iw'     => __( 'Hebrew', 'advanced-nocaptcha-recaptcha' ),
					'hi'     => __( 'Hindi', 'advanced-nocaptcha-recaptcha' ),
					'hu'     => __( 'Hungarain', 'advanced-nocaptcha-recaptcha' ),
					'id'     => __( 'Indonesian', 'advanced-nocaptcha-recaptcha' ),
					'it'     => __( 'Italian', 'advanced-nocaptcha-recaptcha' ),
					'ja'     => __( 'Japanese', 'advanced-nocaptcha-recaptcha' ),
					'ko'     => __( 'Korean', 'advanced-nocaptcha-recaptcha' ),
					'lv'     => __( 'Latvian', 'advanced-nocaptcha-recaptcha' ),
					'lt'     => __( 'Lithuanian', 'advanced-nocaptcha-recaptcha' ),
					'no'     => __( 'Norwegian', 'advanced-nocaptcha-recaptcha' ),
					'fa'     => __( 'Persian', 'advanced-nocaptcha-recaptcha' ),
					'pl'     => __( 'Polish', 'advanced-nocaptcha-recaptcha' ),
					'pt'     => __( 'Portuguese', 'advanced-nocaptcha-recaptcha' ),
					'pt-BR'  => __( 'Portuguese (Brazil)', 'advanced-nocaptcha-recaptcha' ),
					'pt-PT'  => __( 'Portuguese (Portugal)', 'advanced-nocaptcha-recaptcha' ),
					'ro'     => __( 'Romanian', 'advanced-nocaptcha-recaptcha' ),
					'ru'     => __( 'Russian', 'advanced-nocaptcha-recaptcha' ),
					'sr'     => __( 'Serbian', 'advanced-nocaptcha-recaptcha' ),
					'sk'     => __( 'Slovak', 'advanced-nocaptcha-recaptcha' ),
					'sl'     => __( 'Slovenian', 'advanced-nocaptcha-recaptcha' ),
					'es'     => __( 'Spanish', 'advanced-nocaptcha-recaptcha' ),
					'es-419' => __( 'Spanish (Latin America)', 'advanced-nocaptcha-recaptcha' ),
					'sv'     => __( 'Swedish', 'advanced-nocaptcha-recaptcha' ),
					'th'     => __( 'Thai', 'advanced-nocaptcha-recaptcha' ),
					'tr'     => __( 'Turkish', 'advanced-nocaptcha-recaptcha' ),
					'uk'     => __( 'Ukrainian', 'advanced-nocaptcha-recaptcha' ),
					'vi'     => __( 'Vietnamese', 'advanced-nocaptcha-recaptcha' ),
				),
			),
			'theme'              => array(
				'label'      => __( 'Theme', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden dd-recaptcha-show-field-for-v2_checkbox dd-recaptcha-show-field-for-v2_invisible',
				'std'        => 'light',
				'options'    => array(
					'light' => __( 'Light', 'advanced-nocaptcha-recaptcha' ),
					'dark'  => __( 'Dark', 'advanced-nocaptcha-recaptcha' ),
				),
			),
			'size'               => array(
				'label'      => __( 'Size', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden dd-recaptcha-show-field-for-v2_checkbox',
				'std'        => 'normal',
				'options'    => array(
					'normal'    => __( 'Normal', 'advanced-nocaptcha-recaptcha' ),
					'compact'   => __( 'Compact', 'advanced-nocaptcha-recaptcha' ),
				),
			),
			'badge'              => array(
				'label'      => __( 'Badge', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden dd-recaptcha-show-field-for-v2_invisible',
				'std'        => 'bottomright',
				'options'    => array(
					'bottomright' => __( 'Bottom Right', 'advanced-nocaptcha-recaptcha' ),
					'bottomleft'  => __( 'Bottom Left', 'advanced-nocaptcha-recaptcha' ),
					'inline'      => __( 'Inline', 'advanced-nocaptcha-recaptcha' ),
				),
				'desc'       => __( 'Badge shows for invisible captcha', 'advanced-nocaptcha-recaptcha' ),
			),
			'failed_login_allow' => array(
				'label'             => __( 'Failed login Captcha', 'advanced-nocaptcha-recaptcha' ),
				'section_id'        => 'other',
				'std'               => 0,
				'type'              => 'number',
				'class'             => 'regular-number',
				'sanitize_callback' => 'absint',
				'desc'              => __( 'Show login Captcha after how many failed attempts? 0 = show always', 'advanced-nocaptcha-recaptcha' ),
			),
			'v3_script_load'     => array(
				'label'      => __( 'v3 Script Load', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden dd-recaptcha-show-field-for-v3',
				'std'        => 'all_pages',
				'options'    => array(
					'all_pages'  => __( 'All Pages', 'advanced-nocaptcha-recaptcha' ),
					'form_pages' => __( 'Form Pages', 'advanced-nocaptcha-recaptcha' ),
				),
				'desc'       => __( 'Loading in All Pages help google for analytics', 'advanced-nocaptcha-recaptcha' ),
			),
			'score'              => array(
				'label'      => __( 'Captcha Score', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'select',
				'class'      => 'regular hidden dd-recaptcha-show-field-for-v3',
				'std'        => '0.5',
				'options'    => $score_values,
				'desc'       => __( 'Higher means more sensitive', 'advanced-nocaptcha-recaptcha' ),
			),
			'whitelisted_ips'              => array(
				'label'      => __( 'Whitelisted IPs', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'textarea',
				'class'      => 'regular',
				'desc'       => __( 'One per line', 'advanced-nocaptcha-recaptcha' ),
			),
			'loggedin_hide'      => array(
				'label'      => __( 'Logged in Hide', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox',
				'cb_label'   => __( 'Hide Captcha for logged in users?', 'advanced-nocaptcha-recaptcha' ),
			),
			'remove_css'         => array(
				'label'      => __( 'Remove CSS', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox hidden dd-recaptcha-show-field-for-v2_checkbox',
				'cb_label'   => __( "Remove this plugin's css from login page?", 'advanced-nocaptcha-recaptcha' ),
				'desc'       => __( 'This css increase login page width to adjust with Captcha width.', 'advanced-nocaptcha-recaptcha' ),
			),
			'no_js'              => array(
				'label'      => __( 'No JS Captcha', 'advanced-nocaptcha-recaptcha' ),
				'section_id' => 'other',
				'type'       => 'checkbox',
				'class'      => 'checkbox hidden dd-recaptcha-show-field-for-v2_checkbox',
				'cb_label'   => __( 'Show captcha if javascript disabled?', 'advanced-nocaptcha-recaptcha' ),
				'desc'       => __( 'If JavaScript is a requirement for your site, we advise that you do NOT check this.', 'advanced-nocaptcha-recaptcha' ),
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
				printf( __( 'No hook defined for %s', 'advanced-nocaptcha-recaptcha' ), esc_html( $field['type'] ) );
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
		add_options_page( __( 'Advanced noCaptcha & invisible captcha Settings', 'advanced-nocaptcha-recaptcha' ), __( 'Advanced noCaptcha & invisible captcha', 'advanced-nocaptcha-recaptcha' ), 'manage_options', 'dd-recaptcha-admin-settings', array( $this, 'admin_settings' ) );
		add_submenu_page( 'dd-recaptcha-non-exist-menu', 'Advanced noCaptcha reCaptcha - ' . __( 'Instruction', 'advanced-nocaptcha-recaptcha' ), __( 'Instruction', 'advanced-nocaptcha-recaptcha' ), 'manage_options', 'dd-recaptcha-instruction', array( $this, 'instruction_page' ) );

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
			<div id="poststuff">
				<h2><?php _e( 'Advanced noCaptcha & invisible captcha Settings', 'advanced-nocaptcha-recaptcha' ); ?></h2>
				<div id="post-body" class="metabox-holder columns-2">
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
					<h2><?php _e( 'Advanced noCaptcha reCaptcha Setup Instruction', 'advanced-nocaptcha-recaptcha' ); ?></h2>
					<!-- main content -->
					<div id="post-body-content">
						<div class='postbox'>
							<div class='inside'>
								<div><?php printf( __( 'Get your site key and secret key from <a href="%s" target="_blank">GOOGLE</a> if you do not have already.', 'advanced-nocaptcha-recaptcha' ), esc_url( 'https://www.google.com/recaptcha/admin' ) ); ?></div>
								<div><?php printf( __( 'Goto %s page of this plugin and set up as you need. and ENJOY...', 'advanced-nocaptcha-recaptcha' ), '<a href="' . esc_url( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) ) . '">' . esc_html__( 'Settings', 'advanced-nocaptcha-recaptcha' ) . '</a>' ); ?></div>
				
								<h3><?php _e( 'Implement noCaptcha in Contact Form 7', 'advanced-nocaptcha-recaptcha' ); ?></h3>
								<div><?php printf( __( 'To show noCaptcha use %s', 'advanced-nocaptcha-recaptcha' ), '<code>[dd_recaptcha_nocaptcha g-recaptcha-response]</code>' ); ?></div>
				
								<h3><?php _e( 'Implement noCaptcha in WooCommerce', 'advanced-nocaptcha-recaptcha' ); ?></h3>
								<div><?php _e( 'If Login Form, Registration Form, Lost Password Form, Reset Password Form is selected in SETTINGS page of this plugin they will show and verify Captcha in WooCommerce respective forms also.', 'advanced-nocaptcha-recaptcha' ); ?></div>
								
								<h3><?php _e( 'If you want to implement noCaptcha in any other custom form', 'advanced-nocaptcha-recaptcha' ); ?></h3>
								<div><?php printf( __( 'To show noCaptcha in a form use %1$s OR %2$s', 'advanced-nocaptcha-recaptcha' ), "<code>do_action( 'dd_recaptcha_captcha_form_field' )</code>", '<code>[dd-recaptcha-captcha]</code>' ); ?></div>
								<div><?php printf( __( 'To verify use %s. It will return true on success otherwise false.', 'advanced-nocaptcha-recaptcha' ), '<code>dd_recaptcha_verify_captcha()</code>' ); ?></div>
								<div><?php printf( __( 'For paid support pleasse visit <a href="%s" target="_blank">Advanced noCaptcha reCaptcha</a>', 'advanced-nocaptcha-recaptcha' ), esc_url( 'https://www.shamimsplugins.com/hire/' ) ); ?></div>
							</div>
						</div>
						<div><a class="button" href="<?php echo esc_url( admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) ); ?>"><?php esc_html_e( 'Back to Settings', 'advanced-nocaptcha-recaptcha' ); ?></a></div>
                    </div>
				</div>
				<br class="clear" />
			</div>
		</div>
		<?php
	}


	function add_settings_link( $links ) {
		// add settings link in plugins page
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) . '">' . __( 'Settings', 'advanced-nocaptcha-recaptcha' ) . '</a>';
		array_unshift( $links, $settings_link );
		return $links;
	}


} //END CLASS

add_action( 'wp_loaded', array( DD_RECAPTCHA_Settings::init(), 'actions_filters' ) );

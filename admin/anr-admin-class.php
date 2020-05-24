<?php

if (!class_exists('dd_recaptcha_admin_class'))
{
  class dd_recaptcha_admin_class
  {
	private static $instance;

	public static function init()
		{
			if(!self::$instance instanceof self) {
				self::$instance = new self;
			}
			return self::$instance;
		}

	function actions_filters()
	{
		if ( is_multisite() ) {
			$same_settings = apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false );
		} else {
			$same_settings = false;
		}
		if ( $same_settings ) {
			add_action('network_admin_menu', array($this, 'MenuPage'));
		} else {
			add_action('admin_menu', array($this, 'MenuPage'));
		}

	add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2 );
	}



/******************************************ADMIN SETTINGS PAGE BEGIN******************************************/

	function MenuPage()
	{
	//add_menu_page('Advanced noCaptcha reCaptcha', 'Advanced noCaptcha', 'manage_options', 'dd-recaptcha-admin-settings', array($this, 'admin_settings'),plugins_url( 'advanced-nocaptcha-recaptcha/images/advanced-nocaptcha-recaptcha.jpg' ));

	//add_submenu_page('dd-recaptcha-admin-settings', 'Advanced noCaptcha reCaptcha - ' .__('Settings','duck-recaptcha-plugin'), __('Settings','duck-recaptcha-plugin'), 'manage_options', 'dd-recaptcha-admin-settings', array($this, 'admin_settings'));

	//add_submenu_page('dd-recaptcha-admin-settings', 'Advanced noCaptcha reCaptcha - ' .__('Instruction','fepcf'), __('Instruction','fepcf'), 'manage_options', 'dd-recaptcha-instruction', array($this, "InstructionPage"));

	add_options_page( __('Advanced noCaptcha & invisible captcha Settings','duck-recaptcha-plugin'), __('Advanced noCaptcha & invisible captcha','duck-recaptcha-plugin'), 'manage_options', 'dd-recaptcha-admin-settings', array($this, 'admin_settings') );

	}


	function admin_settings()
	{
	  $token = wp_create_nonce( 'dd-recaptcha-admin-settings' );
	  $url = 'https://www.shamimsplugins.com/contact-us/';
	  $ReviewURL = 'https://wordpress.org/support/plugin/advanced-nocaptcha-recaptcha/reviews/?filter=5#new-post';
	  echo "<style>
			input[type='text'], textarea, select {
				width: 100%;
			}
		</style>";
	  $languages = array(
							__( 'Auto Detect', 'duck-recaptcha-plugin' )         	=> '',
							__( 'Arabic', 'duck-recaptcha-plugin' )              	=> 'ar',
							__( 'Bulgarian', 'duck-recaptcha-plugin' )           	=> 'bg',
							__( 'Catalan', 'duck-recaptcha-plugin' )             	=> 'ca',
							__( 'Chinese (Simplified)', 'duck-recaptcha-plugin' )	=> 'zh-CN',
							__( 'Chinese (Traditional)', 'duck-recaptcha-plugin' ) => 'zh-TW',
							__( 'Croatian', 'duck-recaptcha-plugin' )           	=> 'hr',
							__( 'Czech', 'duck-recaptcha-plugin' )             	=> 'cs',
							__( 'Danish', 'duck-recaptcha-plugin' )             	=> 'da',
							__( 'Dutch', 'duck-recaptcha-plugin' )              	=> 'nl',
							__( 'English (UK)', 'duck-recaptcha-plugin' )         => 'en-GB',
							__( 'English (US)', 'duck-recaptcha-plugin' )         => 'en',
							__( 'Filipino', 'duck-recaptcha-plugin' )				=> 'fil',
							__( 'Finnish', 'duck-recaptcha-plugin' ) 				=> 'fi',
							__( 'French', 'duck-recaptcha-plugin' )           	=> 'fr',
							__( 'French (Canadian)', 'duck-recaptcha-plugin' )   	=> 'fr-CA',
							__( 'German', 'duck-recaptcha-plugin' )   			=> 'de',
							__( 'German (Austria)', 'duck-recaptcha-plugin' )		=> 'de-AT',
							__( 'German (Switzerland)', 'duck-recaptcha-plugin' ) => 'de-CH',
							__( 'Greek', 'duck-recaptcha-plugin' )           		=> 'el',
							__( 'Hebrew', 'duck-recaptcha-plugin' )             	=> 'iw',
							__( 'Hindi', 'duck-recaptcha-plugin' )             	=> 'hi',
							__( 'Hungarain', 'duck-recaptcha-plugin' )            => 'hu',
							__( 'Indonesian', 'duck-recaptcha-plugin' )         	=> 'id',
							__( 'Italian', 'duck-recaptcha-plugin' )         		=> 'it',
							__( 'Japanese', 'duck-recaptcha-plugin' )				=> 'ja',
							__( 'Korean', 'duck-recaptcha-plugin' ) 				=> 'ko',
							__( 'Latvian', 'duck-recaptcha-plugin' )           	=> 'lv',
							__( 'Lithuanian', 'duck-recaptcha-plugin' )   		=> 'lt',
							__( 'Norwegian', 'duck-recaptcha-plugin' )   			=> 'no',
							__( 'Persian', 'duck-recaptcha-plugin' )           	=> 'fa',
							__( 'Polish', 'duck-recaptcha-plugin' )   			=> 'pl',
							__( 'Portuguese', 'duck-recaptcha-plugin' )   		=> 'pt',
							__( 'Portuguese (Brazil)', 'duck-recaptcha-plugin' )  => 'pt-BR',
							__( 'Portuguese (Portugal)', 'duck-recaptcha-plugin' )=> 'pt-PT',
							__( 'Romanian', 'duck-recaptcha-plugin' )         	=> 'ro',
							__( 'Russian', 'duck-recaptcha-plugin' )         		=> 'ru',
							__( 'Serbian', 'duck-recaptcha-plugin' )				=> 'sr',
							__( 'Slovak', 'duck-recaptcha-plugin' ) 				=> 'sk',
							__( 'Slovenian', 'duck-recaptcha-plugin' )           	=> 'sl',
							__( 'Spanish', 'duck-recaptcha-plugin' )   			=> 'es',
							__( 'Spanish (Latin America)', 'duck-recaptcha-plugin' )=> 'es-419',
							__( 'Swedish', 'duck-recaptcha-plugin' )           	=> 'sv',
							__( 'Thai', 'duck-recaptcha-plugin' )   				=> 'th',
							__( 'Turkish', 'duck-recaptcha-plugin' )   			=> 'tr',
							__( 'Ukrainian', 'duck-recaptcha-plugin' )   			=> 'uk',
							__( 'Vietnamese', 'duck-recaptcha-plugin' )   		=> 'vi'

							);

		$locations = array(
							__( 'Login Form', 'duck-recaptcha-plugin' )   				=> 'login',
							__( 'Registration Form', 'duck-recaptcha-plugin' )   			=> 'registration',
							__( 'Multisite User Signup Form', 'duck-recaptcha-plugin' )   => 'ms_user_signup',
							__( 'Lost Password Form', 'duck-recaptcha-plugin' )   		=> 'lost_password',
							__( 'Reset Password Form', 'duck-recaptcha-plugin' )  		=> 'reset_password',
							__( 'Comment Form', 'duck-recaptcha-plugin' )   				=> 'comment',
							__( 'bbPress New topic', 'duck-recaptcha-plugin' )   			=> 'bb_new',
							__( 'bbPress reply to topic', 'duck-recaptcha-plugin' )		=> 'bb_reply',
							__( 'WooCommerce Checkout', 'duck-recaptcha-plugin' )		=> 'wc_checkout',

							);


	  if(isset($_POST['dd-recaptcha-admin-settings-submit'])){
			$errors = $this->admin_settings_action();
			if(count($errors->get_error_messages())>0){
				echo'<div id="message" class="error fade"><p>' . implode( '<br />', $errors->get_error_messages() ). '</p></div>';
			} else {
				echo'<div id="message" class="updated fade"><p>' .__("Options successfully saved.", 'duck-recaptcha-plugin'). '</p></div>';
			}
		}
		echo "<div id='poststuff'>

		<div id='post-body' class='metabox-holder columns-2'>

		<!-- main content -->
		<div id='post-body-content'>
		<div class='postbox'><div class='inside'>
		  <h2>".__("Advanced noCaptcha reCaptcha Settings", 'duck-recaptcha-plugin')."</h2>
		  <h5>".sprintf(__("If you like this plugin please <a href='%s' target='_blank'>Review in Wordpress.org</a> and give 5 star", 'duck-recaptcha-plugin'),esc_url($ReviewURL))."</h5>
		  <form method='post' action=''>
		  <table>
		  <thead>
		  <tr><th width = '50%'>".__("Setting", 'duck-recaptcha-plugin')."</th><th width = '50%'>".__("Value", 'duck-recaptcha-plugin')."</th></tr>
		  </thead>
		  <tr><td>".__("Site Key", 'duck-recaptcha-plugin')."<br/><small><a href='https://www.google.com/recaptcha/admin' target='_blank'>Get From Google</a></small></td><td><input type='text' size = '40' name='site_key' value='".esc_attr( dd_recaptcha_get_option('site_key') )."' /></td></tr>
		  <tr><td>".__("Secret key", 'duck-recaptcha-plugin')."<br/><small><a href='https://www.google.com/recaptcha/admin' target='_blank'>Get From Google</a></small></td><td><input type='text' size = '40' name='secret_key' value='".esc_attr( dd_recaptcha_get_option('secret_key') )."' /></td></tr>

		  <tr><td>".__("Language", 'duck-recaptcha-plugin')."</td><td><select name='language'>";

		  foreach ( $languages as $language => $code ) {

		  echo "<option value='". esc_attr( $code ) ."' ".selected(dd_recaptcha_get_option('language'), $code,false).">".esc_html( $language )."</option>";

		  }

		  echo "</select></td></tr>
		  <tr><td>".__("Theme", 'duck-recaptcha-plugin')."</td><td><select name='theme'>

		  <option value='light' ".selected(dd_recaptcha_get_option('theme'), 'light',false).">".__("Light", 'duck-recaptcha-plugin')."</option>
		  <option value='dark' ".selected(dd_recaptcha_get_option('theme'), 'dark',false).">".__("Dark", 'duck-recaptcha-plugin')."</option>

		  </select></td></tr>
		  <tr><td>".__("Size", 'duck-recaptcha-plugin')."</td><td><select name='size'>

		  <option value='normal' ".selected(dd_recaptcha_get_option('size'), 'normal',false).">".__("Normal", 'duck-recaptcha-plugin')."</option>
		  <option value='compact' ".selected(dd_recaptcha_get_option('size'), 'compact',false).">".__("Compact", 'duck-recaptcha-plugin')."</option>
		  <option value='invisible' ".selected(dd_recaptcha_get_option('size'), 'invisible',false).">".__("Invisible", 'duck-recaptcha-plugin')."</option>

		  </select>
		  <div class='description'>".__("For invisible captcha set this as Invisible. Make sure to use site key and secret key for invisible reCaptcha", 'duck-recaptcha-plugin')."</div>
		  </td></tr>

		  <tr><td>".__("Badge", 'duck-recaptcha-plugin')."</td><td><select name='badge'>

		  <option value='bottomright' ".selected(dd_recaptcha_get_option('badge'), 'bottomright',false).">".__("Bottom Right", 'duck-recaptcha-plugin')."</option>
		  <option value='bottomleft' ".selected(dd_recaptcha_get_option('badge'), 'bottomleft',false).">".__("Bottom Left", 'duck-recaptcha-plugin')."</option>
		  <option value='inline' ".selected(dd_recaptcha_get_option('badge'), 'inline',false).">".__("Inline", 'duck-recaptcha-plugin')."</option>

		  </select>
		  <div class='description'>".__("Badge shows for invisible reCaptcha", 'duck-recaptcha-plugin')."</div>
		  </td></tr>

		  <tr><td>".__("Error Message", 'duck-recaptcha-plugin')."</td><td><input type='text' size = '40' name='error_message' value='".wp_kses_post( dd_recaptcha_get_option('error_message', '<strong>ERROR</strong>: Please solve reCAPTCHA correctly.') )."' /></td></tr>
		  <tr><td>".__("Show login reCaptcha after how many failed attempts", 'duck-recaptcha-plugin')."</td><td><input type='number' size = '40' name='failed_login_allow' value='".absint(dd_recaptcha_get_option('failed_login_allow', 0 ))."' /></td></tr>

		  <tr><td>".__("Show reCaptcha on", 'duck-recaptcha-plugin')."</td><td>";

		  foreach ( $locations as $location => $slug ) {

		  echo "<ul colspan='2'><label><input type='checkbox' name='" . esc_attr( $slug ) . "' value='1' ".checked(dd_recaptcha_get_option($slug), '1', false)." /> ". esc_html( $location ) ."</label></ul>";

		  }
		  /**
		  if ( function_exists('fepcf_plugin_activate'))
		  echo "<ul colspan='2'><label><input type='checkbox' name='fep_contact_form' value='1' ".checked(dd_recaptcha_get_option('fep_contact_form'), '1', false)." /> FEP Contact Form</label></ul>";
		  else
		  echo "<ul colspan='2'><label><input type='checkbox' name='fep_contact_form' disabled value='1' ".checked(dd_recaptcha_get_option('fep_contact_form'), '1', false)." /> FEP Contact Form (is not installed) <a href='https://wordpress.org/plugins/fep-contact-form/' target='_blank'>Install Now</a></label></ul>";
		  */

		  //echo "<ul colspan='2'> For other forms see <a href='".esc_url(admin_url( 'admin.php?page=dd-recaptcha-instruction' ))."'>Instruction</a></ul>";
		  echo "</td></tr>";

		  do_action('dd_recaptcha_admin_setting_form');

		  echo "<tr><td colspan='2'><label><input type='checkbox' name='loggedin_hide' value='1' ".checked(dd_recaptcha_get_option('loggedin_hide'), '1', false)." /> ".__("Hide Captcha for logged in users?", 'duck-recaptcha-plugin')."</label></td></tr>
		  <tr><td colspan='2'><label><input type='checkbox' name='remove_css' value='1' ".checked(dd_recaptcha_get_option('remove_css'), '1', false)." /> ".__("Remove this plugin's css from login page?", 'duck-recaptcha-plugin')."<br/><small>".__("This css increase login page width to adjust with Captcha width.", 'duck-recaptcha-plugin')."</small></label></td></tr>
		  <tr><td colspan='2'><label><input type='checkbox' name='no_js' value='1' ".checked(dd_recaptcha_get_option('no_js'), '1', false)." /> ".__("Show captcha if javascript disabled?", 'duck-recaptcha-plugin')."<br/><small>".__("If JavaScript is a requirement for your site, we advise that you do NOT check this.", 'duck-recaptcha-plugin')."</small></label></td></tr>
		  <tr><td colspan='2'><span><input class='button-primary' type='submit' name='dd-recaptcha-admin-settings-submit' value='".__("Save Options", 'duck-recaptcha-plugin')."' /></span></td><td><input type='hidden' name='token' value='$token' /></td></tr>
		  </table>
		  </form>
		  <ul>".sprintf(__("For paid support pleasse visit <a href='%s' target='_blank'>Advanced noCaptcha reCaptcha</a>", 'duck-recaptcha-plugin'),esc_url($url))."</ul>
		  </div></div></div>
		  ". $this->dd_recaptcha_admin_sidebar(). "
		  </div></div>";
		  }

function dd_recaptcha_admin_sidebar()
	{
		return '<div id="postbox-container-1" class="postbox-container">


				<div class="postbox">
					<h3 class="hndle" style="text-align: center;">
						<span>'. __( "Plugin Author", "anr" ). '</span>
					</h3>

					<div class="inside">
						<div style="text-align: center; margin: auto">
						<strong>Shamim Hasan</strong><br />
						Know php, MySql, css, javascript, html. Expert in WordPress. <br /><br />

						You can hire for plugin customization, build custom plugin or any kind of wordpress job via <br> <a
								href="https://www.shamimsplugins.com/contact-us/"><strong>Contact Form</strong></a>
					</div>
				</div>
			</div>
				</div>';
	}


	function admin_settings_action()
	{
		if (isset($_POST['dd-recaptcha-admin-settings-submit']))
		{
			$errors = new WP_Error();
			$options = $_POST;

			if( !current_user_can('manage_options'))
			$errors->add('noPermission', __('No Permission!', 'duck-recaptcha-plugin'));


			if ( !wp_verify_nonce($options['token'], 'dd-recaptcha-admin-settings'))
			$errors->add('invalidToken', __('Sorry, your nonce did not verify!', 'duck-recaptcha-plugin'));

			unset( $options['token'], $options['dd-recaptcha-admin-settings-submit'] );

			$options['site_key'] = isset( $options['site_key'] ) ? sanitize_text_field( $options['site_key'] ) : '';
			$options['secret_key'] = isset( $options['secret_key'] ) ? sanitize_text_field( $options['secret_key'] ) : '';
			$options['error_message'] = isset( $options['error_message'] ) ? wp_kses_post( $options['error_message'] ) : '';

			$options = apply_filters('dd_recaptcha_filter_admin_setting_before_save', $options, $errors);
			//var_dump($options);

			if ( count( $errors->get_error_codes() ) == 0 ){
				if ( is_multisite() && apply_filters( 'dd_recaptcha_same_settings_for_all_sites', false ) ){
					update_site_option( 'dd_recaptcha_admin_options', $options );
				} else {
					update_option( 'dd_recaptcha_admin_options', $options );
				}
			}
			return $errors;
		}
		return false;
	}

	function InstructionPage()
	{
	$url = 'https://www.shamimsplugins.com/contact-us/';
	echo '<div id="poststuff">

		<div id="post-body" class="metabox-holder columns-2">

		<!-- main content -->
		<div id="post-body-content">';

	  echo 	"<div class='postbox'><div class='inside'>
		  <h2>".__("Advanced noCaptcha reCaptcha Setup Instruction", 'duck-recaptcha-plugin')."</h2>
		  <p><ul>
		  <li>".sprintf(__("Get your site key and secret key from <a href='%s' target='_blank'>GOOGLE</a> if you do not have already.", 'duck-recaptcha-plugin'),esc_url('https://www.google.com/recaptcha/admin'))."</li>
		  <li>".__("Goto SETTINGS page of this plugin and set up as you need. and ENJOY...", 'duck-recaptcha-plugin')."</li><br/>

		  <h3>".__("Implement noCaptcha in Contact Form 7", 'duck-recaptcha-plugin')."</h3><br />
		  <li>".__("To show noCaptcha use ", 'duck-recaptcha-plugin')."<code>[dd_recaptcha g-recaptcha-response]</code></li><br />

		  <h3>".__("Implement noCaptcha in WooCommerce", 'duck-recaptcha-plugin')."</h3><br />
		  <li>".__("If Login Form, Registration Form, Lost Password Form, Reset Password Form is selected in SETTINGS page of this plugin they will show and verify Captcha in WooCommerce respective forms also.", 'duck-recaptcha-plugin')."</li><br />

		  <h3>".__("If you want to implement noCaptcha in any other custom form", 'duck-recaptcha-plugin')."</h3><br />
		  <li>".__("To show form field use ", 'duck-recaptcha-plugin')."<code>do_action( 'dd_recaptcha_captcha_form_field' )</code></li>
		  <li>".__("To verify use ", 'duck-recaptcha-plugin')."<code>dd_recaptcha_verify_captcha()</code> it will return true on success otherwise false</li><br />
		  <li>".sprintf(__("For paid support pleasse visit <a href='%s' target='_blank'>Advanced noCaptcha reCaptcha</a>", 'duck-recaptcha-plugin'),esc_url($url))."</li>
		  </ul></p></div></div></div>
		  ". $this->dd_recaptcha_admin_sidebar(). "
		  </div></div>";
		  }


function add_settings_link( $links, $file ) {
	//add settings link in plugins page
	$plugin_file = 'advanced-nocaptcha-recaptcha/advanced-nocaptcha-recaptcha.php';
	if ( $file == $plugin_file ) {
		$settings_link = '<a href="' . admin_url( 'options-general.php?page=dd-recaptcha-admin-settings' ) . '">' .__( 'Settings', 'duck-recaptcha-plugin' ) . '</a>';
		array_unshift( $links, $settings_link );
	}
	return $links;
}
/******************************************ADMIN SETTINGS PAGE END******************************************/


  } //END CLASS
} //ENDIF

add_action('wp_loaded', array(dd_recaptcha_admin_class::init(), 'actions_filters'));

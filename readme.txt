=== Google Recaptcha For Wordpress (v2 & v3) ===
Contributors: thehowarde
Tags: recaptcha,nocaptcha,invisible,bot,spam,captcha,woocommerce,widget,plugin,comments,google,bbpress
Requires at least: 4.4
Tested up to: 5.4
Stable tag: 1.0
Requires PHP: 5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Show noCaptcha or invisible captcha in Comment (after Comment textarea before submit button), CF7, bbpress, BuddyPress, woocommerce, Login, Register, Lost & Reset Password.

== Description ==

Show noCaptcha or invisible captcha in Comment Form (after Comment textarea before submit button), Contact Form 7, bbPress, BuddyPress, woocommerce, Login, Register, Lost Password, Reset Password. Also can implement in any other form easily.

* **Allow multiple captcha in same page.**
* **Allow conditional login captcha** (you can set after how many failed login attempts login captcha will show)

> [For **Advanced noCaptcha & invisible Captcha PRO** click here](https://www.shamimsplugins.com/products/advanced-nocaptcha-and-invisible-captcha-pro/?utm_campaign=wordpress&utm_source=readme_pro&utm_medium=description)

= Show noCaptcha on =

* Comment Form (after Comment textarea before submit button)
* WooCommerce
* Login
* Register
* Multisite User Signup
* Lost Password
* Reset Password
* Contact Form 7
* FEP Contact Form
* bbPress New topic
* bbPress reply to topic
* BuddyPress register

= Options =

* You can select which version of reCaptcha will be used (v2 I'm not robot checkbox, v2 invisible or v3)
* Language can be changed
* Error message can be changed
* For v2 I'm not robot: Theme, Size can be changed.
* For v2 Invisible: Theme, badge location can be changed.
* For v3: Score and when to load script can be changed
* Option to show/hide captcha for logged in users
* Captcha will show if javascript disabled also (optional)

= Privacy Notices =

* This plugin send IP address go Google for captcha verification. Please read [Google Privacy Policy](https://policies.google.com/).
* If you set "Show login Captcha after how many failed attempts" to more than 0(zero) then user hash from ip address will be stored in database. After successful login, hash of that ip address will be deleted. 

== Installation ==
1. Upload "advanced-nocaptcha-recaptcha" to the "/wp-content/plugins/" directory.
1. Activate the plugin through the "Plugins" menu in WordPress.
1. Go to plugin settings page for setup.


== Frequently Asked Questions ==

= Can i use this plugin to my language? =
Yes. this plugin is translate ready. But If your language is not available you can make one. If you want to help us to translate this plugin to your language you are welcome.

= Can i show multiple captcha in same page? =
Yes. You can show unlimited number of captcha in same page.

= How to load reCaptcha v3 script only when there is form in that page? =
Loading v3 script in All Pages help google for analytics. If you want to load script only when there is form in that page please go to Dashboard > Settings > Advanced noCaptcha & invisible Captcha > v3 Script Load and set to "Form Pages".
If you are not using v3 then script will only load when there is form in that page. no settings required.

= How to set captcha in contact form 7? =
To show noCaptcha use [dd_recaptcha_nocaptcha g-recaptcha-response]

= How to login if i am locked out? =
You can access your file via FTP or file manager and rename "advanced-nocaptcha-recaptcha" folder to something else. Then login as normal. Then rename back this folder.

== Screenshots ==

1. Captcha in comment form
2. Captcha in Contact Form 7
3. Captcha in WooCommerce (multiple in same page)
4. Captcha in Login Form
5. Captcha in Register Form
6. Captcha in Lost Password Form
7. Advanced noCaptcha reCaptcha Settings
8. Advanced noCaptcha reCaptcha Setup Instruction

== Changelog ==
<?php
/*
Plugin Name: WPS Hide Login
Description: Protect your website by changing the login URL and preventing access to wp-login.php page and wp-admin directory while not logged-in
Donate link: https://www.paypal.me/donateWPServeur
Author: WPServeur, NicolasKulka, tabrisrp
Author URI: https://wpserveur.net
Version: 1.4.5
Requires at least: 4.1
Tested up to: 4.9
Requires PHP: 7.0
Domain Path: languages
Text Domain: wpserveur-hide-login
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

// Plugin constants
define( 'WPS_HIDE_LOGIN_VERSION', '1.4.5' );
define( 'WPS_HIDE_LOGIN_FOLDER', 'wps-hide-login' );

define( 'WPS_HIDE_LOGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPS_HIDE_LOGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPS_HIDE_LOGIN_BASENAME', plugin_basename( __FILE__ ) );

require_once WPS_HIDE_LOGIN_DIR . 'autoload.php';

register_activation_hook( __FILE__, array( '\WPS\WPS_Hide_Login\Plugin', 'activate' ) );

add_action( 'plugins_loaded', 'plugins_loaded_wps_hide_login_plugin' );
function plugins_loaded_wps_hide_login_plugin() {
	\WPS\WPS_Hide_Login\Plugin::get_instance();

	load_plugin_textdomain( 'wpserveur-hide-login', false, dirname( WPS_HIDE_LOGIN_BASENAME ) . '/languages' );

	$message = __( 'Do you like plugin WPS Hide Login? <br> Thank you for taking a few seconds to note us on', 'wps-hide-login' );
	if( 'fr_FR' === get_locale() ) {
		$message = 'Vous aimez l\'extension WPS Hide Login ?<br>Merci de prendre quelques secondes pour nous noter sur';
	}

	new \WP_Review_Me(
		array(
			'days_after' => 10,
			'type'       => 'plugin',
			'slug'       => 'wps-hide-login',
			'message'    => $message,
			'link_label' => 'WordPress.org'
		)
	);
}
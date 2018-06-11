<?php
/*
Plugin Name: WPS Hide Login
Description: Protect your website by changing the login URL and preventing access to wp-login.php page and wp-admin directory while not logged-in
Author: WPServeur, NicolasKulka, tabrisrp
Author URI: https://wpserveur.net
Version: 1.3.4.2
Requires at least: 4.1
Tested up to: 4.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

// don't load directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPS_HIDE_LOGIN_BASENAME', plugin_basename( __FILE__ ) );

foreach ( glob( dirname( __FILE__ ) . "/classes/*.php" ) as $file ) {
    require_once $file;
}

register_activation_hook( __FILE__, array( 'WPS_Hide_Login', 'activate' ) );

add_action( 'plugins_loaded', 'wps_hide_login_load_textdomain' );
function wps_hide_login_load_textdomain() {
	new WPS_Hide_Login;

	load_plugin_textdomain( 'wpserveur-hide-login', false, dirname( WPS_HIDE_LOGIN_BASENAME ) . '/languages' );
}
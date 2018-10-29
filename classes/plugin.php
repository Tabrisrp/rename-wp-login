<?php

namespace WPS\WPS_Hide_Login;


class Plugin {

	use Singleton;

	private $wp_login_php;

	protected function init() {
		global $wp_version;

		if ( version_compare( $wp_version, '4.0-RC1-src', '<' ) ) {
			add_action( 'admin_notices', array( $this, 'admin_notices_incompatible' ) );
			add_action( 'network_admin_notices', array( $this, 'admin_notices_incompatible' ) );

			return;
		}


		if ( is_multisite() && ! function_exists( 'is_plugin_active_for_network' ) || ! function_exists( 'is_plugin_active' ) ) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );

		}

		if ( is_plugin_active_for_network( 'rename-wp-login/rename-wp-login.php' ) ) {
			deactivate_plugins( WPS_HIDE_LOGIN_BASENAME );
			add_action( 'network_admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return;
		}

		if ( is_plugin_active( 'rename-wp-login/rename-wp-login.php' ) ) {
			deactivate_plugins( WPS_HIDE_LOGIN_BASENAME );
			add_action( 'admin_notices', array( $this, 'admin_notices_plugin_conflict' ) );
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}

			return;
		}

		if ( is_multisite() && is_plugin_active_for_network( WPS_HIDE_LOGIN_BASENAME ) ) {
			add_action( 'wpmu_options', array( $this, 'wpmu_options' ) );
			add_action( 'update_wpmu_options', array( $this, 'update_wpmu_options' ) );

			add_filter( 'network_admin_plugin_action_links_' . WPS_HIDE_LOGIN_BASENAME, array(
				$this,
				'plugin_action_links'
			) );
		}

		add_action( 'admin_init', array( $this, 'admin_init' ) );
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ), 9999 );
		add_action( 'admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'network_admin_notices', array( $this, 'admin_notices' ) );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_action( 'setup_theme', array( $this, 'setup_theme' ), 1 );

		add_filter( 'plugin_action_links_' . WPS_HIDE_LOGIN_BASENAME, array( $this, 'plugin_action_links' ) );
		add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
		add_filter( 'site_option_welcome_email', array( $this, 'welcome_email' ) );

		remove_action( 'template_redirect', 'wp_redirect_admin_locations', 1000 );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

		add_action( 'admin_menu', array( $this, 'wps_hide_login_menu_page' ) );
		add_action( 'admin_init', array( $this, 'whl_template_redirect' ) );

		add_action( 'template_redirect', array( $this, 'wps_hide_login_redirect_page_email_notif_woocommerce' ) );
		add_filter( 'login_url', array( $this, 'login_url' ), 10, 3 );
	}

	private function use_trailing_slashes() {

		return ( '/' === substr( get_option( 'permalink_structure' ), - 1, 1 ) );

	}

	private function user_trailingslashit( $string ) {

		return $this->use_trailing_slashes() ? trailingslashit( $string ) : untrailingslashit( $string );

	}

	private function wp_template_loader() {

		global $pagenow;

		$pagenow = 'index.php';

		if ( ! defined( 'WP_USE_THEMES' ) ) {

			define( 'WP_USE_THEMES', true );

		}

		wp();

		if ( $_SERVER['REQUEST_URI'] === $this->user_trailingslashit( str_repeat( '-/', 10 ) ) ) {

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/wp-login-php/' );

		}

		require_once( ABSPATH . WPINC . '/template-loader.php' );

		die;

	}

	private function new_login_slug() {
		if ( $slug = get_option( 'whl_page' ) ) {
			return $slug;
		} else if ( ( is_multisite() && is_plugin_active_for_network( WPS_HIDE_LOGIN_BASENAME ) && ( $slug = get_site_option( 'whl_page', 'login' ) ) ) ) {
			return $slug;
		} else if ( $slug = 'login' ) {
			return $slug;
		}
	}

	public function new_login_url( $scheme = null ) {

		if ( get_option( 'permalink_structure' ) ) {

			return $this->user_trailingslashit( home_url( '/', $scheme ) . $this->new_login_slug() );

		} else {

			return home_url( '/', $scheme ) . '?' . $this->new_login_slug();

		}

	}

	public function admin_notices_incompatible() {

		echo '<div class="error notice is-dismissible"><p>' . __( 'Please upgrade to the latest version of WordPress to activate', 'wpserveur-hide-login' ) . ' <strong>' . __( 'WPS Hide Login', 'wpserveur-hide-login' ) . '</strong>.</p></div>';

	}

	public function admin_notices_plugin_conflict() {

		echo '<div class="error notice is-dismissible"><p>' . __( 'WPS Hide Login could not be activated because you already have Rename wp-login.php active. Please uninstall rename wp-login.php to use WPS Hide Login', 'wpserveur-hide-login' ) . '</p></div>';

	}

	/**
	 * Plugin activation
	 */
	public static function activate() {
		//add_option( 'whl_redirect', '1' );

		do_action( 'wps_hide_login_activate' );
	}

	public function wpmu_options() {

		$out = '';

		$out .= '<h3>' . __( 'WPS Hide Login', 'wpserveur-hide-login' ) . '</h3>';
		$out .= '<p>' . __( 'This option allows you to set a networkwide default, which can be overridden by individual sites. Simply go to to the site’s permalink settings to change the url.', 'wpserveur-hide-login' ) . '</p>';
		$out .= '<p>' . sprintf( __( 'Need help? Try the <a href="%1$s" target="_blank">support forum</a>. This plugin is kindly brought to you by <a href="%2$s" target="_blank">WPServeur</a>', 'wpserveur-hide-login' ), 'http://wordpress.org/support/plugin/wps-hide-login/', 'https://www.wpserveur.net/?refwps=14&campaign=wpshidelogin' ) . '</p>';
		$out .= '<table class="form-table">';
		$out .= '<tr valign="top">';
		$out .= '<th scope="row"><label for="whl_page">' . __( 'Networkwide default', 'wpserveur-hide-login' ) . '</label></th>';
		$out .= '<td><input id="whl_page" type="text" name="whl_page" value="' . esc_attr( get_site_option( 'whl_page', 'login' ) ) . '"></td>';
		$out .= '</tr>';
		$out .= '</table>';

		echo $out;

	}

	public function update_wpmu_options() {
		if ( check_admin_referer( 'siteoptions' ) ) {
			if ( ( $whl_page = sanitize_title_with_dashes( $_POST['whl_page'] ) )
			     && strpos( $whl_page, 'wp-login' ) === false
			     && ! in_array( $whl_page, $this->forbidden_slugs() ) ) {

				update_site_option( 'whl_page', $whl_page );

			}
		}
	}

	public function admin_init() {

		global $pagenow;

		add_settings_section(
			'wps-hide-login-section',
			'WPS Hide Login',
			array( $this, 'whl_section_desc' ),
			'general'
		);

		add_settings_field(
			'whl_page',
			'<label for="whl_page">' . __( 'Login url', 'wpserveur-hide-login' ) . '</label>',
			array( $this, 'whl_page_input' ),
			'general',
			'wps-hide-login-section'
		);

		register_setting( 'general', 'whl_page', 'sanitize_title_with_dashes' );

		if ( get_option( 'whl_redirect' ) ) {

			delete_option( 'whl_redirect' );

			if ( is_multisite()
			     && is_super_admin()
			     && is_plugin_active_for_network( WPS_HIDE_LOGIN_BASENAME ) ) {

				$redirect = network_admin_url( 'settings.php#whl_settings' );

			} else {

				$redirect = admin_url( 'options-general.php#whl_settings' );

			}

			wp_safe_redirect( $redirect );
			die();

		}

	}

	public function whl_section_desc() {

		$out = '';

		if ( ! is_multisite()
		     || is_super_admin() ) {

			$details_url_wpsbidouille = add_query_arg(
				array(
					'tab'       => 'plugin-information',
					'plugin'    => 'wps-bidouille',
					'TB_iframe' => true,
					'width'     => 722,
					'height'    => 949,
				),
				admin_url( 'plugin-install.php' )
			);

			$details_url_wpscleaner = add_query_arg(
				array(
					'tab'       => 'plugin-information',
					'plugin'    => 'wps-cleaner',
					'TB_iframe' => true,
					'width'     => 722,
					'height'    => 949,
				),
				admin_url( 'plugin-install.php' )
			);

			$details_url_wpslimitlogin = add_query_arg(
				array(
					'tab'       => 'plugin-information',
					'plugin'    => 'wps-limit-login',
					'TB_iframe' => true,
					'width'     => 722,
					'height'    => 949,
				),
				admin_url( 'plugin-install.php' )
			);

			$out .= '<div id="whl_settings">';
			$out .= sprintf( __( 'Need help? Try the <a href="%1$s" target="_blank">support forum</a>. This plugin is kindly brought to you by <a href="%2$s" target="_blank">WPServeur</a>', 'wpserveur-hide-login' ), 'http://wordpress.org/support/plugin/wps-hide-login/', 'https://www.wpserveur.net/?refwps=14&campaign=wpshidelogin' ) . ' (' . __( 'WordPress specialized hosting', 'wpserveur-hide-login' ) . ')';
			$out .= '<br>' . __( 'Discover our other plugins:', 'wpserveur-hide-login' ) . ' ';
			$out .= __( 'the plugin', 'wpserveur-hide-login' ) . ' <a href="' . $details_url_wpsbidouille . '" class="thickbox open-plugin-details-modal">' . __( 'WPS Bidouille', 'wpserveur-hide-login' ) . '</a>';
			$out .= ', ' . __( 'the plugin', 'wpserveur-hide-login' ) . ' <a href="' . $details_url_wpscleaner . '" class="thickbox open-plugin-details-modal">' . __( 'WPS Cleaner', 'wpserveur-hide-login' ) . '</a>';
			$out .= ' ' . __( 'and', 'wpserveur-hide-login' ) . ' <a href="' . $details_url_wpslimitlogin . '" class="thickbox open-plugin-details-modal">' . __( 'WPS Limit Login', 'wpserveur-hide-login' ) . '</a>';
			$out .= '</div>';

		}

		if ( is_multisite()
		     && is_super_admin()
		     && is_plugin_active_for_network( WPS_HIDE_LOGIN_BASENAME ) ) {

			$out .= '<p>' . sprintf( __( 'To set a networkwide default, go to <a href="%s">Network Settings</a>.', 'wpserveur-hide-login' ), network_admin_url( 'settings.php#whl_settings' ) ) . '</p>';

		}

		echo $out;

	}

	public function whl_page_input() {

		if ( get_option( 'permalink_structure' ) ) {

			echo '<code>' . trailingslashit( home_url() ) . '</code> <input id="whl_page" type="text" name="whl_page" value="' . $this->new_login_slug() . '">' . ( $this->use_trailing_slashes() ? ' <code>/</code>' : '' );

		} else {

			echo '<code>' . trailingslashit( home_url() ) . '?</code> <input id="whl_page" type="text" name="whl_page" value="' . $this->new_login_slug() . '">';

		}

		echo '<p class="description">' . __( 'Protect your website by changing the login URL and preventing access to the wp-login.php page and the wp-admin directory to non-connected people.', 'wpserveur-hide-login' ) . '</p>';

	}

	public function admin_notices() {

		global $pagenow;

		$out = '';

		if ( ! is_network_admin()
		     && $pagenow === 'options-general.php'
		     && isset( $_GET['settings-updated'] )
		     && ! isset( $_GET['page'] ) ) {

			echo '<div class="updated notice is-dismissible"><p>' . sprintf( __( 'Your login page is now here: <strong><a href="%1$s">%2$s</a></strong>. Bookmark this page!', 'wpserveur-hide-login' ), $this->new_login_url(), $this->new_login_url() ) . '</p></div>';

		}

	}

	public function plugin_action_links( $links ) {

		if ( is_network_admin()
		     && is_plugin_active_for_network( WPS_HIDE_LOGIN_BASENAME ) ) {

			array_unshift( $links, '<a href="' . network_admin_url( 'settings.php#whl_settings' ) . '">' . __( 'Settings', 'wpserveur-hide-login' ) . '</a>' );

		} elseif ( ! is_network_admin() ) {

			array_unshift( $links, '<a href="' . admin_url( 'options-general.php#whl_settings' ) . '">' . __( 'Settings', 'wpserveur-hide-login' ) . '</a>' );

		}

		return $links;

	}

	public function plugins_loaded() {

		global $pagenow;

		if ( ! is_multisite()
		     && ( strpos( $_SERVER['REQUEST_URI'], 'wp-signup' ) !== false
		          || strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) !== false ) && apply_filters( 'wps_hide_login_signup_enable', false ) === false ) {

			wp_die( __( 'This feature is not enabled.', 'wpserveur-hide-login' ) );

		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-login.php' ) !== false
		       || untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' ) )
		     && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';

		} elseif ( untrailingslashit( $request['path'] ) === home_url( $this->new_login_slug(), 'relative' )
		           || ( ! get_option( 'permalink_structure' )
		                && isset( $_GET[ $this->new_login_slug() ] )
		                && empty( $_GET[ $this->new_login_slug() ] ) ) ) {

			$pagenow = 'wp-login.php';

		} elseif ( ( strpos( rawurldecode( $_SERVER['REQUEST_URI'] ), 'wp-register.php' ) !== false
		             || untrailingslashit( $request['path'] ) === site_url( 'wp-register', 'relative' ) )
		           && ! is_admin() ) {

			$this->wp_login_php = true;

			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );

			$pagenow = 'index.php';
		}

	}

	public function setup_theme() {
		global $pagenow;

		if ( ! is_user_logged_in() && 'customize.php' === $pagenow ) {
			wp_die( __( 'This has been disabled', 'wpserveur-hide-login' ), 403 );
		}
	}

	public function wp_loaded() {

		global $pagenow;

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( is_admin() && ! is_user_logged_in() && ! defined( 'DOING_AJAX' ) && $pagenow !== 'admin-post.php' && ( isset( $_GET ) && empty( $_GET['adminhash'] ) && $request['path'] !== '/wp-admin/options.php' ) ) {
			wp_safe_redirect( home_url( '/404' ) );
			die();
		}

		if ( $pagenow === 'wp-login.php'
		     && $request['path'] !== $this->user_trailingslashit( $request['path'] )
		     && get_option( 'permalink_structure' ) ) {

			wp_safe_redirect( $this->user_trailingslashit( $this->new_login_url() )
			                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

			die;

		} elseif ( $this->wp_login_php ) {

			if ( ( $referer = wp_get_referer() )
			     && strpos( $referer, 'wp-activate.php' ) !== false
			     && ( $referer = parse_url( $referer ) )
			     && ! empty( $referer['query'] ) ) {

				parse_str( $referer['query'], $referer );

				if ( ! empty( $referer['key'] )
				     && ( $result = wpmu_activate_signup( $referer['key'] ) )
				     && is_wp_error( $result )
				     && ( $result->get_error_code() === 'already_active'
				          || $result->get_error_code() === 'blog_taken' ) ) {

					wp_safe_redirect( $this->new_login_url()
					                  . ( ! empty( $_SERVER['QUERY_STRING'] ) ? '?' . $_SERVER['QUERY_STRING'] : '' ) );

					die;

				}

			}

			$this->wp_template_loader();

		} elseif ( $pagenow === 'wp-login.php' ) {
			global $error, $interim_login, $action, $user_login;

			if ( is_user_logged_in() && ! isset( $_REQUEST['action'] ) ) {
				wp_safe_redirect( admin_url() );
				die();
			}

			@require_once ABSPATH . 'wp-login.php';

			die;

		}

	}

	public function site_url( $url, $path, $scheme, $blog_id ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function network_site_url( $url, $path, $scheme ) {

		return $this->filter_wp_login_php( $url, $scheme );

	}

	public function wp_redirect( $location, $status ) {

		if ( strpos( $location, 'https://wordpress.com/wp-login.php' ) !== false ) {
			return $location;
		}

		return $this->filter_wp_login_php( $location );

	}

	public function filter_wp_login_php( $url, $scheme = null ) {

		if ( strpos( $url, 'wp-login.php' ) !== false ) {

			if ( is_ssl() ) {

				$scheme = 'https';

			}

			$args = explode( '?', $url );

			if ( isset( $args[1] ) ) {

				parse_str( $args[1], $args );

				if ( isset( $args['login'] ) ) {
					$args['login'] = rawurlencode( $args['login'] );
				}

				$url = add_query_arg( $args, $this->new_login_url( $scheme ) );

			} else {

				$url = $this->new_login_url( $scheme );

			}

		}

		return $url;

	}

	public function welcome_email( $value ) {

		return $value = str_replace( 'wp-login.php', trailingslashit( get_site_option( 'whl_page', 'login' ) ), $value );

	}

	public function forbidden_slugs() {

		$wp = new \WP;

		return array_merge( $wp->public_query_vars, $wp->private_query_vars );

	}

	/**
	 * Load scripts
	 */
	public function admin_enqueue_scripts( $hook ) {
		if ( 'options-general.php' != $hook ) {
			return false;
		}

		wp_enqueue_style( 'plugin-install' );

		wp_enqueue_script( 'plugin-install' );
		wp_enqueue_script( 'updates' );
		add_thickbox();
	}

	public function wps_hide_login_menu_page() {
		$title = __( 'WPS Hide Login' );

		add_options_page( $title, $title, 'manage_options', 'whl_settings', array(
			$this,
			'settings_page'
		) );
	}

	public function settings_page() {
		_e( 'WPS Hide Login' );
	}

	public function whl_template_redirect() {
		if ( ! empty( $_GET ) && isset( $_GET['page'] ) && 'whl_settings' === $_GET['page'] ) {
			wp_redirect( admin_url( 'options-general.php#whl_settings' ) );
			exit();
		}
	}

	/**
	 * Update redirect for Woocommerce email notification
	 */
	public function wps_hide_login_redirect_page_email_notif_woocommerce() {

		if ( ! class_exists( 'WC_Form_Handler' ) ) {
			return false;
		}

		if ( ! empty( $_GET ) && isset( $_GET['action'] ) && 'rp' === $_GET['action'] && isset( $_GET['key'] ) && isset( $_GET['login'] ) ) {
			wp_redirect( $this->new_login_url() );
			exit();
		}
	}

	/**
	 *
	 * Update url redirect : wp-admin/options.php
	 *
	 * @param $login_url
	 * @param $redirect
	 * @param $force_reauth
	 *
	 * @return string
	 */
	public function login_url( $login_url, $redirect, $force_reauth ) {
		if ( is_404() ) {
			return '#';
		}

		if ( $force_reauth === false ) {
			return $login_url;
		}

		if ( empty( $redirect ) ) {
			return $login_url;
		}

		$redirect = explode( '?', $redirect );

		if ( $redirect[0] === admin_url( 'options.php' ) ) {
			$login_url = admin_url();
		}

		return $login_url;
	}

}
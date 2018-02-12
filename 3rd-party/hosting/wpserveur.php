<?php
defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

if ( ! function_exists( 'wps_hide_login_remove_http' ) ) {
	function wps_hide_login_remove_http( $url ) {
		$disallowed = array( 'http://', 'https://' );
		foreach ( $disallowed as $d ) {
			if ( strpos( $url, $d ) === 0 ) {
				return str_replace( $d, '', $url );
			}
		}

		return $url;
	}
}

if ( ! function_exists( 'wps_hide_login_return_pf' ) ) {
	function wps_hide_login_return_pf() {
		$ip_array = array(
			'pf1'   => '212.129.21.7',
			'pf2'   => '163.172.241.32',
			'pf3'   => '212.83.178.111',
			'pf4'   => '212.129.40.192',
			'pf5'   => '212.129.45.189',
			'pf999' => '163.172.111.251',
		);
		$siteurl  = get_site_url();
		$host     = gethostbyname( wps_hide_login_remove_http( $siteurl ) );

		$pf = '';

		if ( in_array( $host, array_values( $ip_array ) ) ) {
			$pf = array_search( $host, $ip_array );

			return strtoupper( $pf );
		}

		// gestion ip persos
		$host_name = gethostname();
		if ( strpos( $host_name, 'wps' ) !== false ) {
			$pf = preg_replace( "/[^0-9]/", '', $host_name );
			$pf = 'PF' . $pf;
		}

		return $pf;
	}
}

if ( ! function_exists( 'wpserveur_wps_hide_login_activate' ) ) {
	add_action( 'wps_hide_login_activate', 'wpserveur_wps_hide_login_activate' );
	function wpserveur_wps_hide_login_activate() {
		$pf = wps_hide_login_return_pf();
		if ( empty( $pf ) ) {
			return false;
		}

		$whl_page = get_option( 'whl_page' );
		if( empty( $whl_page ) || $whl_page === 'login' ) {
			update_option( 'whl_page', 'connecter' );
		}
	}
}
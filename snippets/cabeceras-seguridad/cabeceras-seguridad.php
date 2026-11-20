<?php
/**
 * Plugin Name: Cabeceras de seguridad y XML-RPC desactivado
 * Description: Desactiva XML-RPC y los pingbacks, quita el enlace RSD y X-Pingback, oculta X-Powered-By y envía cabeceras de seguridad en el front.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 9 de nachomg.com, generalizado.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración. Cada cabecera se puede cambiar o eliminar con el filtro:
 *
 *   add_filter( 'wpe_cabeceras_seguridad', function ( $h ) {
 *       unset( $h['X-Frame-Options'] );                 // si tu web debe poder incrustarse en otra
 *       $h['Permissions-Policy'] .= ', usb=()';
 *       return $h;
 *   } );
 *
 * Y XML-RPC se puede mantener activo (por ejemplo, si usas la app móvil de WordPress
 * o Jetpack) con:
 *
 *   add_filter( 'wpe_desactivar_xmlrpc', '__return_false' );
 */
if ( ! function_exists( 'wpe_cabeceras_seguridad' ) ) {
	function wpe_cabeceras_seguridad() {
		$cabeceras = array(
			// Solo tiene efecto si la web se sirve entera por HTTPS. Empieza con un valor bajo si dudas.
			'Strict-Transport-Security' => 'max-age=31536000',
			'X-Content-Type-Options'    => 'nosniff',
			'Referrer-Policy'           => 'strict-origin-when-cross-origin',
			'X-Frame-Options'           => 'SAMEORIGIN',
			'Permissions-Policy'        => 'camera=(), microphone=(), geolocation=(), payment=()',
		);
		return (array) apply_filters( 'wpe_cabeceras_seguridad', $cabeceras );
	}
}

if ( apply_filters( 'wpe_desactivar_xmlrpc', true ) ) {
	add_filter( 'xmlrpc_enabled', '__return_false' );
	add_filter( 'xmlrpc_methods', '__return_empty_array' );
	remove_action( 'wp_head', 'rsd_link' );
	add_filter(
		'wp_headers',
		function ( $headers ) {
			unset( $headers['X-Pingback'] );
			return $headers;
		}
	);
}

add_action(
	'send_headers',
	function () {
		if ( is_admin() || headers_sent() ) {
			return;
		}
		if ( function_exists( 'header_remove' ) ) {
			header_remove( 'X-Powered-By' );
		}
		foreach ( wpe_cabeceras_seguridad() as $nombre => $valor ) {
			if ( '' === (string) $valor ) {
				continue;
			}
			// No envía HSTS si la petición no llega por HTTPS.
			if ( 'Strict-Transport-Security' === $nombre && ! is_ssl() ) {
				continue;
			}
			header( $nombre . ': ' . $valor );
		}
	}
);

<?php
/**
 * Plugin Name: Caducidad de las cookies de Google Analytics
 * Description: Limita la duración de _ga y _ga_* (por defecto, 13 meses) en la etiqueta de Google Analytics 4 que inserta Site Kit.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 13 de nachomg.com, generalizado.
 * Requiere: Site Kit by Google con el módulo de Analytics activo.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración: duración en meses. Pon aquí la misma cifra que declara tu política de cookies.
 * También puedes cambiarla con:
 *   add_filter( 'wpe_meses_cookies_analytics', fn() => 12 );
 */
if ( ! defined( 'WPE_MESES_COOKIES_ANALYTICS' ) ) {
	define( 'WPE_MESES_COOKIES_ANALYTICS', 13 );
}

add_filter(
	'googlesitekit_gtag_opt',
	function ( $opt ) {
		if ( ! is_array( $opt ) ) {
			$opt = array();
		}
		$meses = (int) apply_filters( 'wpe_meses_cookies_analytics', WPE_MESES_COOKIES_ANALYTICS );
		if ( $meses > 0 ) {
			// Días = meses × 365 / 12, redondeado: 13 meses = 395 días = 34 128 000 segundos.
			$opt['cookie_expires'] = (int) round( $meses * 365 / 12 ) * DAY_IN_SECONDS;
		}
		return $opt;
	}
);

<?php
/**
 * Plugin Name: Índice de Rank Math con nombre accesible
 * Description: Añade aria-label al <nav> del bloque «Tabla de contenidos» de Rank Math para distinguirlo del menú principal.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 15 de nachomg.com, generalizado.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración: texto del nombre accesible.
 * Cámbialo aquí o con el filtro, por ejemplo:
 *   add_filter( 'wpe_indice_aria_label', fn() => 'Table of contents' );
 */
if ( ! function_exists( 'wpe_indice_aria_label' ) ) {
	function wpe_indice_aria_label() {
		return (string) apply_filters( 'wpe_indice_aria_label', 'Índice del artículo' );
	}
}

add_filter(
	'render_block_rank-math/toc-block',
	function ( $html ) {
		// Respeta un aria-label que ya exista (por ejemplo, si Rank Math lo añade en el futuro).
		if ( false !== strpos( $html, '<nav aria-label' ) || false !== stripos( $html, 'aria-labelledby' ) ) {
			return $html;
		}
		$label = esc_attr( wpe_indice_aria_label() );
		return preg_replace( '/<nav(\s|>)/', '<nav aria-label="' . $label . '"$1', $html, 1 );
	}
);

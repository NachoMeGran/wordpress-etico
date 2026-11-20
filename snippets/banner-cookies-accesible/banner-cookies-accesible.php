<?php
/**
 * Plugin Name: Banner de cookies accesible por teclado
 * Description: Convierte los botones del banner de cookies (construidos con <div>) en botones que se pueden usar con teclado y lector de pantalla, y etiqueta el banner como región.
 * Version:     0.9.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 12 de nachomg.com, generalizado.
 * Pensado para: Lightweight Cookie Notice (identificadores daextlwcnf-*). Con otro banner, cambia los selectores.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración. Cámbiala aquí o con el filtro 'wpe_banner_accesible'.
 */
if ( ! function_exists( 'wpe_banner_accesible_config' ) ) {
	function wpe_banner_accesible_config() {
		$config = array(
			// Contenedor del banner y nombre de la región.
			'contenedor'       => '#daextlwcnf-cookie-notice-container',
			'nombre_region'    => 'Aviso de cookies',
			// Elementos que actúan como botones pero no lo son.
			'botones'          => array(
				'#daextlwcnf-cookie-notice-button-1',
				'#daextlwcnf-cookie-notice-button-2',
				'#daextlwcnf-cookie-notice-button-3',
				'#daextlwcnf-revisit-cookie-consent-container',
			),
			// Nombres accesibles para botones que solo tienen icono.
			'nombres'          => array(
				'#daextlwcnf-revisit-cookie-consent-container' => 'Cambiar preferencias de cookies',
			),
		);
		return (array) apply_filters( 'wpe_banner_accesible', $config );
	}
}

add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		$json = wp_json_encode( wpe_banner_accesible_config() );
		$js   = <<<'JS'
(function (cfg) {
  function arreglar() {
    var c = document.querySelector(cfg.contenedor);
    if (c && !c.hasAttribute('role')) {
      c.setAttribute('role', 'region');
      c.setAttribute('aria-label', cfg.nombre_region);
    }
    document.querySelectorAll(cfg.botones.join(',')).forEach(function (b) {
      if (b.dataset.wpeA11y) { return; }
      b.dataset.wpeA11y = '1';
      if (b.tagName !== 'BUTTON' && b.tagName !== 'A') {
        b.setAttribute('role', 'button');
        b.setAttribute('tabindex', '0');
        b.addEventListener('keydown', function (e) {
          if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); b.click(); }
        });
      }
      Object.keys(cfg.nombres).forEach(function (sel) {
        if (b.matches(sel) && !b.hasAttribute('aria-label')) { b.setAttribute('aria-label', cfg.nombres[sel]); }
      });
    });
  }
  function iniciar() {
    arreglar();
    // El banner puede insertarse o reconstruirse después de cargar la página.
    new MutationObserver(arreglar).observe(document.body, { childList: true, subtree: true });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', iniciar);
  } else {
    iniciar();
  }
})
JS;
		$codigo = $js . '(' . $json . ');';
		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $codigo, array( 'id' => 'wpe-banner-accesible' ) );
		} else {
			echo "<script id=\"wpe-banner-accesible\">\n" . $codigo . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	},
	99
);

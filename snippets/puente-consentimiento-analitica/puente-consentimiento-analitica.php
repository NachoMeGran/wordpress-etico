<?php
/**
 * Plugin Name: Puente entre el banner de cookies y Consent Mode v2 (solo analítica)
 * Description: Registra si la persona acepta o rechaza la analítica en el banner y lo comunica a la etiqueta de Google con gtag('consent','update'). Opcionalmente, envía el evento sign_up de GA4 cuando un formulario de suscripción muestra su mensaje de éxito.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 8 de nachomg.com, generalizado.
 * Pensado para: Lightweight Cookie Notice (identificadores daextlwcnf-*) + Site Kit by Google,
 * que declara analytics_storage=denied por defecto. Con otro banner, cambia los selectores.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración. Cámbiala aquí o con el filtro 'wpe_puente_consentimiento':
 *
 *   add_filter( 'wpe_puente_consentimiento', function ( $c ) {
 *       $c['evento_alta']['activo'] = false;   // sin evento sign_up
 *       return $c;
 *   } );
 */
if ( ! function_exists( 'wpe_puente_consentimiento_config' ) ) {
	function wpe_puente_consentimiento_config() {
		$config = array(
			// Cookie propia donde se guarda la elección: 1 = acepta analítica, 0 = rechaza.
			'cookie_eleccion'      => 'wpe_analytics',
			'dias_eleccion'        => 365,
			// IDs de los botones del banner (Lightweight Cookie Notice: 1 = rechazar, 2 = aceptar,
			// según la configuración de botones de este sitio; compruébalo en el tuyo).
			'botones_aceptar'      => array( 'daextlwcnf-cookie-notice-button-2', 'daextlwcnf-cookie-settings-button-2' ),
			'botones_rechazar'     => array( 'daextlwcnf-cookie-notice-button-1', 'daextlwcnf-cookie-settings-button-1' ),
			// Cookie que el banner deja cuando alguien aceptó antes de instalar este puente.
			// Déjala vacía si tu banner distingue ya entre aceptar y rechazar.
			'cookie_banner_legado' => 'daextlwcnf-accepted',
			// Evento GA4 al completarse una suscripción. Por defecto, formularios de GenerateBlocks Pro.
			'evento_alta'          => array(
				'activo'         => true,
				'evento'         => 'sign_up',
				'metodo'         => 'newsletter',
				'formulario'     => 'form.gb-form',
				'mensaje'        => '.gb-form-message',
				'clase_exito'    => 'gb-form-message--success',
				'atributo_id'    => 'data-gb-form-id',
			),
		);
		return (array) apply_filters( 'wpe_puente_consentimiento', $config );
	}
}

add_action(
	'wp_footer',
	function () {
		if ( is_admin() ) {
			return;
		}
		$json = wp_json_encode( wpe_puente_consentimiento_config() );
		$js   = <<<'JS'
(function (cfg) {
  window.dataLayer = window.dataLayer || [];
  function g() { window.dataLayer.push(arguments); }

  function leerCookie(n) {
    var m = document.cookie.match('(?:^|;\\s*)' + n.replace(/[-.]/g, '\\$&') + '=([^;]*)');
    return m ? m[1] : null;
  }
  function guardarEleccion(v) {
    document.cookie = cfg.cookie_eleccion + '=' + v + '; max-age=' + (cfg.dias_eleccion * 86400) +
      '; path=/; SameSite=Lax' + (location.protocol === 'https:' ? '; Secure' : '');
  }

  // 1. Consentimiento analítico. Solo se envía 'update' cuando hay un cambio real:
  //    al cargar, si no hay aceptación, se respeta el 'denied' por defecto sin repetirlo.
  var estado = null;
  function aplicar() {
    var c = leerCookie(cfg.cookie_eleccion);
    var legado = cfg.cookie_banner_legado && c === null && leerCookie(cfg.cookie_banner_legado) === '1';
    var siguiente = (c === '1' || legado) ? 'granted' : 'denied';
    if (siguiente === estado) { return; }
    if (estado === null && siguiente === 'denied') { estado = siguiente; return; }
    estado = siguiente;
    g('consent', 'update', { analytics_storage: siguiente });
  }
  document.addEventListener('click', function (e) {
    var el = e.target && e.target.closest ? e.target.closest('[id]') : null;
    if (!el) { return; }
    if (cfg.botones_aceptar.indexOf(el.id) !== -1) { guardarEleccion(1); setTimeout(aplicar, 50); }
    if (cfg.botones_rechazar.indexOf(el.id) !== -1) { guardarEleccion(0); setTimeout(aplicar, 50); }
  }, true);
  aplicar();
  window.addEventListener('load', aplicar);

  // 2. Evento de alta al mostrarse el mensaje de éxito del formulario.
  var ev = cfg.evento_alta || {};
  if (!ev.activo || !window.MutationObserver) { return; }
  function vigilar() {
    Array.prototype.forEach.call(document.querySelectorAll(ev.formulario), function (form) {
      var msg = form.querySelector(ev.mensaje);
      if (!msg) { return; }
      var enviado = false;
      new MutationObserver(function () {
        if (!enviado && msg.classList.contains(ev.clase_exito)) {
          enviado = true;
          g('event', ev.evento, { method: ev.metodo, form_id: form.getAttribute(ev.atributo_id) || '' });
        }
      }).observe(msg, { attributes: true, attributeFilter: ['class'] });
    });
  }
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', vigilar);
  } else {
    vigilar();
  }
})
JS;
		$codigo = $js . '(' . $json . ');';
		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $codigo, array( 'id' => 'wpe-puente-consentimiento' ) );
		} else {
			echo "<script id=\"wpe-puente-consentimiento\">\n" . $codigo . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	},
	// Prioridad alta: después de que Site Kit imprima su 'consent default' en la cabecera.
	99
);

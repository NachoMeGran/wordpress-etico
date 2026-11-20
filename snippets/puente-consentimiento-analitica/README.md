# Puente entre el banner de cookies y Consent Mode v2 (solo analítica)

Une un banner de cookies que no habla con Google y la etiqueta de Google Analytics de Site Kit, que por defecto **deniega** el almacenamiento de analítica. Cuando la persona pulsa «Aceptar», el snippet envía `gtag('consent', 'update', { analytics_storage: 'granted' })`; cuando pulsa «Rechazar», vuelve a `denied`. Además, si quieres, envía el evento `sign_up` de GA4 cuando un formulario de suscripción muestra su mensaje de éxito.

Solo toca `analytics_storage`. **No concede almacenamiento publicitario** (`ad_storage`, `ad_user_data`, `ad_personalization`).

## Por qué

**Legal y de privacidad.** En España, el art. 22.2 de la [LSSI](https://www.boe.es/buscar/act.php?id=BOE-A-2002-13758) exige consentimiento para las cookies no necesarias, como las de analítica. Site Kit declara por defecto todos los tipos de almacenamiento como `denied` en las regiones que tiene configuradas (comprobado en el código de Site Kit 1.192.0, `includes/Core/Consent_Mode/Consent_Mode.php`) y espera 500 ms a que una plataforma de consentimiento actualice el estado. Si tu banner no envía esa actualización, ocurre una de dos cosas: o la analítica no funciona nunca, o alguien «arregla» el problema concediendo todo por defecto. Este puente permite respetar la elección y medir solo a quien acepta.

Hay un detalle que motivó la cookie propia: con la configuración de nachomg.com, Lightweight Cookie Notice dejaba la misma marca al aceptar y al rechazar, así que la elección se registra aparte en `wpe_analytics` (1 o 0).

La [documentación de Google sobre el modo de consentimiento](https://developers.google.com/tag-platform/security/guides/consent) describe los comandos `consent default` y `consent update` que se usan aquí.

> Guía práctica, no asesoramiento jurídico.

## Requisitos

- [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/) con Analytics y el modo de consentimiento activos, o cualquier instalación de gtag.js que declare `analytics_storage: 'denied'` por defecto antes de este script.
- Un banner con botones de aceptar y rechazar identificables por `id`. Los valores por defecto son los de [Lightweight Cookie Notice](https://wordpress.org/plugins/lightweight-cookie-notice-free/) tal como está configurado en nachomg.com: comprueba en tu sitio qué botón es cuál.
- Para el evento `sign_up`: formularios de GenerateBlocks Pro, o cambia los selectores.

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar solo en el front», contenido de [`puente-consentimiento-analitica.php`](puente-consentimiento-analitica.php) sin la línea `<?php`.

**Como mu-plugin:** copia el archivo a `wp-content/mu-plugins/`.

El script se imprime en el pie (`wp_footer`, prioridad 99), después del `consent default` que Site Kit escribe en la cabecera.

## Configuración

```php
add_filter( 'wpe_puente_consentimiento', function ( $c ) {
	// Otro banner: IDs de sus botones.
	$c['botones_aceptar']  = array( 'mi-banner-aceptar' );
	$c['botones_rechazar'] = array( 'mi-banner-rechazar' );
	$c['cookie_banner_legado'] = '';      // si tu banner ya distingue aceptar y rechazar

	// Sin evento de alta, o con otro nombre.
	$c['evento_alta']['activo'] = false;
	return $c;
} );
```

## Cómo verificarlo

1. Ventana privada. En la consola: `dataLayer.filter(a => a[0] === 'consent')`. Debe aparecer solo el `default` con `analytics_storage: 'denied'`, y no deben existir cookies `_ga`.
2. Pulsa «Aceptar» y repite: aparece un `update` con `analytics_storage: 'granted'`, la cookie `wpe_analytics=1` y, tras el siguiente hit, las cookies `_ga`.
3. Pulsa «Rechazar» desde el botón de preferencias: nuevo `update` con `denied` y `wpe_analytics=0`.
4. Con Tag Assistant de Google se ve el estado de consentimiento de cada hit.
5. Para el evento: suscríbete con un correo de prueba y comprueba `sign_up` en el informe en tiempo real o en DebugView de GA4.

## Limitaciones

- Rechazar después de haber aceptado no borra las cookies `_ga` ya instaladas; GA deja de usarlas, pero si quieres eliminarlas debes hacerlo desde el banner o con código adicional.
- Si tu banner bloquea scripts por su cuenta o ya integra Consent Mode, no necesitas este puente y podría duplicar señales.
- Depende de los `id` del banner: una actualización del plugin que los cambie lo rompe en silencio. Verifícalo tras cada actualización.
- Con plugins de optimización que difieren o combinan JavaScript, comprueba que este script sigue ejecutándose después del `consent default`.
- El evento `sign_up` se basa en un cambio de clase en el mensaje de éxito; si el formulario cambia de marcado, deja de enviarse.

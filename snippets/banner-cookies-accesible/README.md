# Banner de cookies accesible por teclado

Muchos banners de cookies dibujan sus botones con `<div>`: se ven como botones, pero no reciben el foco con el tabulador, no responden a Intro ni a la barra espaciadora y el lector de pantalla no los anuncia como botones. Este snippet les da `role="button"`, `tabindex="0"` y manejo de teclado, pone nombre al botón de preferencias que solo tiene icono y marca el banner como región con nombre («Aviso de cookies»).

## Por qué

**Accesibilidad y legalidad a la vez.** Si una persona que navega con teclado no puede pulsar «Rechazar», el banner no le ofrece una elección real. La [guía de cookies de la AEPD](https://www.aepd.es/guias/guia-cookies.pdf) pide que la opción de rechazar se ofrezca en la misma capa y al mismo nivel que la de aceptar, con un mecanismo similar; un rechazo que solo funciona con ratón no lo cumple para todo el mundo. En términos de WCAG 2.2, el snippet corrige fallos de los criterios [2.1.1 Teclado](https://www.w3.org/WAI/WCAG22/Understanding/keyboard.html) y [4.1.2 Nombre, función, valor](https://www.w3.org/WAI/WCAG22/Understanding/name-role-value.html).

> Guía práctica, no asesoramiento jurídico.

## Requisitos

- Por defecto, [Lightweight Cookie Notice](https://wordpress.org/plugins/lightweight-cookie-notice-free/) (identificadores `daextlwcnf-*`, comprobados en la versión 1.23). Con otro banner, cambia los selectores.
- Navegadores con `MutationObserver` y `Element.matches` (todos los actuales).

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar solo en el front», contenido de [`banner-cookies-accesible.php`](banner-cookies-accesible.php) sin la línea `<?php`.

**Como mu-plugin:** copia el archivo a `wp-content/mu-plugins/`.

## Configuración

```php
add_filter( 'wpe_banner_accesible', function ( $c ) {
	$c['contenedor']    = '#mi-banner';
	$c['nombre_region'] = 'Aviso de cookies';
	$c['botones']       = array( '#mi-banner .aceptar', '#mi-banner .rechazar', '#mi-banner-preferencias' );
	$c['nombres']       = array( '#mi-banner-preferencias' => 'Cambiar preferencias de cookies' );
	return $c;
} );
```

## Cómo verificarlo

1. Ventana privada, sin tocar el ratón: pulsa Tab hasta llegar al banner. Cada botón debe recibir el foco (y verse el indicador de foco; si no se ve, revisa el CSS del tema).
2. Con el foco en «Rechazar», pulsa Intro y, en otra prueba, la barra espaciadora: el banner debe cerrarse y registrar la elección.
3. Con NVDA o VoiceOver: el banner se anuncia como región «Aviso de cookies» y cada opción como «botón».
4. El botón flotante para reabrir las preferencias debe anunciarse como «Cambiar preferencias de cookies, botón».

## Limitaciones

- Es un parche: la solución correcta es que el plugin use elementos `<button>`. Si lo hace en una versión futura, el snippet no toca los elementos que ya son `<button>` o `<a>`.
- No gestiona el orden del foco: si el banner aparece al final del documento, quien usa teclado tiene que recorrer toda la página para llegar a él. Eso depende del plugin.
- No añade estilos de foco; asegúrate de que el tema muestre un indicador visible.

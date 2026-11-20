# Cabeceras de seguridad y XML-RPC desactivado

Desactiva XML-RPC y los pingbacks, elimina el enlace RSD y la cabecera `X-Pingback`, oculta `X-Powered-By` y añade cinco cabeceras HTTP de seguridad a las páginas públicas.

| Cabecera | Valor por defecto | Para qué sirve |
|---|---|---|
| [`Strict-Transport-Security`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Strict-Transport-Security) | `max-age=31536000` | El navegador recuerda durante un año que la web solo se visita por HTTPS. |
| [`X-Content-Type-Options`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Content-Type-Options) | `nosniff` | Impide que el navegador «adivine» el tipo de un archivo y lo ejecute como script. |
| [`Referrer-Policy`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Referrer-Policy) | `strict-origin-when-cross-origin` | A otras webs solo les llega el dominio de origen, no la URL completa que visitaba la persona. |
| [`X-Frame-Options`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/X-Frame-Options) | `SAMEORIGIN` | Evita que otra web incruste la tuya en un iframe (*clickjacking*). |
| [`Permissions-Policy`](https://developer.mozilla.org/en-US/docs/Web/HTTP/Reference/Headers/Permissions-Policy) | `camera=(), microphone=(), geolocation=(), payment=()` | Bloquea cámara, micrófono, geolocalización y pagos, también para scripts de terceros incrustados. |

## Por qué

**Seguridad y privacidad.** XML-RPC es una vía clásica de ataques de fuerza bruta y de amplificación por pingbacks, y la mayoría de blogs no lo usa. `Referrer-Policy` reduce lo que tu web filtra a terceros sobre la navegación de tus visitantes y `Permissions-Policy` impide que un script de terceros pida acceso a la cámara o la ubicación. Ocultar `X-Powered-By` evita anunciar la versión de PHP. Ninguna de estas medidas sustituye a mantener WordPress y los plugins actualizados.

## Requisitos

- Una versión de WordPress con soporte (probado en 6.x).
- La web entera servida por HTTPS (para HSTS).

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar en todas partes», contenido de [`cabeceras-seguridad.php`](cabeceras-seguridad.php) sin la línea `<?php`.

**Como mu-plugin:** copia el archivo a `wp-content/mu-plugins/`. Es la opción recomendable: así las cabeceras no dependen de que un plugin esté activo.

## Configuración

```php
// Mantener XML-RPC (app móvil de WordPress, Jetpack, algunos editores externos).
add_filter( 'wpe_desactivar_xmlrpc', '__return_false' );

// Cambiar o quitar cabeceras.
add_filter( 'wpe_cabeceras_seguridad', function ( $h ) {
	unset( $h['X-Frame-Options'] );          // si tu web debe poder incrustarse
	$h['Strict-Transport-Security'] = 'max-age=300';  // valor bajo para probar
	return $h;
} );
```

## Cómo verificarlo

```bash
curl -sI https://tu-dominio.example/ | grep -iE "strict-transport|x-content-type|referrer-policy|x-frame|permissions-policy|x-powered-by|x-pingback"
```

Deben aparecer las cinco cabeceras y no `X-Powered-By` ni `X-Pingback`. Para XML-RPC:

```bash
curl -s -X POST -d '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>' https://tu-dominio.example/xmlrpc.php
```

Debe responder con un error o una lista vacía.

## Limitaciones

- **HSTS no tiene marcha atrás inmediata:** si un día sirves la web por HTTP, los navegadores que ya la visitaron se negarán a abrirla hasta que caduque `max-age`. Empieza con un valor bajo y sube cuando estés seguro. El snippet no envía la cabecera si la petición no llega por HTTPS.
- Si un proxy o CDN (Cloudflare, LiteSpeed, nginx) ya envía alguna de estas cabeceras, puede duplicarse. Revísalo con `curl -I`.
- Las páginas en caché completa pueden servirse sin pasar por PHP; en ese caso las cabeceras deben configurarse en el servidor.
- No añade `Content-Security-Policy`: es la cabecera más eficaz, pero depende de los scripts de cada web y no tiene un valor razonable por defecto.
- `X-Frame-Options` está superada por la directiva `frame-ancestors` de CSP; se mantiene por compatibilidad.

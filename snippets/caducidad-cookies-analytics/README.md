# Caducidad de las cookies de Google Analytics

Limita la duración de las cookies `_ga` y `_ga_*` de Google Analytics 4 a 13 meses (configurable) cuando la etiqueta la inserta **Site Kit by Google**.

## Por qué

Por defecto, Google Analytics renueva sus cookies con una duración de **dos años (63 072 000 segundos)** cada vez que se envía un hit, según la [referencia de configuración de GA4](https://developers.google.com/analytics/devguides/collection/ga4/reference/config) (campo `cookie_expires`). Quien visita la web una vez al mes nunca ve caducar su cookie.

La [Guía sobre el uso de las cookies de la AEPD](https://www.aepd.es/guias/guia-cookies.pdf) (actualizada en mayo de 2024) recomienda que, cuando se instalen cookies persistentes, se reduzca al mínimo necesario su duración atendiendo a su finalidad. La guía no fija una cifra para la duración de las cookies; 13 meses es la que declara la política de cookies de nachomg.com y una cifra habitual en Europa. Lo importante es que **la duración real coincida con la que declara tu política de cookies**, y eso es lo que resuelve este snippet.

> Guía práctica, no asesoramiento jurídico.

## Requisitos

- [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/) con Analytics conectado. El filtro `googlesitekit_gtag_opt` existe desde Site Kit 1.24.0 (comprobado en el código de la versión 1.192.0, `includes/Modules/Analytics_4/Web_Tag.php`).
- Las cookies de analítica solo deberían instalarse tras el consentimiento: combina este snippet con un banner de cookies y con el [puente de consentimiento](../puente-consentimiento-analitica/).

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar en todas partes», contenido de [`caducidad-cookies-analytics.php`](caducidad-cookies-analytics.php) sin la línea `<?php`.

**Como mu-plugin:** copia el archivo a `wp-content/mu-plugins/`.

## Configuración

```php
// En wp-config.php o antes de cargar el snippet:
define( 'WPE_MESES_COOKIES_ANALYTICS', 12 );

// O con un filtro:
add_filter( 'wpe_meses_cookies_analytics', fn() => 12 );
```

El cálculo es `meses × 365 / 12` días: 13 meses son 395 días (34 128 000 segundos).

## Cómo verificarlo

1. Abre la web en una ventana privada y acepta la analítica.
2. Herramientas de desarrollo → Aplicación → Cookies: `_ga` y `_ga_<ID>` deben caducar unos 13 meses después de la fecha actual, no dos años.
3. En el código fuente, la llamada `gtag('config', …)` de Site Kit debe incluir `"cookie_expires":34128000`.

## Limitaciones

- Solo funciona con la etiqueta de Site Kit. Si insertas gtag.js a mano o con Google Tag Manager, añade el campo `cookie_expires` en tu configuración (`gtag('config', 'G-XXXXXXXXXX', { cookie_expires: 34128000 })`) o en la etiqueta de Google de GTM.
- No borra las cookies ya instaladas con dos años: se corrigen en la siguiente visita, cuando GA las reescribe.
- No afecta a cookies publicitarias de Google (Ads, DoubleClick).

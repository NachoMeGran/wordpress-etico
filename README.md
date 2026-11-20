# WordPress ético

Siete snippets de WordPress que respetan la privacidad y la accesibilidad de quien visita una web. No son ejemplos de laboratorio: los saqué del código que funciona en [nachomg.com](https://nachomg.com/), les quité todo lo específico de mi sitio (IDs de bloques, mapas de categorías, rutas, datos de contacto) y lo convertí en configuración con filtros.

Están pensados para quien mantiene una web en WordPress y quiere medir solo con consentimiento, no regalar datos de sus visitantes a terceros sin necesidad y no dejar fuera a quien navega con teclado o lector de pantalla. Cada uno explica qué hace, por qué, qué necesita, cómo instalarlo, cómo comprobar que funciona y qué no resuelve.

> Los snippets que tocan cookies, consentimiento o protección de datos son una guía práctica. **No sustituyen el asesoramiento jurídico** sobre tu caso.

## Snippets

| Snippet | Qué hace | Motivo principal |
|---|---|---|
| [`navegacion-editorial`](snippets/navegacion-editorial/) | Ruta a la guía pilar, aviso de antigüedad, botones de compartir sin scripts de terceros, relacionadas y cabecera de categoría. Con CSS. | Privacidad y accesibilidad |
| [`indice-rank-math-accesible`](snippets/indice-rank-math-accesible/) | Da nombre accesible al `<nav>` del índice de Rank Math. | Accesibilidad |
| [`puente-consentimiento-analitica`](snippets/puente-consentimiento-analitica/) | Traslada la elección del banner de cookies a Consent Mode v2 (solo analítica) y, opcionalmente, envía `sign_up` a GA4. | Legal (LSSI, RGPD) |
| [`cabeceras-seguridad`](snippets/cabeceras-seguridad/) | Desactiva XML-RPC y añade HSTS, `nosniff`, `Referrer-Policy`, `X-Frame-Options` y `Permissions-Policy`. | Seguridad y privacidad |
| [`comentarios-rgpd-accesibles`](snippets/comentarios-rgpd-accesibles/) | Formulario de comentarios con etiquetas visibles, sin campo web, información del art. 13 RGPD y consentimiento validado en el servidor. Con CSS. | Accesibilidad y legal |
| [`banner-cookies-accesible`](snippets/banner-cookies-accesible/) | Hace usables con teclado y lector de pantalla los botones del banner de cookies. | Accesibilidad y legal |
| [`caducidad-cookies-analytics`](snippets/caducidad-cookies-analytics/) | Limita `_ga` y `_ga_*` a 13 meses (configurable) con Site Kit. | Privacidad |

## Instalación

Cada carpeta contiene un archivo `.php` que funciona de dos maneras:

1. **Con el plugin [Code Snippets](https://wordpress.org/plugins/code-snippets/).** Crea un fragmento de tipo PHP, pega el contenido del archivo **sin la primera línea** (`<?php`), elige dónde se ejecuta (lo indica cada README) y actívalo. Es lo más cómodo para probar y desactivar.
2. **Como *must-use plugin*.** Copia el archivo tal cual a `wp-content/mu-plugins/` (crea la carpeta si no existe). WordPress lo carga siempre, antes que los plugins normales, y no se puede desactivar desde el escritorio. Es lo recomendable para las cabeceras de seguridad.

Los archivos CSS van en **Apariencia → Personalizar → CSS adicional** o en la hoja de estilos de tu tema hijo.

Toda la configuración se cambia con filtros (`wpe_nav_config`, `wpe_puente_consentimiento`, `wpe_cabeceras_seguridad`…) desde otro snippet o desde el `functions.php` del tema hijo, así puedes actualizar el snippet sin perder tus ajustes.

## Principios

- **Nada se carga desde otro dominio** si no es imprescindible. Los botones de compartir son enlaces normales; ningún snippet añade scripts de terceros.
- **La analítica espera al consentimiento**, y el rechazo es tan fácil como la aceptación, como pide la [guía de cookies de la AEPD](https://www.aepd.es/guias/guia-cookies.pdf).
- **HTML semántico antes que ARIA**, y ARIA cuando el HTML no basta: nombres accesibles, regiones, mensajes de estado.
- **Lo que se declara se cumple:** si la política de cookies dice 13 meses, la cookie dura 13 meses.
- **Sin datos personales ni IDs reales** en el código: donde hace falta uno, hay un marcador (`privacidad@example.com`, `G-XXXXXXXXXX`).

## Comprobación

Todos los archivos PHP pasan `php -l` (PHP 8.3):

```bash
for f in snippets/*/*.php; do php -l "$f"; done
```

Antes de publicar los probé con funciones de WordPress simuladas (filtros, salida HTML y el JavaScript del pie con Node). Aun así, prueba cada snippet en un entorno de pruebas antes de llevarlo a producción: los plugins de caché, optimización y seguridad de cada sitio cambian las cosas.

## Licencias

| Qué | Licencia |
|---|---|
| Código (`.php`, `.css` y el JavaScript que contienen) | [MIT](LICENSE) |
| Documentación (`README.md` y demás textos) | [CC BY 4.0](LICENSE-DOCS) |

## Cómo citar

> Menéndez Granda, N. (2026). *WordPress ético: snippets que respetan la privacidad y la accesibilidad* (versión 1.0.0) [Software]. https://github.com/NachoMeGran/wordpress-etico

GitHub genera la cita en APA y BibTeX a partir de [`CITATION.cff`](CITATION.cff).

## Contribuir

Incidencias y *pull requests* bienvenidos. Criterios en [`CONTRIBUTING.md`](CONTRIBUTING.md); cambios de cada versión en [`CHANGELOG.md`](CHANGELOG.md).

## Autoría

Nacho Menéndez Granda, [consultor de marketing digital en Barcelona](https://nachomg.com/consultor-de-marketing-freelance-en-barcelona/). Escribo sobre marketing ético y uso responsable de la tecnología en [nachomg.com](https://nachomg.com/) y en la newsletter quincenal [Life on marketing](https://nachomg.com/life-on-marketing/). Si necesitas ayuda para aplicar esto en tu web, la vía es la [página de contacto](https://nachomg.com/contacto-profesional/).

# Índice de Rank Math con nombre accesible

Añade `aria-label="Índice del artículo"` al elemento `<nav>` que genera el bloque **Tabla de contenidos** de Rank Math.

## Por qué

El bloque `rank-math/toc-block` guarda su contenido dentro de un `<nav>` sin nombre (lo he comprobado en el código de Rank Math 1.0.282, `assets/src/save.js`). Si la plantilla ya tiene un menú principal en otro `<nav>`, quien navega por regiones con un lector de pantalla oye dos veces «navegación» sin saber cuál es cuál. Las [prácticas de ARIA del W3C](https://www.w3.org/WAI/ARIA/apg/practices/landmark-regions/) piden que, si un tipo de región aparece más de una vez en la página, cada instancia tenga una etiqueta única. Herramientas como axe lo señalan con la regla `landmark-unique`.

Es una corrección de **accesibilidad**: dos líneas de código y una molestia menos para quien no ve la pantalla.

## Requisitos

- WordPress 5.7 o superior (filtro `render_block_{nombre}`).
- [Rank Math SEO](https://wordpress.org/plugins/seo-by-rank-math/) con el bloque de tabla de contenidos en uso.

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** crea un fragmento PHP, pega el contenido de [`indice-rank-math-accesible.php`](indice-rank-math-accesible.php) **sin la primera línea** (`<?php`), elige «Ejecutar en todas partes» y actívalo.

**Como mu-plugin:** copia el archivo a `wp-content/mu-plugins/`. Se carga solo y no se puede desactivar desde el escritorio.

## Configuración

Para cambiar el texto (por ejemplo, en una web en inglés):

```php
add_filter( 'wpe_indice_aria_label', fn() => 'Table of contents' );
```

## Cómo verificarlo

1. Abre una entrada que tenga el bloque de índice.
2. Inspecciona el HTML: el `<nav>` del índice debe empezar por `<nav aria-label="Índice del artículo"`.
3. Con un lector de pantalla (NVDA, VoiceOver), abre la lista de regiones: deben aparecer «navegación, Índice del artículo» y tu menú, cada uno con su nombre. Si usas axe DevTools, la regla `landmark-unique` ya no debe fallar por este elemento.

## Limitaciones

- Solo actúa sobre el bloque de Gutenberg. Si insertas el índice con un shortcode o desde el constructor de otro tema, no se aplica.
- Si una versión futura de Rank Math añade su propio `aria-label` o `aria-labelledby`, el snippet lo respeta y no hace nada.
- Tu menú principal también debería tener nombre (`aria-label="Principal"` o similar); eso depende del tema.

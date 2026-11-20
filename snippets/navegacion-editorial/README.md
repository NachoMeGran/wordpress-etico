# Navegación editorial sin terceros

Cinco piezas de navegación para blogs, todas con HTML semántico y sin cargar nada de otros dominios:

1. **Ruta «Blog / Categoría»** en la cabecera de la entrada, con la categoría enlazada a su **guía pilar** (o a su archivo si no tiene guía).
2. **Aviso de antigüedad** en entradas de hace cinco años o más, y aviso específico para categorías personales.
3. **Botones de compartir sin scripts de terceros**: copiar enlace, menú nativo del sistema en móviles y enlaces a LinkedIn, X, Bluesky, WhatsApp y correo.
4. **Entradas relacionadas** de la misma categoría, con enlace a la guía.
5. **Cabecera de archivo de categoría** con nombre, descripción y enlace a la guía.

Incluye [`navegacion-editorial.css`](navegacion-editorial.css) con estilos de partida y variables para adaptarlo a tu tema.

## Por qué

**Privacidad.** Los botones de compartir de las redes sociales (los que cargan un script o un iframe de cada red) avisan a esas plataformas de cada visita, aunque nadie pulse nada. Aquí cada botón es un enlace normal a la URL pública de compartir de cada red: no se conecta con nadie hasta que la persona hace clic, y entonces es ella quien decide ir. Copiar enlace usa la [API del portapapeles](https://developer.mozilla.org/en-US/docs/Web/API/Clipboard/writeText) y el botón «Compartir…» la [Web Share API](https://developer.mozilla.org/en-US/docs/Web/API/Navigator/share) del propio dispositivo. Tampoco hacen falta cookies ni consentimiento.

**Accesibilidad.** Los botones de solo icono llevan `aria-label` («Compartir en LinkedIn (se abre en otra pestaña)»), los SVG están ocultos al lector de pantalla, la confirmación de «Enlace copiado» se anuncia con una región `role="status"` y los objetivos miden 44 × 44 px, por encima de los 24 × 24 px que pide el criterio [2.5.8 de WCAG 2.2](https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html). Las tarjetas de relacionadas son clicables enteras, pero contienen un solo enlace, así que el lector no repite cada entrada dos veces. El aviso de antigüedad usa `role="note"`.

**Honestidad editorial.** Avisar de que un texto tiene años es una cortesía con quien lee y evita que una referencia caducada pase por actual.

## Requisitos

- WordPress 5.7 o superior (filtros `render_block`).
- Para la ruta en la cabecera y la cabecera de categoría: una plantilla basada en bloques (de un tema de bloques o de un constructor como GenerateBlocks) donde puedas identificar el bloque que quieres sustituir. Los avisos, compartir y relacionadas funcionan con cualquier tema porque se añaden al contenido.

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar en todas partes», contenido de [`navegacion-editorial.php`](navegacion-editorial.php) sin la línea `<?php`. Copia el CSS en **Apariencia → Personalizar → CSS adicional** o en tu tema.

**Como mu-plugin:** copia el PHP a `wp-content/mu-plugins/` y encola el CSS desde tu tema.

## Configuración

Todo se ajusta con el filtro `wpe_nav_config`, en otro snippet o en el `functions.php` del tema hijo:

```php
add_filter( 'wpe_nav_config', function ( $c ) {
	// Categoría → guía pilar.
	$c['guias'] = array(
		'marketing' => array( '/guia-de-marketing/', 'Marketing' ),
		'series'    => array( '/cultura-pop/', 'Cultura pop' ),
		'cine'      => array( '/cultura-pop/', 'Cultura pop' ),
	);
	$c['blog_nombre']           = 'Blog';
	$c['categorias_personales'] = array( 'diario' );
	$c['aviso_anios']           = 5;              // 0 desactiva el aviso
	$c['redes']                 = array( 'linkedin', 'bluesky', 'mail' );
	$c['relacionadas']          = 3;              // 0 desactiva el bloque
	$c['relacionadas_orden']    = 'date';         // 'rand' es más variado pero más costoso

	// Ruta en la cabecera: bloque que contiene el enlace al blog y HTML que hay que sustituir.
	$c['ruta_en_bloque'] = array(
		'bloque'   => 'generateblocks/text',
		'atributo' => 'uniqueId',
		'valor'    => 'abc12345',                  // el uniqueId de TU bloque
		'buscar'   => '<a href="/blog/">Blog</a>',
	);

	// Cabecera de categoría: bloque de la plantilla de archivo que se sustituye.
	$c['cabecera_categoria'] = array(
		'bloque'   => 'generateblocks/element',
		'atributo' => 'uniqueId',
		'valor'    => 'def67890',
	);
	return $c;
} );
```

Para encontrar el `uniqueId` de un bloque de GenerateBlocks, abre la plantilla en el editor, cambia a «Editor de código» y búscalo en el comentario `<!-- wp:generateblocks/... {"uniqueId":"…"} -->`. Con otros bloques puedes usar cualquier atributo, o solo el nombre del bloque si es único en la plantilla.

Los textos visibles usan el dominio de traducción `wordpress-etico`, así que se pueden traducir con Loco Translate o un archivo `.po`.

## Cómo verificarlo

1. Abre una entrada de una categoría con guía: debajo del contenido aparecen «Compartir» y «Sigue leyendo» con el enlace a la guía.
2. En una entrada de hace más de cinco años aparece el aviso al principio del contenido.
3. Herramientas de desarrollo → Red, filtra por dominios externos y recarga: **ninguna petición** a LinkedIn, X, Bluesky ni WhatsApp hasta pulsar un botón.
4. Pulsa «Copiar enlace»: el texto cambia a «Enlace copiado» y el lector de pantalla lo anuncia.
5. En un móvil aparece además «Compartir…», que abre el menú del sistema.
6. Recorre los botones con Tab: todos reciben el foco con indicador visible y cada uno se anuncia con su nombre.

## Limitaciones

- Toma la **primera categoría** de cada entrada para la ruta, la guía y las relacionadas.
- `orderby => rand` es lento en sitios grandes y anula parte de la caché de consultas; por eso el valor por defecto es `date`.
- Las URL de compartir de cada red son las que funcionaban en noviembre de 2026; las redes las cambian de vez en cuando.
- Compartir por correo o WhatsApp desde escritorio depende de que haya una aplicación configurada.
- El enlace a otra red, una vez pulsado, sí envía la URL de la entrada a esa plataforma: es lo que la persona ha pedido.
- La cabecera de categoría sustituye el bloque completo. Si tu bloque original tenía otros elementos, perderás esos elementos.

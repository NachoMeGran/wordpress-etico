<?php
/**
 * Plugin Name: Navegación editorial sin terceros
 * Description: Ruta a la guía de cada categoría, aviso de antigüedad, botones de compartir sin scripts de terceros, entradas relacionadas y cabecera de categoría.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 14 de nachomg.com, generalizado.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 * Estilos: navegacion-editorial.css (en esta misma carpeta).
 */

defined( 'ABSPATH' ) || exit;

/*
 * =========================================================================================
 * CONFIGURACIÓN
 * Cambia los valores aquí o, mejor, con el filtro 'wpe_nav_config' desde otro snippet:
 *
 *   add_filter( 'wpe_nav_config', function ( $c ) {
 *       $c['guias'] = array(
 *           'marketing' => array( '/guia-de-marketing/', 'Marketing' ),
 *           'cine'      => array( '/cultura-pop/', 'Cultura pop' ),
 *       );
 *       $c['categorias_personales'] = array( 'diario' );
 *       return $c;
 *   } );
 * =========================================================================================
 */
if ( ! function_exists( 'wpe_nav_config' ) ) {
	function wpe_nav_config() {
		$pagina_blog = (int) get_option( 'page_for_posts' );
		$config      = array(
			// Enlace y nombre del blog en la ruta («Blog / Categoría»).
			'blog_url'              => $pagina_blog ? get_permalink( $pagina_blog ) : home_url( '/' ),
			'blog_nombre'           => 'Blog',

			// Mapa categoría → guía pilar: 'slug-de-categoria' => array( 'ruta/o/URL', 'Nombre de la guía' ).
			// Las categorías sin guía enlazan a su propio archivo.
			'guias'                 => array(),

			// Categorías personales: aviso específico y sin bloque de relacionadas.
			'categorias_personales' => array(),

			// Aviso de antigüedad: años a partir de los cuales se muestra (0 = desactivado).
			'aviso_anios'           => 5,

			// Botones de compartir: orden y redes (linkedin, x, bluesky, whatsapp, mail). Lista vacía = sin botones.
			'redes'                 => array( 'linkedin', 'x', 'bluesky', 'whatsapp', 'mail' ),

			// Entradas relacionadas: número (0 = desactivado) y orden ('rand' o 'date').
			'relacionadas'          => 3,
			'relacionadas_orden'    => 'date',

			// Ruta «Blog / Categoría» dentro de un bloque concreto de la plantilla de entrada.
			// Indica el nombre del bloque, el valor del atributo que lo identifica y el HTML del enlace al
			// blog que hay que sustituir. Vacío = desactivado. Ejemplo con GenerateBlocks:
			// array( 'bloque' => 'generateblocks/text', 'atributo' => 'uniqueId', 'valor' => 'abc123',
			//        'buscar' => '<a href="/blog/">Blog</a>' )
			'ruta_en_bloque'        => array(),

			// Cabecera de archivo de categoría: sustituye un bloque concreto de la plantilla de archivo
			// por una cabecera con nombre, descripción y enlace a la guía. Mismo formato, sin 'buscar'.
			'cabecera_categoria'    => array(),
		);
		return (array) apply_filters( 'wpe_nav_config', $config );
	}
}

/* ---------- Utilidades ---------- */

function wpe_nav_categoria( $post_id ) {
	$cats = get_the_category( $post_id );
	return $cats ? $cats[0] : null;
}

function wpe_nav_guia( $cat ) {
	if ( ! $cat ) {
		return null;
	}
	$guias = wpe_nav_config()['guias'];
	if ( ! isset( $guias[ $cat->slug ] ) ) {
		return null;
	}
	list( $ruta, $nombre ) = $guias[ $cat->slug ];
	return array(
		'url'    => preg_match( '#^https?://#', $ruta ) ? $ruta : home_url( $ruta ),
		'nombre' => $nombre,
	);
}

function wpe_nav_es_personal( $cat ) {
	return $cat && in_array( $cat->slug, wpe_nav_config()['categorias_personales'], true );
}

function wpe_nav_es_entrada_principal() {
	return is_singular( 'post' ) && get_the_ID() === get_queried_object_id();
}

function wpe_nav_bloque_coincide( $block, $destino ) {
	if ( empty( $destino['bloque'] ) || ( $block['blockName'] ?? '' ) !== $destino['bloque'] ) {
		return false;
	}
	if ( empty( $destino['atributo'] ) ) {
		return true;
	}
	return ( $block['attrs'][ $destino['atributo'] ] ?? null ) === $destino['valor'];
}

/* ---------- 1. Ruta «Blog / Categoría» enlazada a la guía ---------- */

add_filter(
	'render_block',
	function ( $html, $block ) {
		$destino = wpe_nav_config()['ruta_en_bloque'];
		if ( ! is_singular( 'post' ) || empty( $destino['buscar'] ) || ! wpe_nav_bloque_coincide( $block, $destino ) ) {
			return $html;
		}
		$cat = wpe_nav_categoria( get_queried_object_id() );
		if ( ! $cat ) {
			return $html;
		}
		$cfg   = wpe_nav_config();
		$guia  = wpe_nav_guia( $cat );
		$href  = $guia ? $guia['url'] : get_category_link( $cat );
		$ruta  = '<a href="' . esc_url( $cfg['blog_url'] ) . '">' . esc_html( $cfg['blog_nombre'] ) . '</a>'
			. ' <span aria-hidden="true">/</span> '
			. '<a href="' . esc_url( $href ) . '">' . esc_html( $cat->name ) . '</a>';
		return str_replace( $destino['buscar'], $ruta, $html );
	},
	10,
	2
);

/* ---------- 2. Aviso de antigüedad y pie de entrada ---------- */

add_filter(
	'the_content',
	function ( $content ) {
		static $hecho = false;
		if ( $hecho || is_admin() || is_feed() || ! wpe_nav_es_entrada_principal() ) {
			return $content;
		}
		$hecho   = true;
		$cfg     = wpe_nav_config();
		$post_id = get_the_ID();
		$cat     = wpe_nav_categoria( $post_id );
		$anio    = (int) get_the_date( 'Y', $post_id );
		$antes   = '';

		if ( wpe_nav_es_personal( $cat ) ) {
			$antes = '<p class="wpe-aviso" role="note">' . sprintf(
				/* translators: %d: año de publicación */
				esc_html__( 'Entrada personal del archivo, publicada en %d y fuera de la línea habitual del blog.', 'wordpress-etico' ),
				$anio
			) . '</p>';
		} elseif ( $cfg['aviso_anios'] > 0 && $anio && $anio <= (int) wp_date( 'Y' ) - (int) $cfg['aviso_anios'] ) {
			$antes = '<p class="wpe-aviso" role="note">' . sprintf(
				/* translators: %d: año de publicación */
				esc_html__( 'Publicada en %d. La mantengo por su interés, pero algunas referencias pueden haber envejecido.', 'wordpress-etico' ),
				$anio
			) . '</p>';
		}
		return $antes . $content . wpe_nav_compartir( $post_id ) . wpe_nav_relacionadas( $post_id, $cat );
	},
	20
);

/* ---------- 3. Botones de compartir sin terceros ---------- */

function wpe_nav_icono( $nombre ) {
	$p = array(
		'share'    => '<path d="M12 3v12M7 8l5-5 5 5M5 13v6a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2v-6"/>',
		'link'     => '<path d="M10 14a4 4 0 0 0 5.66 0l3-3a4 4 0 0 0-5.66-5.66l-1 1"/><path d="M14 10a4 4 0 0 0-5.66 0l-3 3a4 4 0 0 0 5.66 5.66l1-1"/>',
		'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="2"/><path d="M8 10v7M8 7v.01M12 17v-4a2 2 0 0 1 4 0v4M12 10v7"/>',
		'x'        => '<path d="M4 4l16 16M20 4L4 20"/>',
		'bluesky'  => '<path d="M12 11c-1.5-3-5-6-7-6-1 0-1 1.5-1 2.5S4.5 13 7 13c-2.5.5-3 2.5-1 4s4 0 6-4c2 4 4 5.5 6 4s1.5-3.5-1-4c2.5 0 3-4.5 3-5.5S20 5 19 5c-2 0-5.5 3-7 6z"/>',
		'whatsapp' => '<path d="M4 20l1.3-4A8 8 0 1 1 8 19.2z"/><path d="M9 9.5c0 3 2.5 5.5 5.5 5.5l1-1.5-2-1-1 1c-1-.5-2-1.5-2.5-2.5l1-1-1-2z"/>',
		'mail'     => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
	);
	return '<svg aria-hidden="true" focusable="false" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' . $p[ $nombre ] . '</svg>';
}

function wpe_nav_compartir( $post_id ) {
	$redes = wpe_nav_config()['redes'];
	if ( empty( $redes ) ) {
		return '';
	}
	$url    = get_permalink( $post_id );
	$titulo = wp_strip_all_tags( get_the_title( $post_id ) );
	$u      = rawurlencode( $url );
	$t      = rawurlencode( $titulo );

	// Enlaces de intención pública de cada red: no cargan nada hasta que la persona hace clic.
	$todas = array(
		'linkedin' => array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . $u ),
		'x'        => array( 'X', 'https://x.com/intent/post?text=' . $t . '&url=' . $u ),
		'bluesky'  => array( 'Bluesky', 'https://bsky.app/intent/compose?text=' . rawurlencode( $titulo . ' ' . $url ) ),
		'whatsapp' => array( 'WhatsApp', 'https://wa.me/?text=' . rawurlencode( $titulo . ' ' . $url ) ),
		'mail'     => array( 'correo', 'mailto:?subject=' . $t . '&body=' . $u ),
	);

	$o  = '<aside class="wpe-share" aria-labelledby="wpe-share-t">';
	$o .= '<p class="wpe-share__t" id="wpe-share-t">' . esc_html__( 'Compartir', 'wordpress-etico' ) . '</p><ul class="wpe-share__list">';
	// Menú nativo del sistema (Web Share API): oculto hasta que el JS confirme que existe.
	$o .= '<li class="wpe-share__native" hidden><button type="button" class="wpe-share__btn" data-share-native data-title="' . esc_attr( $titulo ) . '" data-url="' . esc_url( $url ) . '">'
		. wpe_nav_icono( 'share' ) . '<span>' . esc_html__( 'Compartir…', 'wordpress-etico' ) . '</span></button></li>';
	$o .= '<li><button type="button" class="wpe-share__btn" data-share-copy data-url="' . esc_url( $url ) . '">'
		. wpe_nav_icono( 'link' ) . '<span data-label>' . esc_html__( 'Copiar enlace', 'wordpress-etico' ) . '</span></button></li>';

	foreach ( $redes as $clave ) {
		if ( ! isset( $todas[ $clave ] ) ) {
			continue;
		}
		list( $nombre, $href ) = $todas[ $clave ];
		$es_correo             = ( 'mail' === $clave );
		$destino               = $es_correo ? '' : ' target="_blank" rel="noopener noreferrer"';
		/* translators: %s: nombre de la red social */
		$etiqueta = $es_correo ? __( 'Enviar por correo', 'wordpress-etico' ) : sprintf( __( 'Compartir en %s (se abre en otra pestaña)', 'wordpress-etico' ), $nombre );
		$o       .= '<li><a class="wpe-share__btn wpe-share__btn--icon" href="' . esc_url( $href, array( 'https', 'mailto' ) ) . '"' . $destino
			. ' aria-label="' . esc_attr( $etiqueta ) . '" title="' . esc_attr( $es_correo ? __( 'Correo', 'wordpress-etico' ) : $nombre ) . '">'
			. wpe_nav_icono( $clave ) . '</a></li>';
	}
	$o .= '</ul><p class="wpe-share__status" role="status" aria-live="polite"></p></aside>';
	return $o;
}

/* JS de compartir (nativo + copiar), solo en entradas. Sin dependencias ni llamadas externas. */
add_action(
	'wp_footer',
	function () {
		if ( ! is_singular( 'post' ) || empty( wpe_nav_config()['redes'] ) ) {
			return;
		}
		$textos = wp_json_encode(
			array(
				'copiado'   => __( 'Enlace copiado', 'wordpress-etico' ),
				'aviso'     => __( 'Enlace copiado al portapapeles', 'wordpress-etico' ),
				'copiar'    => __( 'Copiar enlace', 'wordpress-etico' ),
				'manual'    => __( 'Copia el enlace:', 'wordpress-etico' ),
			)
		);
		$js     = <<<'JS'
(function (tx) {
  var s = document.querySelector('.wpe-share'); if (!s) { return; }
  var st = s.querySelector('.wpe-share__status');
  var n = s.querySelector('[data-share-native]');
  // El menú nativo solo se ofrece en pantallas táctiles, donde es la forma natural de compartir.
  if (n && navigator.share && window.matchMedia('(pointer:coarse)').matches) {
    n.parentNode.hidden = false;
    n.addEventListener('click', function () {
      navigator.share({ title: n.dataset.title, url: n.dataset.url }).catch(function () {});
    });
  }
  var c = s.querySelector('[data-share-copy]');
  if (c) {
    c.addEventListener('click', function () {
      var hecho = function () {
        var l = c.querySelector('[data-label]');
        l.textContent = tx.copiado; st.textContent = tx.aviso; c.classList.add('is-done');
        setTimeout(function () { l.textContent = tx.copiar; st.textContent = ''; c.classList.remove('is-done'); }, 2500);
      };
      if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(c.dataset.url).then(hecho).catch(function () { window.prompt(tx.manual, c.dataset.url); });
      } else {
        window.prompt(tx.manual, c.dataset.url);
      }
    });
  }
})
JS;
		$codigo = $js . '(' . $textos . ');';
		if ( function_exists( 'wp_print_inline_script_tag' ) ) {
			wp_print_inline_script_tag( $codigo, array( 'id' => 'wpe-share-js' ) );
		} else {
			echo "<script id=\"wpe-share-js\">\n" . $codigo . "\n</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}
	},
	30
);

/* ---------- 4. Entradas relacionadas y enlace a la guía ---------- */

function wpe_nav_relacionadas( $post_id, $cat ) {
	$cfg = wpe_nav_config();
	if ( ! $cat || wpe_nav_es_personal( $cat ) || (int) $cfg['relacionadas'] < 1 ) {
		return '';
	}
	$guia = wpe_nav_guia( $cat );
	$q    = new WP_Query(
		array(
			'post_type'           => 'post',
			'posts_per_page'      => (int) $cfg['relacionadas'],
			'post__not_in'        => array( $post_id ),
			'cat'                 => $cat->term_id,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
			'orderby'             => 'rand' === $cfg['relacionadas_orden'] ? 'rand' : 'date',
		)
	);
	if ( ! $q->have_posts() && ! $guia ) {
		return '';
	}
	$o  = '<section class="wpe-related" aria-labelledby="wpe-related-t"><div class="wpe-related__head">';
	$o .= '<h2 class="wpe-related__t" id="wpe-related-t">' . esc_html__( 'Sigue leyendo', 'wordpress-etico' ) . '</h2>';
	if ( $guia ) {
		$o .= '<p class="wpe-related__guide">' . esc_html__( 'Guía:', 'wordpress-etico' ) . ' <a href="' . esc_url( $guia['url'] ) . '">' . esc_html( $guia['nombre'] ) . ' <span aria-hidden="true">→</span></a></p>';
	} else {
		/* translators: %s: nombre de la categoría */
		$o .= '<p class="wpe-related__guide"><a href="' . esc_url( get_category_link( $cat ) ) . '">' . esc_html( sprintf( __( 'Más en %s', 'wordpress-etico' ), $cat->name ) ) . ' <span aria-hidden="true">→</span></a></p>';
	}
	$o .= '</div><ul class="wpe-related__list">';
	foreach ( $q->posts as $p ) {
		// La imagen es decorativa (alt vacío): el título enlazado ya nombra la entrada.
		$img = get_the_post_thumbnail(
			$p,
			'medium_large',
			array(
				'class'   => 'wpe-related__img',
				'alt'     => '',
				'loading' => 'lazy',
			)
		);
		$o  .= '<li class="wpe-related__item">' . ( $img ? $img : '<span class="wpe-related__img wpe-related__img--empty" aria-hidden="true"></span>' )
			. '<p class="wpe-related__meta">' . esc_html( get_the_date( '', $p ) ) . '</p>'
			. '<h3 class="wpe-related__title"><a href="' . esc_url( get_permalink( $p ) ) . '">' . esc_html( wp_strip_all_tags( get_the_title( $p ) ) ) . '</a></h3></li>';
	}
	$o .= '</ul></section>';
	wp_reset_postdata();
	return $o;
}

/* ---------- 5. Cabecera de archivo de categoría ---------- */

add_filter(
	'render_block',
	function ( $html, $block ) {
		$destino = wpe_nav_config()['cabecera_categoria'];
		if ( ! is_category() || empty( $destino ) || ! wpe_nav_bloque_coincide( $block, $destino ) ) {
			return $html;
		}
		$cfg   = wpe_nav_config();
		$cat   = get_queried_object();
		$guia  = wpe_nav_guia( $cat );
		$desc  = $cat->description ? esc_html( $cat->description ) : '';
		if ( $guia ) {
			/* translators: %s: nombre de la guía */
			$desc .= ' <a href="' . esc_url( $guia['url'] ) . '">' . esc_html( sprintf( __( 'Empieza por la guía de %s', 'wordpress-etico' ), mb_strtolower( $guia['nombre'] ) ) ) . ' <span aria-hidden="true">→</span></a>';
		}
		$pagina = max( 1, (int) get_query_var( 'paged' ) );
		return '<header class="wpe-cat-header">'
			. '<p class="wpe-cat-header__kicker"><a href="' . esc_url( $cfg['blog_url'] ) . '">' . esc_html( $cfg['blog_nombre'] ) . '</a> — ' . esc_html__( 'Categoría', 'wordpress-etico' )
			/* translators: %d: número de página */
			. ( $pagina > 1 ? ' — ' . esc_html( sprintf( __( 'Página %d', 'wordpress-etico' ), $pagina ) ) : '' ) . '</p>'
			. '<h1 class="wpe-cat-header__title">' . esc_html( $cat->name ) . '</h1>'
			. ( $desc ? '<p class="wpe-cat-header__desc">' . $desc . '</p>' : '' )
			. '</header>';
	},
	10,
	2
);

<?php
/**
 * Plugin Name: Formulario de comentarios accesible y con consentimiento RGPD
 * Description: Etiquetas visibles y autocompletado en el formulario de comentarios, sin campo web, con primera capa de información sobre protección de datos y casilla de consentimiento obligatoria validada en el servidor.
 * Version:     1.0.0
 * Author:      Nacho Menéndez Granda
 * Author URI:  https://nachomg.com
 * License:     MIT
 *
 * Origen: snippet n.º 11 de nachomg.com, generalizado.
 * Si lo pegas en Code Snippets, omite la etiqueta de apertura «<?php» de la primera línea.
 *
 * Importante: el texto de protección de datos es un ejemplo. Ajústalo a tu tratamiento real
 * (responsable, finalidad, base jurídica, destinatarios, derechos). No es asesoramiento jurídico.
 */

defined( 'ABSPATH' ) || exit;

/*
 * Configuración. Rellena los datos del responsable aquí o con el filtro 'wpe_comentarios_rgpd'.
 */
if ( ! function_exists( 'wpe_comentarios_rgpd_config' ) ) {
	function wpe_comentarios_rgpd_config() {
		$config = array(
			'responsable'        => get_bloginfo( 'name' ),
			// Correo o página de contacto para ejercer derechos. Sustituye el marcador.
			'contacto_derechos'  => 'privacidad@example.com',
			// URL de la política de privacidad. Por defecto, la que tengas configurada en Ajustes > Privacidad.
			'url_privacidad'     => get_privacy_policy_url(),
			// ¿Muestra avatares de Gravatar tu tema? Si es así, se informa de ello.
			'informar_gravatar'  => (bool) get_option( 'show_avatars' ),
			// Quita el campo «Web» del formulario.
			'quitar_campo_web'   => true,
			// Texto que sustituye al aviso en inglés de GenerateBlocks Pro cuando JavaScript está desactivado.
			// Déjalo vacío si no usas GenerateBlocks Pro.
			'aviso_noscript_gb'  => 'Activa JavaScript para enviar este formulario.',
		);
		return (array) apply_filters( 'wpe_comentarios_rgpd', $config );
	}
}

/** Primera capa de información (art. 13 RGPD). */
if ( ! function_exists( 'wpe_comentarios_rgpd_texto' ) ) {
	function wpe_comentarios_rgpd_texto() {
		$c        = wpe_comentarios_rgpd_config();
		$contacto = is_email( $c['contacto_derechos'] )
			? esc_html( $c['contacto_derechos'] )
			: '<a href="' . esc_url( $c['contacto_derechos'] ) . '">la página de contacto</a>';
		$texto    = '<p class="wpe-rgpd"><strong>Protección de datos.</strong> '
			. 'Responsable: ' . esc_html( $c['responsable'] ) . '. '
			. 'Finalidad: publicar y moderar tu comentario. '
			. 'Legitimación: tu consentimiento. '
			. 'Se publican tu nombre y tu comentario; el correo y la IP no se muestran. ';
		if ( $c['informar_gravatar'] ) {
			$texto .= 'La imagen de perfil se obtiene de Gravatar (Automattic) a partir de una versión cifrada de tu correo. ';
		}
		$texto .= 'No se ceden datos a terceros. '
			. 'Derechos: acceso, rectificación, supresión, oposición, limitación y portabilidad en ' . $contacto . '.';
		if ( $c['url_privacidad'] ) {
			$texto .= ' Más información en la <a href="' . esc_url( $c['url_privacidad'] ) . '">política de privacidad</a>.';
		}
		$texto .= '</p>';
		return (string) apply_filters( 'wpe_comentarios_rgpd_texto', $texto );
	}
}

/** Campos: etiquetas visibles, autocompletado y consentimiento. */
add_filter(
	'comment_form_default_fields',
	function ( $fields ) {
		$c   = wp_get_current_commenter();
		$cfg = wpe_comentarios_rgpd_config();
		$req = ' <span class="wpe-req" aria-hidden="true">*</span>';

		$fields['author'] = '<p class="comment-form-author wpe-field"><label for="author">Nombre' . $req . '</label>'
			. '<input id="author" name="author" type="text" value="' . esc_attr( $c['comment_author'] ) . '" maxlength="245" autocomplete="name" required aria-required="true"></p>';
		$fields['email']  = '<p class="comment-form-email wpe-field"><label for="email">Correo electrónico' . $req . ' <span class="wpe-hint">(no se publica)</span></label>'
			. '<input id="email" name="email" type="email" value="' . esc_attr( $c['comment_author_email'] ) . '" maxlength="100" autocomplete="email" required aria-required="true"></p>';

		if ( $cfg['quitar_campo_web'] ) {
			unset( $fields['url'] );
		}

		// Casilla de WordPress para recordar nombre y correo (cookies de comentarios), si está activada.
		if ( isset( $fields['cookies'] ) ) {
			$checked           = empty( $c['comment_author_email'] ) ? '' : ' checked';
			$fields['cookies'] = '<p class="comment-form-cookies-consent wpe-check"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $checked . '>'
				. '<label for="wp-comment-cookies-consent">Recordar mi nombre y correo en este navegador para la próxima vez.</label></p>';
		}

		$fields['wpe_privacy'] = '<p class="comment-form-privacy wpe-check"><input id="wpe-privacy" name="wpe_privacy" type="checkbox" value="1" required aria-required="true">'
			. '<label for="wpe-privacy">Acepto que se traten mis datos para publicar y moderar este comentario.' . $req . '</label></p>'
			. wpe_comentarios_rgpd_texto();

		return $fields;
	},
	20
);

/** Textos del formulario y campo de comentario con etiqueta visible. */
add_filter(
	'comment_form_defaults',
	function ( $d ) {
		$req                       = ' <span class="wpe-req" aria-hidden="true">*</span>';
		$d['title_reply']          = 'Comenta esta entrada';
		$d['title_reply_to']       = 'Responder a %s';
		$d['cancel_reply_link']    = 'Cancelar respuesta';
		$d['title_reply_before']   = '<h2 id="reply-title" class="comment-reply-title">';
		$d['title_reply_after']    = '</h2>';
		$d['comment_notes_before'] = '<p class="comment-notes">Los campos con * son obligatorios.</p>';
		$d['comment_field']        = '<p class="comment-form-comment wpe-field"><label for="comment">Comentario' . $req . '</label>'
			. '<textarea id="comment" name="comment" cols="45" rows="6" maxlength="65525" required aria-required="true"></textarea></p>';
		$d['comment_notes_after']  = '';
		$d['label_submit']         = 'Publicar comentario';
		return $d;
	},
	20
);

/** El consentimiento se valida también en el servidor (el atributo required se puede saltar). */
add_filter(
	'preprocess_comment',
	function ( $data ) {
		if ( is_user_logged_in() ) {
			return $data;
		}
		$tipo = isset( $data['comment_type'] ) ? $data['comment_type'] : '';
		if ( '' !== $tipo && 'comment' !== $tipo ) {
			return $data; // Pingbacks y trackbacks no pasan por el formulario.
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- el formulario nativo de comentarios no usa nonce.
		if ( empty( $_POST['wpe_privacy'] ) ) {
			wp_die(
				esc_html__( 'Para publicar el comentario tienes que aceptar el tratamiento de tus datos. Vuelve atrás y marca la casilla.', 'wordpress-etico' ),
				esc_html__( 'Falta el consentimiento', 'wordpress-etico' ),
				array(
					'response'  => 400,
					'back_link' => true,
				)
			);
		}
		return $data;
	}
);

/** Traduce el aviso noscript de los formularios de GenerateBlocks Pro (opcional). */
add_filter(
	'gettext',
	function ( $traduccion, $texto ) {
		if ( 'Please enable JavaScript to submit this form.' === $texto ) {
			$aviso = wpe_comentarios_rgpd_config()['aviso_noscript_gb'];
			return $aviso ? $aviso : $traduccion;
		}
		return $traduccion;
	},
	10,
	2
);

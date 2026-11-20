# Formulario de comentarios accesible y con consentimiento RGPD

Rehace el formulario de comentarios de WordPress para que:

- cada campo tenga una **etiqueta visible** (no solo un texto de ejemplo que desaparece al escribir);
- los campos de nombre y correo usen `autocomplete="name"` y `autocomplete="email"`;
- desaparezca el campo «Web», que casi solo usan los spammers;
- se muestre una **primera capa de información sobre protección de datos** junto a una casilla de consentimiento obligatoria;
- el consentimiento se **valide también en el servidor**, porque el atributo `required` se puede saltar;
- el título del formulario sea un `<h2>` y los textos estén en español claro.

Incluye [`comentarios-rgpd-accesibles.css`](comentarios-rgpd-accesibles.css) con estilos de partida.

## Por qué

**Accesibilidad.** Las etiquetas visibles y asociadas a su campo responden al criterio [3.3.2 Etiquetas o instrucciones](https://www.w3.org/WAI/WCAG22/Understanding/labels-or-instructions.html) de WCAG 2.2, y el autocompletado al [1.3.5 Identificar el propósito de la entrada](https://www.w3.org/WAI/WCAG22/Understanding/identify-input-purpose.html). Las casillas miden 24 × 24 px, el mínimo del criterio [2.5.8 Tamaño del objetivo](https://www.w3.org/WAI/WCAG22/Understanding/target-size-minimum.html), y su etiqueta también es clicable.

**Protección de datos.** El [art. 13 del RGPD](https://www.boe.es/buscar/doc.php?id=DOUE-L-2016-80807) obliga a informar en el momento de recoger los datos: quién es el responsable, para qué se usan, con qué base jurídica y cómo ejercer los derechos. Un formulario de comentarios recoge nombre, correo e IP; poner la información básica junto al botón, con enlace a la política completa, es la forma más honrada de hacerlo.

> El texto de protección de datos del snippet es un **ejemplo**. Adáptalo a lo que de verdad hace tu web. Guía práctica, no asesoramiento jurídico.

## Requisitos

- Una versión de WordPress con soporte y comentarios activados en las entradas.
- Una página de política de privacidad configurada en **Ajustes → Privacidad** (o indica la URL en la configuración).

## Instalación

**Con [Code Snippets](https://wordpress.org/plugins/code-snippets/):** fragmento PHP, «Ejecutar en todas partes» (la validación ocurre al enviar el formulario), contenido de [`comentarios-rgpd-accesibles.php`](comentarios-rgpd-accesibles.php) sin la línea `<?php`. Copia el CSS en **Apariencia → Personalizar → CSS adicional** o en tu tema.

**Como mu-plugin:** copia el PHP a `wp-content/mu-plugins/` y encola el CSS desde tu tema.

## Configuración

```php
add_filter( 'wpe_comentarios_rgpd', function ( $c ) {
	$c['responsable']       = 'Nombre y apellidos o razón social';
	$c['contacto_derechos'] = 'privacidad@tu-dominio.example';   // o la URL de un formulario de contacto
	$c['url_privacidad']    = home_url( '/politica-de-privacidad/' );
	$c['aviso_noscript_gb'] = '';  // si no usas GenerateBlocks Pro
	return $c;
} );

// Para reescribir todo el texto informativo:
add_filter( 'wpe_comentarios_rgpd_texto', fn() => '<p class="wpe-rgpd">…</p>' );
```

Si tu web usa Akismet u otro servicio antispam externo, **el texto por defecto («No se ceden datos a terceros») no es cierto para ti**: cámbialo e informa de ese tratamiento.

## Cómo verificarlo

1. Cierra sesión y abre una entrada. Las etiquetas «Nombre», «Correo electrónico» y «Comentario» deben verse siempre, y no debe haber campo «Web».
2. Recorre el formulario con Tab: el orden es lógico y cada casilla se marca con la barra espaciadora.
3. Intenta enviar sin marcar la casilla de consentimiento: el navegador lo impide. Quita el atributo `required` desde las herramientas de desarrollo y envía: WordPress debe responder con el error «Falta el consentimiento» (código 400).
4. Con un lector de pantalla, cada campo se anuncia con su etiqueta y como obligatorio.

## Limitaciones

- No guarda prueba del consentimiento (fecha, versión del texto). Si la necesitas, añade un metadato al comentario en `comment_post`.
- Los usuarios con sesión iniciada no ven la casilla ni pasan la validación.
- Algunos temas o constructores sustituyen el formulario nativo; en ese caso estos filtros no se aplican.
- El ajuste de la casilla «Recordar mis datos» depende de **Ajustes → Comentarios**; si está desactivado, no aparece.

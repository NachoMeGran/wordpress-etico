# Cómo contribuir

Las incidencias y los *pull requests* son bienvenidos.

## Incidencias

Indica el snippet, la versión de WordPress y de los plugins implicados (Site Kit, Rank Math, el banner de cookies…), qué esperabas y qué ocurre. Si es un problema de accesibilidad, di con qué lector de pantalla y navegador lo has probado.

## Pull requests

- Un cambio por PR, con su explicación en el README del snippet si cambia el comportamiento o la configuración.
- Comprueba la sintaxis antes de enviarlo:

  ```bash
  for f in snippets/*/*.php; do php -l "$f"; done
  ```

- Nada de dependencias externas ni de llamadas a dominios de terceros sin una razón que se explique en el README.
- Nada de datos personales, IDs de medición reales (`G-…`, `AW-…`, `GTM-…`) ni claves en el código: usa marcadores.
- Mantén los prefijos `wpe_` en funciones, filtros y clases CSS.
- Las afirmaciones legales o técnicas de la documentación deben ir con enlace a la fuente primaria (BOE, AEPD, EDPB, W3C, MDN, documentación oficial del plugin).

El código se publica con licencia MIT y la documentación con CC BY 4.0.

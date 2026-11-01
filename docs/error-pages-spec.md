# Páginas 404 y 503 — especificación para implementación

- Estado: 404 implementada; plantilla 503 y controlador opcional preparados, pero **sin publicar ni activar en producción**. La cámara real espera una URL aprobada y 502 queda para una fase futura.
- Fecha de verificación de Figma: 2026-10-29.
- Plataforma objetivo: WordPress 6.6+, PHP 8.0+; tema clásico LTT Dive In.
- Idioma de la interfaz pública: inglés, con cadenas traducibles mediante `ltt-dive-in`.

## 1. Fuentes y decisiones

| Vista | Nodo del contenido | Composición completa |
| --- | --- | --- |
| 404 desktop | [3152:21468](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21468&m=dev) | [3152:21434](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21434&m=dev) |
| 404 mobile | [3152:21396](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21396&m=dev), contenido `3152:21398` | El mismo nodo incluye header y footer. |
| 503 desktop | [3152:21374](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21374&m=dev) | [3152:21350](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21350&m=dev) |
| 503 mobile | [3152:21422](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21422&m=dev), encabezado dentro de `3152:21421` | [3152:21419](https://www.figma.com/design/xRR5x46EumF8Lbvd4zoavZ/?node-id=3152-21419&m=dev); contenido `3152:21421`. |

Los nombres de capa `404: Not Found` dentro de la composición 503 son heredados y no definen el estado HTTP. Los textos decorativos grandes `404:` (`3152:21474`, `3152:21380` y `3152:21426`) están ocultos y no se renderizan. La variante móvil `3152:21409`, también oculta, no sustituye el contenido visible `3152:21398`.

[DESIGN.md](../DESIGN.md) conserva la autoridad sobre tokens ya confirmados. En estos frames el cuerpo aparece en **Raleway SemiBold**, pero se acordó usar **Gotham HTF Medium** del tema para 404/503; es una desviación deliberada. Los tamaños, alineaciones y espacios de las vistas siguen siendo objetivos de comparación. La inspección de Figma mostró `letterSpacing: 2%` para Big Caslon de 48 px, equivalente a **0.96 px**. No se incorporará Raleway ni se cambiarán tokens globales.

La anotación **NOTE FOR DEV** aportada por el cliente establece: “When site is down or bad gateway, default to a externally hosted live cam on this page instead of a search bar.” En esta fase se implementa la vista **503 de mantenimiento controlado con WordPress disponible**, que reserva el espacio para una cámara externa en vez de un buscador. **502 Bad Gateway queda como trabajo futuro**, por decisión posterior del cliente. La anotación no identifica la URL de la cámara ni su proveedor definitivo; el cliente consultará esa URL.

## 2. Composición y medidas

Las medidas son geometría de referencia de Figma, no alturas fijas que deban recortar texto. En pantallas pequeñas, con contenido ampliado o al 200% de zoom, cada sección debe crecer y conservar su orden de lectura. Autorizar únicamente los breakpoints desktop-first definidos en [AGENTS.md](../AGENTS.md).

| Elemento | Desktop, lienzo de 1440 px | Mobile, lienzo de 375 px | Fuente |
| --- | --- | --- | --- |
| Header | Composición: 86 px de alto aproximadamente. | 66 px de alto. | `3152:21434`, `3152:21350`, `3152:21397`, `3152:21419` |
| Área principal 404 | 1440 × 1209 px. | 375 × 608 px. | `3152:21468`, `3152:21398` |
| Área principal 503 | 1440 × 1209 px. | 375 × 735 px. | `3152:21374`, `3152:21421` |
| Banda superior de contenido | 238 px de alto; 64 px de padding horizontal; contenedor máximo de 1280 px; 64 px de padding superior. | 404: 240 px; 503: 298 px; 24 px de padding horizontal y 64 px superior. | `3152:21469`, `3152:21375`, `3152:21399`, `3152:21422` |
| Título | Big Caslon Medium, 48 px, `letter-spacing: 0.96px`, blanco cálido `#F9F9F9`, centrado. El marco de Figma mide 709 × 174 px. | Mismos tamaño, fuente, color y tracking; el título se parte en líneas naturales. El de 404 aparece como “You dove in / too deep...”; el de 503, “Great things / are on the / horizon...”. | `3152:21472`, `3152:21378`, `3152:21405`, `3152:21428` |
| Cuerpo | Gotham HTF Medium, 20 px, interlineado 145%, `#F9F9F9`, centrado; caja de Figma de 742 px. | Gotham HTF Medium, 14 px, interlineado 145%, `#F9F9F9`, centrado; caja de 349 px. | `3152:21477`, `3152:21383`, `3152:21407`, `3152:21430` |
| Bloque bajo el título | 404: grupo de 1186 × 144 px, comienza a los 238 px del área. 503: grupo de 1186 × 516 px, con 48 px entre párrafo y live. | 404: grupo de Figma de 377 × 126 px, comienza a 240 px. 503: grupo de 377 × 373 px, comienza a 298 px, con padding vertical de 48 px y live debajo. | `3152:21476`, `3152:21382`, `3152:21406`, `3152:21429` |
| Buscador 404 | En un área de 480 × 58 px, separada 28 px del cuerpo; barra visual de 25 px de alto. | En un área de 377 × 58 px con 48 px de padding lateral en Figma. | `3152:21478`, `3152:21408` |
| Live 503 | Fotograma de referencia de 596 × 381 px. | Fotograma de referencia de 265 × 169 px. | `3152:21384`, `3152:21431` |

El grupo móvil de 377 px comienza en `x = -1` dentro de un área de 375 px. Implementar el ancho disponible de forma fluida, sin el desbordamiento de 2 px. El buscador dibujado tiene solo 25 px de alto: ampliar el área clicable y de foco a un tamaño accesible, conservando su apariencia ligera y centrada. Por indicación posterior del cliente, se elimina la etiqueta visible “Search the site”; el campo conserva el placeholder visual “Search” y un nombre accesible programático. Esta es una excepción explícita a la preferencia de etiqueta visible en `AGENTS.md`. La búsqueda del header no reemplaza la búsqueda principal de 404.

### 2.1 Imágenes, overlays y recorte

| Uso | Nodo y referencia de imagen | Tratamiento |
| --- | --- | --- |
| Fondo 404, desktop/mobile | Fills de `3152:21468` y `3152:21398`; mismo `imageHash` de Figma `9c0046a86bad91b233f94fca5ab8d618a9f4bc8a`. | Paisaje nevado; `FILL`/equivalente a `cover`, centrado según la captura a 1440 y 375 px. |
| Fondo 503, desktop/mobile | Fills de `3152:21374` y `3152:21421`; mismo `imageHash` `d6de3167b63e24837719b1c88f0a99d64c353176`. | Lago al atardecer; `FILL`/`cover`, con el horizonte y la orilla visibles según ambas capturas. |
| Live ilustrado | `3152:21384` y `3152:21431`; `imageHash` `eda217044ffce98f529f4cbdb1de57ce9084e1f0`. | Captura de un reproductor ARCHR, **solo referencia visual**; no usarla como feed funcional. |

Cada fondo tiene una capa negra uniforme al **40%** y un gradiente negro superior que comienza al **46%** y llega a transparente al **36.554%** de la altura. La banda del título añade un gradiente vertical `rgba(2,22,43,0.6)` → `rgba(2,22,43,0.3)` al **72.115%** → transparente. Verificar la composición real de estas capas sobre ambas fotos: el gradiente no autoriza cambiar la paleta global. Las imágenes son decorativas respecto al mensaje y usan `alt=""` si se renderizan con `<img>`; una imagen CSS no necesita alternativa redundante.

Los **dos fondos originales** proceden de los fills de Figma. Los enlaces temporales de assets del MCP no son URLs de producción. La implementación conserva los WebP de máxima resolución como opción para pantallas de mayor densidad y deriva variantes más pequeñas del mismo fotograma. `background-position: center` y `background-size: cover` se mantienen en todos los breakpoints, para conservar el recorte sin introducir una foto diferente.

| Ancho de viewport | Fondo 404 (`1x` / `2x`) | Fondo 503 (`1x` / `2x`) |
| --- | --- | --- |
| Desktop, `≥1200px` | 2000 × 1501 / 2617 × 1963 px | 2000 × 2667 / 2487 × 3316 px |
| Tablet, `576–1199.98px` | 1600 × 1201 / 2617 × 1963 px | 1600 × 2134 / 2487 × 3316 px |
| Mobile, `≤575.98px` | 1080 × 811 / 1600 × 1201 px | 600 × 800 / 1200 × 1600 px |

Cada plantilla usa `image-set()` para elegir por densidad de píxeles y una declaración `url()` previa como fallback. Los cortes `1199.98px` y `575.98px` pertenecen a la escala aprobada; no representan dispositivos concretos. Verificar la imagen efectivamente solicitada y el recorte a 1440, 375 y 320 CSS px, además de ambos lados de esos cortes.

### 2.2 Copia pública

| Página | Título visible | Cuerpo de Figma |
| --- | --- | --- |
| 404 | `You dove in too deep...` | `We couldn’t find what you were looking for.` / `Use the search below to find your way:` |
| 503 | `Great things are on the horizon...` | `Our site is down for momentarily.` / `Take a moment to take a break or watch` / `a live feed from one of our live cams below:` |

La frase **“Our site is down for momentarily.”** contiene un error gramatical en el diseño. Mantenerla como texto de referencia y registrar cualquier corrección editorial antes de modificar el contenido público; no corregirla por cuenta propia. Los saltos de línea móviles son objetivos visuales, no `<br>` obligatorios: conservar la adaptación a 320 px y al zoom. Cada título visible será el único `h1` de su página; no mostrar un “404:” decorativo que duplique el estado.

El header y el footer de las composiciones se compararán con los componentes existentes en `header.php` y `footer.php`. Figma muestra datos de ejemplo (por ejemplo, `28°F`, enlaces y marcas del footer); el tema debe seguir mostrando sus datos reales. Las discrepancias previas de esos componentes se registran fuera del alcance de estas páginas. La 404 actual incluye una búsqueda y un botón “Back to homepage”; el diseño nuevo muestra solo búsqueda en su área principal. El botón no se conservará allí salvo decisión editorial posterior.

## 3. Comportamiento y propiedad del contenido

### 3.1 Página 404

Usar `404.php` en la jerarquía nativa de WordPress y conservar el estado **HTTP 404** y una respuesta no indexable. El formulario principal hace `GET` a `home_url( '/' )` con el parámetro `s`, funciona sin JavaScript y conserva valor, botón de envío y nombres accesibles. El campo lleva `aria-label="Search"` tras retirar la etiqueta visible por petición del cliente. Reutilizar el tratamiento compartido del buscador cuando pueda expresar el diseño; la colocación pertenece a `assets/css/templates/404.css`.

Los mensajes fijos de estado pueden ser cadenas traducibles del tema. Si se añade edición de texto o imagen en una fase posterior, la fuente será WordPress (página designada, título, contenido e imagen destacada), con fallback a esas cadenas y a los fondos del tema. No guardar la única copia de estos valores en una base de datos local. Si se introducen grupos ACF para contenido específico, versionar cada grupo una sola vez, en `acf-json/` **o** PHP, según [AGENTS.md](../AGENTS.md); la ausencia de ACF no debe romper la página.

### 3.2 Página 503 de mantenimiento controlado

En mantenimiento controlado con WordPress operativo, `503.php` y `assets/css/templates/503.css` presentan el mensaje y la cámara externa cuando esté configurada. Un plugin de sitio controla la activación, selecciona esa vista y sirve **HTTP 503**, `Retry-After` y encabezados que eviten caché. El plugin mantiene acceso de administración para personal autorizado y permite desactivar el modo sin editar el tema. La vista no puede devolver 200 ni convertirse en una página editorial pública ordinaria.

**Pendiente para una fase futura:** ante **502 Bad Gateway** o una caída que impida cargar WordPress/PHP, el servidor frontal o CDN deberá entregar una versión **estática e independiente** de la misma página visual, incluidos estilos, fuentes, fondo y cámara externa. Los recursos deberán estar disponibles aunque el origen WordPress falle. La infraestructura de producción y su configuración no figuran en este repositorio; no se implementan ni se validan en esta fase. Referencia de los códigos: [RFC 9110, secciones 15.6.3 y 15.6.4](https://www.rfc-editor.org/rfc/rfc9110.html).

El mantenimiento temprano de WordPress funciona de otra forma: `wp_maintenance()` carga `wp-content/maintenance.php` antes del tema cuando existe el estado de mantenimiento; [la documentación oficial](https://developer.wordpress.org/reference/functions/wp_maintenance/) describe ese drop-in y la respuesta 503 predeterminada. En ese punto no se debe asumir disponibilidad normal de `get_header()`, ACF, menús o el plugin de sitio. Si se desea la apariencia personalizada durante actualizaciones automáticas, habrá que coordinarla con la entrega estática o con un drop-in autocontenido, sin depender del tema cargado.

El modo de mantenimiento pertenece al plugin del sitio. La URL autorizada de la cámara todavía no está disponible; hasta recibirla, el template muestra un mensaje de indisponibilidad en el marco destinado al live. La integración final deberá validar el host y usar el embed oficial. La futura entrega estática necesitará su propia configuración independiente de WordPress.

El controlador versionado está en [`site-plugin/ltt-site-maintenance.php`](../site-plugin/ltt-site-maintenance.php) y la copia de desarrollo se instala en `wp-content/mu-plugins/ltt-site-maintenance.php`, fuera del directorio del tema. El archivo dentro de `site-plugin/` **no se carga automáticamente** al desplegar el repositorio del tema. Por decisión del cliente, **no copiarlo a `wp-content/mu-plugins/` en producción ni activar mantenimiento hasta completar y aprobar la 503**. Cuando se instale en una fase posterior, se cargará automáticamente con WordPress, pero su opción inicial estará **desactivada**. El administrador podrá activarlo en **Settings → Site Maintenance**, ajustar `Retry-After` y abrir una vista previa sin activar el modo. Los administradores conservarán acceso al sitio; los visitantes recibirán la vista 503 solo cuando se active. Esa pantalla permite configurar el host aprobado, el embed y el enlace público, todos vacíos hasta recibir la autorización. El tema exige un host HTTPS exacto mediante los filtros `ltt_dive_in_maintenance_camera_allowed_hosts`, `ltt_dive_in_maintenance_camera_embed_url` y `ltt_dive_in_maintenance_camera_public_url`. No configurar esos valores con una URL supuesta.

## 4. Live de ARCHR

La composición muestra “Everline Resort & Spa Cam” en un reproductor con marca ARCHR. La [página oficial de ARCHR CAPTURE](https://getarchr.com/products/capture/) describe un **Dynamic Embed for Partners** y capturas periódicas cada cinco minutos de día y quince de noche. También distingue sus interfaces y otras opciones de transmisión. Por ello, antes del lanzamiento hay que confirmar si el recurso concreto es una cámara de imágenes actualizadas, timelapse o video continuo; la palabra “live” del diseño no demuestra un flujo de video permanente.

La opción recomendada es integrar el **embed oficial que ARCHR autorice** para la cámara elegida, siempre que se confirme su alojamiento fuera de la infraestructura del sitio. El plugin del sitio guarda el host aprobado, la URL de embed y el enlace público; el tema vuelve a validar HTTPS y la coincidencia exacta del host antes de presentar el iframe en su marco responsive. Comprobar el host exacto con ARCHR y usar una lista cerrada de dominios, HTTPS, `title` descriptivo, carga diferida cuando proceda y permisos mínimos. Mantener controles nativos del proveedor, sin autoplay audible. Mostrar debajo un enlace textual a la cámara que funcione si el iframe está bloqueado o JavaScript no está disponible.

Si todavía no hay URL válida, no mostrar un rectángulo vacío ni la captura como si fuera live: presentar un mensaje breve de indisponibilidad y, si existe una página pública válida de la cámara, enlazarla. Una carga fallida **dentro** de un iframe de otro origen no se puede detectar fiablemente sin una API o evento documentado por ARCHR; no prometer recuperación automática que dependa de esa detección. El módulo actual `template-parts/blocks/video-module.php` acepta Cloudflare Stream, YouTube, Vimeo y MP4, y exige póster: no es un adaptador ARCHR y no debe reutilizarse directamente.

Antes de publicar: obtener URL o código oficial, permiso de inserción y política de uso, confirmar `frame-ancestors`/CSP y dominios, revisar privacidad/cookies, comprobar teclado y orden de foco dentro del iframe, títulos y controles a 375/320 px, y probar la alternativa de enlace. Comparar las proporciones visibles con **596 × 381 px** y **265 × 169 px** sin distorsionar el reproductor real.

## 5. Validación y aceptación

- Encolar cada hoja solo en su vista: `is_404()` para `404.css`; condición específica del plugin/controlador para `503.css`. Los estilos de componentes compartidos permanecen en `assets/css/components/`. No agregar CSS de página a `main.css` ni instalar un build system.
- Usar CSS desktop-first con los límites aprobados `1399.98`, `1199.98`, `991.98`, `767.98` y `575.98` px solo cuando el contenido lo requiera. La semántica y el orden DOM no cambian entre desktop y mobile.
- Comparar capturas locales de cada página con los nodos citados a **1440 px** y **375 px**: fuente efectivamente cargada, tamaño y saltos de títulos, distancia entre título/cuerpo/control, tonos y capas del overlay, imagen y recorte, tamaño y proporción del buscador/live. Guardar evidencia y anotar diferencias de header/footer fuera de alcance.
- Probar **320 CSS px**, **200% de zoom** y ambos lados de cada breakpoint usado; no debe aparecer desplazamiento horizontal ni texto recortado. Revisar contraste de texto y foco sobre las dos fotos, `prefers-reduced-motion` y controles en forced colors cuando corresponda.
- Comprobar `h1` único, `main` único, skip link, nombre accesible y foco del buscador, búsqueda por teclado y sin JavaScript, resultado 404 real y ausencia de enlaces vacíos.
- En 503, comprobar activación y desactivación por personal autorizado, HTTP 503, `Retry-After`, no-caché y acceso de administración. Probar con URL ARCHR válida, sin configurar, rechazada y bloqueada; verificar que el enlace alternativo y el mensaje sean útiles. Registrar explícitamente qué estados internos del proveedor no son observables.
- En la fase futura de 502, simular el origen WordPress/PHP inaccesible y verificar que el servidor frontal/CDN entrega la versión estática con sus assets y cámara externa, sin buscador, conservando el código HTTP del fallo.

### 5.1 Evidencia de esta fase (2026-10-29 y 2026-10-30)

- Los fondos exportados de Figma miden **2617 × 1963 px** (404) y **2487 × 3316 px** (503); ambos corresponden a los fills citados en §2.1. La conversión WebP conserva esas dimensiones y reduce sus tamaños de **728 a 480 KiB** y de **aproximadamente 2.1 a 1.0 MiB**, respectivamente.
- En 2026-10-30 se añadieron las variantes responsive indicadas en §2.1. A densidad `1x`, los archivos de desktop pesan **227 KiB / 382 KiB** (404/503), los de tablet **172 KiB / 273 KiB** y los de mobile **100 KiB / 48 KiB**. Todas las rutas `url()` de ambas hojas CSS resuelven a archivos no vacíos; se inspeccionaron las variantes móviles para detectar cambios de encuadre o artefactos evidentes.
- Sintaxis PHP correcta en todos los archivos del tema y en el MU plugin con el binario PHP 8.2 de Local. Pruebas aisladas superadas para mantenimiento desactivado, fallback 503 sin plantilla y rechazo/aceptación de URLs según HTTPS y host permitido; `git diff --check` sin errores.
- El sitio de desarrollo Local no respondió durante esta verificación. Quedan pendientes las capturas comparativas con Figma, la comprobación real de HTTP 404/503 y `Retry-After`, la búsqueda, la navegación por teclado, 320 px y zoom 200%. La vista sin cámara debe compararse como estado provisional; el embed real requiere la URL autorizada.

La aprobación de este documento no equivale a una validación visual de las páginas en WordPress Local.

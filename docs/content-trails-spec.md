# Content Trails — especificación C-02

- Estado: especificación para implementación; el bloque y las integraciones descritas no están implementados por este documento.
- Fecha: 2026-10-27.
- Bloque propuesto: `ltt-dive-in/content-trails`.
- Plataformas: WordPress 6.6+, PHP 8.0+, ACF Pro y las dependencias locales del tema.
- Idioma del documento: español. Nombres técnicos, controles editoriales y textos públicos: inglés, traducibles con `ltt-dive-in`.

## 1. Propósito, alcance y autoridad

Crear un módulo portable de recomendaciones mixtas que pueda insertarse en páginas, contenido de posts y áreas editoriales al final de artículos. Un mismo bloque ACF ofrece Content Trail y When are you visiting?, con fuentes Dynamic y Manual. Algolia se considera aprobado para esta especificación.

Esta entrega es exclusivamente documental. No registra bloques, modifica plugins, configura índices, cambia contenido ni migra End Page Cluster. La futura implementación comprende presentación, edición, calendario, consultas al índice y fallback. El conector, el modelo persistente de contenido y los servicios de consentimiento/seguimiento tienen responsables externos definidos en la sección 10.

Las decisiones de esta conversación resuelven las discrepancias indicadas abajo. C-02 y la sección 3.1 aportados por el cliente fijan los requisitos funcionales. Figma aporta composición, tarjetas y anotaciones. [DESIGN.md](../DESIGN.md) conserva la autoridad sobre los tokens ya confirmados y [AGENTS.md](../AGENTS.md) sobre las convenciones del proyecto. Este documento no modifica silenciosamente sus reglas globales.

### 1.1 Fuentes inspeccionadas

| Referencia | Nodos y contenido |
| --- | --- |
| [Content Trail: componente y notas](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11660&m=dev) | Tablero `1:11660`; anotaciones completas en `1:11664` |
| [Content Trail desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11669&m=dev) y [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11670&m=dev) | Carrusel, encabezado, CTA, indicadores y flechas |
| [Visiting con resultados desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11672&m=dev) y [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11671&m=dev) | Grid y carrusel de resultados |
| [Calendario desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11674&m=dev) y [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11673&m=dev) | Calendario expandido y cuatro accesos de temporada |
| [Visiting contraído desktop](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11676&m=dev) y [mobile](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=1-11675&m=dev) | Estado inicial con `Select Dates` |
| [Notas para desarrollo](https://www.figma.com/design/ojSvaH9s0cyWEnxWdk3ksH/?node-id=2145-12745&m=dev) | Inventario complementario; notas de eventos `2145:14652` |
| [Review Phase 2 — All Pages](https://www.figma.com/design/vBshJcx70aPkoObfGZzd4f/?node-id=95-12598&m=dev) | Home `95:20302`/`95:20322`; Experience Grouping `95:21129`/`95:21266` y `95:21327`/`95:21339`; When to Visit `95:23936`/`95:23947`; Season `95:24310`/`95:24324` |

### 1.2 Decisiones confirmadas y correcciones

| Tema | Decisión para esta especificación |
| --- | --- |
| Unidad editorial | Un bloque con dos variantes; no dos bloques independientes |
| Content Trail: grid frente a carrusel | Seguir el carrusel de Figma en desktop y móvil |
| Tarjeta de página | Page Driver, según C-02; no Inspiration Panel con pin aunque aparezca en Figma |
| Activities | CPT independiente `activity`, propiedad de un plugin de sitio; tarjeta Listing |
| Eventos | Mantener Seeker; no crear posts de eventos ni instalar The Events Calendar para este bloque |
| Personalización | Phase 1 por Rules y `ruleContexts`; no Recommend ni Personalization por usuario, a diferencia de las notas antiguas de Figma |
| Consulta dinámica | Search con `query: ""`; no el endpoint literal `browse`, incompatible con Rules |
| Respaldo | Selección manual obligatoria de 2–12 elementos en Dynamic |
| Fechas | La temporada de llegada determina los resultados, incluso si el viaje termina en otra temporada |
| End Page Cluster | Mantener campos, datos y comportamiento existentes; sin conversión automática al nuevo bloque |

Las referencias históricas de DESIGN.md a Algolia como provisional y a la ausencia de registros Seeker en Algolia describen el estado actual. La futura integración aquí especificada incorpora esos registros sin convertirlos en posts. La implementación deberá actualizar la documentación afectada cuando ese cambio exista realmente.

## 2. Variantes, modos y colocación

| Variante | Fuente Dynamic | Fuente Manual | Presentación |
| --- | --- | --- | --- |
| `content_trail` — Content Trail | Mezcla ordenada por `ltt_content`, con contexto de persona opcional | Selección editorial ordenada | Carrusel en todos los tamaños |
| `visiting` — When are you visiting? | Consulta estacional a `ltt_content` después de una selección | Selección editorial filtrada por temporada | Grid desde 768px; carrusel por debajo de 768px |

El bloque no aporta un H1. Usa un H2 para el título de sección y títulos de tarjeta subordinados. No duplica contenido significativo para resolver desktop/móvil. Cada instancia tiene identificadores únicos, estado, controles, resultados y mensajes propios, incluidos bloques anidados.

Se podrá insertar en las áreas del editor que admitan ACF Blocks. Una ubicación fuera de `the_content()`, como un pie editorial de artículo, requerirá que la plantilla tenga un área editable de bloques; no se insertará una instancia global automáticamente. Actualmente no existen detalles locales de eventos Seeker: su colocación en futuros detalles propios es una capacidad de integración, no una página incluida en este trabajo.

Los valores iniciales de nuevos bloques serán `content_trail`, `manual` y cantidad dinámica `4`. No se publicará una selección ficticia para completar un bloque vacío.

## 3. Modelo editorial ACF

Grupo Local JSON propuesto: `group_ltt_dive_in_content_trails`. Ubicación exclusiva: bloque `ltt-dive-in/content-trails`. Los nombres de campo y las claves usarán el prefijo `ltt_dive_in_content_trails` y permanecerán estables después de guardar contenido.

| Campo / etiqueta | Tipo y validación | Visibilidad |
| --- | --- | --- |
| `variant` / Variant | Select: Content Trail, When are you visiting?; obligatorio | Siempre |
| `mode` / Content source | Select: Dynamic, Manual; obligatorio | Siempre |
| `label` / Section label | Texto opcional; no sustituye al título | Siempre |
| `title` / Title | Texto obligatorio | Siempre |
| `body` / Body | Textarea opcional; texto, sin HTML arbitrario | Siempre |
| `cta` / Call to action | Link opcional; exige texto y URL válidos para renderizar | Siempre |
| `content_types` / Content types | Multiselect obligatorio: Blog posts, Hub pages, Activities, Events; al menos uno | Dynamic |
| `tile_count` / Number of tiles | Entero de 4 a 12; predeterminado 4 | Dynamic |
| `rule_context` / Persona rule context | Texto opcional, alfanumérico con guiones o guiones bajos; clave de Rule, no etiqueta libre de persona | Dynamic + Content Trail |
| `manual_items` / Selected content | Lista ordenable de 2–12 referencias, sin duplicados | Manual |
| `fallback_items` / Fallback content | Lista ordenable obligatoria de 2–12 referencias, sin duplicados; tipos compatibles con `content_types` | Dynamic |

Los nombres de la tabla son sufijos: por ejemplo, `ltt_dive_in_content_trails_tile_count`. En la UI se ocultarán controles irrelevantes mediante lógica condicional, conservando sus valores al cambiar variante o modo. Visiting nunca utilizará un `rule_context` conservado de Content Trail.

### 3.1 Selección mixta

Usar un repeater ordenable con `type` y una referencia condicional por fila:

- `post`: Post Object de un post publicado, devolviendo ID.
- `page`: Post Object de una página publicada marcada explícitamente como hub, devolviendo ID.
- `activity`: Post Object del CPT Activity publicado, devolviendo ID.
- `event`: selector remoto de Seeker reutilizado de Event Driver, guardando solamente el UUID validado.

Es una adaptación explícita del Relationship solicitado por C-02: Seeker no es un post type y no puede almacenarse en ese campo. No usar listas separadas que impidan intercalar y ordenar los cuatro tipos. No guardar copias editoriales del título, fotografía o descripción dentro del bloque.

Las filas de Visiting obtienen `seasons` de la fuente, tanto en Manual como en fallback. El editor debe informar qué elementos carecen de temporadas. Una lista de Visiting necesita al menos un elemento clasificable; no es obligatorio cubrir las cuatro temporadas y una temporada sin coincidencias tendrá estado vacío. Cambiar un campo oculto no debe borrar una selección ya guardada.

Los límites son validación editorial, no una condición para borrar contenido en runtime: si solo queda un elemento válido, mostrarlo sin controles de carrusel innecesarios. Si todos desaparecen, aplicar el estado vacío correspondiente. Duplicados se identifican mediante el `objectID` canónico, conservando la primera posición.

### 3.2 Persistencia y seguridad

Versionar el grupo en `acf-json/`, sin registrarlo simultáneamente en PHP. Los hooks para opciones dinámicas, búsqueda Seeker y validación pueden vivir en un archivo enfocado de `inc/`, cargado desde `functions.php`. Los metadatos de hubs, temporadas y el CPT no pertenecen a este grupo del tema.

Todo campo es opcional a efectos de ejecución: comprobar disponibilidad de ACF, estructura y valores antes de renderizar. Usar `get_field()` y escapar por contexto. No exponer clases CSS, colores, tamaños, niveles de encabezado ni HTML como opciones editoriales. La CTA sin etiqueta o destino válido se omite; los enlaces que abren otra pestaña necesitan aviso accesible y `rel` apropiado.

## 4. Componentes de tarjeta y datos necesarios

| Tipo canónico | Implementación existente | Datos necesarios y tratamiento |
| --- | --- | --- |
| `post` | [story-card.php](../template-parts/components/story-card.php), [story-card.css](../assets/css/components/story-card.css), adaptador en [story-drivers.php](../inc/story-drivers.php) | Título, URL e imagen válidos; categoría, excerpt y tiempo de lectura. El índice también incluye autor. Mantener la variante tile de carrusel en Content Trail y la variante responsive de grid en Visiting desktop; en Visiting móvil usar la variante tile del carrusel existente. No añadir una línea de autor si el componente nativo no la muestra. |
| `page` | Tarjeta dentro de [page-drivers/grid.php](../template-parts/components/page-drivers/grid.php), estilos en [page-drivers.css](../assets/css/components/page-drivers.css) | Hub publicado, título, imagen, excerpt, enlace y etiqueta de destino. Extraer la tarjeta como componente compartido. Usar su tratamiento Six-plus en carrusel y Four-up en el grid Visiting; no copiar el panel de lugar de End Page Cluster. |
| `activity` | [listing-card.php](../template-parts/components/listing-card.php), estilos de tarjeta en [listing-module.css](../assets/css/components/listing-module.css) | Título, imagen opcional con estado nativo sin imagen, excerpt, cero a dos botones y rollover copy opcional. Preservar prioridades de botones, tratamiento sin botones y marcos aprobados. Extraer los estilos internos para cargarlos sin FacetWP ni el comportamiento del Listing Module. |
| `event` | [event-card.php](../template-parts/components/event-card.php), [event-card.css](../assets/css/components/event-card.css), [seeker-events.php](../inc/seeker-events.php) | UUID, título, inicio, fin, zona horaria, venue, organizer, categorías, excerpt, imagen con rendiciones, URL Seeker y datos para Remind Me. Usar `tile` en desktop y su transición nativa a `card` en móvil. |

El bloque Activities actual selecciona páginas/posts y no constituye el CPT Activity solicitado. Sus selecciones existentes no se convierten ni se reindexan como actividades automáticamente.

Un adaptador por fuente convertirá WordPress, Seeker y registros Algolia al contrato de presentación de cada tarjeta. Las tarjetas PHP actuales dependen de IDs de adjuntos y, para eventos, de `DateTimeImmutable`; no se les entregará un hit JSON sin adaptación.

La implementación cliente tendrá renderizadores compartidos por tipo, con el mismo DOM semántico, clases y comportamiento que sus equivalentes PHP. No crear copias de tarjetas dentro del código del bloque ni consultas WordPress por cada hit. La paridad se verificará con registros equivalentes. Si se extraen componentes, sus consumidores actuales deben seguir funcionando.

El campo de rollover de Listing forma parte de 3.1, pero el renderer actual no lo presenta. Su incorporación será una ampliación del componente compartido, con acceso por teclado y touch, no una tarjeta exclusiva de Content Trail. No reproducir los placeholders de productos, precios o textos de muestra presentes en algunos frames.

## 5. Composición responsive y accesibilidad

### 5.1 Referencias visuales y reglas de interpolación

Los frames inspeccionados usan superficie clara, encabezado alineado a la izquierda y CTA outline. Content Trail presenta CTA a la derecha del encabezado en desktop y debajo del texto en móvil. Visiting coloca Select Dates al lado del encabezado en desktop y debajo en móvil; una CTA editorial adicional permanece separada del control de fechas.

| Medida observada | Uso en implementación |
| --- | --- |
| Content Trail desktop: frame 1440px, contenedor 1280px situado a 80px, padding vertical 50px | Referencia del encabezado y composición, con track que puede extenderse hasta el borde derecho |
| Mobile: frame 375px, márgenes laterales 20px, padding vertical 50px | Mantener contenido dentro del viewport también a 320px |
| Título de sección: Big Caslon Medium, 48px, tracking 2% en los frames desktop/mobile | Regla de este componente, sin cambiar globalmente H2; permitir wrapping y altura intrínseca |
| Intro desktop/mobile: Gotham HTF Medium 18px, line-height 145%, tracking 0% | Mantener la misma escala en ambas referencias; adaptar el ancho y permitir wrapping |
| Tarjetas del ejemplo: aproximadamente 335 × 484px; Content Trail móvil muestra una tarjeta de unos 313px y parte de la siguiente | Referencia del track; las tarjetas conservan sus proporciones y estados nativos, sin forzar altura fija al texto |
| Separación de tarjetas Content Trail: 11px desktop, 6px móvil; controles inferiores separados 48px | Aplicar al carrusel correspondiente, no a los estilos internos de cada tarjeta |

Visiting desktop contiene cuatro tarjetas de 335px y separaciones de 11px dentro de un frame declarado de 1280px: la suma excede ese ancho. Registrar esta inconsistencia de Figma y usar columnas fluidas que quepan, no reproducir overflow accidental. Como interpolación de ingeniería, Visiting usará cuatro columnas desde 1200px, dos entre 768px y 1199.98px y carrusel por debajo de 768px. Esta interpolación no es una medición de un frame intermedio.

Las alturas se ajustan al contenido más largo. Los carruseles no tendrán autoplay ni loop con clones; los botones se deshabilitan en los límites y permiten toda navegación sin arrastrar. Reducir animación y momentum con `prefers-reduced-motion`.

CSS desktop-first con límites descendentes `1399.98px`, `1199.98px`, `991.98px`, `767.98px` y `575.98px`, usando únicamente los que cambien la composición. No instalar Bootstrap. Reutilizar Swiper, Alpine, botones, tags, flechas e indicadores ya registrados; no añadir otra copia de las librerías.

### 5.2 Requisitos accesibles

- Sección etiquetada por su H2, orden DOM coherente y controles con nombres únicos por instancia. No añadir otro `main`.
- Enlaces para navegación y botones para acciones; no convertir un `<article>` completo en un control artificial.
- Hover reveal también disponible en foco y touch según la tarjeta nativa. Conservar lectura de contenido significativo y evitar enlaces duplicados en el orden de tabulación.
- Imágenes decorativas de Story, Page Driver y Event conservan `alt=""`; Listing mantiene el flujo de alt de Media Library. El índice debe conservar las alternativas necesarias.
- Focus visible, sin quedar oculto por viewport o cabecera. No desplazar foco al terminar una consulta; anunciar cantidad y estado en una región `aria-live="polite"`.
- No reemplazar una tarjeta que contiene el foco mientras llega una actualización; diferir el intercambio hasta que el foco salga. Reiniciar el carrusel al primer resultado cuando el usuario aplique otro filtro.
- Contraste WCAG 2.2 AA, forced-colors, zoom de 200% y reflow a 320 CSS px. Los colores de selección del calendario no podrán depender únicamente del color ni conservar combinaciones insuficientes, como texto blanco pequeño sobre cyan.

## 6. When are you visiting? y calendario Alpine

El calendario es un disclosure en línea, no un modal ni un select de opciones simples. No modificar `lttSelect` para alojar lógica de fechas. Los selects ordinarios que se necesiten usarán el [componente compartido](../template-parts/components/select.php).

Reutilizar la instalación local Alpine y los estilos [calendar-dropdown.css](../assets/css/components/calendar-dropdown.css) y [calendar.css](../assets/css/components/calendar.css). Esos archivos no equivalen a una interacción de calendario ya implementada. Corregir sus estados y accesibilidad dentro de su componente al desarrollar la funcionalidad.

### 6.1 Estados y transiciones

1. **Inicial:** encabezado y `Select Dates`, calendario cerrado, sin resultados y sin consulta Algolia. El formulario nativo permanece disponible como alternativa progresiva.
2. **Abierto:** mostrar mes actual o llegada ya seleccionada, navegación anterior/siguiente, campos `Arrival date` y `Departure date`, accesos de temporada, `Apply dates` y `Clear selection`.
3. **Edición:** mantener separados valores provisionales y selección aplicada. Una llegada posterior a la salida limpia la salida provisional; `Apply dates` muestra un error asociado si falta una fecha o el orden no es válido. Permitir llegada y salida el mismo día.
4. **Aplicación:** cerrar el panel, actualizar el nombre visible del control con el rango, devolver foco al disparador y mostrar resultados de la temporada de llegada. La solicitud solo se dispara al aplicar; navegar entre meses no consulta Algolia.
5. **Acceso de temporada:** Winter, Spring, Summer o Fall aplica inmediatamente ese valor, limpia el rango de fechas y cierra el panel. No inventa fechas para el visitante.
6. **Limpieza:** cancelar solicitudes pendientes, limpiar selección aplicada/provisional y volver al estado inicial, sin resultados.
7. **Escape:** cerrar sin aplicar cambios provisionales y devolver foco a Select Dates. Volver a abrir conserva la selección aplicada.

Las fechas son valores de calendario `YYYY-MM-DD`, sin convertir la llegada mediante UTC ni la zona del navegador. El mes inicial y el concepto de hoy usan la zona configurada en WordPress, no la del servidor del desarrollador. Se inicia en el mes actual; el mes July 2026 de Figma es contenido de muestra. La selección es de orientación estacional, no una reserva: no bloquear fechas pasadas ni imponer un horizonte arbitrario.

| Valor `seasons` | Etiqueta | Meses de llegada |
| --- | --- | --- |
| `winter` | Winter | Diciembre, enero, febrero |
| `spring` | Spring | Marzo, abril, mayo |
| `summer` | Summer | Junio, julio, agosto |
| `fall` | Fall | Septiembre, octubre, noviembre |

Del 28 de febrero al 4 de marzo se consulta `winter`; del 30 de diciembre al 3 de enero también. `seasons` no distingue años. No prometer que un evento coincida con el viaje: los eventos solo necesitan ser vigentes y pertenecer a la temporada. La UI incluirá la ayuda `Recommendations are based on your arrival season, not exact travel dates.`

### 6.2 Teclado y alternativa nativa

Select Dates expone `aria-expanded` y `aria-controls`. Al abrir, el foco entra al día seleccionado o al día actual. El calendario ofrece nombres completos de fecha, un solo día en el orden Tab, flechas para días/semanas, Home/End para la semana y Page Up/Page Down para meses. Enter/Space selecciona; Tab recorre el resto de controles sin trap. Los días se agrupan con semántica accesible de calendario y el rango seleccionado tiene un indicador adicional al color.

El formulario nativo incluye etiquetas visibles, dos campos `type="date"`, un select de temporada y un botón `Show recommendations`. Una temporada explícita tiene precedencia sobre las fechas; de lo contrario se exige un rango válido y se deriva la temporada de llegada. Los errores se muestran junto a los campos y no se sustituyen por una consulta sin filtro.

Sin JavaScript, el GET se dirige a la URL WordPress del documento y ancla de la instancia. Sus parámetros se agrupan por identificador estable del bloque y conservan los de otras instancias. El servidor consulta exclusivamente la lista de respaldo/manual del bloque solicitado, nunca IDs o tipos arbitrarios enviados por el visitante. Para una nueva selección con JavaScript se usa el mismo cálculo de temporada.

## 7. Contrato del índice y de presentación

### 7.1 Separación de sistemas

| Sistema | Índice / integración | Alcance aquí |
| --- | --- | --- |
| Site search y blog index | `wp_searchable_posts`, WP Search with Algolia e InstantSearch | No modificar, consultar ni reutilizar como fallback de Content Trails |
| Content Trails y otros dynamic drivers | `ltt_content`, conector propio de Convoy DevOps | Único índice consultado por este bloque |

La sección 3.1 describe la búsqueda como existente bajo la cuenta de Last Waav Studios; este documento no verifica su estado remoto ni sus credenciales. C-05, C-06, C-07, C-09 y C-14 pueden compartir infraestructura, pero su implementación o migración queda fuera del alcance del nuevo bloque.

### 7.2 Esquema mínimo

Los nombres comunes siguientes provienen de 3.1. Las estructuras detalladas y campos auxiliares son el contrato de integración propuesto por esta especificación, no afirmaciones sobre un índice ya configurado. Convoy debe suministrarlos o adaptar sus datos antes de habilitar Dynamic.

| Campo | Forma y significado |
| --- | --- |
| `objectID` | String estable: `post_<WP-ID>`, `page_<WP-ID>`, `activity_<WP-ID>`, `event_<Seeker-UUID>`; nunca posición de tarjeta ni URL mutable |
| `contentType` | Facet: `post`, `page`, `activity`, `event`; corresponde al tipo lógico, no al nombre del componente |
| `title` | Texto plano no vacío |
| `excerpt` | Texto plano; admite vacío y lo recorta el componente según sus reglas nativas |
| `image` | Objeto o `null`: `src`, `alt`, `width`, `height` y rendiciones `sources: [{ url, width }]`; `attachmentId` opcional para render PHP de imágenes WordPress |
| `url` | Destino público válido; para eventos, `eventurl` Seeker normalizada |
| `date` | ISO 8601 con offset: publicación para contenido WordPress e inicio de la ocurrencia representada para eventos; no se usa como temporada de posts/hubs |
| `tags`, `categories` | Arrays de `{ id, slug, name }`; vacíos cuando no existan. IDs de origen serializados como strings y orden estable para elegir la etiqueta principal |
| `seasons` | Array sin duplicados de `winter`, `spring`, `summer`, `fall`; vacío significa sin clasificación, no todo el año |

| Tipo | Campos adicionales |
| --- | --- |
| Post | `readTime`: minutos enteros positivos; `author`: nombre público y URL pública opcional, sin email ni datos privados |
| Page | `hubLink: { url, label }`, ambos válidos; el conector solo incluye páginas con la marca de hub. En el adaptador actual el destino es el permalink y la etiqueta predeterminada es `Explore` |
| Activity / tarjeta Listing | `buttons`: array de hasta dos `{ title, url, target, variant }`, con variantes aprobadas; `rolloverCopy`: texto opcional. La URL del registro no crea un botón que el editor no configuró |
| Event | `eventStart`, `eventEnd`: ISO 8601; `eventTimezone`: zona IANA; `eventEndTimestamp`: segundos Unix para vigencia; `eventAllDay`: boolean; `venue`, `organizer`: nombres públicos; `reminderUrl`: URL pública del endpoint WordPress generado para su UUID |

El conector debe normalizar el fin efectivo de un evento sin hora/fin explícitos usando la política compartida de Seeker, incluido final de día local para all-day. PHP y JavaScript deben mostrar la misma ocurrencia, fecha y estado de vigencia. No inferir zonas desde el navegador ni usar un `event_<id>` de WordPress para Seeker.

Para contenido WordPress, las temporadas se editan en metadatos/taxonomía del plugin de sitio. Para Seeker, el conector deriva temporadas del calendario de la ocurrencia representada y expone el mismo dato al adaptador de respaldo, sin depender de consultar Algolia. Los elementos válidos todo el año llevan las cuatro temporadas explícitas. No deducir la temporada de un post por su fecha de publicación.

El índice puede servir otros módulos con listings adicionales; este bloque limita sus fuentes a los cuatro tipos aprobados. No interpretar cualquier CPT público como `activity`. Solo registros publicados, públicos y elegibles deben formar parte del índice; despublicar, privatizar o eliminar debe retirar el registro.

### 7.3 Configuración y consultas

La integración entrega al tema configuración pública de Application ID, nombre del índice `ltt_content`, clave restringida a búsqueda de ese índice y estado de disponibilidad. No añadir campos de credenciales a cada bloque, copiar claves de administración al navegador ni reutilizar credenciales del plugin de búsqueda.

Usar Search con `query: ""`, `hitsPerPage` igual a la cantidad configurada y `page: 0`. Algolia documenta que `browse` fuerza `enableRules: false` y no recopila Search Analytics; por ello no satisface Phase 1. Véase [Browse for records](https://www.algolia.com/doc/libraries/sdk/methods/search/browse).

Construir filtros solo desde valores permitidos, usando OR entre tipos y AND con las restricciones restantes. Excluir eventos cuyo fin efectivo haya pasado; un ejemplo conceptual es `(contentType:post OR contentType:event) AND (NOT contentType:event OR eventEndTimestamp >= now)`, donde `now` es un timestamp numérico, no un literal enviado a Algolia. Visiting añade `seasons:<valor>` y no hace consultas antes de aplicar una selección.

En Content Trail, `rule_context` explícito prevalece; si está vacío, usar el contexto que suministre Convoy a partir de la página y la afinidad permitida. Si no hay contexto válido, omitirlo. El contexto explícito se envía como array de un elemento en `ruleContexts`; los contextos externos respetan la validación y el máximo de 10 que documenta [ruleContexts](https://www.algolia.com/doc/api-reference/api-parameters/ruleContexts). No inventar Rules en el tema. Las 4–6 personas, sus pesos y el contador pertenecen a Convoy.

En Visiting, omitir contextos y desactivar Rules de persona; usar `enableRules: false` para evitar reglas globales que alteren esa selección estacional. En ambas variantes mantener `enablePersonalization: false` y `enableReRanking: false`, de modo que Phase 1 no active implícitamente productos de ranking conductual. Con consentimiento, la consulta usa el mismo `userToken` que Insights; sin consentimiento no se crea ni transmite un identificador de seguimiento propio.

Mantener el orden devuelto por Algolia. No imponer cuotas por tipo ni garantizar que siempre aparezcan los cuatro. No agregar paginación o Load More: el límite del bloque es 12. Validar cada hit, deduplicar por `objectID`, descartar tipos desconocidos y representar solo campos seguros. No insertar HTML de Algolia con `innerHTML`/`x-html`; los textos son datos, no plantillas.

## 8. Render, fallback y manejo de fallos

### 8.1 Secuencia de carga

En Manual, PHP resuelve referencias publicadas y eventos Seeker en lotes, conserva el orden editorial y genera tarjetas desde sus componentes. No consulta Algolia. En Dynamic, PHP resuelve la lista de respaldo con los mismos adaptadores y la utiliza como base; el cliente intenta obtener el conjunto dinámico una vez por selección aplicada.

Content Trail muestra esa base desde la respuesta inicial. Visiting no muestra tarjetas antes de una selección; puede preparar los datos de respaldo sin exponer un grid inicial. Una selección GET válida permite renderizar ese respaldo en el servidor. Las imágenes WordPress server-side usan `wp_get_attachment_image()`; el cliente usa las rendiciones, dimensiones y alternativas equivalentes del índice.

Un resultado dinámico válido reemplaza el conjunto completo conservando su orden. Si contiene menos elementos que el máximo, mostrar esos elementos; no rellenarlo con fallback ni ocultarlo por no alcanzar el mínimo editorial. El máximo configurado limita el conjunto dinámico; la lista de respaldo conserva su cantidad propia de 2–12, según lo confirmado.

### 8.2 Estados y respuesta

| Situación | Resultado público | Diagnóstico editorial |
| --- | --- | --- |
| Falta configuración o integración | Lista manual/de respaldo, sin control técnico roto | Indicar dependencia faltante |
| Índice vacío, cero resultados o todos inválidos | Respaldo aplicando los mismos tipos y temporada | Indicar ausencia de resultados dinámicos |
| Error de red, cuota, 429 o respuesta no válida | Respaldo; no reintentos automáticos en bucle | Indicar fallo sin exponer credenciales |
| Respuesta lenta | Respaldo usable mientras carga; timeout de 8 segundos como valor inicial de ingeniería | Diagnóstico de timeout |
| Respaldo Visiting sin coincidencias | `No recommendations for this season. Try another season.` | Señalar clasificación o cobertura faltante |
| No queda ningún elemento en Content Trail | Omitir la sección pública completa | Mostrar aviso de contenido faltante |
| ACF no disponible | No fatal; no generar controles vacíos | Explicar dependencia cuando sea posible |
| Seeker no disponible | Usar caché permitida por su integración; omitir eventos irrecuperables y conservar otros tipos | Aviso específico de eventos |

Ante 429, respetar `Retry-After` y evitar nuevas llamadas durante esa ventana. La integración puede compartir un estado de indisponibilidad temporal entre instancias, pero no registrar como permanente una falla transitoria. No mostrar nombres de planes, excepciones ni configuración al visitante.

Al cambiar selección, invalidar la solicitud anterior mediante cancelación cuando el cliente la admita y, siempre, un identificador de secuencia. Una respuesta antigua jamás reemplaza la selección actual. No conservar recomendaciones de otra temporada bajo el nuevo encabezado. Reaplicar la misma temporada no repite una consulta ya resuelta durante esa visita a la página.

Excluir eventos cancelados o finalizados en ambas fuentes; usar la ocurrencia vigente normalizada por Seeker. Evitar una llamada remota por cada fila y reutilizar el acceso/caché existente. Si un evento ya caducó en una pestaña abierta, retirarlo en la siguiente actualización de resultados; el conector también debe mantener el índice vigente.

Sin JavaScript, los carruseles usan scroll horizontal nativo y las flechas sin función no se muestran. Visiting usa su GET de respaldo. La ausencia de Algolia no impide utilizar el calendario con JavaScript ni elegir temporadas en el formulario nativo.

### 8.3 Carga y previsualización

Registrar assets del bloque en `inc/blocks.php`, inspeccionar recursivamente el árbol con `ltt_dive_in_block_tree_contains()` y mantener un fallback de enqueue al renderizar. Reutilizar handles para que varias instancias no dupliquen Swiper, Alpine, Insights o el cliente Algolia. No cargar FacetWP como efecto de usar Listing Card.

El CSS nuevo de composición pertenece a `assets/css/components/content-trails.css`; cada tarjeta conserva la propiedad de sus estados internos. El JavaScript de calendario y del bloque pertenece a componentes enfocados. No añadir un pipeline Node/Composer ni CSS inline para valores editoriales.

La vista del editor muestra contenido manual/de respaldo y avisos de configuración. No utiliza la afinidad del administrador, no dispara Insights y no necesita llamadas Algolia para previsualizar. Mantener la fuente elegida al editar, guardar, recargar y duplicar bloques; las instancias duplicadas reciben identidades DOM/formulario distintas.

## 9. Insights, consentimiento y atribución

La integración de Convoy es propietaria de la inicialización de Insights, del `userToken`, del consentimiento Osano y de la afinidad. El tema entrega los eventos de interacción normalizados con índice, objeto y contexto de instancia. No debe inicializar otro tracker ni inventar otra cookie.

| Interacción | Evento requerido | Condición |
| --- | --- | --- |
| Tarjeta visible | `viewedObjectIDs` | Primera intersección real con área positiva del viewport y del track; documento visible, no una slide oculta o una tarjeta solo presente en DOM |
| Activación de enlace de tarjeta | `clickedObjectIDs` | Navegación por ratón, touch o teclado a un destino de la tarjeta; no clics en espacio vacío, flechas, indicadores o controles de fechas |
| Carga de detalle propio desde una tarjeta | `convertedObjectIDs` | Identidad del destino coincide con una atribución de clic válida a post/event y el consentimiento sigue vigente |

Usar nombres estables, por ejemplo `Content Trail Tile Viewed`, `Content Trail Tile Clicked` y `Content Trail Detail Viewed`. Los eventos de Manual y fallback llevan los mismos IDs canónicos si corresponden a objetos registrados en `ltt_content`; si la integración no puede establecer esa correspondencia, no fabricar IDs para seguimiento. Sus tarjetas siguen funcionando.

Deduplicar vistas por instancia y `objectID` durante la carga de página, incluso si se reconstruye Swiper, se repite un filtro o un respaldo se reemplaza por el mismo hit. No deduplicar entre bloques distintos que muestran realmente el mismo elemento. El registro de clic se hace una sola vez por activación y no debe esperar la red para permitir navegación. Remind Me puede registrar un clic de acción si Convoy lo identifica como tal, pero nunca atribuye una conversión de lectura del detalle.

La atribución de detalle la transporta y valida Convoy, con destino, objeto, momento y consumo único; el tema no debe inferir una conversión solamente porque se abrió un post. Las conversiones se limitan a detalles propios de post/event solicitados en 3.1, no a cualquier CTA ni a páginas hub o actividades. Actualmente los eventos conducen a Seeker: el clic puede medirse aquí, pero su carga externa requiere cooperación del destino. No sustituir esa carga por un evento enviado antes de salir.

La fase solicitada conserva los métodos sin `AfterSearch`. Algolia recomienda [clickedObjectIDsAfterSearch](https://www.algolia.com/doc/libraries/search-insights/clicked-object-ids-after-search) y [convertedObjectIDsAfterSearch](https://www.algolia.com/doc/libraries/search-insights/converted-object-ids-after-search) para atribuir acciones a una consulta mediante `queryID`. Adoptarlos sería una ampliación coordinada con Convoy; no enviar ambos métodos para la misma interacción ni prometer métricas atribuidas a búsquedas usando solo los métodos base. Referencias de los métodos pedidos: [viewedObjectIDs](https://www.algolia.com/doc/libraries/search-insights/viewed-object-ids), [clickedObjectIDs](https://www.algolia.com/doc/libraries/search-insights/clicked-object-ids) y [convertedObjectIDs](https://www.algolia.com/doc/libraries/search-insights/converted-object-ids).

### 9.1 Política predeterminada de consentimiento

- Antes de consentimiento, con rechazo o con Osano aún sin resolver: no enviar Insights, no crear cookie de seguimiento y no mantener un contador persistente de afinidad. Las recomendaciones y enlaces siguen utilizables; el contexto puede derivarse de la página sin historial personal.
- Después de consentimiento: Convoy establece una cookie first-party pseudónima y entrega el mismo `userToken` para consultas y eventos. Nunca usar email, nombre ni otro dato personal como token.
- Al revocar: detener envío, descartar eventos pendientes y retirar token/afinidad persistentes según el servicio de consentimiento. No reproducir retroactivamente vistas o clics anteriores a la autorización.
- Las alternativas de eventos anónimos mencionadas en 3.1 no se activan por defecto. Cualquier política distinta corresponde a Convoy y Osano, sin cambios silenciosos en el tema.

## 10. Dependencias y división de trabajo

| Responsable | Entrega requerida |
| --- | --- |
| Convoy DevOps | Plugin conector propio, configuración `ltt_content`, claves restringidas, Rules para 4–6 personas, resolución de contexto/afinidad, Insights, atribución y puente Osano |
| Convoy DevOps / plugin de sitio | CPT Activity, marca explícita de hub, metadatos persistentes de temporada y adaptadores legibles sin Algolia |
| Convoy DevOps | Jobs de actualización/retiro para WordPress vía `save_post` y `transition_post_status`, con cola que no bloquee al editor y comando WP-CLI de reindexación |
| Convoy DevOps | Ingesta independiente de Seeker, actualización de ocurrencias, cancelaciones y vencimiento; no depende de hooks de posts ni crea duplicados de eventos en WordPress |
| Last Waav Studios | Poblar contenido, marcar hubs, clasificar temporadas, preparar selecciones manuales/de respaldo y revisar su calidad |
| Desarrollador WordPress | Bloque y campos ACF, adaptadores de presentación, extracción/reutilización de tarjetas, calendario Alpine, consultas cliente, fallback, estados accesibles y puntos de emisión de eventos |

El conector procesa publicación, cambios, trash, borrado y cambios de estado de forma idempotente. Tras guardar datos ACF debe leer la versión final del contenido; una ejecución tardía no puede republicar un registro retirado. Debe reindexar sin exponer contenido privado y retirar imágenes/URLs obsoletas. La reindexación completa incluye las fuentes Seeker, además de WordPress.

El nombre concreto del comando WP-CLI, la frecuencia de sincronización Seeker, los nombres de metadatos del plugin y la duración de atribución/cookie son entregables del servicio de Convoy, no capacidades existentes ni decisiones del tema. El contrato exige sus resultados observables; hasta disponer del servicio, la implementación debe poder operar en Manual/respaldo. La salida completa Dynamic necesita una prueba de integración real, no solo mocks.

No se verificó ni se cambia la suscripción de Algolia: 3.1 informa del plan Build. Convoy confirma capacidad y límites antes del lanzamiento; una ampliación comercial sigue siendo decisión de la sección 4 del cliente. No hacer depender el funcionamiento básico del sitio de esa ampliación.

## 11. Matriz de aceptación

Estas pruebas corresponden a la futura implementación. La creación de este archivo no demuestra que el bloque ni la infraestructura pasen los escenarios.

| Área | Casos | Resultado esperado |
| --- | --- | --- |
| Variantes/modos | Content Trail y Visiting en Manual y Dynamic | Presentación y controles correctos; no hay consultas en Manual ni antes de seleccionar en Visiting |
| ACF | 1/2/12/13 referencias; cantidad dinámica 3/4/12/13; cambiar variante/modo | Validar límites editoriales, preservar valores ocultos, no borrar contenido ni reutilizar un contexto en Visiting |
| Selección mixta | Intercalar cuatro tipos, reordenar, duplicar un UUID/ID | Orden estable, selección Seeker funcional, duplicados rechazados o deduplicados defensivamente |
| Elegibilidad | Página no hub, post privado, borrado, imagen requerida ausente, Activity no registrado | No exponer contenido inválido; avisos editoriales; no fatal |
| Paridad | Misma Story, hub, Activity y Event desde fuente nativa e índice | Mismo componente, datos, enlaces, estados y comportamiento responsive |
| Listing | Cero/uno/dos botones, segundo botón solo, sin imagen, rollover | Variantes y prioridad correctas; copia accesible; sin assets FacetWP |
| Eventos | All-day, zona horaria, cambio de horario, recurrencia, cancelado y vencido | Misma ocurrencia y fecha que Event Driver; sin eventos inválidos ni conversiones externas ficticias |
| Algolia | Types combinados, regla explícita, contexto externo, sin contexto | Filtros correctos, precedencia definida, ninguna llamada a `wp_searchable_posts` |
| Aislamiento de Visiting | Rule global existente y usuario con afinidad | Solo selección estacional; no Rules de persona ni Personalization |
| Fallback | Índice vacío, todos los hits inválidos, cuota, 429, timeout, falta config | Respaldo usable, pausa/reintentos acotados, sin mensajes técnicos al visitante |
| Resultados parciales | Menos hits que el límite; respaldo con 1 o 0 válidos | No rellenar mezclando fuentes; un resultado sin controles innecesarios; estado vacío definido |
| Fechas | Rango incompleto, salida anterior, mismo día, año bisiesto, cambio de año | Validación coherente; sin consulta hasta aplicar un rango válido |
| Temporadas | Llegada en límite, viaje entre temporadas, acceso directo, todo el año | La llegada manda; mismos resultados elegibles en índice y respaldo; sin inferir temporada de publicación |
| Limpieza/cancelación | Clear, Escape, cambiar provisional y cerrar | Volver al estado definido; no aplicar cambios cancelados ni mostrar respuestas antiguas |
| Concurrencia | Cambios rápidos, varias instancias, bloques anidados/duplicados | Estado aislado, IDs únicos, última selección prevalece y dependencias cargadas una vez |
| Sin JavaScript | Carrusel, Visiting GET, error de formulario, dos bloques | Contenido nativo usable, selección por instancia, sin controles inertes ni consultas Algolia |
| Insights | Scroll dentro/fuera, reconstrucción, clic teclado/touch, clic en flechas | Vistas y clics reales sin duplicados; navegación no bloqueada; controles no cuentan como contenido |
| Conversión | Clic a post propio, recarga, carga directa, enlace Seeker | Solo conversión válida y consumida una vez; ninguna para carga directa ni salida externa no instrumentada |
| Consentimiento | Desconocido, aceptado, rechazado, revocado | Sin seguimiento antes/después de permiso válido; token coherente; no reenviar historial previo |
| Responsive | 1440px, 375px, 320px y a ambos lados de límites activos | Sin desbordamiento del documento ni pérdida de contenido; grid/carrusel según variante |
| Accesibilidad | Tab/Shift+Tab, Enter/Space, Escape, flechas de calendario, lector de pantalla | Foco, nombres, selección, errores y anuncios correctos; sin trap; cambio de resultados sin robo de foco |
| Preferencias visuales | Zoom 200%, reduced motion, forced-colors | Controles legibles y visibles, selección no basada solo en color, movimiento reducido |
| Seguridad | Títulos/URLs manipulados, datos inesperados, GET arbitrario | Escape por contexto, URLs válidas, ningún HTML ejecutable ni selección fuera de la configuración |
| Regresiones | End Page Cluster, Page Drivers, Story Drivers, Event Driver, Listing Module y búsqueda | Consumidores existentes sin cambios de contenido o comportamiento; índices separados |

Validar PHP con `php -l`, JSON con un parser, JavaScript con los chequeos disponibles y diff con `git diff --check`. Añadir pruebas de contrato significativas para normalización, temporadas, vigencia, respuestas fuera de orden y seguimiento, sin introducir una infraestructura de build por este bloque. Comparar capturas nativas y dinámicas y ejecutar pruebas manuales de teclado/lector de pantalla; una auditoría automática no acredita por sí sola WCAG.

Las pruebas WordPress se realizan únicamente con Local ya disponible. No iniciar, resetear ni reconfigurar el sitio para comprobar esta especificación. Registrar por separado qué se validó localmente y qué depende del índice, Osano o Seeker reales.

## 12. Orden de implementación

1. **Contrato y dependencias:** entregar a Convoy el esquema y la división de responsabilidades; conectar Activity, hubs, temporadas, configuración pública y servicios de consentimiento/contexto. Preparar registros de prueba para los cuatro tipos.
2. **Tarjetas compartidas:** extraer Page Driver y CSS de Listing; completar adaptadores y rollover accesible; comprobar paridad y regresiones de sus consumidores.
3. **Manual y respaldo:** registrar bloque/campos, resolver referencias en lotes, renderizar composición y estados vacíos, asegurar múltiples instancias y ausencia de JavaScript.
4. **Calendario:** implementar disclosure Alpine y formulario GET, rango/temporada, errores, limpieza, teclado y filtrado de respaldo.
5. **Algolia e Insights:** activar Search cliente, Rules solo en Content Trail, vigencia, fallback y control de concurrencia; conectar los eventos y consentimiento de Convoy sin duplicar infraestructura.
6. **Validación integral:** ejecutar la matriz con datos y servicios reales, revisar cuotas y sincronización, verificar accesibilidad y actualizar DESIGN.md únicamente donde la implementación confirmada cambie su estado documentado.

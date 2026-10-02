# Landing de Flooring · CJ Remodeling Group (Miami, FL)

Plugin de WordPress que agrega una landing page de **Flooring** con la misma identidad visual de cjremodeling.services (negro + dorado, Instrument Sans, botones tipo píldora, tarjetas redondeadas), con **H1 único**, datos estructurados SEO/AEO/GEO, **botones de contacto** y **formulario** que envía los leads por correo y los guarda en el panel de WordPress.

## Qué hay en esta carpeta

| Archivo | Para qué sirve |
|---|---|
| `cj-flooring-landing.zip` | **El plugin listo para subir a WordPress.** |
| `cj-flooring-landing/` | El mismo plugin como código fuente editable. |
| `preview/` | Capturas de cómo se ve (escritorio completo, móvil completo y portadas). |
| `INSTRUCCIONES-INSTALACION.md` | Este documento. |

---

## 1. Antes de instalar (5 minutos, recomendado)

1. **Haz un respaldo** del sitio (o de la base de datos) como harías antes de instalar cualquier plugin.
2. **Ajustes → Enlaces permanentes → "Nombre de la entrada" → Guardar.**
   Hoy el sitio usa enlaces simples (`?page_id=123`), por eso la landing quedaría como `cjremodeling.services/?page_id=123`. Con "Nombre de la entrada" queda `cjremodeling.services/flooring-installation-miami/`, que es lo que Google y las IA prefieren. Además esto arregla `robots.txt` y el mapa del sitio, que hoy dan error 404. WordPress redirige solo las URLs antiguas a las nuevas.
3. *(Opcional pero útil)* **Ajustes → Generales → Idioma del sitio → English (United States).** El sitio está en inglés, pero WordPress está en español y escribe `lang="es"` en todas las páginas. La landing ya declara `lang="en-US"` por su cuenta.

## 2. Instalar

1. **Plugins → Añadir nuevo → Subir plugin** → elige `cj-flooring-landing.zip` → **Instalar ahora** → **Activar**.
2. Al activarlo aparece un aviso verde y se crea la página **"Flooring Installation in Miami, FL"** como **borrador**. No se publica nada solo.
3. Pulsa **Preview the landing page** y revisa la página completa en escritorio y celular.
4. Cuando estés conforme: **Páginas → "Flooring Installation in Miami, FL" → Publicar.**
   *Es normal que el editor de esa página se vea vacío: el contenido lo dibuja el plugin. No uses Fusion Builder en esa página.*

La URL final será: `https://cjremodeling.services/flooring-installation-miami/`

## 3. Configurar (Flooring Leads → Settings)

El plugin trae todo precargado con los datos reales del sitio (teléfono 954-671-8595, WhatsApp, info@cjremodeling.services, Miami FL). Revisa:

| Sección | Qué hacer |
|---|---|
| **Contact details** | Confirma teléfono y WhatsApp. Si dejas el WhatsApp vacío, desaparecen todos los botones de WhatsApp. |
| **Form & leads** | Pon el/los correos que recibirán las solicitudes. Pulsa **Send test email** y confirma que llega. |
| **SEO** | Déjalo vacío para usar el título, descripción y H1 optimizados (se muestran en gris). |
| **Business information** | **Revisa la lista de zonas de servicio** (ver "Textos a validar"). Agrega tus perfiles (Google Business Profile, Facebook, Instagram…) en *Profile links*. Licencia, horario y año de fundación: solo si son reales. |
| **Advanced** | "Fast, clean mode" viene activo (ver sección 8). |

## 4. Cómo llegan los leads

Cada solicitud del formulario:
1. Se **guarda** en el menú **Flooring Leads** (nombre, teléfono, correo, tipo de piso, tipo de propiedad, mensaje, página, campaña UTM). Solo lo ven administradores.
2. Se **envía por correo** a las direcciones configuradas (con "Reply-To" al cliente, así respondes directo).

Si WordPress no logra enviar el correo, el lead **no se pierde**: queda guardado y marcado como *Email failed*. Para que el correo llegue siempre, instala un plugin SMTP (WP Mail SMTP, FluentSMTP…) y repite el *Send test email*.

**Prueba el formulario tú mismo** (ventana de incógnito): llena el formulario de arriba y el de abajo y confirma que aparecen en *Flooring Leads* y en tu correo.

Protección contra spam incluida: campo trampa invisible, límite de envíos por IP, validación en el servidor, nonce renovado en cada envío (funciona con LiteSpeed Cache y otros cachés).

## 5. Botones de contacto incluidos

* Botón dorado **"Contact us!"** en el encabezado (lleva al formulario más cercano).
* Botones **Get a Free Estimate / Call / WhatsApp** en la portada.
* **Botón flotante de WhatsApp** (escritorio) y **barra inferior fija** en celulares: *Call · WhatsApp · Free estimate*.
* Sección final de contacto con teléfono, WhatsApp, correo y ubicación.

## 6. Después de publicar (checklist SEO / GEO)

1. **Enlázala desde el Home.** En Avada, edita la tarjeta *Flooring* del Home y apunta su botón *View this service* a `/flooring-installation-miami/` (hoy esos botones apuntan a `/service/software-development/`, que es contenido de la plantilla). Opcional: agrégala al menú.
2. **Google Search Console:** *Inspección de URLs* → pega la URL → **Solicitar indexación**. Envía el mapa del sitio `https://cjremodeling.services/wp-sitemap.xml` (funciona cuando activas "Nombre de la entrada").
3. **Google Business Profile:** usa esta URL como página de servicio/enlace y mantén idénticos nombre, teléfono y zona (NAP).
4. **Valida:** [Rich Results Test](https://search.google.com/test/rich-results) y [validator.schema.org](https://validator.schema.org) (deben leer los datos de negocio local y FAQ sin errores; Google ya casi no muestra el desplegable de FAQ en sus resultados, pero los buscadores con IA sí aprovechan esos datos) y [PageSpeed Insights](https://pagespeed.web.dev).
5. **Reseñas reales** en Google y, cuando existan, súmalas (el plugin no inventa calificaciones ni reseñas).
6. **Textos alternativos:** hoy *todas* las imágenes de tu Biblioteca de medios tienen el alt vacío. La landing usa sus propios alt descriptivos, pero conviene completarlos en el resto del sitio.
7. **Conversiones:** el formulario dispara `cjfl_lead` (dataLayer / Google Tag Manager), `generate_lead` (GA4) y `Lead` (Meta Pixel); los clics en botones disparan `cjfl_click`. Márcalos como conversión en GA4 / Google Ads. Las campañas con `utm_*` y `gclid` quedan guardadas en cada lead.

## 7. Qué incluye (SEO / AEO / GEO)

* **Un solo `<h1>`**: "Flooring Installation in Miami, FL"; jerarquía H2 → H3 sin saltos.
* **Title** (56 caracteres) y **meta description** (159) optimizados; canonical, Open Graph, Twitter Card, `robots` con vista previa grande y `lang="en-US"`.
* **Schema JSON-LD:** `GeneralContractor` (LocalBusiness) con teléfono/correo/zona, `Service` con catálogo de pisos, `FAQPage` (9 preguntas, idénticas al texto visible), `WebPage`, `BreadcrumbList`, `WebSite`.
* **AEO/GEO:** introducción "respuesta primero", cuadro *At a glance*, tabla comparativa de pisos para el clima de Miami, nota sobre condominios, zonas de servicio, FAQ con respuestas directas y fecha de "Last updated".
* **Rendimiento:** fuente incluida en el plugin (sin pedir nada a Google), imágenes responsivas con `srcset`, imagen principal con prioridad alta, sin librerías externas. Lighthouse móvil (medido en un WordPress de prueba): **Rendimiento 96 · Accesibilidad 100 · Buenas prácticas 100 · SEO 100**. En tu hosting el rendimiento depende del servidor y del caché; la página es estática para visitantes, así que con LiteSpeed Cache activo debería igualar o mejorar esos números.
* **Accesibilidad:** 0 errores con axe-core; HTML válido; sin scroll horizontal desde 320 px.

## 8. "Fast, clean mode" (por qué la landing es rápida)

La landing es una **plantilla independiente**: dibuja su propio encabezado y pie con la identidad del sitio y **no carga el CSS/JS de Avada** (más rápido y sin choques de estilos). Los códigos de seguimiento que hayas puesto en Avada (Code Fields), GTM, Pixel, etc. **siguen funcionando**. También oculta el cartel flotante "Don't miss our new product launch…" en esta página.
Si algún día necesitas que algo del tema aparezca aquí, desactiva la opción en *Settings → Advanced*.

## 9. Personalizar

* **Textos de la página:** `cj-flooring-landing/includes/class-cjfl-content.php` (un arreglo PHP legible: hero, servicios, proceso, FAQ…). Edítalo desde cPanel o *Plugins → Editor de archivos*. Los datos de contacto, SEO y zonas se cambian en *Flooring Leads → Settings* sin tocar código.
* **Colores:** variables al inicio de `assets/css/landing.css` (`--cjfl-gold`, `--cjfl-bg`…).
* **Cambiar fotos** (en `functions.php` del tema hijo o con un plugin de snippets):

```php
add_filter( 'cjfl_images', function ( $images ) {
	$images['hero']['file'] = '2026/10/mi-foto-flooring.jpg'; // ruta dentro de /wp-content/uploads/
	$images['hero']['w']    = 2000;
	$images['hero']['h']    = 1200;
	return $images;
} );
```
  Claves disponibles: `hero`, `install`, `wood`, `light`, `bath`, `kitchen`, `logo`, `logo_small`.
* **Integraciones (CRM, Zapier, Slack…):** engánchate a `do_action( 'cjfl_lead_created', $lead_id, $data, $email_sent )`.
* **Re-empaquetar el .zip:** clic derecho sobre la carpeta `cj-flooring-landing` → *Enviar a → Carpeta comprimida*.
* **Actualizar el plugin:** sube el nuevo .zip en *Plugins → Subir plugin* y elige *Reemplazar*. Ajustes y leads se conservan.

## 10. Textos a validar con el cliente antes de publicar

Usé solo afirmaciones que ya están en cjremodeling.services o que son conocimiento general de pisos. Aun así, confirma:

1. **Zonas de servicio** (Miami, Miami Beach, Coral Gables, Doral, Hialeah, Aventura, Pinecrest, Kendall, Homestead, North Miami): es una lista *de ejemplo*. Ajústala a donde realmente trabajan (el teléfono es 954, quizá también atienden Broward).
2. **Proceso de preparación** (retiro del piso viejo, nivelación, chequeo de humedad, base/underlayment) y la FAQ de **tiempos** ("un cuarto pequeño de LVP/laminado en ~1 día"): que coincida con su forma real de trabajar.
3. **Fotos:** son las que ya están en la Biblioteca de medios. Algunas parecen de banco de imágenes o de otras empresas (p. ej. `roma-arquitectura-remodelaciones-portada.jpg`, `custom-home-renovations-calgary-hero-v2.webp`). Verifica que tengan licencia de uso y, apenas puedan, reemplázalas por **fotos reales de sus trabajos** (mejora conversión y credibilidad). No uso la foto de "Calgary".
4. **Licencia, seguro, años de experiencia, garantías, precios:** no se muestran porque no estaban en el sitio. Si los tienen, agrégalos en *Settings* (licencia/año) o en el archivo de contenido.

## 11. Problemas frecuentes

| Síntoma | Solución |
|---|---|
| La landing se ve sin estilos o desactualizada | Purga el caché (LiteSpeed Cache → *Purge All*) y recarga con Ctrl+F5. |
| El formulario dice que no pudo enviar | Revisa que `admin-ajax.php` no esté bloqueado por el hosting/firewall (Imunify, Cloudflare) y recarga. Mientras tanto, el teléfono y WhatsApp siguen funcionando. |
| No llegan los correos, pero sí aparecen en *Flooring Leads* | Instala un plugin SMTP y usa *Send test email*. Revisa también spam. |
| Aparece "Page not found" o la URL es `?page_id=…` | Activa "Nombre de la entrada" en *Enlaces permanentes* (paso 1.2). |
| Borré la página por error | *Flooring Leads → Settings → Create the landing page* la vuelve a crear como borrador. |

## 12. Desactivar o desinstalar

* **Desactivar:** la página queda en WordPress pero sin diseño (vacía). Al reactivar, vuelve la landing.
* **Eliminar el plugin:** conserva ajustes, leads y la página. Para borrar *todo*, marca antes "Delete all data on uninstall" en *Settings → Advanced*.

---

## Qué se probó

WordPress **7.1.2** (la misma versión que usa el sitio) en PHP **7.4, 8.3 y 8.5**, sin avisos ni errores en `debug.log`; instalación desde el `.zip`; estilos con y sin el CSS real de Avada; escritorio, tablet y móvil (320–2560 px); 33 pruebas del formulario (validación, spam, límite de envíos, correo, leads); 10 pruebas de botones; axe-core, validador HTML y Lighthouse.

> **Importante:** no tengo acceso al panel de tu WordPress real, así que la prueba final en cjremodeling.services (con Avada, LiteSpeed y tu hosting) es la que hay que hacer tú siguiendo el paso 2 y la prueba de formulario del paso 4.

## Observaciones sobre el sitio actual (afectan el SEO de todo el dominio)

* **El Home no tiene ningún `<h1>`** (todos son H2) y su meta description dice *"Hello! We are a group of skilled developers and programmers"*, texto de la plantilla.
* **Cartel flotante** "Don't miss our new product launch: a game-changer for web development!" y varios botones *View this service* que apuntan a `/service/software-development/`: contenido de demostración de Avada.
* **Enlaces permanentes simples:** `robots.txt` y `wp-sitemap.xml` devuelven 404.
* `<html lang="es">` y `og:locale es_ES` en un sitio en inglés.
* El botón de WhatsApp usa `…?utm_source=chatgpt.com` (parámetro sobrante) y el teléfono del pie es `tel:954 671 8595` (mejor `tel:+19546718595`).
* Errores de texto: "What de do". Enlaces `#` en el pie, y un enlace público a `?author=1` (muestra el usuario "admin").
* Todos los archivos de la Biblioteca de medios tienen el texto alternativo vacío.

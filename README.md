# Corredora Online Plugin

Este plugin de WordPress integra y sincroniza automáticamente la información de tu “Corredora Online” con tu sitio web, facilitando la administración de aseguradoras, valoraciones, cotizaciones, tipografías personalizadas y más. Está diseñado para trabajar de forma conjunta con un servicio externo que provee la información, manteniendo todo siempre actualizado en tu WordPress.

---

## Características principales

- **Integración y sincronización automática**:  
  Conecta con la API de Corredora Online para recibir, actualizar o eliminar datos de:
  - Aseguradoras (incluyendo logotipos y enlaces de pago o siniestros).
  - Valoraciones de clientes (testimonios y calificaciones).
  - Datos de contacto (correo, teléfono, colores corporativos, etc.).

- **Shortcodes incluidos**:
  - `[Corredora_Online mostrar="correo"]`, `[Corredora_Online mostrar="numero"]` y más para mostrar datos simples de contacto.
  - `[Corredora_Online_Cotizador]` para mostrar un formulario de cotización en línea.
  - `[Corredora_Online_Compañias]` para un slider infinito de aseguradoras.
  - `[Corredora_Online_Login]` para iniciar sesión con RUT y redirigir a la plataforma externa.
  - `[Corredora_Online_Primas]` y `[Corredora_Online_Siniestros]` para mostrar la grilla de compañías con enlaces directos.
  - `[Corredora_Online_Valoraciones]` para mostrar reseñas de clientes, su calificación (estrellas) y comentarios.

- **Panel de configuración sencillo**:
  - Guarda la API Key y el ID de corredora para activar la integración.
  - Configura la tipografía preferida (con soporte para Google Fonts o fuentes comunes).
  - Asigna un vendedor específico a las cotizaciones, todo vía AJAX sin recargar la página.

- **Bloqueo de edición manual**:
  - Los datos gestionados por la API no pueden editarse manualmente desde WordPress, evitando inconsistencias.
  - Advertencias en la página de edición y eliminación de metaboxes para garantizar que toda modificación suceda únicamente desde Corredora Online.

---

## Requisitos

- WordPress 5.0 o superior.
- PHP 7.2 o superior.
- Acceso a la API de Corredora Online (requiere clave y un ID de corredora válido).

---

## Instalación

1. **Descarga** o clona este repositorio en tu computadora.
2. **Extrae** la carpeta del plugin (si procede) y **renómbrala** adecuadamente (por ejemplo, `corredora-online-plugin`).
3. Sube la carpeta resultante a tu directorio de plugins de WordPress (generalmente `wp-content/plugins/`).
4. En el **Escritorio de WordPress**, ve a la sección **Plugins** y **activa** “Corredora Online”.

---

## Configuración

1. En el menú de WordPress, busca **“Corredora Online”**.
2. Haz clic en **Configuración**:
   - Ingresa tu **ID de Corredora** y **API Key**. Presiona **Integrar** para validar.
   - (Opcional) Escoge la **tipografía** deseada para el plugin.
   - Si la integración es correcta, podrás configurar el **Cotizador** (asignando un Vendedor).
3. Verifica si la integración se ha completado con éxito revisando las secciones de **Aseguradoras** y **Valoraciones**, así como probando los shortcodes en tu sitio.

---

## Uso de los Shortcodes

A continuación, algunos ejemplos de uso:

- **Mostrar contacto o año actual**:  
  ```php
  [Corredora_Online mostrar="correo"]
  [Corredora_Online mostrar="numero"]
  [Corredora_Online mostrar="año-actual"]
  ```
  Muestra el correo de contacto, teléfono o el año en curso (útil para pie de página).

- **Cotizador en línea**:  
  ```php
  [Corredora_Online_Cotizador]
  ```
  Despliega un formulario de 2 pasos para capturar patente, RUT y datos personales, conectado a la API.

- **Slider de compañías**:  
  ```php
  [Corredora_Online_Compañias]
  ```
  Genera un carrusel infinito con los logotipos de las aseguradoras.

- **Enlaces de pago de primas**:  
  ```php
  [Corredora_Online_Primas]
  ```
  Muestra una grilla de aseguradoras con enlaces de pago.

- **Enlaces para siniestros**:  
  ```php
  [Corredora_Online_Siniestros]
  ```
  Muestra una grilla de aseguradoras con enlaces a siniestros.

- **Login con RUT**:  
  ```php
  [Corredora_Online_Login]
  ```
  Formulario que abre en una nueva pestaña la página de Corredora Online con login prellenado.

- **Valoraciones**:  
  ```php
  [Corredora_Online_Valoraciones columns="3" limit="9" star_color="#FFD700" box_bg="#f9f9f9" text_color="#333"]
  ```
  Despliega las últimas valoraciones de clientes, incluidas sus calificaciones y comentarios, en una grilla configurable.

---

## Mantenimiento y Actualizaciones

- **Sincronización manual**:  
  Para forzar la sincronización, se puede llamar al endpoint REST `/corredora-online/v1/pulse/`, por ejemplo agregando las query params como `?restRoute=udpAseguradoras`, `?restRoute=udpValoraciones`, etc.  
  Asegúrate de enviar la cabecera `Authorization` con la API Key almacenada en WordPress.

- **Automatización**:  
  Corredora Online puede llamar automáticamente a este endpoint cuando se necesiten actualizar datos (aseguradoras, valoraciones, etc.).

---

## Contribuciones

¡Las contribuciones son bienvenidas! Puedes:

1. Realizar un **fork** de este repositorio.
2. Crear una **branch** con tu mejora o solución de error.
3. Enviar un **Pull Request** para su revisión.

---

## Licencia

Este plugin se distribuye con licencia [MIT](https://opensource.org/licenses/MIT), lo que te permite usarlo y modificarlo libremente siempre que se mantenga esta referencia de licencia en el código fuente.

---

### Soporte

Para cualquier duda, error o sugerencia, por favor abre un “issue” en este repositorio o contacta al equipo de desarrollo de Corredora Online. ¡Gracias por tu interés en mejorar este plugin!

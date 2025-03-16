<?php

// -- ↕ Iniciación Código Page Back Office

// =========================================================
// 1. Creación de menú y submenú en el Back Office
// =========================================================

// Creación de la menu en el back office
function add_custom_menu_page() {
    $plugin_dir_url = plugins_url('/', __FILE__);
    $icon_url = $plugin_dir_url . 'logotipo.png';

    // Nombre visible y slug distinto
    $page_title = 'Corredora Online';
    $menu_title = 'Corredora Online';
    $capability = 'manage_options';
    $top_level_slug = 'corredora-online-main';  // <--- OJO, distinto
    $function = 'corredora_online_settings_page'; // Apuntamos directamente a tu callback
    $position = 100;

    add_menu_page(
        $page_title,
        $menu_title,
        $capability,
        $top_level_slug,   // <--- SLUG de top-level
        $function,         // Muestra Configuración directamente
        $icon_url,
        $position
    );
}
add_action('admin_menu', 'add_custom_menu_page');

function add_corredora_online_submenu() {
    // El parent_slug ahora es 'corredora-online-main'
    add_submenu_page(
        'corredora-online-main',       // parent slug (el top-level de arriba)
        'Configuración',               // page_title
        'Configuración',               // menu_title
        'manage_options',              // capability
        'corredora-online-settings',   // slug distinto para subpágina
        'corredora_online_settings_page', // la misma función callback
        1 // posición para que vaya de primera (o un número muy bajo)
    );
}
add_action('admin_menu', 'add_corredora_online_submenu');

// =========================================================
// 2. Creación de la página en el Back Office
// =========================================================

function corredora_online_settings_page() {
    $stored_api_key      = trim(get_option('api_key'));
    $stored_corredora_id = trim(get_option('corredora_id'));
    // --- (NUEVO) Tipografía: obtener la tipografía guardada
    $stored_font_choice  = trim(get_option('co_font_choice'));

    $hay_integracion = (!empty($stored_api_key) && !empty($stored_corredora_id));
    ?>
    <style>
    .custom-page {
        padding: 20px;
        font-family: 'Nunito', sans-serif;
    }
    .custom-page h2 {
        margin: 0;
        font-size: 24px;
    }
    .custom-page p.intro {
        margin: 5px 0 30px 1px;
        font-size: 14px;
    }
    /* Toast de éxito */
    #co-toast-success {
        position: fixed;
        top: 20px;
        right: 20px;
        background: #fff;
        border: 1px solid #ddd;
        border-radius: 8px;
        padding: 12px 16px;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        display: none;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        color: #333;
        z-index: 999999;
    }
    #co-toast-success .icon-check {
        color: #28a745;
        font-weight: bold;
    }
    /* Cajas (acordeones) */
    .co-config-box {
        border: 1px solid #DCDCDC;
        border-radius: 12px;
        background: #ffffff;
        box-shadow: 0 0 28px -10px rgba(0,0,0,0.15);
        margin-bottom: 20px;
        opacity: 1;
        position: relative;
    }
    .co-config-box.disabled {
        opacity: 0.5; /* grisácea */
    }
    .co-config-box-header {
        cursor: pointer;
        padding: 30px 26px;
        font-size: 19px;
        font-weight: 500;
        color: #1B1B1B;
        border-bottom: 1px solid #DCDCDC;
        margin: 0;
    }
    .co-config-box-header .title-text {
        margin-right: 0;
    }
    .co-config-box-content {
        display: none;
        padding: 30px 32px 38px 32px;
        font-size: 15px;
        color: #313131;
    }
    .co-config-box-content label {
        display: block;
        font-size: 15px;
        margin-bottom: 10px;
    }
    .co-config-box-content input[type="text"],
    .co-config-box-content select {
        width: 100%;
        padding: 6px 12px;
        font-size: 15px;
        border: 1px solid #BABABA;
        border-radius: 12px;
        color: #313131;
    }
    .co-config-box-content .description {
        color: red;
        margin-top: 5px;
        font-size: 14px;
    }
    /* Botón “Integrar” => 100% */
    .co-btn-integrar {
        background-color: #00DFC0;
        border: none;
        border-radius: 50px;
        padding: 12px 24px;
        cursor: pointer;
        color: #090909;
        font-family: 'Nunito', sans-serif;
        margin-top: 20px;
        font-size: 16px;
        font-weight: 400;
        width: 100%;
    }
    </style>

    <!-- Toast -->
    <div id="co-toast-success">
      <span class="icon-check">✔</span>
      <span id="co-toast-success-msg">Mensaje de éxito</span>
    </div>

    <div class="wrap custom-page">
        <h2>Corredora Online</h2>
        <p class="intro">
            Nuestro plugin de Wordpress permite generar una conexión directa con el sistema, 
            permitiendo editar la información de su web directo desde Corredora Online, 
            además de funcionalidades valiosas. 
            Encuentra más info en <strong>Config &gt; Integraciones &gt; Wordpress</strong>.
        </p>

        <!-- Caja 1: Integración -->
        <div class="co-config-box" id="boxIntegracion">
            <div class="co-config-box-header" data-target="integrationBox">
                <span class="title-text">Integración con Corredora Online</span>
            </div>
            <div class="co-config-box-content" id="integrationBox">
                <form id="formIntegracion">
                    <input type="hidden" name="action" value="corredora_integrar_settings"/>

                    <label for="corredora_id">ID Corredora</label>
                    <?php 
                      // Enmascarar la clave actual:
                      $masked_api_key = str_repeat('*', max(0, strlen($stored_api_key) - 4)) . substr($stored_api_key, -4);
                    ?>
                    <input 
                        type="text" 
                        name="corredora_id" 
                        id="corredora_id"
                        value="<?php echo esc_attr($stored_corredora_id); ?>" 
                        required
                    />

                    <label for="api_key" style="margin-top: 20px;">API KEY</label>
                    <input 
                        type="text"
                        name="api_key"
                        id="api_key"
                        value="<?php echo $masked_api_key; ?>"
                        required
                    />

                    <button type="submit" class="co-btn-integrar">Integrar</button>
                </form>
            </div>
        </div>

        <!-- Caja 2: Cotizador -->
        <?php $cotizadorDisabled = $hay_integracion ? '' : 'disabled'; ?>
        <div class="co-config-box <?php echo $cotizadorDisabled; ?>" id="boxCotizador">
            <div class="co-config-box-header" data-target="cotizacionesBox">
                <span class="title-text">Cotizador en línea</span>
            </div>
            <div class="co-config-box-content" id="cotizacionesBox">
                <?php if (!$hay_integracion): ?>
                    <p style="color:red;">Debes integrar primero para configurar el Cotizador.</p>
                <?php else: ?>
                    <label for="corredora_vendedor_id">
                        Vendedor al que se le asignarán las cotizaciones
                    </label>
                    <select name="corredora_vendedor_id" id="corredora_vendedor_id">
                        <option value="">-- Cargando... --</option>
                    </select>
                    <p class="description" id="vendedoresError" style="display:none;"></p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Caja 3: Tipografía personalizada (NUEVO) -->
        <div class="co-config-box" id="boxTipografia"><!-- sin disabled, no depende de la integracion -->
            <div class="co-config-box-header" data-target="tipografiaBox">
                <span class="title-text">Colores y diseños corporativos</span>
            </div>
            <div class="co-config-box-content" id="tipografiaBox">
                <label for="co_font_choice">Selecciona la tipografía que se usará en el plugin:</label>
                <select name="co_font_choice" id="co_font_choice">
                    <?php
                    // Lista ampliada de fuentes populares
                    $font_list = array(
                        'Arial', 'Helvetica', 'Times New Roman', 'Georgia', 'Open Sans', 'Roboto',
                        'Lato', 'Montserrat', 'Nunito', 'Poppins', 'Tajawal', 'Raleway', 
                        'Oswald', 'Source Sans Pro', 'Courier New', 'Verdana', 'Tahoma', 
                        'Trebuchet MS', 'Garamond', 'Comic Sans MS'
                    );
                    foreach ($font_list as $font) {
                        $selected = ($stored_font_choice === $font) ? 'selected' : '';
                        echo '<option value="' . esc_attr($font) . '" ' . $selected . '>' . esc_html($font) . '</option>';
                    }
                    ?>
                </select>
            </div>
        </div>
    </div>

    <script>
    (function(){
        let hayIntegracion = <?php echo $hay_integracion ? 'true' : 'false'; ?>;

        // -------------------------------------
        // Toggle (abre/cierra)
        // -------------------------------------
        const headers = document.querySelectorAll('.co-config-box-header');
        headers.forEach(header => {
            header.addEventListener('click', function(){
                const targetId = header.getAttribute('data-target');
                const content  = document.getElementById(targetId);

                if (content.style.display === 'block'){
                    content.style.display = 'none';
                } else {
                    content.style.display = 'block';

                    // Si es la caja 2 y está integrada => cargar vendedores
                    if(targetId === 'cotizacionesBox' && hayIntegracion){
                        cargarVendedores();
                    }
                }
            });
        });

        // -------------------------------------
        // Integrar (Caja 1)
        // -------------------------------------
        const formIntegracion = document.getElementById('formIntegracion');
        formIntegracion.addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(formIntegracion);
            const idc     = formData.get('corredora_id');
            const apikey  = formData.get('api_key');

            // Validación real con endpoint validate-wp
            validarEnServidor(idc, apikey)
              .then(valid => {
                  if (!valid) {
                      alert('La validación de la API KEY ha fallado. Revisa si los datos son correctos.');
                      return;
                  }
                  // Si es OK => guardamos vía AJAX en WP
                  fetch(ajaxurl, {
                      method: 'POST',
                      body: formData
                  })
                  .then(r => r.json())
                  .then(data => {
                      if(data.success){
                          mostrarToast('Integración realizada con éxito');
                          hayIntegracion = true;
                          const boxCotizador = document.getElementById('boxCotizador');
                          boxCotizador.classList.remove('disabled');
                      } else {
                          alert('Error: ' + (data.data || 'No se pudo integrar'));
                      }
                  })
                  .catch(err => {
                      console.error(err);
                      alert('Error al integrar. Revisa la consola.');
                  });
              })
              .catch(error => {
                  console.error('Error en la validación:', error);
                  alert('Error de conexión o validación.');
              });
        });

        // -------------------------------------
        // Validación en el servidor
        // -------------------------------------
        async function validarEnServidor(corredoraId, apiKey) {
            if(!corredoraId || !apiKey) return false;

            let siteUrl = '<?php echo get_site_url(); ?>';
            let bodyData = new URLSearchParams();
            bodyData.append('idc', corredoraId);
            bodyData.append('api-key', apiKey);
            bodyData.append('url', siteUrl);

            try {
                const response = await fetch('https://atm.novelty8.com/webhook/api/corredora-online/validate-wp', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: bodyData.toString()
                });

                // Si el status HTTP no es 200, lo consideramos falla
                if (!response.ok) {
                    console.error('Respuesta HTTP no OK:', response.status, response.statusText);
                    return false;
                }

                // Si la respuesta es 200, parseamos JSON
                const respData = await response.json();
                // La API EXITOSA => { "estado": "exitoso", "mensaje": "Validación exitosa" }
                if (respData && respData.estado === 'exitoso') {
                    return true;
                } else {
                    console.error('API devolvió estado no exitoso:', respData);
                    return false;
                }
            } catch(e) {
                console.error('Error en fetch:', e);
                return false;
            }
        }

        // -------------------------------------
        // Cargar Vendedores (Caja 2)
        // -------------------------------------
        function cargarVendedores(){
            const selVendedor = document.getElementById('corredora_vendedor_id');
            const vendedoresError = document.getElementById('vendedoresError');
            if(!selVendedor) return;

            selVendedor.innerHTML = '<option value="">Cargando...</option>';
            vendedoresError.style.display = 'none';
            vendedoresError.textContent = '';

            fetch(ajaxurl + '?action=obtener_vendedores_cotizador')
            .then(r => r.json())
            .then(data => {
                if(!data.success){
                    vendedoresError.style.display = 'block';
                    vendedoresError.textContent = data.data || 'Error al cargar vendedores.';
                    selVendedor.innerHTML = '<option value="">-- Error --</option>';
                    return;
                }
                // data.data.usuarios => la lista
                const vendedores = data.data.usuarios || [];
                selVendedor.innerHTML = '<option value="">-- Seleccione un vendedor --</option>';

                vendedores.forEach(v => {
                    const opt = document.createElement('option');
                    opt.value = v.id;
                    opt.textContent = v.nombre + ' ' + v.apellido;

                    // Si coincide con la opción guardada
                    if(v.id == '<?php echo esc_js(get_option("corredora_vendedor_id")); ?>'){
                        opt.selected = true;
                    }
                    selVendedor.appendChild(opt);
                });
            })
            .catch(err => {
                vendedoresError.style.display = 'block';
                vendedoresError.textContent = 'Error de conexión';
                console.error(err);
            });
        }

        // -------------------------------------
        // Al cambiar el vendedor => guardamos via AJAX
        // -------------------------------------
        const selVend = document.getElementById('corredora_vendedor_id');
        if(selVend){
            selVend.addEventListener('change', function(){
                const val = this.value;
                if(!val) return; // si no es válido, no guardamos

                const fData = new FormData();
                fData.append('action', 'guardar_vendedor_cotizaciones');
                fData.append('vendedor_id', val);

                fetch(ajaxurl, { method: 'POST', body: fData })
                .then(r => r.json())
                .then(data => {
                    if(data.success){
                        mostrarToast('Se ha actualizado el Vendedor.');
                    } else {
                        alert('Error al guardar: ' + (data.data || 'Desconocido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión');
                });
            });
        }

        // -------------------------------------
        // (NUEVO) Al cambiar la tipografía => guardar vía AJAX
        // -------------------------------------
        const selTipografia = document.getElementById('co_font_choice');
        if(selTipografia){
            selTipografia.addEventListener('change', function(){
                const val = this.value;
                if(!val) return; 

                const fData = new FormData();
                fData.append('action', 'guardar_tipografia_personalizada');
                fData.append('fuente', val);

                fetch(ajaxurl, { method: 'POST', body: fData })
                .then(r => r.json())
                .then(data => {
                    if(data.success){
                        mostrarToast('Tipografía guardada correctamente.');
                    } else {
                        alert('Error al guardar: ' + (data.data || 'Desconocido'));
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error de conexión');
                });
            });
        }

        // -------------------------------------
        // Función para mostrar el toast
        // -------------------------------------
        function mostrarToast(mensaje){
            const toast = document.getElementById('co-toast-success');
            const msg   = document.getElementById('co-toast-success-msg');
            msg.textContent = mensaje;
            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }

    })();
    </script>
    <?php
}

// =========================================================
// 3. Acciones AJAX: integrar, obtener vendedores, guardar vendedor, (NUEVO) guardar tipografía
// =========================================================

// 3.1 Integrar (Caja 1)
function ajax_corredora_integrar_settings() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('No tienes permisos suficientes');
    }

    $corredora_id   = sanitize_text_field($_POST['corredora_id'] ?? '');
    $api_key_input  = sanitize_text_field($_POST['api_key'] ?? '');
    $api_key_stored = get_option('api_key');

    // Determinamos la API Key final
    $final_api_key = (strpos($api_key_input, '*') === false) 
        ? $api_key_input 
        : $api_key_stored;

    if(empty($corredora_id) || empty($final_api_key)){
        wp_send_json_error('Debes ingresar ID Corredora y API Key.');
    }

    // Guardamos en WP (asumimos que ya validó en JS)
    update_option('corredora_id',  $corredora_id);
    update_option('api_key',       $final_api_key);

    wp_send_json_success('Integración exitosa.');
}
add_action('wp_ajax_corredora_integrar_settings', 'ajax_corredora_integrar_settings');

// 3.2 Obtener lista de vendedores (Caja 2)
function ajax_obtener_vendedores_cotizador() {
    if(!current_user_can('manage_options')){
        wp_send_json_error('No tienes permisos');
    }

    $stored_api_key      = trim(get_option('api_key'));
    $stored_corredora_id = trim(get_option('corredora_id'));
    if(empty($stored_api_key) || empty($stored_corredora_id)){
        wp_send_json_error('No se ha integrado aún.');
    }

    // Construimos la URL
    $url_usuarios = add_query_arg('idc', $stored_corredora_id, 'https://atm.novelty8.com/webhook/api/corredora-online/usuarios');

    // Realizamos la petición
    $response = wp_remote_get($url_usuarios, array(
        'headers' => array('X-API-KEY' => $stored_api_key),
        'timeout' => 45
    ));

    if(is_wp_error($response)){
        wp_send_json_error('Error de conexión al obtener vendedores');
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Aquí la respuesta real es un array con un objeto adentro, así que tomamos el primero:
    if(!is_array($data) || !isset($data[0])) {
        wp_send_json_error('Respuesta de la API no es la esperada.');
    }

    $primero = $data[0]; // <-- Primer objeto del array

    if (
        isset($primero['estado']) &&
        $primero['estado'] === 'exitoso' &&
        isset($primero['usuarios']) &&
        is_array($primero['usuarios'])
    ) {
        wp_send_json_success(array('usuarios' => $primero['usuarios']));
    } else {
        wp_send_json_error('Error en la respuesta de vendedores');
    }
}
add_action('wp_ajax_obtener_vendedores_cotizador', 'ajax_obtener_vendedores_cotizador');

// 3.3 Guardar vendedor_id (Caja 2)
function ajax_guardar_vendedor_cotizaciones() {
    if(!current_user_can('manage_options')){
        wp_send_json_error('No tienes permisos');
    }
    $nuevo_vendedor_id = sanitize_text_field($_POST['vendedor_id'] ?? '');
    update_option('corredora_vendedor_id', $nuevo_vendedor_id);

    wp_send_json_success('El vendedor ha sido actualizado exitosamente.');
}
add_action('wp_ajax_guardar_vendedor_cotizaciones', 'ajax_guardar_vendedor_cotizaciones');

// 3.4 (NUEVO) Guardar tipografía seleccionada (Caja 3)
function ajax_guardar_tipografia_personalizada() {
    if(!current_user_can('manage_options')){
        wp_send_json_error('No tienes permisos');
    }
    $nueva_fuente = sanitize_text_field($_POST['fuente'] ?? '');
    update_option('co_font_choice', $nueva_fuente);

    wp_send_json_success('Tipografía actualizada.');
}
add_action('wp_ajax_guardar_tipografia_personalizada', 'ajax_guardar_tipografia_personalizada');



// -- ↕ Terminación Código Page Back Office
// -- ↕ Iniciación Código Página de Cotización





// Creacion de la página frontend "Cotización"
function crear_pagina_cotizacion() {
    // Verificar si la página ya existe
    $pagina = get_page_by_path('cot');
    
    if (!isset($pagina->ID)) {
        // Definir los datos de la página
        $pagina_data = array(
            'post_title'   => 'Cotización',
            'post_name'    => 'cot',
            'post_content' => '[Corredora_Online_Cotizacion]',
            'post_status'  => 'publish',
            'post_type'    => 'page'
        );
        
        // Insertar la página en la base de datos
        wp_insert_post($pagina_data);
    }
}



// -- ↕ Terminación Código Página de Cotización
// -- ↕ Iniciación Código Shortcode Corredora_Online


// Shortcode [ Corredora_Online mostrar="xx" ] 'correo', 'numero', 'año-actual' despliega información guardada en options
function corredora_online_shortcode($atts) {
    $atts = shortcode_atts(array(
        'mostrar' => ''
    ), $atts, 'Corredora_Online');

    $mostrar = $atts['mostrar'];

    if ($mostrar === 'correo') {
        return get_option('co-correo-contacto', 'No disponible');
    } elseif ($mostrar === 'numero') {
        return get_option('co-numero-contacto', 'No disponible');
    } elseif ($mostrar === 'año-actual') {
        return date('Y');
    } else {
        return 'Parámetro no válido';
    }
}

add_shortcode('Corredora_Online', 'corredora_online_shortcode');




// -- ↕ Terminación Código Shortcode Corredora_Online
// -- ↕ Iniciación Código registro de CPTs ("Aseguradoras, Valoraciones"), Meta Fields y Hide Updates Elements


// Registro de CPTs y Meta Fields
function registrar_aseguradoras_custom_post_type() {
    $args = array(
        'public'             => true,
        'label'              => __('Aseguradoras'),
        'labels'             => array(
            'name'               => __('Aseguradoras'),
            'singular_name'      => __('Aseguradora'),
            'add_new'            => __('Agregar Nueva'),
            'add_new_item'       => __('Agregar Nueva Aseguradora'),
            'edit_item'          => __('Editar aseguradora'),
            'new_item'           => __('Nueva aseguradora'),
            'view_item'          => __('Ver aseguradora'),
            'search_items'       => __('Buscar Aseguradoras'),
            'not_found'          => __('No se encontraron aseguradoras'),
            'not_found_in_trash' => __('No se encontraron aseguradoras en la papelera')
        ),
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'corredora-online-main',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'aseguradora' ),
        'capability_type'    => 'post',
        'has_archive'        => false,
        'hierarchical'       => false,
        'menu_position'      => 2,
        'supports'           => array( 'title', 'thumbnail' ),
        'show_in_rest'       => false,
        'rest_base'          => 'aseguradoras',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    register_post_type( 'aseguradoras', $args );
}

add_action( 'init', 'registrar_aseguradoras_custom_post_type' );


function registrar_campos_personalizados() {
    register_post_meta('aseguradoras', 'id_aseguradora', array(
        'type' => 'string',
        'single' => true,
        'show_in_rest' => false,
    ));
    
    $campos_valoraciones = array(
        'id',
        'nombre',
        'apellido',
        'atencion',
        'disposicion',
        'contratacion',
        'recomendacion',
        'promedio',
        'llegada',
        'comentarios',
        'destacar',
        'fecha'
    );
    
    foreach ($campos_valoraciones as $campo) {
        register_post_meta('valoraciones', $campo, array(
            'type' => 'string',
            'single' => true,
            'show_in_rest' => false,
        ));
    }
}
add_action('init', 'registrar_campos_personalizados');


function registrar_valoraciones_custom_post_type() {
    $args = array(
        'public'             => true,
        'label'              => __('Valoraciones'),
        'labels'             => array(
            'name'               => __('Valoraciones'),
            'singular_name'      => __('Valoración'),
            'add_new'            => __('Agregar Nueva'),
            'add_new_item'       => __('Agregar Nueva Valoración'),
            'edit_item'          => __('Editar valoración'),
            'new_item'           => __('Nueva valoración'),
            'view_item'          => __('Ver valoración'),
            'search_items'       => __('Buscar Valoraciones'),
            'not_found'          => __('No se encontraron valoraciones'),
            'not_found_in_trash' => __('No se encontraron valoraciones en la papelera')
        ),
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => 'corredora-online-main',
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'valoracion' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => 3,
        'supports'           => array( 'title', 'thumbnail' ),
        'show_in_rest'       => true,
        'rest_base'          => 'valoraciones',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    register_post_type( 'valoraciones', $args );
}

add_action( 'init', 'registrar_valoraciones_custom_post_type' );




add_action('admin_menu', 'reordenar_submenus_corredora', 999);
function reordenar_submenus_corredora() {
    // El slug de tu menú principal
    $parent_slug = 'corredora-online-main';

    global $submenu;
    // Asegurarnos de que exista
    if (!empty($submenu[$parent_slug])) {
        // $submenu[$parent_slug] es un array de arrays
        // Cada sub-array se ve como: [0 => 'Título', 1 => 'Capacidad', 2 => 'menu_slug', 3 => 'Título Página', ...]

        $nuevo_orden = array();
        $config_item = null;

        // 1. Encontrar el item 'Configuración' (menu_slug = 'corredora-online-settings')
        foreach ($submenu[$parent_slug] as $index => $sub) {
            if (!empty($sub[2]) && $sub[2] === 'corredora-online-settings') {
                $config_item = $sub;
                // Lo quitamos del array original
                unset($submenu[$parent_slug][$index]);
                break;
            }
        }

        // 2. Insertar el item de Configuración en la posición 0
        if ($config_item) {
            $nuevo_orden[0] = $config_item;
        }

        // 3. Reindexar el resto de submenús (Aseguradoras, Valoraciones, etc.)
        $i = 1;
        foreach ($submenu[$parent_slug] as $sub) {
            $nuevo_orden[$i] = $sub;
            $i++;
        }

        // Ordenar por índice para no romper la estructura
        ksort($nuevo_orden);
        // Asignamos el array reordenado de vuelta
        $submenu[$parent_slug] = $nuevo_orden;
    }
}



// Eliminar botones de actualizar y edición rápida
function ocultar_botones_edicion_personalizados($actions, $post) {
    if ($post->post_type == 'valoraciones' || $post->post_type == 'aseguradoras') {

        unset($actions['edit']);
        unset($actions['inline hide-if-no-js']);
    }
    return $actions;
}
add_filter('post_row_actions', 'ocultar_botones_edicion_personalizados', 10, 2);


function quitar_metaboxes_personalizados() {
    remove_meta_box('submitdiv', 'valoraciones', 'side');
    remove_meta_box('slugdiv', 'valoraciones', 'normal');
    remove_meta_box('authordiv', 'valoraciones', 'normal');

    remove_meta_box('submitdiv', 'aseguradoras', 'side');
    remove_meta_box('slugdiv', 'aseguradoras', 'normal');
    remove_meta_box('authordiv', 'aseguradoras', 'normal');
}
add_action('add_meta_boxes', 'quitar_metaboxes_personalizados');


function mostrar_mensaje_personalizado() {
    global $pagenow;

    if ( $pagenow == 'post.php' && isset( $_GET['post'] ) ) {
        $post_id = $_GET['post'];
        $post_type = get_post_type( $post_id );

        if ( $post_type == 'aseguradoras' || 'valoraciones' ) {
            echo '<div class="notice notice-error is-dismissible"><p>NO modifique estos datos directamente. Hágalo a través de Corredora Online, ya que serán modificados semanalmente de manera automática.</p></div>';
        }
    }

    if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) ) {
        $post_type = $_GET['post_type'];

        if ( $post_type == 'aseguradoras' || 'valoraciones' ) {
            echo '<div class="notice notice-error"><p>No modifique estos datos directamente. Hágalo a través de Corredora Online, ya que serán modificados semanalmente de manera automática.</p></div>';
        }
    }
}
add_action( 'admin_notices', 'mostrar_mensaje_personalizado' );




// -- ↕ Terminación Código registro de CPTs ("Aseguradoras, Valoraciones"), Meta Fields y Hide Updates Elements
// -- ↕ Iniciación Código API REST Updator Elements


// Función de validación del API KEY para solicitudes entrantes
function verificar_autenticacion_api( $request ) {
    $api_key = get_option( 'api_key' );
    $api_key_received = $request->get_header( 'Authorization' );

    if ( $api_key === $api_key_received ) {
        return true;
    } else {
        return new WP_Error( 'rest_forbidden', esc_html__( 'Acceso no autorizado.', 'text-domain' ), array( 'status' => 401 ) );
    }
}


// Registro de endpoint "Pulse"
function registrar_endpoint_personalizado() {
    register_rest_route('corredora-online/v1', '/pulse/', array(
        'methods'   => 'GET',
        'callback'  => function ($request) {
            $rest_route = $request->get_param('restRoute');
            if ($rest_route === 'udpAseguradoras') {
                return procesar_peticion_endpoint_personalizado($request);
            } elseif ($rest_route === 'udpInfoContacto') {
                return procesar_peticion_contacto($request);
            } elseif ($rest_route === 'udpValoraciones') {
                return procesar_peticion_valoraciones($request);
            } else {
                return new WP_REST_Response('OK', 200);
            }
        },
        'permission_callback' => 'verificar_autenticacion_api',
    ));
}
add_action('rest_api_init', 'registrar_endpoint_personalizado');


// Function para procesar solicitudes Pulse con query ?restRoute = "udpAseguradoras"
function procesar_peticion_endpoint_personalizado($data) {
    $corredora_id = get_option('corredora_id');
    $api_key = get_option('api_key');

    $url = 'https://atm.novelty8.com/webhook/api/corredora-online/aseguradoras';
    $url = add_query_arg('idc', $corredora_id, $url);

    $args = array(
        'headers' => array(
            'X-API-KEY' => $api_key
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return new WP_REST_Response("Error: $error_message", 500);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data) 
        || !isset($data[0]['estado']) 
        || $data[0]['estado'] !== 'exitoso'
        || !isset($data[0]['aseguradoras'])) {
        return new WP_REST_Response('Error: Respuesta no válida de la API.', 500);
    }

    // Obtener IDs de las aseguradoras que vienen en la respuesta
    $aseguradoras_ids = array_map(function($aseguradora) {
        return $aseguradora['id'];
    }, $data[0]['aseguradoras']);

    // Obtener todos los posts 'aseguradoras' existentes en WP
    $existing_posts = get_posts(array(
        'post_type'   => 'aseguradoras',
        'numberposts' => -1,
        'post_status' => 'any',
    ));

    // Eliminar las aseguradoras que no aparezcan en la nueva respuesta
    foreach ($existing_posts as $post) {
        $post_id = $post->ID;
        $id_aseguradora = get_post_meta($post_id, 'id_aseguradora', true);

        if (!in_array($id_aseguradora, $aseguradoras_ids)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            if ($thumbnail_id) {
                wp_delete_attachment($thumbnail_id, true);
            }
            wp_delete_post($post_id, true);
        }
    }

    // Crear o actualizar posts con la información recibida
    foreach ($data[0]['aseguradoras'] as $aseguradora) {
        $id_aseguradora = $aseguradora['id'];

        // Verificar si ya existe un post con ese 'id_aseguradora'
        $existing_post = get_posts(array(
            'post_type' => 'aseguradoras',
            'meta_key' => 'id_aseguradora',
            'meta_value' => $id_aseguradora,
            'posts_per_page' => 1,
        ));

        if ($existing_post) {
            // Si existe, lo actualizamos
            $post_id = $existing_post[0]->ID;
            wp_update_post(array(
                'ID'         => $post_id,
                'post_title' => wp_strip_all_tags($aseguradora['nombre']),
            ));
        } else {
            // Si no existe, lo creamos
            $post_id = wp_insert_post(array(
                'post_title'  => wp_strip_all_tags($aseguradora['nombre']),
                'post_type'   => 'aseguradoras',
                'post_status' => 'publish'
            ));

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, 'id_aseguradora', $id_aseguradora);
            }
        }

        // **Agregar o actualizar los nuevos meta fields**: 'enlace_de_pago' y 'enlace_siniestros'
        update_post_meta($post_id, 'enlace_de_pago',      $aseguradora['enlace_de_pago']);
        update_post_meta($post_id, 'enlace_siniestros',   $aseguradora['enlace_siniestros']);

        // Asignar (o no) la imagen destacada si no existe una
        if (!empty($aseguradora['logotipo']) && !has_post_thumbnail($post_id)) {
            $image_url = $aseguradora['logotipo'];
            $image_name = basename($image_url);
            $upload = wp_upload_bits($image_name, null, file_get_contents($image_url));

            if (!$upload['error']) {
                $file_path = $upload['file'];
                $file_name = basename($file_path);
                $file_type = wp_check_filetype($file_name, null);
                $attachment_title = sanitize_file_name(pathinfo($file_name, PATHINFO_FILENAME));
                $wp_upload_dir = wp_upload_dir();

                $attachment = array(
                    'guid'           => $wp_upload_dir['url'] . '/' . $file_name,
                    'post_mime_type' => $file_type['type'],
                    'post_title'     => $attachment_title,
                    'post_content'   => '',
                    'post_status'    => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

                if (!is_wp_error($attach_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                } else {
                    // En caso de error, si es un post recién creado, lo borramos
                    if (!$existing_post) {
                        wp_delete_post($post_id, true);
                    }
                }
            } else {
                // En caso de error en upload
                if (!$existing_post) {
                    wp_delete_post($post_id, true);
                }
            }
        }
    }

    return new WP_REST_Response('OK', 200);
}


// Function para procesar solicitudes Pulse con query ?restRoute = "udpInfoContacto"
function procesar_peticion_contacto($request) {
    // Obtener la API KEY y el ID de corredora guardados como opciones
    $api_key = get_option('api_key');
    $corredora_id = get_option('corredora_id');

    // Construir la URL de la API con el ID de corredora
    $url = 'https://atm.novelty8.com/webhook/api/corredora-online/corredora';
    $url = add_query_arg(array('idc' => $corredora_id), $url);

    // Configurar los argumentos para la solicitud HTTP
    $args = array(
        'headers' => array(
            'X-API-KEY' => $api_key
        )
    );

    // Realizar la solicitud HTTP
    $response = wp_remote_get($url, $args);

    // Verificar si ocurrió un error en la solicitud
    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return new WP_REST_Response("Error: $error_message", 500);
    }

    // Decodificar la respuesta JSON
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Verificar si la respuesta es válida y contiene la información de contacto
    if (!is_array($data) || !isset($data['estado']) || $data['estado'] !== 'exitoso' || !isset($data['data'])) {
        return new WP_REST_Response('Error: Respuesta no válida de la API.', 500);
    }

    // Obtener los datos de la respuesta
    $correo_contacto = isset($data['data']['correo']) ? $data['data']['correo'] : '';
    $numero_contacto = isset($data['data']['celular']) ? $data['data']['celular'] : '';
    $color_fondo = isset($data['data']['color-fondo']) ? $data['data']['color-fondo'] : '';
    $color_texto = isset($data['data']['color-texto']) ? $data['data']['color-texto'] : '';
    $border_radius = isset($data['data']['border-radius']) ? $data['data']['border-radius'] : '';

    // Actualizar los valores en las opciones
    update_option('co-correo-contacto', $correo_contacto);
    update_option('co-numero-contacto', $numero_contacto);
    update_option('co-color-fondo', $color_fondo);
    update_option('co-color-texto', $color_texto);
    update_option('co-border-radius', $border_radius);

    return new WP_REST_Response('OK', 200);
}


// Function para procesar solicitudes Pulse con query ?restRoute = "udpValoraciones"
function procesar_peticion_valoraciones($data) {
    $corredora_id = get_option('corredora_id');
    $api_key = get_option('api_key');

    $url = 'https://atm.novelty8.com/webhook/api/corredora-online/valoraciones';
    $url = add_query_arg('idc', $corredora_id, $url);

    $args = array(
        'headers' => array(
            'X-API-KEY' => $api_key
        )
    );

    $response = wp_remote_get($url, $args);

    if (is_wp_error($response)) {
        $error_message = $response->get_error_message();
        return new WP_REST_Response("Error: $error_message", 500);
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!is_array($data)) {
        return new WP_REST_Response('Error: Respuesta no válida de la API.', 500);
    }

    $valoraciones_ids = array_map(function($valoracion) {
        return $valoracion['id'];
    }, $data);

    $existing_posts = get_posts(array(
        'post_type' => 'valoraciones',
        'numberposts' => -1,
        'post_status' => 'any',
    ));

    foreach ($existing_posts as $post) {
        $post_id = $post->ID;
        $id_valoracion = get_post_meta($post_id, 'id', true);

        if (!in_array($id_valoracion, $valoraciones_ids)) {
            wp_delete_post($post_id, true);
        }
    }

    foreach ($data as $valoracion) {
        $id_valoracion = $valoracion['id'];

        $existing_post = get_posts(array(
            'post_type' => 'valoraciones',
            'meta_key' => 'id',
            'meta_value' => $id_valoracion,
            'posts_per_page' => 1,
        ));

        if ($existing_post) {
            $post_id = $existing_post[0]->ID;
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => wp_strip_all_tags($valoracion['nombre'] . ' ' . $valoracion['apellido']),
            ));
        } else {
            $post_id = wp_insert_post(array(
                'post_title' => wp_strip_all_tags($valoracion['nombre'] . ' ' . $valoracion['apellido']),
                'post_type' => 'valoraciones',
                'post_status' => 'publish'
            ));

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, 'id', $id_valoracion);
            }
        }

        $meta_fields = array('nombre', 'apellido', 'atencion', 'disposicion', 'contratacion', 'recomendacion', 'promedio', 'llegada', 'comentarios', 'destacar', 'fecha');

        foreach ($meta_fields as $field) {
            update_post_meta($post_id, $field, $valoracion[$field]);
        }
    }

    return new WP_REST_Response('OK', 200);
}




// -- ↕ Terminación Código API REST Updator Elements
// -- ↕ Iniciación Código Shortcode [Corredora_Online_Cotizador]

// Función para mostrar el formulario del cotizador
function corredora_online_cotizador($atts)
{
    // Array de comunas por región, sin cambios
    $comunasPorRegion = array(
        'Arica y Parinacota' => ['Arica', 'Camarones', 'Putre', 'General Lagos'],
        'Tarapacá' => ['Iquique', 'Alto Hospicio', 'Pozo Almonte', 'Camiña', 'Colchane', 'Huara', 'Pica'],
        'Antofagasta' => ['Antofagasta', 'Mejillones', 'Sierra Gorda', 'Taltal', 'Calama', 'Ollagüe', 'San Pedro de Atacama', 'María Elena', 'Tocopilla'],
        'Atacama' => ['Copiapó', 'Caldera', 'Tierra Amarilla', 'Chañaral', 'Diego de Almagro', 'Vallenar', 'Alto del Carmen', 'Freirina', 'Huasco'],
        'Coquimbo' => ['La Serena', 'Coquimbo', 'Andacollo', 'La Higuera', 'Paiguano', 'Vicuña', 'Illapel', 'Canela', 'Los Vilos', 'Salamanca', 'Ovalle', 'Combarbalá', 'Monte Patria', 'Punitaqui', 'Río Hurtado'],
        'Valparaíso' => ['Valparaíso', 'Casablanca', 'Concón', 'Juan Fernández', 'Puchuncaví', 'Quintero', 'Viña del Mar', 'Isla de Pascua', 'Los Andes', 'Calle Larga', 'Rinconada', 'San Esteban', 'La Ligua', 'Cabildo', 'Papudo', 'Petorca', 'Zapallar', 'Quillota', 'Calera', 'Hijuelas', 'La Cruz', 'Nogales', 'San Antonio', 'Algarrobo', 'Cartagena', 'El Quisco', 'El Tabo', 'Santo Domingo', 'San Felipe', 'Catemu', 'Llaillay', 'Panquehue', 'Putaendo', 'Santa María', 'Quilpué', 'Limache', 'Olmué', 'Villa Alemana'],
        'Metropolitana de Santiago' => ['Santiago', 'Cerrillos', 'Cerro Navia', 'Conchalí', 'El Bosque', 'Estación Central', 'Huechuraba', 'Independencia', 'La Cisterna', 'La Florida', 'La Granja', 'La Pintana', 'La Reina', 'Las Condes', 'Lo Barnechea', 'Lo Espejo', 'Lo Prado', 'Macul', 'Maipú', 'Ñuñoa', 'Pedro Aguirre Cerda', 'Peñalolén', 'Providencia', 'Pudahuel', 'Quilicura', 'Quinta Normal', 'Recoleta', 'Renca', 'San Joaquín', 'San Miguel', 'San Ramón', 'Vitacura', 'Puente Alto', 'Pirque', 'San José de Maipo', 'Colina', 'Lampa', 'Tiltil'],
        'Libertador General Bernardo O’Higgins' => ['Rancagua', 'Codegua', 'Coinco', 'Coltauco', 'Doñihue', 'Graneros', 'Las Cabras', 'Machalí', 'Malloa', 'Mostazal', 'Olivar', 'Peumo', 'Pichidegua', 'Quinta de Tilcoco', 'Rengo', 'Requínoa', 'San Vicente', 'Pichilemu', 'La Estrella', 'Litueche', 'Marchihue', 'Navidad', 'Paredones', 'San Fernando', 'Chépica', 'Chimbarongo', 'Lolol', 'Nancagua', 'Palmilla', 'Peralillo', 'Placilla', 'Pumanque', 'Santa Cruz'],
        'Maule' => ['Talca', 'Consitución', 'Curepto', 'Empedrado', 'Maule', 'Pelarco', 'Pencahue', 'Río Claro', 'San Clemente', 'San Rafael', 'Cauquenes', 'Chanco', 'Pelluhue', 'Curicó', 'Hualañé', 'Licantén', 'Molina', 'Rauco', 'Romeral', 'Sagrada Familia', 'Teno', 'Vichuquén', 'Linares', 'Colbún', 'Longaví', 'Parral', 'Retiro', 'San Javier', 'Villa Alegre', 'Yerbas Buenas'],
        'Ñuble' => ['Chillán', 'Bulnes', 'Cobquecura', 'Coelemu', 'Coihueco', 'Chillán Viejo', 'El Carmen', 'Ninhue', 'Ñiquén', 'Pemuco', 'Pinto', 'Portezuelo', 'Quillón', 'Quirihue', 'Ránquil', 'San Carlos', 'San Fabián', 'San Ignacio', 'San Nicolás', 'Treguaco', 'Yungay'],
        'Biobío' => ['Concepción', 'Coronel', 'Chiguayante', 'Florida', 'Hualqui', 'Lota', 'Penco', 'San Pedro de la Paz', 'Santa Juana', 'Talcahuano', 'Tomé', 'Hualpén', 'Lebu', 'Arauco', 'Cañete', 'Contulmo', 'Curanilahue', 'Los Álamos', 'Tirúa', 'Los Ángeles', 'Antuco', 'Cabrero', 'Laja', 'Mulchén', 'Nacimiento', 'Negrete', 'Quilaco', 'Quilleco', 'San Rosendo', 'Santa Bárbara', 'Tucapel', 'Yumbel', 'Alto Biobío'],
        'La Araucanía' => ['Temuco', 'Carahue', 'Cunco', 'Curarrehue', 'Freire', 'Galvarino', 'Gorbea', 'Lautaro', 'Loncoche', 'Melipeuco', 'Nueva Imperial', 'Padre las Casas', 'Perquenco', 'Pitrufquén', 'Pucón', 'Saavedra', 'Teodoro Schmidt', 'Toltén', 'Vilcún', 'Villarrica', 'Cholchol', 'Angol', 'Collipulli', 'Curacautín', 'Ercilla', 'Lonquimay', 'Los Sauces', 'Lumaco', 'Purén', 'Renaico', 'Traiguén', 'Victoria'],
        'Los Ríos' => ['Valdivia', 'Corral', 'Lanco', 'Los Lagos', 'Máfil', 'Mariquina', 'Paillaco', 'Panguipulli', 'La Unión', 'Futrono', 'Lago Ranco', 'Río Bueno'],
        'Los Lagos' => ['Puerto Montt', 'Calbuco', 'Cochamó', 'Fresia', 'Frutillar', 'Los Muermos', 'Llanquihue', 'Maullín', 'Puerto Varas', 'Castro', 'Ancud', 'Chonchi', 'Curaco de Vélez', 'Dalcahue', 'Puqueldón', 'Queilén', 'Quellón', 'Quemchi', 'Quinchao', 'Osorno', 'Puerto Octay', 'Purranque', 'Puyehue', 'Río Negro', 'San Juan de la Costa', 'San Pablo', 'Chaitén', 'Futaleufú', 'Hualaihué', 'Palena'],
        'Aysén del General Carlos Ibáñez del Campo' => ['Coyhaique', 'Lago Verde', 'Aysén', 'Cisnes', 'Guaitecas', 'Cochrane', 'O’Higgins', 'Tortel', 'Chile Chico', 'Río Ibáñez'],
        'Magallanes y de la Antártica Chilena' => ['Punta Arenas', 'Laguna Blanca', 'Río Verde', 'San Gregorio', 'Cabo de Hornos', 'Antártica', 'Porvenir', 'Primavera', 'Timaukel', 'Natales', 'Torres del Paine'],
    );
    
    // Obtener valores opcionales de color y border desde la BD
    $color_fondo = get_option('co-color-fondo', '#52868E');
    $color_texto = get_option('co-color-texto', '#ffffff');
    $border_radius = get_option('co-border-radius', '8px'); 

    ob_start();
    ?>
    
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap');

        .cotizador-form-container {
            padding: 0px 0px 0px 40px;
            font-family: 'Nunito', sans-serif;
        }

        .cotizador-form-container label {
            display: block;
            margin-bottom: 8px;
            font-size: 16px;
            font-weight: 600;
        }

        .cotizador-form-container input[type="text"],
        .cotizador-form-container input[type="email"],
        .cotizador-form-container input[type="password"] {
            width: calc(100% - 22px);
            padding: 10px 16px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: <?php echo esc_attr($border_radius); ?>;
            box-sizing: border-box;
            font-size: 16px;
        }

        .cotizador-form-container input[type="submit"],
        .cotizador-form-container .next-step {
            width: calc(100% - 20px);
            padding: 14px;
            margin-top: 4px;
            background-color: <?php echo esc_attr($color_fondo); ?>;
            border: none;
            border-radius: <?php echo esc_attr($border_radius); ?>;
            color: <?php echo esc_attr($color_texto); ?>;
            font-size: 16px;
            cursor: pointer;
            font-family: 'Nunito', sans-serif;
            font-weight: 600;
        }

        .cotizador-form-container input[type="submit"]:hover,
        .cotizador-form-container .next-step:hover {
            background-color: #0056b3;
        }

        .cotizador-form-container .form-footer {
            text-align: center;
            margin-top: 20px;
        }

        .cotizador-form-container .form-footer a {
            color: #007bff;
            text-decoration: none;
        }

        .cotizador-form-container .form-footer a:hover {
            text-decoration: underline;
        }

        #patente {
            text-transform: uppercase;
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .steps {
            display: flex;
            justify-content: space-around;
            margin-bottom: 30px;
            width: calc(100% - 20px);
        }

        .steps div {
            flex: 1;
            padding: 8px;
            text-align: center;
            border: 1px solid #ccc;
            cursor: pointer;
            font-size: 14px;
            color: #5F5F5F;
            border-radius: 8px;
            margin-right: 18px;
        }
        
        .steps div:last-child {
            margin-right: 0;
        }

        .steps div.active {
            cursor: default;
            background-color: #F0F0F0;
            border: 1px solid #F0F0F0;
        }

        .error-message {
            color: red;
            font-family: 'Nunito', sans-serif;
            font-size: 13px;
            margin-top: -1px;
            margin-bottom: 7px;
        }

        .row {
            display: flex;
        }

        .half {
            flex: 1;
            margin-right: 4px;
            margin-top: -4px;
            margin-bottom: -4px;
        }

        .half:last-child {
            margin-right: 0;
        }

        .half.margin-bottom {
            margin-bottom: 19px;
        }

        .invalid-input {
            border-color: red !important;
        }

        .wait-cursor {
            cursor: wait !important;
        }

        select {
            all: unset;
        }

        .cotizador-form-container select {
            width: calc(100% - 22px);
            padding: 9px 14px;
            margin-top: 2px;
            margin-bottom: 8px;
            border: 1px solid #ccc;
            border-radius: <?php echo esc_attr($border_radius); ?>;
            box-sizing: border-box;
            font-size: 15px;
        }

        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #3498db;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 10px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        #loading-indicator {
            display: none; 
            position: fixed; 
            top: 0; 
            left: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(255,255,255,0.8); 
            z-index: 9999; 
            border-radius: 20px;
        }
        #loading-indicator > div {
            position: absolute; 
            top: 50%; 
            left: 50%; 
            transform: translate(-50%, -50%);
        }
    </style>

    <div id="loading-indicator">
        <div>
            <div class="spinner"></div>
            <p>Cargando...</p>
        </div>
    </div>

    <div class="cotizador-form-container">
        <div class="steps">
            <div class="step-title active" data-step="1">1. Vehículo</div>
            <div class="step-title" data-step="2">2. Contacto</div>
        </div>
        <form id="cotizador-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
            <div class="step active" data-step="1">
                <p>
                    <label for="patente">Ingresa la patente</label>
                    <input type="text" id="patente" name="patente" required placeholder="ABCD12">
                    <span id="error-message" class="error-message" style="display: none;">La patente ingresada no es válida</span>
                </p>
                <p id="marca-field" style="display: none;">
                    <label for="marca">Marca</label>
                    <input type="text" id="marca" name="marca">
                </p>
                <p id="modelo-field" style="display: none;">
                    <label for="modelo">Modelo</label>
                    <input type="text" id="modelo" name="modelo">
                </p>
                <p id="año-field" style="display: none;">
                    <label for="año">Año</label>
                    <input type="text" id="año" name="año">
                </p>
                <!-- Campo oculto para guardar el parámetro "tipo" obtenido de la API -->
                <input type="hidden" id="tipoVehiculo" name="tipoVehiculo" value="">
                <p>
                    <button type="button" class="next-step">Siguiente</button>
                </p>
            </div>
            <div class="step" data-step="2">
                <p>
                    <label for="rut">RUT</label>
                    <input type="text" id="rut" name="rut" required maxlength="12" placeholder="12.345.678-9">
                    <span id="rut-error-message" class="error-message" style="display: none;">El RUT ingresado no es válido</span>
                </p>
                <div class="row">
                    <p class="half margin-bottom" id="nombre-field" style="display: none;">
                        <label for="nombre">Nombre</label>
                        <input type="text" id="nombre" name="nombre" required>
                    </p>
                    <p class="half margin-bottom" id="apellido-field" style="display: none;">
                        <label for="apellido">Apellido</label>
                        <input type="text" id="apellido" name="apellido" required>
                    </p>
                </div>
                <div class="row" style="margin-bottom: -8px;">
                    <p class="half margin-bottom">
                        <label for="region">Región</label>
                        <select id="region" name="region" required>
                            <option value="">Selecciona una región</option>
                            <option value="Arica y Parinacota">Región de Arica y Parinacota</option>
                            <option value="Tarapacá">Región de Tarapacá</option>
                            <option value="Antofagasta">Región de Antofagasta</option>
                            <option value="Atacama">Región de Atacama</option>
                            <option value="Coquimbo">Región de Coquimbo</option>
                            <option value="Valparaíso">Región de Valparaíso</option>
                            <option value="Metropolitana de Santiago">Región Metropolitana de Santiago</option>
                            <option value="Libertador General Bernardo O’Higgins">Región del Libertador General Bernardo O’Higgins</option>
                            <option value="Maule">Región del Maule</option>
                            <option value="Ñuble">Región de Ñuble</option>
                            <option value="Biobío">Región del Biobío</option>
                            <option value="La Araucanía">Región de La Araucanía</option>
                            <option value="Los Ríos">Región de Los Ríos</option>
                            <option value="Los Lagos">Región de Los Lagos</option>
                            <option value="Aysén del General Carlos Ibáñez del Campo">Región de Aysén del General Carlos Ibáñez del Campo</option>
                            <option value="Magallanes y de la Antártica Chilena">Región de Magallanes y de la Antártica Chilena</option>
                        </select>
                    </p>
                    <p class="half margin-bottom">
                        <label for="comuna">Comuna</label>
                        <select id="comuna" name="comuna" required></select>
                    </p>
                </div>
                <p>
                    <label for="correo">Correo</label>
                    <input type="email" id="correo" name="correo" required placeholder="micorreo@gmail.com">
                </p>
                <p>
                    <label for="telefono">Celular</label>
                    <input type="text" id="telefono" name="telefono" required placeholder="+56911111111">
                </p>
                <p>
                    <input type="submit" name="submit" value="Cotizar online" id="submit-button" disabled>
                </p>
            </div>
        </form>
    </div>
     
    <script type="text/javascript">
    document.addEventListener('DOMContentLoaded', function () {

        // Declarar currentStep e iniciarlo en 1
        var currentStep = 1;

        var patenteInput = document.getElementById('patente');
        var errorMessage = document.getElementById('error-message');
        var rutInput = document.getElementById('rut');
        var rutErrorMessage = document.getElementById('rut-error-message');
        var nombreField = document.getElementById('nombre-field');
        var apellidoField = document.getElementById('apellido-field');
        var nombreInput = document.getElementById('nombre');
        var apellidoInput = document.getElementById('apellido');
        
        var timeout = null;
        var isPatenteValid = false;
        var isRUTValid = false;

        var comunasPorRegion = <?php echo json_encode($comunasPorRegion); ?>;

        function showLoading() {
            document.getElementById('loading-indicator').style.display = 'block';
        }
    
        function hideLoading() {
            document.getElementById('loading-indicator').style.display = 'none';
        }

        // Función para validar el RUT
        function validarRUT(rut) {
            rut = rut.replace(/[.-]/g, '');
            const dv = rut.slice(-1);
            const rutCuerpo = rut.slice(0, -1);
            let suma = 0;
            let multiplicador = 2;

            for (let i = rutCuerpo.length - 1; i >= 0; i--) {
                suma += parseInt(rutCuerpo.charAt(i)) * multiplicador;
                multiplicador = (multiplicador === 7) ? 2 : multiplicador + 1;
            }

            const dvCalculado = 11 - (suma % 11);
            const dvEsperado = (dvCalculado === 11) ? '0' : (dvCalculado === 10 ? 'K' : dvCalculado.toString());
            return dv.toUpperCase() === dvEsperado;
        }

        // Formateo de RUT en tiempo real
        rutInput.addEventListener('input', function () {
            var rut = this.value.trim().replace(/\./g, '').replace('-', '');
            if (rut.length > 1) {
                rut = rut.replace(/^(\d{1,9})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4');
            }
            this.value = rut;
        });

        // Cambio de región => carga comunas
        document.getElementById('region').addEventListener('change', function() {
            var region = this.value;
            var comunaSelect = document.getElementById('comuna');
            comunaSelect.innerHTML = ''; // Limpiar opciones anteriores

            if (region) {
                var comunas = comunasPorRegion[region];
                if (comunas && comunas.length > 0) {
                    comunas.forEach(function(comuna) {
                        var option = document.createElement('option');
                        option.value = comuna;
                        option.textContent = comuna;
                        comunaSelect.appendChild(option);
                    });
                }
            }
        });

        // Validar patente en tiempo real
        patenteInput.addEventListener('input', function () {
            clearTimeout(timeout);

            if (this.value.replace(/-/g, '').length >= 4) {
                timeout = setTimeout(function () {
                    var patente = patenteInput.value;
                    if (patente) {
                        showLoading();
                        document.body.classList.add('wait-cursor');

                        var apiKey = '<?php echo esc_js(get_option('api_key')); ?>';
                        var corredoraId = '<?php echo esc_js(get_option('corredora_id')); ?>';

                        fetch(`https://atm.novelty8.com/webhook/api/corredora-online/tools/vehiculo-info?patente=${patente}&idc=${corredoraId}`, {
                            headers: {
                                'X-API-KEY': apiKey
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            document.body.classList.remove('wait-cursor');

                            if (data.estado === "exitoso") {
                                document.getElementById('marca').value = data.data.marca;
                                document.getElementById('modelo').value = data.data.modelo;
                                document.getElementById('año').value = data.data.año;
                                // Asignar el valor del parámetro "tipo" (no "type") al campo oculto
                                document.getElementById('tipoVehiculo').value = data.data.tipo;
                                
                                errorMessage.style.display = 'none';
                                patenteInput.classList.remove('invalid-input');
                                isPatenteValid = true;
                                enableSubmitButton();
                                showFields(['marca-field', 'modelo-field', 'año-field']);
                            } else {
                                errorMessage.textContent = 'La patente ingresada no es válida';
                                errorMessage.style.display = 'block';
                                patenteInput.classList.add('invalid-input');
                                isPatenteValid = false;
                                enableSubmitButton();
                                hideFields(['marca-field', 'modelo-field', 'año-field']);
                            }
                        })
                        .catch(error => {
                            hideLoading();
                            document.body.classList.remove('wait-cursor');
                            console.error('Error al obtener los datos del vehículo:', error);
                        });
                    }
                }, 1000);
            }
        });

        // Validar RUT en tiempo real
        rutInput.addEventListener('input', function () {
            clearTimeout(timeout);

            timeout = setTimeout(function () {
                var rut = rutInput.value.trim().replace(/[\.\-]/g, '');
                if (rut.length > 0) {
                    if (validarRUT(rut)) {
                        showLoading();
                        rutInput.classList.remove('invalid-input');
                        rutErrorMessage.style.display = 'none';
                        
                        document.body.classList.add('wait-cursor');

                        var apiKey = '<?php echo esc_js(get_option('api_key')); ?>';
                        var corredoraId = '<?php echo esc_js(get_option('corredora_id')); ?>';

                        fetch(`https://atm.novelty8.com/webhook/api/corredora-online/tools/persona-info?rut=${rut}&idc=${corredoraId}`, {
                            headers: {
                                'X-API-KEY': apiKey
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            hideLoading();
                            document.body.classList.remove('wait-cursor');

                            if (data.estado === "exitoso") {
                                // Si no hay nombre en la base, mostramos campos para que el usuario los rellene
                                if (data.data.nombre === "**" || !data.data.nombre) {
                                    nombreField.style.display = 'block';
                                    apellidoField.style.display = 'block';
                                    rutInput.classList.remove('invalid-input');
                                    isRUTValid = true;
                                } else {
                                    isRUTValid = true;
                                    var nombreCompleto = data.data.nombre.split(' ');
                                    nombreInput.value = nombreCompleto[0] || '';
                                    apellidoInput.value = nombreCompleto.slice(1).join(' ') || '';
                                    nombreField.style.display = 'none';
                                    apellidoField.style.display = 'none';
                                    rutErrorMessage.style.display = 'none';
                                    rutInput.classList.remove('invalid-input');
                                }
                            } else {
                                nombreField.style.display = 'block';
                                apellidoField.style.display = 'block';
                                rutInput.classList.remove('invalid-input');
                                isRUTValid = false;
                            }
                            updateSubmitButton();
                        })
                        .catch(error => {
                            hideLoading();
                            document.body.classList.remove('wait-cursor');

                            rutErrorMessage.textContent = 'Error al obtener información del RUT';
                            rutErrorMessage.style.display = 'block';
                            nombreField.style.display = 'none';
                            apellidoField.style.display = 'none';
                            rutInput.classList.add('invalid-input');
                            isRUTValid = false;
                            updateSubmitButton();
                        });
                    } else {
                        rutErrorMessage.textContent = 'El RUT ingresado no es válido';
                        rutErrorMessage.style.display = 'block';
                        nombreField.style.display = 'none';
                        apellidoField.style.display = 'none';
                        rutInput.classList.add('invalid-input');
                        isRUTValid = false;
                        updateSubmitButton();
                    }
                } else {
                    rutErrorMessage.style.display = 'none';
                    isRUTValid = false;
                    updateSubmitButton();
                }
            }, 500);
        });

        // Función para detectar el tipo de cliente según el nombre y apellido
        function detectarTipoCliente() {
            var nombre = document.getElementById('nombre').value.trim();
            var apellido = document.getElementById('apellido').value.trim();
            var fullName = (nombre + " " + apellido).toUpperCase();
            var regexEmpresa = /SPA|LIMITADA|LTDA|EIRL|S\.A\.?|S A\.?|SOCIEDAD ANÓNIMA|SOCIEDAD COMERCIAL|COMERCIAL/;
            return regexEmpresa.test(fullName) ? "empresa" : "persona-natural";
        }

        // Habilitar / deshabilitar botón "Cotizar online"
        function updateSubmitButton() {
            var submitButton = document.getElementById('submit-button');
            if (isPatenteValid && isRUTValid) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        function enableSubmitButton() {
            var submitButton = document.getElementById('submit-button');
            if (isPatenteValid && isRUTValid) {
                submitButton.disabled = false;
            } else {
                submitButton.disabled = true;
            }
        }

        // Botón para pasar del paso 1 al paso 2
        document.querySelector('.next-step').addEventListener('click', function () {
            var valid = true;
            // Chequea campos requeridos en el paso 1
            document.querySelectorAll('.step[data-step="1"] [required]').forEach(function (input) {
                if (!input.value) {
                    valid = false;
                    input.focus();
                }
            });

            if (valid && isPatenteValid) {
                // Oculta paso 1, muestra paso 2
                document.querySelector('.step[data-step="1"]').classList.remove('active');
                document.querySelector('.step[data-step="2"]').classList.add('active');

                document.querySelector('.step-title[data-step="1"]').classList.remove('active');
                document.querySelector('.step-title[data-step="2"]').classList.add('active');

                // Actualiza variable de control
                currentStep = 2;
            }
        });

        // Permite clickear en los títulos de paso si step <= currentStep
        document.querySelectorAll('.step-title').forEach(function (title) {
            title.addEventListener('click', function () {
                var step = parseInt(this.getAttribute('data-step'));
                if (step <= currentStep) {
                    showStep(step);
                    currentStep = step;
                }
            });
        });

        function showStep(step) {
            document.querySelectorAll('.step').forEach(function (stepElement) {
                stepElement.classList.remove('active');
            });
            document.querySelector('.step[data-step="' + step + '"]').classList.add('active');

            document.querySelectorAll('.step-title').forEach(function (title) {
                title.classList.remove('active');
            });
            document.querySelector('.step-title[data-step="' + step + '"]').classList.add('active');
        }

        // Mostrar / ocultar fields
        function showFields(fields) {
            fields.forEach(field => {
                document.getElementById(field).style.display = 'block';
            });
        }

        function hideFields(fields) {
            fields.forEach(field => {
                document.getElementById(field).style.display = 'none';
            });
        }

        // Envío final del formulario
        document.getElementById('cotizador-form').addEventListener('submit', function (event) {
            event.preventDefault();

            if (isPatenteValid && isRUTValid) {
                var apiKey = '<?php echo esc_js(get_option('api_key')); ?>';
                var corredoraId = '<?php echo esc_js(get_option('corredora_id')); ?>';
                var producto = "347";
                var idVendedor = "<?php echo esc_js(get_option('corredora_vendedor_id')); ?>";
                var notificarCot = true;
                
                // Detectar dinámicamente el tipo de cliente usando la función
                var tipoCliente = detectarTipoCliente();

                var data = {
                    producto: producto,
                    idc: corredoraId,
                    idVendedor: idVendedor,
                    patenteVehiculo: document.getElementById('patente').value,
                    marcaVehiculo: document.getElementById('marca').value,
                    modeloVehiculo: document.getElementById('modelo').value,
                    // Enviar además región, comuna, tipo de vehículo y el año (anoVehiculo)
                    region: document.getElementById('region').value,
                    comuna: document.getElementById('comuna').value,
                    tipoVehiculo: document.getElementById('tipoVehiculo').value,
                    anoVehiculo: document.getElementById('año').value,
                    rut: document.getElementById('rut').value,
                    'tipo-cliente': tipoCliente,
                    nombre: document.getElementById('nombre').value,
                    apellido: document.getElementById('apellido').value,
                    email: document.getElementById('correo').value,
                    celular: document.getElementById('telefono').value,
                    notificar: notificarCot
                };
                
                showLoading();

                fetch('https://atm.novelty8.com/webhook/api/corredora-online/cotizaciones', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-API-KEY': apiKey
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(responseData => {
                    hideLoading();
                    alert('Solicitud enviada con éxito.');
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error al enviar la solicitud:', error);
                    alert('Hubo un error al enviar la solicitud.');
                });
            } else {
                alert('Por favor, completa correctamente todos los campos.');
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('Corredora_Online_Cotizador', 'corredora_online_cotizador');

// -- ↕ Terminación Código Shortcode [Corredora_Online_Cotizador]
// -- ↕ Iniciación Código Shortcode [Corredora_Online_Compañias]


function slider_aseguradoras_shortcode() {
    $args = array(
        'post_type' => 'aseguradoras',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'orderby' => 'title',
        'order' => 'ASC'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $output = '<div class="slider-aseguradoras-container">
                      <div class="slider-aseguradoras">';
        
        while ($query->have_posts()) {
            $query->the_post();
            if (has_post_thumbnail()) {
                $image = get_the_post_thumbnail_url(get_the_ID(), 'full');
                $output .= '<div class="slide">';
                $output .= '<img src="' . esc_url($image) . '" alt="' . esc_attr(get_the_title()) . '" loading="lazy">';
                $output .= '</div>';
            }
        }
        
        $output .= '</div></div>';
        
        wp_reset_postdata();
        return $output;
    } else {
        return '<p>Configure las compañías con las que tiene código primero.</p>';
    }
}

function slider_aseguradoras_assets() {
    $custom_css = "
    <style>
        .slider-aseguradoras-container {
            width: 100%;
            overflow: hidden;
            position: relative;
        }
        .slider-aseguradoras {
            display: flex;
            transition: transform 0.5s linear;
            will-change: transform;
        }
        .slider-aseguradoras .slide {
            flex: 0 0 auto;
            margin-right: 60px; /* Aumenta este valor para más separación */
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .slider-aseguradoras img {
            max-width: 100%;
            max-height: 80px;
            width: auto;
            height: auto;
            object-fit: contain;
        }
        /* Ajustes para tablets */
        @media (max-width: 1024px) {
            .slider-aseguradoras .slide {
                margin-right: 40px;
            }
        }
        /* Ajustes para móviles */
        @media (max-width: 768px) {
            .slider-aseguradoras .slide {
                margin-right: 30px;
            }
        }
    </style>
    ";
    echo $custom_css;

    $custom_js = "
    <script>
    jQuery(document).ready(function($) {
        var slider = $('.slider-aseguradoras');
        var slideCount = slider.find('.slide').length;
        var slideWidth = slider.find('.slide').outerWidth(true);
        var totalWidth = slideCount * slideWidth;

        // Duplicar el contenido para crear efecto continuo
        slider.append(slider.html());
        totalWidth = slider.find('.slide').length * slideWidth;
        slider.css('width', totalWidth + 'px');

        function animateSlider() {
            slider.animate({'margin-left': -totalWidth / 2}, totalWidth * 10, 'linear', function() {
                slider.css('margin-left', '0');
                animateSlider();
            });
        }

        animateSlider();

        $(window).resize(function() {
            slideWidth = slider.find('.slide').outerWidth(true);
            totalWidth = slider.find('.slide').length * slideWidth;
            slider.css('width', totalWidth + 'px');
            slider.stop();
            slider.css('margin-left', '0');
            animateSlider();
        });
    });
    </script>
    ";
    echo $custom_js;
}

add_shortcode('Corredora_Online_Compañias', 'slider_aseguradoras_shortcode');

add_action('wp_footer', 'slider_aseguradoras_assets');


// -- ↕ Terminación Código Shortcode [Corredora_Online_Compañias]
// -- ↕ Iniciación Código Shortcode [Corredora_Online_Login]


function corredora_online_login_shortcode($atts) {

    $border_radius = get_option('co-border-radius', '10px');
    $color_fondo   = get_option('co-color-fondo', '#52868E');
    $color_texto   = get_option('co-color-texto', '#ffffff');

    // 1. Obtenemos el valor dinámico de la opción "corredora_id"
    //    Si no existe, le damos por defecto "15989" (o bien podrías dejarlo vacío o lo que quieras).
    $idcl_value = get_option('corredora_id', '15989');

    $html = '
    <style>
        form {
            font-family: "Poppins", sans-serif;
        }
        input[type="text"] {
            width: 100%;
            padding: 10px 20px;
            font-size: 17px;
            border: 1px solid #ccc;
            border-radius: ' . $border_radius . ';
        }
        .custom-button {
            width: 100%;
            background-color: ' . $color_fondo . ';
            color: ' . $color_texto . ';
            padding: 15px;
            border: none;
            border-radius: ' . $border_radius . ';
            font-size: 16px;
            cursor: pointer;
        }
        .custom-button:hover {
            background-color: #9492f8;
        }
        #messageLoginSuccess {
            display: none;
            font-family: "Poppins", sans-serif;
            font-size: 14px;
            color: #757575C7;
            margin-top: 20px;
        }
    </style>

    <div id="formLoginContainer">
        <form onsubmit="event.preventDefault(); redirectToUrl();">
            <input type="text" id="loginRut" name="loginRut" required placeholder="Ingresa tu RUT" oninput="formatearRUT(this)">
            <br><br>
            <button type="submit" class="custom-button">Continuar</button>
        </form>
    </div>
    <div id="messageLoginSuccess">
        Ingresando desde una nueva pestaña...
    </div>

    <script>
        function redirectToUrl() {
            const rut = document.getElementById("loginRut").value;
            
            // 2. Inyectamos la variable idcl desde PHP:
            const idcl = "' . esc_js($idcl_value) . '";

            // 3. Construimos la URL con el idcl dinámico
            const url = `https://atm.novelty8.com/webhook/auth-login?idcl=${idcl}&t=c&c=cl&port_validate=email&port=customer&roll=${rut}`;

            window.open(url, "_blank");

            document.getElementById("formLoginContainer").style.display = "none";
            document.getElementById("messageLoginSuccess").style.display = "block";

            const listLoginSuccess = document.getElementById("listLoginSuccess");
            const coLoginSuccess   = document.getElementById("coLoginSuccess");

            if (listLoginSuccess) {
                listLoginSuccess.style.display = "none";
            }

            if (coLoginSuccess) {
                coLoginSuccess.style.display = "none";
            }
        }

        function formatearRUT(input) {
            let rut = input.value.replace(/\\./g, "").replace(/-/g, "");
            if (rut.length > 1) {
                rut = rut.replace(/^(\\d{1,2})(\\d{3})(\\d{3})([\\dkK])?$/, "$1.$2.$3-$4");
            }
            input.value = rut;
        }
    </script>';

    return $html;
}
add_shortcode('Corredora_Online_Login', 'corredora_online_login_shortcode');



// -- ↕ Terminación Código Shortcode [Corredora_Online_Login]
// -- ↕ Iniciación Código Shortcode [Corredora_Online_Primas] y  [Corredora_Online_Siniestros]


function corredora_online_aseguradoras_grid($tipo = 'primas') {
    // Definir la meta key según $tipo
    $meta_key = ($tipo === 'siniestros') ? 'enlace_siniestros' : 'enlace_de_pago';

    // Consulta CPT 'aseguradoras'
    $args = array(
        'post_type'      => 'aseguradoras',
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'orderby'        => 'title',
        'order'          => 'ASC'
    );
    $query = new WP_Query($args);

    if ($query->have_posts()) {
        $output = '<div class="aseguradoras-grid">';

        while ($query->have_posts()) {
            $query->the_post();
            $title     = get_the_title();
            $thumbnail = get_the_post_thumbnail_url(get_the_ID(), 'full');
            // Tomamos la meta key definida: 'enlace_de_pago' o 'enlace_siniestros'
            $enlace    = get_post_meta(get_the_ID(), $meta_key, true);

            $output .= '<div class="aseguradoras-grid-item">';

            // Si existe el enlace, abrimos <a>
            if (!empty($enlace)) {
                $output .= '<a href="' . esc_url($enlace) . '" target="_blank" rel="noopener">';
            }

            // Mostrar imagen
            if ($thumbnail) {
                $output .= '<img src="' . esc_url($thumbnail) . '" alt="' . esc_attr($title) . '" loading="lazy" />';
            }

            // Mostrar nombre
            $output .= '<p>' . esc_html($title) . '</p>';

            // Cerramos <a>
            if (!empty($enlace)) {
                $output .= '</a>';
            }

            $output .= '</div>';
        }

        $output .= '</div>';
        wp_reset_postdata();
        return $output;

    } else {
        return '<p>No hay aseguradoras configuradas.</p>';
    }
}

// =============================
// = 2. SHORTCODE DE PRIMAS    =
// =============================
/**
 * [Corredora_Online_Primas]
 * Muestra la misma grilla de aseguradoras, con enlace_de_pago.
 */
function corredora_online_primas_shortcode() {
    return corredora_online_aseguradoras_grid('primas');
}
add_shortcode('Corredora_Online_Primas', 'corredora_online_primas_shortcode');

// =============================
// = 3. SHORTCODE DE SINIESTROS=
// =============================
/**
 * [Corredora_Online_Siniestros]
 * Muestra la grilla de aseguradoras, con enlace_siniestros.
 */
function corredora_online_siniestros_shortcode() {
    return corredora_online_aseguradoras_grid('siniestros');
}
add_shortcode('Corredora_Online_Siniestros', 'corredora_online_siniestros_shortcode');

// =============================
// = 4. ESTILOS DE LA GRILLA   =
// =============================
/**
 * Estilos compartidos para la grilla de aseguradoras.
 */
function corredora_online_aseguradoras_grid_styles() {
    echo "
    <style>
    .aseguradoras-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 50px;
        align-items: start;
    }
    .aseguradoras-grid-item {
        text-align: center;
    }
    .aseguradoras-grid-item img {
        max-width: 60%;
        height: auto;
        display: block;
        margin: 0 auto 7px auto;
        object-fit: contain;
    }
    .aseguradoras-grid-item p {
        margin: 0;
        font-size: 13px;
        font-weight: 300;
        color: #757575;
    }
    .aseguradoras-grid-item a {
        text-decoration: none;
        color: inherit;
        cursor: pointer;
    }
    .aseguradoras-grid-item a:hover {
        text-decoration: none;
    }
    </style>
    ";
}
add_action('wp_head', 'corredora_online_aseguradoras_grid_styles');



// -- ↕ Terminación Código Shortcode [Corredora_Online_Primas] y  [Corredora_Online_Siniestros]
// -- ↕ Iniciación Código Shortcode [Corredora_Online_Valoraciones]

function corredora_online_valoraciones_shortcode($atts) {
    // 1. Definir los parámetros por defecto y mezclar con los pasados en el shortcode
    $args = shortcode_atts(array(
        'columns'    => '3',          // Número de columnas en la cuadrícula
        'limit'      => '-1',         // -1 para mostrar todas las valoraciones
        'star_color' => '#FFD700',    // Color de las estrellas (dorado por defecto)
        'box_bg'     => '#f9f9f9',    // Fondo de cada caja
        'text_color' => '#333333',    // Color del texto dentro de la caja
    ), $atts, 'Corredora_Online_Valoraciones');

    // 2. Convertir los parámetros a valores utilizables
    $columns     = max(1, intval($args['columns']));  // forzamos al menos 1
    $limit       = intval($args['limit']);
    $star_color  = sanitize_hex_color($args['star_color']) ?: '#FFD700';
    $box_bg      = sanitize_hex_color($args['box_bg'])     ?: '#f9f9f9';
    $text_color  = sanitize_hex_color($args['text_color']) ?: '#333333';

    $query_args = array(
        'post_type'      => 'valoraciones',
        'posts_per_page' => $limit,
        'post_status'    => 'publish',
        'orderby'        => 'date',
        'order'          => 'DESC',
        'meta_query'     => array(
            array(
                'key'     => 'promedio',
                'value'   => 3,
                'compare' => '>',
                'type'    => 'NUMERIC',
            ),
        ),
    );
    $valoraciones_query = new WP_Query($query_args);

    // 4. Verificamos si hay valoraciones
    if (!$valoraciones_query->have_posts()) {
        return '<p>No hay valoraciones disponibles.</p>';
    }

    // Obtenemos el ID de la corredora, para el link de "Escribir una valoración"
    $corredora_id = get_option('corredora_id', 'XXXX'); // Ajusta tu valor por defecto si gustas
    // Armamos la URL
    $link_encuesta = 'https://corredoraonline.com/cv/encuesta-de-satisfaccion/?ilc=' . urlencode($corredora_id);

    // 5. Generamos HTML
    ob_start();
    ?>
    <div class="co-valoraciones-grid">
    <?php
    while ($valoraciones_query->have_posts()) {
        $valoraciones_query->the_post();
        // Extraer metadatos
        $nombre      = get_post_meta(get_the_ID(), 'nombre', true);
        $apellido    = get_post_meta(get_the_ID(), 'apellido', true);
        $comentarios = get_post_meta(get_the_ID(), 'comentarios', true);
        $promedio    = get_post_meta(get_the_ID(), 'promedio', true);

        // Convertir 'promedio' a número
        $promedio_num = floatval($promedio);
        if ($promedio_num < 0)  { $promedio_num = 0; }
        if ($promedio_num > 5) { $promedio_num = 5; }
        ?>
        <div class="co-valoracion-item">
            <div class="co-valoracion-nombre">
                <?php echo esc_html($nombre . ' ' . $apellido); ?>
            </div>
            <div class="co-valoracion-estrellas">
                <?php echo corredora_online_estrella_html($promedio_num, $star_color); ?>
            </div>
            <div class="co-valoracion-comentarios">
                <?php echo nl2br(esc_html($comentarios)); ?>
            </div>
        </div>
        <?php
    }
    ?>
    </div>
    <!-- Footer con los 2 enlaces (izq y der) -->
    <div class="co-valoraciones-footer">
        <a class="co-valoraciones-left" 
           href="<?php echo esc_url($link_encuesta); ?>" 
           target="_blank">Escribir una valoración</a>

        <a class="co-valoraciones-right" 
           href="https://corredoraonline.com" 
           target="_blank">Verificado por Corredora Online</a>
    </div>

    <style>
    /* GRID principal con N columnas */
    .co-valoraciones-grid {
        display: grid;
        grid-template-columns: repeat(<?php echo $columns; ?>, 1fr);
        gap: 22px;
    }
    /* Caja de cada valoracion */
    .co-valoracion-item {
        background-color: <?php echo esc_attr($box_bg); ?>;
        border-radius: 8px;
        padding: 22px;
        color: <?php echo esc_attr($text_color); ?>;
        font-family: sans-serif;
        box-shadow: 0 2px 6px rgba(0,0,0,0.08);
    }
    .co-valoracion-nombre {
        font-size: 15px;
        font-weight: 600;
    }
    .co-valoracion-estrellas {
        margin-bottom: 8px;
        font-size: 0; /* para evitar espacios en inline-block */
    }
    .co-valoracion-comentarios {
        font-size: 14px;
        line-height: 1;
        margin-bottom: 8px;
    }
    /* Estrella individual */
    .co-star {
        display: inline-block;
        color: <?php echo esc_attr($star_color); ?>;
        font-size: 15px;
    }
    /* Footer con los 2 enlaces */
    .co-valoraciones-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 10px;
        padding: 0 2px;
    }
    .co-valoraciones-footer a {
        font-size: 11px;
        color: #C2C2C2;
        font-weight: 300;
        text-decoration: none;
        font-family: sans-serif;
    }
    .co-valoraciones-footer a:hover {
        text-decoration: underline;
    }
    </style>
    <?php

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('Corredora_Online_Valoraciones', 'corredora_online_valoraciones_shortcode');


/**
 * Función auxiliar para generar estrellas
 * Recibe un valor 0..5 y colorea '★' o deja en opacidad parcial.
 */
function corredora_online_estrella_html($promedio_num, $star_color = '#FFD700') {
    $rounded = round($promedio_num);  // redondeo (0..5)
    $html = '';
    for ($i=1; $i<=5; $i++) {
        if ($i <= $rounded) {
            // Estrella llena
            $html .= '<span class="co-star">★</span>';
        } else {
            // Estrella vacía
            $html .= '<span class="co-star" style="opacity:0.3">★</span>';
        }
    }
    return $html;
}

?>

<?php

function add_custom_menu_page() {
    $plugin_dir_url = plugins_url('/', __FILE__);
    $icon_url = $plugin_dir_url . 'logotipo.png';

    $page_title = 'Corredora Online';
    $menu_title = 'Corredora Online';
    $capability = 'manage_options';
    $menu_slug = 'corredora-online-settings';
    $function = 'corredora_online_settings_page';
    $position = 100;
    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

add_action('admin_menu', 'add_custom_menu_page');


function corredora_online_settings_page() {
    ?>
    <style>
        .custom-page {
            padding-left: 20px;
            padding-right: 20px;
        }
        
        /* Estilos personalizados para el botón */
        .custom-button {
            font-family: 'Nunito', sans-serif !important;
            color: #090909 !important;
            background-color: #00DFC0 !important;
            border-radius: 50px !important;
            border: none !important;
            width: 100%;
        }
    </style>
    <div class="wrap custom-page">
        <h2 style="font-family: 'Nunito', sans-serif; margin-top: 20px; margin-bottom: 0px;">Corredora Online</h2>
        <p style="font-family: 'Nunito', sans-serif; margin-top: 5px; margin-left: 1px; margin-bottom: 30px;">Nuestro plugin de Wordpress permite generar una conexión directa con el sistema, permitiendo editar la información de su web directo desde Corredora Online, además le entrega otras funcionalidades valiosas, encuentre mayor información en Configuración > Integraciones > Wordpress.</p>
        
        
        <?php
        // Verificar si el formulario se ha enviado y mostrar un mensaje de confirmación
        settings_errors('corredora-settings');
        ?>
        <form method="post" action="" style="background-color: #ffffff; border-radius: 12px; padding: 20px;">
            <div class="corredora-form-container" style="font-family: 'Nunito', sans-serif; padding-top: 20px; padding-bottom: 30px; padding-left: 18px; padding-right: 20px;">
                <label for="corredora_id" style="display: block; font-size: 15px; margin-bottom: 10px;">ID Corredora</label>
                <input type="text" name="corredora_id" id="corredora_id" value="<?php echo esc_attr(get_option('corredora_id')); ?>" style="margin-bottom: 20px; border-radius: 12px; padding: 6px 12px; color: #313131; font-size: 15px; border: 1px solid #BABABA; width: 100%;" required />
                
                <label for="api_key" style="display: block; font-size: 15px; margin-bottom: 10px;">API KEY</label>
                <?php
                // Obtener la API KEY almacenada
                $stored_api_key = esc_attr(get_option('api_key'));
                
                // Mostrar solo los últimos 4 caracteres y ocultar la longitud real
                $masked_api_key = str_repeat('*', max(0, strlen($stored_api_key) - 4)) . substr($stored_api_key, -4);
                ?>
                <input type="text" name="api_key" id="api_key" value="<?php echo $masked_api_key; ?>" style="margin-bottom: 20px; border-radius: 12px; padding: 6px 12px; color: #313131; font-size: 15px; border: 1px solid #BABABA; width: 100%;" required />
                
                <br>
                <input type="submit" name="submit" class="button-primary custom-button" value="Actualizar" style="padding-top: 7px; padding-bottom: 7px; font-size: 15px; font-weight: 400;" />
            </div>
        </form>
    </div>
    <?php
}

function save_corredora_settings() {
    if (isset($_POST['submit'])) {
        $corredora_id = sanitize_text_field($_POST['corredora_id']);
        $api_key_input = sanitize_text_field($_POST['api_key']); // La API KEY con asteriscos
        $api_key_stored = get_option('api_key'); // La API KEY almacenada sin asteriscos

        // Si el campo de entrada contiene asteriscos, utiliza la API KEY almacenada sin asteriscos
        $api_key = (strpos($api_key_input, '*') === false) ? $api_key_input : $api_key_stored;

        if (!empty($corredora_id) && !empty($api_key)) {
            $site_url = get_site_url();
            
            // Realizar la solicitud HTTP
            $response = wp_remote_post('https://atm.novelty8.com/webhook/api/corredora-online/validate-wp', array(
                'method'    => 'POST',
                'body'      => array(
                    'idc'       => $corredora_id,
                    'api-key'   => $api_key,
                    'url'       => $site_url
                ),
                'timeout'   => 45,
                'headers'   => array(
                    'Content-Type' => 'application/x-www-form-urlencoded'
                )
            ));

            if (is_wp_error($response)) {
                // Error en la solicitud HTTP
                $message = 'Ocurrió un error al validar la API KEY. Por favor, inténtelo de nuevo.';
                $class = 'error';
            } else {
                $status_code = wp_remote_retrieve_response_code($response);
                if ($status_code == 200) {
                    // Guardar las opciones si la validación es exitosa
                    update_option('corredora_id', $corredora_id);
                    update_option('api_key', $api_key);
                    
                    // Agregar un mensaje de confirmación
                    $message = 'La información ha sido guardada con éxito.';
                    $class = 'updated';
                } else {
                    // Agregar un mensaje de error
                    $message = 'La validación de la API KEY ha fallado. Por favor, verifique los datos ingresados.';
                    $class = 'error';
                }
            }
        } else {
            // Agregar un mensaje de error
            $message = 'Ocurrió un error, inténtelo de nuevo o contacte a soporte.';
            $class = 'error';
        }
        
        // Mostrar el mensaje solo una vez
        add_settings_error('corredora-settings', 'corredora-settings', $message, $class);
    }
}

add_action('admin_init', 'save_corredora_settings');


function corredora_online_shortcode($atts) {
    // Extraer el atributo 'mostrar' del shortcode
    $atts = shortcode_atts(array(
        'mostrar' => ''
    ), $atts, 'Corredora_Online');

    $mostrar = $atts['mostrar'];

    // Obtener la información correspondiente según el valor del atributo 'mostrar'
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

// Registrar el shortcode
add_shortcode('Corredora_Online', 'corredora_online_shortcode');


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
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'aseguradora' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'thumbnail' ),
        'show_in_rest'       => false,
        'rest_base'          => 'aseguradoras',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    register_post_type( 'aseguradoras', $args );
}

add_action( 'init', 'registrar_aseguradoras_custom_post_type' );


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
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => array( 'slug' => 'valoracion' ),
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'thumbnail' ),
        'show_in_rest'       => true,
        'rest_base'          => 'valoraciones',
        'rest_controller_class' => 'WP_REST_Posts_Controller',
    );
    register_post_type( 'valoraciones', $args );
}

add_action( 'init', 'registrar_valoraciones_custom_post_type' );


function ocultar_botones_edicion_personalizados($actions, $post) {
    if ($post->post_type == 'valoraciones' || $post->post_type == 'aseguradoras') {
        // Eliminar botones de edición y edición rápida
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




// Función para registrar el endpoint como webhook o pulse
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


function verificar_autenticacion_api( $request ) {
    $api_key = get_option( 'api_key' );
    $api_key_received = $request->get_header( 'Authorization' );

    if ( $api_key === $api_key_received ) {
        return true;
    } else {
        return new WP_Error( 'rest_forbidden', esc_html__( 'Acceso no autorizado.', 'text-domain' ), array( 'status' => 401 ) );
    }
}




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

    if (!is_array($data) || !isset($data[0]['estado']) || $data[0]['estado'] !== 'exitoso' || !isset($data[0]['aseguradoras'])) {
        return new WP_REST_Response('Error: Respuesta no válida de la API.', 500);
    }

    $aseguradoras_ids = array_map(function($aseguradora) {
        return $aseguradora['id'];
    }, $data[0]['aseguradoras']);

    $existing_posts = get_posts(array(
        'post_type' => 'aseguradoras',
        'numberposts' => -1,
        'post_status' => 'any',
    ));

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

    // Crear o actualizar posts con la información recibida de la API
    foreach ($data[0]['aseguradoras'] as $aseguradora) {
        $id_aseguradora = $aseguradora['id'];

        // Verificar si ya existe un post con el mismo id_aseguradora
        $existing_post = get_posts(array(
            'post_type' => 'aseguradoras',
            'meta_key' => 'id_aseguradora',
            'meta_value' => $id_aseguradora,
            'posts_per_page' => 1,
        ));

        if ($existing_post) {
            $post_id = $existing_post[0]->ID;
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => wp_strip_all_tags($aseguradora['nombre']),
            ));
        } else {
            $post_id = wp_insert_post(array(
                'post_title' => wp_strip_all_tags($aseguradora['nombre']),
                'post_type' => 'aseguradoras',
                'post_status' => 'publish'
            ));

            if (!is_wp_error($post_id)) {
                update_post_meta($post_id, 'id_aseguradora', $id_aseguradora);
            }
        }

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
                    'guid' => $wp_upload_dir['url'] . '/' . $file_name,
                    'post_mime_type' => $file_type['type'],
                    'post_title' => $attachment_title,
                    'post_content' => '',
                    'post_status' => 'inherit'
                );

                $attach_id = wp_insert_attachment($attachment, $file_path, $post_id);

                if (!is_wp_error($attach_id)) {
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attach_data = wp_generate_attachment_metadata($attach_id, $file_path);
                    wp_update_attachment_metadata($attach_id, $attach_data);
                    set_post_thumbnail($post_id, $attach_id);
                } else {
                    if (!$existing_post) {
                        wp_delete_post($post_id, true);
                    }
                }
            } else {
                if (!$existing_post) {
                    wp_delete_post($post_id, true);
                }
            }
        }
    }

    return new WP_REST_Response('OK', 200);
}




// Función para procesar la petición y actualizar la información de contacto
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






//Shortcode COTIZADOR ONLINE


// Función para mostrar el formulario del cotizador
function corredora_online_cotizador($atts)
{
	
    // Extraer los atributos del shortcode
    $atts = shortcode_atts(
        array(
            'mostrar' => '',
        ),
        $atts,
        'Corredora_Online'
    );

    // Verificar el atributo 'mostrar'
    if ($atts['mostrar'] === 'cotizador') {
    
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
                margin-bottom: 6px;
                font-size: 16px;
                font-weight: 600;
            }

            .cotizador-form-container input[type="text"],
            .cotizador-form-container input[type="email"],
            .cotizador-form-container input[type="password"] {
                width: calc(100% - 22px);
                padding: 10px;
                margin-bottom: 8px;
                border: 1px solid #ccc;
                border-radius: 8px;
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
    			margin-right: 0; /* Eliminar el margen derecho de la última tab */
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
				border-radius: 8px;
				box-sizing: border-box;
				font-size: 15px;
			}
        </style>
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
                    <div class="row">
    <p class="half">
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
    <p class="half">
    <label for="comuna">Comuna</label>
    <select id="comuna" name="comuna" required>
    </select>
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
    var patenteInput = document.getElementById('patente');
    var errorMessage = document.getElementById('error-message');
    var rutInput = document.getElementById('rut');
    var rutErrorMessage = document.getElementById('rut-error-message');
    var nombreField = document.getElementById('nombre-field');
    var apellidoField = document.getElementById('apellido-field');
    var timeout = null;
    var isPatenteValid = false;
    var isRUTValid = false;
    var comunasPorRegion = <?php echo json_encode($comunasPorRegion); ?>;

    function validarRUT(rut) {
        rut = rut.replace(/[.-]/g, '');
        const dv = rut.slice(-1);
        const rutCuerpo = rut.slice(0, -1);
        let suma = 0;
        let multiplicador = 2;
        
        for (let i = rutCuerpo.length - 1; i >= 0; i--) {
            suma += parseInt(rutCuerpo.charAt(i)) * multiplicador;
            multiplicador = multiplicador === 7 ? 2 : multiplicador + 1;
        }
        
        const dvCalculado = 11 - (suma % 11);
        const dvEsperado = dvCalculado === 11 ? '0' : dvCalculado === 10 ? 'K' : dvCalculado.toString();
        
        return dv.toUpperCase() === dvEsperado;
    }

    rutInput.addEventListener('input', function () {
        var rut = this.value.trim().replace(/\./g, '').replace('-', '');
        if (rut.length > 1) {
            rut = rut.replace(/^(\d{1,9})(\d{3})(\d{3})(\w{1})$/, '$1.$2.$3-$4');
        }
        this.value = rut;
    });

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
    
    patenteInput.addEventListener('input', function () {
        clearTimeout(timeout);

        if (this.value.replace(/-/g, '').length >= 4) {
            timeout = setTimeout(function () {
                var patente = patenteInput.value;
                if (patente) {
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
                            document.body.classList.remove('wait-cursor');

                            if (data.estado === "exitoso") {
                                document.getElementById('marca').value = data.data.marca;
                                document.getElementById('modelo').value = data.data.modelo;
                                document.getElementById('año').value = data.data.año;
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
                            document.body.classList.remove('wait-cursor');
                            console.error('Error al obtener los datos del vehículo:', error);
                        });
                }
            }, 1000);
        }
    });

    rutInput.addEventListener('input', function () {
        clearTimeout(timeout);

        if (this.value.replace(/[\.\-]/g, '').length >= 8) {
            timeout = setTimeout(function () {
                var rut = rutInput.value.replace(/[\.\-]/g, '');
                if (rut && validarRUT(rut)) {
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
                            document.body.classList.remove('wait-cursor');

                            if (data.estado === "exitoso") {
                                if (data.data.nombre === "**" || !data.data.nombre) {
                                    nombreField.style.display = 'block';
                                    apellidoField.style.display = 'block';
                                    rutInput.classList.remove('invalid-input');
                                } else {
                                    isRUTValid = true;
                                    nombreField.style.display = 'none';
                                    apellidoField.style.display = 'none';
                                    rutErrorMessage.style.display = 'none';
                                    rutInput.classList.remove('invalid-input');
                                }
                            } else {
                                nombreField.style.display = 'block';
                                apellidoField.style.display = 'block';
                                rutInput.classList.remove('invalid-input');
                            }
                            updateSubmitButton();
                        })
                        .catch(error => {
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
            }, 1000);
        } else {
            rutErrorMessage.style.display = 'none';
            isRUTValid = false;
            updateSubmitButton();
        }
    });

    function updateSubmitButton() {
        var submitButton = document.getElementById('submit-button');
        if (isPatenteValid && isRUTValid) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    document.querySelector('.next-step').addEventListener('click', function () {
        var valid = true;
        document.querySelectorAll('.step[data-step="1"] [required]').forEach(function (input) {
            if (!input.value) {
                valid = false;
                input.focus();
            }
        });

        if (valid && isPatenteValid) {
            document.querySelector('.step[data-step="1"]').classList.remove('active');
            document.querySelector('.step[data-step="2"]').classList.add('active');

            document.querySelector('.step-title[data-step="1"]').classList.remove('active');
            document.querySelector('.step-title[data-step="2"]').classList.add('active');
        }
    });

    document.querySelectorAll('.step-title').forEach(function (title) {
        title.addEventListener('click', function () {
            var step = parseInt(this.getAttribute('data-step'));
            if (step <= currentStep) {
                showStep(step);
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

    function enableSubmitButton() {
        var submitButton = document.getElementById('submit-button');
        if (isPatenteValid && isRUTValid) {
            submitButton.disabled = false;
        } else {
            submitButton.disabled = true;
        }
    }

    document.querySelector('.next-step').addEventListener('click', function () {
        if (isPatenteValid) {
            document.querySelector('.step[data-step="1"]').classList.remove('active');
            document.querySelector('.step[data-step="2"]').classList.add('active');
            document.querySelector('.step-title[data-step="1"]').classList.remove('active');
            document.querySelector('.step-title[data-step="2"]').classList.add('active');
        } else {
            patenteInput.focus();
            patenteInput.classList.add('invalid-input');
            errorMessage.style.display = 'block';
        }
    });

    document.querySelector('.step-title[data-step="1"]').addEventListener('click', function () {
        document.querySelector('.step[data-step="1"]').classList.add('active');
        document.querySelector('.step[data-step="2"]').classList.remove('active');
        document.querySelector('.step-title[data-step="1"]').classList.add('active');
        document.querySelector('.step-title[data-step="2"]').classList.remove('active');
    });
});
        </script>
        <?php
        return ob_get_clean();
    } else {
        return '<p>No se ha encontrado la opción solicitada.</p>';
    }
}
add_shortcode('Corredora_Online', 'corredora_online_cotizador');



?>

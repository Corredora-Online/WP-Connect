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
    </style>
    <div class="wrap custom-page">
        <h2 style="font-family: 'Nunito', sans-serif; margin-top: 20px; margin-bottom: 20px;">Corredora Online</h2>
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
    <style>
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

    // Obtener el correo y el número de contacto de la respuesta
    $correo_contacto = isset($data['data']['correo']) ? $data['data']['correo'] : '';
    $numero_contacto = isset($data['data']['celular']) ? $data['data']['celular'] : '';

    // Actualizar los valores de correo y número de contacto en las opciones
    update_option('co-correo-contacto', $correo_contacto);
    update_option('co-numero-contacto', $numero_contacto);

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


?>

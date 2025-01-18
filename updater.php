<?php
if (!class_exists('Corredora_Online_Updater')) {
    class Corredora_Online_Updater {
        private $file;
        private $plugin;
        private $basename;
        private $active;
        private $error_message;

        public function __construct($file) {
            $this->file = $file;
            $this->plugin = plugin_basename($file);
            $this->basename = str_replace('/', '-', $this->plugin);
            $this->active = $this->is_plugin_active($this->plugin);
            $this->error_message = '';
        }

        public function initialize() {
            add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
            add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
            add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
            add_action('admin_notices', array($this, 'admin_notices'));
        }

        private function is_plugin_active($plugin) {
            return in_array($plugin, (array) get_option('active_plugins', array()));
        }

        private function get_repository_info() {
            $request_uri = 'https://api.github.com/repos/Corredora-Online/WP-Connect/releases';
            $response = wp_remote_get($request_uri);

            if (is_wp_error($response)) {
                $this->error_message = 'Error en la solicitud a GitHub: ' . $response->get_error_message();
                return false;
            }

            $response_body = wp_remote_retrieve_body($response);
            $response_array = json_decode($response_body, true);

            if (!is_array($response_array) || empty($response_array)) {
                $this->error_message = 'No se pudo decodificar la respuesta JSON de GitHub o está vacía.';
                return false;
            }

            return $response_array;
        }

        public function modify_transient($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $repository = $this->get_repository_info();
            if ($repository === false || !is_array($repository) || empty($repository[0]['tag_name'])) {
                return $transient;
            }

            $version = $repository[0]['tag_name'];
            $current_version = $transient->checked[$this->plugin];
            $new_version = version_compare($version, $current_version, 'gt');

            if ($new_version) {
                $package = $repository[0]['zipball_url'];

                // Agregar información de la nueva versión
                $obj = new stdClass();
                $obj->slug = $this->basename;
                $obj->new_version = $version;
                $obj->url = $this->plugin;
                $obj->package = $package;
                $transient->response[$this->plugin] = $obj;
            }

            return $transient;
        }

        public function plugin_popup($result, $action, $args) {
            if (!empty($args->slug) && $args->slug == $this->basename) {
                $repository = $this->get_repository_info();
                if ($repository !== false) {
                    $version = $repository[0]['tag_name'];

                    // Agregar información del plugin
                    $result = new stdClass();
                    $result->name = $this->plugin;
                    $result->slug = $this->basename;
                    $result->version = $version;
                    $result->author = '<a href="https://corredoraonline.com/">Corredora Online</a>';
                    $result->homepage = $this->plugin;
                    $result->download_link = $repository[0]['zipball_url'];
                    $result->sections = array(
                        'description' => 'Un plugin personalizado para múltiples sitios web.',
                    );
                }
            }

            return $result;
        }

        public function after_install($response, $hook_extra, $result) {
            global $wp_filesystem;
            $install_directory = plugin_dir_path($this->file);
            $wp_filesystem->move($result['destination'], $install_directory);
            $result['destination'] = $install_directory;

            if ($this->active) {
                activate_plugin($this->plugin);
            }

            return $result;
        }

        public function admin_notices() {
            if (!empty($this->error_message)) {
                echo '<div class="notice notice-error"><p>' . $this->error_message . '</p></div>';
            }
        }
    }
}

/**
 * -------------------------------------------------------
 * SNIPPET PARA AGREGAR EL ENLACE "Buscar actualizaciones"
 * Y FORZAR LA BÚSQUEDA DE ACTUALIZACIONES DE INMEDIATO.
 * -------------------------------------------------------
 */

// Recomendable: detectar el plugin principal.
$co_main_plugin_file = plugin_basename(dirname(__FILE__) . '/corredora-online.php');

/**
 * Agregar enlace "Buscar actualizaciones" en la fila del plugin
 * (en la página de Plugins).
 */
add_filter('plugin_row_meta', 'co_add_force_update_link', 10, 2);
function co_add_force_update_link($links, $file) {
    // Compara con tu plugin principal
    global $co_main_plugin_file;

    if ($file === $co_main_plugin_file) {
        // Construimos la URL con nonce y parámetro
        $force_update_url = wp_nonce_url(
            add_query_arg(array('co_force_update' => '1')),
            'co_force_update_nonce'
        );

        // Añadir el enlace al array $links
        $links[] = sprintf(
            '<a href="%s" style="color:#2271b1;">%s</a>',
            esc_url($force_update_url),
            __('Buscar actualizaciones', 'text-domain')
        );
    }
    return $links;
}

/**
 * Capturar el click en "?co_force_update=1"
 * Forzar la eliminación del transient y recargar la página.
 */
add_action('admin_init', 'co_maybe_force_update');
function co_maybe_force_update() {
    if (
        isset($_GET['co_force_update']) &&
        $_GET['co_force_update'] == '1' &&
        check_admin_referer('co_force_update_nonce')
    ) {
        // Borra el transient de actualizaciones de plugins
        delete_site_transient('update_plugins');

        // Redirecciona de vuelta a la página de Plugins
        wp_safe_redirect(admin_url('plugins.php?co_forced=1'));
        exit;
    }
}

/**
 * Mostrar un aviso de éxito tras forzar la búsqueda de updates
 */
add_action('admin_notices', 'co_show_forced_update_notice');
function co_show_forced_update_notice() {
    if (isset($_GET['co_forced']) && $_GET['co_forced'] == '1') {
        echo '<div class="notice notice-success is-dismissible"><p>';
        esc_html_e('Se ha forzado la búsqueda de actualizaciones del plugin Corredora Online.', 'text-domain');
        echo '</p></div>';
    }
}

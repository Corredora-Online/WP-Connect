<?php
/*
Plugin Name: Corredora Online
Plugin URI: https://github.com/Corredora-Online/WP-Connect
Description: Un plugin personalizado para múltiples sitios web.
Version: 1.8
Author: Corredora Online
Author URI: https://corredoraonline.com/
License: GPL2
GitHub Plugin URI: https://github.com/Corredora-Online/WP-Connect
GitHub Branch: main
*/

if (!class_exists('Corredora_Online_Updater')) {
    class Corredora_Online_Updater {
        private $file;
        private $plugin;
        private $basename;
        private $active;

        public function __construct($file) {
            $this->file = $file;
            $this->plugin = plugin_basename($file);
            $this->basename = str_replace('/', '-', $this->plugin);
            $this->active = $this->is_plugin_active($this->plugin);
        }

        public function initialize() {
            add_filter('pre_set_site_transient_update_plugins', array($this, 'modify_transient'), 10, 1);
            add_filter('plugins_api', array($this, 'plugin_popup'), 10, 3);
            add_filter('upgrader_post_install', array($this, 'after_install'), 10, 3);
        }

        private function is_plugin_active($plugin) {
            return in_array($plugin, (array) get_option('active_plugins', array()));
        }

        private function get_repository_info() {
            $request_uri = 'https://api.github.com/repos/Corredora-Online/WP-Connect/releases';
            $response = wp_remote_get($request_uri);

            if (is_wp_error($response)) {
                return false; // Error en la solicitud
            }

            $response = json_decode(wp_remote_retrieve_body($response), true);

            if (isset($response['message'])) {
                return false; // Error en la respuesta de la API
            }

            return $response;
        }

        public function modify_transient($transient) {
            if (empty($transient->checked)) {
                return $transient;
            }

            $repository = $this->get_repository_info();
            if ($repository === false) {
                return $transient;
            }

            $version = $repository[0]['tag_name'];
            $new_version = version_compare($version, $transient->checked[$this->plugin], 'gt');

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
    }

    $updater = new Corredora_Online_Updater(__FILE__);
    $updater->initialize();
}
?>

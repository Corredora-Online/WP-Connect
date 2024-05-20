<?php
/*
Plugin Name: Corredora Online
Plugin URI: https://github.com/Corredora-Online/WP-Connect
Description: Este plugin obtiene la información del sistema Corredora Online y procesa para ser mostrada en la página web.
Version: 1.0.0
Author: Corredora Online
Author URI: https://corredoraonline.com/
License: GPL2
GitHub Plugin URI: https://github.com/Corredora-Online/WP-Connect
GitHub Branch: main
*/

// Incluir el archivo de actualizaciones
require_once plugin_dir_path(__FILE__) . 'updater.php';

// Incluir el archivo de funcionalidades
require_once plugin_dir_path(__FILE__) . 'functions.php';

// Inicializar el actualizador
if (class_exists('Corredora_Online_Updater')) {
    $updater = new Corredora_Online_Updater(__FILE__);
    $updater->initialize();
}
?>

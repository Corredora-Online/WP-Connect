<?php
// Función para mostrar un mensaje en el backoffice de WordPress
function corredora_online_test_message() {
    echo '<div class="notice notice-success is-dismissible">';
    echo '<p>Mensaje de prueba: ¡El plugin Corredora Online está funcionando correctamente!</p>';
    echo '</div>';
}

// Hook para mostrar el mensaje en el admin
add_action('admin_notices', 'corredora_online_test_message');
?>

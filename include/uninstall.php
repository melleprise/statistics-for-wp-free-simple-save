<?php

// include/uninstall.php

// Uninstall-Hook, der beim Deinstallieren des Plugins ausgeführt wird
register_uninstall_hook(__FILE__, 'wpfse_uninstall_cleanup');

function wpfse_uninstall_cleanup() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpfse_visits';

    // Löscht die Datenbanktabelle
    $sql = "DROP TABLE IF EXISTS $table_name;";
    $wpdb->query($sql);

    // Löscht alle Optionen, die durch das Plugin gesetzt wurden
    delete_option('wpsfse_menu_visited');
    delete_option('wpsfse_redirect_done');
}
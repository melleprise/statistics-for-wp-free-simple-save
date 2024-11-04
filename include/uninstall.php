<?php

// include/uninstall.php

// Uninstall-Hook, der beim Deinstallieren des Plugins ausgeführt wird
register_uninstall_hook(__FILE__, 'wpfse_uninstall_cleanup');

function wpfse_uninstall_cleanup() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpfse_visits';

    // Löscht die Datenbanktabelle sicher und direkt
    $wpdb->query("DROP TABLE IF EXISTS `" . esc_sql($table_name) . "`");

    // Löscht alle Optionen, die durch das Plugin gesetzt wurden
    delete_option('wpsfse_menu_visited');
    delete_option('wpsfse_redirect_done');
}
<?php

// include/export.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Export-Logik
function wpsfse_export_data() {
    global $wpdb;

    // Sicherheitsüberprüfung
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'Unauthorized access.', 'wp-statistics-free-simple-easy' ) );
    }

    // Header für die CSV-Datei setzen
    header( 'Content-Type: text/csv' );
    header( 'Content-Disposition: attachment; filename="wpsfse_data_export.csv"' );

    // Datenbanktabelle abfragen
    $table_name = $wpdb->prefix . 'wp_statistics_free_metadata';
    $results    = $wpdb->get_results( "SELECT * FROM {$table_name}", ARRAY_A );

    // Datei öffnen
    $output = fopen( 'php://output', 'w' );

    // Kopfzeilen setzen (Spaltennamen)
    if ( ! empty( $results ) ) {
        fputcsv( $output, array_keys( $results[0] ) );
    }

    // Daten schreiben
    foreach ( $results as $row ) {
        fputcsv( $output, $row );
    }

    fclose( $output );
    exit;
}
add_action( 'admin_post_wpsfse_export', 'wpsfse_export_data' );

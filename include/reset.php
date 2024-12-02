<?php

// include/reset.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Funktion zum Zurücksetzen der Datenbank
function wpsfse_reset_database() {
    error_log( 'wpsfse_reset_database() function called.' );

    global $wpdb;

    if ( ! current_user_can( 'manage_options' ) ) {
        error_log( 'User does not have the required permissions to reset the database.' );
        wp_die( esc_html__( 'Unauthorized access.', 'wp-statistics-free-simple-easy' ) );
    }

    if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( $_POST['_wpnonce'], 'wpsfse_reset_nonce' ) ) {
        error_log( 'Nonce verification failed.' );
        wp_die( esc_html__( 'Nonce verification failed.', 'wp-statistics-free-simple-easy' ) );
    }

    $table_name = $wpdb->prefix . 'wp_statistics_free_metadata';

    error_log( 'Attempting to drop table: ' . $table_name );

    $result = $wpdb->query( "DROP TABLE IF EXISTS $table_name" );

    if ( $result === false ) {
        error_log( 'Failed to drop table: ' . $table_name );
    } else {
        error_log( 'Table dropped successfully: ' . $table_name );
    }

    require_once plugin_dir_path( __FILE__ ) . 'database.php';
    wpfse_create_db_table();

    error_log( 'Table recreated successfully.' );

    // Weiterleitung auf die Dashboard-Seite
    wp_redirect( admin_url( 'admin.php?page=wpsfse-dashboard' ) );
    exit;
}

// Hook für die Reset-Funktion
add_action( 'admin_post_wpsfse_reset_database', 'wpsfse_reset_database' );
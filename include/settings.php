<?php

// include/settings.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '<div class="wrap">';
echo '<h1>' . esc_html__( 'Settings', 'wp-statistics-free-simple-easy' ) . '</h1>';
echo '<p>' . esc_html__( 'Manage your settings, export data, or reset all data.', 'wp-statistics-free-simple-easy' ) . '</p>';

// Toggle-Button für das Anzeigen von Admin-Einträgen
$show_admin_results = get_option('wpsfse_show_admin_results', 'yes'); // Standardwert ist 'yes'

echo '<form method="post" action="' . esc_url( admin_url('admin-post.php') ) . '" style="margin-bottom: 20px;">';
echo '<input type="hidden" name="action" value="wpsfse_toggle_admin_results">';
echo wp_nonce_field('wpsfse_toggle_admin_results_nonce');

$toggle_text = ($show_admin_results === 'yes') ? 'Hide Admin Results' : 'Show Admin Results';
echo '<button type="submit" class="button button-primary">' . esc_html__($toggle_text, 'wp-statistics-free-simple-easy') . '</button>';
echo '</form>';

// Export-Button
echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-bottom: 20px;">';
echo '<input type="hidden" name="action" value="wpsfse_export">';
echo '<button type="submit" class="button button-primary">' . esc_html__( 'Export Data', 'wp-statistics-free-simple-easy' ) . '</button>';
echo '</form>';

// Reset and Delete All Data Button
echo '<form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" style="margin-bottom: 20px;">';
echo wp_nonce_field( 'wpsfse_reset_nonce' );
echo '<input type="hidden" name="action" value="wpsfse_reset_database">';
echo '<button type="submit" class="button button-secondary" onclick="return confirm(\'' . esc_js( __( 'Are you sure you want to reset and delete all data? This action cannot be undone.', 'wp-statistics-free-simple-easy' ) ) . '\');">' . esc_html__( 'Reset and Delete All Data', 'wp-statistics-free-simple-easy' ) . '</button>';
echo '</form>';
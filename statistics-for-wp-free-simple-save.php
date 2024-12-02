<?php
/*
Plugin Name: Statistics for WP - Free, Simple & Save
Description: No paywalls, pop-ups, cookies, or heavy database load. Provides essential stats without slowing down your site or collecting personal data. Simple, effective, and privacy-friendly. Upon uninstallation, all data is removed, leaving your WordPress as clean as before.
Version: 1.0.1
Author: MELLEPRISE
Author URI: https://melleprise.de
GitHub Plugin URI: https://github.com/melleprise/statistics-for-wp
Text Domain: statistics-for-wp-free-simple-save
License: GPL-2.0+
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Block direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'include/database.php');
require_once(plugin_dir_path(__FILE__) . 'include/metadata.php');
require_once(plugin_dir_path(__FILE__) . 'include/uninstall.php');
require_once(plugin_dir_path(__FILE__) . 'include/export.php');
require_once(plugin_dir_path(__FILE__) . 'include/reset.php');

// Menüeintrag für "WP Statistics Free" hinzufügen
function wpsfse_menu() {
    $icon = 'dashicons-chart-pie';
    
    // Hauptmenüeintrag für das Dashboard erstellen
    add_menu_page(
        'Statistics',             // Seiten-Titel
        'WP Statistics',          // Menü-Name
        'manage_options',         // Berechtigungen
        'wpsfse-dashboard',       // Slug der Seite
        'wpsfse_load_dashboard',  // Funktion zum Laden des Inhalts
        $icon,                    // Icon für das Menü (Dashicons)
        2                         // Position im Menü
    );

    // Untermenü für Settings hinzufügen
    add_submenu_page(
        'wpsfse-dashboard',       // Elternmenü (Slug des Hauptmenüs)
        'Settings',               // Seiten-Titel
        'Settings',               // Menü-Name
        'manage_options',         // Berechtigungen
        'wpsfse-settings',        // Slug der Unterseite
        'wpsfse_load_settings'    // Funktion zum Laden des Inhalts
    );
}
add_action('admin_menu', 'wpsfse_menu');

// Funktion zum Laden der Settings-Seite
function wpsfse_load_settings() {
    include plugin_dir_path(__FILE__) . 'include/settings.php';
}

// CSS für Hintergrundfarbe des Menüs hinzufügen
function wpsfse_menu_highlight_css() {
    if (!get_option('wpsfse_menu_visited')) {
        echo '<style>
            #toplevel_page_wpsfse-dashboard .wp-menu-name {
                background-color: #198754 !important;
            }
        </style>';
    }
}
add_action('admin_head', 'wpsfse_menu_highlight_css');

// Weiterleitung zum Dashboard nach der Plugin-Aktivierung
function wpsfse_redirect_to_dashboard_on_activation() {
    if (!get_option('wpsfse_redirect_done')) {
        update_option('wpsfse_redirect_done', true);
        wp_safe_redirect(admin_url('admin.php?page=wpsfse-dashboard'));
        exit;
    }
}
add_action('admin_notices', 'wpsfse_redirect_to_dashboard_on_activation');

// Setzt die Menü- und Weiterleitungsoption bei jeder Plugin-Aktivierung zurück
function wpsfse_reset_menu_visited() {
    update_option('wpsfse_menu_visited', false);
    update_option('wpsfse_redirect_done', false);
}

function wpsfse_activation() {
    // Bestehende Aufgaben
    wpsfse_reset_menu_visited();
    wpfse_create_db_table();

    // Standardwert für die Einstellung setzen
    if (get_option('wpsfse_show_admin_results') === false) {
        add_option('wpsfse_show_admin_results', 'yes');
    }
}
register_activation_hook(__FILE__, 'wpsfse_activation');

// Funktion zum Laden des Dashboards
function wpsfse_load_dashboard() {
    update_option('wpsfse_menu_visited', true); // Menü als besucht markieren
    include plugin_dir_path(__FILE__) . 'include/dashboard.php';
}


// Enqueue das Skript nur auf der Plugin-Seite
function wpsfse_delete_warning_script($hook) {
    if ($hook != 'plugins.php') {
        return;
    }
    
    wp_register_script('wpsfse_delete_warning_js', plugins_url('include/delete-warning.js', __FILE__), array('jquery'), time(), true);

    wp_enqueue_script('wpsfse_delete_warning_js');

}
add_action('admin_enqueue_scripts', 'wpsfse_delete_warning_script');

// Aktion-Links unter dem Plugin-Namen hinzufügen
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wpsfse_add_action_links' );

function wpsfse_add_action_links( $links ) {
    $new_links = array(
        '<a href="' . esc_url( admin_url( 'admin.php?page=wpsfse-dashboard' ) ) . '">' . esc_html__( 'Dashboard', 'wp-statistics-free-simple-easy' ) . '</a>',
    );
    return array_merge( $new_links, $links );
}

// Metadaten-Links neben der Versions- und Autorenangabe hinzufügen
add_filter( 'plugin_row_meta', 'wpsfse_add_plugin_meta_links', 10, 2 );

function wpsfse_add_plugin_meta_links( $links, $file ) {
    // Überprüfen, ob es sich um dieses Plugin handelt
    if ( plugin_basename( __FILE__ ) === $file ) {
        // Neue Links erstellen
        $new_links = array(
            '<a href="https://melleprise.de/kontakt/" target="_blank">' . esc_html__( 'Help', 'wp-statistics-free-simple-easy' ) . '</a>',
            '<a href="https://melleprise.de/donate" target="_blank">' . esc_html__( 'Donate', 'wp-statistics-free-simple-easy' ) . '</a>',
        );

        // Die neuen Links zu den bestehenden Links hinzufügen
        $links = array_merge( $links, $new_links );
    }
    return $links;
}

// Setze ein Cookie, wenn sich ein Administrator im Backend einloggt
function wpsfse_set_admin_cookie() {
    // Prüfe, ob der Benutzer eingeloggt ist und Admin-Rechte hat
    if ( is_user_logged_in() && current_user_can( 'manage_options' ) ) {
        // Setze ein Cookie mit dem Namen "wpsfse_is_admin"
        setcookie( 'wpsfse_is_admin', 'yes', time() + 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true );
    }
}
add_action( 'admin_init', 'wpsfse_set_admin_cookie' );

// Verarbeitung des Toggle-Buttons
function wpsfse_toggle_admin_results() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Unauthorized access.', 'wp-statistics-free-simple-easy'));
    }

    check_admin_referer('wpsfse_toggle_admin_results_nonce');

    $current_setting = get_option('wpsfse_show_admin_results', 'yes');
    $new_setting = ($current_setting === 'yes') ? 'no' : 'yes';

    update_option('wpsfse_show_admin_results', $new_setting);

    wp_redirect(admin_url('admin.php?page=wpsfse-settings'));
    exit;
}
add_action('admin_post_wpsfse_toggle_admin_results', 'wpsfse_toggle_admin_results');
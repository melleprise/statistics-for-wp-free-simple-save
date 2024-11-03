<?php
/*
Plugin Name: WP Statistics Free - Simple & Easy
Description: A straightforward statistics plugin with no paywalls, pop-ups, cookies, or heavy database usage. Delivers essential stats without slowing down your site or collecting any personal data. Simple, effective, and privacy-friendly.
Version: 1.0
Author: MELLEPRISE
Author URI: https://melleprise.com
License: GPL2
*/

// Block direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

require_once(plugin_dir_path(__FILE__) . 'include/database.php');
require_once(plugin_dir_path(__FILE__) . 'include/metadata.php');
require_once(plugin_dir_path(__FILE__) . 'include/uninstall.php');

// Menüeintrag für "WP Statistics Free" hinzufügen
function wpsfse_menu() {
    $icon = 'dashicons-chart-pie';
    
    // Menüeintrag für das Dashboard erstellen
    add_menu_page(
        'Statistics',             // Seiten-Titel
        'WP Statistics',          // Menü-Name
        'manage_options',         // Berechtigungen
        'wpsfse-dashboard',       // Slug der Seite
        'wpsfse_load_dashboard',  // Funktion zum Laden des Inhalts
        $icon,                    // Icon für das Menü (Dashicons)
        2                         // Position im Menü
    );
}
add_action('admin_menu', 'wpsfse_menu');

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
	wpsfse_reset_menu_visited();
	wpfse_create_db_table();
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

    // wp_enqueue_style('wpsfse_delete_warning_css', plugins_url('include/delete-warning.css', __FILE__));

    wp_enqueue_script('wpsfse_delete_warning_js');
    // wp_enqueue_script('wpsfse_delete_warning_js', 'wpsfse_delete_warning_css');
}
add_action('admin_enqueue_scripts', 'wpsfse_delete_warning_script');

// // Function to display visit count in the admin dashboard
// function wpsfse_dashboard_widget() {
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'wpsfse_visits';

//     // Get total visit count
//     $visit_count = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

//     echo '<p>Total Visits: ' . esc_html($visit_count) . '</p>';
// }
// function wpsfse_add_dashboard_widgets() {
//     wp_add_dashboard_widget(
//         'wpsfse_dashboard_widget',
//         'WP Statistics Free, Simple & Easy',
//         'wpsfse_dashboard_widget'
//     );
// }
// add_action('wp_dashboard_setup', 'wpsfse_add_dashboard_widgets');

// // Function to record each visit
// function wpsfse_record_visit() {
//     if (is_user_logged_in()) {
//         return; // Avoid logging visits from logged-in users
//     }

//     global $wpdb;
//     $table_name = $wpdb->prefix . 'wpsfse_visits';
//     $ip_address = $_SERVER['REMOTE_ADDR'];

//     // Insert visit record without collecting any personal info or cookies
//     $wpdb->insert(
//         $table_name,
//         array(
//             'visit_date' => current_time('mysql'),
//             'ip_address' => $ip_address,
//         )
//     );
// }
// add_action('wp_footer', 'wpsfse_record_visit');

<?php

// include/database.php

// Block direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

function wpfse_create_db_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wp_statistics_free_metadata';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        visit_date datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        ip_address varchar(55) DEFAULT '' NOT NULL,
        device VARCHAR(50) DEFAULT NULL,
        browser TEXT DEFAULT NULL,
        operating_system VARCHAR(100) DEFAULT NULL,
        platform VARCHAR(100) DEFAULT NULL,
        user_agent varchar(255) DEFAULT NULL,
        screen_resolution VARCHAR(20) DEFAULT NULL,
        time_zone VARCHAR(100) DEFAULT NULL,
        referrer_url TEXT DEFAULT NULL,
        url TEXT DEFAULT NULL,
        languages VARCHAR(10) DEFAULT NULL,
        connection_type VARCHAR(10) DEFAULT NULL,
        page_load_time FLOAT DEFAULT NULL,
        location VARCHAR(255) DEFAULT NULL,
        admin ENUM('yes', 'no') DEFAULT 'no' NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
<?php

// include/metadata.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function wpsfse_add_nonce() {
    if ( ! is_admin() ) {
        wp_register_script( 'wpsfse-statistics', plugins_url( 'metadata.js', __FILE__ ), array( 'jquery' ), time(), true );

        // Nonce für AJAX-Requests erstellen
        $nonce = wp_create_nonce( 'wpsfse_nonce' );

        // Nonce und AJAX-URL für das JavaScript verfügbar machen
        wp_localize_script( 'wpsfse-statistics', 'wpsfse_ajax', array(
            'ajax_url' => admin_url( 'admin-ajax.php' ),
            'nonce'    => $nonce,
        ) );

        wp_enqueue_script( 'wpsfse-statistics' );
    }
}
add_action( 'wp_enqueue_scripts', 'wpsfse_add_nonce' );

function save_metadata_via_ajax() {
    global $wpdb;

    // Sicherheitsüberprüfung
    $nonce = isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'wpsfse_nonce' ) ) {
        wp_send_json_error( 'Invalid request. Nonce verification failed.' );
        return;
    }

    // Verarbeitung von $_SERVER['HTTP_USER_AGENT']
    if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
        $user_agent_raw = wp_unslash( $_SERVER['HTTP_USER_AGENT'] );
        $user_agent = sanitize_text_field( $user_agent_raw );
    } else {
        $user_agent = '';
    }

    // Verarbeitung von $_SERVER['HTTP_ACCEPT_LANGUAGE']
    if ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ) {
        $accept_language_raw = wp_unslash( $_SERVER['HTTP_ACCEPT_LANGUAGE'] );
        $accept_language_sanitized = sanitize_text_field( $accept_language_raw );
        $languages = substr( $accept_language_sanitized, 0, 2 );
    } else {
        $languages = '';
    }

    $device = get_device_type( $user_agent );
    $browser = detect_browser( $user_agent );
    $operating_system = get_operating_system_with_version( $user_agent );
    $platform = isset( $_POST['platform'] ) ? sanitize_text_field( wp_unslash( $_POST['platform'] ) ) : 'unknown';
    $screen_resolution = isset( $_POST['screen_resolution'] ) ? sanitize_text_field( wp_unslash( $_POST['screen_resolution'] ) ) : 'unknown';
    $timezone = isset( $_POST['timezone'] ) ? sanitize_text_field( wp_unslash( $_POST['timezone'] ) ) : 'Unknown';
    $connection_type = isset( $_POST['connection_type'] ) ? sanitize_text_field( wp_unslash( $_POST['connection_type'] ) ) : 'unknown';
    $page_load_time = isset( $_POST['page_load_time'] ) ? floatval( wp_unslash( $_POST['page_load_time'] ) ) : 0;
    $location = isset( $_POST['location'] ) ? sanitize_text_field( wp_unslash( $_POST['location'] ) ) : 'unknown';
    $current_url = isset( $_POST['url'] ) ? esc_url_raw( wp_unslash( $_POST['url'] ) ) : 'unknown';
    $referrer_url = isset( $_POST['referrer_url'] ) ? esc_url_raw( wp_unslash( $_POST['referrer_url'] ) ) : 'Direct';
    $ip_address = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : 'unknown';

    // Admin-Feld setzen basierend auf Benutzerkontext oder Cookie
    $is_admin = 'no';
    if ( current_user_can( 'manage_options' ) ) {
        $is_admin = 'yes'; // Benutzer ist Admin
    } elseif ( isset( $_COOKIE['wpsfse_is_admin'] ) && $_COOKIE['wpsfse_is_admin'] === 'yes' ) {
        $is_admin = 'yes'; // Cookie zeigt Adminstatus an
    }

    // Bevor Sie $user_agent in die Datenbank speichern, bereinigen Sie es
    $user_agent_db = sanitize_text_field( $user_agent );

    // Cache Key für Duplikatsvermeidung
    $cache_key = 'wpsfse_metadata_' . md5( serialize( compact(
        'device', 'browser', 'operating_system', 'platform', 'screen_resolution', 'timezone', 'referrer_url',
        'current_url', 'languages', 'connection_type', 'page_load_time', 'location', 'ip_address', 'is_admin'
    ) ) );
    if ( false === wp_cache_get( $cache_key, 'wpsfse' ) ) {

        // Daten in die Tabelle einfügen
        $table_name_metadata = $wpdb->prefix . 'wp_statistics_free_metadata';

        $insert_result = $wpdb->insert(
            $table_name_metadata,
            array(
                'device'            => $device,
                'browser'           => $browser,
                'operating_system'  => $operating_system,
                'platform'          => $platform,
                'screen_resolution' => $screen_resolution,
                'time_zone'         => $timezone,
                'referrer_url'      => $referrer_url,
                'url'               => $current_url,
                'languages'         => $languages,
                'connection_type'   => $connection_type,
                'page_load_time'    => $page_load_time,
                'location'          => $location,
                'ip_address'        => $ip_address,
                'user_agent'        => $user_agent_db,
                'visit_date'        => current_time( 'mysql', 1 ), // GMT Zeit
                'admin'             => $is_admin, // Admin-Feld
            ),
            array(
                '%s', // device
                '%s', // browser
                '%s', // operating_system
                '%s', // platform
                '%s', // screen_resolution
                '%s', // time_zone
                '%s', // referrer_url
                '%s', // url
                '%s', // languages
                '%s', // connection_type
                '%f', // page_load_time
                '%s', // location
                '%s', // ip_address
                '%s', // user_agent
                '%s', // visit_date
                '%s', // admin
            )
        );

        if ( $insert_result !== false ) {
            wp_cache_set( $cache_key, true, 'wpsfse', 3600 ); // Cache für 1 Stunde
            wp_send_json_success( 'Metadata successfully saved.' );
        } else {
            wp_send_json_error( 'Error inserting metadata.' );
        }
    } else {
        wp_send_json_success( 'Data already cached.' );
    }
}

// add_action( 'wp_ajax_save_metadata', 'save_metadata_via_ajax' );
add_action( 'wp_ajax_save_metadata', 'save_metadata_via_ajax' );
add_action( 'wp_ajax_nopriv_save_metadata', 'save_metadata_via_ajax' );

function get_operating_system_with_version( $user_agent ) {
    $os_platform = "Unknown OS";
    $os_version  = "";

    // Sanitize $user_agent before using in preg_match
    $user_agent = sanitize_text_field( $user_agent );

    $os_array = array(
        '/windows nt 10.0/i'    => 'Windows 10/11',
        '/windows nt 6.3/i'     => 'Windows 8.1',
        '/windows nt 6.2/i'     => 'Windows 8',
        '/windows nt 6.1/i'     => 'Windows 7',
        '/windows nt 5.1/i'     => 'Windows XP',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/android/i'            => 'Android',
        '/iphone/i'             => 'iOS',
        '/ipad/i'               => 'iOS',
        '/linux/i'              => 'Linux',
        '/smarttv/i'            => 'Smart TV',
        '/tizen/i'              => 'Tizen OS'
    );

    foreach ( $os_array as $regex => $value ) {
        if ( preg_match( $regex, $user_agent ) ) {
            $os_platform = $value;
            if ( preg_match( '/windows nt ([\d\.]+)/i', $user_agent, $version_match ) && strpos( $os_platform, 'Windows' ) !== false ) {
                $os_version = $version_match[1];
            } elseif ( preg_match( '/Mac OS X ([\d_\.]+)/', $user_agent, $version_match ) && strpos( $os_platform, 'Mac OS X' ) !== false ) {
                $os_version = str_replace( '_', '.', $version_match[1] );
            } elseif ( preg_match( '/Android ([\d\.]+)/', $user_agent, $version_match ) && strpos( $os_platform, 'Android' ) !== false ) {
                $os_version = $version_match[1];
            } elseif ( preg_match( '/OS ([\d_\.]+) like Mac OS X/', $user_agent, $version_match ) && strpos( $os_platform, 'iOS' ) !== false ) {
                $os_version = str_replace( '_', '.', $version_match[1] );
            } elseif ( preg_match( '/Tizen ([\d\.]+)/', $user_agent, $version_match ) && strpos( $os_platform, 'Tizen OS' ) !== false ) {
                $os_version = $version_match[1];
            }
            break;
        }
    }
    return $os_platform . ( $os_version ? " " . $os_version : "" );
}

function get_device_type( $user_agent ) {
    // Sanitize $user_agent before using in preg_match
    $user_agent = sanitize_text_field( $user_agent );

    if ( preg_match( '/mobile/i', $user_agent ) ) {
        return 'Mobile';
    } elseif ( preg_match( '/tablet/i', $user_agent ) ) {
        return 'Tablet';
    } elseif ( preg_match( '/tv/i', $user_agent ) || preg_match( '/smarttv/i', $user_agent ) || preg_match( '/hbbtv/i', $user_agent ) ) {
        return 'TV';
    } elseif ( preg_match( '/macintosh|mac os x|windows|linux/i', $user_agent ) ) {
        return 'Desktop';
    } else {
        return 'Unknown Device';
    }
}

function detect_browser( $user_agent ) {
    // Sanitize $user_agent before using in preg_match
    $user_agent = sanitize_text_field( $user_agent );

    $browser         = "Unknown Browser";
    $browser_version = "";

    $browser_array = array(
        '/firefox/i'    => 'Firefox',
        '/edg/i'        => 'Edge',
        '/chrome/i'     => 'Chrome',
        '/safari/i'     => 'Safari',
        '/opera|opr/i'  => 'Opera',
        '/msie/i'       => 'Internet Explorer',
        '/trident/i'    => 'Internet Explorer'
    );

    foreach ( $browser_array as $regex => $value ) {
        if ( preg_match( $regex, $user_agent ) ) {
            $browser = $value;
            if ( preg_match( '/' . preg_quote( $value, '/' ) . '[\/\s](\d+\.\d+)/i', $user_agent, $version_match ) ||
                 preg_match( '/version[\/\s](\d+\.\d+)/i', $user_agent, $version_match ) ) {
                $browser_version = $version_match[1];
            }
            break;
        }
    }
    return $browser . ( $browser_version ? " " . $browser_version : "" );
}
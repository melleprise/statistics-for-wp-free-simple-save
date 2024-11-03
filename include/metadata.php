<?php

// include/metadata.php

// Block direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

// Funktion zum Erstellen und Einbinden des Nonce und der Metadaten im Frontend
function wpsfse_add_nonce() {
    // Nur im Frontend ausführen, nicht im Backend
    if (!is_admin()) {
        // JavaScript-Datei registrieren
        wp_register_script('wpsfse-statistics', plugins_url('metadata.js', __FILE__), array('jquery'), time(), true);

        // Nonce für AJAX-Requests erstellen
        $nonce = wp_create_nonce('wpsfse_nonce');

        // Nonce und AJAX-URL für das JavaScript verfügbar machen
        wp_localize_script('wpsfse-statistics', 'wpsfse_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => $nonce,
        ));

    // Nonce und AJAX-URL für das JavaScript verfügbar machen
    wp_localize_script('melleprise-onboarding', 'melleprise_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => $nonce // Nonce korrekt übergeben
    ));

        // JavaScript-Datei in die Warteschlange einfügen
        wp_enqueue_script('wpsfse-statistics');

        // Log-Ausgabe in die debug.log-Datei
        error_log('metadata.js und Nonce im Frontend eingebunden');
    }
}
add_action('wp_enqueue_scripts', 'wpsfse_add_nonce');

function save_metadata_via_ajax() {
    global $wpdb;

    // Sicherheitsüberprüfung
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'wpsfse_nonce')) {
        error_log('Ungültige Anfrage. Nonce-Verifizierung fehlgeschlagen.');
        wp_send_json_error('Ungültige Anfrage. Fehlende oder falsche Nonce.');
        return;
    }

    // Eingehende Daten verarbeiten
    $cookie_hash = generate_user_hash_cookie();
    $device = wp_is_mobile() ? 'Mobile' : 'Desktop';
    $browser = sanitize_text_field($_SERVER['HTTP_USER_AGENT']);
    $operating_system = get_operating_system($_SERVER['HTTP_USER_AGENT']);
    $screen_resolution = isset($_POST['screen_resolution']) ? sanitize_text_field($_POST['screen_resolution']) : 'unknown';
    $timezone = isset($_POST['timezone']) ? sanitize_text_field($_POST['timezone']) : 'Unknown';
    $connection_type = isset($_POST['connection_type']) ? sanitize_text_field($_POST['connection_type']) : 'unknown';
    $page_load_time = isset($_POST['page_load_time']) ? floatval($_POST['page_load_time']) : 0;
    $location = isset($_POST['location']) ? sanitize_text_field($_POST['location']) : 'unknown';
    $current_url = isset($_POST['url']) ? esc_url_raw($_POST['url']) : 'unknown';
    $referrer_url = isset($_POST['referrer_url']) && $_POST['referrer_url'] !== 'Direct' ? esc_url_raw($_POST['referrer_url']) : 'Direct';
    $ip_address = sanitize_text_field($_SERVER['REMOTE_ADDR']); // Verwende den richtigen Namen für die Spalte
    $languages = sanitize_text_field(substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2));

    // Korrigierter Tabellenname und Spaltenname
    $table_name_metadata = $wpdb->prefix . 'wp_statistics_free_metadata';
    $wpdb->insert(
        $table_name_metadata,
        array(
            'device' => $device,
            'browser' => $browser,
            'operating_system' => $operating_system,
            'screen_resolution' => $screen_resolution,
            'time_zone' => $timezone,
            'referrer_url' => $referrer_url,
            'url' => $current_url,
            'languages' => $languages,
            'connection_type' => $connection_type,
            'page_load_time' => strval($page_load_time),
            'location' => $location,
            'ip_address' => $ip_address // Ändere hier zu 'ip_address'
        ),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%s')
    );

    if ($wpdb->last_error) {
        error_log('Fehler beim Einfügen der Metadaten: ' . $wpdb->last_error);
    } else {
        wp_send_json_success('Metadaten erfolgreich gespeichert.');
    }
}

add_action('wp_ajax_save_metadata', 'save_metadata_via_ajax');
add_action('wp_ajax_nopriv_save_metadata', 'save_metadata_via_ajax');

function generate_user_hash_cookie() {
    $cookie_name = 'user_unique_hash';
    $cookie_expiry = time() + (365 * 24 * 60 * 60); // Cookie läuft in einem Jahr ab

    // Überprüfen, ob der Cookie bereits gesetzt ist
    if (!isset($_COOKIE[$cookie_name])) {
        // Falls kein Cookie vorhanden ist, erstelle einen neuen
        $cookie_hash = wp_hash(uniqid(rand(), true));

        // Setze den Cookie
        setcookie($cookie_name, $cookie_hash, $cookie_expiry, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

        // Sicherstellen, dass der Cookie sofort im PHP verfügbar ist
        $_COOKIE[$cookie_name] = $cookie_hash;
    }

    return sanitize_text_field($_COOKIE[$cookie_name]);
}

function get_operating_system($user_agent) {
    $os_platform = "Unknown OS";

    $os_array = array(
        '/windows nt 10/i' => 'Windows 10',
        '/windows nt 6.3/i' => 'Windows 8.1',
        '/windows nt 6.2/i' => 'Windows 8',
        '/windows nt 6.1/i' => 'Windows 7',
        '/macintosh|mac os x/i' => 'Mac OS X',
        '/linux/i' => 'Linux',
        '/iphone/i' => 'iOS',
        '/android/i' => 'Android',
    );

    foreach ($os_array as $regex => $value) {
        if (preg_match($regex, $user_agent)) {
            $os_platform = $value;
        }
    }

    return $os_platform;
}
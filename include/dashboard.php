<?php

// include/dashboard.php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

global $wpdb;

// Datumsbereiche definieren
$today_start  = gmdate( 'Y-m-d 00:00:00' );
$week_start   = gmdate( 'Y-m-d 00:00:00', strtotime( 'monday this week' ) );
$month_start  = gmdate( 'Y-m-01 00:00:00' );

// Nonce für Paginierung generieren
$pagination_nonce = wp_create_nonce( 'pagination_nonce' );

// Paginierungsparameter
$items_per_page = 10;
$page = 1;

// Nonce-Verifizierung vor Zugriff auf $_GET['paged']
if ( isset( $_GET['paged'] ) ) {
    $nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';
    if ( ! wp_verify_nonce( $nonce, 'pagination_nonce' ) ) {
        wp_die( esc_html__( 'Sicherheitsüberprüfung fehlgeschlagen.', 'wp-statistics-free-simple-easy' ) );
    }
    $page = max( 1, intval( $_GET['paged'] ) );
}

$offset = ( $page - 1 ) * $items_per_page;

// Gesamtbesuche zählen
$total_visits = wp_cache_get( 'wpsfse_total_visits', 'wpsfse' );
if ( false === $total_visits ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich, Caching implementiert
    $total_visits = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}wp_statistics_free_metadata"
    );
    wp_cache_set( 'wpsfse_total_visits', $total_visits, 'wpsfse', 3600 );
}

// Besuche heute zählen
$total_visits_today_key = 'wpsfse_total_visits_today_' . gmdate( 'Ymd' );
$today_visits = wp_cache_get( $total_visits_today_key, 'wpsfse' );
if ( false === $today_visits ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich, Caching implementiert
    $today_visits = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wp_statistics_free_metadata WHERE visit_date >= %s",
            $today_start
        )
    );
    wp_cache_set( $total_visits_today_key, $today_visits, 'wpsfse', 3600 );
}

// Besuche diese Woche zählen
$total_visits_week_key = 'wpsfse_total_visits_week_' . gmdate( 'YW' );
$week_visits = wp_cache_get( $total_visits_week_key, 'wpsfse' );
if ( false === $week_visits ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich, Caching implementiert
    $week_visits = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wp_statistics_free_metadata WHERE visit_date >= %s",
            $week_start
        )
    );
    wp_cache_set( $total_visits_week_key, $week_visits, 'wpsfse', 3600 );
}

// Besuche diesen Monat zählen
$total_visits_month_key = 'wpsfse_total_visits_month_' . gmdate( 'Ym' );
$month_visits = wp_cache_get( $total_visits_month_key, 'wpsfse' );
if ( false === $month_visits ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich, Caching implementiert
    $month_visits = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}wp_statistics_free_metadata WHERE visit_date >= %s",
            $month_start
        )
    );
    wp_cache_set( $total_visits_month_key, $month_visits, 'wpsfse', 3600 );
}

// Header mit Zählern anzeigen
echo '<div class="header-container">';
echo '<h1 class="header-title">' . esc_html__( 'WP Statistics', 'wp-statistics-free-simple-easy' ) . '</h1>';
echo '<div class="statistics-counters">
    <span><strong>' . esc_html__( 'Heute:', 'wp-statistics-free-simple-easy' ) . '</strong> ' . esc_html( $today_visits ) . '</span>
    <span><strong>' . esc_html__( 'Woche:', 'wp-statistics-free-simple-easy' ) . '</strong> ' . esc_html( $week_visits ) . '</span>
    <span><strong>' . esc_html__( 'Monat:', 'wp-statistics-free-simple-easy' ) . '</strong> ' . esc_html( $month_visits ) . '</span>
    <span><strong>' . esc_html__( 'Gesamt:', 'wp-statistics-free-simple-easy' ) . '</strong> ' . esc_html( $total_visits ) . '</span>
</div>';
echo '</div>';

// Gesamtanzahl der Einträge abrufen
$total_items = wp_cache_get( 'wpsfse_total_items', 'wpsfse' );
if ( false === $total_items ) {
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich, Caching implementiert
    $total_items = $wpdb->get_var(
        "SELECT COUNT(*) FROM {$wpdb->prefix}wp_statistics_free_metadata"
    );
    wp_cache_set( 'wpsfse_total_items', $total_items, 'wpsfse', 600 );
}

// Daten für die aktuelle Seite abrufen
// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Direkte Abfrage erforderlich
$results = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}wp_statistics_free_metadata ORDER BY visit_date DESC LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    )
);

// Tabelle anzeigen
echo '<div class="content-container">';
echo '<table class="widefat fixed" cellspacing="0">';
echo '<thead><tr>
    <th>' . esc_html__( 'Datum', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'IP-Adresse', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Gerät', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Browser', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Betriebssystem', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Auflösung', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Zeitzone', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Referrer', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'URL', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Sprachen', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Verbindungstyp', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Seiten-Ladezeit (s)', 'wp-statistics-free-simple-easy' ) . '</th>
    <th>' . esc_html__( 'Standort', 'wp-statistics-free-simple-easy' ) . '</th>
</tr></thead>';
echo '<tbody>';

if ( ! empty( $results ) ) {
    foreach ( $results as $row ) {
        echo '<tr>';
        echo '<td>' . esc_html( $row->visit_date ) . '</td>';
        echo '<td>' . esc_html( $row->ip_address ) . '</td>';
        echo '<td>' . esc_html( $row->device ) . '</td>';
        echo '<td>' . esc_html( $row->browser ) . '</td>';
        echo '<td>' . esc_html( $row->operating_system ) . '</td>';
        echo '<td>' . esc_html( $row->screen_resolution ) . '</td>';
        echo '<td>' . esc_html( $row->time_zone ) . '</td>';
        echo '<td>' . esc_html( $row->referrer_url ) . '</td>';
        echo '<td>' . esc_html( $row->url ) . '</td>';
        echo '<td>' . esc_html( $row->languages ) . '</td>';
        echo '<td>' . esc_html( $row->connection_type ) . '</td>';
        echo '<td>' . esc_html( $row->page_load_time ) . '</td>';
        echo '<td>' . esc_html( $row->location ) . '</td>';
        echo '</tr>';
    }
} else {
    echo '<tr><td colspan="13">' . esc_html__( 'Keine Daten verfügbar.', 'wp-statistics-free-simple-easy' ) . '</td></tr>';
}

echo '</tbody></table>';

// Paginierung anzeigen
$total_pages = ceil( $total_items / $items_per_page );

echo '<div class="pagination-container" style="text-align:center; margin-top:20px;">';

if ( $total_pages > 1 ) {
    if ( $page > 1 ) {
        $prev_page = $page - 1;
        echo '<a class="page-numbers" href="' . esc_url( add_query_arg( array( 'paged' => $prev_page, '_wpnonce' => $pagination_nonce ), admin_url( 'admin.php?page=wpsfse-dashboard' ) ) ) . '">&laquo; ' . esc_html__( 'Zurück', 'wp-statistics-free-simple-easy' ) . '</a> ';
    }

    for ( $i = 1; $i <= $total_pages; $i++ ) {
        $class = ( $i == $page ) ? 'current' : '';
        echo '<a class="page-numbers ' . esc_attr( $class ) . '" href="' . esc_url( add_query_arg( array( 'paged' => $i, '_wpnonce' => $pagination_nonce ), admin_url( 'admin.php?page=wpsfse-dashboard' ) ) ) . '">' . esc_html( $i ) . '</a> ';
    }

    if ( $page < $total_pages ) {
        $next_page = $page + 1;
        echo '<a class="page-numbers" href="' . esc_url( add_query_arg( array( 'paged' => $next_page, '_wpnonce' => $pagination_nonce ), admin_url( 'admin.php?page=wpsfse-dashboard' ) ) ) . '">' . esc_html__( 'Weiter', 'wp-statistics-free-simple-easy' ) . ' &raquo;</a>';
    }
}

echo '</div>';
echo '</div>';
?>
<style>
/* Container to provide padding on both left and right sides */
.content-container {
    padding-right: 20px;
    box-sizing: border-box;
    width: 100%;
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 0px;
}

.header-title {
    font-size: 24px;
    font-weight: bold;
}

.statistics-counters {
	padding-right: 20px;
    font-size: 16px;
    display: flex;
    gap: 20px;
}

.statistics-counters span {
    display: inline-block;
}

/* Pagination styling */
.pagination-container .page-numbers {
    font-size: 18px;
    margin: 0 5px;
    padding: 5px 10px;
    text-decoration: none;
    border-radius: 4px;
    color: #0073aa;
}

.pagination-container .page-numbers.current {
    font-weight: bold;
    color: #ffffff;
    background-color: #0073aa;
}

.pagination-container .page-numbers:hover {
    background-color: #005177;
    color: #ffffff;
}
</style>

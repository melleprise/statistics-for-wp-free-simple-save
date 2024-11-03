<?php

//	include/dashboard.php

// Block direct access to the file
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;
$table_name = $wpdb->prefix . 'wp_statistics_free_metadata';

// Define date ranges for today, this week, and this month
$today_start = date('Y-m-d 00:00:00');
$week_start = date('Y-m-d 00:00:00', strtotime('monday this week'));
$month_start = date('Y-m-01 00:00:00');

// Count total visits
$total_visits = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Count visits today
$today_visits = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE visit_date >= %s", $today_start));

// Count visits this week
$week_visits = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE visit_date >= %s", $week_start));

// Count visits this month
$month_visits = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_name WHERE visit_date >= %s", $month_start));

// Display title with counters
echo '<div class="header-container">';
echo '<h1 class="header-title">WP Statistics</h1>';
echo "<div class='statistics-counters'>
    <span><strong>Today:</strong> $today_visits</span>
    <span><strong>This Week:</strong> $week_visits</span>
    <span><strong>This Month:</strong> $month_visits</span>
    <span><strong>Total:</strong> $total_visits</span>
</div>";
echo '</div>';

// Pagination parameters
$items_per_page = 10;
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$offset = ($page - 1) * $items_per_page;

// Total number of entries in the table
$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");

// Retrieve data for the current page
$results = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name ORDER BY visit_date DESC LIMIT %d OFFSET %d", $items_per_page, $offset));

// Display table within padded container
echo '<div class="content-container">';
echo '<table class="widefat fixed" cellspacing="0">';
echo '<thead><tr>
    <th>Date</th>
    <th>IP Address</th>
    <th>Device</th>
    <th>Browser</th>
    <th>Operating System</th>
    <th>Resolution</th>
    <th>Time Zone</th>
    <th>Referrer</th>
    <th>URL</th>
    <th>Languages</th>
    <th>Connection Type</th>
    <th>Page Load Time (s)</th>
    <th>Location</th>
</tr></thead>';
echo '<tbody>';

foreach ($results as $row) {
    echo '<tr>';
    echo '<td>' . esc_html($row->visit_date) . '</td>';
    echo '<td>' . esc_html($row->ip_address) . '</td>';
    echo '<td>' . esc_html($row->device) . '</td>';
    echo '<td>' . esc_html($row->browser) . '</td>';
    echo '<td>' . esc_html($row->operating_system) . '</td>';
    echo '<td>' . esc_html($row->screen_resolution) . '</td>';
    echo '<td>' . esc_html($row->time_zone) . '</td>';
    echo '<td>' . esc_html($row->referrer_url) . '</td>';
    echo '<td>' . esc_html($row->url) . '</td>';
    echo '<td>' . esc_html($row->languages) . '</td>';
    echo '<td>' . esc_html($row->connection_type) . '</td>';
    echo '<td>' . esc_html($row->page_load_time) . '</td>';
    echo '<td>' . esc_html($row->location) . '</td>';
    echo '</tr>';
}

echo '</tbody></table>';

// Calculate total pages
$total_pages = ceil($total_items / $items_per_page);

// Display pagination links
echo '<div class="pagination-container" style="text-align:center; margin-top:20px;">';

if ($total_pages > 1) {
    if ($page > 1) {
        $prev_page = $page - 1;
        echo "<a class='page-numbers' href='?page=wpsfse-dashboard&paged=$prev_page'>&laquo; Previous</a> ";
    }

    for ($i = 1; $i <= $total_pages; $i++) {
        $class = ($i == $page) ? 'current' : '';
        echo "<a class='page-numbers $class' href='?page=wpsfse-dashboard&paged=$i'>$i</a> ";
    }

    if ($page < $total_pages) {
        $next_page = $page + 1;
        echo "<a class='page-numbers' href='?page=wpsfse-dashboard&paged=$next_page'>Next &raquo;</a>";
    }
}

echo '</div>';
echo '</div>'; // Close content-container

?>

<style>
/* Container to provide padding on both left and right sides */
.content-container {
    padding: 0 20px; /* Adds padding to both sides */
    box-sizing: border-box; /* Ensures padding is within width */
    width: 100%; /* Ensure full width within the content area */
}

.header-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.header-title {
    font-size: 24px;
    font-weight: bold;
}

.statistics-counters {
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

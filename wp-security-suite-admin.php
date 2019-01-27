<?php

add_action('admin_menu', 'wp_security_suite_setup_menu');
 
function wp_security_suite_setup_menu(){
        add_menu_page( 'WP Security Suite', 'WP Security Suite', 'manage_options', 'wp-sec-dashboard', 'wp_security_suite_dashboard' );
        add_submenu_page( 'wp-sec-dashboard', 'View Change Logs', 'File Change Logs', 'manage_options', 'wp-sec-file-change-logs', 'wp_security_suite_file_change_logs');
}
 
function wp_security_suite_dashboard(){
    echo "<h1>WP Security Suite Dashboard</h1>";
    echo "<hr>";
}

function wp_security_suite_file_change_logs() {
    echo "<h1>File Change Logs</h1>";
    echo "<hr>";
    global $wpdb;

    $results = $wpdb->get_results(
        $wpdb->prepare("SELECT `id`, `filename`, `change_type`, `change_date` FROM {$wpdb->prefix}filechanges ORDER BY `id` DESC LIMIT 50", $ent)
    );

    if(sizeof($results) != 0) {

        echo "<table>";
        echo "<tr>";
        echo "<th>File Name</th>";
        echo "<th>Change Type</th>";
        echo "<th>Change Date</th>";
        echo "<tr>";
        foreach($results as $Result) {
            echo "<tr>";
            echo "<td>" . $Result->filename . "</td>";
            echo "<td>" . $Result->change_type . "</td>";
            echo "<td>" . $Result->change_date . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    }else{
        echo "No data available.";
    }
}
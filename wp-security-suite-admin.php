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

function wp_sec_setting_section_update_callback() {
    echo "<p>Enter email to be notified at.";
}

function wp_sec_setting_update_callback() {
    echo '<input name="wp_sec_email" id="wp_sec_email" type="text" value="' . get_option('wp_sec_email') . '">';
}

function wp_sec_settings_api_init() {
   add_settings_section(
     'wp_sec_email',
     'WP Sec Configuration',
     'wp_sec_setting_section_update_callback',
     'general'
   );
   add_settings_field(
     'wp_sec_email',
     'Email to notify.',
     'wp_sec_setting_update_callback',
     'general',
     'wp_sec_email'
   );
   register_setting( 'general', 'wp_sec_email' );
}
add_action( 'admin_init', 'wp_sec_settings_api_init' );
<?php
/*
 * Plugin Name: wp-file-security
 * Plugin URI: https://swheeler.co
 * Description: Checks files for changes
 * Version: 0.1
 * Author: Scott Wheeler
 * Author URI: https://swheeler.co
*/

define('WP_DEBUG', true);

require_once("wp-file-security.php");
require_once("wp-security-suite-admin.php");


function wp_file_sec_install() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;    

    $table_name = $wpdb->prefix . "filehashes";
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE `{$wpdb->prefix}filehashes` (
        `id` int unsigned auto_increment primary key,
        `filename` varchar(1024) not null,
        `filehash` char(40) not null
    ) $charset_collate;";
    dbDelta($sql);

    $sql = "CREATE TABLE `{$wpdb->prefix}filechanges` (
        `id` int unsigned auto_increment primary key,
        `filename` varchar(1024) not null,
        `change_type` varchar(8) NOT NULL,
        `change_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) $charset_collate;";
    dbDelta($sql);

    if(!wp_next_scheduled('hourly_file_scan')) {
        wp_schedule_event(time(), 'hourly', 'hourly_file_scan');
    }
}

register_activation_hook(__FILE__, 'wp_file_sec_install');

function wp_file_sec_uninstall() {
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    global $wpdb;

    $sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}filehashes`;";
    dbDelta($sql);
    $sql = "DROP TABLE IF EXISTS `{$wpdb->prefix}filechanges`;";
    dbDelta($sql);

    wp_clear_scheduled_hook('hourly_file_scan');
}

register_deactivation_hook(__FILE__, 'wp_file_sec_uninstall');
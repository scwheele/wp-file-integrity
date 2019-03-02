<?php

// logger

function wp_ss_log_message($message) {
    global $wpdb;
    $table = $wpdb->prefix . 'wp-ss-logging';
    $data = array('message' => $message);
    $format = array('%s', '%s');
    $wpdb->insert($table, $data, $format);
    return $wpdb->insert_id;
}
<?php


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

    wp_schedule_event(time(), 'hourly', 'hourly_file_scan');
    if(!wp_next_scheduled('hourly_file_scan')) {
        wp_schedule_event(time(), 'hourly', 'hourly_file_scan');
    }
}


function wp_file_sec_scan_files() {
    $home_path = ABSPATH;

    $files = rsearch($home_path, "/^.+\.php$/i");

    foreach($files as $ent)
    {
        if(is_dir($ent))
        {
            continue;
        }

        global $wpdb;
        $file_hash = sha1_file($ent);

        $results = $wpdb->get_results(
            $wpdb->prepare("SELECT `id`, `filehash`, `filename` FROM {$wpdb->prefix}filehashes WHERE `filename`=%s", $ent)
        );

        if(sizeof($results) == 0) {
            $fc_insert_id = add_file_change($ent, 'ADD');
            $fa_insert_id = add_file($ent, $file_hash);
            echo $ent . " (SHA1: " . $file_hash . ") added. ID: " . $fc_insert_id;
            echo "<br>";
        }else{
            foreach($results as $result) {
                echo $result->id . " " . $result->filename . " " . $result->filehash;
                if($result->filehash != $file_hash) {
                    add_file_change($result->filename, "CHANGE");
                    update_file($result->filename, $file_hash);
                    echo " Different";
                }
            }
            echo "<br>";
        }
    }
}
add_action('hourly_file_scan', 'wp_file_sec_scan_files');

function update_file($filename, $filehash) {
    global $wpdb;
    $table = $wpdb->prefix . 'filehashes';
    $data = array('filehash' => $filehash);
    $where = array('filename' => $filename);
    $wpdb->update($table, $data, $where);
}

function add_file($filename, $filehash) {
    global $wpdb;
    $table = $wpdb->prefix . 'filehashes';
    $data = array('filename' => $filename, 'filehash' => $filehash);
    $format = array('%s', '%s');
    $wpdb->insert($table, $data, $format);
    return $wpdb->insert_id;
}

function add_file_change($filename, $change_type) {
    global $wpdb;
    $table = $wpdb->prefix . 'filechanges';
    $data = array('filename' => $filename, 'change_type' => $change_type);
    $format = array('%s', '%s');
    $wpdb->insert($table, $data, $format);
    return $wpdb->insert_id;
}


function rsearch($folder, $pattern) {

    $Directory = new RecursiveDirectoryIterator($folder);
    $Iterator = new RecursiveIteratorIterator($Directory);
    $files = new RegexIterator($Iterator, $pattern, RecursiveRegexIterator::GET_MATCH);

    $fileList = array();

    echo $dir;
    foreach($files as $file) {
        $fileList = array_merge($fileList, $file);
    }
    return $fileList;
}
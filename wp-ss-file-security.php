<?php

function wp_file_sec_check_permissions() {
    $home_path = ABSPATH;
    $files = rsearch($home_path, "/.*/i");
    $config_file = "wp-config.php"; // perm 660

    foreach($files as $file) {
        $file_permission = substr(sprintf('%o', fileperms($file)), -4);
        if(substr($file, strlen($file) - strlen($config_file)) == $config_file) {
            echo "config" . $file . " " . $file_permission . "<br>";
            if((int)$file_permission != 660) {
                echo '<span style="color: red;"'>$file . " Perms " . $file_permission . "</span>";
            }
        }
        
        if(is_dir($file)) {
            echo "file" . $file . "<br>";
            if($file_permission != 0775) {
                echo '<span style="color: red;"'>$file . " Perms " . $file_permission . "</span>";
            }
        }
        //    if($file_permission != 0664) {
        //        echo '<span style="color: red;"'>$file . " Perms " . $file_permission . "</span>";
        //    }else{
        //        echo $file;
        //    }
       // }
    }
    
}
//add_action('daily_permission_scan', 'wp_file_sec_check_permissions');
//add_action('init', 'wp_file_sec_check_permissions');

function wp_file_sec_scan_files() {
    $home_path = ABSPATH;
    $files = rsearch($home_path, "/^.+\.php$/i");
    $file_list = "";

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
            $file_list .= "ADD | " . $ent . "<br />";
        }else{
            foreach($results as $result) {
                echo $result->id . " " . $result->filename . " " . $result->filehash;
                if($result->filehash != $file_hash) {
                    add_file_change($result->filename, "CHANGE");
                    update_file($result->filename, $file_hash);
                    $file_list .= "CHG | " . $result->filename . "<br />";
                }
            }
        }
    }
    mail_report($file_list);
}
add_action('hourly_file_scan', 'wp_file_sec_scan_files');

function mail_report($ReportedItems) {
    if($ReportedItems != "") {
        $to = get_option('wp_sec_email');
        $subject = 'File Change Report';
        $headers = array('Content-Type: text/html; charset=UTF-8');

        $body = '
            Hello,<br />

            Some files have changed in your WordPress install. Please take a minute to review them.<br /><br />

        ' . $ReportedItems;
    
        wp_mail( $to, $subject, $body, $headers );
    }
}

function update_file($filename, $filehash) {
    global $wpdb;
    $table = $wpdb->prefix . 'wp-ss-filehashes';
    $data = array('filehash' => $filehash);
    $where = array('filename' => $filename);
    $wpdb->update($table, $data, $where);
}

function add_file($filename, $filehash) {
    global $wpdb;
    $table = $wpdb->prefix . 'wp-ss-filehashes';
    $data = array('filename' => $filename, 'filehash' => $filehash);
    $format = array('%s', '%s');
    $wpdb->insert($table, $data, $format);
    return $wpdb->insert_id;
}

function add_file_change($filename, $change_type) {
    global $wpdb;
    $table = $wpdb->prefix . 'wp-ss-filechanges';
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

    foreach($files as $file) {
        $fileList = array_merge($fileList, $file);
    }
    return $fileList;
}
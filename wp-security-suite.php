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

register_activation_hook(__FILE__, 'wp_file_sec_install');
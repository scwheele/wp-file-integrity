<?php

// user register logging

function wp_ss_user_login_hook($user_id) {
    $user = get_user_by('id', $user_id);

    wp_ss_log_message('User Registered: ' . $user->user_login);
}

add_action('user_register', 'wp_ss_user_login_hook');
<?php

/*
Plugin Name: Chat by Chatwee
Plugin URI: https://chatwee.com/
Description: WordPress Chat by Chatwee is fully customizable social chat & comment platform for websites and blogs. With Chatwee you can engage your online community and provide real-time communication.
Version: 2.1.3
Author: Chatwee Ltd
Author URI: https://chatwee.com/
License: GPLv2 or later
Text Domain: chatwee
*/

if (!defined("ABSPATH")) exit;

$chatwee_settings = get_option("chatwee_settings");

require_once(dirname( __FILE__ ) . "/chatwee-admin.php");

require_once(dirname( __FILE__ ) . "/lib/ChatweeV2_SDK/Chatwee.php");

register_activation_hook(__FILE__, "activate_chatwee");


function get_default_chatwee_settings() {
    return Array(
        "chatwee_script" => "",
        "disable_offline_users" => false,
        "categories_to_display" => array("main_page", "search_page", "archive_page", "post_page", "single_page"),
        "enable_sso" => false,
        "chat_id" => "",
        "api_url" => "",
        "client_key" => "",
        "moderator_roles" => array()
    );
}

function activate_chatwee() {

    require_once(ABSPATH . "wp-admin/includes/upgrade.php");

    global $wpdb;

    $moderators_table_name = $wpdb->prefix . "chatwee_moderators";

    $create_moderators_table_clause = "CREATE TABLE $moderators_table_name (id int(11) NOT NULL AUTO_INCREMENT, user_id int(11) DEFAULT NULL, UNIQUE KEY id (id));";

    dbDelta($create_moderators_table_clause);


    $log_table_name = $wpdb->prefix . "chatwee_log";

    $create_log_table_clause = "CREATE TABLE $log_table_name (log_id int(11) NOT NULL AUTO_INCREMENT, log_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP, log_message TEXT, UNIQUE KEY log_id (log_id));";

    dbDelta($create_log_table_clause);


    $pages_to_display_table_name = $wpdb->prefix . "chatwee_pages_to_display";

    $create_pages_to_display_table_clause = "CREATE TABLE $pages_to_display_table_name (id int(11) NOT NULL AUTO_INCREMENT, page_id int(11) DEFAULT NULL, UNIQUE KEY id (id));";

    dbDelta($create_pages_to_display_table_clause);


    update_option("chatwee_settings", get_default_chatwee_settings());
}

function chatwee_is_page_enabled() {
    $chatwee_settings = get_option("chatwee_settings");

    $id = get_the_ID();

    $categories_to_display = $chatwee_settings["categories_to_display"];

    $main_page_matched = is_array($categories_to_display) && in_array("main_page", $categories_to_display) && is_home() === true;
    $search_page_matched = is_array($categories_to_display) && in_array("search_page", $categories_to_display) && is_search() === true;
    $archive_page_matched = is_array($categories_to_display) && in_array("archive_page", $categories_to_display) && is_archive() === true;
    $post_page_matched = is_array($categories_to_display) && in_array("post_page", $categories_to_display) && is_single() === true;
    $single_page_matched = is_array($categories_to_display) && in_array("single_page", $categories_to_display) && is_page() === true;

    $page_matched = chatwee_is_page_selected($id) && is_singular() === true;

    return $main_page_matched || $search_page_matched || $archive_page_matched || $post_page_matched || $single_page_matched || $page_matched;
}

function chatwee_if_user_is_moderator($user_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . "chatwee_moderators";

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE user_id = %d", $user_id));

    return $row && $row->user_id ? true : false;
}

function chatwee_is_page_selected($page_id) {
    global $wpdb;

    $table_name = $wpdb->prefix . "chatwee_pages_to_display";

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . " WHERE page_id = %d", $page_id));

    return $row && $row->page_id ? true : false;
}

function chatwee_get_avatar_url($user_id) {
    $image = get_avatar($user_id);
    preg_match("/src=('|\")(.*?)('|\")/i", $image, $matches);
    return $matches[2];
}

function chatwee_add_log($message) {
    global $wpdb;

    $table_name = $wpdb->prefix . "chatwee_log";

    $wpdb->insert($table_name, Array("log_message" => $message));
}

function chatwee_initialize() {
    $chatwee_settings = get_option("chatwee_settings");

    ChatweeV2_Configuration::setApiUrl($chatwee_settings["api_url"]);
    ChatweeV2_Configuration::setChatId($chatwee_settings["chat_id"]);
    ChatweeV2_Configuration::setClientKey($chatwee_settings["client_key"]);
    ChatweeV2_Configuration::setCustomUserAgent("Chatwee Wordpress Plugin 2.1.2");

    if($chatwee_settings["disable_offline_users"] === true && $chatwee_settings["enable_sso"] === true && is_user_logged_in() === false && ChatweeV2_Session::isSessionSet() === true) {
        chatwee_sso_logout();
    }

    try {
        if($chatwee_settings["enable_sso"] === true && is_user_logged_in() === true) {
            if(ChatweeV2_Session::isSessionSet() === false) {
                chatwee_sso_login(wp_get_current_user());
            } else {
                $sessionId = ChatweeV2_Session::getSessionId();

                $isSessionValid = ChatweeV2_SsoUser::validateSession(Array(
                    "sessionId" => $sessionId
                ));

                if($isSessionValid === false) {
                    chatwee_sso_login(wp_get_current_user());
                }
            }
        }
    } catch(Exception $exception) {
        chatwee_add_log("Error while reinitializng SSO session: " . $exception->getMessage());
    }
}

function chatwee_init_handler() {
    if(wp_doing_ajax() === true) {
        return false;
    }

    chatwee_initialize();
}

add_action("init", "chatwee_init_handler");

function chatwee_render() {
    $chatwee_settings = get_option("chatwee_settings");

    if(is_user_logged_in() === false && $chatwee_settings["disable_offline_users"] === true) {
        return false;
    }
    if(chatwee_is_page_enabled() === true) {
        $chatwee_settings = get_option("chatwee_settings");

        echo $chatwee_settings["chatwee_script"];
    }
}

function chatwee_wp_footer_handler() {
    $chatwee_settings = get_option("chatwee_settings");

    chatwee_render();
}

add_action("wp_footer", "chatwee_wp_footer_handler");

function chatwee_sso_login($user) {
    $chatwee_user_id = get_user_meta($user->ID, "chatwee_v2_user_id_" . ChatweeV2_Configuration::getChatId(), true);

    if(!$chatwee_user_id) {
        $chatwee_user_id = chatwee_sso_register($user);
    }

    if (!isset($chatwee_user_id) || strlen($chatwee_user_id) == 0){
        chatwee_add_log("Error caused by empty or null chatwee_user_id property");
        return;
    }

    try	{
        ChatweeV2_SsoUser::edit(Array(
            "userId" => $chatwee_user_id,
            "login" => $user->display_name ? $user->display_name : $user->user_login,
            "avatar" => chatwee_get_avatar_url($user->ID),
            "isAdmin" => chatwee_does_user_belong_to_moderator_group($user->ID) || chatwee_if_user_is_moderator($user->ID)
        ));
    } catch(Exception $exception) {
        $chatwee_user_id = chatwee_sso_register($user);
        chatwee_add_log("Error while executing chatwee_sso_edit: " . $exception->getMessage());
    }

    try {
        ChatweeV2_SsoManager::loginUser(Array(
            "userId" => $chatwee_user_id
        ));
    } catch(Exception $exception) {
        chatwee_add_log("Error while executing chatwee_sso_login: " . $exception->getMessage());
    }
}

function chatwee_remote_login_handler($user_login, $user) {
    $chatwee_settings = get_option("chatwee_settings");

    chatwee_sso_login($user);
}

if($chatwee_settings["enable_sso"] === true) {
    add_action("wp_login", "chatwee_remote_login_handler", 10, 2);
}

function chatwee_sso_logout() {
    $chatwee_settings = get_option("chatwee_settings");

    try {
        ChatweeV2_SsoManager::logoutUser();
    } catch(Exception $exception) {
        chatwee_add_log("Error while executing chatwee_sso_logout: " . $exception->getMessage());
    }
}

function chatwee_remote_logout_handler() {
    $chatwee_settings = get_option("chatwee_settings");

    chatwee_sso_logout();
}

if($chatwee_settings["enable_sso"] === true) {
    add_action("wp_logout", "chatwee_remote_logout_handler", 10, 2);
}

function chatwee_sso_register($user) {

    $username = $user->display_name ? $user->display_name : $user->user_login;

    $chatwee_settings = get_option("chatwee_settings");

    try {
        $user_id = ChatweeV2_SsoManager::registerUser(Array(
            "login" => $username,
            "avatar" => chatwee_get_avatar_url($user->ID),
            "isAdmin" => chatwee_does_user_belong_to_moderator_group($user->ID) || chatwee_if_user_is_moderator($user->ID)
        ));

        update_user_meta($user->ID, "chatwee_v2_user_id_" . ChatweeV2_Configuration::getChatId(), $user_id);

        return $user_id;
    } catch(Exception $exception) {
        chatwee_add_log("Error while executing chatwee_sso_register: " . $exception->getMessage());
        return null;
    }
}

function chatwee_user_register_handler($user_id) {
    $user = get_user_by("id", $user_id);

    chatwee_sso_register($user);
}

if($chatwee_settings["enable_sso"] === true) {
    add_action("user_register", "chatwee_user_register_handler", 10, 1);
}

function chatwee_does_user_belong_to_moderator_group($user_id) {
    $chatwee_settings = get_option("chatwee_settings");
    $user = new WP_User($user_id);

    if (!empty($user->roles) && is_array($user->roles) && is_array($chatwee_settings["moderator_roles"])) {
        foreach ($user->roles as $role) {
            if (in_array($role, $chatwee_settings["moderator_roles"])) {
                return true;
            }
        }
    }
    return false;
}

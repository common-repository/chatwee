<?php

require_once(dirname( __FILE__ ) . "/lib/ChatweeV2_SDK/Chatwee/DataSanity.php");
require_once(dirname( __FILE__ ) . "/lib/ChatweeV2_SDK/Chatwee/FormAttributes.php");

if (!defined('ABSPATH')) exit;

if(isSet($_POST)) {

    if (isSet($_POST["chatwee_general_submit"])) {
        chatwee_save_general_settings();
    }
    if (isSet($_POST["chatwee_sso_submit"])) {
        chatwee_save_sso_settings();
    }
    if (isSet($_POST["chatwee_display_properties_submit"])) {
        chatwee_save_display_properties_settings();
    }
    if (isSet($_POST["chatwee_export_configuration_submit"])) {
        chatwee_export_configuration();
    }
}

function chatwee_save_display_properties_settings() {
    $chatwee_settings = get_option("chatwee_settings");

    $displayList = ChatweeV2_FormAttributes::displayList();

    $list = array();
    if (isset($_POST["categories_to_display"])) {
        $list = ChatweeV2_DataSanity::sanitizeList($_POST["categories_to_display"]);
    }
    $result = ChatweeV2_DataSanity::validateListAgainstValues($list, $displayList);

    if ($result) {
        $chatwee_settings["categories_to_display"] = $list;
        update_option("chatwee_settings", $chatwee_settings);
    }
}

function chatwee_save_general_settings() {
    $chatwee_settings = get_option("chatwee_settings");
    $chatwee_settings["chatwee_script"] = ChatweeV2_DataSanity::sanitizeScript($_POST["chatwee_script"]) ;
    $chatwee_settings["disable_offline_users"] = isSet($_POST["disable_offline_users"]) ? true : false;
    if(ChatweeV2_DataSanity::validateTag($chatwee_settings["chatwee_script"])) {
        update_option("chatwee_settings", $chatwee_settings);
    }
}

function chatwee_save_sso_settings() {
    $chatwee_settings = get_option("chatwee_settings");

    $enableSSO= isSet($_POST["enable_sso"]) ? true : false;
    $apiUrl = sanitize_text_field($_POST["api_url"]);
    $chatId = sanitize_text_field($_POST["chat_id"]);
    $clientKey = sanitize_text_field($_POST["client_key"]);

    $roles = ChatweeV2_FormAttributes::moderatorRoles();

    $list = array();
    if (isset($_POST["moderator_roles"])) {
        $list = ChatweeV2_DataSanity::sanitizeList($_POST["moderator_roles"]);
    }


    if (ChatweeV2_DataSanity::validateUrl($apiUrl)
        && ChatweeV2_DataSanity::validateApiKey($chatId)
        && ChatweeV2_DataSanity::validateApiKey($clientKey)
        && ChatweeV2_DataSanity::validateListAgainstValues($list, $roles)) {

        $chatwee_settings["enable_sso"] = $enableSSO;
        $chatwee_settings["api_url"] = $apiUrl;
        $chatwee_settings["chat_id"] = $chatId;
        $chatwee_settings["client_key"] = $clientKey;
        $chatwee_settings["moderator_roles"] = $list;
        update_option("chatwee_settings", $chatwee_settings);
    }
}

function chatwee_export_configuration() {
    $chatwee_settings = get_option("chatwee_settings");
    $keys = array(
        "chatwee_script",
        "disable_offline_users",
        "categories_to_display",
        "enable_sso",
        "chat_id",
        "client_key",
        "moderator_roles"
    );
    $settings = Array();
    foreach($keys as $key) {
        $settings[$key] = $chatwee_settings[$key];
    }

    $logs = chatwee_get_logs();

    $file = dirname( __FILE__ ) . "/chatwee_config.json";
    $current = @file_get_contents($file);
    $current = json_encode($settings) . json_encode($logs);
    file_put_contents($file, $current);

    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=' . basename($file));
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . filesize($file));
    readfile($file);
    exit;
}

function chatwee_get_role_names() {
    global $wp_roles;

    if (!isset($wp_roles)) {
        $wp_roles = new WP_Roles();
    }

    return $wp_roles->get_names();
}

function chatwee_get_logs() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'chatwee_log';

    $logs = $wpdb->get_results("SELECT * FROM " . $table_name . " ORDER BY log_id DESC LIMIT 50");

    return $logs;
}

function chatwee_admin() {

    $chatwee_settings = get_option("chatwee_settings");

    ?>

    <div id="chatwee-admin" class="wrap">
        <div id="header">
            <a href="https://chatwee.com" class="chatwee-logo"></a>
            <h2>WordPress Chat by Chatwee</h2>
        </div>

        <div class="chatwee-content-box chatwee-main-box">

            <div class="nav-tab-wrapper">
                <label class="nav-tab tab-switch" data-tab-key="general">General settings</label>
                <label class="nav-tab tab-switch" data-tab-key="display_properties">Display properties</label>
                <label class="nav-tab tab-switch" data-tab-key="advanced_display">Advanced display properties</label>
                <label class="nav-tab tab-switch" data-tab-key="sso">Single Sign-On</label>
                <label class="nav-tab tab-switch" data-tab-key="moderators">Moderators</label>
                <label class="nav-tab tab-switch" data-tab-key="logs">Logs</label>
            </div>


            <div class="chatwee-options-section" data-tab-key="general">
                <div class="chatwee-options-section-content">
                    <form method="post">
                        <table class="form-table chatwee-options-table">
                            <tr>
                                <th>
                                    <label for="chatwee_script">Installation code</label>
                                </th>
                                <td>
                                    <textarea id="chatwee_script" name="chatwee_script"><?php echo $chatwee_settings["chatwee_script"] ?></textarea>
                                    <p class="chatwee-option-info">Paste the Chatwee installation code above, depending on the version you want to use. Get the code in your <a href="https://chatwee.com/login-form/v2" target="_blank">Chatwee Dashboard</a>. If you don’t have a Chatwee account yet, please <a target="_blank" href="https://chatwee.com/register-form/v2">sign up for free.</a></p>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="disable_offline_users">Show only for logged-in users </label>
                                </th>
                                <td>
                                    <input type="checkbox" id="disable_offline_users" name="disable_offline_users" value="1" <?php if($chatwee_settings["disable_offline_users"] === true) echo "checked"; ?> />
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <input type="submit" name="chatwee_general_submit" class="button-primary" value="Save changes" />
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="chatwee-options-section" data-tab-key="display_properties">
                <div class="chatwee-options-section-content">
                    <form method="post">
                        <p class="chatwee-option-info">This section allows you to determine the page categories, where you want to display the chat. </p>
                        <table class="form-table chatwee-options-table">
                            <tr>
                                <th>
                                    <label>Display on the following page categories:</label>
                                </th>
                                <td>
                                    <div class="chatwee-option-wrapper">
                                        <input type="checkbox" id="display_categories_main_page" name="categories_to_display[]" value="main_page" <?php if (is_array($chatwee_settings["categories_to_display"]) && in_array("main_page", $chatwee_settings["categories_to_display"])) echo "checked"; ?> />
                                        <label for="display_categories_main_page">Main page</label>
                                    </div>
                                    <div class="chatwee-option-wrapper">
                                        <input type="checkbox" id="display_categories_search_page" name="categories_to_display[]" value="search_page" <?php if (is_array($chatwee_settings["categories_to_display"]) && in_array("search_page", $chatwee_settings["categories_to_display"])) echo "checked"; ?> />
                                        <label for="display_categories_search_page">Search page</label>
                                    </div>
                                    <div class="chatwee-option-wrapper">
                                        <input type="checkbox" id="display_categories_archive_page" name="categories_to_display[]" value="archive_page" <?php if (is_array($chatwee_settings["categories_to_display"]) && in_array("archive_page", $chatwee_settings["categories_to_display"])) echo "checked"; ?> />
                                        <label for="display_categories_archive_page">Archive page</label>
                                    </div>
                                    <div class="chatwee-option-wrapper">
                                        <input type="checkbox" id="display_categories_post_page" name="categories_to_display[]" value="post_page" <?php if (is_array($chatwee_settings["categories_to_display"]) && in_array("post_page", $chatwee_settings["categories_to_display"])) echo "checked"; ?> />
                                        <label for="display_categories_post_page">Post page</label>
                                    </div>
                                    <div class="chatwee-option-wrapper">
                                        <input type="checkbox" id="display_categories_single_page" name="categories_to_display[]" value="single_page" <?php if (is_array($chatwee_settings["categories_to_display"]) && in_array("single_page", $chatwee_settings["categories_to_display"])) echo "checked"; ?> />
                                        <label for="display_categories_single_page">Single page</label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <input type="submit" name="chatwee_display_properties_submit" class="button-primary" value="Save changes" />
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="chatwee-options-section" data-tab-key="advanced_display">
                <div class="chatwee-options-section-content">
                    <p class="chatwee-option-info"> This section allows you to display the chat on particular pages and posts.</p>

                    <div id="page_picker_wrapper"></div>

                    <div id="page_list_wrapper"></div>

                </div>
            </div>

            <div class="chatwee-options-section" data-tab-key="sso">
                <div class="chatwee-options-section-content">
                    <form method="post">
                        <table class="form-table chatwee-options-table">
                            <tr>
                                <th>
                                    <label for="enable_sso">Enable SSO login</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="enable_sso" name="enable_sso" value="1" <?php if($chatwee_settings["enable_sso"] === true) echo "checked"; ?> />
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="api_url">API URL</label>
                                </th>
                                <td>
                                    <input type="text" id="api_url" name="api_url" value="<?php echo (isset($chatwee_settings['api_url'])?$chatwee_settings['api_url']:'') ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="chat_id">Chat ID</label>
                                </th>
                                <td>
                                    <input type="text" id="chat_id" name="chat_id" value="<?php echo $chatwee_settings['chat_id'] ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label for="client_key">Client API Key</label>
                                </th>
                                <td>
                                    <input type="text" id="client_key" name="client_key" value="<?php echo $chatwee_settings['client_key'] ?>"/>
                                </td>
                            </tr>
                            <tr>
                                <th>
                                    <label>Turn particular users into moderators</label>
                                </th>
                                <td>
                                    <?php
                                    $roles = chatwee_get_role_names();
                                    foreach($roles as $roleKey => $roleName) {
                                        $checkedClause = is_array($chatwee_settings["moderator_roles"]) && in_array($roleKey, $chatwee_settings["moderator_roles"]) ? "checked" : "";
                                        echo "<div class='chatwee-option-wrapper'>";
                                        echo "<input type='checkbox' value='$roleKey' name='moderator_roles[]' id='moderator_roles_$roleKey' $checkedClause/>";
                                        echo "<label for='moderator_roles_$roleKey'>$roleName</label>";
                                        echo "</div>";
                                    }
                                    ?>
                                    <p class="chatwee-option-info">This section allows you to turn all the users having the particular role on your WordPress site into chat moderators simply by ticking the relevant box.</p>
                                </td>
                            </tr>
                            <tr>
                                <th></th>
                                <td>
                                    <input type="submit" name="chatwee_sso_submit" class="button-primary" value="Save changes" />
                                </td>
                            </tr>
                        </table>
                    </form>
                </div>
            </div>

            <div class="chatwee-options-section" data-tab-key="moderators">
                <div class="chatwee-options-section-content">
                    <p class="chatwee-option-info">The list below shows all the moderators you appointed, who are directly connected to your WordPress user database. The authorization remains active for as long as the Single Sign-on feature is enabled. Please note that you can also appoint moderators in your <a href="https://client.chatwee.com/" target="_blank">Chatwee Dashboard</a> if you wish to employ other authorization methods. Here, please enter the user name of the person you wish to appoint as a moderator into the box below.</p>

                    <div id="user_picker_wrapper"></div>

                    <div id="user_list_wrapper"></div>

                </div>
            </div>

            <div class="chatwee-options-section" data-tab-key="logs">
                <div class="chatwee-options-section-content">
                    <p class="chatwee-option-info">The list below shows errors that occurred during the execution of plugin tasks (up to 50 most recent ones).</p>

                    <?php

                    $logs = chatwee_get_logs();
                    if(count($logs) > 0) {
                        ?>
                        <form method="post">
                            <table>
                                <tr>
                                    <th></th>
                                    <td>
                                        <input type="submit" name="chatwee_export_configuration_submit" class="button-primary" value="Export configuration" />
                                    </td>
                                </tr>
                            </table>
                        </form>
                        <table class="chatwee-log-table">
                            <thead>
                            <tr>
                                <th class="time-cell">Log time</th>
                                <th class="message-cell">Log message</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php
                            $logs = chatwee_get_logs();
                            for($i = 0; $i < count($logs); $i++) {
                                echo "<tr><td class='time-cell'>" . $logs[$i]->log_time . "</td><td class='message-cell'>" . $logs[$i]->log_message . "</td></tr>";
                            }
                            ?>
                            </tbody>
                        </table>

                    <?php } else { ?>

                        <div class="empty-log-box">The log is empty</div>

                    <?php } ?>
                </div>
            </div>
        </div>



        <div class="chatwee-content-box chatwee-side-box">
            <a href="https://blog.chatwee.com" target="_blank">
                <img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/blog.png'; ?>">
            </a>
        </div>
        <div class="chatwee-content-box chatwee-side-box">
            <a href="https://chatwee.com/pricing" target="_blank">
                <img src="<?php echo plugin_dir_url( __FILE__ ) . 'images/upgrade_now.png'; ?>">
            </a>
        </div>
    </div>

    <?php
}


function chatwee_create_menu() {
    add_menu_page('Chatwee', 'Chatwee', 'administrator', 'chatwee_admin', 'chatwee_admin',  plugins_url( '/images/chatwee_icon.svg',__FILE__));
}

add_action('admin_menu', 'chatwee_create_menu');


function chatwee_admin_style($hook) {
    if($hook != "toplevel_page_chatwee_admin") {
        return;
    }

    wp_register_style('chatwee_admin_css_main', plugin_dir_url(__FILE__) . 'css/chatwee-admin.css', false, 'CFFVER');
    wp_enqueue_style('chatwee_admin_css_main');
    wp_register_style('chatwee_admin_css_toolkit', plugin_dir_url(__FILE__) . 'css/toolkit.css', false, 'CFFVER');
    wp_enqueue_style('chatwee_admin_css_toolkit');
}

add_action('admin_enqueue_scripts', 'chatwee_admin_style');

function chatwee_admin_scripts($hook) {
    if($hook != "toplevel_page_chatwee_admin") {
        return;
    }

    wp_register_script('chatwee_admin_script_main', plugin_dir_url(__FILE__) . 'js/chatwee-admin.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_main');
    wp_register_script('chatwee_admin_script_user_picker', plugin_dir_url(__FILE__) . 'js/toolkit/UserPicker.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_user_picker');
    wp_register_script('chatwee_admin_script_user_picker_item', plugin_dir_url(__FILE__) . 'js/toolkit/UserPickerItem.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_user_picker_item');
    wp_register_script('chatwee_admin_script_user_list', plugin_dir_url(__FILE__) . 'js/toolkit/UserList.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_user_list');
    wp_register_script('chatwee_admin_script_user_list_item', plugin_dir_url(__FILE__) . 'js/toolkit/UserListItem.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_user_list_item');

    wp_register_script('chatwee_admin_script_page_picker', plugin_dir_url(__FILE__) . 'js/toolkit/PagePicker.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_page_picker');
    wp_register_script('chatwee_admin_script_page_picker_item', plugin_dir_url(__FILE__) . 'js/toolkit/PagePickerItem.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_page_picker_item');
    wp_register_script('chatwee_admin_script_page_list', plugin_dir_url(__FILE__) . 'js/toolkit/PageList.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_page_list');
    wp_register_script('chatwee_admin_script_page_list_item', plugin_dir_url(__FILE__) . 'js/toolkit/PageListItem.js', false, 'CFFVER');
    wp_enqueue_script('chatwee_admin_script_page_list_item');
}

add_action('admin_enqueue_scripts', 'chatwee_admin_scripts');

function chatwee_search_user() {
    $search = '*'.sanitize_text_field($_POST['search_name']).'*'	;

    $user_query = new WP_User_Query(Array(
        'search' => $search,
        'search_columns' => Array(
            'user_login',
            'user_nicename',
            'user_email',
            'user_url'
        ),
        'number' => 10
    ));

    $users_found = $user_query->get_results();

    echo json_encode($users_found);
    exit;
}

add_action('wp_ajax_chatwee_admin_search_user', 'chatwee_search_user');

function chatwee_search_page() {
    global $wpdb;
    $search_name = sanitize_text_field($_POST['search_name']);
    $pages_query = $wpdb->get_results(
        $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_title LIKE '%%" . $search_name . "%%' AND post_status = 'publish' AND (post_type = 'post' OR post_type = 'page') LIMIT 10", Array())
    );

    $pages_found = $pages_query;

    echo json_encode($pages_found);
    exit;
}

add_action('wp_ajax_chatwee_admin_search_page', 'chatwee_search_page');

function chatwee_add_moderator() {
    global $wpdb;

    $user_id = sanitize_text_field($_POST["user_id"]);

    $table_name = $wpdb->prefix . 'chatwee_moderators';

    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $table_name . ' WHERE user_id = %d', $user_id));

    $wpdb->insert($table_name, Array('user_id' => $user_id), Array('%d'));

    echo json_encode(true);
    exit;
}

add_action('wp_ajax_chatwee_admin_add_moderator', 'chatwee_add_moderator');

function chatwee_remove_moderator() {
    global $wpdb;

    $user_id = sanitize_text_field($_POST["user_id"]);

    $table_name = $wpdb->prefix . 'chatwee_moderators';

    $wpdb->delete($table_name, Array('user_id' => $user_id));

    echo json_encode(true);
    exit;
}

add_action('wp_ajax_chatwee_admin_remove_moderator', 'chatwee_remove_moderator');

function chatwee_get_moderators() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'chatwee_moderators';

    $users = $wpdb->get_results("SELECT * FROM " . $table_name);

    $result = Array();
    foreach($users as $user) {
        $userdata = get_userdata($user->user_id);
        if($userdata) {
            array_push($result, $userdata);
        }
    }

    echo json_encode($result);
    exit;
}

add_action("wp_ajax_chatwee_admin_get_moderators", "chatwee_get_moderators");


function chatwee_add_page() {
    global $wpdb;
    $page_id = sanitize_text_field($_POST["page_id"]);
    $table_name = $wpdb->prefix . "chatwee_pages_to_display";
    if (ChatweeV2_DataSanity::validateNumber($page_id)) {
        $wpdb->insert($table_name, Array("page_id" => $page_id), Array("%d"));
        echo json_encode(true);
    }else {
        echo json_encode(false);
    }
    exit;
}

add_action('wp_ajax_chatwee_admin_add_page', 'chatwee_add_page');

function chatwee_remove_page() {
    global $wpdb;

    $page_id = sanitize_text_field($_POST["page_id"]);

    $table_name = $wpdb->prefix . "chatwee_pages_to_display";
    if ($page_id) {
        $wpdb->delete($table_name, Array("page_id" => $page_id));
        echo json_encode(true);
    }else{
        echo json_encode(false);
    }
    exit;
}

add_action('wp_ajax_chatwee_admin_remove_page', 'chatwee_remove_page');

function chatwee_get_pages_to_display() {
    global $wpdb;

    $table_name = $wpdb->prefix . "chatwee_pages_to_display";

    $pages = $wpdb->get_results("SELECT * FROM " . $table_name);

    $result = Array();
    foreach($pages as $page) {
        $pagedata = get_post($page->page_id);
        if($pagedata) {
            array_push($result, $pagedata);
        }
    }

    echo json_encode($result);
    exit;
}

add_action("wp_ajax_chatwee_admin_get_pages_to_display", "chatwee_get_pages_to_display");

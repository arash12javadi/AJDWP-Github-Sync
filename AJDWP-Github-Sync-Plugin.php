<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Plugin Name:       AJDWP-Github-Sync-Plugin
 * Plugin URI:        https://github.com/arash12javadi/
 * Description:       This plugin facilitates the installation and continuous updating of GitHub themes and plugins using the specified GitHub username, repository, and branch. Additionally, it seamlessly installs and maintains the latest versions of all AJDWP plugins and themes with just a single click. Enjoy the convenience! :)
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Arash Javadi
 * Author URI:        https://arashjavadi.com/  
 */

//  ini_set('error_log', 'C:/wamp64/logs/php_error.log');
// ini_set('display_errors', 1);
// define('WP_DEBUG', true);
// define('WP_DEBUG_LOG', true);

//__________________________________________________________________________//
//			Theme Update From Github Repo
//__________________________________________________________________________//

// Add a menu item to the admin menu
add_action('admin_menu', 'github_plugin_menu');

function github_plugin_menu() {
    add_menu_page(
        'GitHub Plugin Settings',
        'GitHub Sync',
        'manage_options',
        'github-plugin-settings',
        'github_plugin_page'
    );
}

// Callback function for the settings page
function github_plugin_page() {
    ?>
    <div class="wrap">
        <h2>GitHub Sync for theme and plugin</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('github_plugin_settings');
            do_settings_sections('github-plugin-settings');
            wp_nonce_field('github_plugin_nonce_action', 'github_plugin_nonce');
            submit_button('Install / Update');
            ?>
        </form>
        <form method="post" action="options.php">
            <?php
            settings_fields('AJDWP_plugin_settings');
            do_settings_sections('AJDWP-plugin-settings');
            wp_nonce_field('AJDWP_plugin_nonce_action', 'AJDWP_plugin_nonce');
            submit_button('Install / Update');
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action('admin_init', 'github_plugin_settings');

function github_plugin_settings() {
    register_setting('github_plugin_settings', 'github_username', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'github_repository', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'github_Branch', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'theme_or_plugin', 'sanitize_text_field');

    add_settings_section(
        'github_plugin_section',
        'GitHub Settings',
        'github_plugin_section_callback',
        'github-plugin-settings'
    );

    add_settings_field(
        'github_username',
        'GitHub Username',
        'github_username_callback',
        'github-plugin-settings',
        'github_plugin_section'
    );

    add_settings_field(
        'github_repository',
        'GitHub Repository',
        'github_repository_callback',
        'github-plugin-settings',
        'github_plugin_section'
    );

    add_settings_field(
        'github_Branch',
        'GitHub Branch',
        'github_Branch_callback',
        'github-plugin-settings',
        'github_plugin_section'
    );

    add_settings_field(
        'theme_or_plugin',
        'Theme or Plugin',
        'theme_or_plugin_callback',
        'github-plugin-settings',
        'github_plugin_section'
    );
}

function github_plugin_section_callback() {
    echo 'Enter your GitHub details for the theme or plugins you would like to install or update below:';
}

function github_username_callback() {
    $github_username = get_option('github_username');
    echo '<input type="text" name="github_username" value="' . esc_attr($github_username) . '" />';
}

function github_repository_callback() {
    $github_repository = get_option('github_repository');
    echo '<input type="text" name="github_repository" value="' . esc_attr($github_repository) . '" />';
}

function github_Branch_callback() {
    $github_Branch = get_option('github_Branch', 'main'); // Set the default value to 'main'
    echo '<input type="text" name="github_Branch" value="' . esc_attr($github_Branch) . '" />';
}

function theme_or_plugin_callback() {
    $types = get_option('theme_or_plugin');
    $selected_type = array('Plugin', 'Theme'); // Replace with your actual plugin names

    echo '<select name="theme_or_plugin">';
    foreach ($selected_type as $type) {
        echo '<option value="' . esc_attr($type) . '" ' . selected($types, $type, false) . '>' . esc_html($type) . '</option>';
    }
    echo '</select>';
}


//--------------------------- AJDWP Plugins Section ---------------------------//
// Register settings
add_action('admin_init', 'AJDWP_plugin_settings');

function AJDWP_plugin_settings() {
    register_setting('AJDWP_plugin_settings', 'AJDWP_select_plugins');
    add_settings_section(
        'AJDWP_plugins_section',
        'AJDWP Theme and Plugins',
        'AJDWP_plugins_section_callback',
        'AJDWP-plugin-settings'
    );
    add_settings_field(
        'AJDWP_select_plugins',
        'Select needed options to be installed in once:',
        'AJDWP_select_plugins_callback',
        'AJDWP-plugin-settings',
        'AJDWP_plugins_section'
    );
}

function AJDWP_plugins_section_callback() {
    echo 'Select the theme and the plugins that you would like to be installed:';
}

function AJDWP_select_plugins_callback() {
    $selected_plugins = get_option('AJDWP_select_plugins');
    $all_plugins = array('Hello-Elementor-Child-theme', 'AJDWP-floating-login-form', 'AJDWP-Navbar-Sidebar', 'AJDWP-page-template-Styler', 'AJDWP-Theme-accessories', 'AJDWP-user-profile', 'AJDWP-user-social-media'); // Replace with your actual plugin names

    if (!is_array($selected_plugins)) {
        $selected_plugins = array();
    }

    foreach ($all_plugins as $plugin) {
        echo '<label><input type="checkbox" name="AJDWP_select_plugins[]" value="' . esc_attr($plugin) . '" ';
        
        if (is_array($selected_plugins) && in_array($plugin, $selected_plugins)) {
            echo 'checked="checked"';
        }
        
        echo '> ' . esc_html($plugin) . '</label><br>';        
    }
}


//--------------------------- Global Variables - GitHub inserted details ---------------------------//
$AJDWP_github_user       = get_option('github_username');    // Get the GitHub username from the settings
$AJDWP_github_repo       = get_option('github_repository');  // Get the GitHub repository from the settings
$AJDWP_github_branch     = get_option('github_Branch');      // Get the GitHub Branch from the settings


//--------------------------- Install theme or plugin with inserted details of Github ---------------------------//
// Nonce verification and capability check for GitHub inserted details
add_action('admin_init', 'github_plugin_nonce_check');
function github_plugin_nonce_check() {
    if (isset($_POST['github_plugin_nonce']) && wp_verify_nonce($_POST['github_plugin_nonce'], 'github_plugin_nonce_action')) {
        // Process form data
        if (current_user_can('manage_options')) {

            if(get_option('theme_or_plugin') === 'Theme'){
                global $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch;
                include_once('theme_installer.php');
                install_github_theme('theme', $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch);
            }

            if(get_option('theme_or_plugin') === 'Plugin'){
                include_once('plugin_installer.php');
                install_github_plugin($AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch);
            }

        }
    }
}

//--------------------------- Keep the inserted details of Github Update ---------------------------//
if(get_option('theme_or_plugin') === 'Theme'){
    include_once('theme_updater.php');
}

if(get_option('theme_or_plugin') === 'Plugin'){
    require_once( 'plugin_updater.php' );
    if ( is_admin() ) {
        $plugin_file = $AJDWP_github_repo.'-Plugin/'.$AJDWP_github_repo.'.php';
        $plugin_slug = plugin_basename($plugin_file);
        new AJDWP_GitHubPluginUpdater( $plugin_slug, $AJDWP_github_user, $AJDWP_github_repo );
    }
}


//--------------------------- Install selected AJDWP theme or plugins ---------------------------//
// Nonce verification and capability check for AJDWP theme and Plugins
add_action('admin_init', 'AJDWP_plugin_nonce_check');

function AJDWP_plugin_nonce_check() {
    if (isset($_POST['AJDWP_plugin_nonce']) && wp_verify_nonce($_POST['AJDWP_plugin_nonce'], 'AJDWP_plugin_nonce_action')) {
        // Process form data
        if (current_user_can('manage_options')) {
            $selected_options = get_option('AJDWP_select_plugins');
            $theme_exsists = wp_get_theme('Hello-Elementor-Child-Theme');
            foreach($selected_options as $option){

                $plugin_exsists = WP_PLUGIN_DIR . '/'.$option.'-Plugin/'.$option.'.php';
                if($option === 'Hello-Elementor-Child-theme' && !$theme_exsists->exists()){
                    include_once('theme_installer.php');
                    install_github_theme('theme', 'arash12javadi', 'Hello-Elementor-Child', 'Theme');
                }elseif(!file_exists($plugin_exsists)){
                    include_once('plugin_installer.php');
                    install_github_plugin('arash12javadi', $option, 'Plugin');
                }

            }
        }
    }
}


//--------------------------- keep Update the selected AJDWP theme or plugins ---------------------------//
$selected_options = get_option('AJDWP_select_plugins');
foreach ($selected_options as $option) {

    if ($option === 'Hello-Elementor-Child-theme') {

        if (!function_exists('automatic_GitHub_theme_updater')) {

            add_filter('pre_set_site_transient_update_themes', 'automatic_GitHub_theme_updater', 101, 1);
            function automatic_GitHub_theme_updater($data)
            {
                $AJDWP_github_user = 'arash12javadi';
                $AJDWP_github_repo = 'Hello-Elementor-Child';
                $AJDWP_github_branch = 'Theme';
                $theme   = 'Hello-Elementor-Child-Theme'; // Folder name
                $current = wp_get_theme('Hello-Elementor-Child-Theme')->get('Version'); // Get the version of the current theme

                $file = @json_decode(@file_get_contents('https://api.github.com/repos/' . $AJDWP_github_user . '/' . $AJDWP_github_repo . '/releases/latest', false, stream_context_create(['http' => ['header' => "User-Agent: " . $AJDWP_github_user . "\r\n"]])));
                $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // Only return a response if the new version number is higher than the current version
                if (version_compare($update, $current, '>')) {
                    $data->response[$theme] = array(
                        'theme'       => $theme,
                        'new_version' => $update,
                        'url'         => 'https://github.com/' . $AJDWP_github_user . '/' . $AJDWP_github_repo,
                        'package'     => 'https://codeload.github.com/' . $AJDWP_github_user . '/' . $AJDWP_github_repo . '-' . $AJDWP_github_branch . '/zip/refs/heads/' . $AJDWP_github_branch,
                    );
                }
                return $data;
            }

        }

    } else {

        require_once('plugin_updater.php');
        if (is_admin()) {
            $plugin_file = $option . '-Plugin/' . $option . '.php';
            $plugin_slug = plugin_basename($plugin_file);
            new AJDWP_GitHubPluginUpdater($plugin_slug, 'arash12javadi', $option);
        }

    }

}
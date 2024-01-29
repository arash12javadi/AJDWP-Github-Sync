<?php

if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Plugin Name:       AJDWP-Github_Updater
 * Plugin URI:        https://github.com/arash12javadi/
 * Description:       In the admin menu, locate the section titled 'GitHub Settings'. Effortlessly input your username, repository, and default branch name (usually 'main'). Verify updates by navigating to Dashboard -> Updates.
 * Version:           1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Arash Javadi
 * Author URI:        https://arashjavadi.com/  
 */


//__________________________________________________________________________//
//			Theme Update From Github Repo
//__________________________________________________________________________//

// Add a menu item to the admin menu
add_action('admin_menu', 'github_plugin_menu');

function github_plugin_menu() {
    add_menu_page(
        'GitHub Plugin Settings',
        'GitHub Settings',
        'manage_options',
        'github-plugin-settings',
        'github_plugin_page'
    );
}

// Callback function for the settings page
function github_plugin_page() {
    ?>
    <div class="wrap">
        <h2>GitHub Theme Updater</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('github_plugin_settings');
            do_settings_sections('github-plugin-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}


// Register settings
add_action('admin_init', 'github_plugin_settings');

function github_plugin_settings() {
    register_setting('github_plugin_settings', 'github_username');
    register_setting('github_plugin_settings', 'github_repository');
    register_setting('github_plugin_settings', 'github_Branch');

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
}

function github_plugin_section_callback() {
    echo 'Enter your GitHub username and repository below:';
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


if (!function_exists('plugin_automatic_GitHub_updater')) {
    add_filter('pre_set_site_transient_update_themes', 'plugin_automatic_GitHub_updater', 101, 1);

    function plugin_automatic_GitHub_updater($data) {
        $theme   = get_stylesheet(); // Folder name of the current theme
        $current = wp_get_theme()->get('Version'); // Get the version of the current theme

        $user = get_option('github_username'); // Get the GitHub username from the settings
        $repo = get_option('github_repository'); // Get the GitHub repository from the settings
        $Branch = get_option('github_Branch'); // Get the GitHub Branch from the settings

        $file = @json_decode(@file_get_contents('https://api.github.com/repos/' . $user . '/' . $repo . '/releases/latest', false, stream_context_create(['http' => ['header' => "User-Agent: " . $user . "\r\n"]])));
        $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Only return a response if the new version number is higher than the current version
        if (version_compare($update, $current, '>')) {
            $data->response[$theme] = array(
                'theme'       => $theme,
                'new_version' => $update,
                'url'         => 'https://github.com/' . $user . '/' . $repo,
                'package'     => 'https://codeload.github.com/' . $user . '/' . $repo . '/zip/refs/heads/'.$Branch,
            );
        }
        return $data;
    }
}

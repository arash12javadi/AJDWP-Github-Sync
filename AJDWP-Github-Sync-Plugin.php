<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Plugin Name:       AJDWP GitHub Sync Plugin
 * Plugin URI:        https://github.com/arash12javadi/
 * Description:       Easily install and keep GitHub-hosted themes and plugins up-to-date by specifying the GitHub username, repository, and branch. Also, install and update all AJDWP plugins and themes with a single click.
 * Version:           1.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Arash Javadi
 * Author URI:        https://arashjavadi.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 */

/* -------------------------------------------------------------
   1) ADMIN MENU: GITHUB PLUGIN/THEME SETTINGS + AJDWP PLUGIN SETTINGS
------------------------------------------------------------- */

add_action('admin_menu', 'github_plugin_menu');
function github_plugin_menu()
{
    add_menu_page(
        'GitHub Plugin Settings',
        'AJDWP GitHub Sync',
        'manage_options',
        'github-plugin-settings',
        'github_plugin_page',    // callback
        'dashicons-update-alt'
    );
}

/**
 * The "settings page" callback function.
 */
function github_plugin_page()
{
?>
    <div class="wrap">

        <!-- 1) Form for GitHub Settings (Single Plugin or Theme from user input) -->
        <form method="post" action="options.php">
            <?php
            settings_fields('github_plugin_settings');
            do_settings_sections('github-plugin-settings');
            // The nonce for the GitHub form
            wp_nonce_field('github_plugin_nonce_action', 'github_plugin_nonce');

            // The submit button named "install_github_plugins"
            submit_button(
                'Install / Update',
                'primary',
                'install_github_plugins'
            );
            ?>
        </form>

        <!-- 2) Form for AJDWP Plugin Settings (Multiple selection) -->
        <form method="post" action="options.php">
            <?php
            settings_fields('AJDWP_plugin_settings');
            do_settings_sections('AJDWP-plugin-settings');
            // The nonce for the AJDWP selection form
            wp_nonce_field('AJDWP_plugin_nonce_action', 'AJDWP_plugin_nonce');

            // The submit button named "install_selected_plugins"
            submit_button(
                'Save (Install Selected)',
                'primary',
                'install_selected_plugins'
            );
            ?>
        </form>

    </div>
<?php
}

/* -------------------------------------------------------------
   2) REGISTER / RENDER GITHUB PLUGIN SETTINGS
------------------------------------------------------------- */
add_action('admin_init', 'github_plugin_settings');
function github_plugin_settings()
{
    // Register & sanitize
    register_setting('github_plugin_settings', 'github_username', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'github_repository', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'github_Branch', 'sanitize_text_field');
    register_setting('github_plugin_settings', 'theme_or_plugin', 'sanitize_text_field');

    add_settings_section(
        'github_plugin_section',
        'GitHub Installer and Updater',
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
function github_plugin_section_callback()
{
    echo esc_html('Enter your GitHub details for the themes or plugins you would like to install or update below:');
}
function github_username_callback()
{
    $github_username = get_option('github_username', '');
    echo '<input type="text" name="github_username" value="' . esc_attr($github_username) . '" />';
}
function github_repository_callback()
{
    $github_repository = get_option('github_repository', '');
    echo '<input type="text" name="github_repository" value="' . esc_attr($github_repository) . '" />';
}
function github_Branch_callback()
{
    $github_Branch = get_option('github_Branch', 'main');
    echo '<input type="text" name="github_Branch" value="' . esc_attr($github_Branch) . '" />';
}
function theme_or_plugin_callback()
{
    $selected_type = get_option('theme_or_plugin', '');
    $types = ['Plugin', 'Theme'];
    echo '<select name="theme_or_plugin">';
    foreach ($types as $type) {
        echo '<option value="' . esc_attr($type) . '" ' . selected($selected_type, $type, false) . '>' . esc_html($type) . '</option>';
    }
    echo '</select>';
}

/* -------------------------------------------------------------
   3) REGISTER / RENDER AJDWP PLUGIN SELECTION SETTINGS
------------------------------------------------------------- */
add_action('admin_init', 'AJDWP_plugin_settings');
function AJDWP_plugin_settings()
{
    register_setting('AJDWP_plugin_settings', 'AJDWP_select_plugins', [
        'sanitize_callback' => 'AJDWP_sanitize_plugins_selection',
        'default' => [],
    ]);

    add_settings_section(
        'AJDWP_plugins_section',
        'AJDWP Theme and Plugins',
        'AJDWP_plugins_section_callback',
        'AJDWP-plugin-settings'
    );

    add_settings_field(
        'AJDWP_select_plugins',
        'Select needed options to be installed at once:',
        'AJDWP_select_plugins_callback',
        'AJDWP-plugin-settings',
        'AJDWP_plugins_section'
    );
}
function AJDWP_plugins_section_callback()
{
    echo esc_html('Select the theme and the plugins that you would like to be installed:');
}
function AJDWP_select_plugins_callback()
{
    $selected_plugins = get_option('AJDWP_select_plugins', []);
    $all_plugins = [
        'Hello-Elementor-Child-theme',
        'AJDWP-floating-login-form',
        'AJDWP-Navbar-Sidebar',
        'AJDWP-page-template-Styler',
        'AJDWP-Theme-accessories',
        'AJDWP-user-profile',
        'AJDWP-user-social-media',
        'AJDWP-SEO-Checklist',
    ];
    if (! is_array($selected_plugins)) {
        $selected_plugins = [];
    }
    foreach ($all_plugins as $plugin) {
        echo '<label><input type="checkbox" name="AJDWP_select_plugins[]" value="' . esc_attr($plugin) . '" ' .
            checked(in_array($plugin, $selected_plugins, true), true, false) . '> ' .
            esc_html($plugin) . '</label><br>';
    }
}
function AJDWP_sanitize_plugins_selection($input)
{
    if (! is_array($input)) {
        return [];
    }
    $allowed_plugins = [
        'Hello-Elementor-Child-theme',
        'AJDWP-floating-login-form',
        'AJDWP-Navbar-Sidebar',
        'AJDWP-page-template-Styler',
        'AJDWP-Theme-accessories',
        'AJDWP-user-profile',
        'AJDWP-user-social-media',
        'AJDWP-SEO-Checklist',
    ];
    return array_filter($input, function ($plugin) use ($allowed_plugins) {
        return in_array($plugin, $allowed_plugins, true);
    });
}

/* -------------------------------------------------------------
   4) LOAD UPDATERS IF user selected "Theme" or "Plugin"
------------------------------------------------------------- */
$AJDWP_github_user   = sanitize_text_field(get_option('github_username', 'default_user'));
$AJDWP_github_repo   = sanitize_text_field(get_option('github_repository', 'default_repo'));
$AJDWP_github_branch = sanitize_text_field(get_option('github_Branch', 'main'));

if (get_option('theme_or_plugin') === 'Theme') {
    include_once('includes/theme_updater.php');
}
if (get_option('theme_or_plugin') === 'Plugin') {
    include_once('includes/plugin_updater.php');
}

/* -------------------------------------------------------------
   5) HOOK 1: Process the FIRST FORM - GITHUB INSTALL/UPDATE
------------------------------------------------------------- */
add_action('admin_init', 'github_plugin_nonce_check');
function github_plugin_nonce_check()
{
    // Only proceed if the first form was submitted
    if (! isset($_POST['install_github_plugins'])) {
        return; // user did not submit the GitHub form
    }

    // Now check the nonce from the GitHub form
    if (
        isset($_POST['github_plugin_nonce'])
        && wp_verify_nonce($_POST['github_plugin_nonce'], 'github_plugin_nonce_action')
    ) {
        // Ensure current user can manage_options
        if (current_user_can('manage_options')) {
            global $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch;

            $type = get_option('theme_or_plugin');
            if ($type === 'Theme') {
                include_once('includes/theme_installer.php');
                install_github_theme('theme', $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch);
            } elseif ($type === 'Plugin') {
                include_once('includes/plugin_installer.php');
                install_github_plugin($AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch);
            }
        }
        // else, user lacks perms -> do nothing or error_log if you want
    }
    // else, nonce was invalid -> do nothing or error_log if you want
}

/* -------------------------------------------------------------
   6) HOOK 2: Process the SECOND FORM - "AJDWP Theme/Plugins" selection
------------------------------------------------------------- */
add_action('admin_init', 'AJDWP_plugin_nonce_check');
function AJDWP_plugin_nonce_check()
{
    // Only proceed if user clicked the second form's button
    if (! isset($_POST['install_selected_plugins'])) {
        return; // user didn't submit the second form
    }

    // Check the nonce from the second form
    if (
        isset($_POST['AJDWP_plugin_nonce'])
        && wp_verify_nonce($_POST['AJDWP_plugin_nonce'], 'AJDWP_plugin_nonce_action')
    ) {
        if (current_user_can('manage_options')) {
            $selected_options = get_option('AJDWP_select_plugins', []);
            if (is_array($selected_options) && ! empty($selected_options)) {
                $theme_exists = wp_get_theme('Hello-Elementor-Child-Theme');
                foreach ($selected_options as $option) {
                    $plugin_path = WP_PLUGIN_DIR . '/' . sanitize_title($option) . '-Plugin/' . sanitize_title($option) . '.php';
                    if ($option === 'Hello-Elementor-Child-theme' && ! $theme_exists->exists()) {
                        include_once('includes/theme_installer.php');
                        install_github_theme('theme', 'arash12javadi', 'Hello-Elementor-Child', 'Theme');
                    } elseif (! file_exists($plugin_path)) {
                        include_once('includes/plugin_installer.php');
                        install_github_plugin('arash12javadi', sanitize_title($option), 'Plugin');
                    } else {
                        error_log("The plugin or theme '{$option}' is already installed.");
                    }
                }
            } else {
                error_log('No valid options were selected for installation.');
            }
        } else {
            error_log('Current user does not have sufficient permissions to install themes/plugins.');
        }
    } else {
        // The AJDWP plugin nonce was invalid
        error_log('Nonce verification failed for AJDWP plugin/theme installation.');
    }
}

/* -------------------------------------------------------------
   7) Keep Update the selected AJDWP theme or plugins
------------------------------------------------------------- */
$selected_options = get_option('AJDWP_select_plugins');
if (is_array($selected_options)) {
    foreach ($selected_options as $option) {
        if ($option === 'Hello-Elementor-Child-theme') {
            include_once plugin_dir_path(__FILE__) . 'includes/theme_updater.php';
            add_filter('pre_set_site_transient_update_themes', function ($data) {
                $AJDWP_github_user   = 'arash12javadi';
                $AJDWP_github_repo   = 'Hello-Elementor-Child';
                $AJDWP_github_branch = 'Theme';
                $theme               = 'Hello-Elementor-Child-Theme';
                $current             = wp_get_theme($theme)->get('Version');

                $response = wp_remote_get(
                    'https://api.github.com/repos/' . esc_attr($AJDWP_github_user) . '/' . esc_attr($AJDWP_github_repo) . '/releases/latest',
                    ['timeout' => 30, 'headers' => ['User-Agent' => esc_attr($AJDWP_github_user)]]
                );
                if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                    error_log('Failed to fetch theme update data from GitHub.');
                    return $data;
                }
                $file = json_decode(wp_remote_retrieve_body($response));
                if (! empty($file->tag_name)) {
                    $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    if (version_compare($update, $current, '>')) {
                        $data->response[$theme] = [
                            'theme'       => $theme,
                            'new_version' => $update,
                            'url'         => 'https://github.com/' . esc_attr($AJDWP_github_user) . '/' . esc_attr($AJDWP_github_repo),
                            'package'     => 'https://codeload.github.com/' . esc_attr($AJDWP_github_user) . '/' . esc_attr($AJDWP_github_repo) . '/zip/refs/heads/' . esc_attr($AJDWP_github_branch),
                        ];
                    }
                }
                return $data;
            });
        } else {
            // Handle plugins
            include_once plugin_dir_path(__FILE__) . 'includes/plugin_updater.php';
            add_filter('pre_set_site_transient_update_plugins', function ($data) use ($option) {
                $github_user = 'arash12javadi';
                $plugin_slug = sanitize_title($option);
                $plugin_file = WP_PLUGIN_DIR . '/' . $plugin_slug . '-Plugin/' . $plugin_slug . '.php';

                if (! file_exists($plugin_file)) {
                    error_log("Plugin file not found: {$plugin_file}");
                    return $data;
                }
                $plugin_data = get_plugin_data($plugin_file, false, false);
                $current_version = preg_replace('/[^0-9]/', '', $plugin_data['Version']);

                $response = wp_remote_get(
                    'https://api.github.com/repos/' . $github_user . '/' . $plugin_slug . '/releases/latest',
                    ['timeout' => 30, 'headers' => ['User-Agent' => $github_user]]
                );
                if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
                    error_log('Failed to fetch plugin update data from GitHub.');
                    return $data;
                }
                $file = json_decode(wp_remote_retrieve_body($response));
                if (! empty($file->tag_name)) {
                    $update_version = preg_replace('/[^0-9]/', '', $file->tag_name);
                    if (version_compare($current_version, $update_version, '<')) {
                        $data->response[$plugin_slug . '-Plugin/' . $plugin_slug . '.php'] = [
                            'slug'        => $plugin_slug,
                            'new_version' => $update_version,
                            'url'         => 'https://github.com/' . $github_user . '/' . $plugin_slug,
                            'package'     => 'https://codeload.github.com/' . $github_user . '/' . $plugin_slug . '/zip/refs/heads/main',
                        ];
                    }
                }
                return $data;
            });
        }
    }
} else {
    error_log('AJDWP_select_plugins option is not an array or is missing.');
}

<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Installs a plugin from a GitHub repository.
 *
 * @param string $username   GitHub username.
 * @param string $repository GitHub repository name.
 * @param string $branch     Branch name to fetch from (default: main).
 */
function install_github_plugin($username, $repository, $branch) {
    // Sanitize inputs
    $username = sanitize_text_field($username);
    $repository = sanitize_text_field($repository);
    $branch = sanitize_text_field($branch);
    
    $plugin_slug = sanitize_title($repository);
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug;

    // Check if plugin is already installed
    if (is_dir($plugin_path)) {
        echo esc_html("Plugin '{$repository}' is already installed.");
        return;
    }

    // Construct the GitHub ZIP URL
    $zip_url = "https://github.com/{$username}/{$repository}/archive/refs/heads/{$branch}.zip";
    
    // Fetch the ZIP contents
    $response = wp_remote_get($zip_url, ['timeout' => 30]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        echo esc_html("Unable to download the ZIP file from {$zip_url}. Please verify the GitHub details.");
        return;
    }

    $zip_contents = wp_remote_retrieve_body($response);

    // Create a temporary file for the ZIP
    $temp_file = wp_tempnam();
    if (!$temp_file) {
        echo esc_html('Failed to create a temporary file.');
        return;
    }

    file_put_contents($temp_file, $zip_contents);

    // Extract the ZIP
    $zip = new ZipArchive;
    if ($zip->open($temp_file) === true) {
        $zip->extractTo(WP_PLUGIN_DIR);
        $zip->close();
        unlink($temp_file); // Cleanup the temp file

        // Construct the plugin file path for activation
        $plugin_file = "{$plugin_slug}-Plugin/{$plugin_slug}.php";
        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin_file)) {
            run_activate_plugin($plugin_file); // Activate the plugin
            echo esc_html("Plugin '{$repository}' has been installed and activated.");
        } else {
            echo esc_html("Plugin file not found: {$plugin_file}. Please verify the plugin structure.");
        }
    } else {
        echo esc_html('Unable to extract the ZIP file.');
        unlink($temp_file); // Cleanup the temp file
    }
}

/**
 * Activates a WordPress plugin.
 *
 * @param string $plugin Plugin path relative to the plugins directory.
 */
function run_activate_plugin($plugin) {
    $plugin = plugin_basename(trim($plugin));
    $current = (array) get_option('active_plugins', []);

    // Activate the plugin if not already active
    if (!in_array($plugin, $current, true)) {
        $current[] = $plugin;
        sort($current);
        update_option('active_plugins', $current);

        // Trigger activation hooks
        do_action('activate_plugin', $plugin);
        do_action('activate_' . $plugin);
        do_action('activated_plugin', $plugin);
    }
}

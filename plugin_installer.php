<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function install_github_plugin($username, $repository, $branch) {
    $plugin_slug = sanitize_title($repository);
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug;

    // Check if plugin is already installed
    if (is_dir($plugin_path)) {
        echo "Plugin '{$repository}' is already installed.";
        return;
    }

    // Construct the GitHub ZIP URL
    $zip_url = "https://github.com/{$username}/{$repository}/archive/refs/heads/{$branch}.zip";
    $zip_contents = @file_get_contents($zip_url);

    if (!$zip_contents) {
        echo "Unable to download the ZIP file from {$zip_url}. Please verify the GitHub details.";
        return;
    }

    // Create a temporary file for the ZIP
    $temp_file = tempnam(sys_get_temp_dir(), 'github_plugin_');
    if (!$temp_file) {
        echo 'Failed to create a temporary file.';
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
        } else {
            echo "Plugin file not found: {$plugin_file}. Please verify the plugin structure.";
        }
    } else {
        echo 'Unable to extract ZIP file.';
        unlink($temp_file); // Cleanup the temp file
    }
}

function run_activate_plugin($plugin) {
    $plugin = trim($plugin);
    $current = get_option('active_plugins');
    $plugin = plugin_basename($plugin);

    // Activate the plugin if not already active
    if (!in_array($plugin, $current)) {
        $current[] = $plugin;
        sort($current);
        do_action('activate_plugin', $plugin);
        update_option('active_plugins', $current);
        do_action('activate_' . $plugin);
        do_action('activated_plugin', $plugin);
    }

    return null;
}

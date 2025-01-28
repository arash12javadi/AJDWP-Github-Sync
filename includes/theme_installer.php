<?php  

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

/**
 * Installs a theme or plugin from a GitHub repository.
 *
 * @param string $type       Type of installation ('theme' or 'plugin').
 * @param string $username   GitHub username.
 * @param string $repository GitHub repository name.
 * @param string $branch     Branch name to fetch from (default: main).
 */
function install_github_theme($type, $username, $repository, $branch) {
    // Sanitize inputs
    $type = sanitize_text_field($type);
    $username = sanitize_text_field($username);
    $repository = sanitize_text_field($repository);
    $branch = sanitize_text_field($branch);

    // Determine the installation path
    $slug = sanitize_title($repository);
    $install_path = ($type === 'theme') ? get_theme_root() . "/{$slug}" : WP_PLUGIN_DIR . "/{$slug}";

    // Check if the theme or plugin is already installed
    if (is_dir($install_path)) {
        echo esc_html(ucfirst($type)) . " '{$repository}' is already installed.";
        return;
    }

    // Construct the GitHub ZIP URL
    $zip_url = "https://github.com/{$username}/{$repository}/archive/refs/heads/{$branch}.zip";

    // Fetch the ZIP file from GitHub
    $response = wp_remote_get($zip_url, ['timeout' => 30]);
    if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
        echo esc_html("Unable to download the ZIP file from {$zip_url}. Please verify the GitHub details.");
        return;
    }

    $zip_contents = wp_remote_retrieve_body($response);

    // Create a temporary file for the ZIP contents
    $temp_file = wp_tempnam();
    if (!$temp_file) {
        echo esc_html('Failed to create a temporary file.');
        return;
    }

    file_put_contents($temp_file, $zip_contents);

    // Extract the ZIP file
    $zip = new ZipArchive;
    if ($zip->open($temp_file) === true) {
        // Determine the extraction path
        $extract_to = ($type === 'theme') ? get_theme_root() : WP_PLUGIN_DIR;

        // Check if the extraction path is writable
        if (!is_writable($extract_to)) {
            echo esc_html(ucfirst($type)) . " directory is not writable: {$extract_to}. Please check permissions.";
            unlink($temp_file); // Clean up temporary file
            return;
        }

        // Extract the contents
        $zip->extractTo($extract_to);
        $zip->close();
        unlink($temp_file); // Clean up temporary file

        echo esc_html(ucfirst($type)) . " '{$repository}' has been successfully installed.";
    } else {
        echo esc_html('Failed to extract the ZIP file.');
        unlink($temp_file); // Clean up temporary file
    }
}

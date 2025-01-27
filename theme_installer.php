<?php  

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

function install_github_theme($type, $username, $repository, $branch) {
    // Sanitize the repository name to create a valid slug
    $slug = sanitize_title($repository);
    $install_path = ($type === 'theme') ? get_theme_root() . "/{$slug}" : WP_PLUGIN_DIR . "/{$slug}";

    // Check if the theme or plugin is already installed
    if (is_dir($install_path)) {
        echo ucfirst($type) . " '{$repository}' is already installed.";
        return;
    }

    // Construct the GitHub ZIP URL
    $zip_url = "https://github.com/{$username}/{$repository}/archive/refs/heads/{$branch}.zip";

    // Download the ZIP file from GitHub
    $zip_contents = @file_get_contents($zip_url);

    if (!$zip_contents) {
        echo "Unable to download ZIP file from {$zip_url}. Please verify the GitHub repository details.";
        return;
    }

    // Create a temporary file for the ZIP contents
    $temp_file = tempnam(sys_get_temp_dir(), 'github_');
    if (!$temp_file) {
        echo 'Failed to create a temporary file.';
        return;
    }

    file_put_contents($temp_file, $zip_contents);

    // Extract the ZIP file
    $zip = new ZipArchive;
    if ($zip->open($temp_file) === true) {
        // Determine the installation path based on the type (theme or plugin)
        $extract_to = ($type === 'theme') ? get_theme_root() : WP_PLUGIN_DIR;
        
        if (!is_writable($extract_to)) {
            echo ucfirst($type) . " directory is not writable: {$extract_to}. Please check permissions.";
            unlink($temp_file);
            return;
        }

        $zip->extractTo($extract_to);
        $zip->close();
        unlink($temp_file); // Clean up temporary file

        echo ucfirst($type) . " '{$repository}' has been successfully installed.";
    } else {
        echo 'Failed to extract the ZIP file.';
        unlink($temp_file); // Clean up temporary file
    }
}

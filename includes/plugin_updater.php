<?php  

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

add_filter('pre_set_site_transient_update_plugins', 'automatic_GitHub_updates', 10, 1);

function automatic_GitHub_updates($transient) {
    $plugin_slug = 'AJDWP-floating-login-form'; // Your plugin slug
    $plugin_file = $plugin_slug . '-Plugin/' . $plugin_slug . '.php'; // Path to the plugin file
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_file;

    // Ensure the plugin file exists
    if (!file_exists($plugin_path)) {
        error_log("Plugin file not found: {$plugin_path}");
        return $transient;
    }

    // Get current plugin version
    $plugin_data = get_plugin_data($plugin_path, false, false);
    $current_version = $plugin_data['Version'];

    // GitHub API URL for the latest release
    $user = 'arash12javadi'; // Replace with your GitHub username
    $repo = 'AJDWP-floating-login-form'; // Replace with your GitHub repository name
    $url = "https://api.github.com/repos/{$user}/{$repo}/releases/latest";

    // Set up request headers
    $headers = [
        'http' => [
            'header' => "User-Agent: {$user}\r\n",
            'timeout' => 30,
        ],
    ];

    // Fetch the API response
    $response = @file_get_contents($url, false, stream_context_create($headers));

    if ($response === false) {
        error_log("GitHub API request failed for URL: {$url}");
        return $transient;
    }

    $file = json_decode($response);

    // Ensure the response contains a valid tag_name
    if (empty($file->tag_name)) {
        error_log('GitHub response missing "tag_name".');
        return $transient;
    }

    $new_version = $file->tag_name;

    // Compare current version with the GitHub version
    if (version_compare($current_version, $new_version, '<')) {
        // Add update data to the transient
        $transient->response[$plugin_file] = (object)[
            'slug'        => $plugin_slug,
            'plugin'      => $plugin_file,
            'new_version' => $new_version,
            'package'     => $file->zipball_url, // Use GitHub's ZIP URL
            'url'         => $file->html_url, // GitHub release page
            'tested'      => '6.3', // WordPress compatibility
            'requires'    => '5.2', // Minimum WordPress version
        ];
    }

    return $transient;
}

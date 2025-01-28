<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

//--------------------------- Update Theme ---------------------------//
if (!function_exists('automatic_GitHub_theme_updater')) {
    add_filter('pre_set_site_transient_update_themes', 'automatic_GitHub_theme_updater', 101, 1);

    function automatic_GitHub_theme_updater($data) {
        global $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch;

        // Sanitize global variables
        $AJDWP_github_user = sanitize_text_field($AJDWP_github_user);
        $AJDWP_github_repo = sanitize_text_field($AJDWP_github_repo);
        $AJDWP_github_branch = sanitize_text_field($AJDWP_github_branch);

        // Get the current theme folder and version
        $theme   = get_stylesheet(); // Folder name of the current theme
        $current = wp_get_theme()->get('Version'); // Current theme version

        // Construct the GitHub API URL
        $api_url = sprintf(
            'https://api.github.com/repos/%s/%s/releases/latest',
            urlencode($AJDWP_github_user),
            urlencode($AJDWP_github_repo)
        );

        // Make a request to the GitHub API
        $response = wp_remote_get($api_url, [
            'timeout' => 30,
            'headers' => [
                'User-Agent' => $AJDWP_github_user,
            ],
        ]);

        // Check for errors in the response
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            error_log('Failed to fetch release data from GitHub: ' . wp_remote_retrieve_response_message($response));
            return $data;
        }

        // Decode the JSON response
        $file = json_decode(wp_remote_retrieve_body($response));
        if (empty($file->tag_name)) {
            error_log('GitHub release data is missing the "tag_name" field.');
            return $data;
        }

        // Sanitize and validate the version number
        $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if (!version_compare($update, $current, '>')) {
            return $data; // No update needed
        }

        // Construct the download package URL
        $package_url = sprintf(
            'https://codeload.github.com/%s/%s/zip/refs/heads/%s',
            urlencode($AJDWP_github_user),
            urlencode($AJDWP_github_repo),
            urlencode($AJDWP_github_branch)
        );

        // Add the update data to the response
        $data->response[$theme] = [
            'theme'       => $theme,
            'new_version' => $update,
            'url'         => esc_url('https://github.com/' . $AJDWP_github_user . '/' . $AJDWP_github_repo),
            'package'     => esc_url($package_url),
        ];

        return $data;
    }
}

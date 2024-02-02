<?php 
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

//--------------------------- update theme ---------------------------//
if (!function_exists('automatic_GitHub_theme_updater')) {
    add_filter('pre_set_site_transient_update_themes', 'automatic_GitHub_theme_updater', 101, 1);

    function automatic_GitHub_theme_updater($data) {
        global $AJDWP_github_user, $AJDWP_github_repo, $AJDWP_github_branch;
        $theme   = get_stylesheet(); // Folder name of the current theme
        $current = wp_get_theme()->get('Version'); // Get the version of the current theme

        $file = @json_decode(@file_get_contents('https://api.github.com/repos/' . $AJDWP_github_user . '/' . $AJDWP_github_repo . '/releases/latest', false, stream_context_create(['http' => ['header' => "User-Agent: " . $AJDWP_github_user . "\r\n"]])));
        $update = filter_var($file->tag_name, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        // Only return a response if the new version number is higher than the current version
        if (version_compare($update, $current, '>')) {
            $data->response[$theme] = array(
                'theme'       => $theme,
                'new_version' => $update,
                'url'         => 'https://github.com/' . $AJDWP_github_user . '/' . $AJDWP_github_repo,
                'package'     => 'https://codeload.github.com/' . $AJDWP_github_user . '/' . $AJDWP_github_repo . '-'.$AJDWP_github_branch.'/zip/refs/heads/'.$AJDWP_github_branch,
            );
        }
        return $data;
    }
}

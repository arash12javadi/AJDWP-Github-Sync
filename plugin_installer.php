<?php 

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

function install_github_plugin($username, $repository, $branch) {
    $plugin_slug = sanitize_title($repository);
    $plugin_path = WP_PLUGIN_DIR . '/' . $plugin_slug;

    if (!is_dir($plugin_path)) {
        $zip_url = "https://github.com/{$username}/{$repository}/archive/{$branch}.zip";
        $zip_contents = file_get_contents($zip_url);

        if ($zip_contents) {
            $temp_file = tempnam(sys_get_temp_dir(), 'github_plugin_');
            file_put_contents($temp_file, $zip_contents);

            $zip = new ZipArchive;
            if ($zip->open($temp_file) === true) {
                $zip->extractTo(WP_PLUGIN_DIR);
                $zip->close();
                unlink($temp_file);

                run_activate_plugin( $repository.'-Plugin/'.$repository.'.php' );

            } else {
                echo 'Unable to extract ZIP file.';
            }
        } else {
            echo 'Unable to download ZIP file.';
        }
    } else {
        echo 'Plugin is already installed.';
    }
}

function run_activate_plugin( $plugin ) {
    $plugin = trim( $plugin );
    $current = get_option( 'active_plugins' );
    $plugin = plugin_basename( $plugin );

    if ( !in_array( $plugin, $current ) ) {
        $current[] = $plugin;
        sort( $current );
        do_action( 'activate_plugin', $plugin );
        update_option( 'active_plugins', $current );
        do_action( 'activate_' . $plugin );
        do_action( 'activated_plugin', $plugin );
    }

    return null;
}



?>
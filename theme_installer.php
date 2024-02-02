<?php  

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

function install_github_theme($type, $username, $repository, $branch) {
    $slug = sanitize_title($repository);
    $path = get_theme_root() . "/{$slug}";

    if (!is_dir($path)) {
        $zip_url = "https://github.com/{$username}/{$repository}/archive/{$branch}.zip";
        $zip_contents = file_get_contents($zip_url);

        if ($zip_contents) {
            $temp_file = tempnam(sys_get_temp_dir(), 'github_');
            file_put_contents($temp_file, $zip_contents);

            $zip = new ZipArchive;
            if ($zip->open($temp_file) === true) {
                $zip->extractTo(($type === 'theme') ? get_theme_root() : WP_PLUGIN_DIR);
                $zip->close();
                unlink($temp_file);
            } else {
                echo 'Unable to extract ZIP file.';
            }
        } else {
            echo 'Unable to download ZIP file.';
        }
    } else {
        echo "{$type} is already installed.";
    }
}
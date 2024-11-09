<?php
// Enqueue plugin styles and scripts for the front end
// --------------------------------------------------------


function iq_bible_api_enqueue_assets()
{
    $enable_caching = get_option('iq_bible_api_cache');

    // Set version to time() if caching is enabled, otherwise set to '1.0.0'
    $version = $enable_caching ? time() : '1.0.0';

    // Enqueue styles for the front end
    wp_enqueue_style('iq-bible-api-style', plugins_url('../assets/css/style.css', __FILE__), array(), $version);

    // Enqueue scripts for the front end
    wp_enqueue_script('iqbible-script', plugins_url('../assets/js/scripts.js', __FILE__), array(), $version, true);

    // Set the versionId from URL or use a default of 'kjv'
    $versionId = isset($_GET['versionId']) ? sanitize_text_field($_GET['versionId']) : 'kjv';

    // Localize the script with the ajaxurl
    wp_localize_script('iqbible-script', 'iqbible_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'plugin_url' => plugin_dir_url(dirname(__FILE__)), // Ensures the correct plugin root path
        'versionId' => $versionId, // Pass versionId to JavaScript
        'isUserLoggedIn' => is_user_logged_in() ? '1' : '0', // Pass login status

    ));
}

// Enqueue Dashicons
// -------------------------------------------
function enqueue_dashicons()
{
    wp_enqueue_style('dashicons');
}


// Enqueue scripts and styles for admin pages
// --------------------------------------------
function iq_bible_enqueue_admin_assets($hook_suffix)
{
    // Check if it's the settings page of the IQ Bible plugin
    if ($hook_suffix === 'settings_page_iq_bible_api') {
        // Enqueue styles and scripts for the admin settings page
        wp_enqueue_style('iq-bible-admin-style', plugins_url('../assets/css/admin-style.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('iqbible-admin-script', plugins_url('../assets/js/scripts.js', __FILE__), array(), '1.0.0', true);

        // Localize the script with the ajaxurl for admin
        wp_localize_script('iqbible-admin-script', 'iqbible_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}



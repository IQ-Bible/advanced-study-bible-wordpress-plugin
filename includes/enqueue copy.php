<?php
// Enqueue plugin styles and scripts for the front end
// --------------------------------------------------------
function iq_bible_api_enqueue_assets()
{
    $enable_caching = get_option('iq_bible_api_cache');
    $version = $enable_caching ? time() : '1.0.0';

    // Enqueue styles for the front end
    wp_enqueue_style('iq-bible-api-style', plugins_url('../assets/css/style.css', __FILE__), array(), $version);

    // Enqueue scripts only if needed on the front end
    // wp_enqueue_script('iqbible-script', plugins_url('../assets/js/scripts.js', __FILE__), array(), $version, true);

    wp_enqueue_script('iqbible-script', plugins_url('../assets/js/scripts.js', __FILE__), array('jquery'), $plugin_version, true); // Added jquery as dependency just in case

        // ---> START NONCE ADDITION <---
        $ajax_nonce = wp_create_nonce('iqbible_ajax_nonce');
        // ---> END NONCE ADDITION <---

    wp_localize_script('iqbible-script', 'iqbible_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'plugin_url' => plugin_dir_url(__FILE__), 
        'versionId' => isset($_GET['versionId']) ? sanitize_text_field($_GET['versionId']) : 'kjv',
        'isUserLoggedIn' => is_user_logged_in() ? '1' : '0', 
        'nonce' => $ajax_nonce // Pass the nonce to JavaScript
    ));
}

// Enqueue Dashicons
function enqueue_dashicons()
{
    wp_enqueue_style('dashicons');
}

// Enqueue scripts and styles for admin pages only
function iq_bible_enqueue_admin_assets($hook_suffix)
{
    if ($hook_suffix === 'settings_page_iq_bible_api') {
        // Enqueue styles and scripts specifically for the admin settings page
        wp_enqueue_style('iq-bible-admin-style', plugins_url('../assets/css/admin-style.css', __FILE__), array(), '1.0.0');
        wp_enqueue_script('iqbible-admin-script', plugins_url('../assets/js/scripts-admin.js', __FILE__), array(), '1.0.0', true);

                // ---> START NONCE ADDITION (for admin if needed later) <---
        // If admin script needs AJAX, add nonce here too
        // $admin_nonce = wp_create_nonce('iqbible_admin_ajax_nonce');
        // ---> END NONCE ADDITION <---

        // Localize script for admin settings page
        wp_localize_script('iqbible-admin-script', 'iqbible_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php')
        ));
    }
}

// Hooks:
add_action('wp_enqueue_scripts', 'iq_bible_api_enqueue_assets');
add_action('admin_enqueue_scripts', 'iq_bible_enqueue_admin_assets');
add_action('wp_enqueue_scripts', 'enqueue_dashicons');

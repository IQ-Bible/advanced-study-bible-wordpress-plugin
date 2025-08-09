<?php
/*
Plugin Name:    IQBible Advanced Study Bible
Plugin URI:     https://iqbible.com
Description:    A WordPress plugin to display an Advanced Study Bible and other features via the IQBible API. Use the shortcode [iqbible_advanced] to display on any page. For settings, go to Settings > IQBible.
Version:        1.0.0-beta-23
Requires at least: 6.0
Tested up to:   6.8
Requires PHP:   7.4
Author:         Jody Pike Méndez
Author URI:     https://jodypm.com
License:        GPLv2 or later
License URI:    https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:    iqbible
Domain Path:    /languages
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Create notes table on plugin activation
function iqbible_create_notes_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'iqbible_notes';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id bigint(20) UNSIGNED NOT NULL,
        note_text text NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    dbDelta($sql);

    // Check if foreign key constraint exists and drop if needed
    $wpdb->query("ALTER TABLE $table_name DROP FOREIGN KEY IF EXISTS fk_user_id");

    // Add foreign key constraint with a unique name
    $wpdb->query("ALTER TABLE $table_name ADD CONSTRAINT fk_user_id_" . uniqid() . " FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE;");
}
register_activation_hook(__FILE__, 'iqbible_create_notes_table');


// Ensure default options are set on plugin activation
register_activation_hook(__FILE__, 'iqbible_set_default_options');

function iqbible_set_default_options() {
    if (get_option('iq_bible_api_cache') === false) {
        update_option('iq_bible_api_cache', 1);
    }
}


// Namespace for PUC (Plugin Update Checker)
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

// Include the PUC library
$puc_path = plugin_dir_path(__FILE__) . 'plugin-update-checker-5.6/plugin-update-checker.php';

if (!file_exists($puc_path)) {
    error_log('PUC file missing at: ' . $puc_path);
} else {
    error_log('PUC file found and will be included.');
    require_once $puc_path;

    $myUpdateChecker = PucFactory::buildUpdateChecker(
        'https://github.com/IQ-Bible/advanced-study-bible-wordpress-plugin/',
        __FILE__,
        'iqbible-advanced-study-bible-wordpress-plugin' 
    );

    $myUpdateChecker->getVcsApi()->enableReleaseAssets();
}


// Create saved verses table on plugin activation
function iq_bible_create_saved_verses_table()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'iqbible_saved_verses';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        verse_id VARCHAR(255) NOT NULL,
        version_id VARCHAR(255) NOT NULL,
        verse_text TEXT NOT NULL,
        saved_at DATETIME NOT NULL,
        FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'iq_bible_create_saved_verses_table');

// Include necessary files
require_once plugin_dir_path(__FILE__) . 'includes/enqueue.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/shortcodes.php';
require_once plugin_dir_path(__FILE__) . 'includes/admin-settings.php';

// Define Plugin Version Constant
if ( ! defined( 'IQBIBLE_VERSION' ) ) {
    define( 'IQBIBLE_VERSION', '1.0.0' ); 
}

// --- I18N SETUP ---
/**
 * Load plugin textdomain.
 * @since 1.0.0 // version
 */
function iqbible_load_textdomain()
{
    load_plugin_textdomain(
        'iqbible',
        false,
        dirname(plugin_basename(__FILE__)) . '/languages/'
    );
}

/* Registries */

/* Shortcode */
add_shortcode('iqbible_advanced', 'iq_bible_api_shortcode');

// I18N
add_action('plugins_loaded', 'iqbible_load_textdomain');

/* Frontend Styles and Scripts */
add_action('wp_enqueue_scripts', 'iq_bible_api_enqueue_assets');

/* Admin Styles and Scripts */
add_action('admin_enqueue_scripts', 'iq_bible_enqueue_admin_assets');

/* Dashicons */
add_action('wp_enqueue_scripts', 'iqbible_enqueue_dashicons');

/*Search */
add_action('wp_ajax_iq_bible_search', 'iq_bible_search_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_search', 'iq_bible_search_ajax_handler');

/* Bible Reading Plans */
add_action('wp_ajax_iq_bible_plans', 'iq_bible_plans_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_plans', 'iq_bible_plans_ajax_handler');

/* Defs */
add_action('wp_ajax_iq_bible_define', 'iq_bible_define_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_define', 'iq_bible_define_ajax_handler');

/* Strong's */
add_action('wp_ajax_iq_bible_strongs_ajax_handler', 'iq_bible_strongs_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_strongs_ajax_handler', 'iq_bible_strongs_ajax_handler');

/* Cross Refs */
add_action('wp_ajax_iq_bible_get_cross_references', 'iq_bible_get_cross_references_handler');
add_action('wp_ajax_nopriv_iq_bible_get_cross_references', 'iq_bible_get_cross_references_handler');

/* Original Text (Hebrew, Greek, or Aramaic)*/
add_action('wp_ajax_iq_bible_get_original_text', 'iq_bible_get_original_text_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_get_original_text', 'iq_bible_get_original_text_ajax_handler');

/* Topics */
add_action('wp_ajax_iq_bible_topics_ajax_handler', 'iq_bible_topics_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_topics_ajax_handler', 'iq_bible_topics_ajax_handler');

/* Chapter AJAX */
add_action('wp_ajax_iq_bible_chapter_ajax_handler', 'iq_bible_chapter_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_chapter_ajax_handler', 'iq_bible_chapter_ajax_handler');

/* Books AJAX */
add_action('wp_ajax_iq_bible_books_ajax_handler', 'iq_bible_books_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_books_ajax_handler', 'iq_bible_books_ajax_handler');

/* Chapter counts AJAX */
add_action('wp_ajax_iq_bible_chapter_count_ajax_handler', 'iq_bible_chapter_count_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_chapter_count_ajax_handler', 'iq_bible_chapter_count_ajax_handler');

// Hook to clear cache when the API key is updated
add_action('update_option_iq_bible_api_key', 'iq_bible_clear_plugin_cache', 10, 2);

/* Language Update AJAX */
add_action('wp_ajax_iq_bible_update_language_and_clear_cache', 'iq_bible_update_language_and_clear_cache_handler');
add_action('wp_ajax_nopriv_iq_bible_update_language_and_clear_cache', 'iq_bible_update_language_and_clear_cache_handler');

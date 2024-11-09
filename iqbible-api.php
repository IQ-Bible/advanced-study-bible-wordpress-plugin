<?php
/*
Plugin Name:    IQBible - Study Bible
Description:    A custom plugin to display a Study Bible and other features via the IQBible API. Use the shortcode [IQBible] to display on any page. For settings, go to Settings > IQBible.
Version:        1.0.0-beta
Text-Domain:    iqbible
Domain Path:    /languages
Author:         Jody Pike Méndez
Author URI:     https://jodypm.com
*/

// Start the session
function start_session()
{
    if (!session_id()) {
        session_start();
    }
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
    $wpdb->query("ALTER TABLE $table_name ADD CONSTRAINT fk_user_id_".uniqid()." FOREIGN KEY (user_id) REFERENCES {$wpdb->prefix}users(ID) ON DELETE CASCADE;");
}
register_activation_hook(__FILE__, 'iqbible_create_notes_table');


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

// Register shortcode
add_shortcode('IQBible', 'iq_bible_api_shortcode');

<?php
/**
 * IQ Bible Plugin Uninstallation.
 *
 * This file runs automatically ONLY when the user clicks the "Delete"
 * link for the IQ Bible plugin in the WordPress admin area.
 * It cleans up database tables, options, and transients created by the plugin.
 *
 * @package IQBible
 * @since   1.0.0
 */

if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit; 
}

global $wpdb;

$saved_verses_table = $wpdb->prefix . 'iqbible_saved_verses';
$wpdb->query("DROP TABLE IF EXISTS `$saved_verses_table`");

$notes_table = $wpdb->prefix . 'iqbible_notes';
$wpdb->query("DROP TABLE IF EXISTS `$notes_table`");

$plugin_options = [
    'iq_bible_api_key', 
    'iq_bible_api_cache',        
    'iq_bible_custom_login_url', 
];

foreach ($plugin_options as $option_name) {
    delete_option($option_name);
}

$transient_prefix = '_transient_iqbible_';
$timeout_prefix = '_transient_timeout_iqbible_';

$sql = $wpdb->prepare(
    "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s",
    $wpdb->esc_like($transient_prefix) . '%', 
    $wpdb->esc_like($timeout_prefix) . '%' 
);

$wpdb->query($sql);

?>
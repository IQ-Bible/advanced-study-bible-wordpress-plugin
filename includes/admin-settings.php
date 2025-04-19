<?php
// Register settings
function iq_bible_api_register_settings()
{
    // Register API Key setting with sanitize_text_field callback
    register_setting(
        'iq_bible_api_options',        // Option group
        'iq_bible_api_key',            // Option name
        'sanitize_text_field'          // Sanitize callback
    );

    // Register Caching setting with absint callback (ensures 0 or 1 for checkbox)
    register_setting(
        'iq_bible_api_options',        // Option group
        'iq_bible_api_cache',          // Option name
        'absint'                       // Sanitize callback
    );

    // Register Custom Login URL setting with esc_url_raw callback
    register_setting(
        'iq_bible_api_options',        // Option group
        'iq_bible_custom_login_url',   // Option name
        'esc_url_raw'                  // Sanitize callback (for saving URLs)
    );
}
add_action('admin_init', 'iq_bible_api_register_settings');

// Display the settings page
function iq_bible_api_settings_page()
{
    // --- NO CHANGES NEEDED IN THIS FUNCTION FOR THIS TASK ---
    // (Existing code for displaying the page remains the same)
    $api_key = esc_attr(get_option('iq_bible_api_key'));
    $masked_api_key = $api_key ? substr($api_key, 0, 4) . str_repeat('*', strlen($api_key) - 8) . substr($api_key, -4) : '';

    $info = iq_bible_api_get_data('GetInfo');
    $api_version = isset($info['version'])
        ? "<h2 style='color:green;'>✔ IQBible API version " . esc_html($info['version']) . "</h2><h3>Use the shortcode [IQBible] to display on any page.</h3>" // Added esc_html
        : "<h2 style='color:red;'>❌ ERROR! Incorrect or missing API Key!</h2><hr><p>Please subscribe to the IQBible API to obtain an API Key.<br>Visit: <a href='https://rapidapi.com/vibrantmiami/api/iq-bible' target='_blank'>IQBible API on The RapidAPI Marketplace</a></p>";
?>
    <div class="wrap">
        <h1>IQBible - Study Bible</h1>

        <!-- Manual Cache Clear Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="iqbible_clear_plugin_cache" />
            <?php 
             wp_nonce_field('iqbible_clear_cache_action', 'iqbible_clear_cache_nonce');
             submit_button('Manually Clear Plugin Cache'); 
             ?>
        </form>

        <form method="post" action="options.php">
            <?php
            settings_fields('iq_bible_api_options');
            do_settings_sections('iq_bible_api_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td>
                        <p id="api-key-display">
                            <span><?php echo $masked_api_key; ?></span>
                            <button type="button" id="edit-api-key-btn" class="button">Edit</button>
                        </p>
                        <input type="text" id="api-key-input" name="iq_bible_api_key" value="<?php echo esc_attr($api_key); ?>" size="60" style="display:none;" autocomplete="off" minlength="10" />
                        <?php echo $api_version; // This contains HTML, consider WP Kses if needed 
                        ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row">Caching</th>
                    <td>
                        <input type="checkbox" name="iq_bible_api_cache" value="1" <?php checked(1, get_option('iq_bible_api_cache'), true); ?> />
                        <label for="iq_bible_api_cache">Enable caching for CSS and API data</label>
                        <p class="description">Caching improves performance by storing data temporarily, reducing API calls and loading times, which is especially important for a smooth user experience.</p>
                    </td>
                </tr>

                <!-- New Setting for Custom Login URL -->
                <tr valign="top">
                    <th scope="row">Custom Login URL</th>
                    <td>
                        <input type="text" name="iq_bible_custom_login_url" value="<?php echo esc_url(get_option('iq_bible_custom_login_url', wp_login_url())); ?>" size="60" placeholder="https://yourcustomloginurl.com" />
                        <p class="description">Enter a custom URL for the login page. Leave blank to use the default WordPress login URL.</p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

<?php
    // Remove commented-out script if it was here
}

// Create a menu item for the settings page
function iq_bible_api_menu()
{
    add_options_page(
        'IQBible - Study Bible',   // Page title
        'IQBible - Study Bible',   // Menu title
        'manage_options',   // Capability
        'iq_bible_api',     // Menu slug
        'iq_bible_api_settings_page' // Callback function
    );
}
add_action('admin_menu', 'iq_bible_api_menu');

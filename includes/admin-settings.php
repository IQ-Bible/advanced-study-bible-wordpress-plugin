<?php // Admin Settings

function iq_bible_api_register_settings()
{
    register_setting( 'iq_bible_api_options', 'iq_bible_api_key', 'sanitize_text_field' );
    register_setting( 'iq_bible_api_options', 'iq_bible_api_cache', 'absint' );
    register_setting( 'iq_bible_api_options', 'iq_bible_custom_login_url', 'esc_url_raw' );
}
add_action('admin_init', 'iq_bible_api_register_settings');

// Display the settings page
function iq_bible_api_settings_page()
{
    $api_key = get_option('iq_bible_api_key'); // No esc_attr needed here yet
    $masked_api_key = $api_key ? substr($api_key, 0, 4) . str_repeat('*', strlen($api_key) - 8) . substr($api_key, -4) : '';

    $info = iq_bible_api_get_data('GetInfo');

    // Build API status string using translation functions
    if (isset($info['version'])) {
        $api_status_string = sprintf(
            '<h2 style="color:green;">%1$s %2$s</h2><h3>%3$s</h3>',
            '✔', // Checkmark entity
            sprintf(
                esc_html__('IQBible API version %s', 'iqbible'),
                esc_html($info['version'])
            ),
            esc_html__('Use the shortcode [IQBible] to display on any page.', 'iqbible')
        );
    } else {
        $api_status_string = sprintf(
            '<h2 style="color:red;">%1$s %2$s</h2><hr><p>%3$s<br>%4$s</p>',
            '❌', // Cross mark entity
            esc_html__('ERROR! Incorrect or missing API Key!', 'iqbible'),
            esc_html__('Please subscribe to the IQBible API to obtain an API Key.', 'iqbible'),
            sprintf(
                wp_kses( // Allow the 'a' tag for the link
                    __('Visit: <a href="%s" target="_blank">IQBible API on The RapidAPI Marketplace</a>', 'iqbible'),
                    ['a' => ['href' => true, 'target' => true]]
                ),
                'https://rapidapi.com/vibrantmiami/api/iq-bible'
            )
        );
    }
?>
    <div class="wrap">
        <h1><?php esc_html_e('IQBible - Study Bible Settings', 'iqbible'); // Changed title slightly ?></h1>

        <!-- Manual Cache Clear Form -->
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="iqbible_clear_plugin_cache" />
            <?php
             wp_nonce_field('iqbible_clear_cache_action', 'iqbible_clear_cache_nonce');
             // Use __() for button text passed to function
             submit_button(__('Manually Clear Plugin Cache', 'iqbible'));
             ?>
        </form>
        <!-- End Manual Cache Clear Form -->

        <hr>

        <!-- Main Settings Form -->
        <form method="post" action="options.php">
            <?php
            settings_fields('iq_bible_api_options');
            do_settings_sections('iq_bible_api_options');
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php esc_html_e('API Key', 'iqbible'); ?></th>
                    <td>
                        <p id="api-key-display">
                            <span><?php echo esc_html($masked_api_key); // Use esc_html for display ?></span>
                            <button type="button" id="edit-api-key-btn" class="button">
                                <?php esc_html_e('Edit', 'iqbible'); // Translate button text ?>
                            </button>
                        </p>
                        <input type="text" id="api-key-input" name="iq_bible_api_key" value="<?php echo esc_attr($api_key); // Use esc_attr for value attribute ?>" size="60" style="display:none;" autocomplete="off" minlength="10" />
                        <?php echo $api_status_string; // Output the prepared status string (already escaped/kses'd) ?>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Caching', 'iqbible'); ?></th>
                    <td>
                        <input type="checkbox" id="iq_bible_api_cache_id" name="iq_bible_api_cache" value="1" <?php checked(1, get_option('iq_bible_api_cache'), true); ?> />
                        <label for="iq_bible_api_cache_id"><?php esc_html_e('Enable caching for CSS and API data', 'iqbible'); ?></label> <?php // Changed label 'for' to match potential ID change if needed ?>
                        <p class="description"><?php esc_html_e('Caching improves performance by storing data temporarily, reducing API calls and loading times, which is especially important for a smooth user experience.', 'iqbible'); ?></p>
                    </td>
                </tr>

                <tr valign="top">
                    <th scope="row"><?php esc_html_e('Custom Login URL', 'iqbible'); ?></th>
                    <td>
                        <input type="text" id="iq_bible_custom_login_url_id" name="iq_bible_custom_login_url" value="<?php echo esc_url(get_option('iq_bible_custom_login_url', wp_login_url())); ?>" size="60" placeholder="<?php esc_attr_e('e.g., https://yourdomain.com/login', 'iqbible'); // Example placeholder ?>" /> <?php // Changed placeholder ?>
                        <label for="iq_bible_custom_login_url_id" style="display:none;"><?php esc_html_e('Custom Login URL Input', 'iqbible'); ?></label> <?php // Added hidden label for accessibility ?>
                        <p class="description"><?php esc_html_e('Enter a custom URL for the login page. Leave blank to use the default WordPress login URL.', 'iqbible'); ?></p>
                    </td>
                </tr>
            </table>
            <?php
            // Use __() for button text passed to function
            submit_button(__('Save Changes', 'iqbible'));
            ?>
        </form>
        <!-- End Main Settings Form -->
    </div>

<?php
}

// Create a menu item for the settings page
function iq_bible_api_menu()
{
    add_options_page(
        __('IQBible Settings', 'iqbible'),          // Page title (Translatable)
        __('IQBible Settings', 'iqbible'),          // Menu title (Translatable) - Changed slightly
        'manage_options',                           // Capability
        'iq_bible_api',                             // Menu slug (Keep as is)
        'iq_bible_api_settings_page'                // Callback function
    );
}
add_action('admin_menu', 'iq_bible_api_menu');
?>
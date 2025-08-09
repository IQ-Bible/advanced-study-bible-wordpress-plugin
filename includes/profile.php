<!-- Profile -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>
 
<h2><?php esc_html_e('My Profile', 'iqbible'); ?></h2>

<div class="iqbible-profile-container">


<?php if (is_user_logged_in()) {
        // Get the current user information
        $current_user = wp_get_current_user();

        // Get the current user's first name and display name
        $first_name = get_user_meta($current_user->ID, 'first_name', true);
        $display_name = $current_user->display_name;

        // Check if the first name exists
        if (!empty($first_name)) {
            echo "Hello, " . esc_html($first_name) . "!";
        } else {
            echo "Hello, " . esc_html($display_name) . "!";
        }

    ?>

        <h3><?php esc_html_e('My Saved Verses', 'iqbible'); ?></h3>
        
        <div class="iqbible-my-saved-verses"></div>

        <?php } else {
        include('logged-out.php');
    }
    ?>
</div>
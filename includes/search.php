<!-- Search -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>
 
<h2><?php esc_html_e('Search', 'iqbible'); ?></h2>
<form id="iqbible-search-form" class="iqbible-search-form">
    <div class="iqbible-search-container">
        <input type="text" id="iqbible-query" name="query" required placeholder="<?php esc_attr_e('Search the bible...', 'iqbible'); ?>">
        <button type="submit" class="iqbible-search-button">
            <?php esc_html_e('Search', 'iqbible'); ?>
        </button>
    </div>
</form>

<!-- Container for displaying search results -->
<div id="iqbible-search-results"></div>
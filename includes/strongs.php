<!-- Strong's Concordance -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>

<h2><?php esc_html_e('Strong\'s Concordance', 'iqbible'); ?></h2>
<?php esc_html_e('Search using Strong’s numbers - identifiers for original Hebrew (Hxxxx) and Greek (Gxxxx) words in the Bible.', 'iqbible'); ?>
<form id="iqbible-strongs-form" class="iqbible-search-form">
    <div class="iqbible-search-container">
        <input type="text" id="iqbible-strongs-query" name="iqbible-strongs-query" required placeholder="<?php esc_attr_e('Enter Strong\'s number (e.g., H21)', 'iqbible'); ?>">
        <button type="submit" class="iqbible-search-button">
            <?php esc_html_e('Search', 'iqbible'); ?>
        </button>
    </div>
</form>

<div id="iqbible-strongs-results"></div>
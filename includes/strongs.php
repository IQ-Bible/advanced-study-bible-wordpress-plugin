<!-- Strong's Concordance -->

<h2><?php esc_html_e('Strong\'s Concordance', 'iqbible'); ?></h2>
<form id="iqbible-strongs-form" class="iqbible-search-form">
    <div class="iqbible-search-container">
        <input type="text" id="iqbible-strongs-query" name="iqbible-strongs-query" required placeholder="<?php esc_attr_e('Search Strong\'s...', 'iqbible'); ?>">
        <button type="submit" class="iqbible-search-button">
            <?php esc_html_e('Search', 'iqbible'); ?>
        </button>
    </div>
</form>

<div id="iqbible-strongs-results"></div>
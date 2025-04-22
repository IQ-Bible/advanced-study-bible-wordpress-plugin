<!-- Dictionary -->
 
<h2><?php esc_html_e('Bible Dictionary', 'iqbible'); // Translate heading ?></h2>

<form id="iqbible-dictionary-form" class="iqbible-search-form">
    <div class="iqbible-search-container">
        <?php // Add label for accessibility ?>
        <label for="iqbible-definition-query" class="screen-reader-text"><?php esc_html_e('Word to define:', 'iqbible'); ?></label>
        <input type="text"
               id="iqbible-definition-query"
               name="query"
               required
               placeholder="<?php esc_attr_e('Type a biblical word to define...', 'iqbible');  ?>">
        <button type="submit" class="iqbible-search-button">
            <?php esc_html_e('Define', 'iqbible');  ?>
        </button>
    </div>
</form>
<div id="iqbible-definition-results">
</div>

<!-- Footer -->
<div class="iqbible-footer">
    <span><?php
           // Use sprintf to allow the link within the translatable string
           printf(
               wp_kses( // Use wp_kses to allow the <a> tag
                   __('The IQBible Study Bible by %s', 'iqbible'),
                   [ 'a' => [ 'href' => true, 'target' => true ] ] // Define allowed tags/attributes
               ),
               '<a href="http://iqbible.com" target="_blank">IQBible</a>' // The placeholder value
           );
    ?></span>
    <?php printf(esc_html__('Plugin: %s', 'iqbible'), esc_html(GetLatestVersionFromChangelog())); // Translate "Plugin:" label ?>
     | 
    <?php
       // Only show API version if available from $info (assuming $info is available here)
       // If $info isn't available in footer.php, this part needs context from shortcodes.php
       global $info; // Attempt to access global $info if set in shortcodes.php
       if (isset($info['version'])) {
            printf(esc_html__('API: %s', 'iqbible'), esc_html($info['version'])); // Translate "API:" label
       }
    ?>
    <a href="#" onclick="document.getElementById('about-dialog').showModal(); return false;" id="about-link">
        <?php esc_html_e('About', 'iqbible'); // Translate "About" link text ?>
    </a>
</div>


<!-- Dialogs
 ---------------- -->


 <!-- ========= NEW: Dialog Box for General Messages ========= -->
<dialog class="iqbible-dialog iqbible-message-dialog" id="iqbible-message-dialog">
    <div class="iqbible-dialog-content iqbible-message-dialog-content">
        <span class="iqbible-dialog-close iqbible-message-dialog-close" onclick="this.closest('dialog').close()">×</span>
        <div id="iqbible-message-text" style="margin-bottom: 15px;"></div>
         <button type="button" class="iqbible-message-dialog-close-btn button" onclick="this.closest('dialog').close()">
             <?php esc_html_e('OK', 'iqbible'); // Already translated - OK ?>
         </button>
    </div>
</dialog>
<!-- ========= END: Dialog Box for General Messages ========= -->

<!-- Dialog Box for About -->
<dialog class="iqbible-dialog" id="about-dialog">
    <div id="about-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('about-dialog').close()">×</span>
        <h2><?php esc_html_e('IQBible Study Bible WordPress Open Source Plugin', 'iqbible'); // Translate heading ?></h2>
        <hr>
        <small><?php printf(esc_html__('Version: %s', 'iqbible'), esc_html(GetLatestVersionFromChangelog())); // Translate "Version:" label ?></small>
        <p><?php esc_html_e('The IQBible Study Bible is a powerful tool designed to enrich your biblical studies and spiritual journey. With an intuitive interface, this plugin allows users to explore the Bible with ease, search for specific passages, and access detailed definitions through the Bible Dictionary. It also features Strong\'s Concordance for deeper insights, an extensive collection of Bible stories, and customizable reading plans that cater to your personal schedule', 'iqbible'); // Translate paragraph ?></p>
    </div>
</dialog>

<!-- Dialog Box for Cross References -->
<dialog class="iqbible-dialog" id="cross-references-dialog">
    <div id="cross-references-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('cross-references-dialog').close()">×</span>
        <h2><?php esc_html_e('Cross References', 'iqbible'); // Translate heading ?></h2>
        <!-- John 3:16 is example text, likely replaced by JS -->
        <div id="cross-references"></div>
    </div>
</dialog>

<!-- Dialog Box for Original Text -->
<dialog class="iqbible-dialog" id="original-text-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('original-text-dialog').close()">×</span>
        <h2><?php esc_html_e('Original Text', 'iqbible'); // Translate heading ?></h2>
         <!-- John 3:16 is example text, likely replaced by JS -->
        <div id="original-text"></div>
    </div>
</dialog>

<!-- Dialog Box for Books -->
<dialog class="iqbible-dialog" id="book-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('book-dialog').close()">×</span>
        <h2><?php esc_html_e('Select a Book', 'iqbible'); // Translate heading ?></h2>
        <ul id="books-list"></ul>
    </div>
</dialog>

<!-- Dialog Box for Versions -->
<dialog class="iqbible-dialog" id="versions-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('versions-dialog').close()">×</span>
        <h2><?php esc_html_e('Select a Version', 'iqbible'); // Translate heading ?></h2>
        <!-- Content added dynamically by JS -->
    </div>
</dialog>

<!-- Dialog Box for Loading... -->
<dialog class="iqbible-dialog" id="loading-dialog">
    <div class="iqbible-dialog-content">
        <div class="spinner"></div>
        <!-- Optional: Add translatable loading text -->
        <!-- <p><?php // esc_html_e('Loading...', 'iqbible'); ?></p> -->
    </div>
</dialog>

<!-- Dialog Box for Commentary -->
<dialog class="iqbible-dialog" id="commentary-dialog">
    <div id="commentary-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('commentary-dialog').close()">×</span>
        <h2><?php esc_html_e('Commentary', 'iqbible'); // Translate heading ?></h2>
        <small><i><?php esc_html_e('From John Gill\'s Exposition of the Bible:', 'iqbible'); // Translate attribution ?></i></small>
        <p></p> <?php // This empty P tag seems unnecessary ?>
        <div id="commentary-text"></div>
    </div>
</dialog>

<!-- Dialog Box for Book Information -->
<dialog class="iqbible-dialog" id="book-intro-dialog">
    <div id="book-intro-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('book-intro-dialog').close()">×</span>
        <h2><?php esc_html_e('Information', 'iqbible'); // Translate heading ?></h2>
        <hr>
        <!-- Content will be dynamically inserted here -->
    </div>
</dialog>
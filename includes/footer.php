<!-- Footer -->
<div class="iqbible-footer">
    <span>The IQBible Study Bible by <a href="http://iqbible.com" target="_blank">IQBible</a></span>
    Plugin: <?php echo GetLatestVersionFromChangelog() ?>
    &nbsp;|&nbsp;
    API: <?php echo $info['version']; ?>
    <a href="" onclick="document.getElementById('about-dialog').showModal(); return false;" id="about-link">About</a>
</div>


<!-- Dialogs 
 ---------------- -->


 <!-- ========= NEW: Dialog Box for General Messages ========= -->
<dialog class="iqbible-dialog iqbible-message-dialog" id="iqbible-message-dialog">
    <div class="iqbible-dialog-content iqbible-message-dialog-content">
        <!-- Close button 'x' -->
        <span class="iqbible-dialog-close iqbible-message-dialog-close" onclick="this.closest('dialog').close()">×</span>
        <!-- Message content area -->
        <div id="iqbible-message-text" style="margin-bottom: 15px;"></div>
         <!-- Explicit OK button -->
         <button type="button" class="iqbible-message-dialog-close-btn button" onclick="this.closest('dialog').close()"><?php esc_html_e('OK', 'iqbible'); ?></button>
    </div>
</dialog>
<!-- ========= END: Dialog Box for General Messages ========= -->

<!-- Dialog Box for About -->
<dialog class="iqbible-dialog" id="about-dialog">
    <div id="about-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('about-dialog').close()">×</span>
        <h2>IQBible Study Bible WordPress Open Source Plugin</h2>
        <hr>
        <small>Version: <?php echo GetLatestVersionFromChangelog() ?></small>
        <p>The IQBible Study Bible is a powerful tool designed to enrich your biblical studies and spiritual journey. With an intuitive interface, this plugin allows users to explore the Bible with ease, search for specific passages, and access detailed definitions through the Bible Dictionary. It also features Strong's Concordance for deeper insights, an extensive collection of Bible stories, and customizable reading plans that cater to your personal schedule</p>
    </div>
</dialog>

<!-- Dialog Box for Cross References -->
<dialog class="iqbible-dialog" id="cross-references-dialog">
    <div id="cross-references-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('cross-references-dialog').close()">×</span>
        <h2>Cross References</h2>John 3:16
        <div id="cross-references"></div>
    </div>
</dialog>

<!-- Dialog Box for Original Text -->
<dialog class="iqbible-dialog" id="original-text-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('original-text-dialog').close()">×</span>
        <h2>Original Text</h2>John 3:16
        <div id="original-text"></div>
    </div>
</dialog>

<!-- Dialog Box for Books -->
<dialog class="iqbible-dialog" id="book-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('book-dialog').close()">×</span>
        <h2>Select a Book</h2>
        <ul id="books-list"></ul>
    </div>
</dialog>

<!-- Dialog Box for Versions -->
<dialog class="iqbible-dialog" id="versions-dialog">
    <div class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('versions-dialog').close()">×</span>
        <h2>Select a Version</h2>
    </div>
</dialog>

<!-- Dialog Box for Loading... -->
<dialog class="iqbible-dialog" id="loading-dialog">
    <div class="iqbible-dialog-content">
        <div class="spinner"></div>
    </div>
</dialog>

<!-- Dialog Box for Commentary -->
<dialog class="iqbible-dialog" id="commentary-dialog">
    <div id="commentary-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('commentary-dialog').close()">×</span>
   
        <h2>Commentary</h2>
        <small><i>From John Gill's Exposition of the Bible:</i></small>
        <p></p>
        <div id="commentary-text"></div>
    </div>
</dialog>

<!-- Dialog Box for Book Information -->
<dialog class="iqbible-dialog" id="book-intro-dialog">
    <div id="book-intro-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('book-intro-dialog').close()">×</span>
        <h2>Information</h2>
        <hr>
        <!-- Content will be dynamically inserted here -->
    </div>
</dialog>









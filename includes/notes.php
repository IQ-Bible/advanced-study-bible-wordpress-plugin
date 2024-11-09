<!-- Notes -->
<h2>My Notes</h2>
<div class="iqbible-notes-container">
    <?php if (is_user_logged_in()) { ?>
        <div class="iqbible-notes-editor">
            <?php
            $content = ''; // Initialize with empty content or preloaded note content if needed.
            $editor_id = 'iqbible_editor'; // Unique ID for the TinyMCE editor.
            $settings = array(
                'media_buttons' => false,
                'textarea_name' => 'iqbible_editor',
                'textarea_rows' => 20,
                'teeny' => true,
                'quicktags' => false
            );
            wp_editor($content, $editor_id, $settings);
            ?>
            <div class="iqbible-note-buttons">
                <button id="save-note-btn">Save New Note</button>
                <button id="cancel-note-btn" style="display: none;">Cancel</button>
            </div>
        </div>

        <div id="saved-notes">
            <h3>Your Saved Notes</h3>
            <div id="iqbible-notes-list">
                <!-- Notes will be loaded dynamically using AJAX -->
            </div>
        </div>
    <?php } else {
        include('logged-out.php');
    }
    ?>
</div>
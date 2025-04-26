<?php // Shortcodes

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

function iq_bible_api_shortcode()
{
    ob_start(); // Start output buffering

    // Fetch Bible information: This also serves to determine if the API Key is valid
    $info = iq_bible_api_get_data('GetInfo');
    // Check if 'version' is present in the response
    if (isset($info['version'])) {
        // 'version' is present, thus the key is VALID
    } else {
        // 'version' is not present, thus the key is INVALID
        // --- I18N for Error Message ---
        echo "<div class='iqbible-main'>";
        echo "<h2>" . esc_html__('There was a problem. Please contact the administrator of this site and let them know of the missing or invalid IQBible API Key.', 'iqbible') . "</h2>";
        echo "<hr>";
        echo "<p><sup>10</sup> Fear thou not; for I am with thee: be not dismayed; for I am thy God: I will strengthen thee; yea, I will help thee; yea, I will uphold thee with the right hand of my righteousness. - Isaiah 41:10 (KJV)</p>";
        echo "</div>";
        // --- End I18N ---
        return ob_get_clean(); // Return the output buffer content
    }

    // Set a few session vars
    // ---------------------------
    $_SESSION['baseUrl'] = get_permalink();
    $_SESSION['siteName'] = get_bloginfo('name'); // Get the site name

    // Set default language to 'english' if not already set
    // This session logic might need replacing later with user meta/options
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'english';
    }

    // Fetch stories
    $current_language = isset($_SESSION['language']) ? $_SESSION['language'] : 'english'; // Defensive check
    $stories = iq_bible_api_get_data('GetStories', array('language' => $current_language));

    $stories_by_verse = array();
    if (is_array($stories)) {
        foreach ($stories as $story) {
            // Correct the key to 'verse_id'
            $story['verse_id'] = sprintf('%08d', $story['verse_id']);
            // Store story by 'verse_id'
            $stories_by_verse[$story['verse_id']] = $story['story'];
        }
    }
    $_SESSION['stories_by_verse'] = $stories_by_verse; // Consider replacing session with Transients
?>

    <div class="iqbible-main" id="iqbible-main">

        <!-- Tab Navigation -->
        <div class="iqbible-tabs">

            <button class="iqbible-tab-button active" title="<?php esc_attr_e('Read the Bible', 'iqbible'); ?>" onclick="openTab('bible')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book-open.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Book Open Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Bible', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Search the Bible', 'iqbible'); ?>" onclick="openTab('search')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/search.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Search Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Search', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Access the Bible Dictionary', 'iqbible'); ?>" onclick="openTab('dictionary')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Dictionary Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Dictionary', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Explore Strong\'s Concordance', 'iqbible'); ?>" onclick="openTab('strongs')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/zap.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Strongs Concordance Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Concordance', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Explore Bible Stories', 'iqbible'); ?>" onclick="openTab('stories')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/file-text.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Document Icon for Stories', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Stories', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Generate Reading Plans', 'iqbible'); ?>" onclick="openTab('plans')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/calendar.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Calendar Icon for Plans', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Reading Plans', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Explore Topics', 'iqbible'); ?>" onclick="openTab('topics')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Topics Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Topics', 'iqbible'); ?></span>
            </button>

            <!-- Parables Button (Commented Out for Beta)
            <button class="iqbible-tab-button" title="<?php // esc_attr_e('Explore Parables', 'iqbible'); ?>" onclick="openTab('parables')">
                <span class="iqbible-tab-icon">
                    <img src="<?php // echo esc_url(plugins_url('../assets/img/book-open.svg', __FILE__)); ?>" alt="<?php // esc_attr_e('Parables Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php // esc_html_e('Parables', 'iqbible'); ?></span>
            </button>
            -->

            <!-- Prophecies Button (Commented Out for Beta)
            <button class="iqbible-tab-button" title="<?php // esc_attr_e('Explore Prophecies', 'iqbible'); ?>" onclick="openTab('prophecies')">
                <span class="iqbible-tab-icon">
                    <img src="<?php // echo esc_url(plugins_url('../assets/img/zap.svg', __FILE__)); ?>" alt="<?php // esc_attr_e('Prophecies Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php // esc_html_e('Prophecies', 'iqbible'); ?></span>
            </button>
            -->

            <button class="iqbible-tab-button" title="<?php esc_attr_e('Get Help', 'iqbible'); ?>" onclick="openTab('help')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/help-circle.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Help Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('Help', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('My Notes', 'iqbible'); ?>" onclick="openTab('notes')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/edit.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Notes Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('My Notes', 'iqbible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="<?php esc_attr_e('My Profile', 'iqbible'); ?>" onclick="openTab('profile')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/user.svg', __FILE__)); ?>" alt="<?php esc_attr_e('Profile Icon', 'iqbible'); ?>">
                </span>
                <span class="iqbible-tab-text"><?php esc_html_e('My Profile', 'iqbible'); ?></span>
            </button>

        </div>

        <div class="iqbible-tab-content-container">
            <div id="bible" class="tab-content active">
                <?php include('bible.php'); ?>
            </div>

            <div id="search" class="tab-content">
                <?php include('search.php'); ?>
            </div>
            <div id="dictionary" class="tab-content">
                <?php include('dictionary.php'); ?>
            </div>
            <div id="strongs" class="tab-content">
                <?php include('strongs.php'); ?>
            </div>
            <div id="stories" class="tab-content">
                <?php include('stories.php'); ?>
            </div>
            <div id="plans" class="tab-content">
                <?php include('plans.php'); ?>
            </div>
            <div id="help" class="tab-content">
                <?php include('help.php'); ?>
            </div>
            <!-- <div id="parables" class="tab-content">
                <?php //include('parables.php'); ?>
            </div> -->
            <div id="topics" class="tab-content">
                <?php include('topics.php'); ?>
            </div>
            <!-- <div id="prophecies" class="tab-content">
                <?php //include('prophecies.php'); ?>
            </div> -->
            <div id="extra-biblical" class="tab-content">
                <?php include('extra-biblical.php'); ?>
            </div>
            <div id="notes" class="tab-content">
                <?php include('notes.php'); ?>
            </div>
            <div id="profile" class="tab-content">
                <?php include('profile.php'); ?>
            </div>

        </div>

    </div>




    
<!-- Dialogs
 ---------------- -->

 <!-- Dialog Box for Message -->
<dialog class="iqbible-dialog iqbible-message-dialog" id="iqbible-message-dialog">
    <div class="iqbible-dialog-content iqbible-message-dialog-content">
        <span class="iqbible-dialog-close iqbible-message-dialog-close" onclick="this.closest('dialog').close()">×</span>
        <div id="iqbible-message-text" style="margin-bottom: 15px;"></div>
         <button type="button" class="iqbible-message-dialog-close-btn button" onclick="this.closest('dialog').close()">
             <?php esc_html_e('OK', 'iqbible');  ?>
         </button>
    </div>
</dialog>

<!-- Dialog Box for About -->
<dialog class="iqbible-dialog" id="about-dialog">
    <div id="about-content" class="iqbible-dialog-content">
        <span class="iqbible-dialog-close" onclick="document.getElementById('about-dialog').close()">×</span>
        <h2><?php esc_html_e('IQBible Advanced Study Bible', 'iqbible'); // Translate heading ?></h2>
        <hr>
        <small>
            <?php 
        // translators: %s: The plugin version number.
        printf(esc_html__('Version: %s', 'iqbible'), esc_html(IQBIBLE_VERSION)); // Translate "Version:" label ?></small>
        <p><?php esc_html_e('The IQBible Advanced Study Bible is a powerful tool designed to enrich your biblical studies and spiritual journey. With an intuitive interface, this plugin allows users to explore the Bible with ease, search for specific passages, and access detailed definitions through the Bible Dictionary. It also features Strong\'s Concordance for deeper insights, an extensive collection of Bible stories, and customizable reading plans that cater to your personal schedule', 'iqbible'); // Translate paragraph ?></p>
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



<?php
    return ob_get_clean(); // Return the output buffer content
}
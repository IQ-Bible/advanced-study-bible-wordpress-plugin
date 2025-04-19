<?php // Include the FPDF library for PDF generation
require_once('lib/FPDF-master/fpdf.php');

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
        echo "<div class='iqbible-main'>
       <h2>There was a problem. Please contact the administrator of this site and let them know of the missing or invalid IQBible API Key.</h2>
        <hr> 
        <p><sup>10</sup> Fear thou not; for I am with thee: be not dismayed; for I am thy God: I will strengthen thee; yea, I will help thee; yea, I will uphold thee with the right hand of my righteousness. - Isaiah 41:10 (KJV)
        </p>
        </div>";
        return ob_get_clean(); // Return the output buffer content
    }

    // Set a few session vars
    // ---------------------------
    $_SESSION['baseUrl'] = get_permalink();
    $_SESSION['siteName'] = get_bloginfo('name'); // Get the site name

    // Set default language to 'english' if not already set
    if (!isset($_SESSION['language'])) {
        $_SESSION['language'] = 'english'; 
    }

    // Fetch stories
    $stories = iq_bible_api_get_data('GetStories', array('language' => 'english'));
    $stories_by_verse = array();
    if (is_array($stories)) {
        foreach ($stories as $story) {
            // Correct the key to 'verse_id'
            $story['verse_id'] = sprintf('%08d', $story['verse_id']);
            // Store story by 'verse_id'
            $stories_by_verse[$story['verse_id']] = $story['story'];
        }
    }
    $_SESSION['stories_by_verse'] = $stories_by_verse;
?>

    <div class="iqbible-main" id="iqbible-main">

        <!-- Tab Navigation -->
        <div class="iqbible-tabs">

            <button class="iqbible-tab-button active" title="Read the Bible" onclick="openTab('bible')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book-open.svg', __FILE__)); ?>" alt="Book Open Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Bible'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Search the Bible" onclick="openTab('search')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/search.svg', __FILE__)); ?>" alt="Search Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Search'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Access the Bible Dictionary" onclick="openTab('dictionary')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book.svg', __FILE__)); ?>" alt="Dictionary Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Dictionary'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Explore Strong's Concordance" onclick="openTab('strongs')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/zap.svg', __FILE__)); ?>" alt="Strongs Concordance Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Concordance'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Explore Bible Stories" onclick="openTab('stories')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/file-text.svg', __FILE__)); ?>" alt="Document Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Stories'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Generate Reading Plans" onclick="openTab('plans')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/calendar.svg', __FILE__)); ?>" alt="Calendar Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Reading Plans'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Topics" onclick="openTab('topics')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book.svg', __FILE__)); ?>" alt="Topics Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Topics'); ?></span>
            </button>

            <!-- <button class="iqbible-tab-button" title="Parables" onclick="openTab('parables')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/book-open.svg', __FILE__)); ?>" alt="Parables Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Parables'); ?></span>
            </button> -->

            <!-- <button class="iqbible-tab-button" title="Prophecies" onclick="openTab('prophecies')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/zap.svg', __FILE__)); ?>" alt="Prophecies Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Prophecies'); ?></span>
            </button> -->

            <button class="iqbible-tab-button" title="Get Help" onclick="openTab('help')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/help-circle.svg', __FILE__)); ?>" alt="Help Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('Help'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Notes" onclick="openTab('notes')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/edit.svg', __FILE__)); ?>" alt="Notes Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('My Notes'); ?></span>
            </button>

            <button class="iqbible-tab-button" title="Profile" onclick="openTab('profile')">
                <span class="iqbible-tab-icon">
                    <img src="<?php echo esc_url(plugins_url('../assets/img/user.svg', __FILE__)); ?>" alt="Profile Icon">
                </span>
                <span class="iqbible-tab-text"><?php echo iqbible_translate('My Profile'); ?></span>
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
            <div id="parables" class="tab-content">
                <?php include('parables.php'); ?>
            </div>
            <div id="topics" class="tab-content">
                <?php include('topics.php'); ?>
            </div>
            <div id="prophecies" class="tab-content">
                <?php include('prophecies.php'); ?>
            </div>
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

        <hr>
        <?php include('footer.php'); ?>
    </div>

<?php
    return ob_get_clean(); // Return the output buffer content
}

/* Registries
---------------- */

/* Session */
add_action('init', 'start_session', 1);

/* Shortcode */
add_shortcode('IQBible', 'iq_bible_api_shortcode');

/* Frontend Styles and Scripts */
add_action('wp_enqueue_scripts', 'iq_bible_api_enqueue_assets');

/* Admin Styles and Scripts */
add_action('admin_enqueue_scripts', 'iq_bible_enqueue_admin_assets');

/* Dashicons */
add_action('wp_enqueue_scripts', 'enqueue_dashicons');

/*Search */
add_action('wp_ajax_iq_bible_search', 'iq_bible_search_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_search', 'iq_bible_search_ajax_handler');

/* Bible Reading Plans */
add_action('wp_ajax_iq_bible_plans', 'iq_bible_plans_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_plans', 'iq_bible_plans_ajax_handler');

/* Defs */
add_action('wp_ajax_iq_bible_define', 'iq_bible_define_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_define', 'iq_bible_define_ajax_handler');

/* Strong's */
add_action('wp_ajax_iq_bible_strongs_ajax_handler', 'iq_bible_strongs_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_strongs_ajax_handler', 'iq_bible_strongs_ajax_handler');

/* Cross Refs */
add_action('wp_ajax_iq_bible_get_cross_references', 'iq_bible_get_cross_references_handler');
add_action('wp_ajax_nopriv_iq_bible_get_cross_references', 'iq_bible_get_cross_references_handler');

/* Original Text (Hebrew, Greek, or Aramaic)*/
add_action('wp_ajax_iq_bible_get_original_text', 'iq_bible_get_original_text_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_get_original_text', 'iq_bible_get_original_text_ajax_handler');

/* Topics */
add_action('wp_ajax_iq_bible_topics_ajax_handler', 'iq_bible_topics_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_topics_ajax_handler', 'iq_bible_topics_ajax_handler');

/* Chapter AJAX */
add_action('wp_ajax_iq_bible_chapter_ajax_handler', 'iq_bible_chapter_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_chapter_ajax_handler', 'iq_bible_chapter_ajax_handler');

/* Books AJAX */
add_action('wp_ajax_iq_bible_books_ajax_handler', 'iq_bible_books_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_books_ajax_handler', 'iq_bible_books_ajax_handler');

/* Chapter counts AJAX */
add_action('wp_ajax_iq_bible_chapter_count_ajax_handler', 'iq_bible_chapter_count_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_chapter_count_ajax_handler', 'iq_bible_chapter_count_ajax_handler');

// Hook to clear cache when the API key is updated
add_action('update_option_iq_bible_api_key', 'iq_bible_clear_plugin_cache', 10, 2);

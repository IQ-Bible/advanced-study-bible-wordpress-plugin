<?php // Enqueue plugin styles and scripts for the front end

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} 

function iq_bible_api_enqueue_assets()
{

    wp_enqueue_style(
        'iq-bible-api-style',
        plugin_dir_url(__DIR__) . 'assets/css/style.css', // Use __DIR__ assuming enqueue.php is in 'includes'
        array(),
        IQBIBLE_VERSION 
    );

    wp_enqueue_script(
        'iqbible-script',
        plugin_dir_url(__DIR__) . 'assets/js/scripts.js', 
        array('jquery'), 
        IQBIBLE_VERSION,
        true 
    );

    $ajax_nonce = wp_create_nonce('iqbible_ajax_nonce');

    $localized_strings_for_js = array(
        'enterValidDays'        => __('Please enter a valid number of days.', 'iqbible'),
        'errorFetchCrossRefs'   => __('An error occurred while fetching cross references. Status:', 'iqbible'),
        'errorFetchOriginalText' => __('An error occurred while retrieving the original text. Status:', 'iqbible'),

        'errorFetchChapter' => __('An error occurred while retrieving the chapter:', 'iqbible'),
        'noAudioSupport' => __('Your browser does not support the audio element.', 'iqbible'),
        'networkError' => __('Network error occurred. Please check your connection.', 'iqbible'),

        'errorProcessingResponse' => __('Error processing response from server.', 'iqbible'),

        'errorGeneratingPlan' => __('An error occurred generating the plan.', 'iqbible'),

        'errorGeneratingPlan' => __('An error occurred generating the plan.', 'iqbible'),
        
        'errorSearch' => __('An error occurred during the search...', 'iqbible'),
        'errorDictionary' => __('An error occurred during the definition retrieval:', 'iqbible'),
        'errorStrongs' => __('An error occurred during the concordance retrieval:', 'iqbible'),
        'errorBookIntro' => __('Error loading book introduction. Please try again.', 'iqbible'),
        'close' => __('Close', 'iqbible'),
        'selectVersion' => __('Select a Version', 'iqbible'),
        'withAudio' => __(' - with AUDIO NARRATION', 'iqbible'),
        'saveNewNote' => __('Save New Note', 'iqbible'),
        'updateNote' => __('Update Note', 'iqbible'),
        'noCommentary' => __('No commentary available for this verse.', 'iqbible'),
        'savedAlt' => __('Saved!', 'iqbible'),
        'errorDialogMissing' => __('Message dialog elements not found. Falling back to alert.', 'iqbible'),
        'errorDialogShow' => __('Error showing message dialog:', 'iqbible'),
        'errorSessionClear' => __('Error clearing session:', 'iqbible'),
        'errorAjaxStatus' => __('AJAX request failed with status:', 'iqbible'),

        'created' => __('Created:', 'iqbible'),
        'updated' => __('Updated:', 'iqbible'),

        'noteNotEmpty'          => __('Note content cannot be empty!', 'iqbible'),
        'noteSaved'             => __('Note saved!', 'iqbible'),
        'errorSavingNote'       => __('Error saving note:', 'iqbible'),
        'confirmDeleteNote'     => __('Are you sure you want to delete this note?', 'iqbible'),
        'errorDeletingNote'     => __('Error deleting note:', 'iqbible'),
        'verseCopied'           => __('Verse copied to clipboard!', 'iqbible'),
        'loginToSave'           => __('You must be logged in to save verses.', 'iqbible'),
        'verseSaved'            => __('Verse saved successfully!', 'iqbible'),
        'verseAlreadySaved'     => __('Verse already saved.', 'iqbible'),
        'linkCopied'            => __('Link copied to clipboard!', 'iqbible'),
        'errorCopyLink'         => __('Failed to copy link, please try again!', 'iqbible'),
        'confirmDeleteVerse'    => __('Are you sure you want to remove this verse from your saved verses?', 'iqbible'),
        'errorRemovingVerse'    => __('Error removing verse:', 'iqbible'),
        'errorRemovingVerseRetry' => __('Error removing verse, please try again!', 'iqbible'),
        'savedOn'               => __('Saved on', 'iqbible'),
        'remove'                => __('Remove verse', 'iqbible'),

        'loading'               => __('Loading....', 'iqbible'),

        'noSavedVerses'         => __('No saved verses.', 'iqbible'),
        'noNotesFound'          => __('No notes found.', 'iqbible'),
        'edit' => __('Edit', 'iqbible'),
        'delete' => __('Delete', 'iqbible')
    );

    // Data to pass to JavaScript
    $data_for_js = array(
        'ajaxurl'        => admin_url('admin-ajax.php'),
        'plugin_url'     => plugin_dir_url(__DIR__),

        'iconBaseUrl'    => esc_url(plugin_dir_url(__DIR__) . 'assets/img/bible-icons/'),

        'versionId'      => isset($_GET['versionId']) ? sanitize_text_field($_GET['versionId']) : 'kjv',
        'isUserLoggedIn' => is_user_logged_in() ? '1' : '0',
        'nonce'          => $ajax_nonce,
        'i18n'           => $localized_strings_for_js // Nest translated strings under an 'i18n' key
    );

    wp_localize_script(
        'iqbible-script', // Handle for the script to attach data to
        'iqbible_ajax',   // Object name in JavaScript (e.g., iqbible_ajax.ajaxurl, iqbible_ajax.nonce, iqbible_ajax.i18n.noteSaved)
        $data_for_js      // The data array
    );
}

// Enqueue Dashicons
function iqbible_enqueue_dashicons()
{
    wp_enqueue_style('dashicons');
}

// Enqueue scripts and styles for admin pages only
function iq_bible_enqueue_admin_assets($hook_suffix)
{
    // Only load on the specific settings page for this plugin
    if ($hook_suffix === 'settings_page_iq_bible_api') {

        // Enqueue admin styles
        // wp_enqueue_style(
        //     'iq-bible-admin-style',
        //     plugin_dir_url(__DIR__) . 'assets/css/admin-style.css',
        //     array(),
        //     IQBIBLE_VERSION 
        // );

        // Enqueue admin scripts
        wp_enqueue_script(
            'iqbible-admin-script',
            plugin_dir_url(__DIR__) . 'assets/js/scripts-admin.js',
            array('jquery'),
            IQBIBLE_VERSION,
            true
        );

        // Nonce for potential admin AJAX actions
        $admin_nonce = wp_create_nonce('iqbible_admin_ajax_nonce');

        // Localize script for admin settings page
        wp_localize_script('iqbible-admin-script', 'iqbible_admin_ajax', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'admin_nonce' => $admin_nonce
        ));
    }
}



// Hooks:
add_action('wp_enqueue_scripts', 'iq_bible_api_enqueue_assets');
add_action('admin_enqueue_scripts', 'iq_bible_enqueue_admin_assets');
add_action('wp_enqueue_scripts', 'iqbible_enqueue_dashicons');

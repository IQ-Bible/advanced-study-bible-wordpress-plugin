<?php

// Translations
require_once plugin_dir_path(__FILE__) . 'translations.php';

// Function to get the translation based on the current locale
function iqbible_translate($text)
{
    global $iqbible_translations;

    // Get current locale (defaulting to 'en' if not set)
    $locale = get_locale();

    // Extract the language code (e.g., 'es' from 'es_ES')
    $language = substr($locale, 0, 2);

    // Check if a translation exists for the current language
    if (isset($iqbible_translations[$language]) && isset($iqbible_translations[$language][$text])) {
        return $iqbible_translations[$language][$text];
    }

    // Return the original text if no translation is found
    return $text;
}

function GetLatestVersionFromChangelog()
{
    // Get the path to the root of the plugin directory
    $plugin_root = plugin_dir_path(__FILE__);

    // Read the contents of the CHANGELOG.md file from the root of the plugin directory
    $changelog_file = $plugin_root . '../CHANGELOG.md';

    // Check if the file exists and read its contents
    if (file_exists($changelog_file)) {
        $subject = file_get_contents($changelog_file);
        preg_match_all('/\[.*?\]/', $subject, $matches);

        // Return the version, defaulting to '0.0.0' if not found
        return "v" . str_replace(['[', ']'], '', $matches[0][1] ?? '0.0.0');
    } else {
        // Handle the error if the file does not exist
        return "v0.0.0";
    }
}


function kill_session()
{
    // Start the session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Clear all session variables
    $_SESSION = [];

    // Destroy the session
    session_destroy();

    // Clear session cookie if needed
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }

    // Delete all WordPress transients
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");

    return "Session data and all cache transients have been cleared successfully.";
}



// Book icons
function iqbible_get_book_icon_url($bookName)
{
    // Replace spaces with hyphens and make it lowercase
    $formattedBookName = strtolower(str_replace(' ', '-', $bookName));

    // Construct the URL for the book icon
    $iconUrl = plugin_dir_url(__DIR__) . 'assets/img/bible-icons/' . $formattedBookName . '.png';

    return esc_url($iconUrl);
}



function iq_bible_api_get_data($endpoint, $params = array(), $cache_duration = 3600)
{
    // Log the API key being used
    $api_key = get_option('iq_bible_api_key');
    error_log('Using API key: ' . $api_key);

    // Check if caching is enabled
    $cache_enabled = get_option('iq_bible_api_cache');

    // Generate a unique transient key based on the endpoint and parameters
    $transient_key = 'iqbible_' . md5($endpoint . json_encode($params));

    // Check if cached data exists and caching is enabled
    if ($cache_enabled && $cache_duration > 0) {
        $cached_response = get_transient($transient_key);
        if ($cached_response !== false) {
            error_log('Returning cached response for ' . $transient_key);
            return $cached_response; // Return cached data if available
        }
    }

    // Build the API URL
    $url = 'https://iq-bible.p.rapidapi.com/' . $endpoint;
    error_log('Requesting URL: ' . $url);

    // Make the API request
    $response = wp_remote_get($url, array(
        'headers' => array(
            'x-rapidapi-host' => 'iq-bible.p.rapidapi.com',
            'x-rapidapi-key' => $api_key
        ),
        'body' => $params
    ));

    // Handle any errors in the request
    if (is_wp_error($response)) {
        error_log('Request failed: ' . $response->get_error_message());
        return false;
    }

    // Retrieve the response body
    $response_body = wp_remote_retrieve_body($response);
    if (empty($response_body)) {
        error_log('Empty response body');
        return false;
    }

    // Decode the response as JSON
    $decoded_response = json_decode($response_body, true);

    error_log('API response: ' . print_r($decoded_response, true));

    // Cache the response using the Transients API if caching is enabled
    if ($cache_enabled && $cache_duration > 0) {
        set_transient($transient_key, $decoded_response, $cache_duration);
    }

    // Return the fresh data
    return $decoded_response;
}





// Fetch current user information
function get_user_info()
{
    $current_user = wp_get_current_user();

    if ($current_user->ID == 0) {
        // User is not logged in
        return false;
    }

    $userInfo = array(
        'display_name' => $current_user->display_name,
        'user_email' => $current_user->user_email,
        'user_login' => $current_user->user_login
    );

    return $userInfo;
}



// Search Ajax handler
// --------------------------
function iq_bible_search_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    $query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
    $count = '';
    $versionId = isset($_POST['versionId']) ? sanitize_text_field($_POST['versionId']) : 'kjv'; // Default to 'kjv'

    // Call the API with the query
    $searchResults = iq_bible_api_get_data('GetSearch', array('query' => $query, 'versionId' => $versionId));

    echo "<h3>" . count($searchResults) . "&nbsp;Search Results for '{$query}'</h3>";

    // Check if the API returned any results
    if (!empty($searchResults)) {
        echo "<ol>";

        foreach ($searchResults as $result) {
            $count++;
            $verseId = $result['id'];
            $bookId = $result['b'];
            $chapterId = $result['c'];
            $verse = $result['v'];
            $text = $result['t'];

            iq_bible_ensure_books_session();

            $books = $_SESSION['books'];

            $bookName = 'Unknown Book Name';
            foreach ($books as $book) {
                if ($book['b'] == $bookId) {
                    $bookName = esc_html($book['n']);
                    break;
                }
            }




            $boldText = preg_replace(
                '/(' . preg_quote($query, '/') . ')/i',
                '<strong>$1</strong>',
                esc_html($text)
            );

            // Use verse-{verseId} format for the verse identifier
            echo "<li><a href='javascript:void(0)' 
                    class='bible-search-result' 
                    data-book-id='$bookId' 
                    data-chapter-id='$chapterId' 
                    data-verse-id='verse-$verseId' 
                    data-version-id='$versionId'>{$boldText}</a><br> 
                    - $bookName&nbsp;$chapterId:$verse&nbsp;(" . strtoupper($versionId) . ")</li><br>";
        }

        echo "</ol>";
    } else {
        echo "<p>No results found for '{$query}'.</p>";
        if (count($searchResults) == 0) {
            echo "<i>Remember, you are using the " . strtoupper($versionId) . " version. Check your spelling for the appropriate version!</i>";
        }
    }

    wp_die();
}

// Definitions AJAX handler
// -------------------------
function iq_bible_define_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    // Check if query is set and sanitize it
    $query = isset($_POST['iqbible-definition-query']) ? sanitize_text_field($_POST['iqbible-definition-query']) : '';

    // Convert the query to lowercase for case-insensitive search
    $query = strtolower($query);

    if (empty($query)) {
        echo 'Please enter a biblical word to define.';
        wp_die();
    }

    // Fetch the biblical definition using the API
    $_SESSION['dictionaryId'] = 'smiths';
    $_SESSION['dictionaryIdFullName'] = "Smith's Bible Dictionary";
    $definition_biblical = iq_bible_api_get_data('GetDefinitionBiblical', array('query' => $query, 'dictionaryId' => $_SESSION['dictionaryId']));

    if (!empty($definition_biblical)) {

        echo "<small><i>From " . esc_html($_SESSION['dictionaryIdFullName']) . ":</i></small><br>";

        // Display the word being defined
        echo '<h3>' . esc_html($definition_biblical['word']) . '</h3>';

        // Handle the XML-like <see> tag and replace it with "See WORD"
        $definition_text = $definition_biblical['definition'];

        // Use preg_replace_callback to find <see> tags and replace them
        $definition_text = preg_replace_callback(
            '/<see target="x-self">(.*?)<\/see>/i',
            function ($matches) {
                // Return the formatted text w/o the XML
                return esc_html($matches[1]);
            },
            $definition_text
        );

        // Output the cleaned-up definition text
        echo esc_html($definition_text) . '<br>';
    } else {
        echo 'No biblical definition found for ' . esc_html($query) . '.';
    }

    wp_die();
}

// Strong's Concordance AJAX handler
// ----------------------------------
function iq_bible_strongs_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    // Check if lexicon and id are set
    $lexicon = isset($_POST['lexicon']) ? sanitize_text_field($_POST['lexicon']) : '';
    $id = isset($_POST['id']) ? sanitize_text_field($_POST['id']) : '';

    if (empty($lexicon) || empty($id)) {
        echo 'Invalid input.';
        wp_die();
    }

    // Fetch the Strong's data using the API
    $strongs = iq_bible_api_get_data('GetStrongs', array('lexiconId' => $lexicon, 'id' => $id));

    // Output the formatted results
    if (!empty($strongs)) {
        foreach ($strongs as $entry) {
            echo '<div class="strongs-entry">';
            echo '<small><i>Strong\'s ID: ' . esc_html($entry['strongs_id']) . '</i></small>';
            echo '<h3>' . esc_html($entry['word']) . '</h3>';
            echo '<p>' . esc_html($entry['glossary']) . '</p>';
            echo '</div>';
        }
    } else {
        echo 'No concordance results found.';
    }
    wp_die();
}

// Cross References AJAX handler
// ---------------------------------
function iq_bible_get_cross_references_handler()
{

        // ---> Verify Nonce <---
        check_ajax_referer('iqbible_ajax_nonce', 'security');
        // ---> End Verify Nonce <---

    $verseId = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';
    if (empty($verseId)) {
        echo json_encode(array('error' => 'Verse ID is required.'));
        wp_die();
    }

    // Fetch cross references using the API
    $crossReferences = iq_bible_api_get_data('GetCrossReferences', array('verseId' => $verseId));

    if (!empty($crossReferences)) {
        iq_bible_ensure_books_session();
        $books = $_SESSION['books'];
        // Prepare the list to display cross references
        $referencesList = '<ul class="cross-references-list">';

        foreach ($crossReferences as $crossReference) {
            $sv = $crossReference['sv']; // Start verse (e.g., 19104030)
            // Parse the bookId, chapter, and verse from 'sv'
            $bookId = substr($sv, 0, 2);  // First two digits represent the book ID
            $chapterId = intval(substr($sv, 2, 3)); // Next three digits represent the chapter number
            $verseNumber = intval(substr($sv, 5, 3));   // Last three digits represent the verse number

            // Find the book name in the session data
            $bookName = '';
            foreach ($books as $book) {
                if ($book['b'] == intval($bookId)) {
                    $bookName = $book['n'];
                    break;
                }
            }

            // Create link with data attributes instead of direct URL
            $referencesList .= sprintf(
                '<li><a href="#" class="cross-reference-link" ' .
                    'data-book-id="%s" ' .
                    'data-chapter-id="%s" ' .
                    'data-verse-id="%s">%s %d:%d</a></li>',
                esc_attr($bookId),
                esc_attr($chapterId),
                esc_attr($sv),
                esc_html($bookName),
                $chapterId,
                $verseNumber
            );
        }

        $referencesList .= '</ul>';
        echo $referencesList;
    } else {
        echo "No cross references found.";
    }
    wp_die();
}



// Original Text AJAX handler
// ----------------------------
function iq_bible_get_original_text_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    // Check if verseId is set and sanitize it
    $verseId = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';

    if (empty($verseId)) {
        echo 'Invalid verse ID.';
        wp_die();
    }

    // Fetch the original text using the API
    $originalTexts = iq_bible_api_get_data('GetOriginalText', array('verseId' => $verseId));

    // Determine if it's Hebrew (Old Testament) or Greek (New Testament)
    $isHebrew = $originalTexts[0]['book'] <= 39;
    $lexicon = $isHebrew ? "H" : "G";

    // Display language header
    if ($isHebrew) {
        echo 'Hebrew<br><small><i>Original Hebrew is read from right to left &larr;</i></small>';
    } else {
        echo 'Greek';
    }

    // Display original text with numbers
    $ct = 0;
    echo "<h3 " . ($isHebrew ? 'style="direction: rtl; text-align: right;"' : '') . ">";

    if (!empty($originalTexts)) {
        foreach ($originalTexts as $originalTextWord) {
            $ct++;
            if ($isHebrew) {
                // For Hebrew: place number to the right of the word (will appear on the right when rendered RTL)
                echo "<span class='hebrew-word-container' style='display: inline-block; margin: 0 2px;'>" .
                    "<sup>#$ct</sup>" . $originalTextWord['word'] .
                    "</span> ";
            } else {
                // For Greek: keep original LTR format
                echo "<sup>#$ct</sup> " . $originalTextWord['word'] . " ";
            }
        }
        echo "</h3>";
        echo "<hr>";

        // Rest of the display code remains the same
        $ct = 0;
        foreach ($originalTexts as $originalText) {
            $ct++;
            $strongs = iq_bible_api_get_data('GetStrongs', array(
                'lexiconId' => $lexicon,
                'id' => $originalText['strongs']
            ));
            $glossary = $strongs[0]['glossary'];

            $pronunciation = json_decode($originalText['pronun'], true);
            echo '<div style="margin-bottom: 15px; ' . ($isHebrew ? 'direction: rtl; text-align: right;' : '') . '">';

            if ($isHebrew) {
                // All details in LTR, only the Hebrew word itself is RTL
                echo '<div style="direction: ltr; text-align: left;">';
                echo '<strong>#' . $ct . ': </strong>';
                // Just the Hebrew word is RTL
                echo '<span style="direction: rtl; display: inline-block;">' . esc_html($originalText['word']) . '</span><br>';
                echo '<strong>Pronunciation:</strong> ' . esc_html($pronunciation['dic_mod']) . '<br>';
                echo '<strong>Strong\'s ID:</strong> ' . $lexicon . esc_html($originalText['strongs']) . '<br>';
                echo '<strong>Strong\'s Glossary:</strong> ' . $glossary . '<br>';
                echo '</div>';
            } else {
                // Greek word details (all LTR)
                echo '<strong>#' . $ct . ':</strong> ' . esc_html($originalText['word']) . '<br>';
                echo '<strong>Pronunciation:</strong> ' . esc_html($pronunciation['dic_mod']) . '<br>';
                echo '<strong>Strong\'s ID:</strong> ' . $lexicon . esc_html($originalText['strongs']) . '<br>';
                echo '<strong>Strong\'s Glossary:</strong> ' . $glossary . '<br>';
            }

            echo '</div>';
        }
    } else {
        echo 'No original text found for the specified verse ID.';
    }

    wp_die();
}





// Reading Plans Ajax Handler
// -----------------------------
function iq_bible_plans_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---


    // Get form data from the AJAX request
    $days = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '365';
    $requestedStartDate = isset($_POST['requestedStartDate']) ? sanitize_text_field($_POST['requestedStartDate']) : '2023-01-01';
    $sections = isset($_POST['sections']) ? sanitize_text_field($_POST['sections']) : 'all';
    $requestedAge = isset($_POST['requestedAge']) ? intval($_POST['requestedAge']) : 15;
    $planName = isset($_POST['iqbible-planName']) ? sanitize_text_field($_POST['iqbible-planName']) : 'Default Plan';
    $planName = esc_html(stripslashes($planName));

    // Handle custom days if selected
    if ($days === 'custom') {
        $customDays = isset($_POST['customDays']) ? intval($_POST['customDays']) : 0;
        if ($customDays > 0) {
            $days = $customDays; // Use the custom number of days provided by the user
        } else {
            wp_send_json_error(array('message' => 'Invalid number of days.'));
            return;
        }
    } else {
        $days = intval($days);
        if ($days <= 0) {
            $days = 365; // Default value if invalid
        }
    }

    // Call the API with the provided form data (excluding planName)
    $planResults = iq_bible_api_get_data(
        'GetBibleReadingPlan',
        array(
            'days' => $days,
            'requestedStartDate' => $requestedStartDate,
            'sections' => $sections,
            'requestedAge' => $requestedAge
        )
    );

    // Function to create reading plan HTML and PDF details from the API response
    function create_plan_list_html($planResults, $planName)
    {
        // URL for the PDF maker (FPDF):
        define('MY_PLUGIN_URL', plugin_dir_url(__FILE__));
        define('MY_PLUGIN_PATH', plugin_dir_path(__FILE__));
        $plugin_url = MY_PLUGIN_URL;
        $plans_pdf_url = $plugin_url . 'plans-pdf.php';

        // Access books from session
        iq_bible_ensure_books_session();
        $books = isset($_SESSION['books']) ? $_SESSION['books'] : array();

        if (empty($planResults)) {
            return '<p>No plan results found for your request.</p>';
        }

        // Extract plan details
        $planDetails = $planResults[0]['datesInfo'];
        $startDate = new DateTime($planDetails['startDate']);
        $endDate = new DateTime($planDetails['endDate']);
        $duration = $startDate->diff($endDate)->days;
        $testaments = $planResults[0]['sections'];

        // Create plan details for HTML (display on page)
        $planDetailsHTML = "<div class='plan-details' id='plan-details'>";
        $planDetailsHTML .= "<h2>'" . $planName . "' <span><small>Bible Reading Plan</small></span></h2>";
        $planDetailsHTML .= "<p><strong>Start Date:</strong> " . $startDate->format('F j, Y') . "</p>";
        $planDetailsHTML .= "<p><strong>End Date:</strong> " . $endDate->format('F j, Y') . "</p>";
        $planDetailsHTML .= "<p><strong>Duration:</strong> " . $duration . " days</p>";
        $planDetailsHTML .= "</div>";

        // Create plan details for PDF (to be sent to PDF maker)
        $planDetailsPDF = "Plan Name: $planName\n";
        $planDetailsPDF .= "Start Date: " . $startDate->format('F j, Y') . "\n";
        $planDetailsPDF .= "End Date: " . $endDate->format('F j, Y') . "\n";
        $planDetailsPDF .= "Duration: " . $duration . " days\n";

        // Begin creating the list HTML
        $planListHTML = "<div class='reading-plan-list'>";
        $planListPDF = ""; // For PDF content

        $currentDate = clone $startDate;
        $currentMonth = $currentDate->format('F');
        $currentYear = $currentDate->format('Y');
        $planListHTML .= "<h3>$currentMonth, $currentYear</h3><ul>";
        $planListPDF .= "<b>Month: $currentMonth, $currentYear\n</b>";

        // Generate the list of readings
        while ($currentDate <= $endDate) {
            if ($currentDate->format('F') != $currentMonth) {
                $currentMonth = $currentDate->format('F');
                $currentYear = $currentDate->format('Y');
                $planListHTML .= "</ul><h3>$currentMonth, $currentYear</h3><ul>";
                $planListPDF .= "\n<b>Month: $currentMonth, $currentYear\n</b>";
            }

            $dayContentHTML = '';
            $dayContentPDF = '';
            $ct = 0;

            foreach ($planResults as $entry) {
                if ($entry['date'] === $currentDate->format('Y-m-d')) {
                    $verseListHTML = [];
                    $verseListPDF = [];
                    foreach ($entry['bookAndChapterIds'] as $id) {
                        $id = str_pad($id, 5, '0', STR_PAD_LEFT);
                        $bookId = intval(substr($id, 0, 2));
                        $chapterId = intval(substr($id, -3));
                        $bookName = 'Unknown Book';
                        foreach ($books as $book) {
                            if ($book['b'] == $bookId) {
                                $bookName = $book['n'];
                                break;
                            }
                        }

                        // Construct the URL using add_query_arg()
                        $url = add_query_arg(
                            array(
                                'bookId' => $bookId,
                                'chapterId' => $chapterId,
                                'versionId' => $_SESSION['versionId']
                            ),
                            $_SESSION['baseUrl']
                        );
                        // Append the verse ID as a fragment
                        $url .= '#' . $verseId;

                        $verseListHTML[] = sprintf(
                            '<label class="chapter-checkbox-label">
                                <input type="checkbox" class="chapter-checkbox" 
                                    data-book-id="%s" 
                                    data-chapter-id="%s" 
                                    data-chapter-ref="%s %s">
                                <a href="#" class="reading-plan-link" data-book-id="%s" data-chapter-id="%s">%s %s</a>
                            </label>',
                            esc_attr($bookId),
                            esc_attr($chapterId),
                            esc_html($bookName),
                            $chapterId,
                            esc_attr($bookId),
                            esc_attr($chapterId),
                            esc_html($bookName),
                            $chapterId
                        );

                        $verseListPDF[] = "$bookName $chapterId";
                    }
                    $dayContentHTML = implode(', ', $verseListHTML);
                    $dayContentPDF = implode(', ', $verseListPDF);
                    break;
                }
                $ct++;
            }

            $planListHTML .= "<small>Day #$ct</small><li style='list-style-type:none;'><strong>{$currentDate->format('l')}, {$currentDate->format('F jS, Y')}</strong><br>$dayContentHTML</li><hr>";
            $planListPDF .= "Day #$ct: {$currentDate->format('l, F jS, Y')} - $dayContentPDF\n";

            $currentDate->modify('+1 day');
        }

        $planListHTML .= "</ul></div>";
        $planListPDF .= "\n";

        // Form to pass data to the PDF generator
        $planDetailsDownloadOrPrint = "
            <form id='generate-pdf' action='" . esc_url($plans_pdf_url) . "' method='post' target='_blank'>
                <input type='hidden' name='planName' value='" . esc_html($planName) . "'>
                <input type='hidden' name='startDate' value='" . esc_html($startDate->format('F j, Y')) . "'>
                <input type='hidden' name='endDate' value='" . esc_html($endDate->format('F j, Y')) . "'>
                <input type='hidden' name='duration' value='" . esc_html($duration) . "'>
                <input type='hidden' name='testaments' value='" . esc_html($testaments) . "'>
                <input type='hidden' name='planDetails' value='" . esc_html($planListPDF) . "'>
                <button type='submit'>Download or Print</button>
            </form>";

        return $planDetailsDownloadOrPrint . $planDetailsHTML . $planListHTML;
    }

    // Output the plan list HTML
    echo create_plan_list_html($planResults, $planName);

    wp_die(); // Terminate immediately and return the proper response
}




// Topics AJAX handler
function iq_bible_topics_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---


    // Check if the topic is set
    $topic = isset($_POST['topic']) ? sanitize_text_field($_POST['topic']) : '';

    if (empty($topic)) {
        echo 'Invalid input.';
        wp_die();
    }

    // Fetch the topic data using the API
    $topicData = iq_bible_api_get_data('GetTopic', array('topic' => $topic));

    // Output the formatted results
    if (!empty($topicData)) {
        echo '<div class="topic-data">';

        $verseLinks = array(); // Initialize an array to store unique verse links

        foreach ($topicData as $entry) {
            if (!empty($entry['verseIds']) && is_array($entry['verseIds'])) {
                // We only need the first verseId for the link
                $firstVerseId = sprintf('%08d', $entry['verseIds'][0]);
                $bookId = substr($firstVerseId, 0, 2);
                $chapterId = substr($firstVerseId, 2, 3);
                $verseNumber = substr($firstVerseId, 5, 3);

                // Create a link with data attributes 
                $verseLinks[] = sprintf(
                    '<small><a href="#" class="topic-verse-link" data-book-id="%s" data-chapter-id="%s" data-verse-id="%s">%s</a></small>',
                    esc_attr($bookId),
                    esc_attr($chapterId),
                    esc_attr($firstVerseId),
                    esc_html($entry['citation'])
                );
            }
        }

        // Display all unique verse links
        echo implode('<br>', array_unique($verseLinks));

        echo '<hr></div>';
    } else {
        echo 'No results found for this topic.';
    }
    wp_die();
}



// Bible Chapter AJAX handler
function iq_bible_chapter_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---


    $bookId = isset($_POST['bookId']) ? sanitize_text_field($_POST['bookId']) : '';
    $chapterId = isset($_POST['chapterId']) ? sanitize_text_field($_POST['chapterId']) : '';
    $versionId = isset($_POST['versionId']) ? sanitize_text_field($_POST['versionId']) : 'kjv';

    if (empty($bookId) || empty($chapterId)) {
        echo json_encode(array('error' => 'Invalid book ID or chapter ID.'));
        wp_die();
    }

    // Get current user ID
    $user_id = get_current_user_id();

    // Get saved verses for this chapter from database
    global $wpdb;
    $table_name = $wpdb->prefix . 'iqbible_saved_verses';

    // Calculate verse ID range for this chapter
    $paddedBookId = str_pad($bookId, 2, '0', STR_PAD_LEFT);
    $paddedChapterId = str_pad($chapterId, 3, '0', STR_PAD_LEFT);
    $verseIdPrefix = $paddedBookId . $paddedChapterId;

    // Get all saved verses for this chapter and user
    $saved_verses = $_SESSION['saved_verses'] = $wpdb->get_col($wpdb->prepare(
        "SELECT verse_id FROM $table_name 
        WHERE user_id = %d 
        AND verse_id LIKE %s",
        $user_id,
        $verseIdPrefix . '%'
    ));

    // Fetch the Bible chapter data using the API
    $chapter = iq_bible_api_get_data('GetChapter', array(
        'bookId' => $bookId,
        'chapterId' => $chapterId,
        'versionId' => $versionId
    ));

    // Fetch the book name by book ID
    $bookNameResponse = iq_bible_api_get_data('GetBookNameByBookId', array(
        'bookId' => $bookId,
        'language' => $_SESSION['language']
    ));

    // Extract the book name from the response
    $bookName = isset($bookNameResponse[0]['n']) ? $bookNameResponse[0]['n'] : 'Unknown Book';

    // Prepare the response
    $response = array(
        'chapterContent' => '',
        'bookName' => $bookName,
        'savedVerses' => $saved_verses // Add saved verses to response
    );

    // Fetch stories from session
    $stories_by_verse = isset($_SESSION['stories_by_verse']) ? $_SESSION['stories_by_verse'] : array();

    // Format the chapter content
    if (!empty($chapter)) {
        foreach ($chapter as $verse) {
            // Pad verse number and create verse ID
            $paddedVerseNum = str_pad($verse['v'], 3, '0', STR_PAD_LEFT);
            $verseId = $paddedBookId . $paddedChapterId . $paddedVerseNum;

            // Check if a story exists for this verse ID
            if (isset($stories_by_verse[$verseId])) {
                $response['chapterContent'] .= '<div class="iqbible-story-title" id="story-' . $verseId . '"><strong>' . esc_html($stories_by_verse[$verseId]) . '</strong></div>';
            }

            // Start verse content
            $response['chapterContent'] .= '<div class="verse" id="verse-' . $verseId . '" data-verse-id="' . $verseId . '" data-version-id="' . $versionId . '">';
            $response['chapterContent'] .= '<sup>' . esc_html($verse['v']) . '</sup>&nbsp;';
            $response['chapterContent'] .= '<span class="copyable-text">' . esc_html($verse['t']) . '</span>';


            // Add saved icon if verse is saved
            if (in_array($verseId, $saved_verses)) {
                $response['chapterContent'] .= '&nbsp;<img src="' . esc_url(plugin_dir_url(__DIR__) . 'assets/img/bookmark.svg') . '" alt="Saved" class="saved-icon" title="Verse saved!">';
            }

            // Add verse options
            $chapterNumber = $paddedChapterId;
            $siteName = $_SESSION['siteName'];

            // Verse options section
            $response['chapterContent'] .= "
<div class='verse-options'>
    <button class='option-button' onclick='copyVerse(\"$verseId\", \"$bookName\", $chapterNumber, \"$versionId\", \"$siteName\", \"" . $_SESSION['language'] . "\")'>
        <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/clipboard.svg') . "' alt='Copy Icon'> Copy
    </button>";


            // Show additional options only for non-'extra' canon

            $response['chapterContent'] .= "
        <button class='option-button' onclick='showOriginalText(\"$verseId\")'>
            <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/key.svg') . "' alt='Original Text Icon'> Original Text
        </button>
        
        <button class='option-button' onclick='showCommentary(\"$verseId\")'>
            <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/message-square.svg') . "' alt='Commentary Icon'> Commentary
        </button>
        
        <button class='option-button' onclick='showCrossReferences(\"$verseId\")'>
            <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/crosshair.svg') . "' alt='Cross References Icon'> Cross References
        </button>";


            $response['chapterContent'] .= "
            <button class='option-button' onclick='shareVerse(\"$verseId\")' data-url='" . $_SESSION['baseUrl'] . "?bookId=$bookId&chapterId=$chapterId&versionId=$versionId#verse-$verseId'>
                <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/share.svg') . "' alt='Share Icon'> Share
            </button>
        
    <button class='option-button' onclick='saveVerse(\"$verseId\")'>
        <img src='" . esc_url(plugin_dir_url(__DIR__) . 'assets/img/bookmark.svg') . "' alt='Bookmark Icon'> Bookmark
    </button>

    <div class='verse-message' id='verse-message-$verseId'></div>
</div>"; // Close verse-options div

            $response['chapterContent'] .= "</div>"; // Close verse div

            $response['chapterContent'] .= "</div>"; // Close verse div
        }
    } else {
        $response['chapterContent'] = 'No chapter content results found.';
    }

    // Send the response as JSON
    echo json_encode($response);
    wp_die();
}






// AJAX handler to fetch chapter count for the selected book
function iq_bible_chapter_count_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---


    // Clear previous book data
    unset($_SESSION['bookId']);
    unset($_SESSION['chapterData']);

    // Get book ID from AJAX request
    $bookId = isset($_POST['bookId']) ? sanitize_text_field($_POST['bookId']) : '';
    $bookCategory = isset($_POST['bookCategory']) ? sanitize_text_field($_POST['bookCategory']) : '';

    // Log the bookId being sent to the API
    error_log('Book ID sent to API: ' . $bookId);

    if (empty($bookId)) {
        echo json_encode(array('error' => 'Invalid book ID.'));
        wp_die();
    }

    // Fetch the chapter count for the selected book
    if ($bookCategory == 'ExtraBiblical') {
        $chapterData = iq_bible_api_get_data('GetChapterCountExtraBiblical', array('bookId' => $bookId));
    } else {
        $chapterData = iq_bible_api_get_data('GetChapterCount', array('bookId' => $bookId));
    }

    // Log the API response for debugging
    //error_log('API response for chapter count: ' . print_r($chapterData, true));

    $chapterCount = isset($chapterData['chapterCount']) ? intval($chapterData['chapterCount']) : 0;

    echo json_encode(array('chapterCount' => $chapterCount));

    wp_die();
}



function iq_bible_ensure_books_session()
{
    // Check if the books session is empty
    if (empty($_SESSION['books'])) {
        // Fetch Old Testament books
        $booksOT = iq_bible_api_get_data('GetBooksOT', array(
            'language' => $_SESSION['language'] ?? 'english'
        ));

        // Fetch New Testament books
        $booksNT = iq_bible_api_get_data('GetBooksNT', array(
            'language' => $_SESSION['language'] ?? 'english'
        ));

        // Merge and store in session
        $_SESSION['booksOT'] = $booksOT;
        $_SESSION['booksNT'] = $booksNT;
        $_SESSION['books'] = array_merge($booksOT, $booksNT);
    }
}




// AJAX handler to fetch the book names and output them in HTML
function iq_bible_books_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---


    if (empty($_SESSION['books'])) {
        // Only make API calls if we don't have the data
        $booksOT = iq_bible_api_get_data('GetBooksOT', array('language' => $_SESSION['language']));
        $booksNT = iq_bible_api_get_data('GetBooksNT', array('language' => $_SESSION['language']));

        // Store in session
        $_SESSION['booksOT'] = $booksOT;
        $_SESSION['booksNT'] = $booksNT;
        $_SESSION['books'] = array_merge($booksOT, $booksNT);
    }

    // Use session data whether it was just set or already existed
    $booksOT = $_SESSION['booksOT'];
    $booksNT = $_SESSION['booksNT'];



    // Start output buffering to capture the HTML
    ob_start();


    // Display Old Testament books
    if (!empty($booksOT)) {
        echo '<h3>Old Testament</h3>';
        echo '<ul>';
        foreach ($booksOT as $bookOT) {
            echo '<li class="book-item" data-book-id="' . esc_attr($bookOT['b']) . '" data-book-category="OT">' . esc_html($bookOT['n']) . '</li>';
        }
        echo '</ul>';
    }

    // Display New Testament books
    if (!empty($booksNT)) {
        echo '<h3>New Testament</h3>';
        echo '<ul>';
        foreach ($booksNT as $bookNT) {
            echo '<li class="book-item" data-book-id="' . esc_attr($bookNT['b']) . '" data-book-category="OT">' . esc_html($bookNT['n']) . '</li>';
        }
        echo '</ul>';
    }

    // Return the buffered content
    echo ob_get_clean();

    wp_die(); // Required to properly terminate the AJAX call
}








function iq_bible_clear_plugin_cache($old_value, $new_value)
{
    global $wpdb;

    // Check if the API key has changed
    if ($old_value !== $new_value) {
        // Clear all transients related to the IQBible plugin
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_iqbible_%'");
        $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_iqbible_%'");

        error_log("Cache cleared due to API key update!");
    }
}

// Manual cache clearing via form submission
add_action('admin_post_iqbible_clear_plugin_cache', 'iq_bible_clear_plugin_cache_form');

function iq_bible_clear_plugin_cache_form()
{

    check_admin_referer('iqbible_clear_cache_action', 'iqbible_clear_cache_nonce');

    if (!current_user_can('manage_options')) {
         wp_die('You do not have sufficient permissions to perform this action.');
    }

    global $wpdb;

    // Clear all transients related to the IQBible plugin
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_iqbible_%'");
    $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_iqbible_%'");

    error_log("Cache manually cleared!");

    // Redirect back to the settings page with a success message
    wp_redirect(add_query_arg('cache_cleared', 'true', wp_get_referer()));
    exit;
}






// AJAX handler for fetching Bible versions
function iq_bible_get_versions()
{

        // ---> Verify Nonce <---
        check_ajax_referer('iqbible_ajax_nonce', 'security');
        // ---> End Verify Nonce <---

    $versions = iq_bible_api_get_data('GetVersions');

    if (!empty($versions)) {
        echo json_encode($versions);
    } else {
        echo json_encode(array('error' => 'No versions found'));
    }

    wp_die(); // Required to terminate the AJAX request properly
}

add_action('wp_ajax_iq_bible_get_versions', 'iq_bible_get_versions');
add_action('wp_ajax_nopriv_iq_bible_get_versions', 'iq_bible_get_versions');





// Handle the check for audio narration availability
function iq_bible_audio_check()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    $bookId = isset($_POST['bookId']) ? sanitize_text_field($_POST['bookId']) : '';
    $chapterId = isset($_POST['chapterId']) ? sanitize_text_field($_POST['chapterId']) : '';
    $versionId = isset($_POST['versionId']) ? sanitize_text_field($_POST['versionId']) : '';

    // Fetch audio narration if available
    $audio = iq_bible_api_get_data('GetAudioNarration', array(
        'bookId' => $bookId,
        'chapterId' => $chapterId,
        'versionId' => $versionId
    ));

    if (isset($audio['fileName']) && !empty($audio['fileName'])) {

        wp_send_json_success(array('audioUrl' => esc_url($audio['fileName'])));
    } else {

        wp_send_json_error(); // No audio found

    }
}

// Add the AJAX action hooks
add_action('wp_ajax_iq_bible_audio_check', 'iq_bible_audio_check');
add_action('wp_ajax_nopriv_iq_bible_audio_check', 'iq_bible_audio_check');




// Notes (CRUD operations)
// -------------------------

// Create/Save Note
function iq_bible_save_note()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $user_id = get_current_user_id();
    $note_text = isset($_POST['note_text']) ? wp_kses_post($_POST['note_text']) : '';

    if (empty($note_text)) {
        wp_send_json_error('Note content is empty.');
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'iqbible_notes';

    $result = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'note_text' => $note_text,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        )
    );

    if ($result) {
        $note_id = $wpdb->insert_id;
        $note = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $note_id), ARRAY_A);
        wp_send_json_success($note);
    } else {
        wp_send_json_error('Failed to save the note.');
    }
}
add_action('wp_ajax_iq_bible_save_note', 'iq_bible_save_note');



// Update Note function
function iq_bible_update_note()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    global $wpdb;

    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
    $note_text = isset($_POST['note_text']) ? stripslashes(wp_kses_post($_POST['note_text'])) : ''; // Use stripslashes() to remove slashes

    if ($note_id === 0 || empty($note_text)) {
        wp_send_json_error('Invalid note ID or content.');
    }

    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'iqbible_notes';

    // Update the note in the database
    $result = $wpdb->update(
        $table_name,
        array('note_text' => $note_text, 'updated_at' => current_time('mysql')),
        array('id' => $note_id, 'user_id' => $user_id),
        array('%s', '%s'),
        array('%d', '%d')
    );

    if ($result !== false) {
        // Fetch the updated note, applying stripslashes again to clean any additional slashes before sending the response.
        $updated_note = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $note_id), ARRAY_A);
        $updated_note['note_text'] = stripslashes($updated_note['note_text']); // Ensure no slashes in the response
        wp_send_json_success($updated_note);
    } else {
        wp_send_json_error('Failed to update note.');
    }
}
add_action('wp_ajax_iq_bible_update_note', 'iq_bible_update_note');




// Get Saved Notes function
// -------------------------
function iq_bible_get_saved_notes()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'iqbible_notes';

    // Fetch the saved notes
    $notes = $wpdb->get_results($wpdb->prepare(
        "SELECT id, note_text, created_at, updated_at FROM $table_name WHERE user_id = %d ORDER BY updated_at DESC",
        $user_id
    ), ARRAY_A);

    // Decode special characters to avoid over-escaping
    if (!empty($notes)) {
        foreach ($notes as &$note) {
            $note['note_text'] = htmlspecialchars_decode($note['note_text'], ENT_QUOTES); // Decode special chars
        }
        wp_send_json_success($notes); // Send the decoded notes
    } else {
        wp_send_json_error('No notes found.');
    }
}
add_action('wp_ajax_iq_bible_get_saved_notes', 'iq_bible_get_saved_notes');




// Delete Note function
// ---------------------
function iq_bible_delete_note()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        wp_send_json_error('User not logged in');
    }

    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;

    if ($note_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iqbible_notes';

        $result = $wpdb->delete($table_name, array('id' => $note_id, 'user_id' => get_current_user_id()));

        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error('Failed to delete note.');
        }
    } else {
        wp_send_json_error('Invalid note ID.');
    }
}
add_action('wp_ajax_iq_bible_delete_note', 'iq_bible_delete_note');




// Commentary Ajax handler
function iq_bible_commentary_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    $verseId = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';

    // Fetch the commentary using the iq_bible_api_get_data function
    $commentary = iq_bible_api_get_data('GetCommentary', array(
        'commentaryName' => 'gills', // You can change this to the desired commentary name
        'verseId' => $verseId
    ));

    // Return the commentary content as a JSON response
    echo json_encode(array('commentary' => $commentary));

    wp_die(); // This is required to terminate immediately and return a proper response
}
add_action('wp_ajax_iq_bible_commentary_ajax_handler', 'iq_bible_commentary_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_commentary_ajax_handler', 'iq_bible_commentary_ajax_handler');





function iq_bible_save_verse_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    // Check if the user is logged in
    if (!is_user_logged_in()) {
        echo json_encode(array('success' => false, 'error' => 'User not logged in.'));
        wp_die();
    }

    // Get the current user ID
    $user_id = get_current_user_id();

    // Get the verse details from the request
    $verseId = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';
    $versionId = isset($_POST['versionId']) ? sanitize_text_field($_POST['versionId']) : '';
    $verseText = isset($_POST['verseText']) ? wp_kses_post($_POST['verseText']) : '';

    // Check if the verseId is valid
    if (empty($verseId)) {
        echo json_encode(array('success' => false, 'error' => 'Invalid verse ID.'));
        wp_die();
    }

    global $wpdb;

    // Define the table name
    $table_name = $wpdb->prefix . 'iqbible_saved_verses';

    // Check if the verse is already saved by the user
    $existing = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE user_id = %d AND verse_id = %s",
        $user_id,
        $verseId
    ));

    if ($existing > 0) {
        echo json_encode(array('success' => false, 'error' => 'Verse already saved.'));
        wp_die();
    }

    // Insert the verse data into the saved verses table
    $inserted = $wpdb->insert(
        $table_name,
        array(
            'user_id' => $user_id,
            'verse_id' => $verseId,
            'version_id' => $versionId,
            'verse_text' => $verseText,
            'saved_at' => current_time('mysql')
        ),
        array('%d', '%s', '%s', '%s', '%s')
    );

    if ($inserted) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error saving verse.'));
    }

    wp_die();
}
add_action('wp_ajax_iq_bible_save_verse', 'iq_bible_save_verse_ajax_handler');



// PHP AJAX handler for getting saved verses
function iq_bible_get_saved_verses_ajax_handler()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        echo json_encode(array('success' => false, 'error' => 'User not logged in.'));
        wp_die();
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $table_name = $wpdb->prefix . 'iqbible_saved_verses';

    // Fetch saved verses including verse_text
    $saved_verses = $wpdb->get_results($wpdb->prepare(
        "SELECT verse_id, version_id, verse_text, saved_at FROM $table_name WHERE user_id = %d ORDER BY saved_at DESC",
        $user_id
    ));

    $response = array('success' => true, 'savedVerses' => array());

    foreach ($saved_verses as $saved) {
        // Split verse_id into components
        $bookId = substr($saved->verse_id, 0, 2);      // First 2 digits: bookId
        $chapterId = substr($saved->verse_id, 2, 3);   // Next 3 digits: chapterId
        $verseNumber = substr($saved->verse_id, 5, 3); // Last 3 digits: verse number

        // Get book name from session or API
        $bookName = iq_bible_get_book_name($bookId, $chapterId);

        // Add verse details to response
        $response['savedVerses'][] = array(
            'bookName' => $bookName,
            'bookId' => $bookId,
            'chapter' => $chapterId,
            'verseNumber' => $verseNumber,
            'verseId' => $saved->verse_id,
            'verseText' => $saved->verse_text,
            'versionId' => $saved->version_id,
            'savedAt' => $saved->saved_at
        );
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_iq_bible_get_saved_verses', 'iq_bible_get_saved_verses_ajax_handler');

// Helper function to get book name
function iq_bible_get_book_name($bookId, $chapterId)
{
    // Try to get book name from session first
    if (!empty($_SESSION['books'])) {
        $bookKey = array_search($bookId, array_column($_SESSION['books'], 'b'));
        if ($bookKey !== false) {
            return $_SESSION['books'][$bookKey]['n'] . ' ' . intval($chapterId);
        }
    }

    // Fallback to API call if needed
    $bookName = iq_bible_api_get_data('GetBookAndChapterNameByBookAndChapterId', array(
        'bookAndChapterId' => $bookId . $chapterId,
        'language' => 'english'
    ));

    return $bookName;
}



// Add new AJAX handler for verse deletion
function iq_bible_delete_saved_verse_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        echo json_encode(array('success' => false, 'error' => 'User not logged in.'));
        wp_die();
    }

    $user_id = get_current_user_id();
    $verse_id = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';

    if (empty($verse_id)) {
        echo json_encode(array('success' => false, 'error' => 'Invalid verse ID.'));
        wp_die();
    }

    global $wpdb;
    $table_name = $wpdb->prefix . 'iqbible_saved_verses';

    $deleted = $wpdb->delete(
        $table_name,
        array(
            'user_id' => $user_id,
            'verse_id' => $verse_id
        ),
        array('%d', '%s')
    );

    if ($deleted) {
        echo json_encode(array('success' => true));
    } else {
        echo json_encode(array('success' => false, 'error' => 'Error deleting verse.'));
    }

    wp_die();
}
add_action('wp_ajax_iq_bible_delete_saved_verse', 'iq_bible_delete_saved_verse_ajax_handler');







function clear_books_session()
{

       // ---> Verify Nonce <---
       check_ajax_referer('iqbible_ajax_nonce', 'security');
       // ---> End Verify Nonce <---

    if (isset($_POST['language'])) {
        $language = sanitize_text_field($_POST['language']);
        // Handle the language as needed, e.g., store it in the session or perform other actions
        $_SESSION['language'] = $language; // Example of saving it to session
    }

    // Clear the books session
    unset($_SESSION['books']);

    // Return a success response
    echo json_encode(['status' => 'success', 'message' => 'Books session cleared successfully']);
    wp_die(); // Required to terminate the AJAX request properly
}

// Hook the AJAX actions
add_action('wp_ajax_clear_books_session', 'clear_books_session');
add_action('wp_ajax_nopriv_clear_books_session', 'clear_books_session');




// Shortcode for the Registration Form
function iqbible_registration_form()
{
    if (is_user_logged_in()) {
        return '<p>You are already logged in.</p>';
    }

    // Display the form
    ob_start(); ?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">
        <p>
            <label for="username">Username</label>
            <input type="text" name="username" required>
        </p>
        <p>
            <label for="email">Email</label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" name="password" required>
        </p>
        <p>
            <input type="submit" name="submit_registration" value="Register">
        </p>
    </form>
<?php
    return ob_get_clean();
}
add_shortcode('iqbible_registration', 'iqbible_registration_form');

// Handle Registration Form Submission
function iqbible_register_user()
{
    if (isset($_POST['submit_registration'])) {
        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = esc_attr($_POST['password']);

        $errors = new WP_Error();

        // Validate fields
        if (username_exists($username) || email_exists($email)) {
            $errors->add('user_exists', 'Username or email already exists');
        }
        if (empty($username) || empty($email) || empty($password)) {
            $errors->add('field_empty', 'Please fill in all required fields');
        }

        // Register user if no errors
        if (empty($errors->get_error_messages())) {
            $user_id = wp_create_user($username, $password, $email);

            if (!is_wp_error($user_id)) {
                // Optionally, log the user in after registration
                wp_set_current_user($user_id);
                wp_set_auth_cookie($user_id);
                wp_redirect(home_url()); // Redirect to homepage or custom page
                exit;
            } else {
                echo '<p>Error creating user: ' . $user_id->get_error_message() . '</p>';
            }
        } else {
            foreach ($errors->get_error_messages() as $error) {
                echo '<p>' . $error . '</p>';
            }
        }
    }
}
add_action('init', 'iqbible_register_user');


// Shortcode for Profile Page
function iqbible_profile_form()
{
    if (!is_user_logged_in()) {
        return '<p>You need to be logged in to view your profile. <a href="' . esc_url(wp_login_url()) . '">Log in here</a>.</p>';
    }

    $current_user = wp_get_current_user();

    ob_start();
?>
    <h3>Your Profile</h3>
    <form method="post">
        <p>
            <label for="email">Email</label>
            <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
        </p>
        <p>
            <label for="first_name">First Name</label>
            <input type="text" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
        </p>
        <p>
            <label for="last_name">Last Name</label>
            <input type="text" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
        </p>
        <p>
            <input type="submit" name="update_profile" value="Update Profile">
        </p>
    </form>
    <?php

    // Handle profile update
    if (isset($_POST['update_profile'])) {
        wp_update_user(array(
            'ID'         => $current_user->ID,
            'user_email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
        ));
        echo '<p>Profile updated successfully!</p>';
    }

    return ob_get_clean();
}
add_shortcode('iqbible_profile', 'iqbible_profile_form');


// Shortcode for Logout Link
function iqbible_logout_link()
{
    if (is_user_logged_in()) {
        $logout_url = wp_logout_url(home_url());
        return '<a href="' . esc_url($logout_url) . '">Logout</a>';
    }
    return '<p>You are not logged in.</p>';
}
add_shortcode('iqbible_logout', 'iqbible_logout_link');


// Shortcode for Login Form
function iqbible_login_form()
{
    if (is_user_logged_in()) {
        return '<p>You are already logged in. <a href="' . esc_url(home_url()) . '">Go to homepage</a>.</p>';
    }

    ob_start();
    ?>
    <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <p>
            <label for="username">Username</label>
            <input type="text" name="log" required>
        </p>
        <p>
            <label for="password">Password</label>
            <input type="password" name="pwd" required>
        </p>
        <p>
            <input type="submit" name="wp-submit" value="Log In">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url(home_url()); ?>">
        </p>
    </form>
<?php

    return ob_get_clean();
}
add_shortcode('iqbible_login', 'iqbible_login_form');





function iq_bible_book_intro_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    // Check if bookId is provided in the request
    $bookId = isset($_POST['bookId']) ? sanitize_text_field($_POST['bookId']) : null;

    // If no bookId is provided, return an error
    if (!$bookId) {
        echo '<p>Error: No book ID provided.</p>';
        wp_die();
    }

    // Default language (can be dynamic if needed)
    $language = 'english';

    // Fetch book info using the GetBookInfo API
    $bookInfo = iq_bible_api_get_data('GetBookInfo', array(
        'bookId' => $bookId,
        'language' => $language
    ));

    // Check if we got a valid response
    if (!empty($bookInfo)) {
        echo '<div class="book-intro-content">';

        // Introduction
        if (isset($bookInfo['introduction'])) {
            echo '<h2>Introduction</h2>';
            echo '<p>' . esc_html($bookInfo['introduction']) . '</p>';
        }

        // Long Introduction
        if (isset($bookInfo['introduction_long'])) {
            echo '<h2>Long Introduction</h2>';
            echo '<p>' . esc_html($bookInfo['introduction_long']) . '</p>';
        }

        // Author
        if (isset($bookInfo['author'])) {
            echo '<h3>Author</h3>';
            echo '<p>' . esc_html($bookInfo['author']) . '</p>';
        }

        // Date
        if (isset($bookInfo['date'])) {
            echo '<h3>Date</h3>';
            echo '<p>' . esc_html($bookInfo['date']) . '</p>';
        }

        // Word Origin
        if (isset($bookInfo['word_origin'])) {
            echo '<h3>Word Origin</h3>';
            echo '<p>' . esc_html($bookInfo['word_origin']) . '</p>';
        }

        // Genre
        if (isset($bookInfo['genre'])) {
            echo '<h3>Genre</h3>';
            echo '<p>' . esc_html($bookInfo['genre']) . '</p>';
        }

        // Theological Details (if available)
        if (isset($bookInfo['theological_introduction'])) {
            echo '<h2>Theological Introduction</h2>';
            echo '<p>' . esc_html($bookInfo['theological_introduction']) . '</p>';
        }

        // Additional Keys
        foreach ($bookInfo as $key => $value) {
            if (!in_array($key, ['introduction', 'introduction_long', 'author', 'date', 'word_origin', 'genre', 'theological_introduction'])) {
                echo '<h3>' . esc_html(ucwords(str_replace('_', ' ', $key))) . '</h3>';
                if (is_array($value)) {
                    echo '<ul>';
                    foreach ($value as $item) {
                        if (is_array($item)) {
                            echo '<li>';
                            foreach ($item as $subKey => $subValue) {
                                echo '<strong>' . esc_html(ucwords(str_replace('_', ' ', $subKey))) . ':</strong> ' . esc_html($subValue) . '<br>';
                            }
                            echo '</li>';
                        } else {
                            echo '<li>' . esc_html($item) . '</li>';
                        }
                    }
                    echo '</ul>';
                } else {
                    echo '<p>' . esc_html($value) . '</p>';
                }
            }
        }

        echo '</div>';
    } else {
        echo '<p>No introduction found for this book.</p>';
    }

    wp_die(); // End the AJAX request
}


// Register the AJAX action for logged-in and guest users
add_action('wp_ajax_iq_bible_book_intro', 'iq_bible_book_intro_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_book_intro', 'iq_bible_book_intro_ajax_handler');

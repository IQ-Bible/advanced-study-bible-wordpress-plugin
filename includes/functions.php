<?php // Functions

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}



// Book icons
function iqbible_get_book_icon_url($bookName)
{
    $formattedBookName = strtolower(str_replace(' ', '-', $bookName));
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


    // Build the base API URL
    $base_url = 'https://iq-bible.p.rapidapi.com/' . $endpoint;
    $url_with_params = add_query_arg($params, $base_url);
    error_log('Requesting URL: ' . $url_with_params);
    $args = array(
        'headers' => array(
            'x-rapidapi-host' => 'iq-bible.p.rapidapi.com',
            'x-rapidapi-key' => $api_key
        ),
        'timeout' => 15
    );
    $response = wp_remote_get($url_with_params, $args);


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

    echo sprintf('<h3>%1$d %2$s \'%3$s\'</h3>', count($searchResults), esc_html__('Search Results for ', 'iqbible'), esc_html($query));

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


            $books_data = iq_bible_get_books_data();
            $books = $books_data['all'];

            $bookName = __('Unknown Book Name', 'iqbible');
            foreach ($books as $book) {
                if ($book['b'] == $bookId) {
                    $bookName = esc_html($book['n']);
                    break;
                }
            }


            // Escape the original text *before* highlighting
            $safe_text = esc_html($text);
            $boldText = preg_replace(
                '/(' . preg_quote($query, '/') . ')/i',
                '<strong>$1</strong>',
                $safe_text
            );

            // Use verse-{verseId} format for the verse identifier
            echo "<li><a href='javascript:void(0)' 
                     class='bible-search-result'
                    data-book-id='" . esc_attr($bookId) . "'
                    data-chapter-id='" . esc_attr($chapterId) . "'
                    data-verse-id='verse-" . esc_attr($verseId) . "'
                    data-version-id='" . esc_attr($versionId) . "'>{$boldText}</a><br> 
                   - " . esc_html($bookName) . " " . intval($chapterId) . ":" . intval($verse) . " (" . esc_html(strtoupper($versionId)) . ")</li><br>";
        }

        echo "</ol>";
    } else {
        // translators: %s: The user's search query.
        echo '<p>' . sprintf(esc_html__('No results found for: \'%s\'.', 'iqbible'), esc_html($query)) . '</p>';
        if (count($searchResults) == 0) {
            // translators: %s: The abbreviation of the Bible version being used (e.g., KJV).
            echo '<i>' . sprintf(esc_html__('Remember, you are using the %s version. Check your spelling for the appropriate version!', 'iqbible'), strtoupper(esc_html($versionId))) . '</i>';
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
        esc_html_e('Please enter a biblical word to define.', 'iqbible');
        wp_die();
    }

    // Fetch the biblical definition using the API
    set_transient('iqbible_dictionaryId', 'smiths', DAY_IN_SECONDS);

    set_transient('iqbible_dictionaryIdFullName', __('Smith\'s Bible Dictionary', 'iqbible'), DAY_IN_SECONDS);

    $definition_biblical = iq_bible_api_get_data('GetDefinitionBiblical', array('query' => $query, 'dictionaryId' => get_transient('iqbible_dictionaryId')));


    if (!empty($definition_biblical)) {
        echo '<small><i>' . sprintf(esc_html__('From %s:', 'iqbible'),     esc_html(get_transient('iqbible_dictionaryIdFullName'))) . '</i></small><br>';

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
        // translators: %s: The word the user tried to define.
        echo sprintf(esc_html__('No biblical definition found for %s.', 'iqbible'), esc_html($query));
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
        esc_html_e('Invalid input.', 'iqbible');
        wp_die();
    }

    // Fetch the Strong's data using the API
    $strongs = iq_bible_api_get_data('GetStrongs', array('lexiconId' => $lexicon, 'id' => $id));

    // Output the formatted results
    if (!empty($strongs)) {
        foreach ($strongs as $entry) {
            echo '<div class="strongs-entry">';
            echo '<small><i>' . esc_html__('Strong\'s ID:', 'iqbible') . ' ' . esc_html($entry['strongs_id']) . '</i></small>';
            echo '<h3>' . esc_html($entry['word']) . '</h3>';
            echo '<p>' . esc_html($entry['glossary']) . '</p>';
            echo '</div>';
        }
    } else {
        esc_html_e('No concordance results found.', 'iqbible');
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
        wp_send_json_error(['error' => __('Verse ID is required.', 'iqbible')]);
        wp_die();
    }

    // Fetch cross references using the API
    $crossReferences = iq_bible_api_get_data('GetCrossReferences', array('verseId' => $verseId));

    if (!empty($crossReferences)) {


        $books_data = iq_bible_get_books_data(); // Call the helper
        $books = $books_data['all']; // Get the 'all' books array


        // Prepare the list to display cross references
        $referencesList = '<ul class="cross-references-list">';

        foreach ($crossReferences as $crossReference) {
            $sv = $crossReference['sv']; // Start verse (e.g., 19104030)
            // Parse the bookId, chapter, and verse from 'sv'
            $bookId = substr($sv, 0, 2);  // First two digits represent the book ID
            $chapterId = intval(substr($sv, 2, 3)); // Next three digits represent the chapter number
            $verseNumber = intval(substr($sv, 5, 3));   // Last three digits represent the verse number

            // Find the book name in the session data
            $bookName = __('Unknown Book', 'iqbible');
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
        esc_html_e('No cross references found.', 'iqbible');
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
        esc_html_e('Invalid verse ID.', 'iqbible');
        wp_die();
    }

    // Fetch the original text using the API
    $originalTexts = iq_bible_api_get_data('GetOriginalText', array('verseId' => $verseId));

    // Determine if it's Hebrew (Old Testament) or Greek (New Testament)
    $isHebrew = $originalTexts[0]['book'] <= 39;
    $lexicon = $isHebrew ? "H" : "G";

    // Display language header
    if ($isHebrew) {
        esc_html_e('Hebrew', 'iqbible');
        echo '<br><small><i>' . esc_html__('Original Hebrew is read from right to left &larr;', 'iqbible') . '</i></small>';
    } else {
        esc_html_e('Greek', 'iqbible');
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
                // translators: %d: The sequential number for a word in the original text view.
                echo '<strong>' . sprintf(esc_html__('#%d:', 'iqbible'), $ct) . ' </strong>';
                // Just the Hebrew word is RTL
                echo '<span style="direction: rtl; display: inline-block;">' . esc_html($originalText['word']) . '</span><br>';
                echo '<strong>' . esc_html__('Pronunciation:', 'iqbible') . '</strong> ' . esc_html($pronunciation['dic_mod']) . '<br>';
                echo '<strong>' . esc_html__('Pronunciation:', 'iqbible') . '</strong> ' . $lexicon . esc_html($originalText['strongs']) . '<br>';
                echo '<strong>' . esc_html__('Strong\'s Glossary:', 'iqbible') . '</strong> ' . esc_html($glossary) . '<br>';
                echo '</div>';
            } else {
                // Greek word details (all LTR)
                echo '<strong>' . sprintf(esc_html__('#%d:', 'iqbible'), $ct) . '</strong> ' . esc_html($originalText['word']) . '<br>';
                echo '<strong>' . esc_html__('Pronunciation:', 'iqbible') . '</strong> ' . esc_html($pronunciation['dic_mod']) . '<br>';
                echo '<strong>' . esc_html__('Strong\'s ID:', 'iqbible') . '</strong> ' . $lexicon . esc_html($originalText['strongs']) . '<br>';
                echo '<strong>' . esc_html__('Strong\'s Glossary:', 'iqbible') . '</strong> ' . $glossary . '<br>';
            }

            echo '</div>';
        }
    } else {
        esc_html_e('No original text found for the specified verse ID.', 'iqbible');
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


    $day_count = 1;

    // --- Get form data ---
    $days = isset($_POST['days']) ? sanitize_text_field($_POST['days']) : '365';
    $requestedStartDateInput = isset($_POST['requestedStartDate']) ? sanitize_text_field($_POST['requestedStartDate']) : date('Y-m-d');
    try {
        $startDateCheck = new DateTime($requestedStartDateInput);
        $requestedStartDate = $startDateCheck->format('Y-m-d');
    } catch (Exception $e) {
        $requestedStartDate = date('Y-m-d');
    }
    $sections = isset($_POST['sections']) ? sanitize_text_field($_POST['sections']) : 'all';
    $requestedAge = isset($_POST['requestedAge']) ? intval($_POST['requestedAge']) : 15;

    $planNameInput = isset($_POST['iqbible-planName']) ? sanitize_text_field(stripslashes($_POST['iqbible-planName'])) : __('Default Plan', 'iqbible');

    // --- Handle custom days ---
    if ($days === 'custom') {
        $customDays = isset($_POST['customDays']) ? intval($_POST['customDays']) : 0;
        if ($customDays > 0) {
            $days = $customDays;
        } else {
            wp_send_json_error(array('message' => __('Invalid number of days.', 'iqbible')));
            return;
        }
    } else {
        $days = intval($days);
        if ($days <= 0) {
            $days = 365;
        }
    }
    $days = min($days, 365 * 5); // Limit duration


    // --- Call API ---
    $planResults = iq_bible_api_get_data(
        'GetBibleReadingPlan',
        array(
            'days'             => $days,
            'requestedStartDate' => $requestedStartDate,
            'sections'         => $sections,
            'requestedAge'     => $requestedAge,
        )
    );

    // --- Validate API Response ---
    if (empty($planResults) || ! is_array($planResults) || ! isset($planResults[0]['datesInfo']) || ! is_array($planResults[0]['datesInfo']) || ! isset($planResults[0]['datesInfo']['startDate']) || ! isset($planResults[0]['datesInfo']['endDate'])) {
        // Log error for server admin if needed: error_log('IQBible Plan Error: Invalid API response structure.');
        wp_send_json_error(array('message' => esc_html__('Invalid plan data received from API. Please try again.', 'iqbible')));
        return;
    }

    // --- Prepare HTML Output ---
    ob_start();

    // Extract and Validate Dates
    $planDetails = $planResults[0]['datesInfo'];
    $startDate   = null;
    $endDate     = null;
    try {
        $startDate = new DateTime($planDetails['startDate']);
        $endDate   = new DateTime($planDetails['endDate']);
    } catch (Exception $e) {
        ob_end_clean();
        // Log error for server admin if needed: error_log('IQBible Plan Error: Failed to parse dates from API - ' . $e->getMessage());
        wp_send_json_error(array('message' => esc_html__('Error processing plan dates.', 'iqbible')));
        return;
    }
    $duration = $startDate->diff($endDate)->days;


    // --- Plan Header ---
    echo "<div id='printable-plan-content'>"; // Start wrapper

    echo "<div class='plan-details' id='plan-details'>";
    echo "<h2>" . esc_html($planNameInput) . " <span><br><small>" . esc_html__('Bible Reading Plan', 'iqbible') . "</small></span></h2>";

    echo "<p><strong>" . esc_html__('Start Date:', 'iqbible') . "</strong> " . date_i18n(get_option('date_format'), $startDate->getTimestamp()) . "</p>";
    echo "<p><strong>" . esc_html__('End Date:', 'iqbible') . "</strong> " . date_i18n(get_option('date_format'), $endDate->getTimestamp()) . "</p>";
    // translators: %d: The number of days in the reading plan duration.
    echo "<p><strong>" . esc_html__('Duration:', 'iqbible') . "</strong> " . sprintf(_n('%d day', '%d days', $duration, 'iqbible'), $duration) . "</p>";
    echo "</div>"; // End plan-details

    // --- Print Button ---
    echo '<div class="iqbible-print-plan-action">';
    echo '<button id="print-reading-plan-btn" class="button button-secondary">' . esc_html__('Print / Save as PDF', 'iqbible') . '</button>';
    echo '</div>';

    // --- Generate Reading List HTML ---
    echo "<div class='reading-plan-list'>"; // Start list container



    $books_data = iq_bible_get_books_data(); // Call the helper
    $books = $books_data['all'] ?? array(); // Get 'all' books, default to empty array



    $book_map = array();
    // Ensure $books is a non-empty array before proceeding
    if (is_array($books) && !empty($books)) {
        // Filter out invalid book entries before creating the map
        $valid_books = array_filter($books, function ($book) {
            return is_array($book) && isset($book['b']) && isset($book['n']);
        });
        // Create map only if there are valid books
        if (!empty($valid_books)) {
            $book_map = array_column($valid_books, 'n', 'b');
        }
    } // If $books wasn't valid or empty, $book_map remains an empty array

    $currentDate  = clone $startDate;
    $loopEndDate  = clone $endDate;
    $output_started = false;
    $prev_month_year = null;

    // ** Main Loop for Days **
    while ($currentDate <= $loopEndDate) {
        $current_ymd = $currentDate->format('Y-m-d');
        $day_entry   = null;

        // Find the API entry for the current date safely
        foreach ($planResults as $entry) {
            if (is_array($entry) && isset($entry['date']) && $entry['date'] === $current_ymd) {
                $day_entry = $entry;
                break;
            }
        }

        // Month/Year Header
        $month_year = date_i18n('F, Y', $currentDate->getTimestamp());
        if (! $output_started || ($month_year !== $prev_month_year)) {
            if ($output_started) {
                echo '</ul>';
            } // Close previous list if needed
            echo "<h3>" . esc_html($month_year) . "</h3><ul>"; // Start new month list
            $output_started  = true;
            $prev_month_year = $month_year;
        }

        // Day's Reading Item

        $day_of_week = date_i18n('l', $currentDate->getTimestamp());
        $day_label   = date_i18n(get_option('date_format'), $currentDate->getTimestamp());
        echo "<li style='list-style-type:none;'>Day " . $day_count . ": <strong>" . esc_html($day_of_week) . ", " . esc_html($day_label) . "</strong><br>";

        // Check day_entry and bookAndChapterIds structure defensively
        if ($day_entry && isset($day_entry['bookAndChapterIds']) && is_array($day_entry['bookAndChapterIds']) && !empty($day_entry['bookAndChapterIds'])) {
            $readings_html = [];
            foreach ($day_entry['bookAndChapterIds'] as $id) {
                // Ensure ID is usable
                if (!is_scalar($id)) {
                    continue;
                } // Skip non-scalar IDs

                $id_str    = str_pad((string)$id, 5, '0', STR_PAD_LEFT);
                $bookId    = intval(substr($id_str, 0, 2));
                $chapterId = intval(substr($id_str, -3));
                // Use book map safely with null coalescing operator ??
                $bookName  = $book_map[$bookId] ?? __('Unknown Book', 'iqbible');

                $readings_html[] = sprintf(
                    '<label class="chapter-checkbox-label" style="margin-right: 10px;">
                        <input type="checkbox" class="chapter-checkbox" data-reading-ref="%s">
                        <a href="#" class="reading-plan-link" data-book-id="%s" data-chapter-id="%s">%s %s</a>
                    </label>',
                    esc_attr($bookId . '-' . $chapterId),
                    esc_attr($bookId),
                    esc_attr($chapterId),
                    esc_html($bookName),
                    esc_html($chapterId)
                );
            } // End foreach $id

            // Only implode if there's something to implode
            if (!empty($readings_html)) {
                echo implode(' ', $readings_html);
            } else {
                // This case might occur if all IDs inside were invalid scalars
                echo '<span>' . esc_html__('No valid readings found for this day.', 'iqbible') . '</span>';
            }
        } else {
            // No readings assigned for this day
            echo '<span>' . esc_html__('No reading assigned for this day.', 'iqbible') . '</span>';
        }
        echo "</li><hr>"; // End list item

        $currentDate->modify('+1 day'); // Increment day
        $day_count++;
    } // ** End while loop **

    // ** Close Final Tags **
    if ($output_started) {
        echo '</ul>';
    } // Close the last month's list
    echo "</div>"; // End reading-plan-list
    echo "</div>"; // End #printable-plan-content

    // --- Get Buffered HTML ---
    $output_html = ob_get_clean();

    // --- Send JSON Success Response ---
    // Ensure the HTML isn't empty before sending success
    if (empty(trim($output_html))) {
        // Log error for server admin if needed: error_log('IQBible Plan Error: Generated HTML was empty.');
        wp_send_json_error(array('message' => esc_html__('Failed to generate plan content.', 'iqbible')));
    } else {
        wp_send_json_success(array('html' => $output_html));
    }

    // wp_die() is called implicitly
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
        esc_html_e('Invalid input.', 'iqbible');
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
        esc_html_e('No results found for this topic.', 'iqbible');
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
        wp_send_json_error(['error' => __('Invalid book ID or chapter ID.', 'iqbible')]);
        wp_die();
    }

    // Fetch Total Chapters for the *specific book requested*
    $chapterCountData = iq_bible_api_get_data('GetChapterCount', array('bookId' => $bookId));
    $totalChapters = isset($chapterCountData['chapterCount']) ? intval($chapterCountData['chapterCount']) : 0;

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
    $saved_verses = $wpdb->get_col($wpdb->prepare(
        "SELECT verse_id FROM $table_name 
        WHERE user_id = %d 
        AND verse_id LIKE %s",
        $user_id,
        $verseIdPrefix . '%'
    ));
    set_transient('iqbible_saved_verses', $saved_verses, DAY_IN_SECONDS);

    // Fetch the Bible chapter data using the API
    $chapter = iq_bible_api_get_data('GetChapter', array(
        'bookId' => $bookId,
        'chapterId' => $chapterId,
        'versionId' => $versionId
    ));

    // Fetch the book name by book ID
    $bookNameResponse = iq_bible_api_get_data('GetBookNameByBookId', array(
        'bookId' => $bookId,
        'language' => get_transient('iqbible_language')

    ));

    // Extract the book name from the response
    $bookName = isset($bookNameResponse[0]['n']) ? $bookNameResponse[0]['n'] : __('Unknown Book', 'iqbible');

    // Prepare the response
    $response = array(
        'chapterContent' => '',
        'bookName' => $bookName,
        'totalChapters' => $totalChapters,
        'savedVerses' => $saved_verses
    );

    // Fetch stories from transient
    $stories_by_verse = get_transient('iqbible_stories_by_verse');
    if (! $stories_by_verse) {
        $stories_by_verse = array();
    }


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
            $response['chapterContent'] .= '<div class="verse" id="verse-' . esc_attr($verseId) . '" data-verse-id="' . esc_attr($verseId) . '" data-version-id="' . esc_attr($versionId) . '">';

            $response['chapterContent'] .= '<sup>' . esc_html($verse['v']) . '</sup>&nbsp;';
            $response['chapterContent'] .= '<span class="copyable-text">' . esc_html($verse['t']) . '</span>';


            // Add saved icon if verse is saved
            if (in_array($verseId, $saved_verses)) {
                $response['chapterContent'] .= '&nbsp;<img src="' . esc_url(plugin_dir_url(__DIR__) . 'assets/img/bookmark.svg') . '" alt="' . esc_attr__('Saved Verse Icon', 'iqbible') . '" class="saved-icon" title="' . esc_attr__('Saved Verse', 'iqbible') . '" >';
            }

            // Add verse options
            $chapterNumber = $paddedChapterId;
            $siteName = get_transient('iqbible_siteName');



            // Verse options section - Using sprintf for cleaner I18N
            $copy_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/clipboard.svg');
            $key_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/key.svg');
            $comment_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/message-square.svg');
            $crosshair_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/crosshair.svg');
            $share_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/share.svg');
            $bookmark_icon_url = esc_url(plugin_dir_url(__DIR__) . 'assets/img/bookmark.svg');



            // Ensure base URL is clean for data attribute
            $base_url_esc = esc_url(get_transient('iqbible_baseUrl'));

            // Build share URL components safely
            $share_url = add_query_arg([
                'bookId' => $bookId,
                'chapterId' => $chapterId,
                'versionId' => $versionId
            ], $base_url_esc) . '#verse-' . $verseId;



            // --- Determine text direction for copy function based on versionId ---
            $copy_text_direction = 'ltr'; // Default to Left-to-Right
            $rtl_versions = ['svd']; // Add known RTL version abbreviations here (lowercase)
            if (in_array(strtolower($versionId), $rtl_versions, true)) {
                $copy_text_direction = 'rtl';
            }
            // --- End direction determination ---


            $response['chapterContent'] .= sprintf(
                '<div class="verse-options">
                    <button class="option-button" onclick="copyVerse(\'%1$s\', \'%2$s\', %3$d, \'%4$s\', \'%5$s\', \'%6$s\')">
                        <img src="%7$s" alt="%8$s"> %9$s
                    </button>
                    <button class="option-button" onclick="showOriginalText(\'%1$s\')">
                        <img src="%10$s" alt="%11$s"> %12$s
                    </button>
                    <button class="option-button" onclick="showCommentary(\'%1$s\')">
                        <img src="%13$s" alt="%14$s"> %15$s
                    </button>
                    <button class="option-button" onclick="showCrossReferences(\'%1$s\')">
                        <img src="%16$s" alt="%17$s"> %18$s
                    </button>
                    <button class="option-button" onclick="shareVerse(\'%1$s\')" data-url="%19$s">
                        <img src="%20$s" alt="%21$s"> %22$s
                    </button>
                    <button class="option-button" onclick="saveVerse(\'%1$s\')">
                        <img src="%23$s" alt="%24$s"> %25$s
                    </button>
                    <div class="verse-message" id="verse-message-%1$s"></div>
                </div>',
                esc_js($verseId),                      // %1$s - verseId (escaped for JS)
                esc_js($bookName),                     // %2$s - bookName (escaped for JS)
                intval($chapterNumber),                // %3$d - chapterNumber (integer)
                esc_js($versionId),                    // %4$s - versionId (escaped for JS)
                esc_js($siteName),                     // %5$s - siteName (escaped for JS)
                esc_js($copy_text_direction),                     // %6$s - session language (already escaped)
                $copy_icon_url,                        // %7$s - copy icon URL
                esc_attr__('Copy Icon', 'iqbible'),    // %8$s - copy icon alt text
                esc_html__('Copy', 'iqbible'),         // %9$s - copy button text
                $key_icon_url,                         // %10$s - key icon URL
                esc_attr__('Original Text Icon', 'iqbible'), // %11$s - key icon alt text
                esc_html__('Original Text', 'iqbible'), // %12$s - key button text
                $comment_icon_url,                     // %13$s - comment icon URL
                esc_attr__('Commentary Icon', 'iqbible'), // %14$s - comment icon alt text
                esc_html__('Commentary', 'iqbible'),   // %15$s - comment button text
                $crosshair_icon_url,                   // %16$s - crosshair icon URL
                esc_attr__('Cross References Icon', 'iqbible'), // %17$s - crosshair icon alt text
                esc_html__('Cross References', 'iqbible'), // %18$s - crosshair button text
                esc_url($share_url),                   // %19$s - share URL (already built and escaped)
                $share_icon_url,                       // %20$s - share icon URL
                esc_attr__('Share Icon', 'iqbible'),   // %21$s - share icon alt text
                esc_html__('Share', 'iqbible'),        // %22$s - share button text
                $bookmark_icon_url,                    // %23$s - bookmark icon URL
                esc_attr__('Bookmark Icon', 'iqbible'), // %24$s - bookmark icon alt text
                esc_html__('Bookmark', 'iqbible'),      // %25$s - bookmark button text
                esc_attr($verseId)                     // %26$s - escaped for HTML id
            );

            $response['chapterContent'] .= "</div>"; // Close verse div

        }
    } else {
        $response['chapterContent'] = esc_html__('No chapter content results found.', 'iqbible');
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
    delete_transient('iqbible_bookId');
    delete_transient('iqbible_chapterData');


    // Get book ID from AJAX request
    $bookId = isset($_POST['bookId']) ? sanitize_text_field($_POST['bookId']) : '';
    $bookCategory = isset($_POST['bookCategory']) ? sanitize_text_field($_POST['bookCategory']) : '';

    // Log the bookId being sent to the API
    error_log('Book ID sent to API: ' . $bookId);

    if (empty($bookId)) {
        echo json_encode(array('error' => __('Invalid book ID.', 'iqbible')));
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






/**
 * Retrieves the current language preference.
 * @return string The language code (e.g., 'english').
 */
function iq_bible_get_current_language()
{

    $language = get_transient('iqbible_language');
    if (!$language) {
        $language = 'english'; // Default to 'english' if transient is not set
    }
    return $language;
}

/**
 * Gets Bible book data (OT, NT, All) for a specific language, using transients for caching.
 * Replaces the logic previously in iq_bible_ensure_books_session.
 *
 * @param string|null $language The language code. If null, uses iq_bible_get_current_language().
 * @return array An array containing 'ot', 'nt', and 'all' book lists, or empty arrays on failure.
 */
function iq_bible_get_books_data($language = null)
{
    if (empty($language)) {
        // Use the temporary session-reading function to get the language
        $language = iq_bible_get_current_language();
    }
    $transient_key = 'iqbible_books_' . sanitize_key($language);
    $cached_books = get_transient($transient_key);

    // Validate cached data structure
    if (
        false !== $cached_books && is_array($cached_books)
        && isset($cached_books['ot']) && is_array($cached_books['ot'])
        && isset($cached_books['nt']) && is_array($cached_books['nt'])
        && isset($cached_books['all']) && is_array($cached_books['all'])
    ) {
        return $cached_books; // Return valid cached data
    }

    // Transient empty or invalid, fetch from API using the determined language
    $booksOT = iq_bible_api_get_data('GetBooksOT', ['language' => $language]);
    $booksNT = iq_bible_api_get_data('GetBooksNT', ['language' => $language]);

    // Ensure results are arrays
    $booksOT = is_array($booksOT) ? $booksOT : [];
    $booksNT = is_array($booksNT) ? $booksNT : [];

    $books_data = [
        'ot'  => $booksOT,
        'nt'  => $booksNT,
        'all' => array_merge($booksOT, $booksNT),
    ];

    // Cache for 1 day (adjust as needed)
    set_transient($transient_key, $books_data, DAY_IN_SECONDS);

    return $books_data;
}










// AJAX handler to fetch the book names and output them in HTML
function iq_bible_books_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---



    $books_data = iq_bible_get_books_data(); // Call the helper
    $booksOT = $books_data['ot']; // Get 'ot' books from the result
    $booksNT = $books_data['nt']; // Get 'nt' books from the result



    // Start output buffering to capture the HTML
    ob_start();


    // Display Old Testament books
    if (!empty($booksOT)) {
        echo '<h3>' . esc_html__('Old Testament', 'iqbible') . '</h3>';
        echo '<ul>';
        foreach ($booksOT as $bookOT) {
            echo '<li class="book-item" data-book-id="' . esc_attr($bookOT['b']) . '" data-book-category="OT">' . esc_html($bookOT['n']) . '</li>';
        }
        echo '</ul>';
    }

    // Display New Testament books
    if (!empty($booksNT)) {
        echo '<h3>' . esc_html__('New Testament', 'iqbible') . '</h3>';
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








// function iq_bible_clear_plugin_cache($old_value, $new_value)
// {
//     global $wpdb;

//     // Check if the API key has changed
//     if ($old_value !== $new_value) {
//         // Clear all transients related to the IQBible plugin
//         $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_iqbible_%'");
//         $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_iqbible_%'");

//         error_log("Cache cleared due to API key update!");
//     }
// }

// // Manual cache clearing via form submission
// add_action('admin_post_iqbible_clear_plugin_cache', 'iq_bible_clear_plugin_cache_form');

// function iq_bible_clear_plugin_cache_form()
// {

//     check_admin_referer('iqbible_clear_cache_action', 'iqbible_clear_cache_nonce');

//     if (!current_user_can('manage_options')) {
//         wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'iqbible'));
//     }

//     global $wpdb;

//     // Clear all transients related to the IQBible plugin
//     $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_iqbible_%'");
//     $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE '%_transient_timeout_iqbible_%'");

//     error_log("Cache manually cleared!");

//     // Redirect back to the settings page with a success message
//     wp_redirect(add_query_arg('cache_cleared', 'true', wp_get_referer()));
//     exit;
// }














function iq_bible_clear_plugin_cache($old_value, $new_value)
{
    global $wpdb;


    if ($old_value !== $new_value) {

        $transient_prefix = '_transient_iqbible_';
        $timeout_prefix = '_transient_timeout_iqbible_';

        $sql_transient = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($transient_prefix) . '%'
        );
        $wpdb->query($sql_transient);
        $sql_timeout = $wpdb->prepare(
            "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
            $wpdb->esc_like($timeout_prefix) . '%'
        );
        $wpdb->query($sql_timeout);

        error_log("IQBible Cache cleared due to API key update!");
    }
}


add_action('admin_post_iqbible_clear_plugin_cache', 'iq_bible_clear_plugin_cache_form');

function iq_bible_clear_plugin_cache_form()
{

    check_admin_referer('iqbible_clear_cache_action', 'iqbible_clear_cache_nonce');


    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have sufficient permissions to perform this action.', 'iqbible'));
    }

    global $wpdb;

    $transient_prefix = '_transient_iqbible_';
    $timeout_prefix = '_transient_timeout_iqbible_';

    $sql_transient = $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like($transient_prefix) . '%'
    );
    $wpdb->query($sql_transient);

    $sql_timeout = $wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
        $wpdb->esc_like($timeout_prefix) . '%'
    );
    $wpdb->query($sql_timeout);

    error_log("IQBible Cache manually cleared by user!");


    $redirect_url = add_query_arg('cache_cleared', 'true', wp_get_referer());
    wp_safe_redirect($redirect_url);
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
        echo json_encode(array('error' => __('No versions found', 'iqbible')));
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
        wp_send_json_error(__('User not logged in', 'iqbible'));
    }

    $user_id = get_current_user_id();
    $note_text = isset($_POST['note_text']) ? wp_kses_post($_POST['note_text']) : '';

    if (empty($note_text)) {
        wp_send_json_error(__('Note content is empty.', 'iqbible'));
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
        wp_send_json_error(__('Failed to save the note.', 'iqbible'));
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
        wp_send_json_error(__('User not logged in', 'iqbible'));
    }

    global $wpdb;

    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;
    $note_text = isset($_POST['note_text']) ? stripslashes(wp_kses_post($_POST['note_text'])) : ''; // Use stripslashes() to remove slashes

    if ($note_id === 0 || empty($note_text)) {
        wp_send_json_error(__('Invalid note ID or content.', 'iqbible'));
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
        wp_send_json_error(__('Failed to update note.', 'iqbible'));
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
        wp_send_json_error(__('User not logged in', 'iqbible'));
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
        wp_send_json_error(__('No notes found!', 'iqbible'));
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
        wp_send_json_error(__('User not logged in', 'iqbible'));
    }

    $note_id = isset($_POST['note_id']) ? intval($_POST['note_id']) : 0;

    if ($note_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'iqbible_notes';

        $result = $wpdb->delete($table_name, array('id' => $note_id, 'user_id' => get_current_user_id()));

        if ($result !== false) {
            wp_send_json_success();
        } else {
            wp_send_json_error(__('Failed to delete note.', 'iqbible'));
        }
    } else {
        wp_send_json_error(__('Invalid note ID.', 'iqbible'));
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
        wp_send_json_error(['success' => false, 'error' => __('User not logged in.', 'iqbible')]);
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
        echo json_encode(array('success' => false, 'error' => __('Invalid verse ID.', 'iqbible')));
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
        echo json_encode(array('success' => false, 'error' => __('Verse already saved.', 'iqbible')));
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
        echo json_encode(array('success' => false, 'error' => __('Error saving verse.', 'iqbible')));
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
        echo json_encode(array('success' => false, 'error' => __('User not logged in.', 'iqbible')));
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
            'verseText' => stripslashes($saved->verse_text),
            'versionId' => $saved->version_id,
            'savedAt' => $saved->saved_at
        );
    }

    echo json_encode($response);
    wp_die();
}
add_action('wp_ajax_iq_bible_get_saved_verses', 'iq_bible_get_saved_verses_ajax_handler');





/**
 * Helper function to get book name and chapter string (e.g., "Genesis 1").
 * Checks transient cache first, then falls back to API.
 *
 * @param string $bookId The book ID (e.g., '01', '40').
 * @param string $chapterId The chapter ID (e.g., '001', '10').
 * @return string The formatted book name and chapter (e.g., "Genesis 1"),
 *                or a fallback string like "Unknown Book 1".
 */
function iq_bible_get_book_name($bookId, $chapterId)
{
    // Step 1: Try to get the book name from the transient cache first.
    // The iq_bible_get_books_data() helper handles getting the language
    // (temporarily via session) and checking the transient.
    $books_data = iq_bible_get_books_data(); // Uses iq_bible_get_current_language() internally
    $books = $books_data['all']; // Get the combined list ('ot' + 'nt')

    // Ensure $books is a non-empty array before searching
    if (is_array($books) && !empty($books)) {
        // Use array_column safely to get just the book IDs ('b' column)
        // Suppress errors just in case the 'b' key isn't in every element
        $book_id_column = @array_column($books, 'b');

        if (is_array($book_id_column)) {
            // Search for the $bookId within the extracted column
            $bookKey = array_search($bookId, $book_id_column);

            // Check if found and if the original $books array has the 'n' (name) key at that index
            if ($bookKey !== false && isset($books[$bookKey]['n'])) {
                // Found in cache! Return the name and chapter number.
                // Use intval() to remove leading zeros from chapter for display.
                return $books[$bookKey]['n'] . ' ' . intval($chapterId);
            }
        }
    }

    // Step 2: Fallback to API call if not found in cache.
    // Determine the language for the API call.
    // !!! We still use the temporary function here which reads from session !!!
    $api_language = iq_bible_get_current_language();

    // Pad the chapter ID if required by the specific API endpoint 'GetBookAndChapterNameByBookAndChapterId'.
    // Assuming it expects a 5-digit format (BBCCC - BookBookChapterChapterChapter)
    $padded_chapterId = str_pad($chapterId, 3, '0', STR_PAD_LEFT);
    $bookAndChapterId_param = $bookId . $padded_chapterId; // e.g., "01001"

    // Make the API call
    $bookNameData = iq_bible_api_get_data('GetBookAndChapterNameByBookAndChapterId', array(
        'bookAndChapterId' => $bookAndChapterId_param,
        'language' => $api_language // Use the determined language
    ));

    // Process the API response
    // Adjust the checks below based on the *actual* structure returned by your API
    if (is_array($bookNameData) && !empty($bookNameData) && isset($bookNameData[0]['name'])) {
        // Example: Assuming API returns like [{ "name": "Genesis 1" }]
        return $bookNameData[0]['name'];
    } elseif (is_string($bookNameData) && !empty($bookNameData)) {
        // Example: Assuming API returns the string directly "Genesis 1"
        return $bookNameData;
    }
    // Add more conditions here if the API returns data differently

    // Step 3: Ultimate fallback if API fails or returns unexpected data.
    return __('Unknown Book', 'iqbible') . ' ' . intval($chapterId);
}




// Add new AJAX handler for verse deletion
function iq_bible_delete_saved_verse_ajax_handler()
{

    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');
    // ---> End Verify Nonce <---

    if (!is_user_logged_in()) {
        echo json_encode(array('success' => false, 'error' => __('User not logged in.', 'iqbible')));
        wp_die();
    }

    $user_id = get_current_user_id();
    $verse_id = isset($_POST['verseId']) ? sanitize_text_field($_POST['verseId']) : '';

    if (empty($verse_id)) {
        echo json_encode(array('success' => false, 'error' => __('Invalid verse ID.', 'iqbible')));
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
        echo json_encode(array('success' => false, 'error' => __('Error deleting verse.', 'iqbible')));
    }

    wp_die();
}
add_action('wp_ajax_iq_bible_delete_saved_verse', 'iq_bible_delete_saved_verse_ajax_handler');



// Shortcode for the Registration Form
function iqbible_registration_form()
{
    if (is_user_logged_in()) {
        return '<p>' . esc_html__('You are already logged in.', 'iqbible') . '</p>';
    }

    // Display the form
    ob_start(); ?>
    <form action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

        <?php wp_nonce_field('iqbible_registration_action', 'iqbible_registration_nonce'); ?>

        <p>
            <label for="username"><?php esc_html_e('Username', 'iqbible'); ?></label>
            <input type="text" name="username" required>
        </p>
        <p>
            <label for="email"><?php esc_html_e('Email', 'iqbible'); ?></label>
            <input type="email" name="email" required>
        </p>
        <p>
            <label for="password"><?php esc_html_e('Password', 'iqbible'); ?></label>
            <input type="password" name="password" required>
        </p>
        <p>
            <input type="submit" name="submit_registration" value="<?php esc_attr_e('Register', 'iqbible'); ?>">
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

        // ---> Verify Nonce <---
        check_admin_referer('iqbible_registration_action', 'iqbible_registration_nonce');
        // ---> End Verify Nonce <---

        $username = sanitize_user($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = $_POST['password'];

        $errors = new WP_Error();

        // Validate fields
        if (username_exists($username) || email_exists($email)) {
            $errors->add('user_exists', __('Username or email already exists', 'iqbible'));
        }
        if (empty($username) || empty($email) || empty($password)) {
            $errors->add('field_empty', __('Please fill in all required fields', 'iqbible'));
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
                echo '<p>' . esc_html__('Error creating user: ', 'iqbible') . esc_html($user_id->get_error_message()) . '</p>';
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
        return sprintf('<p>%s <a href="%s">%s</a>.</p>', esc_html__('You need to be logged in to view your profile.', 'iqbible'), esc_url(wp_login_url()), esc_html__('Log in here', 'iqbible'));
    }

    $current_user = wp_get_current_user();

    ob_start();
?>
    <h3><?php esc_html_e('Your Profile', 'iqbible'); ?></h3>
    <form method="post">
        <p>
            <label for="email"><?php esc_html_e('Email', 'iqbible'); ?></label>
            <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>
        </p>
        <p>
            <label for="first_name"><?php esc_html_e('First Name', 'iqbible'); ?></label>
            <input type="text" name="first_name" value="<?php echo esc_attr($current_user->first_name); ?>">
        </p>
        <p>
            <label for="last_name"><?php esc_html_e('Last Name', 'iqbible'); ?></label>
            <input type="text" name="last_name" value="<?php echo esc_attr($current_user->last_name); ?>">
        </p>
        <p>

            <?php wp_nonce_field('iqbible_update_profile_action', 'iqbible_profile_nonce'); ?>

            <input type="submit" name="update_profile" value="<?php esc_attr_e('Update Profile', 'iqbible'); ?>">

        </p>
    </form>
    <?php

    // Handle profile update
    if (isset($_POST['update_profile'])) {

        // Verify Nonce
        check_admin_referer('iqbible_update_profile_action', 'iqbible_profile_nonce');

        wp_update_user(array(
            'ID'         => $current_user->ID,
            'user_email' => sanitize_email($_POST['email']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name'  => sanitize_text_field($_POST['last_name']),
        ));
        echo '<p>' . esc_html__('Profile updated successfully!', 'iqbible') . '</p>';
    }

    return ob_get_clean();
}
add_shortcode('iqbible_profile', 'iqbible_profile_form');


// Shortcode for Logout Link
function iqbible_logout_link()
{
    if (is_user_logged_in()) {
        $logout_url = wp_logout_url(home_url());
        return '<a href="' . esc_url($logout_url) . '">' . esc_html__('Logout', 'iqbible') . '</a>';
    }
    return '<p>' . esc_html__('You are not logged in.', 'iqbible') . '</p>';
}
add_shortcode('iqbible_logout', 'iqbible_logout_link');


// Shortcode for Login Form
function iqbible_login_form()
{
    if (is_user_logged_in()) {
        return sprintf('<p>%s <a href="%s">%s</a>.</p>', esc_html__('You are already logged in.', 'iqbible'), esc_url(home_url()), esc_html__('Go to homepage', 'iqbible'));
    }

    ob_start();
    ?>
    <form action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" method="post">
        <p>
            <label for="username"><?php esc_html_e('Username', 'iqbible'); ?></label>
            <input type="text" name="log" required>
        </p>
        <p>
            <label for="password"><?php esc_html_e('Password', 'iqbible'); ?></label>
            <input type="password" name="pwd" required>
        </p>
        <p>
            <input type="submit" name="wp-submit" value="<?php esc_attr_e('Log In', 'iqbible'); ?>">
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
        echo '<p>' . esc_html__('Error: No book ID provided.', 'iqbible') . '</p>';
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
            echo '<h2>' . esc_html__('Introduction', 'iqbible') . '</h2>';
            echo '<p>' . esc_html($bookInfo['introduction']) . '</p>';
        }

        // Long Introduction
        if (isset($bookInfo['introduction_long'])) {
            echo '<h2>' . esc_html__('Long Introduction', 'iqbible') . '</h2>';
            echo '<p>' . esc_html($bookInfo['introduction_long']) . '</p>';
        }

        // Author
        if (isset($bookInfo['author'])) {
            echo '<h3>' . esc_html__('Author', 'iqbible') . '</h3>';
            echo '<p>' . esc_html($bookInfo['author']) . '</p>';
        }

        // Date
        if (isset($bookInfo['date'])) {
            echo '<h3>' . esc_html__('Date', 'iqbible') . '</h3>';
            echo '<p>' . esc_html($bookInfo['date']) . '</p>';
        }

        // Word Origin
        if (isset($bookInfo['word_origin'])) {
            echo '<h3>' . esc_html__('Word Origin', 'iqbible') . '</h3>';
            echo '<p>' . esc_html($bookInfo['word_origin']) . '</p>';
        }

        // Genre
        if (isset($bookInfo['genre'])) {
            echo '<h3>' . esc_html__('Genre', 'iqbible') . '</h3>';
            echo '<p>' . esc_html($bookInfo['genre']) . '</p>';
        }

        // Theological Details (if available)
        if (isset($bookInfo['theological_introduction'])) {
            echo '<h2>' . esc_html__('Theological Introduction', 'iqbible') . '</h2>';
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
        echo '<p>' . esc_html__('No introduction found for this book.', 'iqbible') . '</p>';
    }

    wp_die(); // End the AJAX request
}


/**
 * AJAX handler to update the user's language and clear the relevant book cache.
 */
function iq_bible_update_language_and_clear_cache_handler() {
    // ---> Verify Nonce <---
    check_ajax_referer('iqbible_ajax_nonce', 'security');

    if ( ! isset( $_POST['language'] ) ) {
        wp_send_json_error( [ 'message' => __( 'Language not provided.', 'iqbible' ) ] );
    }

    $new_language = sanitize_text_field( $_POST['language'] );
    $old_language = get_transient( 'iqbible_language' );

    // If the language is changing and we have an old language set, clear the old cache.
    if ( $new_language !== $old_language && ! empty( $old_language ) ) {
        $old_transient_key = 'iqbible_books_' . sanitize_key( $old_language );
        delete_transient( $old_transient_key );
    }

    // Set the new language transient.
    set_transient( 'iqbible_language', $new_language, DAY_IN_SECONDS );

    wp_send_json_success( [ 'message' => __( 'Language updated and cache cleared.', 'iqbible' ) ] );
}
add_action( 'wp_ajax_iq_bible_update_language_and_clear_cache', 'iq_bible_update_language_and_clear_cache_handler' );
add_action( 'wp_ajax_nopriv_iq_bible_update_language_and_clear_cache', 'iq_bible_update_language_and_clear_cache_handler' );



// Register the AJAX action for logged-in and guest users
add_action('wp_ajax_iq_bible_book_intro', 'iq_bible_book_intro_ajax_handler');
add_action('wp_ajax_nopriv_iq_bible_book_intro', 'iq_bible_book_intro_ajax_handler');

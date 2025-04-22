<!-- Bible Stories -->
 
<h2><?php esc_html_e('Bible Stories', 'iqbible'); ?></h2>

<div class="iqbible-bible-stories-container">

 <?php

    if (!empty($stories) && is_array($stories)) { // Use !empty() for better check
        $ct = 0; ?>

        <?php foreach ($stories as $story) {
             // Basic validation for expected keys
             if (!isset($story['verse_id']) || !isset($story['story']) || !isset($story['verses_1'])) {
                 continue; 
             }

            $ct++;
            // Use 'verse_id' instead of 'verseId'
            $verseId = sprintf('%08d', $story['verse_id']); // Ensure 8 digits

            // Validate format before extracting substrings
            if (strlen($verseId) === 8 && is_numeric($verseId)) {
                $bookId = substr($verseId, 0, 2);
                $chapterId = intval(substr($verseId, 2, 3)); // Convert chapter to integer

                // Prepare data attributes for JavaScript to use
                echo '<p><sup>' . $ct . ' </sup><a href="javascript:void(0);" class="story-link" data-book-id="' . esc_attr($bookId) . '" data-chapter-id="' . esc_attr($chapterId) . '" data-verse-id="' . esc_attr($verseId) . '">' . esc_html($story['story']) . '</a><br>';

                // Output verses in parentheses and smaller font
                echo '<span class="story-verses">(' . esc_html($story['verses_1']) . ')</span></p>'; // Added parentheses for clarity
            } else {
                 // Optionally log an error for malformed verse_id
                 error_log('IQ Bible: Malformed verse_id encountered in stories: ' . $story['verse_id']);
            }
        } ?>

    <?php } else { ?>
        <p><?php esc_html_e('No stories available.', 'iqbible'); ?></p>
    <?php } ?>
</div>
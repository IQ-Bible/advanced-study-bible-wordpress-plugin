<!-- Bible Stories -->
<h2>Bible Stories</h2>
<div class="iqbible-bible-stories-container">
    <?php 
    if ($stories && is_array($stories)) {
        $ct = 0; ?>

        <?php foreach ($stories as $story) {
            $ct++;
            // Use 'verse_id' instead of 'verseId'
            $verseId = sprintf('%08d', $story['verse_id']);

            // Extract bookId and chapterId using the correct $verseId variable
            $bookId = substr($verseId, 0, 2);
            $chapterId = substr($verseId, 2, 3);

            // Prepare data attributes for JavaScript to use
            echo '<p><sup>' . $ct . '&nbsp;</sup><a href="javascript:void(0);" class="story-link" data-book-id="' . esc_attr($bookId) . '" data-chapter-id="' . esc_attr($chapterId) . '" data-verse-id="' . esc_attr($verseId) . '">' . esc_html($story['story']) . '</a><br>';

            // Output verses in parentheses and smaller font
            echo '<span class="story-verses">' . esc_html($story['verses_1']) . '</span></p>';
        } ?>

    <?php } else { ?>
        <p>No stories available.</p>
    <?php } ?>
</div>

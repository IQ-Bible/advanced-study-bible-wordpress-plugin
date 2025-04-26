<!-- Bible -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>

<div id="iqbible-bible-content-wrapper">

    <div id="iqbible-header-container">

        <?php echo esc_html($bookName); ?>

        <h2 title="Change book or chapter" id="fetch-books-header"></h2>


        <small title="Change version" id="fetch-books-header-version"></small>

        <small title="Book Information" id="fetch-books-header-intro">&#9432;</small>

    </div>

    <!-- Audio -->
    <div class="iqbible-audio" id="iqbible-audio-player">
    </div>

    <p></p>
    <!-- Chapter Results -->

    <div id="iqbible-chapter-results"></div>
</div>
<hr>
<!-- Prev/Next -->
<div id="iqbible-prev-next">
    <span id="prev-chapter">
        < <?php esc_html_e('prev', 'iqbible'); ?> </span> |
            <span id="next-chapter"> <?php esc_html_e('next', 'iqbible'); ?> > </span>
</div>
<!-- Parables -->
<h2><?php esc_html_e( 'Parables', 'iqbible' ); ?></h2>
<?php
// Fetch parables - Consider using session language if API supports it
$session_language = $_SESSION['language'] ?? 'english';
$parables         = iq_bible_api_get_data( 'GetParables', array( 'language' => $session_language ) );

// Remove or keep this line for debugging as needed:
// print_r($parables);
?>
<div class="iqbible-parables-container">
    <?php if ( ! empty( $parables ) && is_array( $parables ) ) : ?>
        <ul class="parables-list">
            <?php foreach ( $parables as $parable_name => $verses ) : ?>
                <li class="parable-item">
                    <?php // Note: Parable names ($parable_name) come from the API. Translation depends on API support or manual mapping. ?>
                    <p><strong><?php echo esc_html( $parable_name ); ?></strong></p>
                    <?php if ( ! empty( $verses ) && is_array( $verses ) ) : ?>
                        <ul class="verse-list">
                            <?php foreach ( $verses as $verse_citation ) : ?>
                                <?php // Verse citations ($verse_citation) are typically not translated directly. Localization depends on API providing localized book names. ?>
                                <li class="verse-item"><?php echo esc_html( $verse_citation ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php esc_html_e( 'No parables found or unable to load data.', 'iqbible' ); // Added "unable to load" possibility ?></p>
    <?php endif; ?>
</div>
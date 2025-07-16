<!-- Parables -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>

<h2><?php esc_html_e( 'Parables', 'iqbible' ); ?></h2>
<?php

$session_language = get_transient( 'iqbible_language' );
if ( ! $session_language ) {
    $session_language = 'english';
}

$parables         = iq_bible_api_get_data( 'GetParables', array( 'language' => $session_language ) );

?>
<div class="iqbible-parables-container">
    <?php if ( ! empty( $parables ) && is_array( $parables ) ) : ?>
        <ul class="parables-list">
            <?php foreach ( $parables as $parable_name => $verses ) : ?>
                <li class="parable-item">

                    <p><strong><?php echo esc_html( $parable_name ); ?></strong></p>
                    <?php if ( ! empty( $verses ) && is_array( $verses ) ) : ?>
                        <ul class="iqbible-verse-list">
                            <?php foreach ( $verses as $verse_citation ) : ?>

                                <li class="iqbible-verse-item"><?php echo esc_html( $verse_citation ); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php esc_html_e( 'No parables found or unable to load data.', 'iqbible' ); ?></p>
    <?php endif; ?>
</div>
<!-- Topics -->

<?php // Prevent direct access
if (!defined('ABSPATH')) {
    exit;
} ?>
 
<h2><?php esc_html_e('Topics', 'iqbible'); ?></h2>

<div class="iqbible-topics-container">
    <?php
    // $current_language = $_SESSION['language'] ?? 'english'; 
    $current_language = get_transient( 'iqbible_language' );
if ( ! $current_language ) {
    $current_language = 'english';
}

    $topics = iq_bible_api_get_data('GetTopics'); 

    if (!empty($topics) && is_array($topics)) : 
    ?>
        <ul>
            <?php foreach ($topics as $topic) : ?>
                <?php if (is_string($topic)) : ?>
                    <li class="iqbible-topic-item" data-topic="<?php echo esc_attr($topic); ?>">
                        <?php echo esc_html(ucfirst($topic));?>
                    </li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p><?php esc_html_e('No topics could be loaded at this time.', 'iqbible'); ?></p>
    <?php endif; ?>
</div>
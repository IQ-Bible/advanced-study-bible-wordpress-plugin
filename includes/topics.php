<!-- Topics -->
<h2>Topics</h2>

<div class="iqbible-topics-container">

    <?php $topics = iq_bible_api_get_data('GetTopics'); ?>

    <ul>
  

        <?php foreach ($topics as $topic) { ?>
            <li class="iqbible-topic-item" data-topic="<?php echo esc_attr($topic); ?>">
                <?php echo ucfirst($topic); ?>
            </li>
        <?php } ?>
    </ul>
</div>


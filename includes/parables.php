<!-- Parables -->
<h2>Parables</h2>
<?php 
$parables = iq_bible_api_get_data('GetParables', array('language' => 'english'));
?>
<div class="iqbible-parables-container">
    <?php if (!empty($parables) && is_array($parables)) : ?>
        <ul class="parables-list">
            <?php foreach ($parables as $parable => $verses) : ?>
                <li class="parable-item">
                    <p><?php echo esc_html($parable); ?></p>
                    <?php if (!empty($verses) && is_array($verses)) : ?>
                        <ul class="verse-list">
                            <?php foreach ($verses as $verse) : ?>
                                <li class="verse-item"><?php echo esc_html($verse); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else : ?>
        <p>No parables found.</p>
    <?php endif; ?>
</div>
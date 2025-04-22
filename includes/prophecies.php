<!-- Prophecies -->
<h2><?php esc_html_e('Prophecies Fulfilled in Jesus', 'iqbible'); ?></h2>
<?php 
$prophecies_fulfilled_in_jesus = iq_bible_api_get_data('GetPropheciesFulfilledInJesus', array('language' => 'english'));
?>
<ol>
<div class="iqbible-prophecies-container">

    <?php if (!empty($prophecies_fulfilled_in_jesus) && is_array($prophecies_fulfilled_in_jesus)) : ?>
   
            <?php foreach ($prophecies_fulfilled_in_jesus as $prophecy => $fulfillment) : ?>
                <?php
                // Ensure we handle cases where the $prophecy string does not have both chapter and verse
                list($book, $chapter_verse) = explode(' ', $prophecy, 2);
                
                // Check if the $chapter_verse contains ':' and explode, else default to empty values
                if (strpos($chapter_verse, ':') !== false) {
                    list($chapter, $verse) = explode(':', $chapter_verse, 2);
                } else {
                    $chapter = $chapter_verse;  // Set chapter to the whole value if ':' is missing
                    $verse = '';  // Default to empty for verse
                }
                ?>

                <div class="prophecy-content">
               
                    <?php 
                    $fulfilled_in = strstr($fulfillment, 'Fulfilled in:');
                    if ($fulfilled_in !== false) :
                        $prophecy_text = trim(str_replace($fulfilled_in, '', $fulfillment));
                        $fulfilled_refs = explode(',', str_replace('Fulfilled in:', '', $fulfilled_in));
                    ?>
                        <li class="prophecy-text"><?php echo esc_html($prophecy_text); ?>
                        <p class="fulfillment-references">
                        <?php esc_html_e('Fulfilled in: ', 'iqbible'); ?> 
                            <?php foreach ($fulfilled_refs as $index => $ref) : ?>
                                <span class="fulfillment-reference"><?php echo esc_html(trim($ref)); ?></span><?php echo $index < count($fulfilled_refs) - 1 ? ', ' : ''; ?>
                            <?php endforeach; ?>
                            </p>
                            </li>
                    <?php else : ?>
                        <li class="prophecy-text"><?php echo esc_html($fulfillment); ?></li>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
                 
    <?php else : ?>
        <p><?php esc_html_e('No prophecies found.', 'iqbible'); ?></p>
    <?php endif; ?>
</div>
<ol>

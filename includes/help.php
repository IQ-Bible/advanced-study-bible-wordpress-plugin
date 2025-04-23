<!-- Help -->
 
<h2><?php esc_html_e( 'Help', 'iqbible' ); ?></h2>
<div class="iqbible-help-container">
    <p><?php esc_html_e( 'Thank you for choosing the IQBible Plugin to enhance your biblical experience. This help page is designed to guide you through the plugin’s features, ensuring you get the most out of your journey with scripture. Whether you\'re looking to explore the Bible, search for definitions, or create custom reading plans.', 'iqbible' ); ?></p>
    <section>
        <h3><?php esc_html_e( 'Bible', 'iqbible' ); ?></h3>
        <p><?php esc_html_e( 'Interact with the Bible by clicking on the', 'iqbible' ); ?> <strong><?php esc_html_e( 'Book Name', 'iqbible' ); ?></strong>, <strong><?php esc_html_e( 'Chapter Number', 'iqbible' ); ?></strong>, <?php esc_html_e( 'or', 'iqbible' ); ?> <strong><?php esc_html_e( 'Version', 'iqbible' ); ?></strong> <?php esc_html_e( 'to change them.', 'iqbible' ); ?></p>
        <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/bible.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Bible Interface Screenshot', 'iqbible' ); ?>">
        <p><?php esc_html_e( 'When selecting a version, if audio is available, a play icon will appear next to its name. Once a version with audio is selected, an audio player will be displayed beneath the Book name, chapter, and verse.', 'iqbible' ); ?></p>
        <p><?php esc_html_e( 'Hovering over a verse will show', 'iqbible' ); ?> <strong><?php esc_html_e( 'Verse Options', 'iqbible' ); ?></strong>, <?php esc_html_e( 'including:', 'iqbible' ); ?></p>
        <ul>
            <li><?php esc_html_e( 'Copy Verse', 'iqbible' ); ?></li>
            <li><?php esc_html_e( 'See Original Text (Hebrew or Greek)', 'iqbible' ); ?></li>
            <li><?php esc_html_e( 'Cross References', 'iqbible' ); ?></li>
            <li><?php esc_html_e( 'Commentary', 'iqbible' ); ?></li>
            <li><?php esc_html_e( 'Share', 'iqbible' ); ?></li>
            <li><?php esc_html_e( 'Bookmark', 'iqbible' ); ?></li>
        </ul>

    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Search', 'iqbible' ); ?></h3>
            <p><?php esc_html_e( 'Use the search functionality to find specific passages or keywords within the Bible quickly. Enter your query in the search bar to get started.', 'iqbible' ); ?></p>
            <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/search.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Search Interface Screenshot', 'iqbible' ); ?>">
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Bible Dictionary', 'iqbible' ); ?></h3>
            <p><?php esc_html_e( 'Access definitions and explanations for biblical words and terms. This feature helps deepen your understanding of Scripture.', 'iqbible' ); ?></p>
            <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/dictionary.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Dictionary Interface Screenshot', 'iqbible' ); ?>">
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Strong\'s Concordance', 'iqbible' ); ?></h3>
            <p><?php esc_html_e( 'Utilize Strong\'s Concordance to explore the original Hebrew and Greek words used in the Bible, along with their meanings and references.', 'iqbible' ); ?></p>
            <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/strongs.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Strongs Concordance Screenshot', 'iqbible' ); ?>">
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Bible Stories', 'iqbible' ); ?></h3>
            <p><?php esc_html_e( 'Explore a comprehensive collection of Bible stories. This feature provides insights into the narratives that shape biblical teachings.', 'iqbible' ); ?></p>
            <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/stories.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Bible Stories Screenshot', 'iqbible' ); ?>">
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Reading Plans', 'iqbible' ); ?></h3>
            <p><?php esc_html_e( 'Generate custom Bible reading plans based on your', 'iqbible' ); ?> <strong><?php esc_html_e( 'Start Date', 'iqbible' ); ?></strong>, <strong><?php esc_html_e( 'Duration', 'iqbible' ); ?></strong>, <?php esc_html_e( 'and', 'iqbible' ); ?> <strong><?php esc_html_e( 'Testament Selection', 'iqbible' ); ?></strong>. <?php esc_html_e( 'Once your plan is created, you can easily', 'iqbible' ); ?> <strong><?php esc_html_e( 'Download', 'iqbible' ); ?></strong> <?php esc_html_e( 'or', 'iqbible' ); ?> <strong><?php esc_html_e( 'Print', 'iqbible' ); ?></strong> <?php esc_html_e( 'it by clicking the respective buttons.', 'iqbible' ); ?></p>
            <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/plans.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Reading Plans Screenshot', 'iqbible' ); ?>">
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Saved Verses', 'iqbible' ); ?></h3>
        <p><?php esc_html_e( 'Registered and logged-in users can save their favorite verses by clicking the Bookmark icon in the Verse Options. View and manage your saved verses here.', 'iqbible' ); ?></p>
        <!-- <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/saved.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Saved Verses Screenshot', 'iqbible' ); ?>"> -->
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Notes', 'iqbible' ); ?></h3>
        <p><?php esc_html_e( 'Registered and logged-in users can create, edit, and delete personal notes related to their study. Keep your thoughts and reflections organized.', 'iqbible' ); ?></p>
        <!-- <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/notes.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Notes Interface Screenshot', 'iqbible' ); ?>"> -->
    </section>
    <hr>
     <section>
        <h3><?php esc_html_e( 'Topics', 'iqbible' ); ?></h3>
        <p><?php esc_html_e( 'Explore biblical topics and find relevant verses associated with them. Select a topic from the dropdown to see related scriptures.', 'iqbible' ); ?></p>
         <!-- <img src="<?php echo esc_url( plugins_url( '../assets/img/screenshots/topics.png', __FILE__ ) ); ?>" width="100%;" alt="<?php esc_attr_e( 'Topics Interface Screenshot', 'iqbible' ); ?>"> -->
    </section>
    <hr>
    <section>
        <h3><?php esc_html_e( 'Support & Feedback', 'iqbible' ); ?></h3>
        <p><?php esc_html_e( 'Need further assistance or have suggestions? Please visit our support forum or contact us through our website.', 'iqbible' ); ?> <a href="https://iqbible.app" target="_blank"><?php esc_html_e( 'Visit IQBible.app', 'iqbible' ); ?></a></p>
    </section>

</div>
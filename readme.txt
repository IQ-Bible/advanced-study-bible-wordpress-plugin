=== IQBible Advanced Study Bible ===
Contributors: @jodypm
Tags: bible, study bible, scripture, concordance, strongs, dictionary, reading plan, bible search, bible audio, shortcode, api, christian, faith, religion
Requires at least: 6.0 
Tested up to: 6.8
Stable tag: 1.0.0 
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: iqbible
Domain Path: /languages

Embed a comprehensive, interactive Bible study experience directly into your WordPress site. Uses the IQ Bible API for dynamic content (free tier available).

== Description ==

Embed a comprehensive, interactive Bible study experience directly into your WordPress site with the **IQBible Study Bible Plugin**. This open-source plugin leverages the powerful [IQ Bible API](https://rapidapi.com/vibrantmiami/api/iq-bible) (requires a free or paid API key from RapidAPI) to fetch and display a wide range of Bible-related data dynamically.

Offer your visitors the ability to:

*   Read multiple Bible versions (API dependent).
*   Navigate easily through Books, Chapters, and Verses.
*   View Book Introductions.
*   Explore Cross References.
*   Use Strong's Concordance (for supported versions like KJV).
*   Look up definitions with integrated dictionaries (e.g., Smith's Bible Dictionary).
*   Perform full-text searches within Bible versions.
*   Listen to audio narrations (where available).
*   Generate customizable Bible reading plans.
*   Browse a Topical Index.
*   Discover key Bible Stories.

The plugin interface is fully translatable and uses AJAX for a smooth user experience. Registered users can benefit from personalized features like saving notes and bookmarking verses (optional shortcodes provided for login/registration).

**Requires an API Key:** This plugin requires an API key from the third-party IQ Bible API service hosted on RapidAPI. Subscription plans (including a free tier) are managed on RapidAPI.

**Main Usage:** Place the shortcode `[IQBible]` on any page or post to display the full study Bible interface.

== Installation ==

1.  **Upload via WP Admin (Recommended):**
    *   Download the latest plugin `.zip` file from the WordPress Plugin Directory.
    *   Log in to your WordPress admin area.
    *   Navigate to `Plugins` > `Add New Plugin`.
    *   Click the `Upload Plugin` button.
    *   Choose the downloaded `.zip` file and click `Install Now`.
    *   Activate the plugin after installation.

2.  **Upload via FTP/SFTP:**
    *   Download the latest plugin `.zip` file and unzip it.
    *   Upload the entire `iqbible-study-bible` (or the correct plugin slug folder name) folder to the `/wp-content/plugins/` directory on your server.
    *   Log in to your WordPress admin area.
    *   Navigate to `Plugins` > `Installed Plugins`.
    *   Activate the 'IQBible - Study Bible' plugin.

3.  **Configuration (Required):**
    *   **Get API Key:** You need an API key from the IQ Bible API provider on RapidAPI.
        *   Navigate to the [IQ Bible API page on RapidAPI](https://rapidapi.com/vibrantmiami/api/iq-bible).
        *   Subscribe to a suitable plan (free tier available).
        *   Find your API key (`X-RapidAPI-Key`) in your RapidAPI dashboard.
    *   **Enter API Key in WordPress:**
        *   In your WordPress admin area, navigate to `Settings` > `IQBible`.
        *   Paste your RapidAPI Key into the 'RapidAPI Key' field.
        *   Configure other settings like caching and default language if needed.
        *   Save the settings. The plugin will not function without a valid API key.

== Frequently Asked Questions ==

= What do I need to use this plugin? =

You need a WordPress website and an API Key from the IQ Bible API service on RapidAPI. The plugin relies on this external service to fetch Bible data.

= Where do I get an API key? =

You can get an API key by subscribing to the [IQ Bible API on RapidAPI](https://rapidapi.com/vibrantmiami/api/iq-bible). There is a free tier available.

= How do I display the Bible interface? =

Simply place the shortcode `[IQBible]` onto any WordPress page or post where you want the study Bible to appear.

= Does this work with any theme? =

The plugin is designed to work with most standard WordPress themes. However, depending on your theme's specific styling, minor CSS adjustments might occasionally be needed for optimal appearance.

= Are user notes and bookmarks stored locally? =

Yes, if a user is logged into your WordPress site, their notes and saved verses are stored within your WordPress database (associated with their user ID). The optional shortcodes `[iqbible_login]`, `[iqbible_registration]`, `[iqbible_profile]`, and `[iqbible_logout]` can help manage user accounts if your theme doesn't provide these features.

= Can I translate the plugin interface? =

Yes, the plugin is translation-ready. The text domain is `iqbible` and the `.pot` file will be located in the `/languages` folder within the plugin directory. You can use standard translation tools like Poedit or translation plugins.

== Screenshots ==

screenshot-1.png
screenshot-2.png
screenshot-3.png
screenshot-4.png

== Changelog ==
= 1.0.0 8
*   Initial release.
*   Features include Bible reading (multiple versions via API), book intros, cross-references, Strong's concordance, dictionary lookup, search, audio narration, reading plans, topics index, Bible stories, user notes & bookmarks (logged-in users), and API configuration settings.

== Upgrade Notice ==

= 1.0.0 =
*   Initial release version.
# Changelog
IQBible - Advanced Study Bible
All notable changes to this project will be documented in this file.
The shortcode for this plugin is [iqbible_advanced].

## Unreleased
- Parables
- Prophecies

## [1.0.0-beta-20] - 2025-08-09
### Fixed
- Saved verses not showing on live site implementations and adding correct version to main file

## [1.0.0-beta-19] - 2025-08-09
### Fixed
- Saved verses not showing on live site implementations

## [1.0.0-beta-18] - 2025-08-07
### Fixed
- Incorrect version to correct 1.0.0-beta-18 in main php file (iqbible-api.php)

## [1.0.0-beta-17] - 2025-08-07
### Added
- Helper text for Strong's tab (explaining about Strong's numbers)
### Changed
- Updated logo for admin iqbible settings
- Updated styling for verse options buttons and tab buttons
### Fixed
- README incorrect instructions #12

## [1.0.0-beta-16] - 2025-08-07
### Changed
- Updated PUC snippet in main php file

## [1.0.0-beta-15] - 2025-08-07
### Changed
- Updated README style

## [1.0.0-beta-14] - 2025-08-07
### Changed
- Incorrect README url to https://github.com/IQ-Bible/advanced-study-bible-wordpress-plugin/issues

## [1.0.0-beta-13] - 2025-08-07
### Changed
- Updated version in README

## [1.0.0-beta-12] - 2025-08-07
### Fixed
- Incorrect URL for RapidAPI page in README.md

## [1.0.0-beta-11] - 2025-08-07
### Changed
- Updated PUC

## [1.0.0-beta-10.2] - 2025-08-07
### Fixed
- Empty PLUGIN_DIR in release.yml
- PLUGIN_DIR wrng path in release.yml
- Fixed wrong path for plugin dir in zip creation for release

## [1.0.0-beta-9] - 2025-08-07
### Fixed
- Missing contents for create-release-zip.sh

## [1.0.0-beta-8] - 2025-08-07
### Fixed
- Org settings to allow read/write permissions for workflows

## [1.0.0-beta-7] - 2025-08-07
### Fixed
- Permissions issue with shell script for create-release-zip.sh

## [1.0.0-beta-6] - 2025-08-07
### Fixed
- Shell script to zip release (single-nest) and GH Action to trigger on release

## [1.0.0-beta-5] - 2025-08-06
### Fixed
- incorrect workflows dir name

## [1.0.0-beta-4] - 2025-08-06
### Added
- New yml file to create separate zip file for release

## [1.0.0-beta-2] - 2025-08-06
### Removed
- GitHub Actions workflow (nesting issues)

## [1.0.0-beta-1] - 2025-08-06
### Changed
- Moved repo to IQ Bible org.
- First beta version release preparation
- PUC (Plugin Update Checker) build URL
### Changed
- Workflow release.yml
### Added
- Added GitHub Actions workflow to package the plugin ZIP with the proper folder structure required by WordPress (plugin files now correctly nested inside a root plugin folder in the ZIP), ensuring seamless plugin installation and automatic updates.


## [1.0.0-alpha-16] - 2025-08-06
### Changed
- Updated .pot - (languages/iqbible.pot)

## [1.0.0-alpha-15] - 2025-08-05
### Added
- Cache checkbox in settings set to checked by default
- PUC (Plugin Update Checker) library

## [1.0.0-alpha-14] - 2025-07-17
### Fixed
- Search results click to correct verse not working

## [1.0.0-alpha-13] - 2025-07-16
### Added
- Plugin version from CHANGELOG functionality to admin-settings.php UI
### Changed
- Separate PUBLIC and PRIVATE repos and folders in order to releaase PUBLIC (w/o PRIVATE's .git) to GitHub (OpenSource)
### Fixed
- CSS Styling to accomodate more themes (prevent icons and text breaking on themes like Twenty Twenty, e.g.)
- Prefixing error with search links fix
### Removed
- Import themes in CSS - now, pkugin inherits styles like font-family, etc.

## [1.0.0-alpha-12] - 2025-07-14 to 2025-07-15
### Removed
- Lingering iqbible_start_session() in iqbible-api.php
### Fixed
- Copy paste error in functions.php within the iq_bible_books_ajax_handler (OT assigned to both NT and OT)
- iq_bible_chapter_ajax_handler and iq_bible_get_book_name make separate API calls just to get a book's name. The plugin already fetches and caches the entire list of books for the current language.
- Fixed/removed duplicate 'IQBible' shortcode in iqbible-api.php
### Changed
- GetOriginalText now includes the joined glossary from the API update (q.v.), so we are now efficient and no longer have to loop through each word.
- All text instances of 'Study Bible' to 'Advanced Study Bible'
- All instances of shortcode ['IQBible'] to ['iqbible_advanced']
- Hebrew chars remain RTL but line itself left-aligned
### Added
- Close dialog when clicking outside content area

## [1.0.0-alpha-11] - 2025-07-13
### Added
- Final checklist reviews for WordPress Plugin Compliance. Almost ready to submit...

## [1.0.0-alpha-10] - 2025-07-13
### Changed
- Successfully converted all $_SESSION vars to transients!
### Removed
- Lingering Session functions
### Fixed
- Issue with wrong language book names when switching version (langs)

## [1.0.0-alpha-9] - 2025-05-05
### Changed
- All $_SESSION uses to WP Transients (WordPress does not use PHP Sessions) for plugin marketplace submission

## [1.0.0-alpha-8] - 2025-04-27
### Added
- uninstall.php
### Changed
- reloadChapterContent to loadChapterContent for clarity as it is not always 'reloading' (e.g., initial page load)
- - credits.txt (is properly in readme.txt for WP Mktplc)
### Fixed
- Fixed clearbooksession() in version update to return a promise before executing loadChapterContent (to avoid race condition)
- Proper $wpdb->prepare() in cache clearing forms (2)

## [1.0.0-alpha-7] - 2025-04-26
### Added
- Verified NONCE validations
- Verified Sanitization
- Added NONCE validation to iqbible_registration_form() and iqbible_register_user()
- Feather icons license to readme.txt and under /licenses
- Free Bible Icons permission to readme.txt
- Additional (complete) escaping to dynamic PHP and JS output
- POT file generation (/languages/iqbible.pot) via WP CLI
- Mapped all version book names to use english book name for the bible book icons (iconNameBase)
### Fixed
- Share verse URL not scrolling to verseId-xxxxxxxx
### Removed/Changed
- Footer (added dialogs to shortcodes) as data is not suited for the WP site owner's visitors. Instead, display version data in admin-settings.php
- Renamed generic start_session to iqbible_start_session to avoid potential conflicts
- enqueue_dashicons to iqbible_enqueue_dashicons

## [1.0.0-alpha-6] - 2025-04-24 - 2025-04-25
### Added
- Updated media queries
- README.txt for WordPress Plugin submission

## [1.0.0-alpha-5] - 2025-04-23
### Added
- Bible Reading Plan planName as default filename for saving reading plans as PDF via window.print() by toggling the docuement.title
- Added remaining i18n standards (internationlization) to outputs in scripts.js
- Bible book icons back to book headings

## [1.0.0-alpha-4] - 2025-04-22
### Added
- Added internationlization functions (output) to functions.php in preparation for .pot file
- Completed internationlization for other files as well
### Changed
- Deprecated PDF generation with just straightforward print HTML for Bible Reading Plans until 'save plan' features are implemented

## [1.0.0-alpha-3] - 2025-04-21
### Added
- Disabled state to #prev-chapter when currentChapterId <= 1

## [1.0.0-alpha-2] - 2025-04-19
### Added
- Refactoring to Standard WordPress Internationalization (I18N)...
- SECURITY: Added sanitization callbacks (`sanitize_text_field`, `absint`, `esc_url_raw`) to `register_setting` calls for plugin options (`iq_bible_api_key`, `iq_bible_api_cache`, `iq_bible_custom_login_url`) to ensure safe data handling.
- SECURITY: Added Nonce verification (`wp_nonce_field`, `check_admin_referer`) to the manual cache clearing action for enhanced security.
- Nonce Security to Profile Updates
- WP CLI to machine and to path
- Added message dialog box instead of alerts()
### Fixed
- Fixed API paramater handling in GET requests: Modified the iq_bible_api_get_data function to use add_query_arg($params, $url) to correctly append parameters to the URL for GET requests, instead of incorrectly placing them in the 'body'.
### Removed
- kill_session() (dangerous and redundant for cache clearing)

## [1.0.0-alpha-1] - 2025-04-19
### Added
- SECURITY: Implemented AJAX Nonce Security (Crucial):
  - Modified `enqueue.php` to generate (`wp_create_nonce`) and pass nonce via `wp_localize_script`.
  - Modified `scripts.js` to send the nonce parameter (`security`) with all relevant AJAX requests.
  - Modified `functions.php` to add nonce verification (`check_ajax_referer`) to all relevant AJAX handler functions.

## [0.14.1-alpha] - 2024-11-09
### Fixed
- Edit API Key button not working in admin > IQBible - Advanced Study Bible settings.

## [0.14.0-alpha] - 2024-11-08
### Changed
- The name of the plugin to IQBible - Study Bible

## [0.13.0-alpha] - 2024-10-28
### Added
- `getURLParameter` utility function for retrieving URL parameters by name.
- Integrated `reloadChapterContent` with dynamic `bookId` and `chapterId` URL parameters.
- Reading plan links now load chapters directly using specified `bookId` and `chapterId` data attributes, maintaining navigation consistency.

### Changed
- `prev-chapter` and `next-chapter` click handlers now read `bookId` and `chapterId` directly from URL parameters to ensure correct navigation.
- Updated `prev-chapter` and `next-chapter` to validate chapter ID conditionally, avoiding outdated values on reload.

## [0.12.0-alpha] - 2024-10-27
### Added
- Custom Login URL setting in admin
- Topic links to AJAX
- Reading Plan links to AJAX
- Cross refs to AJAX

## [0.11.0-alpha] - 2024-10-25
### Added
- Books in session var
- Saved verses in session var
- Share verse (copy URL to clipboard)
### Changed
- Changed the format display of saved verses in profile content

## [0.10.0-alpha] - 2024-10-24
### Added
- Save verses
- My Saved Verses in profile
- Saved verse icon appendage to verse text
- Verse options box and buttons: Copy, Original Text, etc.
- Commentary dialog

## [0.9.0-alpha] - 2024-10-23
### Added
- Copy verse
- Search links to reloadChapterContent with temporary css highlight
- Search result click scroll into view
- Story result click scroll into view

## [0.8.0-alpha] - 2024-10-22
### Added
- Story links to reloadChapterContent
- Param check in URL

## [0.7.0-alpha] - 2024-10-21
### Added
- Notes (CRUD)
- Save/edit note confirmations

## [0.6.0-alpha] - 2024-10-20
### Added
- Audio player to AJAX upgrade

## [0.5.0-alpha] - 2024-10-19
### Added
- Version dialog functionality and audio available indications for supported versions
### Changed
- Versioning sequence (see 'Removed' below) back to alpha
### Removed
- Old changelog data from VF plugin to start new versioning

## [0.4.0-alpha] - 2024-10-17 - 2024-10-18
### Added
- Encrypted API key in admin settings
- Cache clear (transients) for the plugin cleared on API key change in IQBible admin settings
### Changed
- Chapter dropdowns (rows of 5) instead of separate dialog for AJAX redo

## [0.3.0-alpha] - 2024-09-26
### Added
- Setting up chapters, etc. for AJAX

## [0.2.0-alpha] - 2024-09-24 - 2024-09-25
### Added
- Changed UI for tab buttons
- Added Topics, Parables, Prophecies, Extra Biblical, and My notes to tabbed content and buttons
- Added Cross refs, share verse, save, commentary and note to verse options on hover

## [0.1.0-alpha] - 2024-09-23
### Added
- Start of project: Duplicated VF plugin to start anew with an Open Source project: IQBible Open Source WordPress Plugin 
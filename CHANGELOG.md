# Changelog
IQBible - Study Bible
All notable changes to this project will be documented in this file.
The shortcode for this plugin is [IQBible].

## Unreleased
- N/A


## [1.0.0-alpha-2] - 2025-04-19
### Added
- SECURITY: Added sanitization callbacks (`sanitize_text_field`, `absint`, `esc_url_raw`) to `register_setting` calls for plugin options (`iq_bible_api_key`, `iq_bible_api_cache`, `iq_bible_custom_login_url`) to ensure safe data handling.
- SECURITY: Added Nonce verification (`wp_nonce_field`, `check_admin_referer`) to the manual cache clearing action for enhanced security.
### Fixed
- Fixed API paramater handling in GET requests: Modified the iq_bible_api_get_data function to use add_query_arg($params, $url) to correctly append parameters to the URL for GET requests, instead of incorrectly placing them in the 'body'.

## [1.0.0-alpha-1] - 2025-04-19
### Added
- SECURITY: Implemented AJAX Nonce Security (Crucial):
  - Modified `enqueue.php` to generate (`wp_create_nonce`) and pass nonce via `wp_localize_script`.
  - Modified `scripts.js` to send the nonce parameter (`security`) with all relevant AJAX requests.
  - Modified `functions.php` to add nonce verification (`check_ajax_referer`) to all relevant AJAX handler functions.

## [0.14.1-alpha] - 2024-11-09
### Fixed
- Edit API Key button not working in admin > IQBible - Study Bible settings.

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
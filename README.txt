=== Instagram Feed Pro ===
Contributors: smashballoon
Support Website: https://smashballoon/instagram-feed/
Requires at least: 3.4
Tested up to: 5.4
Stable tag: 5.6.2
Version: 5.6.2
License: Non-distributable, Not for resale

Display beautifully clean, customizable, and responsive feeds from multiple Instagram accounts

== Description ==

Display Instagram photos from any non-private Instagram accounts, either in the same single feed or in multiple different ones.

= Features =
* Super **simple to set up**
* Completely **responsive** and mobile ready - layout looks great on any screen size and in any container width
* **Completely customizable** - Customize the width, height, number of photos, number of columns, image size, background color, image spacing, text styling, likes & comments and more!
* Display **multiple Instagram feeds** on the same page or on different pages throughout your site
* Use the built-in **shortcode options** to completely customize each of your Instagram feeds
* Display thumbnail, medium or **full-size photos** from your Instagram feed
* **Infinitely load more** of your Instagram photos with the 'Load More' button
* View photos in a pop-up **lightbox**
* Display photos by User ID or hashtag
* Display photo captions, likes and comments
* Use your own Custom CSS or JavaScript

= Benefits =
* Increase your Instagram followers by displaying your Instagram content on your website
* Save time and increase efficiency by only posting your photos to Instagram and automatically displaying them on your website

== Installation ==

1. Install the Instagram plugin either via the WordPress plugin directory, or by uploading the files to your web server (in the `/wp-content/plugins/` directory).
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to the 'Instagram Feed' settings page to configure your Instagram feed.
4. Use the shortcode `[instagram-feed]` in your page, post or widget to display your photos.
5. You can display multiple Instagram feeds by using shortcode options, for example: `[instagram-feed id=YOUR_USER_ID_HERE cols=3 width=50 widthunit=%]`

== Changelog ==
= 5.6.2 =
* Fix: Accounts can be connected without the use of JavaScript.
* Fix: Default URL for connecting an account changed to prevent "Invalid Scope" connection issue.

= 5.6.1 =
* Fix: Workaround added for PHP warning related to an undefined media_url index.
* Fix: Connecting a business account on a mobile device when more than 2 pages where returned was not possible.
* Fix: After connecting an account, the warning that there were no connected accounts would still be visible.
* Fix: URL for retrieving image files from Instagram using a redirect method was changed to prevent an extra, unnecessary redirect.

= 5.6 =
* New: Added setting to sort posts by number of likes. Posts from connected business accounts can be sorted by the number of likes. Up to the latest 200 posts from the feed can be included.
* New: Added setting to offset the first post in the feed. Add offset=(number of posts) to your shortcode to skip posts before starting the feed.
* New: Date the post was published can now be displayed in hashtag feeds (only for posts collected after this update).
* New: To help us improve the plugin we have added usage tracking so that we can understand what features and settings are being used, and which features matter to you the most. The plugin will send a report in the background once per week with your plugin settings and basic information about your website environment. No personal or sensitive data is collected (such as email addresses, Instagram account information, license keys, etc). You can opt-out by simply disabling the setting at: Instagram Feed > Customize > Advanced > Misc > Enable Usage Tracking. See [here](https://smashballoon.com/instagram-feed/usage-tracking/) for more information.
* New: Some older posts can be included in the feed when first creating a recent hashtag feed. Previously only posts made within the last 24 hours would be included.
* Tweak: Added PHP filter sbi_license_page_output for changing what displays on the License tab. You can use [this guide](https://smashballoon.com/how-can-i-hide-my-license-key-on-the-license-page/) for an example on how you can hide your license key.
* Tweak: "@mentions" were not being detected using the includewords and excludewords filters.
* Tweak: Added additional checks to make sure the HTTP protocol matches when using resized image URLs from the uploads folder.
* Tweak: If a post is on both a white list and the block list, it will be included in the related white list feed. Previously it was blocked.
* Tweak: More information is given when there is an account connection error when connecting an account on the "Configure" page.
* Tweak: Connecting a business account will permanently remove any accounts from the same user that are from the legacy Instagram API that is expiring in June.
* Fix: Added a workaround for sanitize_textarea_field for users using an older version of WordPress.
* Fix: When the lightbox was disabled, clicking the "play" icon for video posts would do nothing.
* Fix: Fixed HTML error causing the manually connect an account feature to not work.
* Fix: Access token and account ID are validated and formatted before trying to manually connect an account to prevent errors.

= 5.5.1 =
* Fix: API Error #2 was not clearing properly in error reports.
* Fix: PHP warning "undefined variable account_found".
* Tweak: User feeds that do not have a user name or ID assigned to them will automatically use the first connected account for the feed.
* Tweak: rel="nofollow" added to all external Instagram Feed links found in the source of the page.
* Tweak: For carousel feeds, if the number of photos is less than or equal to the number of columns in the feed the number of photos in the carousel is doubled.

= 5.5 =
* New: Email alerts for critical issues. If there's an issue with an Instagram feed on your website which hasn't been resolved yet then you'll receive an email notification to let you know. This is sent once per week until the issue is resolved. These emails can be disabled by using the following setting: Instagram Feed > Customize > Advanced > Misc > Feed Issue Email Report.
* New: Admin notifications for critical issues. If there is an error with the feed, admins will see notices in the dashboard and on the front-end of the site along with instructions on how to resolve the issue. Front-end admin notifications can be disabled by using the following setting: Instagram Feed > Customize > Advanced > Misc > Disable Admin Error Notice.
* New: Added a WordPress 'Site Health' integration. If there is a critical error with your feeds, it will now be flagged in the site health page.
* New: Added "About Us" page for those who would like to learn more about Smash Balloon and our other products. Go to Instagram Feed -> About Us in the dashboard.
* Fix: Pinterest "share" button found in the lightbox was not working correctly.

= 5.4.1 =
* Fix: Added workaround for personal account connection error and header display issue due to an Instagram API bug. After updating, click "Save Changes" on the Instagram Feed settings page, "Configure" tab to clear your cache.

= 5.4 =
* New: Added an “Instagram Feed” Gutenberg block to use in the block editor, allowing you to easily add a feed to posts and pages.
* Tweak: Custom JavaScript from the "Custom JavaScript" setting no longer added to the page after the document ready event but instead added on page load.
* Fix: Mobile swipe code will not cause an error if overwritten by another jQuery plugin/code snippet
* Fix: Several improvements and fixes for loading the feed without the JavaScript based image transitions.

= 5.3.3 =
* Tested with upcoming WordPress 5.4 update.
* Tweak: Header and follow button will still be displayed when number of posts is set to 0.
* Fix: Posts in "tagged" feeds would sometimes appear out of order.

= 5.3.2 =
* Fix: Error caused when clicking on an image using touch devices and having the lightbox disabled for a feed.
* Fix: Shoppable feeds feature was missing some URLs in captions due to shortened image alt text

= 5.3.1 =
* Important: March 2 deadline for migrating to the new Instagram API pushed back to March 31.
* Fix: Error saving updated account information caused by emoji in account bio or in account names and MySQL tables that didn't have a UTF8mb4 character set.
* Fix: Touch devices will open the lightbox on first tap.
* Fix: Twitter "Share" button link was incorrect.
* Fix: Some links to Instagram were missing a backslash at the end of the URL causing a 301 redirect.

= 5.3 =
* Important: On March 31, Instagram will stop supporting its old API which will disrupt feeds created from personal connected accounts. If you are using a personal account, you will need to reconnect the account on the Instagram Feed Settings page. Please [see here](https://smashballoon.com/instagram-api-changes-march-2-2020/) for more information.
* New: Support added for the new Instagram Basic Display API.
* New: Added PHP hooks 'sbi_before_feed' and 'sbi_after_feed' for displaying HTML before and after the main Instagram feed HTML.
* New: Added settings for adding a custom header avatar and custom header bio text. Go to the "Customize" tab "Header" area to set these or use customavatar="AVATAR URL" or custombio="BIO TEXT" in the shortcode.
* Tweak: Warnings and messages displaying on the front end of sites now display at the top of the feed.
* Tweak: Header templates changed to accommodate missing data if connected as a personal account to the new API.
* Tweak: Changes to feed.php, header.php, header-boxed.php, and item.php templates.
* Tweak: Added CSS to prevent some themes from adding box shadows and bottom border when hovering over the header.
* Tweak: Added code to clear page caching from Litespeed cache when clearing page caches with the plugin.
* Fix: Emoji in the first few characters of a caption would cause the main post image to switch to an emoji when loading more.
* Fix: Pagination for "tagged" feeds not working for certain accounts.

= 5.2.6 =
* Tweak: Changed screen reader and alt text to be more SEO friendly (change made to item.php template).
* Tweak: Added PHP hooks to use custom alt and screen reader text.
* Fix: Screen reader text would be visible if text was right aligned.
* Fix: Feeds were not updating when background caching used.
* Fix: Incorrect image resolution would be used when setting the image resolution to something other than auto.

= 5.2.5 =
* New: Added a close button for the share feature in the lightbox.
* New: Added aria-label attributes to SVGs for improved accessibility.
* Tweak: Removed Google Plus as a share option.
* Tweak: Error reporting improved. Information about errors encountered while retrieving posts now more concise.
* Fix: Improved reliability of touch detection for opening the lightbox with touch devices.
* Fix: Minified version of CSS file was not actually minified.

= 5.2.4 =
* Tweak: Check added to make sure sbiOwlCarousel was defined before using to show carousel posts in the lightbox added.
* Tweak: Added a default set of options of sb_instagram_js_options not defined on the page.
* Tweak: Added a text link in the settings page footer to our new free [YouTube feed](https://wordpress.org/plugins/feeds-for-youtube/) plugin
* Fix: Part of the next image in the carousel would display when opening carousel posts in the lightbox on slower networks.
* Fix: Added a workaround for error caused by videos in stories displayed in the lightbox not having thumbnails available.

= 5.2.3 =
* New: Added filter "sbi_settings_pages_capability" to change what permission is needed to access settings pages.
* Tweak: Better error messages for no posts being found and API request delays.
* Tweak: If "Favor Local Images" setting is in use, a 640px resolution image will be created for images coming from a personal account.
* Tweak: Better error recovery when image file not found when viewing the feed.
* Tweak: Added "noreferrer" to links found in the captions in the lightbox display.
* Tweak: Button and input field styling updated to look better with WordPress 5.3.
* Tweak: Updated language files for version 2.0+.
* Fix: Accounts that were connected prior to version 4.0 would not show the follow button if the header was not also displayed and would not retrieve comments to display in the lightbox. Visit the "Configure" tab to have the account automatically updated.
* Fix: Fixed incorrect URL for boxed-style header "follow" button.
* Fix: Clearing "white lists" would not work.
* Fix: Duplicate posts added to recent hashtag feeds when background caching enabled.
* Fix: MySQL error when retrieving resized images. Thanks [the-louie](https://github.com/the-louie)!

= 5.2.2 =
* New: Added setting "API request size" on the "Customize" tab to allow requesting of more posts than are in the feed. Setting this to a high number will prevent no posts being found if you often post IG TV posts and use a personal account.
* Tweak: Removed width and height attributes from the image element in the feed to prevent notices about serving scaled images in optimization tools.

= 5.2.1 =
* Tweak: Resized images can be used in the page source code when "Disable JS Image Loading" setting is enabled.
* Fix: Full captions would always show when using the "Disable JS Image Loading" setting.
* Fix: Using the masonry layout and the "Disable JS Image Loading" setting would cause a duplicate image to display on top of the post image.
* Fix: Using a single column for mobile devices and the "Highlight" layout would cause images to display larger than the feed width.
* Fix: Map marker would display as a font icon instead of an SVG.
* Fix: Raw image file opening instead of lightbox for some sites after updating to version 5.2.

= 5.2 =
* New: New feed type "Tagged". Show posts that your account has been "tagged" in on Instagram. Must have a connected business account to use this feed type.
* New: Added the ability to overwrite default templates in your theme. View [this article](https://smashballoon.com/guide-to-creating-custom-templates/) for more information.
* New: Added several PHP hooks for modifying feeds settings and functionality.
* Tweak: Retrieving posts from recent hashtag feeds available only in cache made more lenient to accommodate minor changes in the feed settings.
* Fix: Using the "Load Initial Posts with AJAX" setting would cause images to not resize with the browser window.
* Fix: Added back language files for translations.
* Fix: Changing the image resolution setting would not change the image size.
* Fix: MySQL error when retrieving resized images.
* Fix: Follow button would not show if there was no connected account.
* Fix: Deleting any connected account will delete any connected accounts that have errors in the data that was saved for them.
* Fix: Masonry style layout would not adjust when caption was expanded.
* Fix: Moderation mode would open multiple feeds when used on a page that had multiple feeds.
* Fix: Adding a comma at the end of a list of words used in filters would cause the filter to not work properly.

= 5.1.2 =
* Tweak: Background caching randomizes which feeds are updated first to help with large amounts of cached feeds.
* Tweak: A delay will be triggered for future API requests when the HTTP request fails or Instgram API returns errors.
* Tweak: Invalid access token notices will automatically clear if a successful API request is made with one.
* Tweak: Code bypass added if Image Liquid or visibility change detection code is not defined due to multiple jQuery versions added to the page.
* Fix: Hashtag feeds with multiple hashtags not alternating in certain circumstances.
* Fix: PHP warning for using a static method improperly.
* Fix: Disabling Font Awesome would leave the CSS file for it enqueued.
* Fix: Minified version of JavaScript file is now used when the setting labeled "Are you using an ajax theme?" is enabled.
* Fix: Cache time when using page cache method was incorrect.
* Fix: Images in moderation mode would not be cropped squares.

= 5.1.1 =
* Fix: Background caching would not work for feeds with no shortcode arguments.
* Fix: A second connected business account would not always be used if the first account had reached the 30 hashtag per week limit.
* Tweak: Cache size capped at 200 to prevent PHP memory issues.
* Tweak: Widget name changed back to Instagram Feed.

= 5.1 =
* New: Posts can be loaded initially with an AJAX call. Can help keep feed updated when page caching also used. Enable on the "Customize" tab, "Advanced" sub-tab.
* Tweak: Using both includewords and excludewords in a feed will cause posts that include the includeword and do not include the excludeword to be included.
* Tweak: Caption length limit also applies to hover caption.
* Tweak: Non Instagram post items can be added to the feed without causing a JavaScript error.
* Tweak: Duplicate posts filtered out before displaying.
* Tweak: Bottom margin added to header when image padding is less than 10 pixels.
* Fix: Javascript error caused by feed being hidden initially and then becoming visible.
* Fix: Settings link not appearing on Plugins page where Instagram Feed Pro was listed.
* Fix: Invalid HTML caused by no space between attributes in the main sbi div.
* Fix: Use "http" for svg xlmns attribute.
* Fix: Lazy-loading plugins would sometimes cause the feed to load blank placeholder images.
* Fix: "Disable JS Image Loading" setting would break the lightbox.
* Fix: Incorrect image resolution or duplicate image files loaded on the page in FireFox.
* Fix: Filtering feeds by hashtag can use first author comment when also using a personal Instagram account.
* Fix: Line breaks not always showing in captions.
* Fix: Improved includewords and excludewords word/hashtag detection.
* Fix: Spaces between multiple includewords/excludewords would cause word/hashtag detection problems.
* Fix: More escaping when outputting html.
* Fix: PHP function "wp_json_encode" used instead of "json_encode" where applicable.
* Fix: Recent hashtag feeds would sometimes retrieve the same post multiple times from the database cache.

= 5.0.4 =
* Fix: Fixed error from Instagram when connecting a personal account.

= 5.0.3 =
* Fix: Includewords and Excludewords settings were missing some matching words/hashtags in captions.
* Fix: "Favor local images" setting was sometimes causing the feed to not use the best image resolution available.

= 5.0.2 =
* Fix: Plugin not showing that an update is available on the Plugins page.
* Fix: Hahstags mixing latin and non-latin characters were not always working with includewords setting.
* Fix: Padding around images in FireFox not working for carousel feeds.
* Fix: Added back support for "carousel=true" in shortcodes to make carousel feeds.
* Fix: Using "showheader=false" would still add an empty header to the feed.
* Fix: Removed line breaks from html template to prevent odd issues in certain themes.
* Fix: Carousel arrows would not render as SVGs even when the font style was set to SVG.
* Fix: Mixed feeds would sometimes stop paginating when one of the users or hashtag endpoints ran out of new posts.

= 5.0.1 =
* Fix: Likes counts not working for business feeds.
* Fix: Video thumbnails from non-resized images were causing related video not to open and play in lightbox.
* Fix: Medium resolution image being used in the lightbox for personal accounts when image resizing is enabled and no full size image was created.
* Fix: Removed error message on front-end when custom tables for resized image data are not existing.
* Fix: Location data not displaying on hover when enabled and available.
* Fix: Enqueue CSS file in shortcode setting not working properly.

= 5.0 =
* **MAJOR UDPATE**
* New: We've rebuilt the plugin from the ground up with a focus on performance and reliability. Your feeds are now loaded from the server using PHP removing the reliance on AJAX.
* New: Local image storing expanded to include personal accounts. Use the "Favor Local Images" setting on the "Customize" tab, "Advanced" sub-tab to have the plugin use local images whenever available, thus removing reliance on the Instagram CDN.
* New: You can now set the plugin to check for new Instagram posts in the background rather than when the page loads by using the new "Background caching" option which utilizes the WordPress "cron" feature. Enable this using the "Check for new posts" setting on the "Configure" tab.

= 4.1.5 =
* Tweak: Store URL switched to https to prevent update error.
* Tweak: Code in SB_Instagram_Connected_Accounts class removed that was causing problems for some users. Site would crash intermittently with warning about php.ini settings.
* Fix: PHP warning when trying to count boolean related to an empty comment cache.
* Fix: While viewing a video in the lightbox, the lightbox would automatically go to the next slide when it finished.

= 4.1.4 =
* Fix: Welcome page updated to work with CSS changes in WordPress 5.2.
* Fix: Using "Shoppable Feeds" feature would cause images to disappear when the resolution of the images were raised.
* Fix: Recent hashtag feeds using multiple hashtags where some hashtags did not have many posts would show an incorrect number of images in the feed.

= 4.1.3 =
* Fix: Added check for WordPress version 3.4 to disable image resizing features.
* Fix: Recent hashtag feeds would display posts out of order if they shared saved posts from user feeds.
* Fix: Arbitrary API call was being made for user feeds with headers.

= 4.1.2 =
* Fix: Fixed an issue caused by a bug in the Instagram API which was preventing some Instagram accounts from being able to be connected. If you experienced an issue connecting an Instagram account using the "Personal" option then please try again after updating.

= 4.1.1 =
* Tweak: Mediavine integration JavaScript added to a separate file instead of added to the source to allow for optimization plugins to work with the code.
* Fix: Header would be included in the feed the first time the feed loads for business feeds even if disabled.

= 4.1 =
* New: Instagram stories are now supported. If a story is available for a business user account, clicking the avatar in the user feed header will display the story. Disable this on the Customize tab, header options.
* New: If more than one unique business account is in use (is connected to a different Facebook page), more than 30 unique hashtags can be included in feeds during a week.
* New: Headers can be displayed outside of the scrollable area for feeds with a specific height set. Enable this on the Customize tab, header options.
* New: Added capability "manage_instagram_feed_options" to allow non-admins to manage Instagram Feed settings pages.
* New: Added a setting for multi-site super admins to hide the license tab from site admins. Enable this on the Customize tab, Advanced sub-tab.
* New: Added a setting to enable integration with Mediavine ad networks. If you are using Mediavine, enable this on the Customize tab, Advanced sub-tab.
* New: Added "feedid" setting to explicitly set a unique key for feed caching. When used this will help prevent cache conflicts when using similar includewords for multiple feeds are used.
* Tweak: Scaled images of 640 pixels are saved on the site's server for better optimization.
* Tweak: Error handling for broken image links added.
* Tweak: SVGs given attribute role="presentation" instead of role="img" for better accessibility.
* Tweak: sbi_photo elements given and aria label and the attribute role="img" for improved accessibility.
* Tweak: License renewal notice now only visible for admin users.
* Tweak: Setting to disable the "Welcome" page redirect available. Enable this using the link on the "Welcome" page.
* Tweak: Video element hidden when transitioning slides when viewing posts in the lightbox.
* Fix: Quotes in the captions of recent hashtag feeds would break the caching features.
* Fix: Carousel feeds not displaying correctly for right-to-left sites.
* Fix: Follow button was not linking to user account under certain circumstances.
* Fix: Forced highlight and masonry feeds to use border-box box-sizing for sbi_item elements.
* Fix: Removed zoom effect for moderation mode.
* Fix: Forced the highlight offset setting to be an integer, ensuring that some posts are higlighted when using this feature.
* Fix: Spaces among user names when using shortcode settings would cause the feed to break.
* Fix: Resizing the screen while in moderation mode and also using the "Shoppable Feeds" feature would cause most images to disappear.

= 4.0.9 =
* Fix: Unable to connect new personal accounts due to changes with Instagram's API. Remote requests to connect accounts are now made server-side.
* Fix: User feeds where the username is the same as the hashtag for a recent hashtag feed would cause posts to appear out of order and for user feed posts to be included in the feed.

= 4.0.8 =
* Tweak: Source of scaled images changed from server resized images to images from Instagram's CDN.
* Tweak: Maximum number of posts data saved in the custom database tables raised from 150 to 1500.
* Tweak: Message added to the lightbox visible to admins when a video isn't able to be played, usually due to Instagram copyright restrictions.
* Fix: Some thumbnails were not displaying for video posts in hashtag feeds.
* Fix: Hashtag feeds carousel posts that included videos without video files available would cause a JavaScript error.
* Fix: Having a different number of posts displayed for mobile devices would not work with the initial post set cache.

= 4.0.7 =
* Tweak: Isotope JS code namespaced to prevent conflicts with other versions of Isotope.
* Fix: Duplicate images would be displayed in feed when displaying a user feed that has both a personal account and business account connected.
* Fix: Added rel="noopener" attribute to all cross-origin links.
* Fix: Fewer images than expected would be displayed using the initial post set cache feature when the number of posts on desktop was different than the number of posts for mobile.
* Fix: Double quotes in the location data and backslashes at the end of a caption would cause the initial post set feature to break.
* Fix: No image would display when displaying a carousel type post that contained only videos.
* Fix: PHP error for multisite sites when creating a new sub-site and trying to create tables.

= 4.0.6 =
* Fix: Hashtag feeds were not displaying for certain accounts due to an Instagram API bug. If you see the message "The requested resource does not exist" when trying to display a Hashtag feed, please go to the "Configure" tab and reconnect all of your business accounts using the blue "Connect an Instagram Account" button.
* Fix: Pop-up modal will now scroll when connecting a large amount of Business accounts on the Configure tab.
* Fix: "Mixed" feeds and feeds with multiple hashtags would not work or would display fewer posts than expected.
* Fix: Filtering a User feed by a hashtag or word will work better for feeds that use the hashtag or word infrequently.
* Fix: Feed would break when using the shoppable feeds feature with a Hashtag feed that contained a video with no thumbnail available.

= 4.0.5 =
* Tweak: More information displayed when there is an error connecting business accounts on the Configure tab.
* Tweak: Image resizing aborted in the event that the WordPress image editor code returns an error.
* Fix: Feed not displaying when admin-ajax requests had space added by other plugins or themes.
* Fix: Double quotes in parts of the header data would cause a JSON parsing error for initial page cache feature.
* Fix: 404 error displaying for hammer.js code in Safari.

= 4.0.4 =
* Fix: Business accounts with periods in the user names were causing the token to appear invalid
* Fix: Custom database tables can now be installed using a network activate for multisite installations

= 4.0.3 =
* Fix: "Initial Post Set Cache" feature would not load the feed under certain circumstances
* Fix: Manually connecting Business Profiles is now possible. Enter the Access Token and then the User ID in the respective fields on the "Configure" tab.

= 4.0.2 =
* Fix: Index for feed_id column was too large for certain MySQL servers/settings preventing the new database tables from being created
* Fix: Less than and greater than symbols ("<",">") causing JSON decoding issues and preventing the initial page cache from being set
* Fix: Fixed error "Unable to decrypt access token" occurring in certain circumstances
* Fix: Unable to add or remove user accounts to primary feeds in Firefox

= 4.0.1 =
* Fix: Using the setting "Are you using an AJAX theme?" was causing a JavaScript error
* Fix: Fixed an issue with single quotes in the initial feed JSON data
* Fix: Added a missing comma in the feed options array

= 4.0 =
* **MAJOR UDPATE**
* Important: On December 11, Instagram will be making some major platform changes which will disrupt Location feeds, Single Post feeds, and Hashtag feeds. Please [see here](https://smashballoon.com/instagram-api-changes-dec-11-2018/) for more information.
* New: Support for connecting Instagram Business Profiles on the Configure tab. After December 11, 2018, Hashtag feeds will require a Business Profile to be connected. [More information](https://smashballoon.com/instagram-business-profiles/)
* New: Hashtag feeds can now be ordered by 'Top posts' or 'Most recent'. This can be configured on the plugin settings page 'Hashtag' settings, or in the shortcode: order=recent.
* New: When selecting 'Most recent' Instagram will only return posts from the past 24 hours, however, the plugin will store these posts so that they can continue to be displayed indefinitely, creating a permanent feed of your posts.
* New: A limit of 30 unique hashtags per Business Profile can be queried per 7 day rolling period.
* New: Usernames and dates are no longer available for hashtag posts. [More information](https://smashballoon.com/instagram-api-changes-dec-11-2018/)
* New: Images which are only available in full size are downsized and stored on your server. This feature is automatically enabled for any feed which uses Instagram's new API, but can be disabled in the following location: Instagram Feed > Customize > Advanced > Misc > Image Resizing.
* New: Post data is now stored in a custom table in your database for greater flexibility in future updates.
* Tweak: Ajax requests are reduced in order to decrease feed load times and reduce the amount of resources used.

= 3.0.6 =
* Important: On December 11, Instagram will be making some major platform changes which will disrupt Location feeds, Single Post feeds, and Hashtag feeds. Please [see here](https://smashballoon.com/instagram-api-changes-dec-11-2018/) for more information. We are working hard on a plugin update which will be released prior to December 11 and include support for the new Hashtag API, along with directions on how to transition to it in order to avoid any disruption in your Hashtag feeds.
* Fix: Fixed an issue with spaces in the 'includewords' filter setting
* Fix: Fixed an issue with email "mailto" links in captions

= 3.0.5 =
* Fix: An "Unexpected end of JSON input" error was occurring on certain servers due to an issue with the name of the header cache
* Fix: The "Moderation Type" setting was not saving successfully on the settings page

= 3.0.4 =
* Fix: Japanese characters in hashtags were not working with the "includewords" filtering setting
* Fix: Fixed a bug caused by the previous updated which was causing the Masonry layout to display all posts at the same height
* Fix: Fixed a rare issue where certain settings wouldn't be saved when using the "Preserve settings when plugin is removed" option
* Fix: Fixed a formatting issue in the System Info

= 3.0.3 =
* Tweak: Added a setting to enqueue the JavaScript file for the plugin in the page header rather than the footer.
* Fix: Updating the plugin with the carousel setting enabled by default would cause the new layout settings to not be changeable.
* Fix: Fixed an issue where the Load More button would disappear if all posts for a feed were cached.

= 3.0.2 =
* Fix: PHP warning if settings on the "Customize" tab were never saved.
* Fix: Hashtags the included uppercase letters were not working with the "show posts that contain these words or hashtags" setting.
* Tweak: Updated the plugin updater class to the latest version

= 3.0.1 =
* Fix: Error caused by not including the "#" for a mixed feed type that includes hashtags.
* Fix: Disabling mobile swipe code would cause an error.
* Fix: Using the line break caption adjustment setting with the "Are you using an AJAX theme?" setting would cause an error.

= 3.0 =
* **MAJOR UDPATE**
* New: Masonry layout - Display your posts in their uncropped portrait or landscape aspect ratios with no vertical space between posts.
* New: Highlight layout - Highlight/enlarge specific posts in your feed in a number of ways: based on a set pattern, using specific post IDs, or based on a specific hashtag in the caption. For example, you could set the plugin to highlight any posts which include the hashtag of #highlight.
* New: Additional options for carousel feeds including a 2-row layout and infinite looping.
* New: "Mixed" feed type allows you to display feeds of multiple types in a single feed. Set the username/hashtag/post ID/location in the shortcode with the type set to "mixed" e.g. [instagram-feed type=mixed hashtag="#awesomeplugins" user="smashballoon"].
* New: More customizable header layouts and sizes. You can now center the avatar and account information above your feed as well as choose from three sizes: small, medium, and large.
* New: We've made improvements to the way photos are loaded into the feed, adding a smooth transition to display photos subtly rather than suddenly. We've also made enhacements to other interactive elements - such as hovering over a photo or using the carousel - to create a more refined experience.
* Tweak: Settings area has been reorganized to make finding and setting your options faster and more intuitive.
* Tweak: "includewords" setting will now search through author comments to detect hashtags.
* Tweak: Automatic image resolution detection setting now works better with wide images. Resizing the browser will now automatically raise the image resolution if needed.
* Tweak: Character limit will now adjust based on the number of line breaks in the caption. This can be disabled on the "Advanced" tab.
* Fix: Hashtags containing cyrillic characters were not working with the "includewords" feature.
* Fix: Desktop touch screens were not properly detected.

= 2.11 =
* Fix: Fixed an occasional issue with the Instagram login flow which would result in an "Unauthorized redirect URL" error
* Fix: Fixed an issue with the "Remove" button not working reliably when removing connected accounts
* Tweak: Added nonces to the admin settings

= 2.10 =
* New: Retrieving Access Tokens and connecting multiple Instagram accounts is now easier using our improved interface for managing account information. While on the Configure tab, click on the big blue button to connect an account, or use the "Manually Connect an Account" option to connect one using an existing Access Token. Once an account is connected, you can use the associated buttons to either add it to your primary User feed or to a different feed on your site using the `user` shortcode option, eg: `user=smashballoon`.
* Tweak: Disabled auto load in the database for backup caches and white lists
* Fix: Fixed a issue where comments could display potentially harmful HTML - Thanks Jonas Carlsson from Sweden!
* Fix: Removed code and support for using user names in user ID settings. Will now default to the user ID attached to the Access Token.
* Fix: Carousel feeds not working for right to left languages
* Fix: Comments not being retrieved with the correct Access Token
* Fix: Automatic loading of more posts on scroll disabled for carousel feeds
* Fix: Using a percent for the image padding was causing the height of images to be to tall

= 2.9.7 =
* Fix: Fixed and issue with new comments not being retrieved
* Fix: Access Tokens may have been incorrectly saved as invalid under certain circumstances

= 2.9.6 =
* Tweak: Setting "Cache Error API Recheck" enabled by default for new installs
* Tweak: Added back ability to show caption on the right side of the lightbox and avatars for user feeds in lightbox
* Fix: Page caches created with the WP Rocket plugin will be cleared when the Instagram Feed settings are updated or the cache is forced to clear
* Fix: Fixed a rare issue where feeds were displaying "Looking for cache that doesn't exist" when page caching was not being used

= 2.9.5 =
* Important: Due to [recent changes](https://smashballoon.com/instagram-api-changes-april-4-2018/) in the Instagram API it is no longer possible to display photos from other Instagram accounts which are not your own. You can only display the user feed of the account which is associated with your Access Token.
* New: Added an Access Token shortcode option and support for multiple Access Tokens. If you own multiple Instagram accounts then you can now use multiple Access Tokens in order to display user feeds from each account, either in separate feeds, or in the same feed. Just use the `accesstoken` shortcode option. See [this FAQ](https://smashballoon.com/display-multiple-instagram-feeds/#multiple-user-feeds) for more information on displaying multiple User feeds.

= 2.9.4 =
* Fix: Fixed an issue caused by the last update where the Load More button would skip some posts
* Fix: No source in video element in the lightbox causing an error in IE
* Fix: Combination of a page cache, user feed header, and setting "Cache Error API Recheck" causing header to not display
* Fix: Having more than one of the same feeds on the same page would sometimes cause the second feed not to load

= 2.9.3 =
* Fix: Fixed duplicating images in certain feeds
* Fix: Includewords code missing non-hashtag words after a line break

= 2.9.2 =
* Tweak: Lightbox given a fixed position for large screens. Scrolling the browser window will no longer scroll the lightbox on desktop devices.
* Tweak: Enabling "Cache Error API Recheck" setting will cause feeds to always assume a cache exists and will create one if one doesn't exist.
* Tweak: Custom image sizes no longer supported by Instagram. This feature will be unavailable for now.
* Fix: Added icon source setting for AJAX themes
* Fix: Fixed incorrectly sized images when mobile columns set to "1"
* Fix: SVG icons box-sizing set to "unset" to prevent issues with SVG icon sizes
* Fix: Hover background moved farther to the foreground to prevent rare issue with it not displaying
* Fix: Auto load more on scroll was causing problems for other features on sites that are triggered by scrolling
* Fix: Extra check added to prevent infinite loop of displaying welcome screen when installing the plugin
* Fix: Fixed links breaking for hashtags with underscores in the lightbox

= 2.9.1 =
* New: Added "alt" tags to lightbox images and screen reader text for improved accessibility
* Fix: SVG icons not displaying correctly in IE11
* Fix: Fixed a potential security vulnerability

= 2.9 =
* New: Added a permanent feed option. Permanent feeds are useful if you have a feed with a group of posts which never needs to be updated. It creates a permanent record of the feed for optimal performance. Use the shortcode setting "permanent" if a feed never needs to be updated: `[instagram-feed permanent="true"]`.
* New: Added backup caching for all feeds. If the feed is unable to display or too many posts are being filtered out, a backup feed will be shown to visitors if a backup cache is available.
* New: Added setting in moderation mode to make a "white list" feed permanent. Check the box labeled "This is a permanent white list (never needs to update)" when creating a "white list".
* New: Icons are now generated as SVGs for a sharper look and more semantic markup.
* New: Added support for translating the post date and the word "Share" in the lightbox.
* New: Added a setting to the Misc section to disable jQuery mobile code. This will fix issues with jQuery versions 2.x or later.
* New: Added support for running custom JavaScript code when the lightbox is launched
* Tweak: Added CSS to remove borders underneath links in the feed which were added by some themes.
* Tweak: Updating or installing the plugin will automatically clear the cache in popular page caching plugins and JavaScript optimizing plugins.

= 2.8.3 =
* New: Added config file for WPML compatibility. Display your feed on your multi-language sites [WPML website](https://wpml.org/)
* New: Added translation files for Danish (da_DK), Finnish (fi_FL), Japanese (ja_JP), Norwegian (nn_NO), Portuguese (pt_PT), and Swedish (sv_SE) to translate "Load More..." and "Follow on Instagram"
* Fix: Bug with linking random text in captions in the lightbox.
* Fix: Carousel feeds were not displaying properly when resizing the browser window from desktop to mobile width.

= 2.8.2 =
* Fix: Directions for a single post feed were not opening on the "Configure" tab.
* Fix: Carousel post types in the lightbox would not play the 2nd video in the carousel.
* Fix: Regular expression to add links to hashtags, websites, and mentions in the caption of the lightbox is improved.

= 2.8.1 =
* Fix: Fixed carousel feeds with mobile columns set to "auto" not showing images at their proper size.

= 2.8 =
* New: You can now choose to set the number of columns and posts to use for mobile, which allows you to decide how your Instagram feed is displayed across all devices. You can find these settings by navigating to `Customize > Layout`, and clicking on `Show Mobile Options` under the respective setting, or you can use the following shortcode options: `colsmobile=3 nummobile=9`
* New: Visitors to your site can now trigger the loading of more posts as they scroll down your feed. Enable this for all feeds by using the setting located at `Customize > Autoscroll Load More`, or apply this to a specific feed using the shortcode option: `autoscroll=true`
* New: It's now easier to collect post IDs for creating single post feeds as they can be displayed underneath posts while viewing a feed in "Moderation Mode". To view the ID for a post, enable "Moderation Mode" for your feed and simply check the box labeled "Show post ID under image".
* Tweak: Added an icon to carousel posts to let visitors know that it's a carousel
* Fix: Fixed an issue where the video would not play when the first slide in a carousel post was a video

= 2.7 =
* New: "Custom Image Sizes" now available for use in your feeds. These are available image resolutions not officially supported by Instagram. To use them, go to the "Customize" tab and check the box to "Use a Custom Image Size". You can then select from the revealed dropdown menu.
* New: Private feeds and single posts from private feeds will not break a feed but instead display a message to logged-in admins and exclude the private data
* Tweak: Lightbox moved farther to the foreground to prevent an issue with the navigation menu covering the lightbox in certain themes
* Tweak: Several images in the plugin have been optimized to reduce file size
* Tweak: Welcome page now only displayed for major updates
* Fix: Refactored code that was causing a false positive in a security plugin
* Fix: Carousel "slideshow" posts are still included in feeds which are set to only display photos
* Fix: Fixed missing "media" attribute in CSS file inclusion code

= 2.6.1 =
* Fix: Fixed an issue with videos in slideshow posts

= 2.6 =
* New: Added translation files for French (fr_FR), German (de_DE), English (en_EN), Spanish (es_ES), Italian (it_IT), and Russian (ru_RU) to translate "Load More..." and "Follow on Instagram"
* New: Instagram "Slideshow" posts are now supported. When viewing a slideshow post in the popup lightbox you can now scroll through to view the other images.
* Tweak: The lightbox navigation arrows have been moved outside of the image area to make room for slideshow posts and closer emulate the lightbox on Instagram
* Tweak: Font Awesome stylesheet handle has been renamed so it will only be loaded once if Custom Facebook Feed is also active
* Tweak: Removed query string at the end of the Font Awesome css file when being included on the page
* Fix: Undeclared variables in the JavaScript file now declared for strict mode compatibility

= 2.5.1 =
* Fix: Feed cache was being assigned to the header cache under certain conditions causing the header to show as "undefined"
* Fix: Php notice when saving moderation mode settings without any blocked users

= 2.5 =
* New: Added a workaround for an issue caused by some caching plugins. Enabling the "Force cache to clear on interval" setting on the "Customize" tab will now clear the page cache in some of the major caching plugins when the Instagram feed updates.
* Tweak: Reduced Ajax calls made by the plugin to one per feed to retrieve cached data
* Tweak: The plugin JavaScript file is now only included on pages where the feed is displayed, and a setting has been added to only load the CSS file on pages where the feed is displayed
* Tweak: Access token is now automatically saved if retrieved with the button on the "configure" tab
* Tweak: Changed how the caching errors caused by page caching plugins are handled
* Tweak: If you're using an Ajax theme and calling the plugin's `sbi_init()` function when the page content loads then we advise updating this to add a caching parameter. See [this page](https://smashballoon.com/my-photos-dont-show-up-sometimes-unless-i-refresh-my-page-ajax-theme/) for how you can update any custom code to take advantage of this.
* Fix: Improved sanitization and validation of data to be cached before saving to the database
* Fix: Added workaround for jQuery 3.0+ breaking jQuery mobile code
* Fix: Added space between an attribute to make feed html valid

= 2.4.2 =
* Fix: Using the "Load More" button in moderation mode would cause the moderation settings to submit more than once under certain circumstances.

= 2.4.1 =
* Fix: When used in conjunction with plugins that concatenate/minify/cache JavaScript the feed would sometimes load photos multiple times when certain settings were used. A setting was added to provide a workaround for cached pages as an option in the "Misc" section of the plugin's "Customize" tab.
* Fix: Fixed a bug caused when the HTML element that the Instagram Feed is inside doesn't have a class on it
* Fix: Fixed a JavaScript error that occurred in the lightbox when a post has no caption

= 2.4 =
* New: Added a visual moderation system (moderation mode) to allow you to create feeds of approved posts, block users, and remove specific posts from your feeds. Enable this feature on the "Customize" tab or add this to your shortcode: `[instagram-feed moderationmode="true"]`. Then click the 'moderate feed' button on the front end of your site. For further information, see [these directions](https://smashballoon.com/guide-to-moderation-mode/).
* New: Comments for individual posts are now available to be displayed in the lightbox. Enable this on the "Customize" tab or by adding this to your shortcode: `[instagram-feed lightboxcomments="true"]`. The number of comments shown can be changed as well: `[instagram-feed numcomments="10"]`.
* New: Create a "Shoppable" feed using links in the captions of your Instagram posts. Check the box next to this setting on the "Customize" tab or add this to the shortcode: `[instagram-feed captionlinks="true"]`. This requires an extra step when you post to Instagram. For further information, see [these directions](https://smashballoon.com/make-a-shoppable-feed/).
* New: Ability to show posts only from a specific user. Add a user to the setting on the "Customize" tab or add this to the shortcode: `[instagram-feed showusers="smashballoon"]`.
* Tweak: Improved hashtag detection of the "includewords" setting
* Fix: Spaces in the shortcode were causing issues for "single" feeds
* Fix: Removed padding on the "load more" button if it is hidden in the feed
* Fix: The first hashtag was not always being made into a link in the lightbox
* Fix: Lightboxes are now separated when there are more than one of them on the page
* Fix: Pagination would sometimes break for multiple user/location/hashtag feeds

= 2.3.1 =
* Fix: Instagram's new "Slideshow" post feature isn't supported yet by their API and so this was causing an error in feeds that included them. This error has been fixed but as Instagram hasn't yet added support in their API for slideshow posts then the plugin isn't able to display them. Once they add support then it will be added into the plugin.

= 2.3 =
* New: Added the ability to display a feed of specific posts. You can do this by using the `single` shortcode setting. First set the feed type to be "single", then paste the ID of the post(s) into the single shortcode setting, like so: `[instagram-feed type="single" single="sbi_1349591022052854916_10145706"]`. For further information, see [these directions](https://smashballoon.com/how-do-i-create-a-single-post-feed/).
* New: We've added a widget with the "Instagram Feed" label so that you no longer need to use the default "Text" widget
* Tweak: Addressed an occasional error with includewords/excludewords setting
* Tweak: Added commas to large numbers
* Tweak: When displaying photos by random the plugin will now randomize from the last 33 posts for unfiltered feeds rather than just randomizing the posts shown in the feed
* Tweak: User names can now be used instead of user ids for user feeds
* Fix: International characters are now supported in includewords/excludewords settings
* Fix: Fixed an undefined constant warning

= 2.2.1 =
* Tweak: Added a setting to disable the icon font used in the plugin
* Tweak: The "Include words" filtering option now only returns posts for an exact match instead of fuzzy matching
* Tweak: Change Instagram link to go to https
* Tweak: Added coordinates as attributes to the location element
* Fix: Fixed an issue with the Instagram image URLs which was resulting in inconsistent url references in some feeds
* Fix: Fixed an imcompatibility issue the MediaElement.js plugin
* Fix: Fixed an issue with videos not pausing in the lightbox when navigating using the keyboard arrows

= 2.2 =
* **IMPORTANT: Due to the recent Instagram API changes, in order for the Instagram Feed plugin to continue working after June 1st you must obtain a new Access Token by using the Instagram button on the plugin's Settings page.** This is required even if you recently already obtained a new token. Apologies for any inconvenience.

= 2.1.1 =
* Tweak: Updated the Instagram icon to match their new branding
* Tweak: Added a help link next to the Instagram login button in case there's an issue using it
* Fix: Updated the Font Awesome icon font to the latest version: 4.6.3

= 2.1 =
* Compatible with Instagram's new API changes effective June 1st
* New: Added the ability to display posts that your user has "liked" on Instagram. Thanks to Anders Hjort Straarup for his code contribution.
* New: Added a setting to allow you to use a fixed pixel width for the feed on desktop but switch to a 100% width responsive layout on mobile
* Tweak: Added a width and height attribute to the images to help improve Google PageSpeed score
* Tweak: When a feed contains posts from multiple hashtags then all of the hashtags are listed in the feed header
* Tweak: Allow users with WordPress "Editor" role to be able to moderate images in the feed
* Tweak: Added descriptive error messages
* Tweak: A few minor UI tweaks on the settings pages
* Fix: Hashtags which include foreign characters are now linked correctly
* Fix: Fixed an issue with the `showfollowers` shortcode option
* Fix: Fixed an issue with the carousel shortcode setting not working reliably
* Fix: Fixed an issue with the carousel script firing too soon when multiple API requests were required to fill the feed
* Misc bug fixes

= 2.0.4.2 =
* Fix: Fixed a JavaScript error in the admin area when using WordPress 4.5

= 2.0.4.1 =
* Fix: Fixed an issue with images in carousels not scaling correctly on mobile
* Fix: Fixed an issue with the lightbox breaking when an image didn't have a caption

= 2.0.4 =
* Fix: Fixed a bug which was causing the height of the photos to be shorter than they should have been in some themes
* Fix: Fixed an issue where when a feed was initially hidden (in a tab, for example) then the photo resolution was defaulting to 'thumbnail'

= 2.0.3 =
* Fix: Fixed an issue which was setting the visibility of some photos to be hidden in certain browsers
* Fix: The new square photo cropping is no longer being applied to feeds displaying images at less than 150px wide as the images from Instagram at this size are already square cropped
* Fix: Fixed a JavaScript error in Internet Explorer 8 caused by the 'addEventListener' function not being supported
* Note: If you notice any other bugs then please let us know so we can get them fixed right away. Thanks!

= 2.0.2 =
* Tweak: Added an option to force the plugin cache to clear on an interval if it isn't automatically clearing as expected
* Fix: Fixed an issue where photo wouldn't appear in the Instagram feed if it was initially being hidden
* Fix: Fixed an issue where the new image cropping fuction was failing to run on some sites and causing the images to appear as blank
* Fix: Fixed a bug where stray commas at the beginning or end of lists of IDs or hashtags would cause an error
* Fix: Removed the document ready function from around the plugin's initiating function so that it can be called externally if needed

= 2.0.1 =
* Fix: Fixed an issue with the number of likes and comments not showing over the photo when selected
* Fix: Fixed an issue with the carousel navigation arrows not being correctly aligned vertically when the caption was displayed beneath the photos
* Fix: The icons in the header for the number of photos and followers are now the right way around

= 2.0 =
* **MAJOR UDPATE**
* New: Completely rebuilt the core of the plugin to drastically improve the flexibility of the plugin and allow us to add some new post filtering options
* New: Added caching to minimize Instagram API requests
* New: Added a new Carousel feature which allows you to create awesome, customizable, and responsive carousels out of your Instagram feeds. Includes the ability to display navigation arrows, pagination, or enable autoplay. Use the Carousel settings on the plugin's Customize page or enable the carousel directly in your shortcode by using `carousel=true`. See [here]('https://smashballoon.com/instagram-feed/demo/carousel/') for an example of the carousel in action.
* New: You can now display photos from location ID. Use the field on the plugin's Settings page or the following shortcode options: `type=location location=213456451`.
* New: Display photos by location coordinates. Use the field on the plugin's Settings page or the following shortcode options: `type=coordinates coordinates="(25.76,-80.19,500)"`. See the directions on the plugin's Settings page for help on how to find coordinates.
* New: If you have uploaded a photo in portrait or landscape then the plugin will now display the square cropped version of photo in your feed and the full landscape/portrait image in the pop-up lightbox. **Important:** To enable this you will need to refresh your Access Token by using the big blue Instagram login button on the plugin's Settings page, and then copying your new token into the plugin's Access Token field.
* New: You can now choose to only show photos from your feeds which contain certain words or hashtags. For example, you can display photos from a User account which only contain a specific hashtag. Use the settings in the new 'Post Filtering' section on the Customize page, or define words or hashtags directly in your shortcode; `includewords="#sunshine"`
* New: You can now also remove photos which contain certain words or hashtags. Use the setting in the 'Post Filtering' section, or the following shortcode option `excludewords="bad, words"`
* New: Block photos from certain users by entering their usernames into the 'Block Users' field on the plugin's Customize page
* New: Added a second style of header. The 'boxed' header style can be configured under the 'Header' section of the plugin's Customize page, or enabled using `headerstyle=boxed`
* New: The plugin now automatically removes duplicate photos from your feed
* New: When you click on the name of a setting on the plugin's Settings pages it now displays the shortcode option for that setting, making it easier to find the option that you need
* New: Hashtags and @tags in the caption are now linked to the relevant pages on Instagram
* New: Text in the pop-up lightbox is now formatted with line breaks as it is on Instagram
* New: Choose to show the number of photos and followers an account has in the feed header. Use the setting under the 'Header' section, or the following shortcode option `showfollowers=true`.
* New: You can now choose to include only photos or only videos in your feed. Use the setting under the 'Photos' section on the Customize page, or the following shortcode option: `media=photos`.
* New: You can now display the photo location, caption, or number of likes and comments over the photo when it's hovered upon
* New: Pick and choose which information to show over the photo when it's hovered upon. Use the checkboxes under the 'Photo Hover Style' section, or the `hoverdisplay` shortcode option: `hoverdisplay="date, location, likes"`.
* Tweak: A header is now added to the hashtag feed and displays the hashtag
* Tweak: Added a loading symbol to the 'Load more' button to indicate when new photos are loading
* Fix: Fixed an issue where duplicate photos would be loaded into a feed if the 'Are you using an Ajax powered theme' setting was checked on a non-Ajax powered theme
* Fix: The play button icon shown over the top of the photo is now clickable
* Fix: Fixed an issue with emojis in the feed header displaying on a separate line
* Fix: Fixed a bug where the image resolution 'Auto-detect' setting would sometimes display the wrong image size

= 1.3.1 =
* New: Added an email option to the share icons in the pop-up lightbox
* Fix: Fixed an issue with the 'Load more' button not always showing when displaying photos from multiple hashtags or User IDs
* Fix: Fixed an issue where clicking on the play icon on the photo didn't launch the video pop-up
* Fix: Moved the initiating sbi_init function outside of the jQuery ready function so that it can be called externally if needed by Ajax powered themes/plugins
* Fix: Fixed a problem which sometimes caused the lightbox to conflict with lightboxes built into themes or other plugins

= 1.3 =
* New: Added an option to disable the pop-up photo lightbox
* New: Added swipe support for the popup lightbox on touch screen devices
* New: Added an setting which allows you to use the plugin with an Ajax powered theme
* New: Added an option to disable the mobile layout
* New: Added a Support tab which contains System Info to help with troubleshooting
* New: Added friendly error messages which display only to WordPress admins
* New: Added validation to the User ID field to prevent usernames being entered instead of IDs
* Tweak: Disabled the hover event on touch screen devices so that tapping the photo once launches the lightbox
* Tweak: Made the Access Token field slightly wider to prevent tokens being copy and pasted incorrectly
* Tweak: Updated the plugin updater/license check script

= 1.2.2 =
* New: Added the ability to add a class to the feed via the shortcode, like so: [instagram-feed class="my-feed"]
* Fix: Fixed an issue with videos not playing on some touch-screen devices
* Fix: Fixed an issue with video sizing on some mobile devices
* Fix: Addressed a few CSS issues which were causing some minor formatting issues on certain themes

= 1.2.1 =
* Fix: Fixed an issue with the width of videos exceeding the lightbox container on smaller screen sizes and mobile devices
* Fix: Fixed an issue with both buttons being hidden when there were no more posts to load, rather than just the 'Load More' button
* Fix: Added a small amount of margin to the top of the buttons to prevent them touching when displayed in narrow columns or on mobile

= 1.2 =
* New: You can now display photos from multiple User IDs or hashtags. Simply separate your IDs or hashtags by commas.
* New: Added an optional header to the feed which contains your profile picture, username and bio. You can activate this on the Customize page.
* New: Specific photos in your feed can now be hidden. A link is displayed in the popup photo lightbox to site admins only which reveals the photos ID. This can then be added to the new 'Hide Photos' section on the plugin's Customize page.
* New: The plugin now includes an 'Auto-detect' option for the Image Resolution setting which will automatically set the correct image resolution based on the size of your feed.
* New: Added the username and profile picture to the popup photo lightbox
* New: Added a 'Share' button to the photo lightbox which allows you to share the photo on various social media platforms
* New: Added an Instagram button to the photo lightbox which allows you to view the photo on Instagram
* New: Added an optional 'Follow on Instagram' button which can be displayed at the bottom of your feed. You can activate this on the Customize page.
* New: Added the ability to use your own custom text for the 'Load More' button
* New: You can now change the color of the text and icons which are displayed when hovering over the photos
* New: Added a loader icon to indicate that the images are loading
* Tweak: Tweaked some CSS to improve spacing and cross-browser consistency
* Tweak: Removed the semi-transparent background color from caption and likes section. can now be added via CSS instead using: #sb_instagram .sbi_info{ background: rgba(255,255,255,0.5); }
* Tweak: Improved the documentation within the plugin settings pages
* Fix: Fixed an issue with some photos not displaying at full size in the popup photo lightbox
* Fix: Added word wrapping to captions so that long sentences or hashtags without spaces to wrap onto the next line

= 1.1 =
* New: Added video support. Videos now play in the lightbox!
* New: Redesigned the photo hover state to use icons and include the date and author name
* New: Added an option to change the color of the hover background
* Tweak: You can now specify the hashtag with or without the # symbol
* Tweak: Tweaked the responsive design and modified the media queries so that the feed switches to 1 or 2 columns on mobile
* Tweak: Added a friendly message if you activate the Pro version of the plugin while the free version is still activated
* Tweak: Added a 'Settings' link to the Plugins page
* Tweak: Added a link to the [setup directions](https://smashballoon.com/instagram-feed/docs/)
* Fix: Replaced the 'on' function with the 'click' function to increase compatibility with themes using older versions of jQuery
* Fix: Fixed an issue with double quotes in photo captions
* Fix: Removed float from the feed container to prevent clearing issues with other widgets

= 1.0.3 =
* Tweak: If you have more than one Instagram feed on a page then the photos in each lightbox slideshow are now grouped by feed
* Tweak: Added an initialize function to the plugin
* Fix: Added a unique class and data attribute to the lightbox to prevent conflicts with other lightboxes on your site
* Fix: Fixed an occasional issue with the 'Sort Photos By' option being undefined

= 1.0.2 =
* Tweak: Added the photo caption as the 'alt' tag of the images
* Fix: Fixed an issue with the caption elipsis link not always working correctly after having clicked the 'Load More' button
* Fix: Changed the double quotes to single quotes on the 'data-options' attribute

= 1.0.1 =
* Fix: Fixed a minor issue with the Custom JavaScript being run before the photos are loaded

= 1.0 =
* Launched the Instagram Feed Pro plugin!
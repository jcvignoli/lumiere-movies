**Changelog**

v.4.4
* [feature] New item: connected movies, movies related the movie can be displayed
* [feature] Movie's quotes are back (just select it in admin data)
* [bug] Text if "no taxonomy theme is found" wasn't translated
* [bug] Movie popups shouldn't depend on items selections for the posts (Removed links between the admin data selection and popups in Popup_Movie)
* [bug] Activating the plugin could throw error in Logger class (due to debug file creation)
* [bug] Increased the padding between the layout and menu in popups movies & people 
* [bug] CLI functions (update_options) was not correctly updating the table option name. (Missing an extra "()" in update_options() of method sub_update_options() in Cli_Commands class)
* [bug] CLI functions (copy taxonomy template) was updated to the latest way to copy template (Cli_Commands class now use Copy_Taxo_Template class)
* [technical] PHP < 8.1 support dropped. The plugin is no more compatible with PHP 8.0 and lower. (refactored all classes with properties, not backward compatible)
* [technical] Refactorized Settings class to set the options only on activation (overdue for years: now Settings class is understandable, without properties and only constant, executed as such only on activation and when resetting options in admin panel, with proper static functions => most are called in Get_Options class)
* [technical] It is not possible to limit data in popups anymore. All data will be displayed.

v.4.3.3.1
* [feature] When using Lumière search admin, no message was returned if the query found no results (added a return text in class Search)
* [technical] Better layout for cache manage (add extra columns if need to reach multiple of 3 in people/movies)
* [technical] Logging system improved. (Logger class was a mess inherited for generations. Now it's a true class that one can understand)
* [bug] Cache auto-refresh wasn't working (Cache_Files_Management::cron_all_cache_refresh() wasn't setting correct transient name, missing "_store")

v.4.3.3
* [feature] Cache: ticked people or movie can be refreshed (Cache_Files_Management::refresh_multiple_file() created). Also improved cache files layout.
* [feature] Cache auto-refresh entirely reviewed. It now works per batches of movies and people and is bullet-proof (Cache_Files_Management::all_cache_refresh() rewritten)
* [bug] On [customized WordPress](https://wordpress.org/support/topic/4-3-2-1/ "Bug widget"), widget is unavailable 
* [bug] CLI functions (copy_taxo and update_options) were broken. (Fixed the new path for Get_Options)

v.4.3.2.1
* [bug] Widgets were not displayed anymore if auto widget was not activated

v.4.3.2
* [feature] Taxonomy template in user template folders (wp-content/theme/my_theme) will be automatically updated. Remove the line "* TemplateAutomaticUpdate (etc)" in your user templates (ie, wp-content/theme/my_theme/taxonomy-lumiere-director.php) if you do not want your templates to be automatically updated. (new class Auto_Update_Themes, auto update set up in cron and executed on auto/manual Lumière update or Lumière activation)
* [bug] Widget area limitation was too restrictive and didn't show up in custom posts pages. (rewrote exclusion in Widget_Frontpage::lum_get_widget())
* [technical] If AMP plugin is validating pages (when activating a new plugin) Lumière is executed and may be lead to a PHP fatal error (hidden). Movie class is now executed only if it's not an AMP validation test (new exclusion rule in Movie class lumiere_show() method)
* [technical] Renamed "general options" into "main options"

v.4.3.1
* [bug] When displaying the data in a blog in another language than English, field options for title and pics were displayed while they should appear (fix in template admin-data-display, replaced check of $lumiere_item_translated by $lumiere_item)
* [bug] Better translation availablity in admin manage cache
* [bug] "Writers" section into the Post had the number of episodes displayed even if 0, for the non-taxonomy version (rules updated in class Movie_Data)
* [bug] If not movie was found, the page was stopped (a throw Exception was in GraphQL)
* [bug] The plural version of "plot" was displayed even if only one plot was displayed (edited rule in Movie_Data)
* [bug] The official sites returned into the post were not accurate. (Limited to official and misc sites, rewritten lum_movies_officialsites() in Movie_Data)
* [bug] In admin manage cache, no confirmation notice was returned about the action just made (changed "echo 'test'" to exit( 0 ))
* [bug] Fixed debug and an a bug that prevented to retrieve movies in imdbGraphQLPHP ($this->logger->error() in class GraphQL method doRequest(), also casting object in  'variables', using a personal fork from now onwards). Also make sure that if no movie is retrieved, the page doesn't throw an Exception and breaks the layout (Imddphp class doing a try{} in search_movie_title() and search_person_name() methods)
* [bug] Bots weren't banned anymore.
* [technical] removed load_plugin_textdomain() (in Core class, [not needed anymore](https://make.wordpress.org/core/2016/07/06/i18n-improvements-in-4-6/ "Information on WordPress devolpment website")).
* [technical] removed _construct() that were bulding parent __construct() only

v.4.3
* [feature] Items and people fields (the option names) are now translated in Lumière admin data order
* [feature] Editing "URL for the taxonomy pages" will update the taxonomy. No more taxonomy left behind when removing Lumière. (classes Taxonomy and Save_Options include new methods)
* [feature] Added Plugin compability list in admin help
* [technical] Completely switched to [imdbGraphQLPHP](https://github.com/duck7000/imdbGraphQLPHP "imdbGraphQLPHP git") library. Goodbye [IMDBPHP](https://github.com/tboothman/imdbphp "IMDBPHP git"), thanks for your service for over 20 years.
* [technical] Using an interface for Popupals and they've been all refactored to implement it. They're child classes of Head_Popup and not using Virtual_Page anymore
* [technical] Deeply reviewed the taxonomy system. Less dependent on Polylang, bug fixing (classes Taxonomy and Polylang plugin).
* [bug] When changing "URL for the taxonomy pages" in admin general advanced, rewrite rules weren't automatically updated (Save_Options class now send flush_rewrite_rules() if that field is updated)
* [bug] Switched "Update" and "Reset" in admin menu. Longstanding strange way to display the options (edited admin templates)
* [bug] Forms in taxonomy pages were not working, especially with AMP (Rewritten and added to Polylang plugin, more OOP)
* [bug] Many bugs addressed in Post display of Lumière

v.4.2.3.1
* [bug] WordPress Update process was broken (Uncaught Error: Call to undefined function Lumiere\lum_check_display() in wp-content/plugins/lumiere-movies/class/class-updates.php:17 => added an include functions.php in Updates class)

v.4.2.3
* [technical] Increased security: added $_GET validation for popups (new Validate_Get class in tools)
* [technical] Increased security: added nonces for popups (adding nonces to Abstract_Link_Maker class and lumiere-*-links.js)
* [technical] Increased security: taxonomy pages are executed only if they are indeeded taxonomy (class-taxonomy-items-standard and class-taxonomy-people-standard modified)
* [technical] Increased security: Movie class is executed only on posts and pages (class Movie modified)
* [bug] If AMP plugin is used, showing the admin bar, as it needs it (exception for AMP added to Popup_Movie, Popup_Person, Popup_Search classes)
* [bug] "Click more" links in taxonomy pages and persons popups wasn't working (changed strategy from async to defer for lumiere_hide_show js in class Frontend, hopefully will work)
* [bug] If WordPress is displayed in any language but English, taxonomy copy in Lumière admin was dysfunctional (use of array_keys() in Copy_Template_taxonomy and Detect_New_Theme classes to get the untranslated job/item instead of translated one, the comparaison was made between the translated and English version of the words, in Data class also in method lumiere_data_display_taxo_fields() using $items_key and $items_value)
* [bug] HTML labels and select in WordPress block editions compliants with HTML specifications (block movie index.js and Widget_Selection class fixed)

v.4.2.2
* [feature] Compatibility with Intelly Related (IRP) post plugin. If a post that includes Lumiere movies is displayed, by default URP's related posts won't be displayed inside the post. This feature can be overriden in Lumière advanced general options (new auto plugin IRP, new option var imdbirpdisplay in admin options)
* [technical] Renamed get_compat_* methods in Settings to get_*_tablename, those methods return now automatically the current row name in wp_option only
* [bug] "Notice: Function _load_textdomain_just_in_time was called incorrectly." (Logger class was executed before init hook in class Core)

v.4.2.1
* [feature] Added Spouses and Children to people popups full biography section
* [feature] Language form in Taxonomy pages is automatically submitted on language change (added jquery script in lumiere_scripts.js and renamed &lt;input type="submit" name="submit"&gt; to &lt;input type="submit" name="submit_lang"&gt; - "name" couldn't be "submit")
* [bug] Couldn't save Popups settings in General settings (fixed class Save_options method lumiere_general_options_save(), added "! isset( $_POST['imdbpopup_modal_window'] )" )
* [bug] Notices weren't displayed (trait Admin_General had a sanitize_key() instead of esc_url() in method lumiere_get_current_admin_url())
* [bug] Link to activate taxonomy when it's not activated (next to "Data Management") was outdated (updated in template admin-menu-first-part.php)
* [bug] Movies languages were not available (changed IMDb method from languages() to language(), using GraphQL)
* [technical] Taxonomy pages for people also retrieve pages written about the person (previously only posts were retrieved, added 'page' to queries in classes Polylang and Taxonomy_People_Standard)
* [technical] Renamed database table names (from imdb* to lumiere_*)
* [technical] Added notice for deprecated movie methods 
* [technical] Updated to Monolog 2.10

v.4.2
* [technical] Compatible 6.7 (load_plugin_textdomain() is loaded in add_action('init') in class Core)
* [technical] Better sanitization according to new WordPress rules
* [bug] Fixed biography, wasn't available (moving to imdbGraphQLPHP, IMDbPHP is not maintained anymore)
* [bug] Fixed all movies display for People's popup, (moving to imdbGraphQLPHP, IMDbPHP is not maintained anymore)

v.4.1.8
* [bug] Fatal error if plot is null ("Argument #1 ($plot) must be of type string, null given, called in class/frontend/class-movie-data.php on line 1064")
* [technical] Added cache option to not display any cache detail (for very slow computers, for sites with a lot of cache data that even simplify the cache is not enough

v.4.1.7
* [bug] Fixed fatal error on WordPress 6.6 when deleting cache files (added Files trait in cache_delete_specific_file() of class Cache_Files_Management, "global $wp_filesystem" wasn't working alone anymore)
* [technical] Redondant link to "showtimes" folder pictures built (double "//" iin class Abstract_Link_Makern lumiere_movies_rating_picture_abstract() 

v.4.1.6
* [bug] Fatal error in Abstract link maker if no &lt;a&gt; link exists in medaillon (PHP Fatal error: Uncaught ValueError: strpos(): Argument #3 ($offset) must be contained in argument #1 ($haystack) in wp-content/plugins/lumiere-movies/class/frontend/link_makers/class-abstract-link-maker.php:336)
* [technical] Class Logger fully psalm compliant, logs are standardized ( removed do_action( 'lumiere_logger' ) in class Cache Tools and Uninstall, added do_action( 'lumiere_logger' ) in class Logger->log() )

v.4.1.5
* [bug] Medaillon text wasn't correctly displayed in taxonomy people page. (edits in class Abstract link maker)
* [bug] If widget auto title function is utilised with no extra widget metabox, two identical movies are displayed (fixed array_filter() in class widget frontpage, checking count() in addition to !== null)

v.4.1.4
* [bug] Pictures if using AMP display into the post and widget was screwed up (longstanding bug, added height in Abstract_Link_Maker class and escaping height in Widget_Frontpage class)
* [bug] Pictures if using AMP display in taxonomy people pages was screwed up (longstanding bug, added height in Abstract_Link_Maker class and escaping height in Taxonomy_People_Standard class)
* [bug] Couldn't save any options in admin general options (rewrote header check in Save_Options class::lumiere_general_options_save())
* [technical] If no biography found, return text saying so
* [technical] Taxonomy people pages 100% AMP compatible (removed <font> tags in Taxonomy_People_Standard class)
* [technical] Removed obsolete methods (lumiere_link_picture_taxonomy_abstract() and lumiere_link_picture_taxonomy())

v.4.1.3
* [bug] Items taxonomy template wasn't taking into account if "URL for the taxonomy pages" was changed in advanced admin options
* [bug] Minor warning in class save option ($_POST['imdb_imdburlpopups'] and $_POST['imdb_imdburlstringtaxo'] undefined)
* [technical] Further compliance with WordPress standards and security, notably added nonces
* [technical] New function in wp-cli to copy taxonomy templates
* [technical] Added warning notification in admin general advanced options if wrong value entered for $_POST['imdb_imdburlstringtaxo']

v.4.1.2
* [feature] Popup layouts are now universal and fit into any WordPress theme (finding if it's a block-based theme and displaying a different layout)
* [feature] Taxonomy themes are now universal and fit into any WordPress theme (finding if it's a block-based theme and displaying a different layout)
* [bug] Very unusual bug when copying taxonomy template files (php-fpm with umask 002, could copy but not reading due to permissions, added an extra check in Copy_Template_Taxonomy::copy_taxonomy_template()
* [bug] On some environments, the Polylang selection in people taxonomy didn't find the language. (Changed the SQL query, now using a two-position lang in form)
* [technical] WP functions further WP standard compliance and WP escaping (thanks to the [WP plugin check](https://wordpress.org/plugins/plugin-check/ "Plugin Check on WordPress") )
* [technical] Added the feature of using wp-cli. In the future, we may have subtancial wp-cli commands. (new class Cli_Commands, detection in Core whether execute it)

v.4.1.1
* [feature] Notifications messages translated.
* [bug] Metadata update did not include WordPress pages in update 4.1. It only updated posts. (condition for updating pages added in updates/16.php)
* [bug] Notification of new templates was provided even if taxonomy was not active (extra check in class "Detect new template taxo" added)
* [bug] When updating/reseting options, or when refreshing/deleting cache items, notifications were not shown on slow systems (increased transient time in class Save_Options, added a delete_transient in Admin_Notificationadmin_msg_transients())
* [technical] Ensure greater reliability of the WordPress update process. (A cron meant to execute updates is added on manual/automatic update, so we make sure that latest updates are really run. Also a check about any new update available is processed on every admin page).
* [technical] Notification system more OOP (Class Admin_Notification is called by class Detect_New_Theme, not the other way round)

v.4.1
* [feature] Auto title widget can be excluded on a per-post basis. New option in the post edition that allows to exclude a specific post from the auto title widget. Brings a much needed flexibility for blogs using the auto title widget feature. (added the new options in Metabox_Selection class and new rules in Widget_Frontpage)
* [feature] Thumbnail pictures in taxonomy pages will be quicker to be displayed (using lazy loading, thumbs for the thumb images preciseley)
* [feature] No need anymore for refreshing rewrite rules when creating a new taxonomy item, done automatically (added flush_rewrite_rules() in Save_Options::lumiere_data_options_save(), which is triggered only when saving taxonomy selection)
* [feature] Popups spinners vastly improved, work with all modal windows. Clicking on any link in popup will trigger a new spinner if the loading time is > 1 second.
* [feature] Popups layout vastly reviewed and enhanced, link color changed, added a picture for illustrating the link for movie searching
* [feature] The color of links to taxonomy pages is now different than to popups, for better understanding of what comes next. More respectful of plugin user css, not modifing links into the posts except in Lumière area (used to change to grey color the links)
* [feature] Pages can include Lumière widgets. Only post previously could. Exceptions to auto title widget can also be added into pages.
* [bug] Custom stylesheet added in personal theme folder wasn't working (now unregister main style and register the custom style in Core class if a parent or child theme is found)
* [bug] Frontpage and administration stylesheets standardised, less conflict is expected with themes
* [bug] If using Highslide modal window with OceanWP theme, pictures were somewhat taken between an extra box created by OceanWP (detection if OceanWP is installed and highslide is activated in oceanwp plugins class, then deactivating OceanWP boxes)
* [bug] Title in popup search were a mess with + and \ in the title (better data escaping in Popup Search class)
* [bug] Bootsrap spinner for bootstrap fixed, it correctly disappears when page is fully loaded
* [bug] Pictures in popup where smaller depending on the results. Fixed their size for both movies and people
* [bug] Fix the form in taxonomy people pages (if both Polylang and AMP plugins were activated, the form to change the language did't work, use $_GET instead $_POST)
* [bug] A function to launch a popup in post edition was not working (finalised blocks/opensearch/index.js)
* [bug] Legacy widget wasn't working, who knows since when. If you use classic widget plugin, you can use Lumière legacy widget. (fixed the extends to Widget_Selection instead of WP_Widget in Lumiere\Frontend\Widget_Legacy class, in Widget_Selection register_widget( 'Widget_Legacy' ) instead of __CLASS__ )
* [bug] When reseting options in General options, no confirmation message was displayed.
* [bug] Plugin Update wasn't working (Casting now to string in class update the option 'imdbHowManyUpdates', which doesn't actually make sense but works)
* [technical] Gutenberg block registration is now done in more modern way (using register_block_type() and block.json). Splitted "add imdb link" and "open search" into two blocks. Widget is also registered that way.
* [technical] Due to Gutenberg block limitation, a class is now added in &lt;span data-lum_movie_maker=""&gt;&lt;/span&gt;. The plugin remains fully compatible with the span without class. (Edited regexes in class movie to take into account the possiblity that spans can include a class)
* [technical] Plugins loading is fully OOP
* [technical] Bot banning status changed from 403 to 400, which is more appropriate, not banning bots on taxonomy or normal post pages
* [technical] Notifications are only shown in Lumière admin options pages. If a taxonomy file is not installed, always nag the user to install it.
* [technical] Lot of cleaning, standardised css classes, removed many unused methods in css and php, factorized and using more dedicated traits, using official WordPress way to include blocks (block.json), more standard WordPress string escaping
* [technical] The Polylang language forms in taxonomy pages for people now uses $_Get instead of $_Post, required to make the AMP form work (edited Polylang and Taxonomy_People_Standard clases in this regard)
* [technical] Replaced post metatada 'imdb-movie-widget-bymid' by 'lumiere_widget_movieid' and 'imdb-movie-widget' by 'lumiere_widget_movietitle'. Deleted obsolete post metada keys 'lumiere_queryid_widget_input' and 'lumiere_queryid_widget' The changes are done through the class update number 15.
* [technical] Moved the blueprint.json in the correct asset folder (meant to be in wordpress.org, actually).

v.4.0.2
* [feature] Thumbnail pictures (those which are displayed automatically into the posts, in cache, in popups) are now automatically resized should they weight more that 80kb, which should display all pages faster. Maintained the possiblity to click on these thumbnails to display the big posters (Cover option in Lumière admin).
* [bug] Refresh cache person was broken (left a bracket in glob search in Cache_Files_Management::cache_refresh_specific_file())
* [bug] Refresh cache person didn't recreate the big picture
* [bug] The movie/person big poster wasn't producing expected effects. Changed the way IMDBPHP lib ImageProcess class works: it now resizes the thumbs only if they weight too much (above 80 Ko).

v.4.0.1
* [feature] If year/month/day of birth/born date are unknown in people popups, display "(year/month/day unknown)" instead of displaying a 0
* [feature] The "month" name for birth/born date in people popups is now locally translated.
* [feature] Various layout fixes in people and movie popups, numbering the goofs, trivias, etc. (classes popup person and movie)
* [feature] Added a spinner for loading popups, with 1 seconds timeout. Let's see if it's worth keeping.
* [feature] Bootstrap modal window's width is now editable (removed limits set in bootstrap-custom.css, removed hidding 'imdb_imdbpopuplarg' in function select[name=imdbpopup_modal_window] in script_admin.js, added method bootstrap_convert_modal_size() in class Abstract_Link_Maker)
* [feature] "Back" button in people popups is displayed only if it's not the first page to be displayed.
* [feature] Faster display: into the post, in the widget and in popups smaller versions of the pictures are now displayed. Link to larger version is kept (clicking on the picture will show the larger version).
* [bug] Privacy policy was not displayed in admin Policy Guide, options-privacy.php?tab=policyguide (replaced in title "Lumière" with "Lumiere" in the title of wp_add_privacy_policy_content() function)
* [bug] Into the post, widget and popup picture: if "Display only thumbnail" was selected, huge picture was displayed. (Changed 100em to 100px in abbstract link maker and popups)
* [bug] Cron refresh cache sometimes doesn't retrieve people. Trying a new trick (added a sleep() between movies and people)
* [bug] Trivia in people popups: if less than tree trivias, layout was broken (removed extra div in display_misc() method)
* [bug] On some specific environments, taxonomy template copy wasn't working. (wp_get_referer() wasn't set in copy template taxonomy class, removed the redirection, found using WordPress playground)
* [bug] On some very specific environments, cache display in admin cache options didn't work (GLOB_BRACE couldn't be used, removed it from the cache classes, found using WordPress playground).
* [technical] Added blueprint.json in assets for WordPress Live Preview use.
* [technical] Popups now always ban users when there is no HTTP_REFERER => it's always bots, no need to let them index and call.
* [technical] Popups' size is now default to 800 width and 500 height (edited default values in class Settings and template admin-general-layout)

v.4.0
* [feature] Added in the general advanced options an option to add an extra delay when querying the IMDb website. Meant to avoid the HTTP 504 errors thrown when too many requests in a row are processed. 0 seconds by default, but can be increased to add a delay. If the data are already cached, no further delay, it's as fast as it used to be. Usefull when using the new auto refresh option, if you cache is large it may be welcome to add such a delay.
* [feature] Better way to *display taxonomy items*, such as genre. It now displays all terms related to the item (i.e. movie's genre -> action). Make sure to delete all tags and visit your posts, taxonomy will be automatically created.
* [feature] Added new automatized refresh of the cache function! By selecting that option in admin cache options, your cache will be refreshed every two weeks. No need to bother anymore about refreshing manually or letting the visitors wait until the new cache to be retrieved.
* [bug] Looong standing bug: Lumière taxonomy was preventing "categories" and "tags" to be displayed in posts admin menu (using now 'labels' in class taxonomy)
* [bug] Plugin bulk activation was not working (removed check_admin_referer() in class core lumiere_on_activation() method)
* [bug] Fixed the select of the data position that was displayed vertically (removed lumiere_writing_vertical class in class-data.php)
* [bug] Removed a style attribute in a div that prevented CSP compatibility (removed style="height:100%;" in class-data.php)
* [bug] HTML 4.1 compatibiliy: label HTML tag wasn't linking a proper input id (added '_yes' in method lumiere_data_display_taxo_fields() of class-data.php, properly renamed inputs and labels in class-general.php, fixed the taxonomy form in people taxonomy themes )
* [bug] Fixed the taxonomy system, it now properly creates HTML links towards taxonomy pages within widgets/into the posts sections (now it's hierarchical, it's not using polylang functions anymore, edited classes movie and polylang and taxonomy). It is advised to delete all taxonomy tags you may have, so new are create again.
* [bug] Cache folder creation fixed (in class settings, lumiere_create_cache() was checking if a folder exists before actually creating it)
* [bug] Visibility: When IMDb links to popups where created in WordPress block editor (post edition), they were not visible. Now, an icon clearly shows that a link to popups was created in the post's text.
* [bug] Admin: if left menu display was not selected, subpages were not available (typically on the first plugin install)
* [bug] Reset/Update fields in Manage Cache admin page were not standardized.
* [bug] Popups for people and movies should be compliant with any theme now (removed OceanWP conditions, using 'the_posts' hook only)
* [bug] Only one trailer was beeing (sometimes) displayed, and most often no trailer at all. Fixed trailer method in Title class in *IMDbPHP personal library*.
* [technical] Massively rewrote the admin section. Under the hood, more robust plugin!
* [technical] Movies searches are lowercased for increasing chances of hitting cache (and saving cache space used)
* [technical] PHP8.2 compatibility: replaced FILTER_SANITIZE_STRING (obsolete) with FILTER_SANITIZE_FULL_SPECIAL_CHARS in class admin/data
* [technical] Page downloads from IMDb are refetched up to 3 times before giving up.
* [technical] Cleaned useless cache settings (cache zipping is now hardcoded in class imdbphp)
* [technical] Cleaned useless function in class movie (cleaned lumiere_movies_quote(), which is useless but must be kept for compatibility)
* [technical] Added more security to the cache management (in class cache, refresh/delete of individual people/movies are now checked against a WP NONCE)
* [technical] Updated to latest *personal IMDbPHP libray* which replicates official IMDbPHP 8.2
* [technical] Added Bingbot and Googlebots to the banned bots, since it doesn't respect the "no follow" rule.
* [technical] Rewrote the bot banning. It is now executed before doing IMDb searches, avoiding cache creationg (and 504 HTML errors when calling IMDb website)
* [technical] In admin, notices for update/reset options is now WP notification system compliant (using transients in child classes to display notifications messages set in admin parent class)
* [technical] Removed tipee donation, kept Paypal only.
* [technical] Factorization of classes: Movie class (new class Movie_Data), Cache class (new class Cache_Files_Management), moved Frontend popups in new folder (frontend/popups/)
* [technical] Automatized task that runs to delete oversized cache folder (option "Keep automatically cache size below a limit") runs now hourly instead of every two hours.
* [technical] Updated to bootstrap 5.3.3
* [technical] Replaced obsolete PHP functions, compatibility with PHP > 8.0, compatibility with PHP8.3 too ( function get_class() removed/updated ).
* [technical] Uninstalling the plugin with the option "keep settings upon uninstall" unselected will now delete Lumiere taxonomy templates in theme directory.
* [technical] Still PHP 8.0 compatible, but expect PHP 8.1 to be standard soon.

v.3.11.5
* [bug] Fixed *personal IMDbPHP library*: under some circonstances, error "No such file or directory in ImageProcessor.php on line 44", added extra conditions to before unlink and rename

v.3.11.4
* [feature] Some bots scanning the popups without considering head rules (nofollow) will be now banned. This will prevents from having bots creating a huge cache and many requests that get user banned from IMDB (and save space).
* [bug] Fixed cache image creation (*personal IMDbPHP* class ImageProcessor was deleting _big image before knowing if they could be resized)
* [bug] Fixed pictures were not display on first pass (*personal IMDbPHP* change, if pictures were not reduced in size, a return false was preventing them from showing on first pass)
* [technical] AISEO plugin is deactivated in popups (movies+people). There is no point in adding SEO tools in something that should not be indexed.

v.3.11.3
* [bug] Fixed longstanding missing picture in regular wordpress edition (block edition)
* [bug] Fixed typo in previous release
* [bug] Fixed retrieving episodes (*personal IMDbPHP library* change)
* [bug] Fixed IMDbPHP purge (*personal IMDbPHP library* change, PHP Warning: filemtime(): stat failed for ($fname) in Imdb/Cache.php on line 135)

v.3.11.2
* [bug] Fixed bug introduced by the fix on rewrite rules in v3.11.1 that prevented popups to be displayed unless doing a full rewrite rules flush. Using in class rewrite rules the add_action( 'admin_init' ...) instead of add_action( 'generate_rewrite_rules' ...).

v.3.11.1
* [bug] Rewrite rules caused fatal error on some [environments](https://wordpress.org/support/topic/critical-error-575/ "Lumiere WordPress support") 
* [bug] Version of template item wasn't updated, so the template kept using the removed trait frontend
* [bug] Self adding taxonomy wasn't working.
* [bug] Depending on the theme used, the popups were not display stylesheets anymore.
* [technical] Removed French po/mo since all French strings are now managed online

v.3.11
* [feature] Popups are fully compatible now with Polylang. Both taxonomy and popups are now fully translated into French.
* [feature] Added privacy text to add to the website (/wp-admin/options-privacy.php?tab=policyguide)
* [feature] Movies and People pictures are now automatically resized. No option is (yet?) provided to change the default new size (proportional to 800x800). Meant to save space in cache folder, those IMDb pictures have become huge.
* [feature] Improved French translation. French translation is now available from WordPress website!
* [feature] Added an advanced option to edit plugin path (should be edited only carefully)
* [feature] Displaying articles that include Lumière's data is faster. Taxonomy system changed when displaying movies into the post, reduced the number of calls. 
* [bug] In some rare circonstances, two movies in widget were displayed. (fixed class widget frontpage, added array_search() in lumiere_display_widget() to better filter arrays)
* [bug] Since using GraphQL to retrieve most of the data, cache of GraphQL wasn't deleted when specific movie/people was selected.
* [bug] Uninstall process was not compatible with PHP 8.1, throwing errors. Fixed Uninstall and Settings classes.
* [bug] Better PHP 8.2 compatibility (function lumiere_parse_spans() in Movies class returns now string only instead of ( null | string)
* [bug] The language form for people taxonomy was buggy. Fixed.
* [bug] Picture in people taxonomy page was not shown if no big poster was retrieved.
* [bug] Title's "also known as" is back. It was unavailable since a very long time.
* [technical] Lumière! is not executed in feeds anymore (created lumiere_prohibited_areas() in movie class)
* [technical] Support for PHP < 8.0 dropped, removed home-made str_contains() compatibility meant to support older PHP versions.
* [technical] Permissions for cache directory changed. Created with chmod 775, as the other plugins do.
* [technical] Permissions for logging file. Created with chmod 775, as the other plugins do.
* [technical] Updated to bootstrap 5.3.2

v.3.10.1
* [feature] Spinners added: when loading popups and information in popups, a waiting animation will be displayed until the information is retrieved
* [bug] fixed trivia, goofs and soundtrack in popup movie (and main movie class for soundtrack too)
* [bug] fixed search akas in popup movie was not allowing to click on movie's and director links
* [bug] removed trick for preventing double register of lumiere_scripts.js, seems it is not needed anymore (deactivated wp_dequeue_script( 'lumiere_scripts' ) in class bootstrap-links)
* [bug] fixed javascript back in popup (lumiere_scripts.js was not loaded due to the previous bug)
* [bug] fixed spinner appearing in popup search (lumiere_scripts.js was not loaded due to the previous bug), added spinner in popup person and popup movies

v.3.10
* [bug] fixed many biographical functions (nickname, movies, etc) in popup for people. 
* [bug] On some environments, check for theme folders version fails. Added extra check on $wp_filesystem for $content_intheme and $content_inplugin in class admin. Added extra check in class copy template taxonomy.
* [bug] AMP was not fully detected. fixed in class utils.
* [technical] Using [forked IMDbPHP](https://github.com/jcvignoli/imdbphp "forked IMDbPHP git") library. Will switch back to the [tboothman](https://github.com/tboothman/imdbphp "tboothman's IMDbPHP git")'s one when it will be maintained again.
* [technical] Updated to bootstrap 5.3.1
* [technical] Rewrote lumiere_wp_filesystem_cred() function in class utils. Does not return bool anymore, but fully uses WP_Filesystem. Edited class cache accordingly.

v.3.9.15
* [bug] Moved function the check for empty $_GETs to __construct() in class/frontend/class-popup-search.php so no empty $_GETs for films are throwing errors
* [bug] Extra isset() check for $portrayedmovie in line 462 was throwing error if $portrayedmovie was null in class/frontend/class-popup-person.php

v.3.9.14
* [technical] Updated to IMDbPHP 8.1

v.3.9.13
* [technical] Updated to IMDbPHP 8.0.2

v.3.9.12
* [technical] Updated to IMDbPHP 8.0.1 (update to reflect recent IMDb changes)
* [technical] Small compatibility glitch in class popup person ( added isset( $biomovie[ $i ]['year'] ) )

v.3.9.11
* [bug] Picture in block widget admin interface was not showing up
* [technical] Updated to IMDbPHP 7.4.2

v.3.9.10
* [bug] On theme OceanWP, popups were not displaying correctly. (Detect in class-popup-search if OceanWP is active and use a different hook)
* [bug] Removed loading circle in class-popup-search, has always been random

v.3.9.9
* [bug] On theme OceanWP, popups were not displaying correctly. (Detect in class-popup-movie and class-popup-person if OceanWP is active and use a different hook)
* [technical] Compatibility with PHP 8.1 improved (class-polylang and Title.php from IMDbPHP lib)

v.3.9.8
* [technical] Updated to bootstrap 5.2.3
* [bug] Removed metaboxes in class-help. Long standing useless metaboxes replaced by click to unhide with js.
* [bug] If role for movie's actor line (in Class movie) did not exist, was resulting in php error

v.3.9.7
* [bug] Data-Taxonomy admin page: detecting system was too complicated and led to always display a notification if a template in theme folder was found. Simplified.
* [bug] Updates were never run if plugin was auto-updated. (Added new function lumiere_on_lumiere_upgrade_autoupdate() in 'automatic_updates_complete' hook in Core class)
* [bug] In some environments, help section of admin pages triggered fatal error ( global $wp_system was not initialised, added new require_once and initialisation of WP_Filesystem() in class-help)
* [technical] Various potential "pre-bugs" founds thanks to Phan
* [technical] Since updates are also run during autoupdate, installing one single cron when activating Lumière instead of three.

v.3.9.6
* [bug] Data-Taxonomy admin page: when a new taxonomy template was found, it prevented to display further not installed taxonomy templates.
* [bug] PHPStan fixes, notably add_actions() and add_filter() permutations.
* [technical] The weekly automatic delete is now daily (cron lumiere_cron_deletecacheoversized)
* [technical] Removed all urls in Plots section of Movies (both frontend and Popups). Those urls were a potential security threat and quite useless. Kept the author's names for proper credits.

v.3.9.5
* [feature] Added meta robots "nofollow" to all popups. Complements previous "nofollow" links in release 3.8.3.
* [bug] If deleting all cache, throws error that image folder not found in manage cache admin page.
* [bug] Popup headers were still linking to old pic folder (/pics), not in /assets/pics, fixed in Core class.

v.3.9.4
* [feature] New Cache function: Keep automatically cache folder size below a certain size limit. (Cache size is always growing, with many websiders following the nofollow links. Can set up a WP-cron to keep cache folder size under a select limit.)
* [bug] If using OceanWP theme and bootstrap popups together, the menu disappeard. Added a specific fix in css lumiere-extrapages-oceanwpfixes.css
* [technical] Display warning of "new taxonomy template file is found" limited to Lumière! Admin pages only
* [technical] Moved css, js & pics into new assets folder
* [technical] Fix WordPress admin css that uses Roboto as css in body which prevents flex wrap: added overflow-wrap:anywhere; in lumiere-admin.css

v.3.9.3
* [bug] Recent WordPress changes broke the autodisplay of old widgets (legacy widgets) in widgets admin page. Both are now available at the same time. User should carefully select a legacy or block based widget in WordPress block pages according to whether they have a old WordPress install (prior to 5.8) or installed a Classic Widget plugin that brings back the pre-5.8 widget interface.
* [technical] Improved the "how to" section related to auto title widget.
* [technical] Splitted class-widget into three class, 1 for admin and 2 for Frontpage. Streamlined the code. Legacy widget using Widget_Frontpage class. Totally separated the logic and the layout.

v.3.9.2
* [bug] Bootstrap popup size was not correctly working on WordPress.com environment. Works better, but there is still room for improvement.
* [bug] Popups were not displaying movies/people/search data on WordPress.com environment. Using now a different hook to display layout in those popups, passed from 'get_header' to 'the_posts'.
* [bug] Cache folder could be created above the level of wp-content. It is now limited to create a the cache folder into wp-content, which leads to more versatility of the plugin in multisites environments (using WP_CONTENT_DIR instead of ABSPATH constant to build the cache folder). Works perfectly on WordPress.com environment.
* [bug] Layout was not available when visting first Lumière! admin options page (options-general.php?page=lumiere_options, link to Lumière options in WP Settings section). Added that page to conditional pages to display the layout.
* [technical] lumiere_array_contains_term() in class utils was not escaping special characters provided in array of URLs. Made the function more versatile, using it in Core class.

v.3.9.1
* [bug] Under very specific Linux environments such as wordpress.com, debug file could not be created. Changed the creation process in class-logger and added create_log() method.

v.3.9
* [feature] If a new taxonomy template is found, a notice is shown in admin Lumière options (will keep nagging until update is processed)
* [feature] Lumière will not be compatible with plugins that promote a web of spammers. First plugin to enter the hall of shame: RSS Feed Post Generator Echo, paid plugin used to make ghost websites with ads and make money. If such plugin is activated, Lumière will automatically be deactivated.
* [bug] Under unusual circomstances, class-taxonomy-people-standard.php was failing to retrieve an IMDbPHP class name. Added throwing an exception
* [bug] Tagline in movies popup shows up again
* [bug] Gutenberg's movie type select text in Lumière movie's block was not shown, fixed block/index.js
* [bug] Taxonomy pages did not work for people's names with accented letters (added sanitize_title())
* [bug] Wrong annotation for class-settings.php (extra OPTIONS_WIDGET properties)
* [bug] IMDbPHP search was not working for people on some sporadic circumstances
* [bug] Creating Cache folder (method settings->lumiere_create_cache() ) was not entirely functional. New logic implemented in the method, should take care of any new cache path provided, default path case when installing the plugin, and alternative cache paths (aka inside Lumière plugin folder)
* [bug] If cache was not utilised, IMDB pictures were tried to be retrieved anyway (IMDbPHP needs a folder to store and display pictures). Edited frontend classes (movie, popup-movie and popup-person) and theme taxonomy-people-standard
* [bug] Uninstall did not remove the cache path as saved in database, but Lumiere\Settings::LUMIERE_CACHE_OPTIONS instead
* [bug] Utils::lumiere_notice() warning (case 4) was buggy. Fixed and implemented dismissible notices.
* [technical] Properly using composer scripts in src, not copied to the production vendor folder with composer anymore
* [technical] Using composer autoload, removed all home-made autoloads. Can't use PSR-4 autoloading, since WordPress file naming is not PSR-4 compliant.
* [technical] Bootstrap Window Maker is now relying on composer for updating
* [technical] Unloading various stylesheets in AMP link makers, as in AMP pages those are not found.
* [technical] Removed cache option 'imdbstorecache' (was hidden and not available in cache options); using now cache option 'usecache' in class-imdbphp to define it.
* [technical] Updated to bootstrap 5.2.2
* [technical] Added a link to settings in WP plugins page interface
* [technical] Updated to IMDbPHP library 7.3.1. Fixes major bugs such as the movie search

v.3.8.3
* [bug] An extra space was added for movie links inside posts. Fixed function lumiere_popup_film_link_abstract() in class abstract link maker
* [technical] Updated to Monolog 2.8
* [technical] Added 'rel=nofollow' into links built by popups in order to avoid search engines crawls (a Gb of cache could be subsequently created, and counting)

v.3.8.2
* [feature] Removed the ugly and useless column in taxonomy results only showing a thumbnail
* [bug] Lumière Bootstrap CSS was preventing OCEANWP theme to display its menu (Removed class .dropdown-menu in CSS bootstrap.css which breaks OCEANWP, also modified gulpfile.js for building)
* [technical] Updated to Monolog 2.7
* [technical] Removed lumiere_filter_single_movies() function which doesn't seem usefull anymore in movie class

v.3.8.1
* [bug] Movies inside a post with many words in their titles did not trigger modal window

v.3.8
* [feature] Implemented a new link maker: Bootstrap 5.1 modal window. Can be selected in admin options.
* [bug] Metaboxes in howto admin not displayed under certain context
* [bug] class-logger didn't create debug file if not existing
* [bug] IMDbPHP fix monthNo() and keywords were not displayed
* [bug] Popups do not throw a 404 header anymore (as they use virtual pages class)
* [technical] PHP8.0 is required due to new coding style, as PHP7.4 will obsolete soon
* [technical] Polylang is now a Plugin class with separated functions
* [technical] More OOP oriented, decoupled the link making process, NoLinks, Bootstrap, Highslide, Classic
* [technical] Virtual pages are now created with the new Virtual_Page class
* [technical] Updated to IMDbPHP library 7.3 and Monolog 2.6

v.3.7
* [feature] AMP Wordpress plugin compatibility greatly improved but still room for improvement remains
* [bug] phpstan in class-data.php: -Call to function is_array() with array&lt;int, mixed> will always evaluate to true-, removed the if/then
* [bug] PHP 8 compatibility improved (class-core.php line 714 array key film wasn't declared)
* [bug] Fixed image not aligned in movie block in WP block editor
* [technical] Updated to IMDbPHP library 7.3 and Monolog 2.5
* [technical] Renamed files in blocks folder according to WP standards (using folders and index.js files)
* [technical] Added plugins class, grouping all WP plugins compatible with Lumière
* [technical] Removed user comment option (class-settings, class-data) -> in "updates/09.php"
* [technical] Rewrote update class to call automatically child classes.

v.3.6.6
* [bug] If new taxonomy template available, can't overwrite the old template.
* [technical] Updated to IMDbPHP library (still 7.2, but with bug fixes) and Monolog 2.3.5

v.3.6.5
* [bug] Short biography text could be cut in the middle of a word. Edited lumiere_medaillon_bio() in trait-frontend.php to cut after the first space found.
* [bug] Two scrollbars were displayed in both single pages and popups. Fixed style registration 'lumiere_gutenberg_main' in class core.
* [bug] Fixed rating image alignement in movies, added 'vertical-align:baseline' in lumiere.css

v.3.6.4
* [feature] Improved explaination about how to keep CSS during updates.
* [technical] Few glitches to achieve PHP8.0 compatibility.
* [technical] Improved security: Escaped functions in admin.
* [technical] Improved security: Escaped functions in frontend.
* [technical] Almost 100% of WordPress standards achieved. What remains to fully comply with WP coding standards is either not of great relevance or due to a few errors in WordPress functions/methods/classes PHPDoc documentation.
* [technical] Almost 100% of compliance with PHPStan reporting.
* [technical] Delete lumiere_is_multi_array_empty() function in class utils, was used in class movie in lumiere_movies_plot() only and could be replaced by count()
* [bug] Reset options in cache, general and data admin pages was throwing a warning. Improved check for arrays in class settings get_imdb_cache_option(), get_imdb_admin_option(), get_imdb_widget_option() functions.
* [bug] Internal links in popups for trivias and goofs sections were not working. Using now lumiere_imdburl_to_internalurl() from trait-frontend.php in class-popup-movie.php
* [bug] Biography length was still incorrectly counting the first html tag and led to the text being cut early. Fixed the condition for $esc_html_breaker in lumiere_medaillon_bio() in class-frontend.php
* [bug] Changelog was not correctly displayed in help admin. Modified the regexes.
* [bug] Widget title when using the old widget (WordPress pre-5.8) was not correctly displayed.
* [bug] Soundtrack in popup movie was taking an extra breakline. Fixed the layout.
* [bug] Plural words in French such as Creator, Composer, Actor were not translated. Translated in language/.po file
* [bug] Rating and source pictures were not middle aligned depending on the theme. Added fix 'display:inline' in lumiere.css
* [bug] Widget WP Notice: wp_enqueue_script() was called incorrectly. "wp-editor" script should not be enqueued together with the new widgets editor (wp-edit-widgets or wp-customize-widgets). Changed "wp-editor" to "wp-block-editor" in block registrations.
* [bug] Widget WP Notice: "P_Block_Type_Registry::register" was called incorrectly. Block type "lumiere/widget" is already registered. Added extra condition (if not registered) to widget registration.

v.3.6.3
* [technical] For new installs, the new URL for popups is '/lumiere/' instead of '/imdblt/'
* [technical] For new installs, display poster thumbnails in widget/inside a post/taxonomy/popups instead of default size by default. Set setting 'imdbcoversize' to '1' in class-settings.php.
* [bug] Function to detect of whether a Lumiere widget is active was broken. Fixed lumiere_block_widget_isactive() in class-utils.php
* [bug] Biography length was not correctly counted and led to text being cut early. Added new condition for $esc_html_breaker in lumiere_medaillon_bio() in class-frontend.php
* [bug] Picture link url in taxonomy page for people was not built. Fixed class-taxonomy-people-standard.php
* [bug] Tagline's, Quote's commas were not display accurately and the last tagline displayed was taking wrongfully a comma. Plot's breaking lines &lt;hr&gt; were not displayed after the second result. Fixed the methods in class movie.

v.3.6.2
* [bug] On some configurations, the creations of cache folder could lead to a fatal error. Switched from WordPress builtin $wp_system functions to PHP core functions in method lumiere_create_cache() in class-settings.php.

v.3.6.1
* [bug] If no Lumiere widget was installed, a fatal error was thrown. Temporary fix for lumiere_block_widget_isactive() in class-utils.php

v.3.6
* [major] Massive cleaning of the code, plugin rewritten for perfomance and maintainability. Code is linted using PHPCS, PHPMD and PHPStan. Faster, more secure and more stable plugin.
* [feature] Uninstall process properly implemented. Lumière doesn't rely on WordPress deactivation function anymore for removing its options, taxonomy and cache. Properly deletes taxonomy in database.
* [bug] Debug/log functions, when activated, prevented from saving posts in block editor. Debugging and logging rewritten, checking the current page before showing log/debug on screen.
* [bug] Tags, should they exist, were not displayed in template taxonomy people.
* [bug] Several functions in popup movie did not return an internal link to another popup, but a unlinked text (sanitized text) instead.
* [bug] In actors summary in person popup, the role could link to a useless imdb link. Rewritten the function, no link at all is now displayed.
* [bug] Display of the soundtracks in articles/widgets did not show the soundtrack title
* [bug] Biography in articles/widgets and popups was truncated. Regex rule was broken.
* [bug] Saving Popup width and height options in admin was broken
* [bug] Thumbnail option was not modifying the poster sizes. Now it does in the articles, widget and popups. In admin, ticking the option was providing the width selection, while it was the opposite behaviour that what expected.
* [bug] Data details: Runtime, user comment and source were missing in admin and thus could not be selected.
* [bug] Updates were not running upon updates (only upon activation)
* [bug] Cronjobs were not throwing debugging text. Added DOING_CRON as new condition in settings class lumiere_maybe_log() & utils class lumiere_activate_debug()
* [bug] Various bugs in people taxonomy template, polylang integration was not working as expected. New template version 3.0 released.
* [bug] Taxonomy template item was buggy. Rewritten as a class.
* [bug] Removed imdbtaxonomytitle, a taxonomy option that wasn't used anymore. Titles can't be taxonomised, no need for such an option.
* [bug] Stylesheets and javascripts were missing a "min" suffix in their names.
* [bug] Stylesheets and javascripts were not loaded in admin pages for new installs. Changed conditions in Core class function lumiere_execute_admin_assets()
* [bug] Number of updates not correctly initialised on new install. Fixed the function in class Settings.
* [bug] Popup movie error in displaying data when utilised as a searching popup. Removed imdbsearchdirect condition, cleaned the class, fixed html.
* [bug] flush_rewrite_rules() when adding a new taxonomy page was buggy. Now it is triggered both when saving and and visiting taxonomy options page.
* [bug] Internal popups links for composer were not created. Fixed regex in lumiere_convert_txtwithhtml_into_popup_people() in class movie. 
* [bug] External links in plots were (sometimes) not created. Removed escaping plot in lumiere_movies_plot()
* [bug] Two iterations were made in the widget class per widget movie. Removed the useless loop.
* [technical] Removed imdbsearchdirect, blog_adress option, imdbwidgetonpost, imdbimgdir, imdb_utf8recode, imdbwebsite, imdbwidgetonpage in settings class.
* [technical] New way to deal with debug logging; class/function origin of the log fully implemented
* [technical] Taxonomy for director is set on active by default.
* [technical] Using PHP traits and classes for maintainability and readability purposes.
* [technical] Using only checkboxes in admin.
* [technical] All Lumière scripts are loaded in footer for the sake of performances.
* [technical] Taxonomy template copying in admin now detects if a new template version has been released.

v.3.5.1
* [feature] If new block-based widget is found, do not load pre-5.8 widget.
* [bug] Fatal error upon installation. "Vendor" folder wasn't included. Changed in lumiere-movies.php management of dependencies.

v.3.5
* [feature] In visual editing (with tinymce, old way), new function to add popup, movies by id and by title.
* [feature] Not using shortcodes [imdbltid] and [imdblt] anymore. Replaced by /span data-lum_movie_maker="movie_id"/ and /span data-lum_movie_maker="movie_title"/. This way, if Lumière is uninstalled no garbage is left in posts. Kept the old shortcodes working for compatibility purpose.
* [feature] link to popups are now dealt by a &lt;span data-lum_link_maker="popup"&gt; instead of &lt;span class="lumiere_link_maker"&gt; for consistency.
* [feature] Help section has been updated according to the many changes of the last months and vastly improved
* [technical] Renamed various options in order to mainstream functions and classes. Admin is now only made of classes.
* [bug] activation triggers notice error. Deactivated onscreen debug in class.core lumiere_create_cache() so doesn't throw an error anymore.
* [bug] delete all cache wasn't working (in class utils function lumiere_unlinkRecursive() was missing a "$this->")
* [bug] Logger in lumiere_noresults_text() class.utils wasn't being activated when called.
* [bug] Block widget type wasn't taken into account when checking if a widget was active in admin panel. Added new condition in admin_pages.php
* [bug] Can't open/close metaboxes in post edition. Metabox script added in lumiere_scripts_admin.js was colliding with other WordPress scripts. Removed from lumiere_scripts_admin.js, added to help.php. Took advantage of removing useless 'common','wp-lists', 'postbox' javascripts from loading in every admin page; now loads only in help.php

v.3.4.5
* [feature] New widget written as Gutenberg block, legacy widget works. Both are fully compatible with WordPress 5.8
* [feature] If a new taxonomy template is available, display a message in taxonomy admin panel. If template has not been copied, display a message too.
* [bug] Piece of widget log was displayed to visitors. Fixed in class.config function lumiere_maybe_log()
* [bug] javascript error in admin js: postbox not defined.
* [technical] Updated Monolog
* [technical] Replace admin url imdblt_options by lumiere_options

v.3.4.4
* [bug] Reporting of the cache size in admin panel 'Cache directory (absolute path)' was empty. Fixed layout of that section.
* [bug] Cache creation was called in loop in Lumière admin. Now it is called once on every Lumière admin page. Resource optimisation.
* [technical] Added logging to cache creation function.

v.3.4.3
* [feature] Major improvement for taxonomy pages people-related. Fully versatile if you are using Polylang, it detects it and display a option for allowing to switch the language. Displays all types of roles for people.
* [bug] HTML Links are properly linking to plot authors (class movie).
* [bug] Failing to build html links for taxonomy pages for people with accentuated names. The accents were kept in URL.
* [bug] Same movie could be called several times, which lead to many useless iterations. Got rid of the globals in classes movies and widget. Optimisation of the resources.
* [feature] Dropped support for Ozh admin menu plugin. The plugin is not supported anymore.
* [technical] Use of vars and constants in class config, changed php calls accordingly.
* [technical] Use of Monolog logger implemented in all Lumière classes and pages. Debug can now be saved to file. Everything can be set up in Lumière admin interface.
* [technical] Removed the option to change the URL of the blog. Option inherited from the times where WordPress was very different. Useless now.
* [technical] Using checkboxes instead of radio buttons in General Options
* [technical] Added verbosity of debugging (info, debug, etc)
* [technical] Updated to [IMDbPHP library 7.2](https://github.com/tboothman/imdbphp/releases/tag/v7.2.0 "IMDbPHP library 7.2")

v.3.4.2
* [feature] Added a number limit for producers and akas
* [bug] Taxonomy (english term+additional language related post) were added on the display of a post.
* [technical] Use WP cron to update options. Uses less resources, better optimisation.
* [technical] Class config used to include two classes. Now they're merged. All calls to class config trigger an automatic sending of the settings to IMDbPHP. Debug is now displayed everywhere.
* [technical] Class config has been cleaned. Constants have been included into the class. A lot of polishing is still needed.
* [technical] Implemented [Monolog](https://github.com/Seldaek/monolog/ "Monolo GIT") as new debug parser
* [technical] Updated to [IMDbPHP library 7.1](https://github.com/tboothman/imdbphp/releases/tag/v7.1.0 "IMDbPHP library 7.1")

v.3.4.1
* [bug] auto title widget was not working anymore due to recent changes in class.widget.php

v.3.4
* [info] Due to recent changes on IMDb website, the new IMDbPHP library has been rewritten. You must clear your cache folder to make sure the new pages are downloaded, as the olde pages are no longer supported. Just go to the admin panel, go to Lumière plugin settings, then 'cache management' panel -> 'manage cache', click on 'delete all cache', and you're done.
* [major] Updated to IMDbPHP 7.0 library. Meant to address the new IMDB website format.
* [feature] Added the option to search by movies, by tv shows, by both, and by videogames
* [feature] A bug (see technical _passing all Lumière options_ )was preventing cache functions from IMDbPHP libraries to be fully utilised. Speed greatly improved.
* [feature] Lumiere search gutenberg now searches also TV series. Increased number of max results to 15.
* [feature] Added compatibility with Polylang plugin for taxonomy. Created lumiere_add_taxo_lang_to_polylang() in class.movie.php that push a lang string into polylang.
* [technical] Added debug IMDbPHP to lumiere_debug function
* [technical] Passing all Lumiere options to IMDbPHP libraries through the $config. Language and cache_expire were missing in admin, popups and class.movie pages.
* [technical] Abandonned function lumiere_source_imdb() in functions.php, merged it in class.movie.php
* [technical] Reincluded debug function in all pages (admin+frontpage)
* [technical] Procedure to update options reviewed. created class.update-options, check how many updates have been fired, the version, and runs on any page. Debug text included.
* [bug] In admin cache, pictures (big+small) were not deleted when refreshing/deleting movies.
* [bug] Source imdb link was buggy due to intval(). Using the reliable php filter_var now.
* [bug] Layout with max-width:XX% removed from container classes in lumiere_admin.css
* [bug] Fixed longstanding bug with highslide opening two windows for pictures in class.movie. Created two js functions in lumiere_scripts.js dealing differently (useBox var) between with highslide_pic_popup and highslide_pic
* [bug] Gutenberg main block was getting corrupted when editing a post using shortcode imdbltid (the value was not properly defined in main-block.js)
* [bug] Creator highslide version within a post wasn't working

v.3.3.4
* [feature] Removed option to edit imdb-links to simplify general options. Removed these links in popup-search and popup-movies, quite useless.
* [feature] Moved 'data options - misc' to 'general options - advanced' for more userfriendliness.
* [feature] Simplified explanation of some admin options. Looking forward to a more userfriendly option settings panel.
* [technical] Updated popup image in admin general options.
* [technical] Not using htaccess generation anymore, all admin pages are redirected in the core class. Simplification of the code.
* [bug] Auto title widget was throwing fatal error, probably since transforming imdb-movies into movie.class. Fixed class.movie and class.widget.
* [bug] On some configurations, headers_sent() in admin panel was redirecting to general options when submiting reset/update form in options data. Deactivated it.

v.3.3.3
* [feature] Taxonomy pages related to people include personal details about the person, such as birth, picture, death, biographie. Templates are now separated between people and items.
* [technical] Set 'imdbwidgetsource' to false, so no more link to imdb for movies by default.
* [technical] Added check to ensure taxonomy category and term exist before adding them.
* [bug] No more info shown on update/reset of admin options. lumiere_notice() was changed to return from echo. Added echo to all calls of that function.
* [bug] Taxonomy terms were incorrectly taxonomized, rewrote the process of adding taxonomies (rewrote functions in class.movie)
* [bug] htaccess file wasn't created anymore. Fixed the checking writeable process (changed is_writable() by touch()). Merged lumiere_change_perms_inc() (class.core) with lumiere_make_htaccess() (functions). Fixed lumiere_make_htaccess() function to write the correct path in htaccess for move_template_taxonomy.php.
* [bug] Copying taxonomy template wasn't working anymore. Fixed path check.

v.3.3.2
* [feature] New design for movies popups
* [feature] New design for persons popups
* [feature] Movie popup is now dependent of the admin Data options (actors+number of actors, rating, language, runtime, director).
* [feature] Added "portrayed in", "interviews", "printed publicity" infos in person popup biography
* [feature] Data management main options alphabetically ordered
* [feature] Added full filmography in person popups
* [technical] Renamed widget/inside post options to data management in admin
* [technical] deactivated debug for popups
* [bug] Writer data in popup wasn't working (issue in class.movie.php)

v.3.3.1
* [feature] added a color theme to "widget" and "into the post" sections. Select option added in the "into the post/widget" administration panel (misc tab)
* [bug] In options-widget.php, reactivated 'source'. It was deactivated in widget order.
* [bug] Made sure only one call is made for every movie (imdblt, imdbltid, widget)
* [bug] Removed $imdbwidgetcommentsnumber from config and options from options-widget.php. The new IMDbPHP classes retrieve only one comment. Adapted also class.movie.php.
* [technical] Added title in wp-admin/lumiere/search/ popup
* [technical] Links popup builder and short codes [imdblt] [imdbltid] are not run in the admin anymore 
* [technical] Transformed imdb-movie.inc.php into a class. More versality moving forward.
* [technical] Minification of javascripts and stylesheets, images are even more compressed
* [technical] Removed useless title taxonomy function, title are nomore taxonomised
* [technical] added var imdbintotheposttheme in config for selection of into the post/widget themes
* [technical] options are updated/deleted/added for new versions

v.3.3
* [major] Added a metabox in the edition of the posts (admin area). The movie's title or IMDb ID will be utilized to show a widget with the relevant movie. Included a popup to search for IMDb IDs.
* [major] More robust taxonomy system. More technical and systematic coherence to understand the taxonomy options. Rewrote the taxonomy injection in imdb-movie.inc.php using wp_set_post_terms and only once per taxonomy. Faster, cleaner, and fully working.
* [feature] Added the option to keep the settings upon plugin deactivation.
* [feature] Removed taxonomy metaboxes from edit interface, it's useless
* [feature] Limiting the number of results in queries never worked. Now it does. Use the general options -> advanced 
* [feature] Enhanced popup layout, fancier and more adapted to the theme selected in admin panel
* [feature] Added "loader" css to loader ids (used in popup-search and popup-imdb_movie.php)
* [feature] Added nicer rating layout in the popups, copied from imdb-movie.inc.php
* [bug] Adding more than one movie into the post stopped the second and more movies to be displayed. Adding a movie into the post stopped the widget to be displayed. Replaced the calls to imdb-movie.inc.php with require_once() by require().
* [bug] Taxonomy rewrite rules does definitely work using lumiere_create_taxonomies() function in class/functions.php. Added a specific constraint to be called only in taxonomy pages since it calls the time consuming flush_rewrite_rules() function.
* [bug] Deactivation process (pseudo uninstall) now fully works. Deleted uninstall.php, moved function lumiere_unregister_taxonomy() into class.lumiere.php and renamed to lumiere_on_deactivation()
* [bug] No form for detail cache delete, cache details to delete couldn't be submitted in options-cache.php
* [bug] Values imdbwidgetofficialSites and imdbwidgetprodCompany prevented them from being saved in options-widget.php. Renamed them imdbwidgetofficialsites and imdbwidgetprodCompany so they can be saved.
* [bug] Various fixes for the layout in imdb-movie.inc.php
* [bug] Votes/rating were not working since mass sanitization. Change intval() by esc_html().
* [technical] Admin forms submit/reset are now sticky -> easier for the user to submit
* [technical] Removed options update in plugin update process, options are already automatically updates when visiting options
* [technical] Enhanced tinyMCE, but still work to do
* [technical] Moved class from lumiere-movies.php to class/class.lumiere.php
* [technical] Implemented a taxonomy slug URL. By changing the options in the admin panel, the URL for taxonomy pages can be changed.
* [technical] Taxonomy is now activated by default, allowing "genre" to be available. "genre" is also activated by default in "what to display" now.
* [technical] Widget is now a class compliant with WordPress coding standards.
* [technical] Simplified $imdballmeta arrays management. These globals passing from widget and core classes are now structured as $imdballmeta[]['byname'] and $imdballmeta[]['bymid'] and retrieved in imdb-movie.inc.php

v.3.2.2
* [feature] Implemented a selection for popup colors in admin menu 
* [feature] Changed AKA design in popups
* [bug] Popup-imdb_person: soundtrack film was redirecting to popup-imdb_person instead of popup-imdb_movie
* [bug] New fix for popup titles function in lumiere-movies.php
* [bug] Fixed link with extra "/" in URL for akas in popup-imdb_movie.php
* [technical] Remove options for photo path and directory in options-cache.php, now automatically generated
* [technical] Contrained options for "Plugin directory" and "URL for the popups" in options-general.php, now partially automatically generated
* [technical] Changed $imdbAdminOptions $imdbCacheOptions arrays in config.php, imdbplugindirectory, photo path, and directory are now relative
* [technical] Added a plugin update function to make htaccess file and update config on update
* [technical] Debug is now a function implemented in all relevant pages

v.3.2.1
* [bug] admin panel wasn't displayed, bug in inc/admin_pages.php
* [bug] popup titles function in lumiere-movies.php was preventing the popup from working

v.3.2
* [feature] Display person's name in popup's title page, generaly rewritten the tite's naming and links to popups
* [feature] added metas (favicons and canonical) link in popups
* [bug] Redirection when deleting/updating cache details was still broken, changed the method
* [bug] Fixed the reset buttons in admin pages, reset buttons will now display a "go back" message
* [bug] imdbwidgettrailernumber variable missing in class/config.php (probably since the inception of the plugin!)
* [bug] Fixed WordPress credits in admin were not shown (added two closing divs in inc/admin_pages.php)
* [technical] removed calls to options from class/config.php and created inc/admin_pages.php. Moved the $_POST checks from admin_pages.php to their dedicated sections in options-*.php.
* [technical] (re)Added debugging mode
* [technical] Added the option to delete query cache
* [technical] Added check defined( 'WPINC' ) on the top of included pages to avoid direct calls
* [technical] Added the isset() in functions for further compatibility with PHP8
* [technical] Change the custom url to call /imdblt/(film|person) instead of lumiere-moves/inc/popupup*
* [technical] The custom url /imdblt/(film|person) is now a constant
* [technical] Robust htaccess that takes into account all possiblities of the popups
* [technical] Made the custom url /imdblt/(film|person) an editable variable in admin/ moved the constants to the end of config.php
* [technical] Removed useless data in htacces making (let wordpress deal with it)

v.3.1.1
* [bug] Added into popups and imdb-movies.inc movie/people images the code 'loading="eager"' so they're not lazy-loaded (otherwise they're not displayed)
* [bug] Taxonomy was not registred and not appearing in Posts, fixed function lumiere_create_taxonomies()
* [bug] Redirection when deleting/updating cache details was broken
* [bug] Fixed cache refresh/delete redirect + move form functions to options cache
* [technical] CSS & JS automatically take LUMIERE_VERSION string in URL when they're called, so every new Lumière! version triggers a cache refresh
* [technical] Moved IMDbPHP classes from /src to /class/imdbphp, moved config.php, functions.php, into that same folder

v.3.1
* [Major] Due to compatibility reasons with Gutenberg, the way to call links for internal popupups has changed from '&lt;!--imdb--&gt; &lt;!--/imdb--&gt;' to 'span class="lumiere_link_maker"'. Compatibility with the old way currently maintained.
* [Major] Finished Gutenberg interface, two buttons added.
* [feature] Caching system: Changed the path of Lumière! cache (now in wp-content/cache) so it will survive plugin updates. If wp-content/cache is not writable, it uses wp-content/plugins/lumiere/cache path instead.
* [feature] Caching layout: Vastly simplified the cache menu. Moved the paths options to manage cache submenu (options are hidden by default), merged store and use cache (store is not available anymore), removed the zip options.
* [feature] General options layout: polished general options, moved many option to advanced tab, should be easier to understand
* [feature] Variable imdbcachedetails set to true by default (no real reason to not show the cache management) in options-cache.php and config.php
* [Bug] the lumiere's scripts weren't loaded outside the Lumiere's dedicated pages. Removed checks in lumiere_add_footer_blog()'s lumiere-movies.php, deactivated calls to head and footer construction for imbd-movies.inc.php in lumiere-movies.php and widget (head and footer are already loaded!)
* [Bug] removed CSP compliance in js/lumiere_scripts.js and js/highslide-options.js as it prevented a correct running of the highslide popups -> highslide should close windows under CSP
* [Bug] Removed adding automatically image in tinyMCE, I can't get rid of it when posting (imdbImg in lumiere_admin_tinymce_editor.js)
* [Bug] uninstall wasn't removing taxonomy terms. Simplified the function and now it works.
* [Bug] introduced in v3.0, couldn't display more than one film in widgets. Replaced "require_once( $imdb_admin_values['imdbpluginpath'] . 'inc/imdb-movie.inc.php');" with "require( plugin_dir_path( __FILE__ ) . 'imdb-movie.inc.php');" in widget.php
* [technical] Updated to IMDbPHP 6.5.1 library.

v.3.0.1
* [feature] Gutenberg Block: better layout, implemented selection of either [imdblt] or [imdbltid]
* [feature] Gutenberg Block: created a new page gutenberg-search.php for searching movies from gutenberg
* [feature] Translation: renamed all calls in __() and the likes from 'imdb' to 'lumiere-movies', changed the language system according to the (new) Wordpress language system
* [feature] Explanation: "Toolbar Lumière admin menu" and "Menu for Lumière options" where a mess in terms of explanation. Rewrote.
* [feature] Taxonomy standard template file was missing translation options
* [feature] Renamed the widget's name for the wordpress widget page
* [feature] Converted onclick=\"return hs.close(this)\" from highslide-options.js into a jQuery function
* [Bug] Issues of mobile responsiveness in options-cache.php
* [Bug] highslide popup unactive by default. "imdbpopup_highslide" set on true in config
* [Bug] banner wrong name
* [Bug] The new name for the widget hook register wasn't updated in config.php (the check failed in Lumière options)
* [Bug] chmod'ing process upon plugin activation wasn't taking in consideration if a cache folder already exists
* [Bug] loooongstanding bug for taxonomy links in imdb-movie.inc.php, rewrote how taxonomy links are called, created a function
* [Bug] update taxonomy activation/deactivation didn't refresh rewrite rules, refresh of taxonomy should now make the taxonomy links

v.3.0
* [major] rewritten the code to comply with WordPress security
* [major] plugin is compatible with tablets/mobile phones (fully responsive)
* [major] first block for Gutenberg (for [imdblt] movies included by name)
* [feature] popups URLs are rewritten to (blog)/imdlt/(film|person)
* [feature] updated to highslide 5.0
* [feature] moved all inline javascripts to external files so the plugin is Content Security Policy (CSP) compliant
* [feature] cache folder is created if it doesn't exist, highslide is download if it doesn't exist
* [feature] new icons, new layout
* [feature] removed Pilot search options
* [feature] removed js support for IE
* [feature] removed support for wordpress < 5.7
* [feature] added a tool to copy taxonomy templates in the admin interface
* [feature] added a css fix for OceanWP template users
* [feature] css/js are now loaded only on /imdblt/ and widget pages
* [feature] popup movie pages get their title according to the query string "film"
* [feature] css fix for Oceanwp templates
* [bug] fixed popup-imdb_person.php which showed both actress & actor
* [bug] fixed caching system
* [bug] fixed cache refresh for movies, only normal (not _big) pictures where retrieved
* [bug] fixed longstanding bug of widget ordering
* [bug] fixed broken [imdblt] calls into the post
* [bug] fixed layout for standardized taxonomy templates
* [bug] fixed Deprecated TinyMCE API call: "onPostProcess" (moved to new tinymce standards)
* [bug] removed the use of movie_actress() in popup-imdb_person.php
* [bug] fixed bad English grammar, sentences, but much more work to do. Seems like I've improved my skills over a decade.
* [technical] updated to IMDbPHP-6.5 library
* [misc] renamed the plugin to Lumiere Movies, renamed all classes and functions accordingly
* [misc] Under the hood, more robust plugin following (a bit more) wordpress & PHP standards

v.2.3.2
* [feature] added Ukranian : thanks Michael Yunat
* [bug] fixed longstanding bug: shortcodes when editing a wordpress post are back
* [bug] fixed longstanding bug: bios are back
* [technical] updated to IMDbPHP-2.3.2 library

v.2.2.3
* [technical] updated to IMDbPHP-2.2.3 library

v.2.2.2
* [feature] added production companies
* [technical] updated to IMDbPHP-2.2.2 library

v.2.2.1
* [major] updated to IMDbPHP-2.2.1 library
* [feature] added Croatian : thanks Borisa Djuraskovic!
* [bug] fixed newly added "keywords" bug in the taxonomy

v.2.2
* [feature] added "keywords" option which allows to diplay the movie keywords. Taxonomy included.
* [feature] "Display only thumbnail" option now affects popup picture width in the same way it affects widgets picture width
* [bug] deleted "ob_flush(); flush();" in inc/popup-header.php as it was preventing the CSS and JS to be used
* [bug] removed "movie connection" option in widget; this was a long non-working useless option
* [technical] updated to IMDbPHP-2.2 library

v.2.1.9
* [technical] updated to IMDbPHP-2.1.9 library

v.2.1.8
* [technical] updated to IMDbPHP-2.1.8 library

v.2.1.7
* [bug] soundtracks fixed
* [bug] movie connection is broken, unactivated
* [technical] updated to IMDbPHP-2.1.7 library

v.2.1.6
* [technical] updated to IMDbPHP-2.1.6 library

v.2.1.5
* [technical] updated to IMDbPHP-2.1.5 library

v.2.1.4
* [technical] updated to IMDbPHP-2.1.4 library
* [feature] changed obsolete __ngettext() method to _n() (__ngettext obsolete since wordpress 2.8)
* [feature] removed moviepilot options, but who was still using that?

v.2.1.3
* [major] translated into Romanian, thanks to [Web Geek Science](https://webhostinggeeks.com "Web Hosting Geeks")
* [major] changed the way to use highslide js (on Wordpress request, piece of code not GPL compliant); it is mandatory now to download the library from [IMDBLt website](https://www.jcvignoli.com/blog/en/imdb-link-transformer "IMDbLT website") in order to get this damn cool window. Once the file downloaded, move the folder "highslide" into the "js" one and check general options in order to activate it
* [technical] updated to IMDbPHP-2.1.3 library

v.2.1.2
* [technical] updated to IMDbPHP-2.1.2 library

v.2.1.1
* [feature] new cache option to display cache elements in a shorter way (no picture, only names) -> "Quick advanced cache details" option
* [feature] added IMDB LT to the new wordpress toolbar menu
* [technical] updated to IMDbPHP-2.1.1 library

v.2.1
* [technical] updated to IMDbPHP-2.1.0 library

v.2.0.8
* [major] huge speed improvement changing in inc/functions.php is_multiArrayEmpty() from [PHP empty function 1](https://in2.php.net/manual/fr/function.empty.php#92308 "PHP empty function comments") to [PHP empty function 2](https://in2.php.net/manual/fr/function.empty.php#94786 "PHP empty function comments") -> credits to fha
* [feature] cache options are divided between "Cache general options" and "Manage Cache" pages. Much more advanced way to manage cached movies. Movie's and People's cache management.
* [feature] when the widget displayed more than one movie the widget's title took the title of the previous movie's name
* [feature] still some "Fatal error: Cannot redeclare plugin_action() (previously declared in /xxxxxx/wp-content/plugins/wp-to-twitter/wp-to-twitter.php on line 1064) in /xxxxxx/wp-content/plugins/imdb-link-transformer/inc/widget.php:104" -> updated the way to register the plugin, using wp_register_sidebar_widget instead of register_sidebar_widget and removing the plugin_action() function (all obsoletes)
* [feature] admin menu added few icons
* [bug] fixed displaying " as " text even when no role was existing in popup.php
* [bug] fixed typo missing closing ">" line 142 inc/help.php

v.2.0.7
* [technical] updated to IMDbPHP-2.0.7 library
* [feature] updated to highslide js 4.1.12

v.2.0.6
* [technical] updated to IMDbPHP-2.0.6 library

v.2.0.5
* [feature] widget only finds the movie if the tt number on IMDB is followed by a full seven digits; modified "$moviespecificid = $value;" by "$moviespecificid  = str_pad($value, 7, "0", STR_PAD_LEFT);" into widget.php 
* [technical] updated to IMDbPHP-2.0.5 library

v.2.0.4
* [feature] it is now possible to resize thumbnail even if "Display only thumbnail" is turned on yes (found under "general options" -> "Paths & Layout" -> "layout"->"Imdb cover picture"). "Size" is not anymore unactivated (options-general.php) and value filled in is taken into account (imdb-movie.inc.php)
* [feature] french strings corrected
* [technical] updated to IMDbPHP-2.0.4 library - !! No more "IMDb" word added at the end of popup movie's title - Pictures from IMDb are back !

v.2.0.3
* [feature] added Bulgarian : thanks Peter!
* [feature] added a function to construct highslide and classical links(imdb_popup_link() & imdb_popup_highslide_link() in inc/functions.php, called from imdb-link-transformer.php)
* [feature] added title as taxonomy
* [bug] Many bugs have been corrected. Among them, pictures should be displayed even if using Firefox, country as movie detail is back.
* [bug] Two taxonomy help pictures were forgotten! 
* [bug] Layout broken when using moviepilot (writer & producer sections, $role parts)
* [technical] updated to IMDbPHP-2.0.2 library

v.2.0.2
* [feature] Completely revisted as taxonomy works; one can select many movie's details as taxonomy. New admin options related to.
* [feature] Trailers added as a new movie detail.
* [feature] Year added as a new movie detail. The movie's release year will always take place next the title, in brackets.
* [feature] French strings corrected. Taxonomy help improved.

v.2.0.1
* [major] added possibility (by an option in advanced general settings) to add movie's genre tags directly into post tags (actually, into taxonomy wordpress function). When activated, it display new links instead previous genre tags.
* [feature] added colors when option is selected in "Widget/Inside post Options".
* [feature] uninstall function wasn't activated. Now, uninstalling IMDb LT plugin should remove every single option added by its own.
* [feature] updated to highslide js 4.1.9
* [feature] English/French corrections, misc improvements, help enhanced
* [bug] when using lumiere_external_call() function with an imdbid from external (not with a movie's name), it didn't work. inc/imdb-link-transformer.php corrected

v.2.0
* [feature] better multilanguage support -> echo(sprintf(__ngettext())) instead of _e()
* [feature] as required by moviepilot's team, added website source at the post's end. IMDBLT will display the source(s) where data come from
* [feature] added a few strings forgotten into the translated strings
* [feature] removed imdb-link-transformer.php.noclass (obsolete file)
* a bunch of improvements
* [bug] A bug was preventing for display right pictures cache size (in admin/cache section)
* [bug] access level for Pilot website wasn't working anymore (switching to any value hadn't any effect except staying with "NO_ACCESS")
* [technical] updated to IMDbPHP-2.0.1 library

v.1.6
* [major] it is now possible to get rid of IMDb datas! New option added: IMDb general options -> Advanced -> Moviepilot part. Implementation for this is brand new, so don't expect yet too much anyway! -> PHP < 5 is no more supported.
* [major] as a consequence of Moviepilot's new source, it is now possible to completely switch informations to other languages (much more effective than IMDb way). Currently in use German (the biggest source for Moviepilot), French, English, Spanish and Polish.
* [major] new way to include movies inside a post, using their imdb movie's id instead of their names. Use tags [imdbltid]movie's imdb id[/imdbltid] instead of [imdblt]movie's name[/imdblt]. Idea comes from Mattias Fr&ouml;berg.
* [major] possibility to use in widget several imdb-movie-widget (or imdb-movie-widget-bymid) is back again
* [feature] lumiere_external_call() function could be called again as previously. Added a second parameter ($external) to the function, so calling can be discriminated into the function. Check help for more explanation.
* [feature] added a new widget option, to automatically add a widget according to post title. Turn on "Widget/Inside post Options -> Misc ->Auto widget" option. Especially useful to blogs focused only on movies, where all posts are related to cinema.
* [feature] cache functionality added to searches -> a website as imdb or pilot will be less called, imdblt storing its results
* [feature] css corrections
* [feature] added "creator" detail, aiming to show creators' series
* [feature] updated to highslide js 1.4.8
* [feature] new functions added to functions.php
* [feature] added "Filmography as soundtrack" in popup for people (popup-imdb_person.php)
* [feature] splitted "General options" from wordpress admin into two subsections : "Paths & Layout" and "Advanced". Advanced subsection is meant to include the... advanced options. General option is much more readable, now.
* [feature] cache size is now computed and displayed in admin/cache section 
* [feature] turned imbd-link-transformer into a class (class imdblt{}); modified inc/popup-imdb_movie.php and inc/help.php according the new structure. As a result of this, one has to open the class (ie $imdblt = new imdblt) before calling the function itself (ie $imdblt->lumiere_external_call('Descent', 'external'))
* [feature] by default, cover pictures are not displayed as thumbnail, with a size of 100 px. To change this behaviour: "General options -> Imdb cover picture -> Display only thumbnail"
* [feature] removed imdb adress us.imdb.com, added www.imdb.fr
* [feature] moved and renamed tinymce javascript from tinymce/editor_plugin.js to js/tinymce_editor_plugin.js
* [bug] + [feature] User's comment wasn't working anymore. Rewritten, and also added the option to choose how many comments to display.
* [bug] movieconnection(), releaseInfo(), born() and died(), color(), sound(), mpaa(), mpaa_hist() fixed
* [technical] updated to IMDbPHP-1.9.10 temporary library

v.1.5
* [feature] many broken searches should work again
* [feature] added css update explanations into inc/help.php
* [technical] updated to IMDbPHP-1.9.8 library

v.1.2.2
* [technical] updated to IMDbPHP-1.2.2 library

v.1.2.1
* [feature] It is now possible to keep your customized imdb.css file; just put your imdb.css into your theme folder.
* [feature] added new titles to "inc/options-cache.php"
* [feature] added spanish (thanks to Andrés Cabrera)

v.1.2
* [feature] added new cache option (Show avanced cache details) which permits (or not) to show movie's cache details (and allows to delete cache for every movie). Unactivated by default, it prevents "Fatal error: Maximum execution time of 30 seconds exceeded" errors.
* [feature] modified imdb.css (improvement made by Jeremie Boniface, I'm not able to manage these things)
* [feature] rating (inside a post & widget) displays now pictures instead of numbers; depending of good (or bad) feeedbacks, value $imdbwidgetratingnopics has to be implemented into plugin (to permit users to get back to old display, with the numbers) -> Jeremie Boniface demand
* [feature] more french strings corrected
* [feature] brazilian updated
* [feature] only 1 user comment could be retrieved! changed plugin options according to this limitation (removed $imdbwidgetcommentsnumber)
* [feature] renamed "cache.php" to "inc/options-cache.php"
* [feature] removed robots.txt, since v. 1.1.15 workaround is efficient
* [technical] updated to IMDbPHP-1.2 library

v.1.1.15
(if specified), person: 3/fixed a property missing in initialization, Fixed goofs(), trailers(), and trivia() )
* [major] every admin settings section (cache, widget & general) has its own preferences storage (new foreign keys: $imdb_admin_values, $imdb_widget_values,$imdb_cache_values, whereas $imdb_config_values is dead). As a result of this, resetting General options won't interfere with Cache options, and vice versa.
* [feature] changed the way several css directives related to popup were working (ie, all &lt;a&gt; tags in owner's blog were impacted, instead of only &lt;a&gt; from the popup)
* [feature] added compatibility with [link-indication plugin](http://wordpress.org/extend/plugins/link-indication/ "link-indication plugin home") (modified imdb-link-transformer.php add_filter's execution priority to 11)
* [feature] mixing "imdb-movie-widget-bymid" and "imdb-movie-widget" keys actually does work! Removing references saying it doesn't.
* [feature] cache movie files into cache.php are now alphabetically ordered
* [feature] soundtrack & quotes include an internal popup link instead of a link to imdb website 
* [feature] moved imdb-widget.php to /inc/, and renamed to widget.php
* [feature] added trademarks fields into popup (misc tab) & biographical movies (bio tab)
* [feature] added plot limit selection (imdblt settings : Widget/Inside post Options  / What to display )
* [feature] imdb pictures displayed both in widget and inside a post can be "highslided". To achieve this, activate "Display highslide popup" option and turn "Display only thumbnail" to "no" (and put a small size value, as "150" per example).
* [bug] (somewhat): if highslide popup is active, search engine bots will index several hundred thousands of movies and actors, since they can now follow an href. Except if you do not care at all about space and bandwith, it could down your website quickly. Moved links to highslide popups from href="" to src: '' (inside javascript) and closed the href tag. To prevent any kind of search engine crawling in a place where it should not crawl, added also a robots.txt file (not convinced about its usefulness, though), and a test at the begining of popup-header.php.
* [bug] some translated french strings were wrong
* [bug] when several directors were found, it was missing space or comma to display -> added comma
* [technical] updated to IMDbPHP-1.1.15 library (1/hopefully fixed "invalid stream resource" on some network timeouts, movie: 2/ added method to retrieve movie types 

v.1.1.14.4
* [major] added help page in settings: how to ( including movable boxes to keep useful information), changelog, faqs pages
* [major] added security constraints (mainly data post check)
* [major] added possiblity to arrange disposition of movie data order (available on Widget/Inside post Options)
* [major] added many many new movie's details: goofs, comments, quotes, taglines, colors, also known as,composer, soundtrackmovieconnection, officialSites
* [major] completely new cache management system; cache has its own page, and can be deleted now from this very options page (and cache for a pelicular movie can be deleted)
* [major] new kind of popup, Highslide JS. Much nicer popup, must be activated from "General options / Layout / Popup / Display highslide popup" -> change to yes to see the difference whenever you click a link opening a popup (from inside post or widget)
* [feature] added javascript to unactivate fields which shouldn't be available because of logical constraints
* [feature] added uninstall page (uninstall.php)
* [feature] added many new classes to manage in a more precise way elements in Widget/Inside post. Check imdb.css file.
* [feature] added an option to keep cache files forever (useful if one doesn't want to rely on IMDb)
* [feature] added direct access to this changelog (in admin settings)
* [feature] added direct access to FAQs (regexp from readme)
* [feature] added index.html (empty file)
* [feature] added icon compatibility with [Admin Drop Down Menu plugin](http://planetozh.com/blog/my-projects/wordpress-admin-menu-drop-down-css "Admin Drop Down Menu plugin home") -> added ozh_imdblt_icon() (&filter ad hoc) in imdb-link-transformer.php
* [feature] added new admin menu which can be activated via "General options / Advanced / Display plugin in big menu" -> change to yes to see the difference
* [feature] changed the way to submit preferences(config.php): now, after saving/resetting preferences, one stay on the very same page, instead going to a new (and click on "back" link). Also reset is working as it has to.
* [feature] added standard wordpress picture to plugins (top left admin settings)
* [feature] changed pages admin names -> imdblt_options now (can be seen in adress bar)
* [feature] removed old screenshots, added new ones
* [feature] consequently to many changes and functions added, plugin shouldn't work at 100% with a wordpress release prior to 2.7. However, no crucial function has been added, and even with old releases IMDb link transformer should mostly work as it used to.
* [bug] in config-widget.php, a value for imdbwidgettitle (and one other) section was wrong
* [bug] a value $test (imdb-movie.inc.php) was wrongly named -> renamed to $genre
* [bug] wrong value for "Plugin directory" (config.php) -> modified

v.1.1.14.3
* [feature] new way to include movies inside a post: use tags (new way) [imdblt]movies name[/imdblt] instead of (old way) &lt;?php lumiere_external_call() ?> + Exec-PHP plugin. That means this release doesn't need any more any kind of third party plugin! Compatibility with the old way kept.
* [feature] changed the way options from admin settings appear; depending of the current selections, some options will appear, some disappear
* [feature] added "Remove popup links" option; user can choose to remove popup links from widget -> no more popup
* [feature] added imdb photo size width option (only displayed if thumbnail option deactivated)
* [feature] added more pics in admin settings menu
* [feature] removed call_imdb_movie function from imdb-link-transformer.php (wasn't used)
* [feature] added more screenshots
* [bugs] fixed many comments in French, typos, options badly explained, an so on...

v.1.1.14.2
* [feature] added brazilian language

v.1.1.14.1
* [feature] added imdb photo size option; user can choose to display the big cover picture instead of the thumbnails'
* [bugs] corrected writers section from imdb-movie.inc.php (before that, some &lt;br /&gt; where added before names, which moved forward text). Was only an issue when displaying imdb inside a post (via imd_external_call() and Exec-PHP plugin)
* moved - and renamed - imdb_person.php and imdb_movie.php, and moved popup.php to a more suitable place, "inc/" folder.

v.1.1.14
* [feature] few french lines (not translated) moved to po files
* [feature] defined 'IMDBPHP_CONFIG' value in various files, so future update of izzy's libs could be automatically made, instead of changing config lines from imdb_base.class.php
* [feature] French comments in php's translated to English
* [technical] updated to IMDbPHP-1.1.14 library

v.1.1.13.5
* [feature] added the possiblity to directly enter the movie id through "imdb-movie-widget-bymid" post meta data 

v.1.1.13
* [bug] typo in inc/config-widget.php
* [technical] updated to IMDbPHP-1.1.13 library

v.1.0.1
* [bug] imdb.css, h1 section was preventing of working original h1 

v.1.0
* [feature] actress filmography working at once (added "actress" in filmography part from imdb_person.php)
* [feature] various (very) minor enhancements

v.0.9.5
* [feature] added field plot as an available option for widget
* [feature] update admin css to match wordpress 2.7 css
* [bug] rating function working again
* [technical] updated to IMDbPHP-1.1.1 library

v.0.9.3
* [feature] added multiple queries widget process (several "imdb-movie-widget" field can be added to a post). Caution! Adding several fields will slow down page display.
* [feature] removed the so-said pass of "$filmid = $_GET['imdbpostid']" from imdb-widget to imdb-movie.inc.php (seems to be passed because functional call)
* [feature] added a function lumiere_external_call () to permit to call the widget from anywhere into templates (widget no more mandatory)
* [feature] changed to lumiere_htmlize() function (htmlentities() unactivated, as it doesn't seems anymore needed and prevented the accentuated letters to go through the search process)

v.0.9.2
* [feature] style issue (body class wasn't defined to be only a popup class, therefore general blog theme could be affected)
* [feature] work on w3c validation (it should be valid, now)

v.0.9.1
* [bug] bug giving an error when no meta custom value added to a post

v.0.9
* [feature] added widget. Using both widget & direct search possibilities, now everybody can display information related to a post directy into widget fields!
* [feature] most of the functions have been implemented. It still lacks the "actress" class, which means that it doesn't yet display the data related to an actress. Next release will be out when such a class will be finished.
* [bug] preventing imdb button (no tinymce) to be displayed
* [bug] usual layout&code fixes

v.0.8
* [feature] get rid of the PEAR option, it isn't useful
* [feature] added an option to remove imdb links (when doing a search)
* [feature] added an option to bypass the search stage for the popup. Activated by default.
* [feature] changed the way of browsing in popup window ("AKA search" instead of "back", keeping movie's name in $_GET, layout)
* [feature] Automatic check for the cache folders (under admin board options)
* [bug] Issue with some [searchs not giving all results for a person](https://projects.izzysoft.de/trac/imdbphp/ticket/46 "IMDbPHP website")
* [technical] updated to IMDbPHP-1.0.7 class 

v.0.7.1
* [bugs] relative path & imdb-link-transformer.php with blank spaces resolved

v.0.7
* [feature] added admin page
* [feature] added "wait on load" message when first search (when results are not yet cached, it take some time to get them)

v.0.6.1
* [bugs] minors fixes (layout & code)

v.0.6
* [feature] changed the way the tags are used; &lt;!-- imdb --&gt;nameMovie&lt;!--/imdb--&gt; instead of &lt;imdb&gt;nameMovie&lt;/imdb&gt;. This means the already tagged fields should be changed to these new tags. As a result of this, IMDb link transformer is only compatible with Wordpress > 2.5!
* [feature] support for tinymce editor (imdb tags can be automatically inserted by a new button)
* [bugs] some minors fixes (layout & code)
	
v.0.5
* [feature] director page created (filmo, bio & misc)
* [feature] one title, one result
* [feature] every url links to an internal page (except the imdb logos)
* [feature] localisation files added
* [feature] one config file
* [feature] huge layout and stylesheet work
* [feature] added many functions
* [technical] updated to IMDbPHP-1.0.6 libraries

v.0.3
* [feature] initial public release of IMDb Link Transformer

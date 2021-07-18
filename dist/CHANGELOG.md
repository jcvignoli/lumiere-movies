**Changelog**

v.3.4.3
* [major] Updated to [imdbphp library 7.2](https://github.com/tboothman/imdbphp/releases/tag/v7.2.0 "imdbphp library 7.2")
* [feature] Major improvement for taxonomy pages people-related. Fully versatile if you are using Polylang, it detects it and display a option for allowing to switch the language. Displays all types of roles for people.
* [technical] Use of vars and constants in class config, changed php calls accordingly.
* [technical] Use of Monolog logger implemented in all Lumière classes and pages. Debug can now be saved to file. Everything can be set up in Lumière admin interface.
* [technical] Removed the option to change the URL of the blog. Option inherited from the times where WordPress was very different. Useless now.
* [technical] Using checkboxes instead of radio buttons in General Options
* [bug] HTML Links are properly linking to plot authors (class movie).
* [bug] Failing to build html links for taxonomy pages for people with accentuated names. The accents were kept in URL.

v.3.4.2
* [major] Updated to [imdbphp library 7.1](https://github.com/tboothman/imdbphp/releases/tag/v7.1.0 "imdbphp library 7.1")
* [feature] Added a number limit for producers and akas
* [technical] Use WP cron to update options. Uses less resources, better optimisation.
* [technical] Class config used to include two classes. Now they're merged. All calls to class config trigger an automatic sending of the settings to imdbphp. Debug is now displayed everywhere.
* [technical] Class config has been cleaned. Constants have been included into the class. A lot of polishing is still needed.
* [technical] Implemented [Monolog](https://github.com/Seldaek/monolog/ "Monolo GIT") as new debug parser
* [bug] Taxonomy (english term+additional language related post) were added on the display of a post.

v.3.4.1
* [bug] auto widget was not working anymore due to recent changes in class.widget.php

v.3.4
* [info] Due to recent changes on IMDb website, the new imdbphp library has been rewritten. You must clear your cache folder to make sure the new pages are downloaded, as the olde pages are no longer supported. Just go to the admin panel, go to Lumière plugin settings, then 'cache management' panel -> 'manage cache', click on 'delete all cache', and you're done.
* [major] Updated to IMDbPHP 7.0 library. Meant to address the new IMDB website format.
* [feature] Added the option to search by movies, by tv shows, by both, and by videogames
* [feature] A bug (see technical _passing all Lumière options_ )was preventing cache functions from imdbphp libraries to be fully utilised. Speed greatly improved.
* [feature] Lumiere search gutenberg now searches also TV series. Increased number of max results to 15.
* [feature] Added compatibility with Polylang plugin for taxonomy. Created lumiere_add_taxo_lang_to_polylang() in class.movie.php that push a lang string into polylang.
* [technical] Added debug imdbphp to lumiere_debug function
* [technical] Passing all Lumiere options to imdbphp libraries through the $config. Language and cache_expire were missing in admin, popups and class.movie pages.
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
* [bug] Auto widget was throwing fatal error, probably since transforming imdb-movies into movie.class. Fixed class.movie and class.widget.
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
* [bug] Removed $imdbwidgetcommentsnumber from config and options from options-widget.php. The new imdbphp classes retrieve only one comment. Adapted also class.movie.php.
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
* [technical] Moved class from inside lumiere-movies.php to class/class.lumiere.php
* [technical] Implemented a taxonomy slug URL. By changing the options in the admin panel, the URL for taxonomy pages can be changed.
* [technical] Taxonomy is now activated by default, allowing "genre" to be available. "genre" is also activated by default in "what to display" now.
* [technical] Widget is now a class compliant with WordPress coding standards.
* [technical] Simplified $imdballmeta arrays management. These globals passing from widget and core classes are now structured as $imdballmeta[]['byname'] and $imdballmeta[]['bymid'] and retrieved in imdb-movie.inc.php

v.3.2.2
* [feature] Implemented a selection for popup colors in admin menu 
* [feature] Changed AKA design in popups
* [technical] Remove options for photo path and directory in options-cache.php, now automatically generated
* [technical] Contrained options for "Plugin directory" and "URL for the popups" in options-general.php, now partially automatically generated
* [technical] Changed $imdbAdminOptions $imdbCacheOptions arrays in config.php, imdbplugindirectory, photo path, and directory are now relative
* [technical] Added a plugin update function to make htaccess file and update config on update
* [technical] Debug is now a function implemented in all relevant pages
* [bug] Popup-imdb_person: soundtrack film was redirecting to popup-imdb_person instead of popup-imdb_movie
* [bug] New fix for popup titles function in lumiere-movies.php
* [bug] Fixed link with extra "/" in URL for akas in popup-imdb_movie.php

v3.2.1
* [bug] admin panel wasn't displayed, bug in inc/admin_pages.php
* [bug] popup titles function in lumiere-movies.php was preventing the popup from working

v3.2
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

v3.1.1
* [technical] CSS & JS automatically take LUMIERE_VERSION string in URL when they're called, so every new Lumière! version triggers a cache refresh
* [technical] Moved imdbphp classes from /src to /class/imdbphp, moved config.php, functions.php, into that same folder
* [bug] Added into popups and imdb-movies.inc movie/people images the code 'loading="eager"' so they're not lazy-loaded (otherwise they're not displayed)
* [bug] Taxonomy was not registred and not appearing in Posts, fixed function lumiere_create_taxonomies()
* [bug] Redirection when deleting/updating cache details was broken
* [bug] Fixed cache refresh/delete redirect + move form functions to options cache

v3.1
* [Major] Due to compatibility reasons with Gutenberg, the way to call links for internal popupups has changed from '< !--imdb-- > < !--/imdb-->' to 'span class="lumiere_link_maker"'. Compatibility with the old way currently maintained.
* [Major] Finished Gutenberg interface, two buttons added.
* [Major] Updated to IMDbPHP 6.5.1 library.
* [new] Caching system: Changed the path of Lumière! cache (now in wp-content/cache) so it will survive the plugin updates. If wp-content/cache is not writable, it uses wp-content/plugins/lumiere/cache path.
* [new] Caching layout: Vastly simplified the cache menu. Moved the paths options to manage cache submenu (options are hidden by default), merged store and use cache (store is not available anymore), removed the zip options.
* [new] General options layout: polished general options, moved many option to advanced tab, should be easier to understand
* [feature] Variable imdbcachedetails set to true by default (no real reason to not show the cache management) in options-cache.php and config.php
* [Bug] the lumiere's scripts weren't loaded outside the Lumiere's dedicated pages. Removed checks in lumiere_add_footer_blog()'s lumiere-movies.php, deactivated calls to head and footer construction for imbd-movies.inc.php in lumiere-movies.php and widget (head and footer are already loaded!)
* [Bug] removed CSP compliance in js/lumiere_scripts.js and js/highslide-options.js as it prevented a correct running of the highslide popups -> highslide should close windows under CSP
* [Bug] Removed adding automatically image in tinyMCE, I can't get rid of it when posting (imdbImg in lumiere_admin_tinymce_editor.js)
* [Bug] uninstall wasn't removing taxonomy terms. Simplified the function and now it works.
* [Bug] introduced in v3.0, couldn't display more than one film in widgets. Replaced 			require_once( $imdb_admin_values['imdbpluginpath'] . 'inc/imdb-movie.inc.php'); by require( plugin_dir_path( __FILE__ ) . 'imdb-movie.inc.php'); in widget.php

v3.0.1
* [new] Gutenberg Block: better layout, implemented selection of either [imdblt] or [imdbltid]
* [new] Gutenberg Block: created a new page gutenberg-search.php for searching movies from gutenberg
* [new] Translation: renamed all calls in __() and the likes from 'imdb' to 'lumiere-movies', changed the language system according to the (new) Wordpress language system
* [new] Explaination: "Toolbar Lumière admin menu" and "Menu for Lumière options" where a mess in terms of explanation. Rewrote.
* [new] Taxonomy standard template file was missing translation options
* [new] Renamed the widget's name for the wordpress widget page
* [new] Converted onclick=\"return hs.close(this)\" from highslide-options.js into a jQuery function
* [Bug] Issues of mobile responsiveness in options-cache.php
* [Bug] highslide popup unactive by default. "imdbpopup_highslide" set on true in config
* [Bug] banner wrong name
* [Bug] The new name for the widget hook register wasn't updated in config.php (the check failed in Lumière options)
* [Bug] chmod'ing process upon plugin activation wasn't taking in consideration if a cache folder already exists
* [Bug] loooongstanding bug for taxonomy links in imdb-movie.inc.php, rewrote how taxonomy links are called, created a function
* [Bug] update taxonomy activation/deactivation didn't refresh rewrite rules, refresh of taxonomy should now make the taxonomy links

v3.0
* [major] updated to imdbphp-6.5 library
* [major] rewritten the code to be compliant with wordpress security
* [major] plugin is compatible with tablets/mobile phones (fully responsive)
* [major] first block for Gutenberg (for [imdblt] movies included by name)
* [feature] popups URLs are rewritten to (blog)/imdlt/(film|person)
* [feature] updated to highslide 5.0
* [feature] moved all inline javascripts to external files so the plugin is Content Security Policy (CSP) compliant
* [feature] cache folder is created if it doesn't exist, highslide is download if it doesn't exist
* [feature] new icons, new layout
* [feature] removed Pilot search options
* [feature] removed js support for IE
* [feature] removed support for wordpress smaller to 5.7
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
* [misc] renamed the plugin to Lumiere Movies, renamed all classes and functions accordingly
* [misc] Under the hood, more robust plugin following (a bit more) wordpress & PHP standards

v2.3.2
* [major] updated to imdbphp-2.3.2 library
* [feature] added Ukranian : thanks Michael Yunat
* [bug] fixed longstanding bug: shortcodes when editing a wordpress post are back
* [bug] fixed longstanding bug: bios are back

v2.2.3
* [major] updated to imdbphp-2.2.3 library

v2.2.2
* [major] updated to imdbphp-2.2.2 library
* [feature] added production companies

v2.2.1
* [major] updated to imdbphp-2.2.1 library
* [feature] added Croatian : thanks Borisa Djuraskovic!
* [bug] fixed newly added "keywords" bug in the taxonomy

v2.2
* [major] updated to imdbphp-2.2 library
* [feature] added "keywords" option which allows to diplay the movie keywords. Taxonomy included.
* [feature] "Display only thumbnail" option now affects popup picture width in the same way it affects widgets picture width
* [bug] deleted "ob_flush(); flush();" in inc/popup-header.php as it was preventing the CSS and JS to be used
* [bug] removed "movie connection" option in widget; this was a long non-working useless option

v2.1.9
* [major] updated to imdbphp-2.1.9 library

v2.1.8
* [major] updated to imdbphp-2.1.8 library

v2.1.7
* [major] updated to imdbphp-2.1.7 library
* [bug] soundtracks fixed
* [bug] movie connection is broken, unactivated

v2.1.6
* [major] updated to imdbphp-2.1.6 library

v2.1.5
* [major] updated to imdbphp-2.1.5 library

v2.1.4
* [major] updated to imdbphp-2.1.4 library
* [feature] changed obsolete __ngettext() method to _n() (__ngettext obsolete since wordpress 2.8)
* [feature] removed moviepilot options, but who was still using that?

v2.1.3
* [major] updated to imdbphp-2.1.3 library
* [major] changed the way to use highslide js (on Wordpress request, piece of code not GPL compliant); it is mandatory now to download the library from [IMDBLt website](https://www.jcvignoli.com/blog/en/imdb-link-transformer "IMDbLT website") in order to get this damn cool window. Once the file downloaded, move the folder "highslide" into the "js" one and check general options in order to activate it
* [major] translated into Romanian, thanks to [Web Geek Science](https://webhostinggeeks.com "Web Hosting Geeks")

v2.1.2
* [major] updated to imdbphp-2.1.2 library

v2.1.1
* [major] updated to imdbphp-2.1.1 library
* [feature] new cache option to display cache elements in a shorter way (no picture, only names) -> "Quick advanced cache details" option
* [feature] added IMDB LT to the new wordpress toolbar menu

v2.1
* [major] updated to imdbphp-2.1.0 library

v2.0.8
* [major] huge speed improvement changing in inc/functions.php is_multiArrayEmpty() from [PHP empty function 1](http://in2.php.net/manual/fr/function.empty.php#92308 "PHP empty function comments") to [PHP empty function 2](http://in2.php.net/manual/fr/function.empty.php#94786 "PHP empty function comments") -> credits to fha
* [feature] cache options are divided between "Cache general options" and "Manage Cache" pages. Much more advanced way to manage cached movies. Movie's and People's cache management.
* [feature] when the widget displayed more than one movie the widget's title took the title of the previous movie's name
* [feature] still some "Fatal error: Cannot redeclare plugin_action() (previously declared in /xxxxxx/wp-content/plugins/wp-to-twitter/wp-to-twitter.php on line 1064) in /xxxxxx/wp-content/plugins/imdb-link-transformer/inc/widget.php:104" -> updated the way to register the plugin, using wp_register_sidebar_widget instead of register_sidebar_widget and removing the plugin_action() function (all obsoletes)
* [feature] admin menu added few icons
* [bug] fixed displaying " as " text even when no role was existing in popup.php
* [bug] fixed typo missing closing ">" line 142 inc/help.php

v2.0.7
* [major] updated to imdbphp-2.0.7 library
* [feature] updated to highslide js 4.1.12

v2.0.6
* [major] updated to imdbphp-2.0.6 library

v2.0.5
* [major] updated to imdbphp-2.0.5 library
* [feature] widget only finds the movie if the tt number on IMDB is followed by a full seven digits; modified "$moviespecificid = $value;" by "$moviespecificid  = str_pad($value, 7, "0", STR_PAD_LEFT);" into widget.php 

v2.0.4
* [major] updated to imdbphp-2.0.4 library - !! No more "IMDb" word added at the end of popup movie's title - Pictures from IMDb are back !
* [feature] it is now possible to resize thumbnail even if "Display only thumbnail" is turned on yes (found under "general options" -> "Paths & Layout" -> "layout"->"Imdb cover picture"). "Size" is not anymore unactivated (options-general.php) and value filled in is taken into account (imdb-movie.inc.php)
* [feature] french strings corrected

v2.0.3
* [bug] Many bugs have been corrected. Among them, pictures should be displayed even if using Firefox, country as movie detail is back.
* [bug] Two taxonomy help pictures were forgotten! 
* [bug] Layout broken when using moviepilot (writer & producer sections, $role parts)
* [major] updated to imdbphp-2.0.2 library
* [feature] added Bulgarian : thanks Peter!
* [feature] added a function to construct highslide and classical links(imdb_popup_link() & imdb_popup_highslide_link() in inc/functions.php, called from imdb-link-transformer.php)
* [feature] added title as taxonomy

v2.0.2
* [feature] Completely revisted as taxonomy works; one can select many movie's details as taxonomy. New admin options related to.
* [feature] Trailers added as a new movie detail.
* [feature] Year added as a new movie detail. The movie's release year will always take place next the title, in brackets.
* [feature] French strings corrected. Taxonomy help improved.

v2.0.1
* [bug] when using lumiere_external_call() function with an imdbid from external (not with a movie's name), it didn't work. inc/imdb-link-transformer.php corrected
* [major] added possibility (by an option in advanced general settings) to add movie's genre tags directly into post tags (actually, into taxonomy wordpress function). When activated, it display new links instead previous genre tags.
* [feature] added colors when option is selected in "Widget/Inside post Options".
* [feature] uninstall function wasn't activated. Now, uninstalling IMDb LT plugin should remove every single option added by its own.
* [feature] updated to highslide js 4.1.9
* [feature] English/French corrections, misc improvements, help enhanced

v2.0
* [bug] A bug was preventing for display right pictures cache size (in admin/cache section)
* [bug] access level for Pilot website wasn't working anymore (switching to any value hadn't any effect except staying with "NO_ACCESS")
* [major] updated to imdbphp-2.0.1 library
* [feature] better multilanguage support -> echo(sprintf(__ngettext())) instead of _e()
* [feature] as required by moviepilot's team, added website source at the post's end. IMDBLT will display the source(s) where data come from
* [feature] added a few strings forgotten into the translated strings
* [feature] removed imdb-link-transformer.php.noclass (obsolete file)
* a bunch of improvements

v1.6
* [bug] + [feature] User's comment wasn't working anymore. Rewritten, and also added the option to choose how many comments to display.
* [bug] movieconnection(), releaseInfo(), born() and died(), color(), sound(), mpaa(), mpaa_hist() fixed
* [major] updated to imdbphp-1.9.10 temporary library
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
* [feature] splited "General options" from wordpress admin into two subsections : "Paths & Layout" and "Advanced". Advanced subsection is meant to include the... advanced options. General option is much more readable, now.
* [feature] cache size is now computed and displayed in admin/cache section 
* [feature] turned imbd-link-transformer into a class (class imdblt{}); modified inc/popup-imdb_movie.php and inc/help.php according the new structure. As a result of this, one has to open the class (ie $imdblt = new imdblt) before calling the function itself (ie $imdblt->lumiere_external_call('Descent', 'external'))
* [feature] by default, cover pictures are not displayed as thumbnail, with a size of 100 px. To change this behaviour: "General options -> Imdb cover picture -> Display only thumbnail"
* [feature] removed imdb adress us.imdb.com, added www.imdb.fr
* [feature] moved and renamed tinymce javascript from tinymce/editor_plugin.js to js/tinymce_editor_plugin.js

v1.5
* [major] updated to imdbphp-1.9.8 library
* [feature] many broken searches should work again
* [feature] added css update explanations into inc/help.php

v1.2.2
* [major] updated to imdbphp-1.2.2 library

v1.2.1
* [feature] It is now possible to keep your customized imdb.css file; just put your imdb.css into your theme folder.
* [feature] added new titles to "inc/options-cache.php"
* [feature] added spanish (thanks to Andrés Cabrera)

v1.2
* [major] updated to imdbphp-1.2 library
* [feature] added new cache option (Show avanced cache details) which permits (or not) to show movie's cache details (and allows to delete cache for every movie). Unactivated by default, it prevents "Fatal error: Maximum execution time of 30 seconds exceeded" errors.
* [feature] modified imdb.css (improvement made by Jeremie Boniface, I'm not able to manage these things)
* [feature] rating (inside a post & widget) displays now pictures instead of numbers; depending of good (or bad) feeedbacks, value $imdbwidgetratingnopics has to be implemented into plugin (to permit users to get back to old display, with the numbers) -> Jeremie Boniface demand
* [feature] more french strings corrected
* [feature] brazilian updated
* [feature] only 1 user comment could be retrieved! changed plugin options according to this limitation (removed $imdbwidgetcommentsnumber)
* [feature] renamed "cache.php" to "inc/options-cache.php"
* [feature] removed robots.txt, since v. 1.1.15 workaround is efficient

v1.1.15
* [bug] (somewhat): if highslide popup is active, search engine bots will index several hundred thousands of movies and actors, since they can now follow an href. Except if you do not care at all about space and bandwith, it could down your website quickly. Moved links to highslide popups from href="" to src: '' (inside javascript) and closed the href tag. To prevent any kind of search engine crawling in a place where it should not crawl, added also a robots.txt file (not convinced about its usefulness, though), and a test at the begining of popup-header.php.
* [bug] some translated french strings were wrong
* [bug] when several directors were found, it was missing space or comma to display -> added comma
* [major] updated to imdbphp-1.1.15 library (1/hopefully fixed "invalid stream resource" on some network timeouts, movie: 2/ added method to retrieve movie types (if specified), person: 3/fixed a property missing in initialization, Fixed goofs(), trailers(), and trivia() )
* [major] every admin settings section (cache, widget & general) has its own preferences storage (new foreign keys: $imdb_admin_values, $imdb_widget_values,$imdb_cache_values, whereas $imdb_config_values is dead). As a result of this, resetting General options won't interfere with Cache options, and vice versa.
* changed the way several css directives related to popup were working (ie, all &lt;a&gt tags in owner's blog were impacted, instead of only &lt;a&gt from the popup)
* [feature] added compatibility with [link-indication plugin](http://wordpress.org/extend/plugins/link-indication/ "link-indication plugin home") (modified imdb-link-transformer.php add_filter's execution priority to 11)
* [feature] mixing "imdb-movie-widget-bymid" and "imdb-movie-widget" keys actually does work! Removing references saying it doesn't.
* [feature] cache movie files into cache.php are now alphabetically ordered
* [feature] soundtrack & quotes include an internal popup link instead of a link to imdb website 
* [feature] moved imdb-widget.php to /inc/, and renamed to widget.php
* [feature] added trademarks fields into popup (misc tab) & biographical movies (bio tab)
* [feature] added plot limit selection (imdblt settings : Widget/Inside post Options  / What to display )
* [feature] imdb pictures displayed both in widget and inside a post can be "highslided". To achieve this, activate "Display highslide popup" option and turn "Display only thumbnail" to "no" (and put a small size value, as "150" per example).

v1.1.14.4
* [bug] in config-widget.php, a value for imdbwidgettitle (and one other) section was wrong
* [bug] a value $test (imdb-movie.inc.php) was wrongly named -> renamed to $genre
* [bug] wrong value for "Plugin directory" (config.php) -> modified
* [major] added help page in settings: how to ( including movable boxes to keep useful information), changelog, faqs pages
* [major] added security constraints (mainly data post check)
* [major] added possiblity to arrange disposition of movie data order (available on Widget/Inside post Options)
* [major] added many many new movie's details: goofs,comments,quotes,taglines,colors,also known as,composer,soundtrackmovieconnection,officialSites
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

v1.1.14.3
* [feature] new way to include movies inside a post: use tags (new way) [imdblt]movies name[/imdblt] instead of (old way) <?php lumiere_external_call ?> + Exec-PHP plugin. That means this release doesn't need any more any kind of third party plugin! Compatibility with the old way kept.
* [feature] changed the way options from admin settings appear; depending of the current selections, some options will appear, some disappear
* [feature] added "Remove popup links" option; user can choose to remove popup links from widget -> no more popup
* [feature] added imdb photo size width option (only displayed if thumbnail option deactivated)
* [feature] added more pics in admin settings menu
* [feature] removed call_imdb_movie function from imdb-link-transformer.php (wasn't used)
* [feature] added more screenshots
* [bugs] corrected many french comments, typos, options bad explained, an so on...

v1.1.14.2
* [feature] added brazilian language

v1.1.14.1
* [feature] added imdb photo size option; user can choose to display the big cover picture instead of the thumbnails'
* [bugs] corrected writers section from imdb-movie.inc.php (before that, some <br /> where added before names, which moved forward text). Was only an issue when displaying imdb inside a post (via imd_external_call() and Exec-PHP plugin)
* moved - and renamed - imdb_person.php and imdb_movie.php, and moved popup.php to a more suitable place, "inc/" folder.

v1.1.14
* [major] updated to imdbphp-1.1.14 library
* [feature] few french lines (not translated) moved to po files
* [feature] defined 'IMDBPHP_CONFIG' value in various files, so future update of izzy's libs could be automatically made, instead of changing config lines from imdb_base.class.php
* [feature] French comments in php's translated to English

v1.1.13.5
* [feature] added the possiblity to directly enter the movie id through "imdb-movie-widget-bymid" post meta data 

v1.1.13
* [major] updated to imdbphp-1.1.13 library
* [bug] typo in inc/config-widget.php

v1.0.1
* [bug] imdb.css, h1 section was preventing of working original h1 

v1.0
* [feature] actress filmography working at once (added "actress" in filmography part from imdb_person.php)
* [feature] various (very) minor enhancements

v0.9.5
* [major] updated to imdbphp-1.1.1 library
* [feature] added field plot as an available option for widget
* [feature] update admin css to match wordpress 2.7 css
* [bug] rating function working again

v0.9.3
* [feature] added multiple queries widget process (several "imdb-movie-widget" field can be added to a post). Caution! Adding several fields will slow down page display.
* [feature] removed the so-said pass of "$filmid = $_GET['imdbpostid']" from imdb-widget to imdb-movie.inc.php (seems to be passed because functional call)
* [feature] added a function lumiere_external_call () to permit to call the widget from anywhere into templates (widget no more mandatory)
* [feature] changed to lumiere_htmlize() function (htmlentities() unactivated, as it doesn't seems anymore needed and prevented the accentuated letters to go through the search process)

v0.9.2
* [feature] style issue (body class wasn't defined to be only a popup class, therefore general blog theme could be affected)
* [feature] work on w3c validation (it should be valid, now)

v0.9.1
* [bug] bug giving an error when no meta custom value added to a post

v0.9
* [feature] added widget. Using both widget & direct search possibilities, now everybody can display information related to a post directy into widget fields!
* [feature] most of the functions have been implemented. It still lacks the "actress" class, which means that it doesn't yet display the data related to an actress. Next release will be out when such a class will be finished.
* [bug] preventing imdb button (no tinymce) to be displayed
* [bug] usual layout&code fixes

v0.8
* [major] updated to imdbphp-1.0.7 class 
* [feature] get rid of the PEAR option, it isn't useful
* [bug]  with some [searchs not giving all results for a person](https://projects.izzysoft.de/trac/imdbphp/ticket/46 "IMDbphp website")
* [feature] added an option to remove imdb links (when doing a search)
* [feature] added an option to bypass the search stage for the popup. Activated by default.
* [feature] changed the way of browsing in popup window ("AKA search" instead of "back", keeping movie's name in $_GET, layout)
* [feature] Automatic check for the cache folders (under admin board options)

v0.7.1
* [bugs] relative path & imdb-link-transformer.php with blank spaces resolved

v0.7
* [feature] added admin page
* [feature] added "wait on load" message when first search (when results are not yet cached, it take some time to get them)

v0.6.1
* [bugs] minors fixes (layout & code)

v0.6
* [feature] changed the way the tags are used; < ! --imdb-->nameMovie< ! --/imdb--> instead of < imdb>nameMovie< /imdb>. This means the already tagged fields should be changed to these new tags. As a result of this, IMDb link transformer is only compatible with Wordpress > 2.5!
* [feature] support for tinymce editor (imdb tags can be automatically inserted by a new button)
* [bugs] some minors fixes (layout & code)
	
0.5
* [feature] director page created (filmo, bio & misc)
* [major] updated to imdbphp-1.0.6 libraries
* [feature] one title, one result
* [feature] every url links to an internal page (except the imdb logos)
* [feature] localisation files added
* [feature] one config file
* [feature] huge layout and stylesheet work
* [feature] added many functions

0.3
* [major] initial public release of IMDb Link Transformer

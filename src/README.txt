=== Lumiere Movies ===
Contributors: psykonevro
Tags: cinema, film, imdb, movie, actor, internet-movie-database, director, taxonomy
Requires at least: 5.3
Tested up to: 6.4.1
Stable tag: 3.11.6
Requires PHP: 8.0
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Donate link: https://www.paypal.me/jcvignoli

Lumière! Movies retrieves information from www.imdb.com to include it in your blog. This is the most versatile and comprehensive plugin dedicated to enhance your movie reviews.

== Description ==

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website") to see how the plugin can enhance your website.

**Lumiere! Movies** helps you integrate loads of information about movies and stars. Widgets, links to informative popup, and dedicated taxonomy pages are available. You can easily include movie's information into your posts. Everything is automatised and no further configuration is required from the user. Even though the plugin works out of the box, your can change the themes, add taxonomy to your pages, remove links, display automatically information according to your blog posts' titles, and use many hidden features. All the information about movie directors, pictures, etc. is retrieved from the well-known [IMDb](https://www.imdb.com "Internet movie database") website. Lumière! ensures that you have the most accurate and reliable information always available on your blog.

Many features are available in the wordpress old and new editing interfaces (Gutenberg, Visual editor, and HTML editor). It is Content Security Policy (CSP) compliant, takes advantage of Polylang plugin and is partially compatible with AMP plugin.

**Lumière!** is a great tool to illustrate your articles. You can display movie details through many ways, such as in popups, widgets, and straight inside a post. It can be extensively fine-tuned in the admin options panel.

"Lumière! Movies" is the continuation of [IMDb Link Transformer plugin](https://wordpress.org/plugins/imdb-link-transformer/ "IMDb Link Transformer") that reached over 20'000 downloads. 

== Installation ==

= normal use =

1. Activate the plugin
2. Should you want to fine-tune your blog, configure the plugin (in admin settings). Default values are automatically filled, no change is needed for normal use.
3. Activate a Lumière widget if you intend to include movie information into your sidebar.
4. Write your posts including movie information using the many tools smoothly incorporated in WordPress!

= basic options =

There are three ways to use Lumière!: 1/ with the popup link maker, 2/ with a widget and 3/ inside a post. Each option can be combined with any other; there is no limitation!

1. **Popup** When writing your post, embed a movie's title using "Add IMDb Link" option. Select the movie's title you wrote down and click on that option. It will add an invisible HTML span tag around the selected title such as: < span data-lum_link_maker "popup"> movie's title< /span> that usually you can't see except if you're editing in text mode. You can see if it worked by the little icon on the left of you selected text. After publishing your post, your text will be clickable and will open a popup with data about the movie! Popups can be displayed using Bootstrap, Classic or Highslide modal windows (can be selected in Lumière! admin options).
2. **Widget** can be used to display movie's data. Go to widgets admin options and add a Lumière! widget in the sidebar you want to show information about movies. Once the widget is activated, you can add information about a movie to your sidebar: when editing your blog post, a new widget will be displayed for your to enter either the movie's name (that can lead to unexpected results) or the IMDb ID (this never fails in retrieving a movie) of the movie you want to be shown in the sidebar. If you don't know what the IMDb ID is, you can use the query link provided in Lumière! widget. Just search for the movie name and you will find the IMDb ID.
3. The plugin can **show IMDb data inside a post**. With modern WordPress (WP 5.8), just add a Lumière Inside a Post block and enter a movie's title or movie's imdb ID. For the latter, in order to find the IMDb ID use the query tool provided in Lumière block (gutenberg sidebar). A similar tool is provided with classic WP editor in a form of dropdown menu. If you're writing your post with classic WP editor (or pre-5.0 WordPress), use Lumière's bar tools to select the movie title: it will insert html tags around your selection, such as < span data-lum_movie_maker "movie_title">My movie's title< /span>. 

= Fine-tuning: =

1. Lumière! Movies can create pages that include a list of movies identically tagged (known as taxonomy). Taxonomy templates are provided. Check plugin's help to figure out how to use it.
2. You may edit the "/* ---- imdbincluded */" section in css/lumiere.css file to customize the layout according to your taste. In order to keep your stylesheet changes through Lumière! updates, you need to download an unminified lumiere.css from the [Lumiere GIT repository](https://github.com/jcvignoli/lumiere-movies/blob/master/src/css/lumiere.css), and after editing it just put your new lumiere.css file into your current WordPress template folder (a child template, preferably, it will get deleted by a template update otherwise). This way, your stylesheet modifications will be kept through Lumière!'s updates. Important: do not removed the section before "/* ---- imdbincluded */".

= Advanced =

1. If you **do not want Lumière to add any link** (in the case you are only looking for information displayed in widget and inside posts), search for the option located in "General options -> Advanced -> Remove popup links?" and select "yes". Links opening a popup (both in widget and posts) will not be be available anymore.
2. Should you want to display automatically a widget according to the post's title, just switch on the "Auto widget" option located in "General Options -> Advanced -> Auto widget" in the plugin admin options. Make sure you added a Lumière widget in "Appearence - Widgets". Usefull for blogs exclusively dedicated to movie reviews, where all posts' titles are named after movie's titles.
3. You may want to include a custom page in your blog that includes all your movie related articles. Have a look there : [Lost highway's movies reviews](https://www.jcvignoli.com/blog/critiques-de-cinema).
4. Taxonomy pages and popups URLs can be edited according to your tastes. In advanced general Lumière options, you may want to modify the URL starting with 'lumiere' for taxonomy pages. Make sure to refresh your "rewriting rules" when adding new taxonomy (visit in your admin interface the page Permalink Settings (/wp-admin/options-permalink.php)
5. Should your blog be dedicated to TV shows or videogames only, it is possible to change Lumière's search behaviour to retrieve exclusively those. In advanced general Lumière admin options, look for 'Search categories'.
6. Many more options are offered, just take a look at the options!

== Screenshots ==

1. Popup displayed when an imdb link is clicked.
2. How movie's data is displayed "inside a post" 
3. How movie's data is displayed in a "widget" 
4. Admin preferences for cache
5. The widget area to display a movie
6. Menu in visual editor (tinyMCE) for inclusion of a movie section or popup
7. Tool to insert a movie section or a popup in a post
8. Query page to find a movie IMDb ID
9. Gutenberg block
10. Taxonomy page for a star

== Frequently Asked Questions ==

= How to use the plugin? =

You can find further explanation about how to use the features of Lumière! in the dedicated page of the plugin settings. After installing Lumière!, take a look at the section "Lumière! help".

= Can I suggest a feature/report a bug regarding the plugin? =

Of course, pay a visit to the [Lumière! Movies home](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! Movies home") or [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository"). Do not hesitate to share your comments, glitches and wishes. The plugin does more or less what I need but users have helped me improve Lumière! a lot.

= I don't want to have links to a popup window! =

Look at "General Options -> Advanced -> Misc -> Remove all links?" and switch the option to "yes". Links will not be displayed anymore, both for the "widget" (inside posts) and external links (like popups).

= I want to keep data forever on my disk/server =

Look at "Cache management -> Cache general options -> Cache expire" and click on "never" to keep forever the downloaded data from IMDb. Be carefull with that option: changes made on IMDb will not be anymore reflected in your cache. Should you have selected that option, you can still delete/refresh any specific movie you want in the cache options.

= Is it possible to add several movies to sidebar/widget and inside my post?  =

While one widget only can be added per post, you can insert as many movies as you want inside your articles.

= How to integrate Lumière with Polylang plugin?  =

If Polylang is installed, new features for taxonomy are added, such as a dropdown for languages in taxonomy pages. Once you selected which to data to turn into taxonomy, you must activate the very same taxonomy in Polylang Settings -> Custom post types and Taxonomies -> Custom taxonomies-

= Is it AMP compliant?  =

Mostly it is. You may see some changes in the layout and obviously the apparence will change. As it is expected by AMPization of any webpages.

= Is it CSP compliant?  =

Content Security Policy (CSP) is a webserver based security avoiding injections to your pages. It greatly improves the security of your website.
Although WordPress is difficult to get fully CSP compliant (in particular the admin interface), Lumière is fully CSP compliant. Neither online javascripts nor stylesheets are added. It is advised to use the standards 'wp_script_attributes' and 'wp_inline_script_attributes' hooks to inject your nonces values into scripts, and use a regex WordPress buffer technique to inject the style nonces.

= Can I change the size of the poster pictures?  =

Sure thing, just untick 'Display only thumbnail' in general admin options, and insert the size in pixels of the picture.

= Can I change the size of the popups?  =

Sure thing, just fill in the width and heigth in general admin options.

= Can I change the color themes of the popups or inside the posts?  =

Sure thing, just select one of the theme available in general admin options, either in 'plain page' or 'popup' sections.

= Can I add or remove the data details, such as director or year of release?  =

Sure thing, you can also modify the order of these details. Just take a look at data management options.

= How does the plugin complies with Privacy Policy, such as the GDPR? =

No data is sent to IMDb when end users visits a wordpress website which installed the plugin. The website host does its own queries to the IMDb, without knowing who is visiting it.

Only the website owner is known from the IMDb, and must comply with the IMDb privacy policy: https://www.imdb.com/privacy
No data about the end user is sent to any other third party, so Lumière! is GDPR compliant. A short paragraph can be accessed in you you admin privacy policy page, which can be added to your own privacy policy page.

= Known issues =

* In taxonomy extra page for people, if both Polylang and AMP are activated, the form to switch the language doesn't work.

== Support ==

Go to [WordPress Support](https://wordpress.org/support/plugin/lumiere-movies/ "WordPress Support") for general issues, or to the [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository") for technical requests (developpers oriented).

It's always a good idea to look at the [official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! official website")

== Changelog == 

Take a look at the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.md "latest changes") to find out the latest developments. Or for even more extensive and recent changes available at [GIT commits](https://github.com/jcvignoli/lumiere-movies/commits/master "GIT commits").

Major changes:

= 3.11.4 =

Some bots scanning the popups without considering head rules (nofollow) will be now banned. This will prevents from having bots creating a huge cache and many requests that get user banned from IMDB (and save space).

= 3.11.2 =
Fixed bug preventing popup from popping up

= 3.11 =
-> Faster plugin (images smaller size, technical improvements)
-> Using less disk space (cache).
Fixed cache that was not properly deleted since GraphQL. Please delete your entire cache should you have selected the option "never" for the "Cache expire" in the admin cache options.
Many bugs brought by the availability of the French translation addressed, such as the integration with Polylang plugin.
Privacy explaination in admin should anyone want to add this piece to their own privacy page.
Adressed latest bugs with PHP 8.2. Fully compliant now.

= 3.10.2 =
Support for PHP < 8.0 totally dropped, removed str_contains().

= 3.10 = 
Start using GraphQL, as IMDbPHP is not maintained frequently. Forking the library.

= 3.9.4 = 
New Cache function: Keep automatically cache folder size below a certain size limit.

= 3.9.3 = 
* Code for auto-widgets (widgets displaying automatically movie's details according to the post title) has changed. Should you use this feature, just add again Lumière auto-widget.

= 3.9 = 
* Mainly technical and bug hunting release. Fully using composer, loading classes through composer autoload for more sustainability.
* Lumière will not be compatible with plugins that promote a web of spammers. First plugin to enter the hall of shame: RSS Feed Post Generator Echo, paid plugin used to make ghost websites with ads and make money.
* Better cache paths management. Cache is more solid.

= 3.8 = 
* PHP 8.0 is now required. Introducing a new modal window, Bootstrap. User can select in admin whether to use Bootstrap, Highslide or Classic popups. Movie and People Popups do not throw 404 error anymore, they'll be indexed by search engines. Huge work for OOP coding with the popups links, the plugin is faster.

= 3.7 = 
* Fully PHP 8.0 compliant. Better compliance with AMP plugin. Many bugs addressed, better OOP coding, better plugin structure.

= 3.6 = 
* Code linted and functions rewrote using PHPCS, PHPMD and PHPStan. Faster, more secure and more stable plugin.
* Uninstall process properly implemented. Lumière doesn't rely on WordPress deactivation function anymore. Properly delete taxonomy. Nothing left in user database.
* Bug hunting, as usual.

= 3.5 = 
* Shortcodes [ imdblt ] and [ imdbltid ] have become obsolete, using span html tags instead. It ensures that upon Lumière uninstall, no garbage is left in your articles. Install and uninstall will be smoothly processed! Compatibility with obsolete shortcodes ensured.
* link to popups are now dealt by a < span data-lum_link_maker "popup"> instead of < span class "lumiere_link_maker"> for plugin code consistency. No compatibility with the latter ensured, since it was recently introduced.
* Support for the plugin in Help admin section has been improved and updated

= 3.4 =
* Cache improvement, fixed longstanding bugs, admin design ameliorated, popups design ameliorated, lumière variables are now automatically updated, code simplification (notably droped htaccess generation), taxonomy pages for people created (huge boost for reasons of using taxonomy). Updated to imdbphp 7.0 library.* New types of search: you can select to search movies, tv shows, and even videogames!
* Due to recent changes on IMDb website, the new imdbphp library has been rewritten. You must clear your cache folder to make sure the new pages are downloaded, as the olde pages are no longer supported. Just go to the admin panel, go to Lumière plugin settings, then 'cache management' panel -> 'manage cache', click on 'delete all cache', and you're done.

= 3.3 =
* Considerably simplified the way to include widgets; Lumière! now has a metabox in the edit interface. Taxonomy system is fully versatile (URL is editable). Uninstall/deactivation fully functional. Introduced the option to keep the settings upon deactivation (therefore uninstall too). Better design for the admin panels and popups. Under the hood, coding better respecting WordPress and PHP standards.

= 3.2 =
* Many options related to the popups (favicon, change the URL, etc.), fixed missing/wrong variables all over the plugin, further compatibility with PHP8 added, fixed the submit buttons in the admin, much technical work and bug hunting

= 3.1 =
* Due to compatibility reasons with Gutenberg, the way to display links to internal popupups has changed from '(!--imdb--)(!--/imdb--)' to '< span class "lumiere_link_maker">'. Compatibility with the old way currently maintained.
* Gutenberg interface finished.

= 3.0 =
* Major update, plugin vastly rewritten. Name IMDb Link Transformer changed to Lumière!. Should be Content Security Policy (CSP) compliant. Too many changes to be listed. Check the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.md "latest changelog").

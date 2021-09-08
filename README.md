# Lumiere Movies

**Contributors:** [@jcvignoli](https://github.com/jcvignoli) \
**Donate link:** https://www.paypal.me/jcvignoli \
**Tags:** cinema, film, imdb, movie, actor \
**Requires at least:** 4.0 \
**Tested up to:** 5.8 \
**Stable tag:** 3.6.4 \
**Requires PHP:** 7.1 \
**License:** GPLv3 \
**License URI:** https://www.gnu.org/licenses/gpl-3.0.en.html

Lumière! Movies retrieves information from www.imdb.com to include it in your blog. This is the most versatile and comprehensive plugin for blogs dedicated to movie reviews.

## Description

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website") to see how the plugin can improve your website.

**Lumiere! Movies** helps you integrate loads of information about movies and stars. Widgets, links to informative popup, and dedicated taxonomy pages are available. You even in a click include movie's information into your posts. Everything is automatised: although the plugin works out of the box and doesn't need any further change in the settings, your can change the themes, add taxonomy to your pages, remove links, and use many hidden features. Information about movie directors, pictures, all the information is retrieved from the famous [IMDb](https://www.imdb.com "Internet movie database") website. Lumière! ensures that you have the most accurate and reliable information always available on your blog.

Many features are available in the wordpress editing interfaces (Gutenberg, Visual editor, and HTML editor).

**Lumière!** is a great tool to illustrate your articles. It is an amazingly versatile plugin: users can display movie details through many ways: popups, widget, and straight inside the article. It can be extensively fine-tuned in the admin panel.

"Lumière! Movies" is the continuation of [IMDb Link Transformer plugin](https://wordpress.org/plugins/imdb-link-transformer/ "IMDb Link Transformer") that reached over 20'000 downloads. 

## Installation

### required

PHP 7.2 is required. PHP 8 will soon be mandatory.

1. Activate the plugin
2. Configure the plugin (in admin settings). Default values are automatically filled. In most cases, no change is required.
3. Make sure the cache directories (cache and photo directories) have been created (check the cache settings in Lumière cache settings). The plugin is preconfigured to work with "/wp-content/cache/lumiere-movies/".

### basic options

There are three ways to use Lumière!: 1/ with the popup link maker, 2/ with a widget and 3/ inside a post. Each option can be combined with any other; there is no limitation!

1. **Popup** When writing your post, embed your movie's title using the visual button in Gutenberg or in the former visual editor to add < span data-lum_link_maker "popup"> movie's title< /span> . A **link that opens a popup** will be created in your post. The popup contains data about the movie.
2. **Widget** can be activated and used to display movie's data. Once the widget activated, select accurately what information you want to display on your sidebar in the related admin panel of Lumière! administration settings. Then, when editing your post, just add either the name (can lead to unexpected results) or the IMDb ID (never fails) of the movie you want to be displayed in your widget. If you don't know the IMDb ID, you can use the query link provided in Lumière widget.
3. The plugin can **show IMDb data inside a post**. When writing your post, frame the movie title inside html tags < span data-lum_movie_maker "movie_title"> so you get ie < span data-lum_movie_maker "movie_title">Fight club< /span> in your post. Tools are provided in the form of blocks to do it automatically in gutenberg editor (the new WordPress editor). Or better, use IMDb ID instead of the movie name: < span data-lum_movie_maker "movie_id">0137523< /span>. To find the Imdbid, just use the query link provided in Lumière block.

### Fine tuning:

1. Lumière! Movies can create automatically tags and pages to include all movies identically tagged (known as taxonomy). Taxonomy templates are provided. Check plugin's help to figure out how to use it.
2. You may edit the "/* ---- imdbincluded */" section in css/lumiere.css file to customize the layout according to your taste. You can copy the lumiere.css file into your current template folder so your modification will be kept through Lumière!'s updates.

### Advanced

1. If you **do not want Lumière to add any link** (in the case you are only looking for information displayed in widget and inside posts), search for the option located in "General options -> Advanced -> Remove popup links?" and select "yes". Links opening a popup (both in widget and posts) will not be be available anymore.
2. Should you want to display automatically a widget according to the post's title, just switch on the "Auto widget" option located in "Options -> Advanced -> Auto widget" in the plugin admin panel. Usefull for blogs exclusively dedicated to movie reviews.
3. A (front) page can be created to include all your movie related articles. Have a look there : [Lost highway's movies reviews](https://www.jcvignoli.com/blog/critiques-de-cinema).

## Screenshots

### 1. Popup displayed when an imdb link is clicked.

![Popup displayed when an imdb link is clicked.](https://ps.w.org/lumiere-movies/assets/screenshot-1.jpg)

### 2. How movie's data is displayed "inside a post" 

![How movie's data is displayed "inside a post" ](https://ps.w.org/lumiere-movies/assets/screenshot-2.jpg)

### 3. How movie's data is displayed in a "widget" 

![How movie's data is displayed in a "widget" ](https://ps.w.org/lumiere-movies/assets/screenshot-3.jpg)

### 4. Admin preferences for cache

![Admin preferences for cache](https://ps.w.org/lumiere-movies/assets/screenshot-4.jpg)

### 5. The widget area to display a movie

![The widget area to display a movie](https://ps.w.org/lumiere-movies/assets/screenshot-5.jpg)

### 6. Menu in visual editor (tinyMCE) for inclusion of a movie section or popup

![Menu in visual editor (tinyMCE) for inclusion of a movie section or popup](https://ps.w.org/lumiere-movies/assets/screenshot-6.jpg)

### 7. Tool to insert a movie section or a popup in a post

![Tool to insert a movie section or a popup in a post](https://ps.w.org/lumiere-movies/assets/screenshot-7.jpg)

### 8. Query page to find a movie IMDb ID

![Query page to find a movie IMDb ID](https://ps.w.org/lumiere-movies/assets/screenshot-8.jpg)

### 9. Gutenberg block

![Gutenberg block](https://ps.w.org/lumiere-movies/assets/screenshot-9.jpg)

### 10. Taxonomy page for a star

![Taxonomy page for a star](https://ps.w.org/lumiere-movies/assets/screenshot-10.jpg)


## Frequently Asked Questions

### How to use the plugin?

You can find further explanation about how to use the features of Lumière! in the dedicated page of the plugin settings. After installing the plugin, take a look at the section "Lumière! help".

### Can I suggest a feature/report a bug regarding the plugin?

Of course, pay a visit to the [Lumière! Movies home](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! Movies home") or [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository"). Do not hesitate to share your comments, glitches and wishes. The plugin does more or less what I need but users have helped me improve Lumière! a lot.

### I don't want to have links to a popup window!

Look at "Widget/Inside post Options / Misc / Remove all links?" and switch the option to "yes". Links will not be displayed anymore, both for the "widget" (inside posts) and external links (like popups).

### I want to keep data forever on my disk/server

Look at "Cache management / Cache general options / Cache expire" and click on "never" to keep forever the downloaded data from IMDb. Be carefull with that option: changes made on IMDb will not be anymore reflected in your cache. Should you have selected that option, you can still delete/refresh any specific movie you want in the cache options.

### Is it possible to add several movies to sidebar/widget and inside my post?

While one widget only can be added per post, you can insert as many movies as you want inside your articles.

### Does it integrate with Polylang plugin?

If Polylang is installed, new features for taxonomy are added, such as selecting a specific installed language to show in taxonomy pages.

### Can I change the size of the poster pictures?

Sure thing, just untick 'Display only thumbnail' in general admin options, and insert the size in pixels of the picture.

### Can I change the size of the popups?

Sure thing, just fill in the width and heigth in general admin options.

### Can I change the color themes of the popups or inside the posts?

Sure thing, just select one of the theme available in general admin options, either in 'plain page' or 'popup' sections.

### Can I add or remove the data details, such as director or year of release?

Sure thing, you can also modify the order of these details. Just take a look at data management options.

### Known issues

* None.

## Support

Use the [WordPress Support](https://wordpress.org/support/plugin/lumiere-movies/ "WordPress Support") for general issues, the [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository") for technical requests.

It's always a good idea to look at the [official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! official website")

## Changelog

Take a look at the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.md "latest changes") to find out the latest developments. Or for even more extensive and recent changes available at [GIT commits](https://github.com/jcvignoli/lumiere-movies/commits/master "GIT commits").

Major changes:

### 3.6

* Code linted and functions rewrote using PHPCS, PHPMD and PHPStan. Faster, more secure and more stable plugin.
* Uninstall process properly implemented. Lumière doesn't rely on WordPress deactivation function anymore. Properly delete taxonomy. Nothing left in user database.
* Bug hunting, as usual.

### 3.5

* Shortcodes [ imdblt ] and [ imdbltid ] have become obsolete, using span html tags instead. It ensures that upon Lumière uninstall, no garbage is left in your articles. Install and uninstall will be smoothly processed! Compatibility with obsolete shortcodes ensured.
* link to popups are now dealt by a < span data-lum_link_maker "popup"> instead of < span class "lumiere_link_maker"> for plugin code consistency. No compatibility with the latter ensured, since it was recently introduced.
* Support for the plugin in Help admin section has been improved and updated

### 3.4

* Cache improvement, fixed longstanding bugs, admin design ameliorated, popups design ameliorated, lumière variables are now automatically updated, code simplification (notably droped htaccess generation), taxonomy pages for people created (huge boost for reasons of using taxonomy). Updated to imdbphp 7.0 library.* New types of search: you can select to search movies, tv shows, and even videogames!
* Due to recent changes on IMDb website, the new imdbphp library has been rewritten. You must clear your cache folder to make sure the new pages are downloaded, as the olde pages are no longer supported. Just go to the admin panel, go to Lumière plugin settings, then 'cache management' panel -> 'manage cache', click on 'delete all cache', and you're done.

### 3.3

* Considerably simplified the way to include widgets; Lumière! now has a metabox in the edit interface. Taxonomy system is fully versatile (URL is editable). Uninstall/deactivation fully functional. Introduced the option to keep the settings upon deactivation (therefore uninstall too). Better design for the admin panels and popups. Under the hood, coding better respecting WordPress and PHP standards.

### 3.2

* Many options related to the popups (favicon, change the URL, etc.), fixed missing/wrong variables all over the plugin, further compatibility with PHP8 added, fixed the submit buttons in the admin, much technical work and bug hunting

### 3.1

* Due to compatibility reasons with Gutenberg, the way to display links to internal popupups has changed from '(!--imdb--)(!--/imdb--)' to '< span class "lumiere_link_maker">'. Compatibility with the old way currently maintained.
* Gutenberg interface finished.

### 3.0

* Major update, plugin vastly rewritten. Name IMDb Link Transformer changed to Lumière!. Should be Content Security Policy (CSP) compliant. Too many changes to be listed. Check the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.md "latest changelog").

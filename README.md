# Lumiere Movies

**Contributors:** psykonevro \
**Donate link:** https://www.paypal.me/jcvignoli \
**Tags:** cinema, film, imdb, movie, actor \
**Requires at least:** 4.0 \
**Tested up to:** 5.8 \
**Stable tag:** 3.4.4 \
**Requires PHP:** 7.2 \
**License:** GPLv3 \
**License URI:** https://www.gnu.org/licenses/gpl-3.0.en.html

Lumière! Movies retrieves information from www.imdb.com to include it in your blog. This is the most versatile and comprehensive plugin for blogs dedicated to movies.

## Description

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website") to see how the plugin can improve your website.

**Lumiere! Movies** helps you integrate loads of information about movies and stars. Widgets, links to informative popup, and dedicated taxonomy pages are available. You even in a click include movie's information into your posts. Everything is automatised: although the plugin works out of the box and doesn't need any further change in the settings, your can change the themes, add taxonomy to your pages, remove links, and use many hidden features. Information about movie directors, pictures, all the information is retrieved from the famous [IMDb](https://www.imdb.com "Internet movie database") website. Lumière! ensures that you have the most accurate and reliable information always available on your blog.

Many features are available in the wordpress editing interfaces (Gutenberg, Visual editor, and HTML editor).

**Lumière!** is a great tool to illustrate your articles. It is an amazingly versatile plugin: users can display movie details through many ways: popups, widget, and straight inside the article. It can be extensively fine-tuned in the admin panel.

"Lumière! Movies" is the continuation of [IMDb Link Transformer plugin](https://wordpress.org/plugins/imdb-link-transformer/ "IMDb Link Transformer") that reached over 20'000 downloads. 

## Installation

### required

PHP 7 is required. PHP 8 will soon be mandatory.

1. Activate the plugin
2. Configure the plugin (in admin settings). Default values are automatically filled. In most cases, no change is required.
3. Make sure the cache directories (cache and photo directories) have been created (check the cache settings in Lumière cache settings). The plugin is preconfigured to work with "/wp-content/cache/lumiere-movies/".

### basic options

There are three ways to use Lumière!: 1/ with the popup link maker, 2/ with a widget and 3/ inside a post. Each option can be combined with any other; there is no limitation!

1. **Popup** When writing your post, add either < !--imdb-->movie's name< !--/imdb--> manually to your movie's name. Or use the visual buttons in Gutenberg or in the former Visual wordpress editor. A **link that opens a popup** will be created in your post. The popup contains much data about the movie.
2. **Widget** can be activated and used to display movie's data. Once the widget activated, select accurately what information you want to display on your sidebar in the related admin panel of Lumière! administration settings. Then, when editing your post, just add either the name (can lead to unexpected results) or the IMDb ID (never fails) of the movie you want to be displayed in your widget. If you don't know the IMDb ID, you can use the tool provided at the end of the widget.
3. The plugin can **show IMDb data inside a post**. When writing your post, put the movie name inside tags [imdblt][/imdblt] so you get ie [imdblt]Fight club[/imdblt] in your post. Or better, use IMDb ID instead of the movie name: [imdbltid]0137523[/imdbltid]. Here again, use the tool provided at the end of the widget to find the IMDb ID.

### Fine tuning:

1. Lumière! Movies can create automatically tags and pages to include all movies identically tagged (known as taxonomy). Taxonomy templates are provided. Check plugin's help to figure out how to use it.
2. You may edit the "/* ---- imdbincluded */" section in css/lumiere.css file to customize the layout according to your taste. You can copy the lumiere.css file into your current template folder so your modification will be kept through Lumière!'s updates.

### Advanced

1. If you **do not want Lumière to add any link** (in the case you are only looking for information displayed in widget and inside posts), search for the option located in "General options -> Advanced -> Remove popup links?" and select "yes". Links opening a popup (both in widget and posts) will not be be available anymore.
2. Should you want to display automatically a widget according to the post's title, just switch on the "Auto widget" option located in "Options -> Advanced -> Auto widget" in the plugin admin panel. Usefull for blogs exclusively dedicated to movie reviews.
3. A (front) page can be created to include all your movie related articles. Have a look there : [movie review's page](https://www.jcvignoli.com/blog/critiques-de-cinema "Lost highway's movies reviews").

## Screenshots

### 1. Popup displayed when an imdb link is clicked.

![Popup displayed when an imdb link is clicked.](https://ps.w.org/lumiere-movies/assets/screenshot-1.jpg)

### 2. How movie's data is displayed "inside a post" 

![How movie's data is displayed "inside a post" ](https://ps.w.org/lumiere-movies/assets/screenshot-2.jpg)

### 3. How movie's data is displayed in a "widget" 

![How movie's data is displayed in a "widget" ](https://ps.w.org/lumiere-movies/assets/screenshot-3.jpg)

### 4. Admin preferences for cache

![Admin preferences for cache](https://ps.w.org/lumiere-movies/assets/screenshot-4.jpg)

### 5. The code [imdblt] to enter inside a post and the icon to attach a popup link

![The code [imdblt] to enter inside a post and the icon to attach a popup link](https://ps.w.org/lumiere-movies/assets/screenshot-5.jpg)

### 6. The widget area to display a movie

![The widget area to display a movie](https://ps.w.org/lumiere-movies/assets/screenshot-6.jpg)

### 7. External tool to find a movie's IMDb ID

![External tool to find a movie's IMDb ID](https://ps.w.org/lumiere-movies/assets/screenshot-7.jpg)

### 8. Gutenberg block

![Gutenberg block](https://ps.w.org/lumiere-movies/assets/screenshot-8.jpg)

### 9. Taxonomy page for a person

![Taxonomy page for a person](https://ps.w.org/lumiere-movies/assets/screenshot-9.jpg)


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

### Known issues

* TinyMCE is not working the way it should. Only basic features are available at the moment. I'm working on it.

* Lumière! help section in the administration of the plugin is massively outdated.

## Support

Use the [WordPress Support](https://wordpress.org/support/plugin/lumiere-movies/ "WordPress Support") for general issues, the [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository") for technical requests.

It's always a good idea to look at the [official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! official website")

## Changelog

Take a look at the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.txt "latest changelog") to find out the latest developments. Or for even more extensive and recent changes available at my [GIT commits](https://github.com/jcvignoli/lumiere-movies/commits/master "GIT commits").

Major changes:

### 3.4

* Cache improvement, fixed longstanding bugs, admin design ameliorated, popups design ameliorated, lumière variables are now automatically updated, code simplification (notably droped htaccess generation), taxonomy pages for people created (huge boost for reasons of using taxonomy). Updated to imdbphp 7.0 library.
* New types of search: you can select to search movies, tv shows, and even videogames!
* Due to recent changes on IMDb website, the new imdbphp library has been rewritten. You must clear your cache folder to make sure the new pages are downloaded, as the olde pages are no longer supported. Just go to the admin panel, go to Lumière plugin settings, then 'cache management' panel -> 'manage cache', click on 'delete all cache', and you're done.

### 3.3

* Considerably simplified the way to include widgets; Lumière! now has a metabox in the edit interface. Taxonomy system is fully versatile (URL is editable). Uninstall/deactivation fully functional. Introduced the option to keep the settings upon deactivation (therefore uninstall too). Better design for the admin panels and popups. Under the hood, coding better respecting WordPress and PHP standards.

### 3.2

* Many options related to the popups (favicon, change the URL, etc.), fixed missing/wrong variables all over the plugin, further compatibility with PHP8 added, fixed the submit buttons in the admin, much technical work and bug hunting

### 3.1

* Due to compatibility reasons with Gutenberg, the way to display links to internal popupups has changed from '< !--imdb-->< !--/imdb-->' to 'span class

### "lumiere_link_maker"'. Compatibility with the old way currently maintained.

* Gutenberg interface finished.

### 3.0

* Major update, plugin vastly rewritten. Name IMDb Link Transformer changed to Lumière!. Should be Content Security Policy (CSP) compliant. Too many changes to be listed. Check the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.txt "latest changelog").

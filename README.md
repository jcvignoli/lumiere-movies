# Lumiere Movies

**Contributors:** jcv \
**Donate link:** https://www.paypal.me/jcvignoli and https://en.tipeee.com/lost-highway
Author URI: https://www.jcvignoli.com/blog
Plugin URI: https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin
Version: 3.0
**Tags:**  cinema, film, imdb, link, movie, plugin, review, tag, widget, taxonomy, popup, modal window \
**Requires at least:** 5.7 \
**Tested up to:** 5.7.1 \
**Stable tag:** 3.0

Lumiere Movies retrieves information from www.imdb.com into your blog. Cache management, widget available, this is the most versatile and comprehensive plugin to retrieve data from IMDb. "Lumiere Movies" is the continuation of "IMDb Link Transformer" plugin.

## Description

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website").

**IMDb changed its search method** Please prefer "imdbltid" method in your post/widget rather than "imdblt"

**Lumiere Movies** aims to ease the movies information search process, for both writer and reader. All movies names which are tagged between < !--imdb-->nameMovie< !--/imdb--> are automatically turned into an url. This url can open a new window (a popup) containing many useful data related to the movie itself. Lumiere Movies **transforms all the words you tagged into links to an informative windows**. It means one can view the filmmaker, the casting or the goofs that [IMDb](https://www.imdb.com "Internet movie database") (or similar) website includes with one click; it can show either the director or the movie related data (biography, filmography, miscellaneous related to the director; casting, goofs, AKA titles, crew and many others related to the movie). 

This plugin also adds **button option** in the editing window.

You can also activate the Lumiere Movies **widget**, which will display information parsed from IMDb straight on your sidebar. After activating the widget, each time you will add the key "imdb-movie-widget" in the custom field to your message *and* a movie's title in "value", the plugin will show the related information in your sidebar. 

In the same way, this plugin can display **many movie's related data inside a post**, when putting an IMDb movie's ID inside [imdbltid][/imdbltid] tags. No widget needed here, the data related to a movie can be displayed anywhere inside posts.

**Lumiere Movies** is a great tool increase the value of your posts. It is versatile and efficient. Bloggers can display movie details though many ways (popup, widget, straight into the post). Much of the plugin can be fine-tuned in the admin options and css.

## Installation

### required

PHP 7 is required. PHP 8 will soon be mandatory.

1. Activate the plugin
2. Configure the plugin (in admin settings). Values are automatically filled.
3. Make sure the cache directories (cache and photo directories) have been created (in cache settings). The plugin is preconfigured to work with "/wp-content/plugins/lumiere-movies/cache/". The plugin will work with or without the cache - but beware of an IMDb searching process that will take much time.

### basic options

There are three ways to use Lumiere Movies: popup link creator, widget and inside a post. Each option can be combined with any other, as blogger wants; there is no limitation, feel you free to use all three at once!

1. When writing your post, add either < !--imdb-->movie's name< !--/imdb--> tags to your movie's name (if you disabled visual editor, and that you have HTML interface) or click on Lumiere Movies's button after selecting the movie's name. As a result of this, a **link which will open a popup** will be created. The popup contains many data and is extensively browsable.
2. **Widget** can be activated, and used in a way where informations will be displayed inside it. Once the widget is activated, select closely what you want to display on your sidebar: options are available on 'imdb admin settings' tab. Also add "imdb-movie-widget" or "imdb-movie-widget-bymid" to your message's custom field; the value you add in will be the movie that is going to be displayed inside the widget. Check FAQs.
3. The plugin can **show IMDb data inside a post**. When writing your post, put the movie name inside tags [imdblt][/imdblt] (which gives ie [imdblt]Fight club[/imdblt]) or better, using imdb movie's id instead of the name: [imdbltid]0137523[/imdbltid]
4. You may also edit the "/* ---- imdbincluded */" part from imdb.css in order to customize layout according your taste.
5. To activate Highslide (nice code displaying a integrated windows instead of popups) you have to download the library from [Lumiere Movies website](https://www.jcvignoli.com/blog/wp-content/files/wordpress-lumiere-movies-highslide.zip "Lumiere Movies website"). Once the file downloaded, put the folder "highslide" into the "js" one and check general options in order to activate it. Please note Highslide JS is licensed under a Creative Commons Attribution-NonCommercial 2.5 License, which means you need the author's permission to use Highslide JS on commercial websites.

### Fine tuning:

1. The files inc/imdb-movie.inc.php, popup.php, imdb_movie.php and imdb_person.php can be edited to match your theme; check also /css/imdb.css if you want to customize default theme.
2. A (front) page can be created to include all you movies' related messages. Have a look there : [personal critics page](https://www.jcvignoli.com/blog/critiques-de-cinema "Lost highway's movies reviews").
3. If your language is not available, translate the .po file (located in the folder /lang of the plugin) to your language. Then [send it to me](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumiere Movies home").

### Advanced

1. If you are **not interested in having links opening popup windows** but look only for informations displayed (both in widget and posts), look for "widget options / Remove popup links?" and switch the option to "yes". There will be no more links opening a popup (both in widget and posts).
2. You may use imdb_call_external() function for externals calls to imdb functions. Have a look to help section (Inside post part)
3. Would you like to display automatically a widget according to the title of your post? Just switch on the "Widget/Inside post" option in "Options -> Misc ->Auto widget" admin area. Usefull for blogs dedicated to sharing movie reviews.
4. Lumiere Movies can create automatically tags and related tags' links according to the movies available in your webpage. Check plugin's help to figure out how to.

## Screenshots

### 1. Popup displayed when an imdb link is selected. In background, a widget [Popup and widget](https://ps.w.org/lumiere-movies/assets/screenshot-1.jpg)

[missing image]

### 2. How movie's data is displayed "inside a post" [Inside a post](https://ps.w.org/lumiere-movies/assets/screenshot-2.jpg)

[missing image]

### 3. How movie's data is displayed in a "widget" [widget](https://ps.w.org/lumiere-movies/assets/screenshot-3.jpg)

[missing image]

### 4. Admin preferences[Admin preferences](https://ps.w.org/lumiere-movies/assets/screenshot-4.jpg)

[missing image]

### 5. The field and the value to fill up to use the widget [Values for the Lumiere Movies widget](https://ps.w.org/lumiere-movies/assets/screenshot-5.jpg)

[missing image]

### 6. Option for adding a Lumiere Movies URL in their post [Lumiere Movies URL option](https://ps.w.org/lumiere-movies/assets/screenshot-6.jpg)

[missing image]

### 7. Piece of code needed in order to display movie's data "inside a post" [Lumiere Movies inside a post tag](https://ps.w.org/lumiere-movies/assets/screenshot-8.jpg)

[missing image]

### 8. Help section contains many tips and how-to [Help section](https://ps.w.org/lumiere-movies/assets/screenshot-9.jpg)

[missing image]


## Frequently Asked Questions

### How to use the plugin?

How to use Lumiere Movies is explained in the **How to** page of the plugin settings (so install the plugin first, then take a look at the section "Lumiere Movies help")

### Can I suggest a feature/report a bug regarding the plugin?

Of course, visit the [Lumiere Movies home](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumiere Movies home") or [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository"). Do not hesitate to share your comments and wishes. The plugin does more or less what I do need, but users can help me improve it.

### I don't want to have links to a popup window!

Look at "Widget/Inside post Options / Misc / Remove all links?" and switch the option to "yes". Links will not be displayed anymore, both for the "widget" (inside posts) and external links (like popups).

### I want to keep data forever on my disk/server

Look at "Cache management / Cache general options / Cache expire" and click on "never" to keep forever data download from IMDb. Be carefull with that option: changes made on IMDb will not be reflected in your cache. Should you want to keep your data forever, you have nevertheless the option to refresh a given movie only. Pay a visit to Cache options in order to delete/refresh a specific movie.

### Is it possible to add several movies to sidebar/inside my post?

Yes, just add in the post as many custom fields you want.

### Known issues

* When the imdb widget is put under another widget which display a list (ie, "recent posts" plugin), the widget won't display what it should. Actually, it won't display anything. **Workaround:** put the imdb widget one level above the widget calling a list.

* If you activate both "Display highslide popup" option and in [Next-Gen Gallery's](https://wordpress.org/plugins/nextgen-gallery/ "Next-Gen Gallery home") highslide effect option, NGG picture display will be broken. **Workaround:** Do not use "Display highslide popup" option or use another effect option for NGG.

## Support

Please visit [contact page](https://www.jcvignoli.com/blog/about "Lumiere Movies contact page")

## Changelog

Take a look at the [changelog](https://svn.wp-plugins.org/lumiere-movies/trunk/changelog.txt "latest changelog") to find out the latest developments. Or for even more recent changes, visit my [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository").

Major changes:

### 3.0

Major update, plugin vastly rewritten. Expected to be compliant with Content Security Policy. Too many changes to be listed. Check the [changelog](https://svn.wp-plugins.org/lumiere-movies/trunk/changelog.txt "latest changelog").

### 2.1.3

Changed the way to use highslide js (on Wordpress request, piece of code not GPL compliant); it is mandatory now to download the library from [Lumiere Movies website](https://www.jcvignoli.com/blog/wp-content/files/wordpress-lumiere-movies-highslide.zip "Lumiere Movies website") in order to get this damn cool window. Once the file downloaded, put the folder "highslide" into the "js" one and check general options in order to activate it

### 2.0.2

* Taxonomy considerably expanded
* added trailer's movie detail

### 2.0.1

* Added taxonomies

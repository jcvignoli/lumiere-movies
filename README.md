# Lumiere Movies

**Contributors:** psykonevro \
**Donate link:** https://www.paypal.me/jcvignoli and https://en.tipeee.com/lost-highway \
**Tags:** cinema, film, imdb, movie, actor \
**Requires at least:** 4.0 \
**Tested up to:** 5.7.1 \
**Stable tag:** 3.0.2 \
**Requires PHP:** 7.0 \
**License:** GPLv3 \
**License URI:** https://www.gnu.org/licenses/gpl-3.0.en.html

Lumière! Movies retrieves information from www.imdb.com into your blog. Cache management, widget available, this is the most versatile and comprehensive plugin to retrieve data from IMDb. 

## Description

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website").

"Lumière! Movies" is the continuation of [IMDb Link Transformer plugin](https://wordpress.org/plugins/imdb-link-transformer/ "IMDb Link Transformer") with more that 20'000 downloads. 

**Lumiere Movies** aims to ease the search for info on movies. All movies names which are tagged between < !--imdb-->nameMovie< !--/imdb--> are automatically turned into an url in your posts. On click, the link opens a new window  including much data related to the movie. Lumière! **transforms all the words you tagged into links to an informative windows**. It means one can view the filmmaker, the casting or the goofs that [IMDb](https://www.imdb.com "Internet movie database") (or similar) website includes with one click; it can show either the director or the movie related data (biography, filmography, miscellaneous related to the director; casting, goofs, AKA titles, crew and many others related to the movie). 

Many features are available in the wordpress editing interfaces (Gutenberg, Visual editor, and HTML editor).

You can also activate a Lumière! **widget**, which displays information parsed from IMDb straight to your sidebar. After widget activation, every time you add the key "imdb-movie-widget" in the custom field to your message *and* a movie's title in "value", the plugin will show the related information in your sidebar. Or **even simpler**: just add a widget, give a movie's name to your blog post and the widget will automatically retrieve the accurate info and display it in the widget!

Similarly, this plugin can display **many movie's related details inside a post** when putting an IMDb movie's ID inside [imdbltid][/imdbltid] tags or a movie's name inside [imdblt][/imdblt]. No widget needed here, the data related to a movie can be displayed anywhere inside posts.

**Lumière!** is a great tool that increases the value added of your posts. It is versatile and efficient. Bloggers can display movie details though many ways (with popups, a widget, and straight into a post). The plugin can be extensively fine-tuned with the admin options.

## Installation

### required

PHP 7 is required. PHP 8 will soon be mandatory.

1. Activate the plugin
2. Configure the plugin (in admin settings). Values are automatically filled.
3. Make sure the cache directories (cache and photo directories) have been created (in cache settings). The plugin is preconfigured to work with "/wp-content/plugins/lumiere-movies/cache/". The plugin will work with or without the cache.

### basic options

There are three ways to use Lumière!: 1/ with the popup link maker, 2/ with a widget and 3/ inside a post. Each option can be combined with any other; there is no limitation!

1. **Popup** When writing your post, add either < !--imdb-->movie's name< !--/imdb--> manually to your movie's name. Or use the visual buttons in Gutenberg or in the former Visual wordpress editor.A **link that opens a popup** will be created in your post. The popup contains much data about the movie.
2. **Widget** can be activated and used to display movie's data. Once the widget activated, select closely what you want to display on your sidebar: the options are available in the Lumière! admin settings. You can also add a custom field imdb-movie-widget" or "imdb-movie-widget-bymid" into your posts; add as a value the movie you're looking for (first case, 'imdb-movie-widget') or the IMDb id (second case, 'imdb-movie-widget-bymid'). Check the FAQs.
3. The plugin can **show IMDb data inside a post**. When writing your post, put the movie name inside tags [imdblt][/imdblt] so you get ie [imdblt]Fight club[/imdblt] in your post. Or better, use imdb movie's id instead of the name: [imdbltid]0137523[/imdbltid]. Check the FAQs.

### Fine tuning:

1. The files inc/imdb-movie.inc.php, popup-search.php, popup-movie.php and popup-person.php can be edited to match your theme.
2. A (front) page can be created to include all you movies' related messages. Have a look there : [movie review's page](https://www.jcvignoli.com/blog/critiques-de-cinema "Lost highway's movies reviews").
3. You may edit the "/* ---- imdbincluded */" section in css/lumiere.css file to customize the layout according to your taste. You can copy the lumiere.css file into your current template folder so your modification will make it in spite of Lumière!'s updates.

### Advanced

1. If you **do not want to show any link to open the popup windows** (in case you are only looking for information displayed in widget and inside posts), search for the option located in "widget options / Remove popup links?" and switch it to "yes". Links opening a popup (both in widget and posts) will not be be available anymore.
2. You may use lumiere_call_external() function for externals calls to imdb functions. Have a look at the help section in the admin.
3. Should you want to display automatically a widget according to the post's title, just switch on the "Widget/Inside post" option located in "Options -> Misc ->Auto widget" in the plugin admin. Usefull for blogs dedicated to sharing movie reviews.
4. Lumière! Movies can create automatically tags and related tags'. Taxonomy templates are provided. Check plugin's help to figure out how to.

## Screenshots

### 1. Popup displayed when an imdb link is selected. In background, a widget 

[missing image]

### 2. How movie's data is displayed "inside a post" 

[missing image]

### 3. How movie's data is displayed in a "widget" 

[missing image]

### 4. Admin preferences

[missing image]

### 5. The field and the value to fill up to use the widget 

[missing image]

### 6. Option for adding a Lumière! URL in their post 

[missing image]

### 7. Piece of code needed in order to display movie's data "inside a post"

[missing image]

### 8. Help section contains many tips and how-to

[missing image]

### 9. Gutenberg block

[missing image]


## Frequently Asked Questions

### How to use the plugin?

How to use Lumière! is explained in the **How to** page of the plugin settings (so install the plugin first, then take a look at the section "Lumière! Movies help")

### Can I suggest a feature/report a bug regarding the plugin?

Of course, pay a visit to the [Lumière! Movies home](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! Movies home") or [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository"). Do not hesitate to share your comments, glitches and wishes. The plugin does more or less what I need but users have helped me improve Lumière! a lot.

### I don't want to have links to a popup window!

Look at "Widget/Inside post Options / Misc / Remove all links?" and switch the option to "yes". Links will not be displayed anymore, both for the "widget" (inside posts) and external links (like popups).

### I want to keep data forever on my disk/server

Look at "Cache management / Cache general options / Cache expire" and click on "never" to keep forever data download from IMDb. Be carefull with that option: changes made on IMDb will not be reflected in your cache. Should you want to keep your data forever, you have nevertheless the option to refresh a given movie only. Pay a visit to Cache options in order to delete/refresh a specific movie.

### Is it possible to add several movies to sidebar/widget and inside my post?

Yes, just add in the post as many custom fields you want.

### Known issues

* If you activate both "Display highslide popup" option and in [Next-Gen Gallery's](https://wordpress.org/plugins/nextgen-gallery/ "Next-Gen Gallery home") highslide effect option, NGG picture display will be broken. **Workaround:** Do not use "Display highslide popup" option or use another effect option for NGG.

* If you are updating from the previous IMDb Link Transformer plugin, the plugin's option will be messed up. Reset Lumière! options.

## Support

Please visit [contact page](https://www.jcvignoli.com/blog/about "Lumière! Movies contact page")

## Changelog

Take a look at the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.txt "latest changelog") to find out the latest developments. Or for even more extensive and recent changes available at my [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository").

Major changes:

### 3.0

Major update, plugin vastly rewritten. Name IMDb Link Transformer changed to Lumière!. Should be Content Security Policy (CSP) compliant. Too many changes to be listed. Check the [changelog](http://svn.wp-plugins.org/lumiere-movies/trunk/CHANGELOG.txt "latest changelog").

### 2.1.3

Changed the way to use highslide js; it is mandatory now to download the library from [Lumière! website](https://www.jcvignoli.com/blog/wp-content/files/wordpress-lumiere-movies-highslide.zip "Lumiere Movies website") in order to get this damn cool window. Once the file downloaded, put the folder "highslide" into the "js" one and check general options in order to activate it

### 2.0.2

* Taxonomy considerably expanded
* added trailer's movie detail

### 2.0.1

* Added taxonomies

=== Lumière Movies ===
Contributors: psykonevro
Tags: cinema, film, imdb, movie, actor
Requires at least: 5.6
Tested up to: 6.7.1
Stable tag: 4.6.1
Requires PHP: 8.1
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.en.html
Donate link: https://www.paypal.me/jcvignoli

Lumière! Movies is a WordPress plugin that retrieves data from www.imdb.com and helps you include it in your posts and in your widgets.

== Description ==

Visit the [Official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Official website") to see how the plugin can enhance your website.

**Lumiere! Movies** helps you integrate loads of information about movies and stars in your blog. Widgets, links to informative popup, and dedicated taxonomy pages are available. Everything is automatised and no further configuration is required from the user. However, should you want to access advanced features, your can change the themes, add taxonomy to your pages, remove links, display automatically information according to your blog posts' titles, and use many hidden features. The information is retrieved from the popular [IMDb](https://www.imdb.com "Internet Movie Database") website. Lumière! ensures that you have the most accurate and reliable information always available on your blog.

It is [Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP "Content Security Policy on Mozilla") (CSP) compliant, takes advantage of Polylang plugin and is fully compatible with AMP plugin. It is also fully compatible with Classic Editor, Classic Widgets plugins, and Intelly related posts. It supports any WordPress theme and is adapted to [OceanWP](https://wordpress.org/themes/oceanwp/ "OceanWP theme") theme. 

**Lumière!** is a great tool to illustrate your articles. You can display movie and people details by several ways, such as in popups, widgets, and straight inside your posts. It can be extensively fine-tuned in the admin options panel.

== Installation ==

= normal use =

1. Activate the plugin
2. Should you want to fine-tune your blog, configure the plugin (in admin settings). Default values are automatically filled, no change is needed for normal use.
3. Activate a Lumière widget in the WordPress widget pages if you want to include movie or person information into your sidebars.
4. Write a post that includes information about your favourite movie using any of the tools smoothly incorporated into WordPress!

= basic options =

There are three ways to use Lumière!: 1/ with the popup link maker, 2/ with a widget, and 3/ inside a post. Each option can be combined with any other; there is no limitation.

1. **Popup** When writing your post, embed a movie's title using "Add IMDb Link" option. Select the movie's title you wrote and click on that option. After publishing your post, your text will be clickable and will open a popup with data about the movie! Technically, an invisible HTML span tag will embed the selected title such as: < span data-lum_link_maker "popup"> movie's title< /span> that usually you can't see except if you're editing in text mode. You can see if it worked by the little icon on the left of you selected text. Popups can be displayed using Bootstrap, Classic and Highslide modal windows (to be selected in Lumière! admin options).
2. **Widget** can be used to display movie's data or person's name related to a post. Go to widgets admin options and add a Lumière! widget in the sidebar you want to show information about movies or people. Once the widget is activated, you can add information about a movie or a person to your sidebar: when editing your blog post, a new widget will be displayed for your to enter either the movie's title/person's name  (that can lead to unexpected results) or the movie/person IMDb ID (this never fails in retrieving a movie/person) of the movie/person you want to be shown in the sidebar. If you don't know what the IMDb ID is, you can use the query link provided in Lumière! widget. Just search for the movie title/person name and you will find the IMDb ID.
3. The plugin can **show IMDb data inside a post**. Just add a Lumière block and enter a movie's title/person name or movie's/person's imdb ID. For the latter, in order to find the IMDb ID use the query tool provided in Lumière block (sidebar block). A similar tool is provided with classic WP editor in a form of dropdown menu. If you're writing your post with classic WP editor, use Lumière's bar tools to select the movie/person title: it will insert html tags around your selection, such as < span data-lum_movie_maker "movie_title">My movie's title/person's name< /span>. 

= Fine-tuning: =

1. Lumière! Movies can create virtual pages that include a list of movies identically tagged (known as taxonomy). Taxonomy templates are provided. Check plugin's help to figure out how to use that option. By default, Lumière's taxonomy system is activated for 'director' and 'genre' movies items.
2. You may edit the file assets/css/lumiere.css file to customize the layout according to your tastes. In order to keep your stylesheet changes through Lumière! updates, you will need to download an unminified lumiere.css from [Lumiere's GIT repository](https://github.com/jcvignoli/lumiere-movies/blob/master/src/assets/css/lumiere.css). After editing it, put it into your WordPress current template folder (a [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/ "Child Themes on WordPress") preferably, as it will get deleted by a template update otherwise). In so doing, your stylesheet modifications will be kept through Lumière!'s updates.

= Advanced =

1. If you **do not want Lumière to add any link** (in the case you are only looking for information displayed in widget and inside posts), look for an option located in "General options -> Advanced -> Remove popup links?" and select "yes". Links opening a popup (both in widget and posts) will not be be available anymore. Taxonomy links will remain active, though.
2. Should you want to display automatically a movie in your widget according to the post's title, just switch on the "Auto title widget" option located in "General Options -> Advanced -> Auto title widget" in the plugin admin options. Make sure you added a Lumière widget in "Appearence - Widgets". Usefull for blogs exclusively dedicated to movie reviews, where all posts' titles are named after movie's titles. You can prevent a post from displaying the widget by ticking in the post edition's the Lumiere option "Deactivate auto title widget for this post".
3. You may want to include a custom page in your blog that includes all your movie related articles. Have a look there : [Lost highway's movies reviews](https://www.jcvignoli.com/blog/critiques-de-cinema). Should you want to do the same, check Lumière's help in your administration interface.
4. Taxonomy pages and popups URLs can be edited according to your tastes. In advanced general Lumière options, you may want to modify the URL starting with 'lumiere' for taxonomy pages. Make sure to refresh your "rewriting rules" when adding new taxonomy (visit in your admin interface the page Permalink Settings (/wp-admin/options-permalink.php)
5. Should your blog be dedicated to TV shows, podcasts or videogames only, it is possible to change Lumière's search behaviour to retrieve exclusively those. In advanced general Lumière admin options, look for 'Search categories'.
6. Many more options are offered, just take a look at the options and how-to pages!

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

= I want to get rid of those links that open a popup! =

Look at "General Options -> Advanced -> Misc -> Remove all links?" and switch the option to "yes". Links will not be displayed anymore, both for the "widget" (inside posts) and external links (like popups).

= I want to keep data forever on my disk/server =

There are two ways:
1/ Use the automatized refresh of the cache function, a feature that will ensure that you cache is up to date forever by refreshing you current movie/people cache every two weeks. Go to "Cache management -> Cache general options -> Cache automatized functions" and tick "Cache auto-refresh" option. Selecting this option will remove the time expiration of the cache, which will be automatically set to forever.

2/ Keep the cache forever without refreshing it. Go to "Cache management -> Cache general options -> General options" and click on "never" in "Cache expire" to keep forever the downloaded data from IMDb. This means that changes made on IMDb will not be reflected anymore in your cache. Should you have selected that option, you can still delete/refresh any specific movie you want in the cache options. In most cases, previous option "Cache auto-refresh" should be prefered.

= Is it possible to add several movies to sidebar/widget and inside my post?  =

Although one widget only can be added per post, should you use the "auto widget" feature, you may display up to two movies on you sidebar: one automatically created according to your post's title, one you would have manually added in the sidebar (metabox post options).

Inside your posts, you can insert as many movies blocks as you want, there is no limitation. 

= Compatibility and incompatibility =

A list of plugins that are [compatible and incompatible with Lumière](https://github.com/jcvignoli/lumiere-movies/blob/master/src/COMPATIBILITY.md "Full list of plugins compatible and incompatible with Lumière") is available.

= How to integrate Lumière with Polylang plugin?  =

If [Polylang](https://wordpress.org/plugins/polylang/ "Polylang WordPress plugin") is installed, new features for taxonomy are added, such as a dropdown form for selecting the languages in taxonomy pages (such as https://yourblog.com/lumiere-director/stanley-kubrick/). Just selected which to data to turn into taxonomy, it will work out of the box.

= Is Lumière! CSP compliant? (for developpers)  =

[Content Security Policy](https://developer.mozilla.org/en-US/docs/Web/HTTP/CSP "Content Security Policy on Mozilla") (CSP) is a webserver based security avoiding injections to your pages. It greatly improves the security of your website.

Although it's difficult to make WordPress fully CSP compliant, Lumière is fully CSP compliant both for the admin and the frontend interfaces. Neither javascripts nor stylesheets are directly added inside HTML tags, and the plugin uses the standard WordPress system to add scripts and stylesheets.

In order to add a CSP nonce, it is advised to use the standard 'wp_script_attributes' and 'wp_inline_script_attributes' hooks to inject your nonces into scripts, or use a regex WordPress buffer technique to inject the style nonces.

= Can I change the size of the poster pictures?  =

Sure thing, just untick 'Display only thumbnail' in general admin options, untick "Display only thumbnail" and insert the size you want (in pixels) for the picture.

= Can I change the size of the popups?  =

Sure thing, just fill in the width and heigth in general admin options.

= Can I change the color themes of the popups or inside the posts?  =

Sure thing, just select one of the theme available in general admin options, either in 'plain page' or 'popup' sections.

= Can I add or remove the data details, such as director or year of release?  =

Sure thing, you can also modify the order of these details. Just take a look at data management options.

= How does the plugin complies with Privacy Policy, such as the GDPR? =

No data is sent to IMDb about end users. The website host does its own queries to the IMDb, without knowing who is visiting it.

Only the website owner is known from the IMDb, and must comply with the [IMDb privacy policy](https://www.imdb.com/privacy "Privacy policy on IMDb")
No data about the end user is sent to any other third party, so Lumière! is GDPR compliant. A short paragraph can be accessed in your admin privacy policy page, which can be added to your own privacy policy page.

= When accessing people/movie popups nothing is shown, a "404 Not Found" is thrown =

If you get a "404 Not Found" when accessing pages like "/lumiere/person/?mid=0000040", it is most certainely due to a webserver's misconfiguration. Make sure to add to .htaccess the following option, at the beginning of the file:

`Options Includes`

This value is supposed [to be included by default](https://httpd.apache.org/docs/2.4/mod/core.html#options "Apache Options directive") in your Apache configuration. You can take a look at [how to edit .htaccess](https://wpmudev.com/blog/htaccess/ "learn how to edit .htaccess for WordPress").

= Known issues =

* None

== Support ==

Go to [WordPress Support](https://wordpress.org/support/plugin/lumiere-movies/ "WordPress Support") for general issues, or to the [GIT repository](https://github.com/jcvignoli/lumiere-movies "GIT repository") for technical requests (developper-oriented).

It's always a good idea to look at the [official website](https://www.jcvignoli.com/blog/en/lumiere-movies-wordpress-plugin "Lumière! official website")

== Changelog == 

Recent list of changes is available on [GitHub](https://github.com/jcvignoli/lumiere-movies/commits/master "Lumière GitHub").

= 4.6 =

Person details (in addition to movie details) are now available. New admin panel options to manage this new feature.

= 4.5 =

Added trivia item. Plugin is now fully OOP.

= 4.4 =

PHP >= 8.1 is now required. New item: connected movies, quotes are back. Most work has been done under the hood, to have a faster and more maintenable plugin.

= 4.3 =

The base library is now IMDbGraphQL. Taxonomy deeply changed, forms in taxonomy pages (i.e. director, genre, etc) are working in AMP and with Polylang. Lot of bugs addressed, always more OOP.

= 4.1 =

Popup spinners to make you visitors wait, nicer popup layouts, faster taxonomy pages display, better support of Polylang if it's an AMP page. Fixed longstanding bugs.

More flexibility for the auto title widget users: You can now prevent a post from displaying the auto title widget by ticking in the post edition's the Lumiere option "Deactivate autowidget for this post".

== Upgrade Notice ==

= 4.4 =

Check after a few minutes if the display order of data is the same you want. You may have to fix it.

= 4.3.3 =

If you automatically refresh the cache, make sure to deactivate then reactivate the option in Lumière admin.

= 4.3.2 =

Taxonomy template in your folders (wp-content/theme/my_theme) will be automatically updated. Remove the line "* TemplateAutomaticUpdate..." in your user templates (ie, wp-content/theme/my_theme/taxonomy-lumiere-director.php) if you do not want your templates to be automatically updated.

= 4.3 =
Delete the cache, as the caching system has changed.


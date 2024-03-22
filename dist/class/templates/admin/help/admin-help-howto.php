<?php declare( strict_types = 1 );
/**
 * Template for the submenu of general options pages
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

// Retrieve vars from calling class.
$lumiere_pics_url = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

<div class="lumiere_wrap">

	<div class="lumiere_intro_title_options lumiere_border_shadow">
		<?php esc_html_e( 'This section covers the three main ways to display movie data and some related options in Lumière.', 'lumiere-movies' ); ?>
		<br><br>
		<?php esc_html_e( 'Lumiere Movies is a plugin under intense development; this help section might be innacurate with regard to the latest functions. Main functions of the plugin are explained below, aiming to be as much user-friendly as possible.', 'lumiere-movies' ); ?>
	</div>

	<div class="imdblt_double_container">

		<!--------------- How to use Popups -->

		<div class="postbox-container imdblt_double_container_content lumiere_flex_container_content_fourtyfive">
			<div class="helpdiv">

				<h4 data-show-hidden="inside_help_explain_popup" class="help_titles"><?php esc_html_e( 'Why a popup window?', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_popup" class="hidesection">

				<br clear="both">

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-1.jpg' ); ?>" alt="screenshot Link creator" /></a>

				<?php esc_html_e( 'The first way to use Lumiere Movies is to add links to movie titles that opens popups with information about that very same movies. It is a usefull for posts that mention movies title; just add a link to your movie title, and let visitors knowing more about the details of the movie you mention.', 'lumiere-movies' ); ?>

				<?php esc_html_e( "Popup is a window that opened on click, which allows to consult director's and movie's information; if browsing a movie, one can read more about the movie but also the people who took part in the movie, such as actors, directors, etc.", 'lumiere-movies' ); ?>

				<h4><?php esc_html_e( 'How to make a popup link', 'lumiere-movies' ); ?></h4>

				<?php
				/* translators: %1$s and %2$s are HTML tags */
				echo wp_kses( sprintf( __( 'To create a link to a popup window, you only need to put the %1$smovie\'s title%2$s inside dedicated tags. Depending on the visual interface you use (modern WordPress, wysiwig old WordPress, or pure text interface), you may add these tags in different ways.', 'lumiere-movies' ), '<b>', '</b>' ), [ 'b' => [] ] ); ?>

				 <br clear="both"><br>

				<?php esc_html_e( 'If you use a recent WordPress and have not activated a plugin to continue using the old editor interface, you can add a Lumière link to a popup by selecting the title of your movie, then adding the link with the dedicated contextual menu option:', 'lumiere-movies' ); ?>

				<br clear="both">

				<div align="center"><a href="<?php echo esc_url( $lumiere_pics_url . 'admin-help-addimdblink-gutenberg.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="80%" src="<?php echo esc_url( $lumiere_pics_url . 'admin-help-addimdblink-gutenberg.png' ); ?>" alt="add link in gutenberg" /></a></div>

				<br clear="both">

				<?php esc_html_e( "If you use an old WordPress version or a recent WordPress with the plugin 'classic editor' installed, you can access in the dedicated menu to 'add a popup link:", 'lumiere-movies' ); ?>

				<br clear="both">

				<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="80%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-7.jpg' ); ?>" alt="screenshot Link creator button added for bloggers who prefer HTML writing way" /></a></div>

				<br clear="both">

				<?php esc_html_e( "No explanation is need for those who prefer to write directly in HTML editor; it goes without saying they know how to add an HTML tag. But even in that interface a button is available for adding the code. For references, here is the HTML tag to wrap your movie's title with:", 'lumiere-movies' ); ?>

				<div align="center" clear="both">
					<pre>
					&lt;span data-lum_movie_maker="popup"&gt;
					movie's title
					&lt;/span&gt;
					</pre>
				</div>


				<?php esc_html_e( "Whatever the tool you prefer, when you add such a link a small icon confirming that the link is Lumière compliant is added to your movie's title.", 'lumiere-movies' ); ?>

				</div>
			</div>
			
			<!--------------- How to use Inside posts -->
				
			<div class="helpdiv">
				<h4 data-show-hidden="inside_help_explain_inside_post" class="help_titles"><?php esc_html_e( "Why display movie's data inside my post?", 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_inside_post" class="hidesection">

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-2.jpg' ); ?>" alt="<?php esc_html_e( 'Lumière! Movies widget', 'lumiere-movies' ); ?>" /></a>
				<?php esc_html_e( 'Including movie information within your article is quite useful; it can ingeniously illustrate your post, displays crucial data (directors, actors) and at the same time add links to further popups that include even more detailed information.', 'lumiere-movies' ); ?>

				<h4><?php esc_html_e( 'How to display data inside my post', 'lumiere-movies' ); ?></h4>


				<?php esc_html_e( "Lumière provides you with tools to add 'HTML tags' (span) to wrapp your movie's title when writting your article. These 'HTML tags' will be then converted into movie's details. In the same way as for for popups, three tools are provided depending upon your the WordPress interface you used to publish your posts. If you use the modern WordPress interface, a Lumière block is provided; just enter the movie's title or IMDb id, and you are done!", 'lumiere-movies' ); ?>

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img width="90%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-9.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies Inside a post using gutenberg block', 'lumiere-movies' ); ?>" /></a>

				<?php esc_html_e( 'You can add as many blocks as you whish; there is no limitation in the number of movies you can display per article.', 'lumiere-movies' ); ?>


				<h4><?php esc_html_e( 'How to display data inside my post - advanced users', 'lumiere-movies' ); ?></h4>

				<?php
				esc_html_e( "It could happen you don't want to use the previous solution to display movie's data. For exemple, if you wish to use Lumière outside a post (in a customised page), it won't work. Lumière is written around classes, so it is easy to achieve what you want.", 'lumiere-movies' );
				?>

				<br>

				<?php esc_html_e( "The function to be called is <strong>imdb_call_external ()</strong>. It has two parameters, and both are mandatory. The first is the movie's name, and the second take always 'external'. For exemple, one'd like to display 'The Descent' should call the function like this:", 'lumiere-movies' ); ?>

				<blockquote class="lumiere_bloquote_help imdblt_padding_left">
					$movieClass = new \Lumière\LumiereMovies;<br>
					$movieClass->lumiere_external_call('Descent', false, 'external');
				</blockquote>

				<?php esc_html_e( 'Should you want to call the function using an IMDb ID instead:', 'lumiere-movies' ); ?>

				<blockquote class="lumiere_bloquote_help imdblt_padding_left">
					$movieClass = new \Lumière\LumiereMovies;<br>
					$movieClass->lumiere_external_call(false, '0435625', 'external');
				</blockquote>


				<h4><?php esc_html_e( 'I want to get rid of thoses links opening popups', 'lumiere-movies' ); ?></h4>
				<?php esc_html_e( "It could happen you do not want popups at all. Since by default Lumière Movies adds links whenever relevant to movie's details inside your article, you may change that behaviour. In order to do so, look for 'General options / Advanced / Remove popup links' and uncheck the box. No links will be displayed, both for the widget and within your articles.", 'lumiere-movies' ); ?>
				</div>
			</div>

			<!--------------- How to use the Widget -->

			<div class="helpdiv">
				<h4 data-show-hidden="inside_help_explain_widget" class="help_titles"><?php esc_html_e( 'Why to use widget?', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_widget" class="hidesection">

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-3.jpg' ); ?>" alt="<?php esc_html_e( 'key and value for custom fields', 'lumiere-movies' ); ?>" /></a>

				<?php esc_html_e( 'Widgets are widely used in WordPress. It allows to select which information display in a given area, usually in the sidebar. Lumière allows to display any movie into your sidebar.', 'lumiere-movies' ); ?>

				<br clear="both">

				<h4><?php esc_html_e( 'How to use the widget', 'lumiere-movies' ); ?></h4>

				<?php
				echo wp_kses(
					/* translators: %1$s to %6$s are HTML tags */
					sprintf( __( '%3$sFirst%4$s, prior to WordPress 5.8, go to %5$swidget%6$s administration (%1$sappearance%2$s tab), drag %1$sLumière widget%2$s (from %1$sinactive widgets%2$s) to a sidebar, and modify the box\'s title (in case you don\'t want to have the default name). As of WordPress 5.8, widgets are blocks selected by the user, and adding them is very intuitive.', 'lumiere-movies' ), '<i>', '</i>', '<strong>', '</strong>', '<a href="widgets.php">', '</a>' ),
					[
						'i' => [],
						'strong' => [],
						'a' => [ 'href' => [] ],
					]
				); ?>

				<br>
				<br>
				<?php
				/** translators: %1$s and %2$s are HTML tags */
				echo wp_kses( sprintf( __( '<strong>Second</strong>, edit your post and add the name of the movie in the box to the sidebar on your right-hand. Lumiere Movies will automatically display in the widget the movie selected.', 'lumiere-movies' ), '<strong>', '</strong>' ), [ 'strong' => [] ] ); ?>

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-5.jpg' ); ?>" alt="<?php esc_html_e( 'Lumière metabox to add a movie in a widget', 'lumiere-movies' ); ?>" /></a>

				<br>
				<br>

				<?php esc_html_e( "As in many other sections of Lumière plugin, you can add the movie's IMDb id instead of the movie's title to make sure that the right movie is display. Should you want to find the movie's IMDb id, click on 'use this tool' and a new windows will be displayed; search for your movie, copy-paste its IMDb id into the sidebar, and select by 'movie id' in the dropdown list.", 'lumiere-movies' ); ?>

				<a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="left" width="50%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-8.jpg' ); ?>" alt="<?php esc_html_e( 'Lumiere Movies query interface', 'lumiere-movies' ); ?>" /></a>

				<br><br>

				<?php esc_html_e( "Using the movie's IMDb id allows more security: instead of searching for a title, Lumiere Movies can display directly the movie you are looking for. Very useful when your movie's name does not work as it should, due to movies with the same title, if a incorrect movie is displayed, etc.", 'lumiere-movies' ); ?>

				<br clear="both">

				<?php
				/* translators: %1$s and %2$s are HTML tags */
				echo wp_kses( sprintf( __( 'Get IMDb ids from links provided everywhere in the plugin interface. Even %1$shere%2$s.', 'lumiere-movies' ), '<a data-lumiere_admin_popup="openApopup">', '</a>' ), [ 'a' => [ 'data-lumiere_admin_popup' => [] ] ] );
				?>
				</div>
			</div>
			
			<!--------------- How to add a search form -->

			<div class="helpdiv">
				<h4 data-show-hidden="inside_help_explain_addsearchform" class="help_titles"><?php esc_html_e( 'How to add a search function for movies in my page? (advanced users)', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_addsearchform" class="hidesection">

				<?php esc_html_e( 'It is doable. Lumière is versatile enough to handle this function. With the help of a form, you can add a query field to search for every movie on your blog. Here is the code:', 'lumiere-movies' ); ?>

				<blockquote class="lumiere_bloquote_help imdblt_align_left">
					&lt;form action="" method="post" method="get" id="searchmovie" &gt<br>
						&lt;div&gt Search a movie: &lt;/div&gt<br>
						&lt;input type="text" id="moviesearched" name="moviesearched" &gt<br>
						&lt;input type="submit" value="Go"&gt<br>
					&lt;/form&gt<br>
				</blockquote>

				<?php esc_html_e( 'Then the PHP code:', 'lumiere-movies' ); ?>

				<blockquote class="lumiere_bloquote_help imdblt_align_left">
					&lt;?php<br>
					if (class_exists("\Lumiere\Settings")) {<br>
						$config_class = new \Lumiere\Settings();<br>
						// Get the type of search: movies, series, games<br>
						$typeSearch = $config_class->lumiere_select_type_search();<br>
					}<br>
					<br>
					# Initialization of IMDBphp libraries<br>
					$search = new \Imdb\TitleSearch($config_class );<br>

					if ( (isset ($_GET["moviesearched"])) && (!empty ($_GET["moviesearched"])) ){<br>
						$search_sanitized = isset($_GET["moviesearched"]) ? sanitize_text_field( $_GET["moviesearched"] ) : NULL;<br>
						$results = $search->search ($search_sanitized, $typeSearch );<br>
					}<br>
					<br>
					foreach ($results as $res) {<br>
						echo "\n\t&lt;div class='lumiere_container_flex50 lumiere_italic lumiere_gutenberg_results'&gt".esc_html( $res->title() )." (".intval( $res->year() ).")".'&lt;/div&gt';<br>
					}<br>
				</blockquote>

				<?php esc_html_e( 'It perfectly fits in your sidebar, for example.', 'lumiere-movies' ); ?>
				
				</div>
			</div>
		</div>

		<div class="postbox-container imdblt_double_container_content lumiere_flex_container_content_fourtyfive">

			<!--------------- How to keep css modifications -->
			
			<div class="helpdiv">
				<h4 data-show-hidden="inside_help_explain_keepcss" class="help_titles"><?php esc_html_e( 'How to ensure that my css edits remain through plugin updates?', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_keepcss" class="hidesection">

				<?php esc_html_e( 'Are you tired of losing your carefully hand-made CSS edits at every Lumière! update? There is a solution.', 'lumiere-movies' ); ?>

				<br clear="both">
				<br clear="both">

				<?php
				/* translators: %1$s and %2$s are HTML tags */
				echo wp_kses( sprintf( esc_html__( 'Any modification of the stylesheet you make should be done in your template folder rather than by editing lumiere-movies/css/lumiere.css. Download %1$sunminified css%2$s from the GIT repository, and edit that very file so it suits your needs. Then copy the edited file into you template folder: that file will superseed the plugin\'s one. Whenever you will update, your template\'s file will remain untouched and your edits will make it. Just make sure you are using a child theme, otherwise your customised lumiere.css will be deleted at the next template update.', 'lumiere-movies' ), '<a href="https://github.com/jcvignoli/lumiere-movies/blob/master/src/assets/css/lumiere.css">', '</a>' ), [ 'a' => [ 'href' => [] ] ] ); ?>
				</div>
			</div>
			
			<!--------------- How to use taxonomy -->
			
			<div class="helpdiv">
				<h4 data-show-hidden="inside_help_explain_taxonomy" class="help_titles"><?php esc_html_e( "What is WordPress' taxonomy?", 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_taxonomy" class="hidesection">

				<?php esc_html_e( 'Since WordPress 2.3, WordPress users can use taxonomy.', 'lumiere-movies' ); ?>

				<?php esc_html_e( "Taxonomy is a feature adding an extra layer of category in addition to the already existing Categories and Tags; in other words, it is like having species (a block named 'genre') and subspecies (few words describing the genre, like 'Adventure', 'Terror'). It is not fundamentaly different from Categories and Tags, but it is extremly useful for Lumière Movies.", 'lumiere-movies' ); ?>

				<h4><?php esc_html_e( 'How to use taxonomy in WordPress?', 'lumiere-movies' ); ?></h4>

				<a href="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-details.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="40%" src="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-details.png' ); ?>" alt="taxonomy details"></a>

				<?php esc_html_e( "Taxonomy is automatically generated in Lumière! and already activated. You can however disable it by unchecking the box in 'General options -> Advanced -> 'Use taxonomy'. Note that since taxonomy is related to movie details (such as directors, actors, etc), the movie detail you want to be used as taxonomy has to be also activated in 'Data -> Display'.", 'lumiere-movies' ); ?>
				<br>
				<?php esc_html_e( 'After that, pay a visit to your post or page; on the first refresh, links to taxonomy pages will be created. ', 'lumiere-movies' );
				/* translators: %s is an admin URL */
				echo wp_kses( sprintf( esc_html__( 'Important: you need to go to %1$s Permalink Settings %2$s to refresh the rewriting rules, otherwise you will get a page not found error (404).', 'lumiere-movies' ), '<a href="options-permalink.php">', '</a>' ), [ 'a' => [ 'href' => [] ] ] );
				esc_html_e( 'You can now visit pages that include all your posts grouped by movie details. For instance, if you write a lot about the same movie director, the taxonomy page will include all your posts written about them.', 'lumiere-movies' ); ?>

				<h4><?php esc_html_e( "New option in 'Posts' menu", 'lumiere-movies' ); ?></h4>

				<a href="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-postlist.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="left" width="30%" src="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-postlist.png' ); ?>" alt="taxonomy new options" /></a>

				<?php esc_html_e( "Once you have published your first article including a movie (both widget and inside a post trigger that function) WordPress will display the new taxonomy under 'posts' admin section. Depending on the movie details you have selected, you will find them in the menu.", 'lumiere-movies' ); ?>
				<br>

				<h4><?php esc_html_e( 'Advanced: Customize your theme for your taxonomy pages', 'lumiere-movies' ); ?></h4>

				<?php esc_html_e( "To fully enjoy this taxonomy, make sure you copy the template files located in the Lumière! movies folder 'lumiere-movies/theme/' into your 'theme' folder. This can be automatized by using the options available in Lumière! taxonomy :", 'lumiere-movies' ); ?>

				<br>

				<a href="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-copytemplate.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="right" width="40%" src="<?php echo esc_url( $lumiere_pics_url . 'admin-taxonomy-copytemplate.png' ); ?>" alt="taxonomy new options" /></a>

				<?php esc_html_e( "Click on 'copy template', that's it! You will be notified when a new template is available. You can customize even further the template that was copied into your template to match your specific needs. By default, the new taxonomy template will show you the person/item and the posts and pages mentioning them:", 'lumiere-movies' ); ?><br>

				<br clear="both">
				<br clear="both">

				<div align="center"><a href="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="center" width="40%" src="<?php echo esc_url( \Lumiere\Settings::LUMIERE_WORDPRESS_IMAGES . '/screenshot-10.jpg' ); ?>" alt="taxonomy result" /></a></div>

				<br clear="both">
				</div>
			</div>

			<!--------------- How to use auto widget -->

			<div class="helpdiv">

				<h4 data-show-hidden="inside_help_explain_autowidget" class="help_titles"><?php esc_html_e( 'What is Lumière auto-widget?', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_autowidget" class="hidesection">

					<?php esc_html_e( "It is a special type of widget. Unlike the normal Lumière widget (see widget help section in this page), Lumière auto-widget does not require you to enter any IMDb ID or movie's title manually. It automatically query the IMDb according to title you gave to your post. Beware it does so for all posts you have published.", 'lumiere-movies' );
					echo '<br><br>';
					?>

					<h4><?php esc_html_e( 'When should you use auto-widget?', 'lumiere-movies' ); ?></h4>

					<?php esc_html_e( "Should you have hundreds of posts named after movie's title, and that you don't want to edit them all to manually insert  widgets or a Lumière blocks inside the post. Lumière does everything for you.", 'lumiere-movies' ); ?>
					
					<br>
					<br>

					<h4><?php esc_html_e( 'How to use auto-widget?', 'lumiere-movies' ); ?></h4>

					<?php
					/* translators: %s are HTML URL tags */
					echo wp_kses( sprintf( esc_html__( 'Add a Lumières!\'s %1$s widget %2$s to your sidebar, and go to "General Options / Advanced" and check "Auto widget" option.', 'lumiere-movies' ), '<a href="widgets.php">', '</a>' ), [ 'a' => [ 'href' => [] ] ] );
					?>

					<div align="center">
						<a href="<?php echo esc_url( $lumiere_pics_url . 'auto-widget.png' ); ?>" title="<?php esc_html_e( 'click to get a larger picture', 'lumiere-movies' ); ?>"><img align="center" width="80%" src="<?php echo esc_url( $lumiere_pics_url . 'auto-widget.png' ); ?>" alt="auto widget option" /></a>
					</div>

					<?php esc_html_e( 'Next time you will look at your post, you will find the widget according to your post’s title.', 'lumiere-movies' ); ?>
					<br>
					<br>
					<?php
					/* translators: %s are HTML URL tags */
					echo wp_kses( sprintf( __( 'Notice: in order to have this feature work, you must add a widget using %1$sWidget Page%2$s option. Take a look at the "Widget" section of this "how to" page.', 'lumiere-movies' ), '<a href="widgets.php">', '</a>' ), [ 'a' => [ 'href' => [] ] ] ); ?>
					
					<br><br>
					
					<?php
					esc_html_e( 'Known issue: Lumière offers widgets for both old and modern type of widgets. Old widgets (aka Legacy Widgets) are available if you installed a Classic Widget plugin or any plugin that simplified the way to display your widgets and posts, or if your WordPress install is prior to WordPress 5.8. Modern Widgets (aka Block Widgets) are standard in every new WordPress install as of 5.8 that does not include a Classic Widget plugin. Potential issues could arise should you install both types of Lumière widgets, namely Legacy and Block Widgets. In order to prevent such issues, make sure that both Block and Legacy Widgets are not activated together. Remove any Lumière Block Widget previously added if you use Lumière Legacy Widget when using the pre-5.8/Classic Editor. In the pre-5.8/Classic Widget Editor, no option to add a Block Widget is available, but you may find a Block based Widget previously added in Block Editor, which you need to remove.', 'lumiere-movies' ); ?>
					
					<br><br>
					
					<?php
					esc_html_e( 'Should you switch back to the new WordPress Block Editor standard again (by updating WordPress or uninstalling Classic Widget), remove the Legacy widget that will be visible in the block Editor, save, refresh and add a new Lumière Block Widget. In new Widget WordPress installs, only the standard Block Widget is available.', 'lumiere-movies' ); ?>
					
				</div>
			</div>

			<!--------------- How to use search options -->
			
			<div class="helpdiv">

				<h4 data-show-hidden="inside_help_explain_searchoptions" class="help_titles"><?php esc_html_e( 'Changing default IMDb search options', 'lumiere-movies' ); ?></h4>

				<div id="inside_help_explain_searchoptions" class="hidesection">

				<?php esc_html_e( "Lumière queries the IMDb for every movie or person you are looking for. You can modify the results from the IMDb displayed in your blog will tweaking the search options available in menu options 'General / Advanced'.", 'lumiere-movies' ); ?>

				<br clear="both">
				<br clear="both">

				<?php esc_html_e( 'Three options are available: search language, search categories and the limit of the number of results.', 'lumiere-movies' );?>

				<br clear="both">
				<br clear="both">
				<?php esc_html_e( "1. Search language allows to modify the language of the query. This is not the language of the output: the details will always be returned in English by the IMDb. What this options modifies is the lanuage query only; if the query is run in French, movie's original title will be first returned instead of the English title.", 'lumiere-movies' ); ?>

				<br clear="both">
				<br clear="both">
				<?php esc_html_e( "2. Search category allows to modify the type of material your searching. If your blog is about series only, select in the dropdown list 'series only'. If it is about videogames, select the option accordingly.", 'lumiere-movies' ); ?>

				<br clear="both">
				<br clear="both">

				<?php esc_html_e( '3. It is possible to limit the number of results in the queries using its dedicated option. The less results there is, the less server resources are required and the faster the output is displayed. This limit number applies to the search of movies with a similar name (menu option in movies popups) and in ', 'lumiere-movies' );

				/* translators: %1$s and %2$s are replaced with HTML tags */
				echo wp_kses( sprintf( __( '%1$sthe admin tool of queries to find IMDb id%2$s.', 'lumiere-movies' ), '<a href="' . esc_url( admin_url( \Lumiere\Settings::GUTENBERG_SEARCH_URL_STRING ) ) . '">', '</a>' ), [ 'a' => [ 'href' => [] ] ] );
				?>

				</div>
			</div>

		</div>
	</div>
</div>

<?php declare( strict_types = 1 );
/**
 * Template for the advanced options of main admin page
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

$lumiere_imdb_admin_values = get_option( \Lumiere\Config\Get_Options::get_admin_tablename() );
?>
<div class="lumiere_wrap">
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<div class="lumiere_intro_title_options"><?php esc_html_e( 'The options hereafter can break a lot of things. Edit them only if you know what you are doing.', 'lumiere-movies' ); ?></div>
	
	<!-- ---------------------------------------------------------------- =[Search]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 class="hndle" id="searchpart" name="searchpart"><?php esc_html_e( 'Search', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_display_flex lumiere_flex_make_responsive">

			<div class="lumiere_flex_one lumiere_padding_ten">

				<label class="lumiere_display_block lumiere_labels" for="imdb_imdblanguage"><?php esc_html_e( 'Search language', 'lumiere-movies' ); ?></label>
				<select id="imdb_imdblanguage" name="imdb_imdblanguage">
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdblanguage'] === 'US' ) {
						echo 'selected="selected" ';
					}
					?>
					value="US"><?php esc_html_e( 'English', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdblanguage'] === 'FR' ) {
						echo 'selected="selected"';}
					?>
					value="FR"><?php esc_html_e( 'French', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdblanguage'] === 'DE' ) {
						echo 'selected="selected"';}
					?>
					value="DE"><?php esc_html_e( 'German', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdblanguage'] === 'ES' ) {
						echo 'selected="selected"';}
					?>
					value="ES"><?php esc_html_e( 'Spanish', 'lumiere-movies' ); ?></option>
				</select>

				<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumière! as a widget.', 'lumiere-movies' ); ?>
					<br>
					<br>
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "English"
				</div>
			</div>

			<div class="lumiere_flex_one lumiere_padding_ten">

				<label class="lumiere_display_block lumiere_labels" for="imdb_imdbseriemovies"><?php esc_html_e( 'Search categories', 'lumiere-movies' ); ?></label>
				<select id="imdb_imdbseriemovies" name="imdb_imdbseriemovies">
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdbseriemovies'] === 'movies+series' ) {
						echo 'selected="selected"';
					}
					?>
					value="movies+series"><?php esc_html_e( 'Movies and series', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdbseriemovies'] === 'movies' ) {
						echo 'selected="selected"';
					}
					?>
					value="movies"><?php esc_html_e( 'Movies only', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdbseriemovies'] === 'series' ) {
						echo 'selected="selected"';}
					?>
					value="series"><?php esc_html_e( 'Series only', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdbseriemovies'] === 'videogames' ) {
						echo 'selected="selected"';}
					?>
					value="videogames"><?php esc_html_e( 'Video games only', 'lumiere-movies' ); ?></option>
					<option 
					<?php
					if ( $lumiere_imdb_admin_values['imdbseriemovies'] === 'podcasts' ) {
						echo 'selected="selected"';}
					?>
					value="podcasts"><?php esc_html_e( 'Podcasts only', 'lumiere-movies' ); ?></option>
				</select>

				<div class="explain"><?php esc_html_e( 'What type to use for the search, such as movies, series (for TV Shows), and videogames.', 'lumiere-movies' ); ?>
					<br>
					<br>
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "Movies and series"
				</div>
			</div>

			<div class="lumiere_flex_one lumiere_padding_ten">

				<label class="lumiere_display_block lumiere_labels" for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit results', 'lumiere-movies' ); ?></label>

				<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php echo intval( $lumiere_imdb_admin_values['imdbmaxresults'] ); ?>" />

				<div class="explain">
					<?php esc_html_e( 'Limit of the number of results in a movie query. That limitation will impact the number of movies shown in the popup with movies and director search results.', 'lumiere-movies' ); ?>
					<br>
					<br>
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "10"
				</div>
			</div>
			
			<div class="lumiere_flex_one lumiere_padding_ten">

				<label class="lumiere_display_block lumiere_labels" for="imdb_imdbdelayimdbrequest"><?php esc_html_e( 'Delay queries time', 'lumiere-movies' ); ?></label>

				<input type="text" name="imdb_imdbdelayimdbrequest" id="imdb_imdbdelayimdbrequest" size="5" value="<?php echo intval( $lumiere_imdb_admin_values['imdbdelayimdbrequest'] ); ?>" />

				<div class="explain">
					<?php esc_html_e( 'Add an extra delay in seconds to avoid IMDb website throwing HTTP 504 errors (too many requests).', 'lumiere-movies' ); ?>
					<br>
					<br>
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "0"
				</div>
			</div>
		</div>
	</div>
	
	<!-- ---------------------------------------------------------------- =[Other plugins]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 class="hndle" id="otherpluginspart" name="otherpluginspart"><?php esc_html_e( 'Interaction with third-party plugins', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_flex_container">
		

			<div id="imdbirpdisplay" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Always display Intelly Related Posts', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbirpdisplay_no" name="imdb_imdbirpdisplay" value="0" />

				<input type="checkbox" id="imdb_imdbirpdisplays_yes" name="imdb_imdbirpdisplay" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbirpdisplay'] === '1' ) {
					echo 'checked '; }
				?>
				/>

				<div class="explain">
				<?php
				echo wp_kses(
					wp_sprintf(
						/* translators: %1$s and %2$s are HTML A tag */
						__( 'By default, Lumière deactivates %1$sIntelly Related Post plugin%2$s on posts that display Lumiere movies. You can overrides this Lumière feature and always display IRP instead, even if a movie is displayed in your post.', 'lumiere-movies' ),
						'<a target="_blank" href="https://wordpress.org/plugins/intelly-related-posts/">',
						'</a>'
					),
					[
						'a' => [
							'href' => [],
							'target' => [],
						],
					]
				); ?>
				<br><br>
				<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>&nbsp;<?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>
			</div>
		</div>
	</div>
	
	<!-- ---------------------------------------------------------------- =[Behaviour]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 class="hndle" id="behaviourpart" name="behaviourpart"><?php esc_html_e( 'Special features', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_flex_container">

			<div id="imdbtaxonomy" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Use taxonomy', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="0" />

				<input type="checkbox" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbtaxonomy'] === '1' ) {
					echo ' checked'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'This will add taxonomy terms found for the movie when display a page with a widget or a into a post. Taxonomy allows to group posts by a series of chosen terms, as explained in', 'lumiere-movies' ); ?> <a href="https://developer.wordpress.org/themes/basics/categories-tags-custom-taxonomies/">taxonomy</a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin if you selected not to keep the settings upon uninstall.', 'lumiere-movies' ); ?> <br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?> <?php esc_html_e( '(Activated automatically for "genre" and "director" taxonomies upon installation)', 'lumiere-movies' ); ?></div>

			</div>

			<div id="imdblinkingkill" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Remove all links?', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="0" />

				<input type="checkbox" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdblinkingkill'] === '1' ) {
					echo 'checked'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Remove all links added by Lumière except those for taxonomy. This option will remove every single HTML displayed in widget and into the post. Usefull for users who are not interested in popup function, but want to keep information displayed in a widget or inside the.', 'lumiere-movies' );
				?>
				<br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

			</div>

			<div id="imdbautopostwidget" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Auto title widget?', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="0" />

				<input type="checkbox" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbautopostwidget'] === '1' ) {
					echo 'checked'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Add automatically a widget according to your post title. If regular widgets have been added to post too, the auto title widget will be displayed before them. Usefull if blog a lot about movies; if a query does not bring any result with the post title, nothing is displayed.', 'lumiere-movies' ); ?><br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>&nbsp;<?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

			</div>
		
		</div>
	</div>
	
	<!-- ---------------------------------------------------------------- =[Admin]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 class="hndle" id="miscpart" name="miscpart"><?php esc_html_e( 'Administration', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<div class="lumiere_flex_container">

			<div id="imdbwordpress_bigmenu" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Left menu for Lumière options', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="0" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbwordpress_bigmenu'] === '0' ) {
					echo 'checked'; }
				?>
				/>

				<input type="checkbox" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) {
					echo 'checked'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'If enabled, Lumiere options are displayed in a dedicated menu on the left panel instead of being displayed in the settings menu.', 'lumiere-movies' ); ?> <br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

			</div>
			<div id="imdbwordpress_tooladminmenu" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Top menu for Lumière options', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="0" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbwordpress_tooladminmenu'] === '0' ) {
					echo 'checked '; }
				?>
				/>

				<input type="checkbox" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
					echo 'checked '; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'If activated, Lumière options are displayed in a top menu. Not recommended if you have many plugins occupying that area already.', 'lumiere-movies' ); ?> <br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

			</div>


			<div id="imdbkeepsettings" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Keep settings upon uninstall', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbkeepsettings_no" name="imdb_imdbkeepsettings" value="0" />

				<input type="checkbox" id="imdb_imdbkeepsettings_yes" name="imdb_imdbkeepsettings" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbkeepsettings'] === '1' ) {
					echo 'checked '; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Whether to keep or delete Lumière! settings upon plugin uninstall. When unticked, uninstalling this plugin will delete all taxonomy terms, Lumière databases, taxonomy templates in your theme folder, and your cache folder. For the safety of your WordPress install, metaboxes data in your posts, custom data fields in your posts and Lumière widget added will never be removed.', 'lumiere-movies' ); ?><br><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>&nbsp;<?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

			</div>

			<div id="imdbdebug" class="lumiere_flex_auto lumiere_padding_fifteen">

				<?php esc_html_e( 'Debug Lumière!', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbdebug_no" class="activatehidesectionRemove" name="imdb_imdbdebug" value="0" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbdebug'] === '0' ) {
					echo 'checked'; }
				?>
				/>

				<input type="checkbox" id="imdb_imdbdebug_yes" class="activatehidesectionAdd" name="imdb_imdbdebug" value="1" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbdebug'] === '1' ) {
					echo 'checked'; }
				?>
				/>

				<label for="imdb_imdbdebug_yes"></label>
				<br>
				<br>
				<div class="explain"><?php esc_html_e( 'Use integrated debugging functions.', 'lumiere-movies' ); ?></div>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five hidesectionOfCheckbox">


				<?php esc_html_e( '[Extra debugging options]', 'lumiere-movies' ); ?><br><br>

				<div class="lumiere_padding_top_bottom_ten">
					<?php esc_html_e( 'Debug verbosity', 'lumiere-movies' ); ?>&nbsp;

					<select name="imdb_imdbdebuglevel">
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'DEBUG' ) {
							echo ' selected="selected" ';}
						?>
						value="DEBUG">Debug</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'INFO' ) {
							echo ' selected="selected" ';}
						?>
						value="INFO">Info</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'NOTICE' ) {
							echo 'selected="selected"';}
						?>
						value="NOTICE">Notice</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'WARNING' ) {
							echo 'selected="selected"';}
						?>
						value="WARNING">Warning</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'ERROR' ) {
							echo 'selected="selected"';}
						?>
						value="ERROR">Error</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'CRITICAL' ) {
							echo 'selected="selected"';}
						?>
						value="CRITICAL">Critical</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'ALERT' ) {
							echo 'selected="selected"';}
						?>
						value="ALERT">Alert</option>
						<option 
						<?php
						if ( $lumiere_imdb_admin_values['imdbdebuglevel'] === 'EMERGENCY' ) {
							echo 'selected="selected"';}
						?>
						value="EMERGENCY">Emergency</option>
					</select>
					<div class="explain"><?php esc_html_e( 'From lowest to highest verbosity level.', 'lumiere-movies' ); ?></div>
				</div>

				<div class="lumiere_padding_top_bottom_ten">
					<?php esc_html_e( 'Display debug on screen', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbdebugscreen_no" name="imdb_imdbdebugscreen" value="0" />

					<input type="checkbox" id="imdb_imdbdebugscreen_yes" name="imdb_imdbdebugscreen" value="1" 
					<?php
					if ( $lumiere_imdb_admin_values['imdbdebugscreen'] === '1' ) {
						echo ' checked '; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Show the debug log on screen (for administrators only).', 'lumiere-movies' ); ?></div>
				</div>

				<div class="lumiere_padding_top_bottom_ten">
					<?php esc_html_e( 'Save logs', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbdebuglog_no" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="0" />

					<input type="checkbox" id="imdb_imdbdebuglog_yes" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="1" 
					<?php
					if ( $lumiere_imdb_admin_values['imdbdebuglog'] === '1' ) {
						echo ' checked '; }
					?>
					/>

					<div id="imdb_imdbdebuglogpath_id" class="lumiere_padding_top_bottom_ten" >
						<label for="imdb_imdbdebuglogpath"><?php esc_html_e( 'Path', 'lumiere-movies' ); ?></label>
						<input class="lumiere_border_width_medium imdb_imdbdebuglogpath" type="text" id="imdb_imdbdebuglogpath" name="imdb_imdbdebuglogpath" value="<?php echo esc_attr( $lumiere_imdb_admin_values['imdbdebuglogpath'] ); ?>" >

						<div class="explain"><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'WordPress default debug log', 'lumiere-movies' ); ?></div>
					</div>
				</div>
			</div>
	
		</div>
	</div>

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">
	
		<div class="lumiere_options_intro_inblock"><?php esc_html_e( 'Edit the following values with caution. It can have unattended effects on your WordPress installation.', 'lumiere-movies' ); ?></div>
		<br>
		<br>

		<!-- ----------------------------------------------------------------=[ URL Popups ]=- -->
		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdburlpopups"><?php esc_html_e( 'URL for the popups', 'lumiere-movies' ); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center">
					<?php echo esc_url( get_site_url() ); ?>
					<input type="text" class="lumiere_border_width_medium" id="imdb_imdburlpopups" name="imdb_imdburlpopups" value="<?php echo esc_html( $lumiere_imdb_admin_values['imdburlpopups'] ); ?>">
				</div>
				<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the movies\' and people\'s popups. Cannot be empty or limited to root "/".', 'lumiere-movies' ); ?>
				<br>
				<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo '/lumiere/'; ?>"
				<br>
				<br>
				<?php esc_html_e( 'Example: the full URL utilized for the movies\' popups will be:', 'lumiere-movies' ); ?>
				<br>
				<?php echo esc_url( get_site_url() . $lumiere_imdb_admin_values['imdburlpopups'] . 'film' ); ?>
				<br>
				<br>
				<?php esc_html_e( 'Example: the full URL utilized for the people\'s popup will be:', 'lumiere-movies' ); ?>
				<br>
				<?php echo esc_url( get_site_url() . $lumiere_imdb_admin_values['imdburlpopups'] . 'person' ); ?>
				</div>
			</div>
		</div>

		<br>
		<br>

		<!-- ----------------------------------------------------------------=[ URL Taxonomy ]=---- -->
		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdburlstringtaxo"><?php esc_html_e( 'URL for the taxonomy pages', 'lumiere-movies' ); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center lumiere_padding_top_bottom_ten">
					<?php echo esc_url( get_site_url() ); ?>/
					<input type="text" class="lumiere_border_width_medium" id="imdb_imdburlstringtaxo" name="imdb_imdburlstringtaxo" value="<?php echo esc_html( $lumiere_imdb_admin_values['imdburlstringtaxo'] ); ?>">
					&nbsp;&nbsp;
					<input type="checkbox" id="imdb_imdburlstringtaxo_terms" name="imdb_imdburlstringtaxo_terms" value="1" data-confirm="Existing taxonomy will not be updated." checked>
					<label for="imdb_imdburlstringtaxo_terms"><?php esc_html_e( 'Update also taxonomy terms (may be resource intensive)', 'lumiere-movies' ); ?></label>
				</div>
				<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the taxonomy\'s pages.', 'lumiere-movies' ); ?> <?php esc_html_e( 'Warning! This URL cannot be identical to popup\'s URL above.', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Warning again! Depending on your host and the number of taxonomies you are using, editing this URL may be extremeley resource intensive. You may need to reload the page several times.', 'lumiere-movies' ); ?>
				<br>
				<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo 'lumiere-'; ?>"
				<br>
				<br>
				<?php esc_html_e( 'The full URL utilized for the director taxonomy page will be:', 'lumiere-movies' ); ?>
				<br>
				<?php echo esc_url( get_site_url() . '/' . $lumiere_imdb_admin_values['imdburlstringtaxo'] . 'director' ); ?>
				</div>
			</div>
		</div>

		<br>
		<br>
		
		<!-- ----------------------------------------------------------------=[ Plugins path ]=---- -->
		<div id="imdb_imdbpluginpath_id" class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_twenty">
				<label for="imdb_imdbpluginpath"><?php esc_html_e( 'Lumière! path', 'lumiere-movies' ); ?></label>
			</div>
			<div class="lumiere_flex_container_content_eighty">
				<div class="lumiere_align_items_center">
					<input class="lumiere_border_width_medium imdbpluginpath" type="text" id="imdb_imdbpluginpath" name="imdb_imdbpluginpath" value="<?php echo esc_attr( $lumiere_imdb_admin_values['imdbpluginpath'] ); ?>" >
				</div>
				<div class="explain"><?php esc_html_e( 'In most cases, you should not edit it. Only advanced users should change this value.', 'lumiere-movies' ); ?> <?php
				esc_html_e( 'The path must end with a final slash.', 'lumiere-movies' );
				echo '<br>';
				esc_html_e( 'Unless you changed your environment or use multisite WordPress, Lumière! path should be: ', 'lumiere-movies' );
				echo esc_html( WP_PLUGIN_DIR ) . '/lumiere-movies/';?></div>
			</div>
		</div>
	</div>
	
	<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
		<?php wp_nonce_field( 'lumiere_nonce_main_settings', '_nonce_main_settings' ); ?>
		<input type="submit"  id="lumiere_update_main_settings" class="button-primary" name="lumiere_update_main_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
		<input type="submit" id="lumiere_reset_main_settings" class="button-primary" name="lumiere_reset_main_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
	</div>

	</form>
</div>

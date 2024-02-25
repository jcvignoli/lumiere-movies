<?php declare( strict_types = 1 );
/**
 * Cache options class
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Tools\Utils;
use Lumiere\Admin\Cache_Tools;

/**
 * Display cache admin menu
 * @since 3.12 Methods extracted from this class to cache tools and factorized there, added check nonces for refresh/delete individual movies, added transiants to trigger notices in {@see \Lumiere\Admin::lumiere_admin_display_messages() } and crons in {@see \Lumiere\Admin\Cron::lumiere_add_remove_crons_cache() }
 * @TODO: finalize rewriting and factorization of class
 */
class Cache extends Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Logger: set to true to display debug on screen. => 20240225 Don't see why it is needed, will remove in the future
		// $this->logger->lumiere_start_logger( get_class( $this ), false );
	}

	/**
	 * Display the body
	 * @param Cache_Tools $cache_tools_class To create cache folder if it doesn't exists
	 */
	protected function display_cache_options( Cache_Tools $cache_tools_class ): void {

		// First part of the menu
		$this->include_with_vars( 'admin-menu-first-part', [ $this ] /** Add in an array all vars to send in the template */ );

		// Make sure cache folder exists and is writable
		$cache_tools_class->lumiere_create_cache( true );

		// Show the vars if debug is activated.
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Activate debugging
			$this->utils_class->lumiere_activate_debug( $this->imdb_cache_values, 'no_var_dump', null ); # don't display set_error_handler("var_dump") that gets the page stuck in an endless loop

		}

		// Cache submenu.
		$this->include_with_vars( 'cache/admin-cache-submenu', [ $this ] /** Add in an array all vars to send in the template */ );

		if ( ( ( isset( $_GET['cacheoption'] ) ) && ( $_GET['cacheoption'] === 'option' ) ) || ( ! isset( $_GET['cacheoption'] ) ) ) {

			// Cache options menu.
			$size = Utils::lumiere_format_bytes( $cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] ) );
			$this->include_with_vars( 'cache/admin-cache-options', [ $size ] /** Add in an array all vars to send in the template */ );

		}

		////////////////////////////////////////////// Cache management
		if ( isset( $_GET['cacheoption'] ) && $_GET['cacheoption'] === 'manage' ) {

			// check if folder exists & store cache option is selected
			if ( file_exists( $this->imdb_cache_values['imdbcachedir'] ) ) {
				?>

	<div>
				<?php //--------------------------------------------------------- =[cache delete]=- ?>
		<div class="inside lumiere_border_shadow lumiere_margin_btm_twenty">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Global cache management', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside lumiere_border_shadow">
			<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>" >
				<?php
				wp_nonce_field( 'cache_all_and_query_check', '_nonce_cache_all_and_query_check' );
				echo "\n";

				$imdlt_cache_file_count = $cache_tools_class->lumiere_cache_countfolderfiles( $this->imdb_cache_values['imdbcachedir'] );

				echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

				echo '<strong>' . esc_html__( 'Total cache size:', 'lumiere-movies' ) . ' ';
				$size_cache_total = $cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] );

				/* translators: %s is replaced with the number of files */
				echo '&nbsp;' . esc_html( sprintf( _n( '%s file', '%s files', $imdlt_cache_file_count, 'lumiere-movies' ), number_format_i18n( $imdlt_cache_file_count ) ) );
				echo '&nbsp;' . esc_html__( 'using', 'lumiere-movies' );
				echo ' ' . Utils::lumiere_format_bytes( $size_cache_total );
				echo "</strong>\n";
				echo '</div>';

				// Cache files exist, offer the opportunity to delete them.
				if ( $imdlt_cache_file_count > 0 ) {

					echo '<div>';

					esc_html_e( 'If you want to reset the entire cache (this includes queries, names, and pictures) click on the button below.', 'lumiere-movies' );
					echo '<br />';
					esc_html_e( 'Beware, there is no undo.', 'lumiere-movies' );
					?>
			</div>
				<div class="submit submit-imdb" align="center">

				<input type="submit" class="button-primary" name="delete_all_cache" data-confirm="<?php esc_html_e( 'Delete all cache? Really?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete all cache', 'lumiere-movies' ); ?>" /> 

				<br />
				<br />
					<?php
					echo esc_html__( 'This button will', 'lumiere-movies' ) . '<strong> ' . esc_html__( 'delete all files', 'lumiere-movies' ) . '</strong> ' . esc_html__( 'stored in the following folder:', 'lumiere-movies' );
					echo '<br />';
					echo esc_html( $this->imdb_cache_values['imdbcachedir'] );
					?>
				</div>
					<?php

					// No files in cache
				} else {

					echo '<div class="imdblt_error">' . esc_html__( 'Lumière! cache is empty.', 'lumiere-movies' ) . '</div>';

				}
				?>

				<br />
				<?php
				$imdlt_cache_file_query = Utils::lumiere_glob_recursive( $this->imdb_cache_values['imdbcachedir'] . 'find.s*' );
				$imdlt_cache_file_query_count = count( $imdlt_cache_file_query );

				echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

				echo "\n\t\t\t\t" . '<strong>' . esc_html__( 'Total query cache size:', 'lumiere-movies' );
				$size_cache_query_tmp = 0;
				foreach ( $imdlt_cache_file_query as $filenamecachequery ) {
					if ( is_numeric( filesize( $filenamecachequery ) ) ) {
						$size_cache_query_tmp += intval( filesize( $filenamecachequery ) );
					}
				}
				$size_cache_query_total = $size_cache_query_tmp;
				/* translators: %s is replaced with the number of files */
				echo '&nbsp;' . sprintf( esc_html( _n( '%s file', '%s files', $imdlt_cache_file_query_count, 'lumiere-movies' ) ), intval( number_format_i18n( $imdlt_cache_file_query_count ) ) );
				echo '&nbsp;' . esc_html__( 'using', 'lumiere-movies' );
				echo ' ' . Utils::lumiere_format_bytes( intval( $size_cache_query_total ) );
				echo '</strong>';

				echo '</div>';

				// Query files exist, offer the opportunity to delete them.
				if ( $imdlt_cache_file_query_count > 0 ) {
					?>

		<div>
					<?php
					esc_html_e( 'If you want to reset the query cache (every search creates a cache file) click on the button below.', 'lumiere-movies' );
					echo '<br />';
					?>
		</div>
		<div class="submit submit-imdb" align="center">

		<input type="submit" class="button-primary" name="delete_query_cache" data-confirm="<?php esc_html_e( 'Delete query cache?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete query cache', 'lumiere-movies' ); ?>" /> 
		</div>
					<?php

					// No query files in cache.
				} else {

					echo '<div class="imdblt_error">' . esc_html__( 'Lumière! query cache is empty.', 'lumiere-movies' ) . '</div>';
				}
				?>

		</form>
	</div>
	<br />
	<br />
	<form method="post" name="lumiere_delete_ticked_cache" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>" >

				<?php	//------------------------------------------------------------------ =[movies management]=- ?>

	<div class="inside lumiere_border_shadow">
		<h3 class="hndle" id="cachemovies" name="cachemovies"><?php esc_html_e( 'Movie\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside lumiere_border_shadow">
				<?php

				// Get list of movies cached files
				$results = $cache_tools_class->lumiere_get_movie_cache();

				// if files don't exist.
				if ( count( $results ) === 0 ) {

					echo '<div class="imdblt_error">' . esc_html__( 'No movie\'s cache found.', 'lumiere-movies' ) . '</div>';

					// if files exist.
				} elseif ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) {
					?>

		<div class="lumiere_intro_options">

					<?php esc_html_e( 'If you want to refresh movie\'s cache regardless the cache expiration time, you may either tick movie\'s checkbox(es) related to the movie you want to delete and click on "delete cache". you may also click on "refresh" to update a movie series of details.', 'lumiere-movies' ); ?>
			<br />
			<br />
					<?php esc_html_e( 'You may also select a group of movies to delete.', 'lumiere-movies' ); ?>
			<br />
			<br />
		</div>

		<div class="lumiere_flex_container">

					<?php
					$obj_sanitized = '';
					$data = [];

					foreach ( $results as $res ) {

						$title_sanitized = esc_html( $res->title() ); // search title related to movie id
						$obj_sanitized = esc_html( $res->imdbid() );
						$filepath_sanitized = esc_url( $this->imdb_cache_values['imdbcachedir'] . 'title.tt' . substr( $obj_sanitized, 0, 8 ) );
						if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache movies' names, quicker loading
							$data[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]">' . $title_sanitized . '</label></span>' . "\n"; // send input and results into array
							flush();
						} else { // display every cache movie details, longer loading
							// get either local picture or if no local picture exists, display the default one
							if ( false === $res->photo_localurl() ) {
								$moviepicturelink = 'src="' . esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
							} else {
								$moviepicturelink = 'src="' . $this->imdb_cache_values['imdbphotodir'] . $obj_sanitized . '.jpg" alt="' . $title_sanitized . '"';
							}

							// no flex class so the browser decides how many data to display per lines
							// table so "row-actions" WordPress class works
							$filetime_movie = is_int( filemtime( $filepath_sanitized ) ) === true ? filemtime( $filepath_sanitized ) : 0;
							$data[] = '	<div class="lumiere_flex_container_content_thirty lumiere_breakall"><table><tr><td>
					<img id="pic_' . $title_sanitized . '" class="picfloat" ' . $moviepicturelink . ' width="40px">

					<input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]" class="imdblt_bold">' . $title_sanitized . '</label> <br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_movie ) . ' 
					<div id="refresh_edit_' . $title_sanitized . '" class="row-actions">
					
						<span class="edit"><a id="refreshindividual_' . $title_sanitized . '" href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=refresh&where=' . $obj_sanitized . '&type=movie', 'refreshindividual', '_nonce_cache_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="' . esc_html__( 'Refresh cache for *', 'lumiere-movies' ) . $title_sanitized . '*?">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span>

						<span class="delete"><a id="deleteindividual_' . $title_sanitized . '" href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=delete&where=' . $obj_sanitized . '&type=movie', 'deleteindividual', '_nonce_cache_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '" title="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
					</div></td></tr></table>
				</div>';// send input and results into array

						} //end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort']

					}

					// sort alphabetically the data
					asort( $data );

					// print all lines
					foreach ( $data as $inputline ) {
						// @phpcs:ignore WordPress.Security.EscapeOutput
						echo $inputline;
					}
					?>
				</div>
				<br />

				<div class="imdblt_align_center">
					<input type="button" name="CheckAll" value="Check All" data-check-movies="">
					<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-movies="">
				</div>

				<br />
				<br />

				<div class="imdblt_align_center">
					<input type="submit" class="button-primary" name="delete_ticked_cache" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
					<br/>
					<br/>
				</div>

					<?php
				} // end if cache folder is empty
				?>
			</div>
	<br />
	<br />

				<?php //------------------------------------------------------------------------ =[people delete]=- ?>

	<div class="inside lumiere_border_shadow">
		<h3 class="hndle" id="cachepeople" name="cachepeople"><?php esc_html_e( 'People\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside lumiere_border_shadow">

				<?php
				// Get list of movies cached files
				$results = $cache_tools_class->lumiere_get_people_cache();

				// if files don't exist.
				if ( count( $results ) === 0 ) {

					echo '<div class="imdblt_error">' . esc_html__( 'No people\'s cache found.', 'lumiere-movies' ) . '</div>';

					// if files exist.
				} elseif ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) {
					?>

	<div class="lumiere_intro_options">
					<?php esc_html_e( 'If you want to refresh people\'s cache regardless the cache expiration time, you may either tick people checkbox(es) related to the person you want to delete and click on "delete cache", or you may click on individual people\'s "refresh". The first way will require an additional people refresh - from you post, for instance.', 'lumiere-movies' ); ?>
		<br />
		<br />
					<?php esc_html_e( 'You may also either delete individually the cache or by group.', 'lumiere-movies' ); ?>
		<br />
		<br />
	</div>

	<div class="lumiere_flex_container">

					<?php
					$datapeople = [];

					foreach ( $results as $res ) {

						$name_sanitized = sanitize_text_field( $res->name() ); // search title related to movie id
						$objpiple_sanitized = sanitize_text_field( $res->imdbid() );
						$filepath_sanitized = esc_url( $this->imdb_cache_values['imdbcachedir'] . 'name.nm' . substr( $objpiple_sanitized, 0, 8 ) );
						if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache peoples' names, quicker loading
							$datapeople[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people[]">' . $name_sanitized . '</label></span>'; // send input and results into array

						} else { // display every cache people details, longer loading
							// get either local picture or if no local picture exists, display the default one
							if ( false === $res->photo_localurl() ) {
								$picturelink = 'src="' . esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
							} else {
								$picturelink = 'src="' . esc_url( $this->imdb_cache_values['imdbphotodir'] . 'nm' . $objpiple_sanitized . '.jpg' ) . '" alt="' . $name_sanitized . '"';
							}
							$filetime_people = is_int( filemtime( $filepath_sanitized ) ) === true ? filemtime( $filepath_sanitized ) : 0;
							$datapeople[] = '	
				<div class="lumiere_flex_container_content_thirty lumiere_breakall"><table><tr><td>
					<img id="pic_' . $name_sanitized . '" class="picfloat" ' . $picturelink . ' width="40px" alt="no pic">
					<input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people_[]" class="imdblt_bold">' . $name_sanitized . '</label><br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_people ) . '
					
					<div class="row-actions">
						<span class="view"><a href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=refresh&where=' . $objpiple_sanitized . '&type=people', 'refreshindividual', '_nonce_cache_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *' . $name_sanitized . '*" title="Refresh cache for *' . $name_sanitized . '*">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span> 

						<span class="delete"><a href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=delete&where=' . $objpiple_sanitized . '&type=people', 'deleteindividual', '_nonce_cache_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="You are about to delete *' . $name_sanitized . '* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *' . $name_sanitized . '*">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
					</div></td></tr></table>
			</div>'; // send input and results into array.

							flush();
						} // end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort'].

					}

					// sort alphabetically the data.
					asort( $datapeople );

					// print all lines.
					foreach ( $datapeople as $inputline ) {
						// @phpcs:ignore WordPress.Security.EscapeOutput
						echo $inputline;
					}
					?>
				</div>
				<br />
					<div align="center">
						<input type="button" name="CheckAll" value="Check All" data-check-people="">
						<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-people="">
					</div>
					<br />
					<br />

					<div align="center">
						<input type="submit" class="button-primary" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" name="delete_ticked_cache" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
					</div>
					<br/>
					<br/>

			</div>
					<?php
				} // end if data found.

				// End of form for ticked cache to delete.
				wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );
				?>

		</form>
	</div>
	<br />
	<br />

				<?php //------------------------------------------------------------------ =[cache directories]=- ?>

	<div class="inside lumiere_border_shadow">
		<h3 class="hndle" id="cachedirectory" name="cachedirectory"><?php esc_html_e( 'Cache directories', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside lumiere_border_shadow">

		<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>" >

		<div class="titresection lumiere_padding_five"><?php esc_html_e( 'Cache directory (absolute path)', 'lumiere-movies' ); ?></div>

		<div class="lumiere_padding_five">
			<span class="lumiere_smaller">
				<?php
				// display cache folder size.
				if ( $imdlt_cache_file_count > 0 ) {
					echo esc_html__( 'Movies\' cache is using', 'lumiere-movies' ) . ' ' . Utils::lumiere_format_bytes( $size_cache_total ) . "\n";
				} else {
					echo esc_html__( 'Movies\' cache is empty.', 'lumiere-movies' );
				}
				?>
			</span>

		</div>
		<div class="lumiere_padding_five">

			<div class="lumiere_breakall">
				<?php echo esc_html( WP_CONTENT_DIR ); ?>
				<input type="text" name="imdbcachedir_partial" class="lumiere_border_width_medium" value="<?php echo esc_attr( $this->imdb_cache_values['imdbcachedir_partial'] ); ?>">
			</div>

			<div class="explain">
				<?php
				if ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) { // check if folder exists
					echo '<span class="lumiere_green">';
					esc_html_e( 'Folder exists.', 'lumiere-movies' );
					echo '</span>';
				} else {
					echo '<span class="lumiere_red">';
					esc_html_e( "Folder doesn't exist!", 'lumiere-movies' );
					echo '</span>';
				}
				if ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) { // check if permissions are ok
					if ( is_writable( $this->imdb_cache_values['imdbcachedir'] ) ) {
						echo ' <span class="lumiere_green">';
						esc_html_e( 'Permissions OK.', 'lumiere-movies' );
						echo '</span>';
					} else {
						echo ' <span class="lumiere_red">';
						esc_html_e( 'Check folder permissions!', 'lumiere-movies' );
						echo '</span>';
					}
				}
				?>
			</div>

			<div class="explain lumiere_breakall">
				<?php esc_html_e( 'Absolute path to store cache retrieved from the IMDb website. Has to be ', 'lumiere-movies' ); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
				<?php esc_html_e( 'by the webserver.', 'lumiere-movies' ); ?> 
				<br />
				<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo esc_url( WP_CONTENT_DIR . '/cache/lumiere/' ); ?>"
			</div>
		</div>


		<div>
			<div class="titresection lumiere_padding_five">
				<?php esc_html_e( 'Photo path (relative to the cache path)', 'lumiere-movies' ); ?>
			</div>

			<div class="explain">
				<?php
				// display cache folder size
				$size_cache_pics = $cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbphotoroot'] );

				if ( $size_cache_pics > 0 ) {

					esc_html_e( 'Images cache is using', 'lumiere-movies' );
					echo ' ' . Utils::lumiere_format_bytes( $size_cache_pics ) . "\n";
				} else {
					esc_html_e( 'Image cache is empty.', 'lumiere-movies' );
					echo "\n";
				}
				?>
			</div>

			<div class="explain lumiere_breakall">
					<?php esc_html_e( 'Absolute path to store images retrieved from the IMDb website. Has to be ', 'lumiere-movies' ); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
					<?php esc_html_e( 'by the webserver.', 'lumiere-movies' ); ?>
				<br />
			</div>

			<div class="lumiere_smaller lumiere_breakall">
				<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $this->imdb_cache_values['imdbphotoroot'] ); ?>"
			</div>
			<br />

			<div class="lumiere_smaller">
				<?php
				if ( file_exists( $this->imdb_cache_values['imdbphotoroot'] ) ) { // check if folder exists
					echo '<span class="lumiere_green">';
					esc_html_e( 'Folder exists.', 'lumiere-movies' );
					echo '</span>';
				} else {
					echo '<span class="lumiere_red">';
					esc_html_e( "Folder doesn't exist!", 'lumiere-movies' );
					echo '</span>';
				}
				if ( file_exists( $this->imdb_cache_values['imdbphotoroot'] ) ) { // check if permissions are ok
					if ( is_writable( $this->imdb_cache_values['imdbphotoroot'] ) ) {
						echo ' <span class="lumiere_green">';
						esc_html_e( 'Permissions OK.', 'lumiere-movies' );
						echo '</span>';
					} else {
						echo ' <span class="lumiere_red">';
						esc_html_e( 'Check folder permissions!', 'lumiere-movies' );
						echo '</span>';
					}
				}

				?>

			</div>
		</div>

		<div>
			<div class="titresection lumiere_padding_five">
				<?php esc_html_e( 'Photo URL (relative to the website and the cache path)', 'lumiere-movies' ); ?>
			</div>			

			<div class="explain lumiere_breakall">
				<?php esc_html_e( 'URL corresponding to photo directory.', 'lumiere-movies' ); ?> 
				<br />
				<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $this->imdb_cache_values['imdbphotodir'] ); ?>"
			</div>

		</div>

		<div class="submit submit-imdb lumiere_align_center" align="center"><?php
			wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );
		?><input type="submit" class="button-primary" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
			<input type="submit" class="button-primary" name="lumiere_update_cache_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />

			</form>
		</div>
	</div>

	<br />
	<br />


		<?php	} else { // end if cache folder exists ?>

		<div class="inside lumiere_border_shadow_red">
				<?php esc_html_e( 'A cache folder has to be created and the cache storage option has to be activated before you can manage the cache.', 'lumiere-movies' ); ?>
			<br /><br />
				<?php esc_html_e( 'Apparently, you have no cache folder.', 'lumiere-movies' ); ?> 
			<br /><br />
				<?php esc_html_e( 'Click on "reset settings" to refresh the values.', 'lumiere-movies' ); ?>
		</div>

		<div class="submit submit-imdb" align="center">
			<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>">
				<?php
				//check that data has been sent only once
				wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );
				get_submit_button( esc_html__( 'Reset settings', 'lumiere-movies' ), 'primary large', 'lumiere_reset_cache_settings' );
				?>
				<input type="submit" class="button-primary" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?> " />
			</form>
		</div>

				<?php
		} // end else cache folder exists

		}  //end if $_GET['cacheoption'] == "manage"
		?>

</div>
<br clear="all">
<br />
<br />

<?php	}

}


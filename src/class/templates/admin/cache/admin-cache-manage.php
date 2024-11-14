<?php declare( strict_types = 1 );
/**
 * Template of cache managment
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

/** @psalm-suppress InvalidGlobal Cannot use global scope here (unless this file is included from a non-global scope) */
global $wp_filesystem;

$lum_imdb_cache_values = get_option( \Lumiere\Settings::get_cache_tablename() );

// Retrieve the vars from calling class.
$lum_cache_file_count = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
$lum_size_cache_total = get_transient( Admin_Menu::TRANSIENT_ADMIN )[1];
$lum_list_movie_cache = get_transient( Admin_Menu::TRANSIENT_ADMIN )[2];
$lum_list_people_cached = get_transient( Admin_Menu::TRANSIENT_ADMIN )[3];
$lum_size_cache_pics = get_transient( Admin_Menu::TRANSIENT_ADMIN )[4];
$lum_that = get_transient( Admin_Menu::TRANSIENT_ADMIN )[5];
$lum_this_cache_manage_page = get_transient( Admin_Menu::TRANSIENT_ADMIN )[6];
$lum_query_number_files = get_transient( Admin_Menu::TRANSIENT_ADMIN )[7][0];
$lum_query_cache_size = get_transient( Admin_Menu::TRANSIENT_ADMIN )[7][1];

// Let's go! ?>

<div class="lumiere_wrap">

<?php
// Cache folder doesn't exist
if ( ! file_exists( $lum_imdb_cache_values['imdbcachedir'] ) ) { ?>

	<div class="lumiere_border_shadow_red">
		<?php esc_html_e( 'A cache folder has to be created and the cache storage option has to be activated before you can manage the cache.', 'lumiere-movies' ); ?>
		<br>
		<br>
		<?php esc_html_e( 'Apparently, you have no cache folder.', 'lumiere-movies' ); ?> 
		<br>
		<br>
		<?php esc_html_e( 'Click on "reset settings" to refresh the values.', 'lumiere-movies' ); ?>
	</div>

	<div class="submit" align="center">
		<form method="post" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<?php
			//check that data has been sent only once
			wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );
			get_submit_button( esc_html__( 'Reset settings', 'lumiere-movies' ), 'primary large', 'lumiere_reset_cache_settings' );
			?>
			<input type="submit" class="button-primary" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
		</form>
	</div>
</div>
	<?php
	return;
} ?>		


	<!-- --------------------------------------------------------- =[cache delete]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Global cache management', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<form method="post" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
		<?php wp_nonce_field( 'cache_all_and_query_check', '_nonce_cache_all_and_query_check' ); ?>

		<div class="detailedcacheexplaination lum_padding_bottom_ten lumiere_align_center">

			<strong><?php esc_html_e( 'Total cache size:', 'lumiere-movies' ); ?>&nbsp;<?php

			echo esc_html(
				sprintf(
					/* translators: %1$s is replaced with a number of files, %2$s the size in MB of a folder */
					_n( '%1$sfile using %2$s', '%1$s files using %2$s', $lum_cache_file_count, 'lumiere-movies' ),
					number_format_i18n( $lum_cache_file_count ),
					$lum_that->lumiere_format_bytes( $lum_size_cache_total )
				)
			);
			?></strong>
		</div>

		<?php
		// Cache files exist, offer the opportunity to delete them.
		if ( $lum_cache_file_count > 0 ) { ?>

		<div>
			<?php esc_html_e( 'If you want to reset the entire cache (this includes queries, names, and pictures) click on the button below.', 'lumiere-movies' ); ?>
			<br>
			<?php esc_html_e( 'Beware, there is no undo.', 'lumiere-movies' ); ?>
		</div>
		
		<div class="submit" align="center">

			<input type="submit" class="button-primary" name="delete_all_cache" data-confirm="<?php esc_html_e( 'Delete all cache? Really?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete all cache', 'lumiere-movies' ); ?>" /> 

			<br>
			<br>
			<?php
			echo esc_html__( 'This button will', 'lumiere-movies' ) . '<strong> ' . esc_html__( 'delete all files', 'lumiere-movies' ) . '</strong> ' . esc_html__( 'stored in the following folder:', 'lumiere-movies' );
			echo '<br>';
			echo esc_html( $lum_imdb_cache_values['imdbcachedir'] );
			?>
		</div>
			

			<?php
			// No files in cache
		} else { ?>

		<div class="lum_error"><?php esc_html_e( 'Lumière! cache is empty.', 'lumiere-movies' ); ?></div>

		<?php } // end of no files in cache ?>

		<br>
		
		<div class="detailedcacheexplaination lum_padding_bottom_ten lumiere_align_center">

			<strong><?php esc_html_e( 'Total query cache size:', 'lumiere-movies' ); ?>&nbsp;<?php

			echo esc_html(
				sprintf(
					/* translators: first %1$s is replaced with a number of files, %2$s the size in MB of a folder */
					_n( '%1$sfile using %2$s', '%1$s files using %2$s', $lum_query_number_files, 'lumiere-movies' ),
					$lum_query_number_files,
					$lum_that->lumiere_format_bytes( intval( $lum_query_cache_size ) )
				)
			);
			?></strong>
		</div>

		<?php
		// Query files exist, offer the opportunity to delete them.
		if ( $lum_query_number_files > 0 ) { ?>
			

		<div>
			<?php esc_html_e( 'If you want to reset the query cache (every search creates a cache file) click on the button below.', 'lumiere-movies' ); ?>
		</div>

		<br>
		
		<div class="submit" align="center">

			<input type="submit" class="button-primary" name="delete_query_cache" data-confirm="<?php esc_html_e( 'Delete query cache?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete query cache', 'lumiere-movies' ); ?>" /> 

		</div>
		
			<?php
			// No query files in cache.
		} else { ?>

		<div class="lum_error">
			<?php esc_html_e( 'Lumière! query cache is empty.', 'lumiere-movies' ); ?>
		</div>
			
		<?php } ?>
		</form>
	</div>

	<!-- ------------------------------------------------------------------ =[movies management]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="cachemovies" name="cachemovies"><?php esc_html_e( 'Movie\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<form method="post" name="lumiere_delete_ticked_cache" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >

	<div class="lumiere_border_shadow">

		<?php // if files don't exist.
		if ( count( $lum_list_movie_cache ) === 0 ) { ?>

		<div class="lum_error"><?php esc_html_e( 'No movie\'s cache found.', 'lumiere-movies' ); ?></div>
		
		<?php } elseif ( $lum_imdb_cache_values['imdbcachedetailshidden'] === '1' ) { ?>
		
		<div><?php esc_html_e( 'Displaying cache data is deactivated.', 'lumiere-movies' ); ?></div>

		<?php } elseif ( is_dir( $lum_imdb_cache_values['imdbcachedir'] ) === true ) { ?>
				
		<div class="lumiere_options_intro_inblock">

			<?php esc_html_e( 'If you want to refresh movie\'s cache regardless the cache expiration time, you may either tick movie\'s checkbox(es) related to the movie you want to delete and click on "delete cache". You can also click on "refresh" to update a movie series of details.', 'lumiere-movies' ); ?>
			<br>
			<br>
			<?php esc_html_e( 'You may also select a group of movies to delete.', 'lumiere-movies' ); ?>
			<br>
			<br>
		</div>

		<div class="lumiere_flex_container">

			<?php
			$lum_obj_sanitized = '';
			$lum_data = [];

			foreach ( $lum_list_movie_cache as $lum_movie_results ) {

				$lum_title_sanitized = esc_html( $lum_movie_results->title() ); // search title related to movie id
				$lum_obj_sanitized = esc_html( $lum_movie_results->imdbid() );
				$lum_filepath_sanitized = esc_url( $lum_imdb_cache_values['imdbcachedir'] . 'title.tt' . substr( $lum_obj_sanitized, 0, 8 ) );

				if ( $lum_imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache movies' names, quicker loading
					$lum_data[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $lum_title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $lum_obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]">' . $lum_title_sanitized . '</label></span>' . "\n"; // send input and results into array
					flush();
				} else { // display every cache movie details, longer loading
					// get either local picture or if no local picture exists, display the default one
					if ( false === $lum_movie_results->photo_localurl() ) {
						$lum_moviepicturelink = 'src="' . esc_url( $lum_that->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
					} else {
						$lum_moviepicturelink = 'src="' . $lum_imdb_cache_values['imdbphotodir'] . $lum_obj_sanitized . '.jpg" alt="' . $lum_title_sanitized . '"';
					}

					// no flex class so the browser decides how much data to display per lines
					// table so "row-actions" WordPress class works
					$lum_filetime_movie = is_int( filemtime( $lum_filepath_sanitized ) ) === true ? filemtime( $lum_filepath_sanitized ) : 0;
					$lum_data[] = '	<div class="lumiere_flex_container_content_thirty lumiere_breakall"><table><tr><td>
			<img id="pic_' . $lum_title_sanitized . '" class="lum_cache_pic_float" ' . $lum_moviepicturelink . ' width="40px">

			<input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $lum_title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $lum_obj_sanitized . '" /><span class="lumiere_font_smaller"><label for="imdb_cachedeletefor_movies[]" class="lum_bold">' . $lum_title_sanitized . '</label><br>' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $lum_filetime_movie ) . '</span> 
			<div id="refresh_edit_' . $lum_title_sanitized . '" class="row-actions">
			
				<span class="edit"><a id="refreshindividual_' . $lum_title_sanitized . '" href="' . wp_nonce_url( $lum_this_cache_manage_page . '&dothis=refresh&where=' . $lum_obj_sanitized . '&type=movie', 'refreshindividual', '_nonce_cache_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="' . esc_html__( 'Refresh cache for *', 'lumiere-movies' ) . $lum_title_sanitized . '*?">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span>

				<span class="delete"><a id="deleteindividual_' . $lum_title_sanitized . '" href="' . wp_nonce_url( $lum_this_cache_manage_page . '&dothis=delete&where=' . $lum_obj_sanitized . '&type=movie', 'deleteindividual', '_nonce_cache_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $lum_title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '" title="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $lum_title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
			</div></td></tr></table>
		</div>'; // send input and results into array

				} //end quick/long loading $lum_imdb_cache_values['imdbcachedetailsshort']

			}

			// sort alphabetically the data
			asort( $lum_data );

			// print all lines
			foreach ( $lum_data as $lum_inputline ) {

				echo wp_kses(
					$lum_inputline,
					[
						'span' => [ 'class' => [] ],
						'input' => [
							'id' => [],
							'class' => [],
							'type' => [],
							'name' => [],
							'value' => [],
						],
						'div' => [
							'id' => [],
							'class' => [],
						],
						'table' => [],
						'tr' => [],
						'td' => [],
						'a' => [
							'data-confirm' => [],
							'id' => [],
							'class' => [],
							'href' => [],
							'title' => [],
						],
						'label' => [
							'for' => [],
							'class' => [],
						],
						'img' => [
							'id' => [],
							'class' => [],
							'alt' => [],
							'src' => [],
							'width' => [],
						],
						'br' => [],
					]
				);
			} ?>
		</div>
		
		<br>

		<div class="lumiere_align_center">
			<input type="button" name="CheckAll" value="Check All" data-check-movies="">
			<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-movies="">
		</div>

		<br>
		<br>

		<div class="lumiere_align_center">
			<input type="submit" class="button-primary" name="delete_ticked_cache" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
			<br/>
			<br/>
		</div>
		
		<?php } // end if cache folder is empty ?>
	</div>

	<!-- ---------------------------------------------------------------------- =[people delete]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="cachepeople" name="cachepeople"><?php esc_html_e( 'People\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<?php

		// if files don't exist.
		if ( count( $lum_list_people_cached ) === 0 ) { ?>

		<div class="lum_error"><?php esc_html_e( 'No people\'s cache found.', 'lumiere-movies' ); ?></div>

		<?php } elseif ( $lum_imdb_cache_values['imdbcachedetailshidden'] === '1' ) { ?>
		
		<div><?php esc_html_e( 'Displaying cache data is deactivated.', 'lumiere-movies' ); ?></div>
		
		<?php } elseif ( is_dir( $lum_imdb_cache_values['imdbcachedir'] ) === true ) { // if files exist. ?>

		<div class="lumiere_options_intro_inblock">
			<?php esc_html_e( 'If you want to refresh people\'s cache regardless the cache expiration time, you may either tick people checkbox(es) related to the person you want to delete and click on "delete cache", or you may click on individual people\'s "refresh". The first way will require an additional people refresh - from you post, for instance.', 'lumiere-movies' ); ?>
			<br>
			<br>
			<?php esc_html_e( 'You may also either delete individually the cache or by group.', 'lumiere-movies' ); ?>
			<br>
			<br>
		</div>

		<div class="lumiere_flex_container">

			<?php
			$lum_datapeople = [];

			foreach ( $lum_list_people_cached as $lum_people_results ) {

				$lum_name_sanitized = sanitize_text_field( $lum_people_results->name() ); // search title related to movie id
				$lum_objpiple_sanitized = sanitize_text_field( $lum_people_results->imdbid() );
				$lum_filepath_sanitized = esc_url( $lum_imdb_cache_values['imdbcachedir'] . 'name.nm' . substr( $lum_objpiple_sanitized, 0, 8 ) );

				if ( $lum_imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache peoples' names, quicker loading
					$lum_datapeople[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $lum_name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $lum_objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people[]">' . $lum_name_sanitized . '</label></span>'; // send input and results into array

				} else { // display every cache people details, longer loading
					// get either local picture or if no local picture exists, display the default one
					if ( false === $lum_people_results->photo_localurl() ) {
						$lum_picturelink = 'src="' . esc_url( $lum_that->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
					} else {
						$lum_picturelink = 'src="' . esc_url( $lum_imdb_cache_values['imdbphotodir'] . 'nm' . $lum_objpiple_sanitized . '.jpg' ) . '" alt="' . $lum_name_sanitized . '"';
					}
					$lum_filetime_people = is_int( filemtime( $lum_filepath_sanitized ) ) === true ? filemtime( $lum_filepath_sanitized ) : 0;
					$lum_datapeople[] = '	
	<div class="lumiere_flex_container_content_thirty lumiere_breakall"><table><tr><td>
	<img id="pic_' . $lum_name_sanitized . '" class="lum_cache_pic_float" ' . $lum_picturelink . ' width="40px" alt="no pic">
	<input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $lum_name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $lum_objpiple_sanitized . '" /><span class="lumiere_font_smaller"><label for="imdb_cachedeletefor_people_[]" class="lum_bold">' . $lum_name_sanitized . '</label><br>' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $lum_filetime_people ) . '
	</span>
	<div class="row-actions">
		<span class="view"><a href="' . wp_nonce_url( $lum_this_cache_manage_page . '&dothis=refresh&where=' . $lum_objpiple_sanitized . '&type=people', 'refreshindividual', '_nonce_cache_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *' . $lum_name_sanitized . '*" title="Refresh cache for *' . $lum_name_sanitized . '*">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span> 

		<span class="delete"><a href="' . wp_nonce_url( $lum_this_cache_manage_page . '&dothis=delete&where=' . $lum_objpiple_sanitized . '&type=people', 'deleteindividual', '_nonce_cache_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="You are about to delete *' . $lum_name_sanitized . '* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *' . $lum_name_sanitized . '*">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
		
	</div></td></tr></table>
	</div>'; // send input and results into array.

				} // end quick/long loading $lum_imdb_cache_values['imdbcachedetailsshort'].

			}

			// sort alphabetically the data.
			asort( $lum_datapeople );

			// print all lines.
			foreach ( $lum_datapeople as $lum_inputline ) {
				echo wp_kses(
					$lum_inputline,
					[
						'span' => [ 'class' => [] ],
						'input' => [
							'id' => [],
							'class' => [],
							'type' => [],
							'name' => [],
							'value' => [],
						],
						'div' => [
							'id' => [],
							'class' => [],
						],
						'table' => [],
						'tr' => [],
						'td' => [],
						'a' => [
							'data-confirm' => [],
							'id' => [],
							'class' => [],
							'href' => [],
							'title' => [],
						],
						'label' => [
							'for' => [],
							'class' => [],
						],
						'img' => [
							'id' => [],
							'class' => [],
							'alt' => [],
							'src' => [],
							'width' => [],
						],
						'br' => [],
					]
				);
			}
			?>
		</div>

		<br>
		
		<div align="center">
			<input type="button" name="CheckAll" value="Check All" data-check-people="">
			<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-people="">
		</div>

		<br>
		<br>

		<div align="center">
			<input type="submit" class="button-primary" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" name="delete_ticked_cache" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
		</div>
		
		<br/>
		<br/>
			
		<?php } // end if data found. ?>

	</div>

	<?php
	// End of form for ticked cache to delete.
	wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );
	?>

	</form>

	<!-- ---------------------------------------------------------------- =[cache directories]=- -->

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="cachedirectory" name="cachedirectory"><?php esc_html_e( 'Cache directories', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="lumiere_border_shadow">

		<form method="post" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" >

			<div class="titresection lumiere_padding_five"><?php esc_html_e( 'Cache directory (absolute path)', 'lumiere-movies' ); ?></div>

			<div class="lumiere_padding_five">
			
				<span class="lumiere_smaller">
					<?php
					// display cache folder size.
					if ( $lum_cache_file_count > 0 ) {
						echo esc_html__( 'Movies\' cache is using', 'lumiere-movies' ) . ' ' . esc_html( $lum_that->lumiere_format_bytes( $lum_size_cache_total ) ) . "\n";
					} else {
						esc_html_e( 'Movies\' cache is empty.', 'lumiere-movies' );
					}
					?>
				</span>

			</div>
			
			<div class="lumiere_padding_five">

				<div class="lumiere_breakall">
					<?php echo esc_html( WP_CONTENT_DIR ); ?>
					<input type="text" name="imdbcachedir_partial" class="lumiere_border_width_medium" value="<?php echo esc_attr( $lum_imdb_cache_values['imdbcachedir_partial'] ); ?>">
				</div>

				<div class="explain">
				
				<?php // check if movies/people cache folder exists
				if ( $wp_filesystem->is_dir( $lum_imdb_cache_values['imdbcachedir'] ) ) { ?> 

					<span class="lumiere_green"><?php esc_html_e( 'Folder exists.', 'lumiere-movies' ); ?></span>

					<?php
				} else { ?>
					
					<span class="lumiere_red"><?php esc_html_e( "Folder doesn't exist!", 'lumiere-movies' ); ?></span>

					<?php
				}
				if ( $wp_filesystem->is_dir( $lum_imdb_cache_values['imdbcachedir'] ) ) { // check if permissions are ok

					if ( $wp_filesystem->is_writable( $lum_imdb_cache_values['imdbcachedir'] ) ) { ?>
					
					<span class="lumiere_green"><?php esc_html_e( 'Permissions OK.', 'lumiere-movies' ); ?></span>
						
						<?php
					} else { ?>
						
					<span class="lumiere_red"><?php esc_html_e( 'Check folder permissions!', 'lumiere-movies' ); ?></span>

						<?php
					}
				} ?>
				</div>

				<div class="explain lumiere_breakall">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %1$s is a html ahref tag, %2$s the closure of that very tag */
							__( 'Absolute path to store cache retrieved from the IMDb website. Has to be %1$swritable%2$s by the webserver.', 'lumiere-movies' ),
							'<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">',
							'</a>'
						),
						[
							'a' => [
								'href' => [],
								'title' => [],
							],
						]
					);?> 
					 
					<br>
					
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
					if ( $lum_size_cache_pics > 0 ) {

						echo esc_html__( 'Images cache is using', 'lumiere-movies' ) . ' ' . esc_html( $lum_that->lumiere_format_bytes( $lum_size_cache_pics ) ) . "\n";

					} else {

						echo esc_html__( 'Image cache is empty.', 'lumiere-movies' ) . "\n";
					} ?>
				</div>

				<div class="explain lumiere_breakall">
					<?php
					echo wp_kses(
						sprintf(
							/* translators: %1$s is a html ahref tag, %2$s the closure of that very tag */
							__( 'Absolute path to store images retrieved from the IMDb website. Has to be %1$swritable%2$s by the webserver.', 'lumiere-movies' ),
							'<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">',
							'</a>'
						),
						[
							'a' => [
								'href' => [],
								'title' => [],
							],
						]
					);?> 
					 
					<br>
				</div>

				<div class="lumiere_smaller lumiere_breakall">
					<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $lum_imdb_cache_values['imdbphotoroot'] ); ?>"
				</div>
				<br>

				<div class="lumiere_smaller">
				
				<?php // check if images folder exists
				if ( $wp_filesystem->is_dir( $lum_imdb_cache_values['imdbphotoroot'] ) ) { ?>

					<span class="lumiere_green"><?php esc_html_e( 'Folder exists.', 'lumiere-movies' ); ?></span>

					<?php
				} else { ?>
					
					<span class="lumiere_red"><?php esc_html_e( "Folder doesn't exist!", 'lumiere-movies' ); ?></span>

					<?php
				}
				if ( $wp_filesystem->is_dir( $lum_imdb_cache_values['imdbphotoroot'] ) ) { // check if permissions are ok

					if ( $wp_filesystem->is_writable( $lum_imdb_cache_values['imdbphotoroot'] ) ) { ?>
					
					<span class="lumiere_green"><?php esc_html_e( 'Permissions OK.', 'lumiere-movies' ); ?></span>
						
						<?php
					} else { ?>
						
					<span class="lumiere_red"><?php esc_html_e( 'Check folder permissions!', 'lumiere-movies' ); ?></span>

						<?php
					}
				} ?>

				</div>
			</div>

			<div>
				<div class="titresection lumiere_padding_five">
					<?php esc_html_e( 'Photo URL (relative to the website and the cache path)', 'lumiere-movies' ); ?>
				</div>			

				<div class="explain lumiere_breakall">
					<?php esc_html_e( 'URL corresponding to photo directory.', 'lumiere-movies' ); ?> 
					<br>
					<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $lum_imdb_cache_values['imdbphotodir'] ); ?>"
				</div>

			</div>

			<div class="submit lumiere_align_center" align="center"><?php

				wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );

			?><input type="submit" class="button-primary" id="lumiere_reset_cache_settings" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
				<input type="submit" class="button-primary" id="lumiere_update_cache_settings" name="lumiere_update_cache_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />

			</div>
		</form>

	</div>
</div>

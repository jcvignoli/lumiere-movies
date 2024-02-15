<?php declare( strict_types = 1 );
/**
 * Cache options class
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 * @TODO: rewrite and factorize the class
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

use Lumiere\Settings;
use Lumiere\Tools\Utils;
use Lumiere\Admin\Cache_Tools;

/**
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 *
 * @since 3.12 Methods extracted from this class to cache tools and factorized there, added check nonces for refresh/delete individual movies, added transiants to trigger notices in {@see \Lumiere\Admin::lumiere_admin_display_messages() } and crons in {@see \Lumiere\Admin\Cron::lumiere_add_remove_crons_cache() }
 */
class Cache extends \Lumiere\Admin {

	/**
	 * Class Cache tools
	 * That class includes all the main methods
	 */
	private Cache_Tools $cache_tools_class;

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Activate debugging and display the vars
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Activate debugging
			$this->utils_class->lumiere_activate_debug( $this->imdb_cache_values, 'no_var_dump', null ); # don't display set_error_handler("var_dump") that gets the page stuck in an endless loop

		}

		// Start the Tools class.
		$this->cache_tools_class = new Cache_Tools();

		// Logger: set to true to display debug on screen.
		$this->logger->lumiere_start_logger( get_class(), false );
	}

	/**
	 * Display the layout
	 */
	public function lumiere_cache_layout(): void {

		$this->lumiere_cache_head();
		$this->lumiere_cache_display_submenu();
		$this->lumiere_cache_display_body();

	}

	/**
	 * Display head
	 */
	private function lumiere_cache_head(): void {

		##################################### Saving options

		// save data selected
		if ( isset( $_POST['update_cache_options'] ) && check_admin_referer( 'cache_options_check', 'cache_options_check' ) !== false ) {

			foreach ( $_POST as $key => $postvalue ) {
				// Sanitize
				$key_sanitized = sanitize_key( strval( $key ) );

				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );
				if ( isset( $_POST[ $key_sanitized ] ) ) {
					/** @phpstan-var key-of<OPTIONS_CACHE> $keynoimdb */
					$this->imdb_cache_values[ $keynoimdb ] = sanitize_text_field( $_POST[ $key_sanitized ] );
				}
			}

			update_option( Settings::LUMIERE_CACHE_OPTIONS, $this->imdb_cache_values );

			set_transient( 'notice_lumiere_msg', 'cache_options_update_msg', 1 );

			// If the option for cron imdbcachekeepsizeunder was modified.
			if ( isset( $_POST['imdb_imdbcachekeepsizeunder'] ) ) {
				set_transient( 'cron_settings_updated', 'imdbcachekeepsizeunder', 1 );
			}

			// If the option for cron imdbcachekeepsizeunder was modified.
			if ( isset( $_POST['imdb_imdbcacheautorefreshcron'] ) ) {
				set_transient( 'cron_settings_updated', 'imdbcacheautorefreshcron', 1 );
			}

			wp_redirect( $this->page_cache_option );
			exit;

			// reset options selected
		} elseif ( isset( $_POST['reset_cache_options'] ) && check_admin_referer( 'cache_options_check', 'cache_options_check' ) !== false ) {

			delete_option( Settings::LUMIERE_CACHE_OPTIONS );

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_options_refresh_msg', 1 );
				exit;
			}

			// delete all cache files
		} elseif ( isset( $_POST['delete_all_cache'] ) && check_admin_referer( 'cache_all_and_query_check', 'cache_all_and_query_check' ) !== false ) {

			// prevent drama
			if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
				wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
			}

			// Delete all cache
			Utils::lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] );

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_delete_all_msg', 1 );
				exit;
			}

			// delete all query cache files.
		} elseif ( isset( $_POST['delete_query_cache'] ) && check_admin_referer( 'cache_all_and_query_check', 'cache_all_and_query_check' ) !== false ) {

			$this->cache_tools_class->cache_delete_query_cache_files();

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_query_deleted', 1 );
				exit;
			}

			// delete several ticked files.
		} elseif ( isset( $_POST['delete_ticked_cache'] ) && check_admin_referer( 'cache_options_check', 'cache_options_check' ) !== false ) {

			if ( isset( $_POST['imdb_cachedeletefor_movies'] ) ) {
				$ids_to_delete = isset( $_POST['imdb_cachedeletefor_movies'] ) ? (array) $_POST['imdb_cachedeletefor_movies'] : [];
				$type_to_delete = 'movie';
			} elseif ( isset( $_POST['imdb_cachedeletefor_people'] ) ) {
				$ids_to_delete = isset( $_POST['imdb_cachedeletefor_people'] ) ? (array) $_POST['imdb_cachedeletefor_people'] : [];
				$type_to_delete = 'people';
			}

			if ( isset( $ids_to_delete ) && count( $ids_to_delete ) > 0 && isset( $type_to_delete ) ) {
				$this->cache_tools_class->cache_delete_ticked_files( $ids_to_delete, $type_to_delete );
			}

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_delete_ticked_msg', 1 );
				exit;
			}

			// delete a specific file by clicking on it.
		} elseif (
			isset( $_GET['dothis'] ) && $_GET['dothis'] === 'delete' && isset( $_GET['type'] )
			// Nonce security
			&& isset( $_GET['_nonce_deleteindividual'] ) && wp_verify_nonce( $_GET['_nonce_deleteindividual'], 'deleteindividual' ) !== false
		) {

			$type = isset( $_GET['type'] ) ? filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
			$where = isset( $_GET['where'] ) ? filter_input( INPUT_GET, 'where', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
			$this->cache_tools_class->cache_delete_specific_file( $type, $where );

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_delete_individual_msg', 1 );
				exit;
			}

			// refresh a specific file by clicking on it.
		} elseif (
			isset( $_GET['dothis'] ) && $_GET['dothis'] === 'refresh' && isset( $_GET['type'] )
			// Nonce security
			&& isset( $_GET['_nonce_refreshindividual'] ) && wp_verify_nonce( $_GET['_nonce_refreshindividual'], 'refreshindividual' ) !== false
		) {

			$type = isset( $_GET['type'] ) ? filter_input( INPUT_GET, 'type', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
			$where = isset( $_GET['where'] ) ? filter_input( INPUT_GET, 'where', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
			$this->cache_tools_class->cache_refresh_specific_file( $type, $where );

			if ( wp_redirect( $this->page_cache_manage ) ) {
				set_transient( 'notice_lumiere_msg', 'cache_refresh_individual_msg', 1 );
				exit;
			}
		}

	}

	/**
	 *  Display submenu
	 *
	 */
	private function lumiere_cache_display_submenu(): void { ?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-cache-options.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_cache_option ); ?>"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></a></div>
		<?php
		if ( '1' === $this->imdb_cache_values['imdbusecache'] ) {
			?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-cache-management.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?>" href="<?php echo esc_url( $this->page_cache_manage ); ?>"><?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?></a></div>
			<?php
		};
		?>
	</div>
</div>

<div id="poststuff">

	<div class="intro_cache">
		<?php esc_html_e( 'Cache is crucial for Lumière! operations. Initial IMDb queries are quite time consuming, so if you do not want to kill your server and look for a smooth experience for your users, do not delete often your cache.', 'lumiere-movies' ); ?>
	</div>


		<?php
	}

	/**
	 *  Display the body
	 *
	 */
	private function lumiere_cache_display_body(): void {

		if ( ( ( isset( $_GET['cacheoption'] ) ) && ( $_GET['cacheoption'] === 'option' ) ) || ( ! isset( $_GET['cacheoption'] ) ) ) {

			echo "\n\t" . '<div class="postbox-container">';
			echo "\n\t\t" . '<div id="left-sortables" class="meta-box-sortables" >';

			echo "\n\t\t\t" . '<form method="post" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ?? '' ) . '">';

				//------------------------------------------------------------------ =[cache options]=-
			?>

		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></h3>
		</div>

	<div class="inside imblt_border_shadow">

	<div class="titresection"><?php esc_html_e( 'General options', 'lumiere-movies' ); ?></div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive">
			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<?php esc_html_e( 'Use cache?', 'lumiere-movies' ); ?>&nbsp;
				<input type="hidden" id="imdb_imdbusecache_no" name="imdb_imdbusecache" value="0" data-checkbox_activate="imdb_imdbcacheexpire_id" />

				<input type="checkbox" id="imdb_imdbusecache_yes" name="imdb_imdbusecache" value="1" data-checkbox_activate="imdb_imdbcacheexpire_id"
				<?php
				if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
					echo ' checked="checked"'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Whether to use a cached page to retrieve the information (if available).', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'If cache is deactived, pictures will not be displayed and it will take longer to display the page.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

			</div>
			<div id="imdb_imdbcacheexpire_id" class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<label for="imdb_imdbcacheexpire"><?php esc_html_e( 'Cache expire', 'lumiere-movies' ); ?></label><br /><br />
				<div class="lumiere_flex_container">

					<div>
						<input type="text" id="imdb_imdbcacheexpire" name="imdb_imdbcacheexpire" size="7" value="<?php echo esc_html( $this->imdb_cache_values['imdbcacheexpire'] ); ?>" />
					</div>

					<div class="imdblt_padding_ten">
						<input type="checkbox" value="0" id="imdb_imdbcacheexpire_definitive" name="imdb_imdbcacheexpire_definitive" data-valuemodificator="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_default="2592000"
						<?php
						if ( $this->imdb_cache_values['imdbcacheexpire'] === '0' ) {
							echo 'checked="checked"';
						};
						?>
						/>
						<label for="imdb_imdbcacheexpire"><?php esc_html_e( '(never)', 'lumiere-movies' ); ?></label>
					</div>
				</div>

				<div class="explain"><?php esc_html_e( 'Cache files older than this value (in seconds) will be automatically deleted. Insert "0" or click "never" to keep cache files forever.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "2592000" <?php esc_html_e( '(one month)', 'lumiere-movies' ); ?></div>

			</div>
		</div>

			<?php	//------------------------------------------------------------------ =[cache details]=- ?>
		<div class="titresection"><?php esc_html_e( 'Cache details', 'lumiere-movies' ); ?></div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e( 'Simplified cache details', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="0" />
				<input type="checkbox" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" 
				<?php
				if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) {
					echo ' checked="checked"'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Allows faster loading time for the "manage cache" page by displaying shorter movies and people presentation. Usefull when you have many of them.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

			</div>
		</div>

			<?php	//------------------------------------------------------------------ =[cache cron]=- ?>
		<div class="titresection"><?php esc_html_e( 'Cache automatized functions', 'lumiere-movies' ); ?></div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<div class="lumiere_flex_container">
					<div id="imdb_imdbcachekeepsizeunder_id" class="lumiere_padding_right_fifteen">
						<?php esc_html_e( 'Keep automatically cache size below a limit', 'lumiere-movies' ); ?>&nbsp;
						<input type="hidden" id="imdb_imdbcachekeepsizeunder_no" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="0" />
						<input type="checkbox" id="imdb_imdbcachekeepsizeunder_yes" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="1" <?php
						if ( $this->imdb_cache_values['imdbcachekeepsizeunder'] === '1' ) {
							echo ' checked="checked"';
						} ?> />
					</div>
					<div id="imdb_imdbcachekeepsizeunder_sizelimit_id">
						<input type="text" id="imdb_imdbcachekeepsizeunder_sizelimit"  class="lumiere_width_five_em" name="imdb_imdbcachekeepsizeunder_sizelimit" size="7" value="<?php echo esc_attr( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ); ?>" /> <i>(size in MB)</i>
					</div>
				</div>

				<div class="explain"><?php esc_html_e( 'Keep the cache folder size below a limit. Every day, WordPress will check if your cache folder is over the selected size limit and will delete the newest cache files until it meets your selected cache folder size limit.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' ) . ', ' . Utils::lumiere_format_bytes( 100 * 1000000 ); // 100 MB is the default size ?></div>

			</div>
			
			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<div class="lumiere_flex_container">
					<div id="imdb_imdbcacheautorefreshcron_id" class="lumiere_padding_right_fifteen">
						<?php esc_html_e( 'Cache auto-refresh', 'lumiere-movies' ); ?>&nbsp;
						<input type="hidden" id="imdb_imdbcacheautorefreshcron_no" name="imdb_imdbcacheautorefreshcron" value="0">
						<input type="checkbox" id="imdb_imdbcacheautorefreshcron_yes" name="imdb_imdbcacheautorefreshcron" value="1" <?php
						if ( $this->imdb_cache_values['imdbcacheautorefreshcron'] === '1' ) {
							echo ' checked="checked"';
						} ?> data-valuemodificator_advanced="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_valuecurrent="0" data-valuemodificator_valuedefault="<?php
					// If the value of 0' is in 'imdbcacheexpire' config, set up the default value of '2592000'
					// This allows to go back to this value instead of keeping 0 when deactivating this field
					echo $this->imdb_cache_values['imdbcacheexpire'] === '0' ? '2592000' : esc_html( $this->imdb_cache_values['imdbcacheexpire'] ); ?>">
					</div>
				</div>
				<div class="explain"><?php esc_html_e( 'Auto-refresh the cache every two weeks. Selecting this option will remove the time expiration of the cache, which will be automatically set to forever.', 'lumiere-movies' ); ?><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' ) ?><div class="lumiere_green"><?php

				// Display next schedule if cron is activated
				$next_cron_run = wp_next_scheduled( 'lumiere_cron_autofreshcache' );
				if ( $next_cron_run !== false ) {
					$next_date_cron = gmdate( 'd/m/Y @H:i:sa', $next_cron_run );
					/* translators: %s is replaced with a date in numbers */
					echo sprintf( esc_html__( 'Auto-refresh activated, next cache refresh will take place on %s', 'lumiere-movies' ), esc_html( $next_date_cron ) );
				}
				?></div>
				</div>


			</div>

		</div>
	</div>
</div>		
			<?php
			//------------------------------------------------------------------ =[Submit selection]
			?>
			<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
					<?php wp_nonce_field( 'cache_options_check', 'cache_options_check' ); ?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
				<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />
			</div>
		</form>
	</div>
			<?php
		}  // end $_GET['cacheoption'] == "option"

		////////////////////////////////////////////// Cache management
		if ( isset( $_GET['cacheoption'] ) && $_GET['cacheoption'] === 'manage' ) {

			// check if folder exists & store cache option is selected
			if ( file_exists( $this->imdb_cache_values['imdbcachedir'] ) ) {
				?>

	<div>
				<?php //--------------------------------------------------------- =[cache delete]=- ?>
		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Global cache management', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">
			<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>" >
				<?php
				wp_nonce_field( 'cache_all_and_query_check', 'cache_all_and_query_check' );
				echo "\n";

				$imdlt_cache_file_count = $this->cache_tools_class->lumiere_cache_countfolderfiles( $this->imdb_cache_values['imdbcachedir'] );

				echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

				echo '<strong>' . esc_html__( 'Total cache size:', 'lumiere-movies' ) . ' ';
				$size_cache_total = $this->cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] );

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

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachemovies" name="cachemovies"><?php esc_html_e( 'Movie\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
				<?php

				// Get list of movies cached files
				$results = $this->cache_tools_class->lumiere_get_movie_cache();

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
						if ( get_class( $res ) === 'Imdb\Title' ) {
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
								$data[] = '	<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
						<img id="pic_' . $title_sanitized . '" class="picfloat" ' . $moviepicturelink . ' width="40px">

						<input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]" class="imdblt_bold">' . $title_sanitized . '</label> <br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_movie ) . ' 
						<div id="refresh_edit_' . $title_sanitized . '" class="row-actions">
						
							<span class="edit"><a id="refreshindividual_' . $title_sanitized . '" href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=refresh&where=' . $obj_sanitized . '&type=movie', 'refreshindividual', '_nonce_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="' . esc_html__( 'Refresh cache for *', 'lumiere-movies' ) . $title_sanitized . '*?">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span>

							<span class="delete"><a id="deleteindividual_' . $title_sanitized . '" href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=delete&where=' . $obj_sanitized . '&type=movie', 'deleteindividual', '_nonce_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '" title="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
						</div></td></tr></table>
					</div>';// send input and results into array

							} //end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort']

						}
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

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachepeople" name="cachepeople"><?php esc_html_e( 'People\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

				<?php
				// Get list of movies cached files
				$results = $this->cache_tools_class->lumiere_get_people_cache();

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
						if ( get_class( $res ) === 'Imdb\Person' ) {
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
					<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
						<img id="pic_' . $name_sanitized . '" class="picfloat" ' . $picturelink . ' width="40px" alt="no pic">
						<input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people_[]" class="imdblt_bold">' . $name_sanitized . '</label><br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_people ) . '
						
						<div class="row-actions">
							<span class="view"><a href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=refresh&where=' . $objpiple_sanitized . '&type=people', 'refreshindividual', '_nonce_refreshindividual' ) . '" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *' . $name_sanitized . '*" title="Refresh cache for *' . $name_sanitized . '*">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span> 

							<span class="delete"><a href="' . wp_nonce_url( $this->page_cache_manage . '&dothis=delete&where=' . $objpiple_sanitized . '&type=people', 'deleteindividual', '_nonce_deleteindividual' ) . '" class="admin-cache-confirm-delete" data-confirm="You are about to delete *' . $name_sanitized . '* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *' . $name_sanitized . '*">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
						</div></td></tr></table>
				</div>'; // send input and results into array.

								flush();
							} // end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort'].

						}
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
				wp_nonce_field( 'cache_options_check', 'cache_options_check' );
				?>

		</form>
	</div>
	<br />
	<br />

				<?php //------------------------------------------------------------------ =[cache directories]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachedirectory" name="cachedirectory"><?php esc_html_e( 'Cache directories', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

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
		<div class="imdblt_padding_five">

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
				$size_cache_pics = $this->cache_tools_class->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbphotoroot'] );

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
			<div class="titresection imdblt_padding_five">
				<?php esc_html_e( 'Photo URL (relative to the website and the cache path)', 'lumiere-movies' ); ?>
			</div>			

			<div class="explain lumiere_breakall">
				<?php esc_html_e( 'URL corresponding to photo directory.', 'lumiere-movies' ); ?> 
				<br />
				<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $this->imdb_cache_values['imdbphotodir'] ); ?>"
			</div>

		</div>

	</div>

	<br />
	<br />

	<div class="submit submit-imdb" align="center">

				<?php wp_nonce_field( 'cache_options_check', 'cache_options_check' ); ?>
		<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
		<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />

		</form>
	</div>

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
				wp_nonce_field( 'cache_options_check', 'cache_options_check' );
				get_submit_button( esc_html__( 'Reset settings', 'lumiere-movies' ), 'primary large', 'reset_cache_options' );
				?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?> " />
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


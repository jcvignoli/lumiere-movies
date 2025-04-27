<?php declare( strict_types = 1 );
/**
 * Template for the options of cache
 *
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package       lumieremovies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

$lumiere_imdb_cache_values = get_option( \Lumiere\Config\Get_Options::get_cache_tablename() );

// Retrieve the vars from calling class.
$lumiere_size_cache_folder = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>

<div class="lumiere_wrap">

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></h3>
	</div>

	<form method="post" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<div class="lumiere_border_shadow">

		<div class="titresection"><?php esc_html_e( 'Main options', 'lumiere-movies' ); ?></div>
			
			<div class="lumiere_display_flex lumiere_flex_make_responsive">
				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<?php esc_html_e( 'Use cache?', 'lumiere-movies' ); ?>&nbsp;
					<input type="hidden" id="imdb_imdbusecache_no" name="imdb_imdbusecache" value="0" data-checkbox_activate="imdb_imdbcacheexpire_id" />

					<input type="checkbox" id="imdb_imdbusecache_yes" name="imdb_imdbusecache" value="1" data-checkbox_activate="imdb_imdbcacheexpire_id"
					<?php
					if ( $lumiere_imdb_cache_values['imdbusecache'] === '1' ) {
						echo ' checked="checked"'; }
					?>/>

					<div class="explain"><?php esc_html_e( 'Whether to use a cached page to retrieve the information (if available).', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'If cache is deactived, pictures will not be displayed and it will take longer to display the page.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

				</div>
				<div id="imdb_imdbcacheexpire_id" class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<label for="imdb_imdbcacheexpire"><?php esc_html_e( 'Cache expire', 'lumiere-movies' ); ?></label><br /><br />
					<div class="lumiere_flex_container">

						<div>
							<input type="text" id="imdb_imdbcacheexpire" name="imdb_imdbcacheexpire" size="7" value="<?php echo esc_html( $lumiere_imdb_cache_values['imdbcacheexpire'] ); ?>" />
						</div>

						<div class="lumiere_padding_ten">
							<input type="checkbox" value="0" id="imdb_imdbcacheexpire_definitive" name="imdb_imdbcacheexpire_definitive" data-valuemodificator="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_default="2592000"
							<?php
							if ( $lumiere_imdb_cache_values['imdbcacheexpire'] === '0' ) {
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
			<div class="titresection"><?php esc_html_e( 'Manage cache page', 'lumiere-movies' ); ?></div>

			<div class="lumiere_display_flex lumiere_flex_make_responsive">

				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<?php esc_html_e( 'Simplified cache data', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="0" />
					<input type="checkbox" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" 
					<?php
					if ( $lumiere_imdb_cache_values['imdbcachedetailsshort'] === '1' ) {
						echo ' checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Allows faster loading time for the "manage cache" option page by taking out pictures and limiting options for movies and people cache. Useful when you have too many cache details to display.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

				</div>
				
				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<?php esc_html_e( 'Do not show cache', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbcachedetailshidden_no" name="imdb_imdbcachedetailshidden" value="0" />
					<input type="checkbox" id="imdb_imdbcachedetailshidden_yes" name="imdb_imdbcachedetailshidden" value="1" 
					<?php
					if ( $lumiere_imdb_cache_values['imdbcachedetailshidden'] === '1' ) {
						echo ' checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Do not display any cache data in "manage cache" option page. Useful when you really have too many cache data to display.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

				</div>
			</div>

				<?php	//------------------------------------------------------------------ =[cache cron]=- ?>
			<div class="titresection"><?php esc_html_e( 'Automatized cache functions', 'lumiere-movies' ); ?></div>

			<div class="lumiere_display_flex lumiere_flex_make_responsive">

				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<div class="lumiere_flex_container">
						<div id="imdb_imdbcachekeepsizeunder_id" class="lumiere_padding_right_fifteen">
							<?php esc_html_e( 'Keep automatically cache size below a limit', 'lumiere-movies' ); ?>&nbsp;
							<input type="hidden" id="imdb_imdbcachekeepsizeunder_no" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="0" />
							<input type="checkbox" id="imdb_imdbcachekeepsizeunder_yes" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="1" <?php
							if ( $lumiere_imdb_cache_values['imdbcachekeepsizeunder'] === '1' ) {
								echo ' checked="checked"';
							} ?> />
						</div>
						<div id="imdb_imdbcachekeepsizeunder_sizelimit_id">
							<input type="text" id="imdb_imdbcachekeepsizeunder_sizelimit"  class="lumiere_width_five_em" name="imdb_imdbcachekeepsizeunder_sizelimit" size="7" value="<?php echo esc_attr( $lumiere_imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ); ?>" /> <i>(size in MB)</i>
						</div>
					</div>

					<div class="explain"><?php esc_html_e( 'Keep the cache folder size below a limit. Every hour, WordPress will check if your cache folder is over the selected size limit and will delete the newest cache files until it match your selected cache folder size limit.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' );
					echo '<br>' . esc_html__( 'Current size used: ', 'lumiere-movies' ) . esc_html( $lumiere_size_cache_folder ); ?></div>

				</div>
				
				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<div class="lumiere_flex_container">
						<div id="imdb_imdbcacheautorefreshcron_id" class="lumiere_padding_right_fifteen">
							<?php esc_html_e( 'Cache auto refresh', 'lumiere-movies' ); ?>&nbsp;
							<input type="hidden" id="imdb_imdbcacheautorefreshcron_no" name="imdb_imdbcacheautorefreshcron" value="0">
							<input type="checkbox" id="imdb_imdbcacheautorefreshcron_yes" name="imdb_imdbcacheautorefreshcron" value="1" <?php
							if ( $lumiere_imdb_cache_values['imdbcacheautorefreshcron'] === '1' ) {
								echo ' checked="checked"';
							} ?> data-valuemodificator_advanced="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_valuecurrent="0" data-valuemodificator_valuedefault="<?php
						// If the value of 0' is in 'imdbcacheexpire' config, set up the default value of '2592000'
						// This allows to go back to this value instead of keeping 0 when deactivating this field
						echo $lumiere_imdb_cache_values['imdbcacheexpire'] === '0' ? '2592000' : esc_html( $lumiere_imdb_cache_values['imdbcacheexpire'] ); ?>">
						</div>
					</div>
					<div class="explain"><?php esc_html_e( 'Automatically refresh the cache over a span of two weeks. Selecting this option will remove the time expiration of the cache, which will be automatically set to forever.', 'lumiere-movies' ); ?><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' ) ?><?php

					// Display next schedule if cron is activated
					$lumiere_next_cron_run = get_transient( 'lum_cache_cron_refresh_time_started' );
					$lum_cron_ppl_left = get_transient( 'lum_cache_cron_refresh_store_people' );
					$lum_cron_mv_left = get_transient( 'lum_cache_cron_refresh_store_movie' );
					// transiant and option are set, the process is running
					if (
						$lumiere_next_cron_run !== false
						&& $lumiere_imdb_cache_values['imdbcacheautorefreshcron'] === '1'
					) {
						$lumiere_next_cron_run = wp_date( get_option( 'date_format' ), intval( $lumiere_next_cron_run ) );
						$lum_total_cron = $lum_cron_ppl_left !== false && $lum_cron_mv_left !== false ? count( $lum_cron_ppl_left ) + count( $lum_cron_mv_left ) : false;
						echo '<div class="lumiere_green">';
						if ( $lum_total_cron === false ) {
							esc_html_e( 'Started refreshing cache, this message will be updated as first batch of files has been run.', 'lumiere-movies' );

						} elseif ( $lumiere_next_cron_run !== false ) {
							/* translators: %s is a number */
							$lum_files = wp_sprintf( _n( '%s file', '%s files', intval( $lum_total_cron ), 'lumiere-movies' ), esc_html( strval( $lum_total_cron ) ) );
							/* translators: %1s is a number + file (singular or plural), %2s is replaced with a date in numbers */
							echo wp_sprintf( esc_html__( 'Currently refreshing the cache, %1$1s remain to be refreshed. A new full refresh will start on %2$2s.', 'lumiere-movies' ), esc_html( $lum_files ), esc_html( $lumiere_next_cron_run ) );
						}
						echo '</div>';
						// no transiant available and option is set, meaning the process has started
					} elseif (
						$lumiere_next_cron_run === false
						&& $lumiere_imdb_cache_values['imdbcacheautorefreshcron'] === '1'
					) {
							echo '<div class="lumiere_green">';
							esc_html_e( 'Started refreshing cache, this message will be updated as first batch of files has been run.', 'lumiere-movies' );
						echo '</div>';
						// a cron is scheduled although the option is unset
					} elseif (
						wp_next_scheduled( 'lumiere_cron_autofreshcache' ) !== false
						&& $lumiere_imdb_cache_values['imdbcacheautorefreshcron'] === '0'
					) {
						echo '<div class="lumiere_red">';
						esc_html_e( 'There is an error with your automatic cache refresh, try to activate it again.', 'lumiere-movies' );
						echo '</div>';
					}

					?>
					</div>


				</div>

			</div>
		</div>


		<!------------------------------------------------------------------- =[Submit selection] -->

		<div class="submit lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );?>
			<input type="submit" class="button-primary" id="lumiere_update_cache_settings" name="lumiere_update_cache_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
			<input type="submit" class="button-primary" id="lumiere_reset_cache_settings" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
		</div>
	</form>
</div>


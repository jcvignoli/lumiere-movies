<?php declare( strict_types = 1 );
/**
 * Template for the options of cache
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

use Lumiere\Settings;

$lumiere_imdb_cache_values = get_option( Settings::LUMIERE_CACHE_OPTIONS );

// Retrieve the vars from calling class.
$lumiere_size_cache_folder = get_transient( 'admin_template_pass_vars' )[0];
?>

	<div id="left-sortables" class="meta-box-sortables">

		<div class="inside lumiere_border_shadow lumiere_margin_btm_twenty">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside lumiere_border_shadow">

		<div class="titresection"><?php esc_html_e( 'General options', 'lumiere-movies' ); ?></div>
		
			<form method="post" name="imdbconfig_save" action="<?php esc_url( $_SERVER['REQUEST_URI'] ?? '' ); ?>">
			
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
				<div class="titresection"><?php esc_html_e( 'Cache details', 'lumiere-movies' ); ?></div>

				<div class="lumiere_display_flex lumiere_flex_make_responsive">

					<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

						<?php esc_html_e( 'Simplified cache details', 'lumiere-movies' ); ?>&nbsp;

						<input type="hidden" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="0" />
						<input type="checkbox" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" 
						<?php
						if ( $lumiere_imdb_cache_values['imdbcachedetailsshort'] === '1' ) {
							echo ' checked="checked"'; }
						?>
						/>

						<div class="explain"><?php esc_html_e( 'Allows faster loading time for the "manage cache" page by displaying shorter movies and people presentation. Usefull when you have many of them.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

					</div>
				</div>

					<?php	//------------------------------------------------------------------ =[cache cron]=- ?>
				<div class="titresection"><?php esc_html_e( 'Cache automatized functions', 'lumiere-movies' ); ?></div>

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

						<div class="explain"><?php esc_html_e( 'Keep the cache folder size below a limit. Every day, WordPress will check if your cache folder is over the selected size limit and will delete the newest cache files until it meets your selected cache folder size limit.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' );
						echo '<br>' . esc_html__( 'Current size used: ', 'lumiere-movies' ) . esc_html( $lumiere_size_cache_folder ); ?></div>

					</div>
					
					<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

						<div class="lumiere_flex_container">
							<div id="imdb_imdbcacheautorefreshcron_id" class="lumiere_padding_right_fifteen">
								<?php esc_html_e( 'Cache auto-refresh', 'lumiere-movies' ); ?>&nbsp;
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
						<div class="explain"><?php esc_html_e( 'Auto-refresh the cache every two weeks. Selecting this option will remove the time expiration of the cache, which will be automatically set to forever.', 'lumiere-movies' ); ?><br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html__( 'No', 'lumiere-movies' ) ?><div class="lumiere_green"><?php

						// Display next schedule if cron is activated
						$lumiere_next_cron_run = wp_next_scheduled( 'lumiere_cron_autofreshcache' );
						if ( $lumiere_next_cron_run !== false && $lumiere_imdb_cache_values['imdbcacheautorefreshcron'] === '1' ) {
							$lumiere_next_cron_run = gmdate( 'd/m/Y @H:i:sa', $lumiere_next_cron_run );
							/* translators: %s is replaced with a date in numbers */
							echo sprintf( esc_html__( 'Auto-refresh activated, next cache refresh will take place on %s', 'lumiere-movies' ), esc_html( $lumiere_next_cron_run ) );
						}
						?></div>
						</div>


					</div>

				</div>
			</div>

			<?php
			//------------------------------------------------------------------ =[Submit selection]
			?>
			<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
				<?php wp_nonce_field( 'lumiere_nonce_cache_settings', '_nonce_cache_settings' );?>
				<input type="submit" class="button-primary" name="lumiere_reset_cache_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
				<input type="submit" class="button-primary" name="lumiere_update_cache_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />

			</div>
			</form>
		</div>
	</div>


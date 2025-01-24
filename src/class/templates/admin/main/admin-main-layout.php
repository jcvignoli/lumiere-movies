<?php declare( strict_types = 1 );
/**
 * Template for the layout options of general page
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
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

$lumiere_imdb_admin_values = get_option( \Lumiere\Tools\Get_Options::get_admin_tablename() );

// Retrieve vars from calling class.
$lumiere_pics_url = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0];
?>
<div class="lumiere_wrap">
	<div class="lumiere_intro_title_options">
		<?php esc_html_e( 'The following options usually do not need further action. Nevertheless, Lumière! can be widely customized to match your needs.', 'lumiere-movies' ); ?>
	</div>

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></h3>
	</div>
	
	<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">

	<div class="lumiere_border_shadow">
			
		<!-- ---------------------------------------------------------------- =[Popup]=- -->

		<div id="popup" class="titresection">
			<img src="<?php echo esc_url( $lumiere_pics_url . 'admin-title-popup.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Modal windows (popups)', 'lumiere-movies' ); ?>
		</div>


		<div class="lumiere_padding_five" id="select_modal_window">&nbsp;
			<?php
			echo esc_html__( 'Modal window type', 'lumiere-movies' ) . '&nbsp;';

			/**
			 * The selection of bootstrap value will remove the options to change
			 * larg/long values of popups
			 *  Dealt also by JS in lumiere_scripts_admin.js
			 */
			?>

			<select name="imdbpopup_modal_window" id="imdbpopup_modal_window">
			<?php
			$lumiere_window_options = [ 'bootstrap', 'highslide', 'classic' ];
			foreach ( $lumiere_window_options as $lumiere_modal_option ) {
				echo "\t" . '<option value="' . esc_attr( $lumiere_modal_option ) . '"';
				if ( $lumiere_imdb_admin_values['imdbpopup_modal_window'] === $lumiere_modal_option ) {
					echo ' selected="selected"';
				}
				echo '>' . esc_html( ucfirst( $lumiere_modal_option ) ) . '</option>';
			} ?>
			</select>
			<?php
			echo '<div class="explain">' . esc_html__( 'Modal windows are the popups that show the movie data when clicking on a name or movie title. "Highslide" or "Bootstrap" are advanced modal windows. "Classic" may be blocked by some browsers.', 'lumiere-movies' ) . '<br>';

			// Extra explanation when bootstrap is selected. Dealt also by JS in lumiere_scripts_admin.js
			$lumiere_hide_for_bootstrap = $lumiere_imdb_admin_values['imdbpopup_modal_window'] !== 'bootstrap' ? 'hidesection' : '';
			/* translators: %1$s and %2$s are html tags */
			echo '<div id="bootstrap_explain" class="' . esc_html( $lumiere_hide_for_bootstrap ) . '">' . wp_kses( sprintf( __( 'Only the width value can be edited with bootstrap modal window. The value entered will be matched against these incremental steps: %1$s300%2$s (small size), %1$s500%2$s (medium size), %1$s800%2$s (large size), %1$s1140%2$s (extra large size)', 'lumiere-movies' ), '<i>', '</i>' ), [ 'i' => [] ] ) . '</div>';

			echo '<div>' . esc_html__( 'Default:', 'lumiere-movies' ) . esc_html( 'Bootstrap' ) . '</div>';
			echo '</div>';
			?>
		</div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_auto lumiere_padding_five" id="imdb_imdbpopuplarg">

				<label for="imdb_imdbpopuplarg_input"><?php esc_html_e( 'Width', 'lumiere-movies' ); ?></label>
				<br>
				<br>
				<input type="text" id="imdb_imdbpopuplarg_input" name="imdb_imdbpopuplarg" size="5" value="<?php echo esc_html( $lumiere_imdb_admin_values['imdbpopuplarg'] ); ?>" >

				<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"800"</div>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five <?php echo $lumiere_imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ? 'hidesection' : '';?>" id="imdb_imdbpopuplong">

				<label for="imdb_imdbpopuplong_input"><?php esc_html_e( 'Height', 'lumiere-movies' ); ?></label>
				<br>
				<br>
				<input type="text" id="imdb_imdbpopuplong_input" name="imdb_imdbpopuplong" size="5" value="<?php echo intval( $lumiere_imdb_admin_values['imdbpopuplong'] ); ?>" >

				<br>
				<div class="explain"><?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies' ); ?> <?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"500"</div>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five <?php echo $lumiere_imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ? 'hidesection' : '';?>" id="imdb_popuptheme">

				<label for="imdb_imdbpopuptheme_select"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br><br>
				<select id="imdb_imdbpopuptheme_select" name="imdb_imdbpopuptheme">
					<option<?php
					if ( $lumiere_imdb_admin_values['imdbpopuptheme'] === 'white' ) {
						echo ' selected="selected"';}
					?> value="white"><?php esc_html_e( 'white (default)', 'lumiere-movies' ); ?></option>
					<option<?php
					if ( $lumiere_imdb_admin_values['imdbpopuptheme'] === 'black' ) {
						echo ' selected="selected"';}
					?> value="black"><?php esc_html_e( 'black', 'lumiere-movies' ); ?></option>
					<option<?php
					if ( $lumiere_imdb_admin_values['imdbpopuptheme'] === 'lightgrey' ) {
						echo ' selected="selected"';}
					?> value="lightgrey"><?php esc_html_e( 'lightgrey', 'lumiere-movies' ); ?></option>
				</select>

				<div class="explain"> <?php esc_html_e( 'Popup color theme', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"white"</div>

			</div>
		</div>
		
		<!-- ---------------------------------------------------------------- =[Theme taxo/inside post/widget]=- -->

		<div id="plainpages" class="titresection">
			<img src="<?php echo esc_url( $lumiere_pics_url . 'admin-title-taxonomy.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
			<?php esc_html_e( 'Plain pages', 'lumiere-movies' ); ?>
		</div>

		<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

			<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br><br>

			<select id="imdb_imdbintotheposttheme" name="imdb_imdbintotheposttheme">
				<option value="grey"
				<?php
				if ( $lumiere_imdb_admin_values['imdbintotheposttheme'] === 'grey' ) {
					echo ' selected="selected"';}
				?>
				><?php esc_html_e( 'grey (default)', 'lumiere-movies' ); ?></option>
				<option value="white"
				<?php
				if ( $lumiere_imdb_admin_values['imdbintotheposttheme'] === 'white' ) {
					echo ' selected="selected"';}
				?>
				><?php esc_html_e( 'white', 'lumiere-movies' ); ?></option>
				<option value="black"
				<?php
				if ( $lumiere_imdb_admin_values['imdbintotheposttheme'] === 'black' ) {
					echo ' selected="selected"';}
				?>
				><?php esc_html_e( 'black', 'lumiere-movies' ); ?></option>
			</select>

			<div class="explain"> <?php esc_html_e( 'Inside the post/widget/taxonomy color theme', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"grey"</div>

		</div>


		<!-- --------------------------------------------------------------- =[Cover picture]=- -->

		<div id="coverpicture" class="titresection">
			<img src="<?php echo esc_url( $lumiere_pics_url . 'cover.jpg' ); ?>" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
			<?php esc_html_e( 'Cover picture', 'lumiere-movies' ); ?>
		</div>

		<div class="lumiere_flex_container">
			<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

				<label for="imdb_imdbcoversize_yes"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="0" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" />

				<input type="checkbox" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" 
				<?php
				if ( $lumiere_imdb_admin_values['imdbcoversize'] === '1' ) {
					echo 'checked="checked" '; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Weither to display a thumbnail or a larger poster for movies included in posts, widgets, popups and taxonomy pages. Untick the box to open a new option and choose a different poster width.', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

			</div>
			<div class="lumiere_flex_container_content_thirty lumiere_padding_five" id="imdb_imdbcoversizewidth_id">

				<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies' ); ?></label><br><br>

				<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php echo intval( $lumiere_imdb_admin_values['imdbcoversizewidth'] ); ?>" />

				<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "100"</div>

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

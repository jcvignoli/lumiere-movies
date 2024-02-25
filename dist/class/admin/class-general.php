<?php declare( strict_types = 1 );
/**
 * General options class
 * Child of Admin_Menu
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2022, Lost Highway
 *
 * @version       1.1
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You can not call directly this page' );
}

/**
 * Display General options menu
 */
class General extends Admin_Menu {

	/**
	 * Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Enter in debug mode
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utils_class->lumiere_activate_debug( $this->imdb_admin_values, 'no_var_dump', null );
		}
	}
	/**
	 *  Display the body
	 */
	protected function lumiere_general_display_body(): void {

		echo '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ?? '' ) . '">';

		if ( ( ( isset( $_GET['generaloption'] ) ) && ( $_GET['generaloption'] === 'base' ) ) || ( ! isset( $_GET['generaloption'] ) ) ) {     ////////// Paths & Layout section
			?>

		<div class="intro_cache">
			<?php esc_html_e( 'The following options usually do not need further action. Nevertheless, Lumière! can be widely customized to match your needs.', 'lumiere-movies' ); ?>
		</div>


		<div class="inside lumiere_border_shadow lumiere_margin_btm_twenty">
			<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside">
			<div class="inside lumiere_border_shadow">

			<?php

				$this->lumiere_general_display_body_popup();

				$this->lumiere_general_display_body_themepicture();

		}
				//////////////// Advanced section
		if ( ( isset( $_GET['generaloption'] ) ) && ( $_GET['generaloption'] === 'advanced' ) ) {

			// The template will retrieve the args. In parent class.
			$this->include_with_vars( 'general/admin-general-advanced', [] /** Add in an array all vars to send in the template */ );

		} // end of advanced section

		//------------------------------------------------------------------ =[Submit selection]=- ?>
		<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field( 'lumiere_nonce_general_settings', '_nonce_general_settings' ); ?>
			<input type="submit" id="lumiere_reset_general_settings" class="button-primary" name="lumiere_reset_general_settings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
			<input type="submit"  id="lumiere_update_general_settings" class="button-primary" name="lumiere_update_general_settings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />
		</div>
		<br>
	</form>
	</div>
		<?php
	}

	/**
	 *  Display the popup section
	 */
	protected function lumiere_general_display_body_popup(): void { ?>

		<!-- ---------------------------------------------------------------- =[Popup]=- -->

		<div id="popup" class="titresection">
			<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-title-popup.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Popup', 'lumiere-movies' ); ?>
		</div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_auto lumiere_padding_five" id="select_modal_window">&nbsp;
				<?php
				echo esc_html__( 'Modal windows', 'lumiere-movies' ) . '&nbsp;';

				/**
				 * The selection of bootstrap value will remove the options to change
				 * larg/long values of popups
				 * Done with javascript lumiere_scripts_admin.js
				 */
				?>

				<select name="imdbpopup_modal_window" id="imdbpopup_modal_window">
				<?php
				echo "\t" . '<option value="bootstrap"';
				if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
					echo ' selected="selected"';
				}
				echo '>Bootstrap</option>';
				echo "\n\t\t\t\t\t\t\t" . '<option value="highslide"';
				if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'highslide' ) {
					echo ' selected="selected"';
				}
				echo '>Highslide</option>';
				echo "\n\t\t\t\t\t\t\t" . '<option value="classic"';
				if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'classic' ) {
					echo ' selected="selected"';
				}
				echo ">Classic</option>\n"; ?>
				</select>
				<?php
				echo '<div class="explain">' . esc_html__( 'Modal windows are the popups that show the movie data when clicking on a name or movie title. Highslide or Bootstrap are advanced modal windows.', 'lumiere-movies' ) . '<br>' . esc_html__( 'When bootstrap is selected, popup layout cannot be edited.', 'lumiere-movies' ) . '<br>' . esc_html__( 'Default:', 'lumiere-movies' ) . esc_html__( 'Bootstrap', 'lumiere-movies' ) . '</div>';
				?>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five <?php if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
				echo 'hidesection'; }?>" id="imdb_imdbpopuplarg">

				<label for="imdb_imdbpopuplarg_input"><?php esc_html_e( 'Width', 'lumiere-movies' ); ?></label><br><br>
				<input type="text" id="imdb_imdbpopuplarg_input" name="imdb_imdbpopuplarg" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbpopuplarg'] ); ?>" >

				<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"540"</div>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five <?php if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
				echo 'hidesection'; }?>" id="imdb_imdbpopuplong">

				<label for="imdb_imdbpopuplong_input"><?php esc_html_e( 'Height', 'lumiere-movies' ); ?></label><br><br>
				<input type="text" id="imdb_imdbpopuplong_input" name="imdb_imdbpopuplong" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbpopuplong'] ); ?>" >

				<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"350"</div>
			</div>

			<div class="lumiere_flex_auto lumiere_padding_five <?php if ( $this->imdb_admin_values['imdbpopup_modal_window'] === 'bootstrap' ) {
				echo 'hidesection'; }?>" id="imdb_popuptheme">

				<label for="imdb_imdbpopuptheme_select"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br><br>
				<select id="imdb_imdbpopuptheme_select" name="imdb_imdbpopuptheme">
					<option<?php
					if ( $this->imdb_admin_values['imdbpopuptheme'] === 'white' ) {
						echo ' selected="selected"';}
					?> value="white"><?php esc_html_e( 'white (default)', 'lumiere-movies' ); ?></option>
					<option<?php
					if ( $this->imdb_admin_values['imdbpopuptheme'] === 'black' ) {
						echo ' selected="selected"';}
					?> value="black"><?php esc_html_e( 'black', 'lumiere-movies' ); ?></option>
					<option<?php
					if ( $this->imdb_admin_values['imdbpopuptheme'] === 'lightgrey' ) {
						echo ' selected="selected"';}
					?> value="lightgrey"><?php esc_html_e( 'lightgrey', 'lumiere-movies' ); ?></option>
				</select>

				<div class="explain"> <?php esc_html_e( 'Popup color theme', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"white"</div>

			</div>
		</div>

		<?php
	}

	/**
	 *  Display the theme and picture section
	 */
	private function lumiere_general_display_body_themepicture(): void {

				//------------------------------------------------------------------ =[Theme taxo/inside post/widget]=- ?>

				<div id="plainpages" class="titresection">
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-title-taxonomy.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
					<?php esc_html_e( 'Plain pages', 'lumiere-movies' ); ?>
				</div>


				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br><br>

					<select id="imdb_imdbintotheposttheme" name="imdb_imdbintotheposttheme">
						<option value="grey"
						<?php
						if ( $this->imdb_admin_values['imdbintotheposttheme'] === 'grey' ) {
							echo ' selected="selected"';}
						?>
						><?php esc_html_e( 'grey (default)', 'lumiere-movies' ); ?></option>
						<option value="white"
						<?php
						if ( $this->imdb_admin_values['imdbintotheposttheme'] === 'white' ) {
							echo ' selected="selected"';}
						?>
						><?php esc_html_e( 'white', 'lumiere-movies' ); ?></option>
						<option value="black"
						<?php
						if ( $this->imdb_admin_values['imdbintotheposttheme'] === 'black' ) {
							echo ' selected="selected"';}
						?>
						><?php esc_html_e( 'black', 'lumiere-movies' ); ?></option>
					</select>

					<div class="explain"> <?php esc_html_e( 'Inside the post/widget/taxonomy color theme', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"grey"</div>

				</div>


			<?php //------------------------------------------------------------------ =[Cover picture]=- ?>

			<div id="coverpicture" class="titresection">
				<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'cover.jpg' ); ?>" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Cover picture', 'lumiere-movies' ); ?>
			</div>

			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_thirty lumiere_padding_five">

					<label for="imdb_imdbcoversize_yes"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="0" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" />

					<input type="checkbox" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" 
					<?php
					if ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
						echo 'checked="checked" '; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Weither to display a thumbnail or a larger poster for movies included in posts, widgets, popups and taxonomy pages. Untick the box to open a new option and choose a different poster width.', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

				</div>
				<div class="lumiere_flex_container_content_thirty lumiere_padding_five" id="imdb_imdbcoversizewidth_id">

					<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies' ); ?></label><br><br>

					<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbcoversizewidth'] ); ?>" />

					<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies' ); ?> <br><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "100"</div>

				</div>
			</div>

		</div>
	</div>
	<br>
	<br>
		<?php
	}

}


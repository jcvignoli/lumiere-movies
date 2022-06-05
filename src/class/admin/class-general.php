<?php declare( strict_types = 1 );
/**
 * General options class
 * Child of Admin
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

use \Lumiere\Utils;
use \Lumiere\Plugins\Logger;

/**
 * @phpstan-import-type OPTIONS_ADMIN_KEY from \Lumiere\Settings
 * // To be used with PHPStan >= 1.3 @phpstan-import-type OPTIONS_ADMIN from Settings
 */
class General extends \Lumiere\Admin {

	/**
	 * HTML allowed for use of wp_kses()
	 */
	const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'strong' => '',
		'br' => '',
	];

	/**
	 * Notification messages
	 * @var array<string, string> $messages
	 */
	public array $messages = [
		'highslide_success' => 'Highslide successfully installed!',
		'highslide_failure' => 'Highslide installation failed!',
		'highslide_down' => 'Website to download Highslide is currently down, please try again later.',
		'highslide_website_unkown' => 'Website variable is not set.',
	];

	/**
	 * Constructor
	 */
	protected function __construct() {

		// Construct parent class
		parent::__construct();

		// Enter in debug mode
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utils_class->lumiere_activate_debug( $this->imdb_admin_values, 'no_var_dump', null );
		}

		// Display notices.
		//add_action( 'admin_notices', 'lumiere_admin_display_messages' );
		$this->lumiere_admin_display_messages();

		// Display the page
		$this->lumiere_general_layout();

	}

	/* Display the layout
	 *
	 *
	 */
	private function lumiere_general_layout (): void {

		$this->lumiere_general_head();
		$this->lumiere_general_display_submenu();
		$this->lumiere_general_display_body();

	}

	/**
	 *  Display admin notices
	 *
	 */
	public function lumiere_admin_display_messages(): ?string {

		if ( ( isset( $_GET['msg'] ) ) && array_key_exists( sanitize_key( $_GET['msg'] ), $this->messages ) ) {
			switch ( sanitize_text_field( $_GET['msg'] ) ) {
				// Message for success
				case 'highslide_success':
					echo Utils::lumiere_notice( 1, esc_html( $this->messages['highslide_success'] ) );
					break;
				// Message for failure
				case 'highslide_failure':
					echo Utils::lumiere_notice( 3, esc_html( $this->messages['highslide_failure'] ) . ' ' . esc_html__( 'Your folder might be protected. Download highslide manually', 'lumiere-movies' ) . " <a href='" . esc_url( \Lumiere\Settings::IMDBBLOGHIGHSLIDE ) . "'>" . esc_html__( 'here', 'lumiere-movies' ) . '</a> ' . esc_html__( 'and extract the zip into', 'lumiere-movies' ) . '<br />' . esc_url( $this->config_class->lumiere_js_dir ) );
					break;
				// Message for website down
				case 'highslide_down':
					echo Utils::lumiere_notice( 3, esc_html( $this->messages['highslide_down'] ) );
					break;
				// Message for website unkown
				case 'highslide_website_unkown':
					echo Utils::lumiere_notice( 3, esc_html( $this->messages['highslide_website_unkown'] ) );
					break;
			}

		}
		return null;
	}

	/*
	 *  Display head
	 *
	 */
	private function lumiere_general_head(): void {

		//--------------------save data selected
		if ( isset( $_POST['update_imdbSettings'] ) ) {

			check_admin_referer( 'options_general_check', 'options_general_check' );

			// Check if $_POST['imdburlstringtaxo'] and $_POST['imdburlpopups'] are identical, because they can't
			$post_imdb_imdburlstringtaxo = isset( $_POST['imdb_imdburlstringtaxo'] ) ? esc_html( $_POST['imdb_imdburlstringtaxo'] ) : 'empty';
			$post_imdb_imdburlpopups = isset( $_POST['imdb_imdburlpopups'] ) ? esc_html( $_POST['imdb_imdburlpopups'] ) : 'empty';

			if (
				( $post_imdb_imdburlstringtaxo !== 'empty' ) &&
			( str_replace( '/', '', $post_imdb_imdburlstringtaxo ) === str_replace( '/', '', $post_imdb_imdburlpopups ) ) || isset( $this->imdb_admin_values['imdburlpopups'] ) && ( str_replace( '/', '', $post_imdb_imdburlstringtaxo ) === str_replace( '/', '', $this->imdb_admin_values['imdburlpopups'] ) )
										||
				( $post_imdb_imdburlpopups !== 'empty' ) &&
			( str_replace( '/', '', $post_imdb_imdburlpopups ) === str_replace( '/', '', $post_imdb_imdburlstringtaxo ) ) || isset( $this->imdb_admin_values['imdburlstringtaxo'] ) && ( str_replace( '/', '', $post_imdb_imdburlpopups ) === str_replace( '/', '', $this->imdb_admin_values['imdburlstringtaxo'] ) )
			) {

				echo Utils::lumiere_notice( 3, esc_html__( 'Wrong values. You can not select the same URL string for taxonomy pages and popups.', 'lumiere-movies' ) );
				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();
			}

			foreach ( $_POST as $key => $postvalue ) {
				$key_sanitized = sanitize_key( $key );
				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );
				if ( isset( $_POST[ $key_sanitized ] ) ) {
					/**
					 * @phpstan-var OPTIONS_ADMIN_KEY $keynoimdb
					 *  // To be used with PHPStan >= 1.3 @phpstan-var key-of<OPTIONS_ADMIN> $keynoimdb
					 */
					$this->imdb_admin_values[ $keynoimdb ] = sanitize_text_field( $_POST[ $key_sanitized ] );
				}
			}

			// update options
			update_option( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS, $this->imdb_admin_values );

			// display message on top
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Options saved.', 'lumiere-movies' ) . '</strong>' );

			// Display a refresh link otherwise refreshed data is not seen
			if ( headers_sent() ) {
				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();
			}

		} elseif ( isset( $_POST['reset_imdbSettings'] ) ) { //---------------------reset options selected

			check_admin_referer( 'options_general_check', 'options_general_check' );

			delete_option( \Lumiere\Settings::LUMIERE_ADMIN_OPTIONS );

			// display message on top
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Options reset.', 'lumiere-movies' ) . '</strong>' );

			// Display a refresh link otherwise refreshed data is not seen
			if ( headers_sent() ) {
				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();
			}

		}
	}

	/**
	 *  Display submenu
	 */
	private function lumiere_general_display_submenu(): void { ?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-general-path.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Paths & Layout', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&generaloption=base' ); ?>"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-general-advanced.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&generaloption=advanced' ); ?>"><?php esc_html_e( 'Advanced', 'lumiere-movies' ); ?></a></div>
	</div>
</div>

<div id="poststuff" class="metabox-holder">
		<?php
	}

	/**
	 *  Display the body
	 */
	private function lumiere_general_display_body(): void {

		echo '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';

		if ( ( ( isset( $_GET['generaloption'] ) ) && ( $_GET['generaloption'] === 'base' ) ) || ( ! isset( $_GET['generaloption'] ) ) ) {     ////////// Paths & Layout section
			?>

		<div class="intro_cache"><?php esc_html_e( 'The following options usually do not need further action. Nevertheless, Lumière! can be widely customized to match your needs.', 'lumiere-movies' ); ?></div>


		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="layout" name="layout"><?php esc_html_e( 'Layout', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside">
			<div class="inside imblt_border_shadow">

			<?php

				$this->lumiere_general_display_body_popup();

				$this->lumiere_general_display_body_themepicture();

		}
				//////////////// Advanced section
		if ( ( isset( $_GET['generaloption'] ) ) && ( $_GET['generaloption'] === 'advanced' ) ) {
			?>

		<div class="intro_cache"><?php esc_html_e( 'The options hereafter can break a lot of things. Edit them only if you know what you are doing.', 'lumiere-movies' ); ?></div>

			<?php
				$this->lumiere_general_display_body_advancedsearch();

				$this->lumiere_general_display_body_advancedmisc();

				$this->lumiere_general_display_body_advancedpaths();

		} // end of advanced section

		//------------------------------------------------------------------ =[Submit selection]=- ?>
		<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
			<?php wp_nonce_field( 'options_general_check', 'options_general_check' ); ?>
			<input type="submit" id="reset_imdbSettings" class="button-primary" name="reset_imdbSettings" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
			<input type="submit"  id="update_imdbSettings" class="button-primary" name="update_imdbSettings" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />
		</div>
		<br />
	</form>
	</div>
		<?php
	}

	/**
	 *  Display the popup section
	 */
	private function lumiere_general_display_body_popup(): void {

				//------------------------------------------------------------------ =[Popup]=- ?>

				<div id="popup" class="titresection">
					<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'admin-title-popup.png' ); ?>" width="80" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
						<?php esc_html_e( 'Popup', 'lumiere-movies' ); ?>
				</div>

				<div class="lumiere_flex_container">

					<div class="lumiere_flex_auto imdblt_padding_five" id="select_modal_window">&nbsp;
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
						echo "\t\t\t\t\t\t\t" . '<option value="bootstrap"';
						if ( $this->imdb_admin_values['imdbpopup_modal_window'] == 'bootstrap' ) {
							echo ' selected="selected"';
						}
						echo '>Bootstrap</option>';
						echo "\n\t\t\t\t\t\t\t" . '<option value="highslide"';
						if ( $this->imdb_admin_values['imdbpopup_modal_window'] == 'highslide' ) {
							echo ' selected="selected"';
						}
						echo '>Highslide</option>';
						echo "\n\t\t\t\t\t\t\t" . '<option value="classic"';
						if ( $this->imdb_admin_values['imdbpopup_modal_window'] == 'classic' ) {
							echo ' selected="selected"';
						}
						echo ">Classic</option>\n"; ?>
						</select>
						<?php
						echo '<div class="explain">' . esc_html__( 'Modal windows are the popups that show the movie data when clicking on a name or movie title. Highslide or Bootstrap are advanced modal windows.', 'lumiere-movies' ) . '<br />' . esc_html__( 'When bootstrap is select, popup height and width cannot be edited.', 'lumiere-movies' ) . '<br />' . esc_html__( 'Default:', 'lumiere-movies' ) . esc_html__( 'Bootstrap', 'lumiere-movies' ) . '</div>';

						// If the folder "highslide" was not found
						if (  is_dir( $this->config_class->lumiere_js_path . 'highslide' ) === false && $this->activate_highslide_download === true && $this->imdb_admin_values['imdbpopup_modal_window'] == 'highslide' ) {
							// Say so!
							echo Utils::lumiere_notice( 4, '<span class="imdblt_red_bold">' . esc_html__( 'Warning! No Highslide folder was found.', 'lumiere-movies' ) . '</span>' );
							echo '<br />';

							// Automatic highslide download.
							echo "<a href='" . esc_url( site_url( '', 'relative' ) . '/wp-admin/admin.php?page=lumiere_options&highslide=yes' ) . '\'' . "' title='" . esc_html__( 'Click here to install Highslide', 'lumiere-movies' ) . "'><img src='" . esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/menu/admin-general-install-highslide.png' ) . "' align='absmiddle' />&nbsp;&nbsp;" . esc_html__( 'Install automatically Highslide', 'lumiere-movies' ) . '</a><br /><br />';

							// Add a link to highslide website.
							echo '<a href="http://highslide.com/" title="' . esc_html__( 'Click here to visit Highslide website', 'lumiere-movies' ) . '"><img src="' . esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-general-install-highslide.png' ) . '" align="absmiddle" />&nbsp;&nbsp;' . esc_html__( 'Get Highslide JS library', 'lumiere-movies' ) . '</a><br /><br />';
						}

						?>
					</div>

					<div class="lumiere_flex_auto imdblt_padding_five 					<?php if ( $this->imdb_admin_values['imdbpopup_modal_window'] == 'bootstrap' ) {
						echo 'hidesection'; }?>" id="imdb_imdbpopuplarg">

						<label for="imdb_imdbpopuplarg"><?php esc_html_e( 'Width', 'lumiere-movies' ); ?></label><br /><br />
						<input type="text" name="imdb_imdbpopuplarg" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbpopuplarg'] ); ?>" >

						<div class="explain"> <?php esc_html_e( 'Popup width, in pixels', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"540"</div>

					</div>
					<div class="lumiere_flex_auto imdblt_padding_five 					<?php if ( $this->imdb_admin_values['imdbpopup_modal_window'] == 'bootstrap' ) {
						echo 'hidesection'; }?>" id="imdb_imdbpopuplong">

						<label for="imdb_imdbpopuplong"><?php esc_html_e( 'Height', 'lumiere-movies' ); ?></label><br /><br />
						<input type="text" name="imdb_imdbpopuplong" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbpopuplong'] ); ?>" >

						<div class="explain"> <?php esc_html_e( 'Popup height, in pixels', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"350"</div>

					</div>

					<div class="lumiere_flex_auto imdblt_padding_five">

						<label for="imdb_imdbpopuptheme"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br /><br />

						<select name="imdb_imdbpopuptheme">
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

						<div class="explain"> <?php esc_html_e( 'Popup color theme', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"white"</div>

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


				<div class="lumiere_flex_container_content_third lumiere_padding_five">

					<label for="imdb_imdbintotheposttheme"><?php esc_html_e( 'Theme color', 'lumiere-movies' ); ?></label><br /><br />

					<select name="imdb_imdbintotheposttheme">
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

					<div class="explain"> <?php esc_html_e( 'Inside the post/widget/taxonomy color theme', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?>"grey"</div>

				</div>


			<?php //------------------------------------------------------------------ =[Cover picture]=- ?>

			<div id="coverpicture" class="titresection">
				<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'cover.jpg' ); ?>" height="80" align="absmiddle" />&nbsp;&nbsp;&nbsp;
				<?php esc_html_e( 'Cover picture', 'lumiere-movies' ); ?>
			</div>

			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_third imdblt_padding_five">

					<label for="imdb_imdbcoversize"><?php esc_html_e( 'Display only thumbnail', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbcoversize_no" name="imdb_imdbcoversize" value="0" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" />

					<input type="checkbox" id="imdb_imdbcoversize_yes" name="imdb_imdbcoversize" value="1" data-checkbox_deactivate="imdb_imdbcoversizewidth_id" 
					<?php
					if ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {
						echo 'checked="checked" '; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Weither to display a thumbnail or a larger poster for movies included in posts, widgets, popups and taxonomy pages. Untick the box to open a new option and choose a different poster width.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

				</div>
				<div class="lumiere_flex_container_content_third imdblt_padding_five" id="imdb_imdbcoversizewidth_id">

					<label for="imdb_imdbcoversizewidth"><?php esc_html_e( 'Size', 'lumiere-movies' ); ?></label><br /><br />

					<input type="text" name="imdb_imdbcoversizewidth" id="imdb_imdbcoversizewidth" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbcoversizewidth'] ); ?>" />

					<div class="explain"><?php esc_html_e( 'Size of the imdb cover picture. The value will correspond to the width in pixels. Delete any value to get maximum width.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "100"</div>

				</div>
			</div>

		</div>

		<br />
		<br />
		<?php
	}

	/**
	 *  Display the search section in advanced part
	 */
	private function lumiere_general_display_body_advancedsearch(): void {  ?>

		<div class="inside">

			<?php //------------------------------------------------------------------ =[Search]=- ?>

		<div class="imblt_border_shadow">
			<h3 class="hndle" id="searchpart" name="searchpart"><?php esc_html_e( 'Search', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">

			<div class="lumiere_display_flex lumiere_flex_make_responsive">

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdblanguage"><?php esc_html_e( 'Search language', 'lumiere-movies' ); ?></label><br /><br />
					<select name="imdb_imdblanguage">
						<option 
						<?php
						if ( ( $this->imdb_admin_values['imdblanguage'] === 'en' ) || ( ! isset( $this->imdb_admin_values['imdblanguage'] ) ) ) {
							echo 'selected="selected" ';
						}
						?>
						value="en"><?php esc_html_e( 'English', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdblanguage'] === 'fr,en' ) {
							echo 'selected="selected"';}
						?>
						value="fr,en"><?php esc_html_e( 'French', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdblanguage'] === 'de,en' ) {
							echo 'selected="selected"';}
						?>
						value="de,en"><?php esc_html_e( 'German', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdblanguage'] === 'es,en' ) {
							echo 'selected="selected"';}
						?>
						value="es,en"><?php esc_html_e( 'Spanish', 'lumiere-movies' ); ?></option>
					</select>

					<div class="explain"><?php esc_html_e( 'Language used for the movie search. Very usefull for a non-English blog using Lumière! as a widget.', 'lumiere-movies' ); ?>
						<br /><br />
						<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "English"
					</div>
				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdbseriemovies"><?php esc_html_e( 'Search categories', 'lumiere-movies' ); ?></label><br /><br />
					<select name="imdb_imdbseriemovies">
						<option 
						<?php
						if ( $this->imdb_admin_values['imdbseriemovies'] === 'movies+series' ) {
							echo 'selected="selected"';
						}
						?>
						value="movies+series"><?php esc_html_e( 'Movies and series', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdbseriemovies'] === 'movies' ) {
							echo 'selected="selected"';
						}
						?>
						value="movies"><?php esc_html_e( 'Movies only', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdbseriemovies'] === 'series' ) {
							echo 'selected="selected"';}
						?>
						value="series"><?php esc_html_e( 'Series only', 'lumiere-movies' ); ?></option>
						<option 
						<?php
						if ( $this->imdb_admin_values['imdbseriemovies'] === 'videogames' ) {
							echo 'selected="selected"';}
						?>
						value="videogames"><?php esc_html_e( 'Video games only', 'lumiere-movies' ); ?></option>
					</select>

					<div class="explain"><?php esc_html_e( 'What type to use for the search, such as movies, series (for TV Shows), and videogames.', 'lumiere-movies' ); ?>
						<br /><br />
						<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "Movies and series"
					</div>
				</div>

				<div class="lumiere_flex_auto imdblt_padding_five">

					<label for="imdb_imdbmaxresults"><?php esc_html_e( 'Limit number of results', 'lumiere-movies' ); ?></label>
					<br />
					<br />

					<input type="text" name="imdb_imdbmaxresults" id="imdb_imdbmaxresults" size="5" value="<?php echo intval( $this->imdb_admin_values['imdbmaxresults'] ); ?>" />

					<div class="explain">
						<?php esc_html_e( 'Limit of the number of results in a movie query.', 'lumiere-movies' ); ?>
						<br /><br />
						<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "10"
					</div>
				</div>
			</div>
		</div>

		<br /><br />

		<?php
	}

	/**
	 *  Display the misc section in advanced part
	 */
	private function lumiere_general_display_body_advancedmisc(): void {

		//------------------------------------------------------------------ =[misc]=- ?>


		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="miscpart" name="miscpart"><?php esc_html_e( 'Misc', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">

			<div class="lumiere_flex_container">

				<div id="imdbwordpress_bigmenu" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Left menu for Lumière options', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbwordpress_bigmenu_no" name="imdb_imdbwordpress_bigmenu" value="0" 
					<?php
					if ( $this->imdb_admin_values['imdbwordpress_bigmenu'] === '0' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<input type="checkbox" id="imdb_imdbwordpress_bigmenu_yes" name="imdb_imdbwordpress_bigmenu" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbwordpress_bigmenu'] === '1' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'If enabled, Lumiere options are displayed in a dedicated menu on the left panel instead of being displayed in the settings menu.', 'lumiere-movies' ); ?> <br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

				</div>
				<div id="imdbwordpress_tooladminmenu" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Top menu for Lumière options', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbwordpress_tooladminmenu_no" name="imdb_imdbwordpress_tooladminmenu" value="0" 
					<?php
					if ( $this->imdb_admin_values['imdbwordpress_tooladminmenu'] === '0' ) {
						echo 'checked="checked" '; }
					?>
					/>

					<input type="checkbox" id="imdb_imdbwordpress_tooladminmenu_yes" name="imdb_imdbwordpress_tooladminmenu" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbwordpress_tooladminmenu'] === '1' ) {
						echo 'checked="checked" '; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'If activated, Lumière options are displayed in a  top menu. Not recommended f you have many plugins already occupying that area.', 'lumiere-movies' ); ?> <br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

				</div>

				<div id="imdbtaxonomy" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Use taxonomy', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbtaxonomy_no" name="imdb_imdbtaxonomy" value="0" />

					<input type="checkbox" id="imdb_imdbtaxonomy_yes" name="imdb_imdbtaxonomy" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) {
						echo ' checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'This will add taxonomy terms found for the movie when display a page with a widget or a into a post. Taxonomy allows to group posts by a series of chosen terms, as explained in', 'lumiere-movies' ); ?> <a href="http://codex.wordpress.org/WordPress_Taxonomy">taxonomy</a>. <?php esc_html_e( 'Taxonomy terms are uninstalled when removing the plugin if you selected not to keep the settings upon uninstall.', 'lumiere-movies' ); ?> <br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?> <?php esc_html_e( '(Activated automatically for "genre" and "director" taxonomies upon installation)', 'lumiere-movies' ); ?></div>

				</div>

				<div id="imdblinkingkill" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Remove all links?', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdblinkingkill_no" name="imdb_imdblinkingkill" value="0" />

					<input type="checkbox" id="imdb_imdblinkingkill_yes" name="imdb_imdblinkingkill" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdblinkingkill'] === '1' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Remove all links (popup and external ones) which are automatically added. Usefull for users who are not interested in popup function. Please note that it will remove every single HTML link as well, such as the the links to the official IMDb website.', 'lumiere-movies' );
					echo ' ';
					esc_html_e( 'Please also note that specific links such as to taxonomy pages and inside the post will be kept.', 'lumiere-movies' ); ?><br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

				</div>

				<div id="imdbautopostwidget" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Auto widget?', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbautopostwidget_no" name="imdb_imdbautopostwidget" value="0" />

					<input type="checkbox" id="imdb_imdbautopostwidget_yes" name="imdb_imdbautopostwidget" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbautopostwidget'] === '1' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Add automatically a widget according to your post title. If regular widgets have been added to post too, the auto widget will be displayed before them. Usefull if blog a lot about movies; if a query does not bring any result with the post title, nothing is displayed.', 'lumiere-movies' ); ?><br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?><?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

				</div>

				<div id="imdbkeepsettings" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Keep settings upon uninstall', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbkeepsettings_no" name="imdb_imdbkeepsettings" value="0" />

					<input type="checkbox" id="imdb_imdbkeepsettings_yes" name="imdb_imdbkeepsettings" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbkeepsettings'] === '1' ) {
						echo 'checked="checked" '; }
					?>
					/>

					<div class="explain"><?php esc_html_e( 'Whether to keep or delete Lumière! settings upon plugin uninstall. Prevent from deleting the taxonomy terms and the cache too.', 'lumiere-movies' ); ?><br /><br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

				</div>

				<div id="imdbdebug" class="lumiere_flex_auto lumiere_padding_five">

					<?php esc_html_e( 'Debug Lumière!', 'lumiere-movies' ); ?>&nbsp;

					<input type="hidden" id="imdb_imdbdebug_no" class="activatehidesectionRemove" name="imdb_imdbdebug" value="0" 
					<?php
					if ( $this->imdb_admin_values['imdbdebug'] === '0' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<input type="checkbox" id="imdb_imdbdebug_yes" class="activatehidesectionAdd" name="imdb_imdbdebug" value="1" 
					<?php
					if ( $this->imdb_admin_values['imdbdebug'] === '1' ) {
						echo 'checked="checked"'; }
					?>
					/>

					<label for="imdb_imdbdebug"></label>
					<br />
					<br />
					<div class="explain"><?php esc_html_e( 'Use integrated debugging functions.', 'lumiere-movies' ); ?></div>
				</div>

				<div class="lumiere_flex_auto lumiere_padding_five hidesectionOfCheckbox">


					<?php esc_html_e( '[Extra debugging options]', 'lumiere-movies' ); ?><br /><br />

					<div class="lumiere_padding_top_bottom_ten">
						<?php esc_html_e( 'Debug verbosity', 'lumiere-movies' ); ?>&nbsp;

						<select name="imdb_imdbdebuglevel">
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'DEBUG' ) {
								echo ' selected="selected" ';}
							?>
							value="DEBUG">Debug</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'INFO' ) {
								echo ' selected="selected" ';}
							?>
							value="INFO">Info</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'NOTICE' ) {
								echo 'selected="selected"';}
							?>
							value="NOTICE">Notice</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'WARNING' ) {
								echo 'selected="selected"';}
							?>
							value="WARNING">Warning</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'ERROR' ) {
								echo 'selected="selected"';}
							?>
							value="ERROR">Error</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'CRITICAL' ) {
								echo 'selected="selected"';}
							?>
							value="CRITICAL">Critical</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'ALERT' ) {
								echo 'selected="selected"';}
							?>
							value="ALERT">Alert</option>
							<option 
							<?php
							if ( $this->imdb_admin_values['imdbdebuglevel'] === 'EMERGENCY' ) {
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
						if ( $this->imdb_admin_values['imdbdebugscreen'] === '1' ) {
							echo ' checked="checked" '; }
						?>
						/>

						<div class="explain"><?php esc_html_e( 'Show the debug log on screen (for administrators only).', 'lumiere-movies' ); ?></div>
					</div>

					<div class="lumiere_padding_top_bottom_ten">
						<?php esc_html_e( 'Save logs', 'lumiere-movies' ); ?>&nbsp;

						<input type="hidden" id="imdb_imdbdebuglog_no" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="0" />

						<input type="checkbox" id="imdb_imdbdebuglog_yes" data-checkbox_activate="imdb_imdbdebuglogpath_id" name="imdb_imdbdebuglog" value="1" 
						<?php
						if ( $this->imdb_admin_values['imdbdebuglog'] === '1' ) {
							echo ' checked="checked" '; }
						?>
						/>

						<div id="imdb_imdbdebuglogpath_id" class="lumiere_padding_top_bottom_ten" >
							<label for="imdb_imdbdebuglogpath"><?php esc_html_e( 'Path', 'lumiere-movies' ); ?></label>
							<input class="lumiere_border_width_medium imdb_imdbdebuglogpath" type="text" name="imdb_imdbdebuglogpath" value="<?php echo esc_attr( $this->imdb_admin_values['imdbdebuglogpath'] ); ?>" >

							<div class="explain"><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'WordPress default debug log', 'lumiere-movies' ); ?></div>
							</div>
						</div>
					</div>
			</div>

		</div>

		<?php
	}

	/**
	 *  Display the paths section in advanced part
	 */
	private function lumiere_general_display_body_advancedpaths(): void {  ?>

		<br />
		<br />

		<div class="imblt_border_shadow">
			<h3 class="hndle" id="directories" name="directories"><?php esc_html_e( 'Paths: url & folders', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">
			<div class="lumiere_intro_options"><?php esc_html_e( 'Edit the following values with caution. It can have unattended effects on your WordPress installation.', 'lumiere-movies' ); ?></div>
			<br />
			<br />

		<div>
			<?php //------------------------------------------------------------------=[ URL Popups ]=---- ?>
			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_twenty">
					<label for="imdb_imdburlpopups"><?php esc_html_e( 'URL for the popups', 'lumiere-movies' ); ?></label>
				</div>
				<div class="lumiere_flex_container_content_eighty">
					<div class="lumiere_align_items_center">
						<?php echo esc_url( get_site_url() ); ?>
						<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlpopups" value="<?php echo esc_html( $this->imdb_admin_values['imdburlpopups'] ); ?>">
					</div>
					<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the movies\' and people\'s popups.', 'lumiere-movies' ); ?>
					<br />
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo '/lumiere/'; ?>"
					<br />
					<br />
					<?php esc_html_e( 'Example: the full URL utilized for the movies\' popups will be:', 'lumiere-movies' ); ?>
					<br />
					<?php echo esc_url( get_site_url() . $this->imdb_admin_values['imdburlpopups'] . 'film' ); ?>
					<br />
					<br />
					<?php esc_html_e( 'Example: the full URL utilized for the people\'s popup will be:', 'lumiere-movies' ); ?>
					<br />
					<?php echo esc_url( get_site_url() . $this->imdb_admin_values['imdburlpopups'] . 'person' ); ?>
					</div>
				</div>
			</div>

			<br /><br />

			<?php //------------------------------------------------------------------=[ URL Taxonomy ]=---- ?>
			<div class="lumiere_flex_container">
				<div class="lumiere_flex_container_content_twenty">
					<label for="imdb_imdburlstringtaxo"><?php esc_html_e( 'URL for the taxonomy pages', 'lumiere-movies' ); ?></label>
				</div>
				<div class="lumiere_flex_container_content_eighty">
					<div class="lumiere_align_items_center">
						<?php echo esc_url( get_site_url() ); ?>/
						<input type="text" class="lumiere_border_width_medium" name="imdb_imdburlstringtaxo" value="<?php echo esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ); ?>">
					</div>
					<div class="explain"><?php esc_html_e( 'The URL that will be displayed for the taxonomy\'s pages.', 'lumiere-movies' ); ?> <?php esc_html_e( 'Warning! This URL cannot be identical to popup\'s URL above.', 'lumiere-movies' ); ?>
					<br />
					<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo 'lumiere-'; ?>"
					<br />
					<br />
					<?php esc_html_e( 'The full URL utilized for the director taxonomy page will be:', 'lumiere-movies' ); ?>
					<br />
					<?php echo esc_url( get_site_url() . '/' . $this->imdb_admin_values['imdburlstringtaxo'] . 'director' ); ?>
					</div>
				</div>
			</div>

		</div>
	</div>
		<?php
	}

}


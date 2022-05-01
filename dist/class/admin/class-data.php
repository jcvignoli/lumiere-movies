<?php declare( strict_types = 1 );
/**
 * Child class for displaying data option selection
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Lumiere\Settings;
use \Lumiere\Utils;
use \Lumiere\Logger;

class Data extends \Lumiere\Admin {

	/**
	 * Notification messages
	 * @var array<string, string> $messages
	 */
	public array $messages = [
		'taxotemplatecopy_success' => 'Template successfully copied.',
		'taxotemplatecopy_failed' => 'Template copy failed!',
		'taxotemplate_newversion' => 'New taxonomy template version, visit the taxonomy options to update.',
	];

	/**
	 * Array of people data details
	 * Built from settings class
	 * @var array<string> $array_people
	 */
	private array $array_people = [];

	/**
	 * Array of items data details
	 * Built from settings class
	 * @var array<string> $array_items
	 */
	private array $array_items = [];

	/**
	 * List of data details that display a field to enter
	 * A limit number in "Display" section
	 * @var array<string> $details_with_numbers
	 */
	private array $details_with_numbers = [];

	/**
	 * List of data details missing in the previous lists
	 * These are not meant to be limited in their numbers, are no taxo items or people
	 * @var array<string> $details_extra
	 */
	private array $details_extra = [];

	/**
	 * Constructor
	 *
	 */
	protected function __construct() {

		// Construct parent class
		parent::__construct();

		// Display notices.
		$this->lumiere_admin_display_messages();

		// Start logger
		$this->logger->lumiere_start_logger( 'adminData' );

		// Build vars from config_class
		$this->array_people = $this->config_class->array_people;
		$this->array_items = $this->config_class->array_items;

		// Build the list of data details that include a number limit
		$this->details_with_numbers = [
			__( 'actor', 'lumiere-movies' ) => __( 'actor', 'lumiere-movies' ),
			__( 'alsoknow', 'lumiere-movies' ) => __( 'also known as', 'lumiere-movies' ),
			__( 'goof', 'lumiere-movies' ) => __( 'goof', 'lumiere-movies' ),
			__( 'plot', 'lumiere-movies' ) => __( 'plot', 'lumiere-movies' ),
			__( 'producer', 'lumiere-movies' ) => __( 'producer', 'lumiere-movies' ),
			__( 'quote', 'lumiere-movies' ) => __( 'quote', 'lumiere-movies' ),
			__( 'soundtrack', 'lumiere-movies' ) => __( 'soundtrack', 'lumiere-movies' ),
			__( 'tagline', 'lumiere-movies' ) => __( 'tagline', 'lumiere-movies' ),
			__( 'trailer', 'lumiere-movies' ) => __( 'trailer', 'lumiere-movies' ),
		];

		// Build the list of the rest
		$this->details_extra = [
			__( 'officialsites', 'lumiere-movies' ) => __( 'official websites', 'lumiere-movies' ),
			__( 'prodcompany', 'lumiere-movies' ) => __( 'production company', 'lumiere-movies' ),
			__( 'rating', 'lumiere-movies' ) => __( 'rating', 'lumiere-movies' ),
			__( 'runtime', 'lumiere-movies' ) => __( 'runtime', 'lumiere-movies' ),
			__( 'source', 'lumiere-movies' ) => __( 'source', 'lumiere-movies' ),
			__( 'year', 'lumiere-movies' ) => __( 'year of release', 'lumiere-movies' ),
		];

		// Debugging mode
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Start the class Utils to activate debug -> already started in admin_pages
			$this->utils_class->lumiere_activate_debug( $this->imdb_widget_values, 'no_var_dump', null );
		}

		// Display the page.
		$this->lumiere_data_layout();

	}

	/**
	 * Display the layout
	 *
	 */
	private function lumiere_data_layout (): void {

		$this->lumiere_data_head();
		$this->lumiere_data_display_submenu();
		$this->lumiere_data_display_body();

	}

	/**
	 *  Display admin notices
	 *
	 */
	public function lumiere_admin_display_messages(): ?string {

		// If $_GET["msg"] is found, display a related notice
		if ( ( isset( $_GET['msg'] ) ) && array_key_exists( sanitize_text_field( $_GET['msg'] ), $this->messages ) ) {
			// Message for success
			if ( sanitize_text_field( $_GET['msg'] ) === 'taxotemplatecopy_success' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['taxotemplatecopy_success'] ) );

				// Message for failure
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'taxotemplatecopy_failed' ) {

				echo Utils::lumiere_notice( 3, esc_html( $this->messages['taxotemplatecopy_failed'] ) );

			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'taxotemplate_newversion' ) {

				echo Utils::lumiere_notice( 3, esc_html( $this->messages['taxotemplate_newversion'] ) );

			}

		}

		return null;

	}

	/**
	 *  Display head
	 *
	 */
	private function lumiere_data_head(): void {

		/* Update options selected
		 *
		 */
		if ( isset( $_POST['update_imdbwidgetSettings'] ) ) {

			check_admin_referer( 'imdbwidgetSettings_check', 'imdbwidgetSettings_check' );

			foreach ( $_POST as $key => $postvalue ) {
				// Sanitize
				$key_sanitized = sanitize_key( $key );

				// Keep $_POST['imdbwidgetorderContainer'] untouched
				if ( $key === 'imdbwidgetorderContainer' ) {
					continue;
				}

				// These $_POST values shouldn't be processed
				if ( $key_sanitized === 'imdbwidgetsettings_check' ) {
					continue;
				}
				if ( $key_sanitized === 'update_imdbwidgetsettings' ) {
					continue;
				}

				// remove "imdb_" from $key
				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );

				// Copy $_POST to $this->imdb_widget_values var
				if ( isset( $_POST[ $key ] ) ) {
					// @phpstan-ignore-next-line 'Array (array('imdbwidgettitle' => string, 'imdbwidgetpic' => string,...)) does not accept key string'.
					$this->imdb_widget_values[ $keynoimdb ] = sanitize_text_field( $_POST[ $key_sanitized ] );
				}
			}

			// Special part related to details order
			if ( isset( $_POST['imdbwidgetorderContainer'] ) ) {
				// Sanitize
				$myinputs_sanitized = Utils::lumiere_recursive_sanitize_text_field( $_POST['imdbwidgetorderContainer'] );
				// increment the $key of one
				$data = array_combine( range( 1, count( $myinputs_sanitized ) ), array_values( $myinputs_sanitized ) );

				// flip $key with $value
				$data = array_flip( $data );

				// Put in the option
				// @phpstan-ignore-next-line 'Array (array<string, array<string>|bool|int|string>) does not accept array<int|string, int>.'
				$this->imdb_widget_values['imdbwidgetorder'] = $data;
			}

			// update options
			update_option( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS, $this->imdb_widget_values );

			// display confirmation message
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Options saved.', 'lumiere-movies' ) . '</strong>' );

			// Flush rewrite rules if updating taxonomy details
			// Needed by WordPress as a new page is created
			if ( count( $this->utils_class->lumiere_array_key_exists_wildcard( $_POST, 'imdb_imdbtaxonomy*' ) ) !== 0 ) {
				flush_rewrite_rules();
				$this->logger->log()->debug( 'Rewrite rules flushed' );
			}

			// Display a refresh link otherwise refreshed data is not seen
			if ( headers_sent() ) {
				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				die();
			}

		}

		/* Reset options selected
		 *
		 */
		if ( isset( $_POST['reset_imdbwidgetSettings'] ) ) {

			check_admin_referer( 'imdbwidgetSettings_check', 'imdbwidgetSettings_check' );

			// Delete the options to reset
			delete_option( \Lumiere\Settings::LUMIERE_WIDGET_OPTIONS );

			// display confirmation message
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
	 *
	 */
	private function lumiere_data_display_submenu(): void { ?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">

		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattodisplay.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to display', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption&widgetoption=what' ); ?>"><?php esc_html_e( 'Display', 'lumiere-movies' ); ?></a></div>

		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-order.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'Display order', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption&widgetoption=order' ); ?>"><?php esc_html_e( 'Display order', 'lumiere-movies' ); ?></a></div>

			<?php if ( $this->imdb_admin_values['imdbtaxonomy'] === '1' ) { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<a title="<?php esc_html_e( 'What to taxonomize', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo' ); ?>"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?></a></div>
			<?php } else { ?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-inside-whattotaxo.png' ); ?>" align="absmiddle" width="16px" />&nbsp;<i><?php esc_html_e( 'Taxonomy unactivated', 'lumiere-movies' ); ?></i></div>
			<?php } ?>

	</div>
</div>


		<?php
	}

	/**
	 *  Display the body
	 *
	 */
	private function lumiere_data_display_body(): void {

		echo "\n\t" . '<div id="poststuff" class="metabox-holder">';
		echo "\n\t\t" . '<div class="inside">';

		//------------------------------------------------------------------ =[Submit selection]=-
		echo "\n\t\t" . '<form method="post" id="imdbconfig_save" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '" >';

		//-------------------------------------------------------------------=[Data selection]=-
		if ( ( isset( $_GET['widgetoption'] ) && ( $_GET['widgetoption'] === 'what' ) ) || ( ! isset( $_GET['widgetoption'] ) ) ) {

			$this->lumiere_data_display_dataselection();

		}

		//-------------------------------------------------------------------=[Taxonomy]=-
		if ( ( isset( $_GET['widgetoption'] ) ) && ( $_GET['widgetoption'] === 'taxo' ) ) {

			$this->lumiere_data_display_taxonomy();

		}

		//-------------------------------------------------------------------=[Order]=-
		if ( ( isset( $_GET['widgetoption'] ) ) && ( $_GET['widgetoption'] === 'order' ) ) {

			$this->lumiere_data_display_order();

		}

		//------------------------------------------------------------------ =[Submit selection]=-
		echo "\n\t\t\t\t" . '<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">' . "\n";
		wp_nonce_field( 'imdbwidgetSettings_check', 'imdbwidgetSettings_check' );
		echo "\n\t\t\t\t" . '<input type="submit" class="button-primary" name="reset_imdbwidgetSettings" value="'
			. esc_html__( 'Reset settings', 'lumiere-movies' )
			. '" />&nbsp;&nbsp;';
		echo "\n\t\t\t"
			. '<input type="submit" class="button-primary" id="update_imdbwidgetSettings" name="update_imdbwidgetSettings" value="'
			. esc_html__( 'Update settings', 'lumiere-movies' )
			. '" />';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</form>';
		echo "\n\t\t" . '</div>';

	}

	/**
	 *  Display the fields for taxonomy selection
	 *
	 */
	private function lumiere_data_display_taxo_fields(): void {

		$array_all = [];
		$array_all = array_merge( $this->array_people, $this->array_items );
		asort( $array_all );

		foreach ( $array_all as $item ) {

			echo "\n\t" . '<div class="imdblt_double_container_content_third lumiere_padding_five">';

			echo "\n\t\t" . '<input type="hidden" id="' . esc_attr( 'imdb_imdbtaxonomy' . $item . '_no' ) . '" name="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '" value="0" />';

			echo "\n\t\t" . '<input type="checkbox" id="' . esc_attr( 'imdb_imdbtaxonomy' . $item . '_yes' ) . '" name="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '" value="1"';

			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				echo 'checked="checked"';
			}

			echo '" />';
			echo "\n\t\t" . '<label for="' . esc_attr( 'imdb_imdbtaxonomy' . $item ) . '">';

			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				if ( $this->imdb_widget_values[ 'imdbwidget' . $item ] === '1' ) {
					echo "\n\t\t" . '<span class="lumiere-option-taxo-activated">';
				} else {
					echo "\n\t\t" . '<span class="lumiere-option-taxo-deactivated">';
				}

				echo esc_html( ucfirst( $item ) );
				echo '</span>';

			} else {
				echo esc_html( ucfirst( $item ) );
				echo '&nbsp;&nbsp;';
			}
			echo "\n\t\t" . '</label>';

			// If new template version available, notify
			if ( $this->imdb_widget_values[ 'imdbtaxonomy' . $item ] === '1' ) {
				// @phpcs:ignore WordPress.Security.EscapeOutput
				echo $this->lumiere_check_taxo_template( $item );
			}
			echo "\n\t" . '</div>';

		}
	}

	/**
	 *  Display Page Order of Data Details
	 *
	 */
	private function lumiere_data_display_order(): void {
		?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="taxoorder" name="taxoorder"><?php esc_html_e( 'Position of data', 'lumiere-movies' ); ?></h3>
	</div>

	<br />

	<div class="imblt_border_shadow imdblt_align_webkit_center">

	<div class="lumiere_intro_options_small">
		<?php esc_html_e( 'You can select the order for the information selected in "display" section. Select first the movie detail you want to move, use "up" or "down" to reorder Lumiere Movies display. Once you are happy with the new order, click on "update settings" to keep it.', 'lumiere-movies' ); ?>
	</div>

	<div id="container_imdbwidgetorderContainer" class="imdblt_double_container imdblt_padding_top_twenty lumiere_align_center lumiere_writing_vertical">

		<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">

			<input type="button" value="up" name="movemovieup" id="movemovieup" data-moveform="-1" /> 

			<input type="button" value="down" name="movemoviedown" id="movemoviedown" data-moveform="+1" />

			<div><?php esc_html_e( 'Move selected movie detail:', 'lumiere-movies' ); ?></div>

			<input type="hidden" name="imdb_imdbwidgetorder" id="imdb_imdbwidgetorder" value="" class="imdblt_hidden" />
		</div>

		<div class="imdblt_padding_ten imdblt_align_last_center imdblt_flex_auto">

		<select id="imdbwidgetorderContainer" name="imdbwidgetorderContainer[]" class="imdbwidgetorderContainer" size="<?php echo ( count( $this->imdb_widget_values['imdbwidgetorder'] ) / 2 ); ?>" style="height:100%;" multiple>
		<?php
		foreach ( $this->imdb_widget_values['imdbwidgetorder'] as $key => $value ) {

			echo "\t\t\t\t\t<option value='" . esc_attr( $key ) . "'";

			// search if "imdbwidget'title'" (ie) is activated
			if ( $this->imdb_widget_values[ "imdbwidget$key" ] !== '1' ) {

				echo ' label="' . esc_attr( $key ) . ' (unactivated)">' . esc_html( $key );

			} else {

				echo ' label="' . esc_attr( $key ) . '">' . esc_html( $key );

			}
			echo "</option>\n";
		}
		?>
						</select>
		</div>

	</div>
</div>

		<?php
	}

	/**
	 *  Display Page Taxonomy
	 *
	 */
	private function lumiere_data_display_taxonomy(): void {

		// taxonomy is disabled
		if ( $this->imdb_admin_values['imdbtaxonomy'] !== '1' ) {

			echo "<div align='center' class='accesstaxo'>"
				. esc_html__( 'Please ', 'lumiere-movies' )
				. "<a href='" . esc_url(
					admin_url()
					. 'admin.php?page=lumiere_options&generaloption=advanced'
				) . "'>"
				. esc_html__( 'activate taxonomy', 'lumiere-movies' ) . '</a>'
				. esc_html__( ' priorly', 'lumiere-movies' ) . '<br />'
				. esc_html__( 'to access taxonomies options.', 'lumiere-movies' ) . '</div>';
			return;
		}
		// Flush rewrite rules when visiting taxonomy page
		// Complements the one executed when saving taxonomy options
		flush_rewrite_rules();
		$this->logger->log()->debug( 'Rewrite rules flushed' );
		?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="taxodetails" name="taxodetails"><?php esc_html_e( 'Select details to use as taxonomy', 'lumiere-movies' ); ?></h3>
	</div>
	<br />

	<div class="imblt_border_shadow">

		<div class="lumiere_intro_options"><?php esc_html_e( "Use the checkbox to display the taxonomy tags. When activated, selected taxonomy will become blue if it is activated in the 'display' section and will turn red otherwise.", 'lumiere-movies' ); ?>
		<br /><br />
		<?php esc_html_e( 'Cautiously select the categories you want to display: it may have some unwanted effects, in particular if you display many movies in the same post at once. When selecting one of the following taxonomy options, it will supersede any other function or link created; for instance, you will not have access anymore to the popups for directors, if directors taxonomy is chosen. Taxonomy will always prevail over other Lumiere functionalities.', 'lumiere-movies' ); ?>

		<br /><br />
		<?php esc_html_e( 'Note: once activated, each taxonomy category will show a new option to copy a taxonomy template directy into your template folder.', 'lumiere-movies' ); ?>
		</div>
		<br /><br />

		<div class="imdblt_double_container">

			<?php
				$this->lumiere_data_display_taxo_fields();
			?>
			<div class="imdblt_double_container_content_third lumiere_padding_five"></div>
		</div>
	</div>

		<?php
	}

	/**
	 *  Display Page of Data Selection
	 *
	 */
	private function lumiere_data_display_dataselection(): void {

		// Merge the list of items and people with two extra lists
		//
		$array_full = array_unique(
			array_merge(
				$this->array_people,
				$this->array_items,
				$this->details_extra,
				$this->details_with_numbers,
			)
		);

		// Sort the array to display in alphabetical order
		asort( $array_full );

		$comment = [
			'actor' => esc_html__( 'Display (how many) actors. These options also applies to the pop-up summary', 'lumiere-movies' ),
			'alsoknow' => esc_html__( 'Display (how many) alternative movie names and in other languages', 'lumiere-movies' ),
			'color' => esc_html__( 'Display colors', 'lumiere-movies' ),
			'composer' => esc_html__( 'Display composer', 'lumiere-movies' ),
			'country' => esc_html__( 'Display country. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'creator' => esc_html__( 'Display Creator', 'lumiere-movies' ),
			'director' => esc_html__( 'Display directors. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'genre' => esc_html__( 'Display genre. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'goof' => esc_html__( 'Display (how many) goofs', 'lumiere-movies' ),
			'keyword' => esc_html__( 'Display keywords', 'lumiere-movies' ),
			'language' => esc_html__( 'Display languages. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'officialsites' => esc_html__( 'Display official websites', 'lumiere-movies' ),
			'pic' => esc_html__( 'Display the main poster', 'lumiere-movies' ),
			'plot' => esc_html__( 'Display plots. This field may need a lot of space.', 'lumiere-movies' ),
			'producer' => esc_html__( 'Display (how many) producers', 'lumiere-movies' ),
			'prodcompany' => esc_html__( 'Display the production companies', 'lumiere-movies' ),
			'quote' => esc_html__( 'Display (how many) quotes from movie.', 'lumiere-movies' ),
			'rating' => esc_html__( 'Display rating. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'runtime' => esc_html__( 'Display the runtime. This option also applies to the pop-up summary', 'lumiere-movies' ),
			'soundtrack' => esc_html__( 'Display (how many) soundtracks', 'lumiere-movies' ),
			'source' => esc_html__( 'Display IMDb website source of the movie', 'lumiere-movies' ),
			'tagline' => esc_html__( 'Display (how many) taglines', 'lumiere-movies' ),
			'title' => esc_html__( 'Display the title', 'lumiere-movies' ),
			'trailer' => esc_html__( 'Display (how many) trailers', 'lumiere-movies' ),
			'writer' => esc_html__( 'Display writers', 'lumiere-movies' ),
			'year' => esc_html__( 'Display release year. The release year will appear next to the movie title into brackets', 'lumiere-movies' ),
		];

		echo "\n\t\t" . '<div class="inside imblt_border_shadow">';
		echo "\n\t\t\t" . '<h3 class="hndle" id="taxodetails" name="taxodetails">'
			. esc_html__( 'What to display', 'lumiere-movies' )
			. '</h3>';
		echo "\n\t\t" . '</div>';
		echo "\n\t\t" . '<br />';

		echo "\n\t\t" . '<div class="imblt_border_shadow">';

		echo "\n\t\t\t" . '<div class="lumiere_flex_container lumiere_align_center">';

		foreach ( $array_full as $item => $title ) {

			echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_third lumiere_padding_ten lumiere_align_center">';

			// Add extra color through span if the item is selected
			if ( $this->imdb_widget_values[ 'imdbwidget' . $item ] === '1' ) {

				echo "\n\t\t\t\t\t" . '<span class="admin-option-selected">' . esc_html( ucfirst( $title ) ) . '</span>';

			} else {

				echo esc_html( ucfirst( $title ) );
				echo '&nbsp;&nbsp;';
			}

			echo "\n\t\t\t\t\t"
				. '<input type="hidden" id="imdb_imdbwidget' . esc_attr( $item ) . '_no"'
				. ' name="imdb_imdbwidget' . esc_attr( $item ) . '" value="0">';

			echo "\n\t\t\t\t\t" . '<input type="checkbox" id="imdb_imdbwidget' . esc_attr( $item ) . '_yes"' .
				' name="imdb_imdbwidget' . esc_attr( $item ) . '" value="1"';

			// Add checked if the item is selected
			if ( $this->imdb_widget_values[ 'imdbwidget' . $item ] === '1' ) {
				echo ' checked="checked"';
			}

			// If item is in list of $details_with_numbers, add special section
			if ( array_key_exists( $item, $this->details_with_numbers ) ) {
				echo ' data-checkbox_activate="imdb_imdbwidget' . esc_attr( $item ) . 'number_div" />';

				echo "\n\t\t\t\t\t" . '<div id="imdb_imdbwidget' . esc_attr( $item ) . 'number_div" class="lumiere_flex_container lumiere_padding_five">';

				echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_seventy lumiere_font_ten">' . esc_html__( 'Enter the maximum of items you want to display', 'lumiere-movies' ) . '<br /></div>';

				echo "\n\t\t\t\t\t\t" . '<div class="lumiere_flex_container_content_twenty">';
				echo "\n\t\t\t\t\t\t\t" . '<input type="text" class="lumiere_width_two_em" name="imdb_imdbwidget' . esc_attr( $item ) . 'number" size="3"';
				// @phpstan-ignore-next-line 'Parameter #1 $text of function esc_html expects string, array<string>|bool|int|string given'.
				echo ' value="' . esc_html( $this->imdb_widget_values[ 'imdbwidget' . $item . 'number' ] ) . '" ';
				if ( $this->imdb_widget_values[ 'imdbwidget' . $item ] === 0 ) {
					echo 'disabled="disabled"';
				};

				echo ' />';
				echo "\n\t\t\t\t\t\t" . '</div>';

				echo "\n\t\t\t\t\t" . '</div>';

				// item is not in list of $details_with_numbers
			} else {

				echo ' >';

			}

			echo "\n\t\t\t\t\t" . '<div class="explain">' . esc_html( $comment[ $item ] ) . '</div>';

			echo "\n\t\t\t\t" . '</div>';
		}

		// Reach a multiple of three for layout
		// Include extra lines if not multiple of three
		$operand = ( count( $array_full ) / ( count( $array_full ) / 3 ) );
		for ( $i = 1; $i < $operand; $i++ ) {
			if ( $i % 3 !== 0 ) {
				echo "\n\t\t\t\t" . '<div class="lumiere_flex_container_content_third lumiere_padding_ten lumiere_align_center"></div>';
			}
		}

		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t" . '</div>';
		echo "\n\t" . '</div>';

	}

	/**
	 * Function checking if item/person template is missing or if a new one is available
	 * Returns a link to copy the template if true and a message explaining if missing/update the template
	 *
	 * @param string $type type to search (actor, genre, etc)
	 * @return string
	 */
	private function lumiere_check_taxo_template( string $type ): string {

		global $wp_filesystem;

		// Initialize
		$output = '';
		$version_theme = 'no_theme';
		$version_origin = '';
		$pattern = '~Version: (.+)~i';

		// Get the type to build the links
		$lumiere_taxo_title = esc_html( $type );

		// Files paths
		$lumiere_taxo_file_tocopy = in_array( $lumiere_taxo_title, $this->config_class->array_people, true ) ? Settings::TAXO_PEOPLE_THEME : Settings::TAXO_ITEMS_THEME;
		$lumiere_taxo_file_copied = 'taxonomy-' . $this->imdb_admin_values['imdburlstringtaxo'] . $lumiere_taxo_title . '.php';
		$lumiere_current_theme_path = get_stylesheet_directory() . '/';
		$lumiere_current_theme_path_file = $lumiere_current_theme_path . $lumiere_taxo_file_copied;
		$lumiere_taxonomy_theme_path = $this->imdb_admin_values['imdbpluginpath'];
		$lumiere_taxonomy_theme_file = $lumiere_taxonomy_theme_path . $lumiere_taxo_file_tocopy;

		// Make sure we have the credentials
		Utils::lumiere_wp_filesystem_cred( $lumiere_current_theme_path_file );

		// Make the HTML link with a nonce, checked in move_template_taxonomy.php.
		$link_taxo_copy = esc_url( add_query_arg( '_wpnonce', wp_create_nonce( 'taxo' ), admin_url() . 'admin.php?page=lumiere_options&subsection=dataoption&widgetoption=taxo&taxotype=' . $lumiere_taxo_title ) );

		// No file in the theme folder found, offer to copy it.
		if ( file_exists( $lumiere_current_theme_path_file ) === false ) {

			$output .= "\n\t" . '<br />';
			$output .= "\n\t" . '<div id="lumiere_copy_' . $lumiere_taxo_title . '">';
			$output .= "\n\t\t<a href='"
					. $link_taxo_copy
					. "' title='"
					. esc_html__( 'Copy a standard taxonomy template to your template folder to display this taxonomy.', 'lumiere-movies' )
					. "' ><img src='"
					. esc_url(
						$this->config_class->lumiere_pics_dir
						. 'menu/admin-widget-copy-theme.png'
					)
					. "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />"
					. esc_html__( 'Copy template', 'lumiere-movies' )
					. '</a>';

			$output .= "\n\t" . '<div><font color="red">'
				. esc_html( "No $lumiere_taxo_title template found" )
				. '</font></div>';
			$output .= "\n\t" . '</div>';

			return $output;

		}

		// Get the taxonomy file version in the theme.
		$content = $wp_filesystem->get_contents( $lumiere_current_theme_path_file );
		if ( preg_match( $pattern, $content, $match ) === 1 ) {
			$version_theme = $match[1];

		}

		// No copied taxonomy file in theme folder exists, exit.
		if ( file_exists( $lumiere_taxonomy_theme_file ) === false ) {

			return "\n\t" . '<br /><div><i>' . esc_html__( 'Missing Lumiere template file. A problem has been detected with your installation.', 'lumiere-movies' ) . '</i></div>';

		}

		// Get the taxonomy file version in the lumiere theme folder.
		$content = $wp_filesystem->get_contents( $lumiere_taxonomy_theme_file );
		if ( preg_match( $pattern, $content, $match ) === 1 ) {

			$version_origin = $match[1];

		}

		// Return a message if there is a new version of the template.
		if ( $version_theme !== $version_origin ) {

			$output .= "\n\t" . '<br />';
			$output .= "\n\t" . '<div id="lumiere_copy_' . $lumiere_taxo_title . '">';
			$output .= "\n\t\t<a href='"
					. $link_taxo_copy
					. "' title='"
					. esc_html__( 'Copy a standard taxonomy template to your template folder to display this taxonomy.', 'lumiere-movies' )
					. "' ><img src='" . esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-widget-copy-theme.png' ) . "' alt='copy the taxonomy template' align='absmiddle' align='absmiddle' />"
					. esc_html__( 'Copy template', 'lumiere-movies' ) . '</a>';

			$output .= "\n\t" . '<div><font color="red">'
				. esc_html( "New $lumiere_taxo_title template version available" )
				. '</font></div>';
			$output .= "\n\t" . '</div>';

			return $output;

		}

		return "\n\t" . '<br /><div><i>' . ucfirst( $lumiere_taxo_title ) . ' ' . esc_html__( 'template up-to-date', 'lumiere-movies' ) . '</i></div>';
	}

}


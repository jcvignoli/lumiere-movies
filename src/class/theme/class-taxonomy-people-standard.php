<?php declare( strict_types = 1 );
/**
 * Template People: Taxonomy for Lumière! Movies WordPress plugin (set up for standard people taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.5.4
 *
 * This template retrieves automaticaly the occurence of the name selected
 * If used along with Polylang WordPress plugin, a form is displayed to filter by available language
 * Almost compatible with AMP WordPress plugin, because WP submit_button() does not seem to be yet AMP compliant
 * It uses \Lumiere\Frontend trait and builds its $this->link_maker var
 *
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Imdb\Person;
use Imdb\PersonSearch;
use Lumiere\Plugins\Polylang;
use Lumiere\Link_Makers\Link_Factory;
use WP_Query;

class Taxonomy_People_Standard {

	// Use trait frontend.
	use \Lumiere\Frontend\Main {
		\Lumiere\Frontend\Main::__construct as public __constructFrontend;
	}

	/**
	 * Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

	/**
	 * Polylang plugin object from its class
	 * Can be null if Polylang is not active
	 *
	 * @since 3.7.1
	 * @var Polylang $plugin_polylang
	 */
	private ?Polylang $plugin_polylang = null;

	/**
	 * Class \Imdb\Person
	 */
	private Person $person_class;

	/**
	 * Name of the person sanitized
	 *
	 * @var string $person_name
	 */
	private string $person_name;

	/**
	 * Taxonomy category
	 *
	 * @var string $taxonomy_title
	 */
	private string $taxonomy_title;

	/**
	 * Constructor
	 * @since 3.12 Ban bots from downloading the page.
	 */
	public function __construct( ?Polylang $plugin_polylang = null ) {

		// Ban bots.
		do_action( 'lumiere_ban_bots' );

		// Construct Frontend trait.
		$this->__constructFrontend( 'taxonomy-standard' );

		// Initialise $plugin_polylang.
		if ( ( class_exists( 'Polylang' ) ) && ( $plugin_polylang instanceof Polylang ) && $plugin_polylang->polylang_is_active() === true ) {
			$this->plugin_polylang = $plugin_polylang;
		}

		/**
		 * Start PluginsDetect class
		 * Is instanciated only if not instanciated already
		 * Use lumiere_set_plugins_array() in trait to set $plugins_in_use var in trait
		 * @since 3.8
		 */
		if ( count( $this->plugins_in_use ) === 0 ) {
			$this->lumiere_set_plugins_array();
		}

		// Display the page. Must not be included into an add_action(), as should be displayed directly.
		$this->lumiere_taxo_layout_standard();

		// Remove action that breaks everything in trait-frontend.php, function is executed later
		remove_action( 'wp_head', [ $this, 'remove_lumiere_set_plugins_array' ], 0 );

	}

	/**
	 * Remove action that breaks everything in current class
	 * Action added in trait-frontend.php
	 *
	 * @since 3.7
	 */
	public function remove_lumiere_set_plugins_array(): void {

		remove_action( 'wp_head', [ $this, 'lumiere_set_plugins_array' ], 0 );

	}

	/**
	 * Do the search according to the page title using IMDbPHP classes
	 * Start the logging process
	 */
	private function lumiere_process_imdbphp_search(): void {

		do_action( 'lumiere_logger' );

		// Build the current page name from the tag taxonomy.
		// Sanitize_title() ensures that the search is made according to the URL (fails with accents otherwise)
		$page_title_check = sanitize_title( single_tag_title( '', false ) ?? '' );

		// Full taxonomy title.
		$this->taxonomy_title = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		// If we are in a WP taxonomy page, the info from imdbphp libraries.
		$search = new PersonSearch( $this->imdbphp_class, $this->logger->log() );
		$results = $search->search( $page_title_check ); // search for the person using the taxonomy tag.
		if ( array_key_exists( 0, $results ) ) {
			$mid = $results[0]->imdbid(); // keep the first result only.
			$mid_sanitized = esc_html( $mid ); // sanitize the first result.
			$this->person_class = new Person( $mid_sanitized, $this->imdbphp_class, $this->logger->log() ); // search the profile using the first result.
			$this->person_name = $this->person_class->name();
		}
	}

	/**
	 * Display the layout
	 */
	private function lumiere_taxo_layout_standard(): void {

		get_header();

		// Start IMDbPHP search.
		$this->lumiere_process_imdbphp_search();

		// Build array of plugins from trait-frontend.php.
		$this->lumiere_set_plugins_array();

		// Simplify coding.
		$logger = $this->logger->log();

		// Build the Link Maker var in trait.
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		$logger->debug( '[Lumiere][taxonomy_' . $this->taxonomy_title . '] Using the link maker class: ' . get_class( $this->link_maker ) );

		// Log PluginsDetect.
		$logger->debug( '[Lumiere][taxonomy_' . $this->taxonomy_title . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_in_use ) . ']' );

		echo '<br />';

		if ( true === $this->activate_sidebar ) {
			get_sidebar();
		}
		?>

		<main id="main" class="site-main clr" role="main">
			<div id="content-wrap" class="container clr">
		<?php

		if ( strlen( $this->person_name ) > 0 ) {

			$this->portrait();

		} else { // end of section if a result was found for the taxonomy.

			// No imdb result, so display a basic title.
			$title_from_tag = single_tag_title( '', false );
			echo "\n\t\t" . '<h1 class="pagetitle">' . esc_html__( 'Taxonomy for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . ' as <i>standard</i></h1>';
			echo "\n\t\t" . '<div>' . esc_html__( 'No IMDb result found for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . '</div>';

		}

		// Language from the form.
		// @phpcs:ignore WordPress.Security.NonceVerification -- It is process later!
		$get_lang_form = isset( $_POST['tag_lang'] ) && is_string( $_POST['tag_lang'] ) && strlen( $_POST['tag_lang'] ) > 0 ? filter_input( INPUT_POST, 'tag_lang', FILTER_SANITIZE_FULL_SPECIAL_CHARS ) : null;
		$form_id_language =
			isset( $_POST['_wpnonce_polylangform'] ) && is_string( $_POST['_wpnonce_polylangform'] ) && wp_verify_nonce( $_POST['_wpnonce_polylangform'], 'polylangform' ) !== false
			&& $get_lang_form !== false
			? $get_lang_form
			: null;

		/**
		 *  For every type of role (writer, director) do a WP Query Loop
		 */

		// Var to include all rows and check if it is null.
		$check_if_no_result = [];

		echo "\n\t\t\t\t" . '<div class="lumiere_taxo_results"><!-- taxo_results -->';

		foreach ( $this->config_class->array_people as $people => $people_translated ) {

				// A value was passed in the form.
			if ( $form_id_language !== null ) {

				$args = [
					'post_type' => [ 'post' ],
					'post_status' => 'publish',
					'numberposts' => -1,
					'nopaging' => true,
					'tax_query' => [
						// @phan-suppress-next-line PhanPluginMixedKeyNoKey Should not mix array entries of the form [key => value,] with entries of the form [value,]. -- Since WordPress accepts it, it's ok!
						'relation' => 'AND',
						[
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field' => 'name',
							'terms' => $this->person_name,
						],
						[
							'taxonomy' => 'language',
							'field' => 'term_taxonomy_id',
							'terms' => $form_id_language,
						],
					],
				];

				// No value was passed in the form.
			} elseif ( strlen( $this->person_name ) > 0 ) {

				$args = [
					'post_type' => [ 'post' ],
					'tax_query' => [
						[
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field' => 'name',
							'terms' => $this->person_name,
						],
					],
				];

			}

			// The Query.
			$the_query = strlen( $this->person_name ) > 0 && isset( $args ) ? new WP_Query( $args ) : null;

			// The loop.
			if ( isset( $the_query ) && $the_query->have_posts() ) {

				echo "\n\t\t\t\t" . '<h2 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies' ) . ' ' . esc_html( $people_translated ) . '</h2>';

				while ( $the_query->have_posts() ) {
					$the_query->the_post();
					?>

						<div class="postList">
							<h3 id="post-<?php the_ID(); ?>">
								<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies' ); ?><?php the_title(); ?>">
									<?php the_title(); ?> <span class="lumiere_font_12">(<?php the_time( 'd/m/Y' ); ?>)</span>
								</a>
							</h3>
						<?php
						/**
							 * Too many results, deactivated
							if (get_terms( esc_html( $this->taxonomy_title )){ ?>

						<div class="taxonomy"><?php
							esc_html_e( 'Taxonomy', 'lumiere-movies' );
							echo " $people:";
							echo get_the_term_list(get_the_ID(), $this->taxonomy_title, ' ', ', ', '' ); ?>
						<br /><br />
						</div>
						<?php } */

						?>
				<div class="lumiere_display_flex">
					<?php /* Ugly layout, deactivated
					<div class="lumiere_padding_15">
					// Display the post's thumbnail.
					$thumbnail = get_the_post_thumbnail( null, '', [ 'class' => '' ] );
					if ( strlen( $thumbnail ) !== 0 ) {
						echo get_the_post_thumbnail( null, '', [ 'class' => '' ] );
					}
					echo "\n";
					echo '</div>';
					*/ ?>
					<div class="">
						<?php the_excerpt(); ?>
					</div>
				</div>
				<p class="postmetadata lumiere_align_center lumiere_padding_five">
					<span class="category"><?php esc_html_e( 'Filed under: ', 'lumiere-movies' ); ?> <?php the_category( ', ' ); ?></span>
					<?php
					$tags_list = get_the_tag_list();
					if ( ( $tags_list !== false ) && ( is_wp_error( $tags_list ) === false ) ) {
						?>
							<strong>|</strong>
							<span class="tags"><?php the_tags( esc_html__( 'Tags: ', 'lumiere-movies' ), ' &bull; ', ' ' ); ?></span>
						<?php
						echo "\n";
					}
					?>
						<strong>|</strong> 
						<?php
						comments_popup_link( 'No Comments &#187;', '1 Comment &#187;', '% Comments &#187;' );
						echo "\n";
						?>
				</p>
			</div>
					<?php

					$check_if_no_result[] = get_the_title();

				}

				// there is no post.
			} elseif ( isset( $the_query ) === false && strlen( $this->person_name ) > 0 ) {

				$logger->debug( "[Lumiere][taxonomy_$this->taxonomy_title] No post found for $this->person_name in $people" );

			}

		}

		// Restore original Post Data.
		wp_reset_postdata();

		/**
		 * If no results are found at all
		 * Say so!
		 */
		if ( count( $check_if_no_result ) === 0 && strlen( $this->person_name ) > 0 ) {

			$this->logger->log()->info( '[Lumiere][taxonomy_' . $this->taxonomy_title . '] No post found for ' . $this->person_name . ' in ' . $this->taxonomy_title );

			echo '<div class="lumiere_align_center lumiere_italic lumiere_padding_five">No post written about ' . esc_html( $this->person_name ) . '</div>';

		}
		?>
				</div><!-- taxo_results -->
			</div>
		</main>

		<?php
		wp_meta();

		get_footer();

	}

	/**
	 *  Display People data details
	 *
	 */
	private function portrait(): void {

		echo "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Photo & identity -->';
		echo "\n\t\t" . '<div class="lumiere_container lumiere_font_em_11 lumiere_align_center">';
		echo "\n\t\t\t" . '<div class="lumiere_flex_auto">';

		echo "\n\t\t\t\t" . '<div class="imdbelementTITLE ';
		echo ' imdbelementTITLE_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo '">';
		echo esc_html( $this->person_name );
		echo '</div>';

		/**
		 * Use Highslide, Classical or No Links class links builder.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp pics only if cache is active
			// @phpcs:ignore WordPress.Security.EscapeOutput
			echo $this->link_maker->lumiere_link_picture_taxonomy(
				esc_html( $this->person_class->photo_localurl( false ) ),
				esc_html( $this->person_class->photo_localurl( true ) ),
				esc_html( $this->person_name )
			);
		} else { // no_pics otherwise
			// @phpcs:ignore WordPress.Security.EscapeOutput
			echo $this->link_maker->lumiere_link_picture_taxonomy(
				esc_html( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ),
				esc_html( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ),
				esc_html( $this->person_name )
			);
		}

		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo '">';
		echo '<font size="-1">';

		# Birth
		$birthday = $this->person_class->born() ?? null;
		if ( $birthday !== null && count( $birthday ) !== 0 ) {
			$birthday_day = $birthday['day'] ?? '';
			$birthday_month = $birthday['month'] ?? '';
			$birthday_year = $birthday['year'] ?? '';

			echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
				. '&#9788;&nbsp;'
				. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
				. intval( $birthday_day ) . ' '
				. esc_html( $birthday_month ) . ' '
				. intval( $birthday_year );
		} else {
			echo '&nbsp;';
		}

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
		}

		echo "\n\t\t\t\t" . '</font></div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Death -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo '">';
		echo '<font size="-1">';

		# Death
		$death = ( count( $this->person_class->died() ) !== 0 ) ? $this->person_class->died() : null;
		if ( $death !== null ) {

			echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
				. '&#8224;&nbsp;'
				. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
				. intval( $death['day'] ) . ' '
				. esc_html( $death['month'] ) . ' '
				. intval( $death['year'] );

			if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
				echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
			}

			if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
				echo ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . esc_html( $death['cause'] );
			}

		}

		echo "\n\t\t\t\t" . '</font></div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo ' lumiere-lines-common-fix">';
		echo '<font size="-1">';

		// Biography, function in trait.
		// @phpcs:ignore WordPress.Security.EscapeOutput
		echo $this->link_maker->lumiere_medaillon_bio( $this->person_class->bio() );

		echo "\n\t\t\t\t\t" . '</font></div>';
		echo "\n\t\t\t\t" . '</div>';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t\t" . '<br />';

		// Compatibility with Polylang WordPress plugin, add a form to filter results by language.
		// Function in class Polylang.
		if ( isset( $this->plugin_polylang ) ) {

			$this->plugin_polylang->lumiere_get_form_polylang_selection( $this->taxonomy_title, $this->person_name );

		}

		echo "\n\t\t\t" . '<br />';

	}

}

$lumiere_taxonomy_people_standard_class = new Taxonomy_People_Standard( new Polylang() );

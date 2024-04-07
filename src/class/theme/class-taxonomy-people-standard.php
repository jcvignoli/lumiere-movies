<?php declare( strict_types = 1 );
/**
 * Template People: Taxonomy for Lumière! Movies WordPress plugin (set up for standard people taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.6
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
use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Frontend\Main;
use WP_Query;

/**
 * This template retrieves automaticaly all post related to a person taxonomy
 * It is a virtual page created according to Lumiere taxonomy
 * If used along with Polylang WordPress plugin, a form is displayed to filter by available language
 *
 * @see \Lumiere\Frontend\Taxonomy That build the taxonomy system and taxonomy pages
 * @see \Lumiere\Frontend Trait to builds $this->link_maker var
 *
 * @since 4.1 Use of plugins detection, lumiere_medaillon_bio() returns larger number of characters for introduction, Polylang form with AMP works
 */
class Taxonomy_People_Standard {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

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
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->start_main_trait();

		/**
		 * Start Plugins_Start class in trait
		 * Set $plugins_active_names and $plugins_classes_active var in trait
		 * @since 3.8
		 */
		if ( count( $this->plugins_active_names ) === 0 ) {
			$this->activate_plugins();
		}
	}

	/**
	 * Static start
	 */
	public static function lumiere_static_start(): void {
		$class = new self();

		// Start AMP headers if AMP page, not really in use
		if ( in_array( 'amp', $class->plugins_active_names, true ) === true ) {
			// $this->amp_headers(); // Debug and check
			add_action( 'wp_ajax_amp_comment_submit', [ $class, 'amp_form_submit' ] );
			add_action( 'wp_ajax_nopriv_amp_comment_submit', [ $class, 'amp_form_submit' ] );
		}

		// Display the page. Must not be included into an add_action(), as should be displayed directly.
		$class->lumiere_taxo_layout_standard();
	}

	/**
	 * Use specific headers if it is an AMP submission
	 * Meant to allow a $_GET insted of a $_POST form submission, thus using ajax, not in use
	 */
	public function amp_form_submit(): void {

		if ( isset( $_GET['submit_lang'], $_GET['tag_lang'] ) ) {

			if ( strlen( $_GET['tag_lang'] ) > 0 ) {
				$success = true;
				wp_send_json( [ 'success' => true ] );
				$message = __( 'Language successfully changed.', 'lumiere-movies' );
			} else {
				$success = false;
				$message = __( 'Could not change the language.', 'lumiere-movies' );
				wp_send_json(
					[
						'msg' => __( 'No data passed', 'lumiere-movies' ),
						'response' => esc_html( $_GET['tag_lang'] ),
						'back_link' => true,
					]
				);
			}

			header( 'AMP-Redirect-To: ' . wp_sanitize_redirect( $_GET['_wp_http_referer'] ?? '' ) );

			wp_die(
				esc_html( $message ),
				'',
				[ 'response' => $success ? 200 : 400 ]
			);

		}
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
		$search = new PersonSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log() );
		$results = $search->search( $page_title_check ); // search for the person using the taxonomy tag.
		if ( array_key_exists( 0, $results ) ) {
			$mid = $results[0]->imdbid(); // keep the first result only.
			$mid_sanitized = esc_html( $mid ); // sanitize the first result.
			$this->person_class = new Person( $mid_sanitized, $this->plugins_classes_active['imdbphp'], $this->logger->log() ); // search the profile using the first result.
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

		// Simplify coding.
		$logger = $this->logger->log();

		// Build the Link Maker var in trait.
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		$logger->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . get_class( $this->link_maker ) );

		// Log Plugins_Start.
		$logger->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_active_names ) . ']' );

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
		// @phpcs:ignore WordPress.Security.NonceVerification -- It is processed later!
		$get_lang_form = isset( $_GET['tag_lang'] ) && strlen( $_GET['tag_lang'] ) > 0 ? $_GET['tag_lang'] : null;
		$form_id_language =
			isset( $_GET['_wpnonce_lum_taxo_polylangform'] ) && wp_verify_nonce( $_GET['_wpnonce_lum_taxo_polylangform'], 'lum_taxo_polylangform' ) > 0
			&& isset( $get_lang_form ) && strlen( $get_lang_form ) > 0
			? esc_html( $get_lang_form )
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
			} elseif ( ! isset( $the_query ) || $the_query->have_posts() || strlen( $this->person_name ) > 0 ) {

				$logger->debug( '[Lumiere][' . $this->classname . '] No post found for ' . "$this->person_name in $people" );

			}

		}

		// Restore original Post Data.
		wp_reset_postdata();

		/**
		 * If no results are found at all
		 * Say so!
		 */
		if ( count( $check_if_no_result ) === 0 && strlen( $this->person_name ) > 0 ) {

			$this->logger->log()->info( '[Lumiere][' . $this->classname . '] No post found for ' . $this->person_name . ' in ' . $this->taxonomy_title );

			/* translators: the name of a persons completes the phrase */
			echo '<div class="lumiere_align_center lumiere_italic lumiere_padding_five">' . esc_html__( 'No post written about ', 'lumiere-movies' ) . esc_html( $this->person_name ) . '</div>';

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
		$esc_html_pic = [
			'div' => [ 'class' => [] ],
			'a' => [
				'href' => [],
				'title' => [],
			],
			'img' => [
				'src' => [],
				'loading' => [],
				'class' => [],
				'alt' => [],
				'width' => [],
			],
		];
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp pics only if cache is active
			echo wp_kses(
				$this->link_maker->lumiere_link_picture(
					$this->person_class->photo_localurl( false ),
					$this->person_class->photo_localurl( true ),
					$this->person_name
				),
				$esc_html_pic
			);
		} else { // no_pics otherwise
			echo wp_kses(
				$this->link_maker->lumiere_link_picture(
					$this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif',
					$this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif',
					$this->person_name
				),
				$esc_html_pic
			);
		}

		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo '">';
		echo '<font size="-1">';

		# Birth
		$birthday = $this->person_class->born() !== null ? array_filter( $this->person_class->born() ) : [];
		if ( count( $birthday ) > 0 ) {

			$birthday_day = isset( $birthday['day'] ) && strlen( $birthday['day'] ) > 0 ? (string) $birthday['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
			$birthday_month = isset( $birthday['month'] ) && strlen( $birthday['month'] ) > 0 ? date_i18n( 'F', $birthday['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
			$birthday_year = isset( $birthday['year'] ) && strlen( $birthday['year'] ) > 0 ? (string) $birthday['year'] : __( '(year unknown)', 'lumiere-movies' );

			echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. '&#9788;&nbsp;'
				. esc_html__( 'Born on', 'lumiere-movies' ) . '&nbsp;</span>'
				. esc_html( $birthday_day . $birthday_month . $birthday_year );
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

			$death_day = isset( $death['day'] ) && strlen( $death['day'] ) > 0 ? (string) $death['day'] . ' ' : __( '(day unknown)', 'lumiere-movies' ) . ' ';
			$death_month = isset( $death['month'] ) && strlen( $death['month'] ) > 0 ? date_i18n( 'F', $death['month'] ) . ' ' : __( '(month unknown)', 'lumiere-movies' ) . ' ';
			$death_year = isset( $death['year'] ) && strlen( $death['year'] ) > 0 ? (string) $death['year'] : __( '(year unknown)', 'lumiere-movies' );

			echo "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. '&#8224;&nbsp;'
				. esc_html__( 'Died on', 'lumiere-movies' ) . '&nbsp;</span>'
				. esc_html( $death_day . $death_month . $death_year );

			if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
				/** translators: 'in' like 'Died in' */
				echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
			}

			if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
				/** translators: 'cause' like 'Cause of death' */
				echo ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . esc_html( $death['cause'] );
			}

		}

		echo "\n\t\t\t\t" . '</font></div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
		echo "\n\t\t\t\t" . '<div id="lum_taxo_page_bio" class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		echo ' lumiere-lines-common-fix">';
		echo '<font size="-1">';

		// Biography, function in trait.
		echo wp_kses(
			$this->link_maker->lumiere_medaillon_bio( $this->person_class->bio(), 1500 ) ?? '',
			[
				'span' => [
					'class' => [],
					'id' => [],
				],
				'title' => [
					'data-*' => true,
					'class' => [],
					'aria-label' => [],
					'role' => [],
				],
				'a' => [
					'class' => [],
					'data-*' => true,
					'title' => [],
					'href' => [],
				],
				'button' => [
					'type' => [],
					'name' => [],
					'id' => [],
					'class' => [],
					'aria-live' => [],
					'value' => [],
					'data-*' => true,
				],
				'strong' => [],
			]
		);

		echo "\n\t\t\t\t\t" . '</font></div>';
		echo "\n\t\t\t\t" . '</div>';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t\t" . '<br />';

		// Compatibility with Polylang WordPress plugin, add a form to filter results by language.
		echo isset( $this->plugins_classes_active['polylang'] ) ? wp_kses(
			$this->plugins_classes_active['polylang']->lumiere_get_form_polylang_selection( $this->taxonomy_title, $this->person_name ),
			[
				'div' => [ 'align' => [] ],
				'form' => [
					'target' => [],
					'method' => [],
					'id' => [],
					'name' => [],
					'action' => [],
				],
				'select' => [
					'class' => [],
					'name' => [],
					'id' => [],
				],
				'option' => [
					'value' => [],
					'select' => [],
				],
				'input' => [
					'type' => [],
					'id' => [],
					'name' => [],
					'value' => [],
					'required' => [],
				],
				'button' => [
					'type' => [],
					'name' => [],
					'id' => [],
					'class' => [],
					'aria-live' => [],
					'value' => [],
				],
			]
		) : '';
		echo "\n\t\t\t" . '<br />';
	}
}

$lumiere_people_standard_class = new Taxonomy_People_Standard();
add_action( 'init', [ $lumiere_people_standard_class, 'lumiere_static_start' ] );

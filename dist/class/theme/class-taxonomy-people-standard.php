<?php declare( strict_types = 1 );
/**
 * Template People: Taxonomy for Lumière! Movies WordPress plugin (set up for standard people taxonomy)
 *  You can replace the occurences of the word s'tandard, rename this file, and then copy it in your theme folder
 *  Or easier: just use Lumière admin interface to do it automatically
 *
 *  Version: 3.0
 *
 *  This template retrieves automaticaly the occurence of the name selected
 *  If used along with Polylang WordPress plugin, a form is displayed to filter by available language
 *
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use \Imdb\Person;
use \Imdb\PersonSearch;
use \WP_Query;

class Taxonomy_People_Standard {

	// Use trait frontend
	use \Lumiere\Frontend {
		Frontend::__construct as public __constructFrontend;
	}

	/**
	 *  Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

	/**
	 *  Class \Imdb\Person
	 *
	 */
	private Person $person_class;

	/**
	 *  Array of registered type of people from class \Lumiere\Settings
	 *
	 *  @var array<string> $array_people
	 */
	private array $array_people;

	/**
	 *  Name of the person sanitized
	 *
	 *  @var string $person_name_sntzd
	 */
	private string $person_name_sntzd;

	/**
	 *  Taxonomy category
	 *
	 *  @var string $taxonomy_title
	 */
	private string $taxonomy_title;

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Construct Frontend trait.
		$this->__constructFrontend( 'taxonomy-standard' );

		// List of potential parameters for a person.
		$this->array_people = $this->config_class->array_people;

		// Display the page.
		add_action( 'wp', [ $this, self::lumiere_taxo_layout_standard() ], 0 );

	}

	/**
	 *  Do the search according to the page title using IMDbPHP classes
	 */
	private function lumiere_process_imdbphp_search(): void {

		do_action( 'lumiere_logger' );

		// Build the current page name from the tag taxonomy.
		$page_title_check = is_string( single_tag_title( '', false ) ) === true ? single_tag_title( '', false ) : null;

		// Full taxonomy title.
		$this->taxonomy_title = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		// If we are in a WP taxonomy page, the info from imdbphp libraries.
		if ( $page_title_check !== null ) {

			$search = new PersonSearch( $this->imdbphp_class, $this->logger->log() );
			$results = $search->search( $page_title_check ); // search for the person using the taxonomy tag.
			$mid = $results[0]->imdbid(); // keep the first result only.
			$mid_sanitized = esc_html( $mid ); // sanitize the first result.
			$this->person_class = new Person( $mid_sanitized, $this->imdbphp_class, $this->logger->log() ); // search the profile using the first result.
			$this->person_name_sntzd = sanitize_text_field( $this->person_class->name() );

		}

	}

	/**
	 *  Display the layout
	 */
	private function lumiere_taxo_layout_standard(): void {

		get_header();

		// Start IMDbPHP search.
		$this->lumiere_process_imdbphp_search();

		echo '<br />';

		if ( true === $this->activate_sidebar ) {
			get_sidebar();
		}
		?>

		<main id="main" class="site-main clr" role="main">
			<div id="content-wrap" class="container clr">
		<?php

		if ( strlen( $this->person_name_sntzd ) !== 0 ) {

			$this->portrait();

		} else { // end of section if a result was found for the taxonomy.

			// No imdb result, so display a basic title.
			$title_from_tag = is_string( single_tag_title( '', false ) ) === true ? single_tag_title( '', false ) : '';
			echo "\n\t\t" . '<h1 class="pagetitle">' . esc_html__( 'Taxonomy for ', 'lumiere-movies' ) . ' ' . $title_from_tag . ' as <i>standard</i></h1>';

		}

		// Language from the form.
		// phpcs:ignore WordPress.Security.NonceVerification
		$form_id_language = ( isset( $_POST['tag_lang'] ) && strlen( $_POST['tag_lang'] ) !== 0 && ( wp_verify_nonce( $_POST['_wpnonce'], 'submit_lang' ) !== false ) ) ? intval( $_POST['tag_lang'] ) : null;

		/**
		 *  For every type of role (writer, director) do a WP Query Loop
		 */

		// Var to include all rows and check if it is null.
		$check_if_no_result = [];

		foreach ( $this->array_people as $people ) {

				// A value was passed in the form.
			if ( $form_id_language !== null ) {

				$args = [
					'post_type' => [ 'post', 'page' ],
					'post_status' => 'publish',
					'numberposts' => -1,
					'nopaging' => true,
					'tax_query' => [
						'relation' => 'AND',
						[
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field' => 'name',
							'terms' => $this->person_name_sntzd,
						],
						[
							'taxonomy' => 'language',
							'field' => 'term_taxonomy_id',
							'terms' => $form_id_language,
						],
					],
				];

				// No value was passed in the form.
			} else {

				$args = [
					'post_type' => [ 'post', 'page' ],
					'post_status' => 'publish',
					'tax_query' => [
						[
							'taxonomy' => esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . $people,
							'field' => 'name',
							'terms' => $this->person_name_sntzd,
						],
					],
				];

			}

			// The Query.
			$the_query = new WP_Query( $args );

			// The loop.
			if ( $the_query->have_posts() ) {

				echo "\n\t\t\t\t" . '<h2 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies' ) . ' ' . $people . '</h2>';

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
					<div class="lumiere_padding_15">	
					<?php
					// Display the post's thumbnail.
					$thumbnail = get_the_post_thumbnail( null, '', [ 'class' => '' ] );
					if ( strlen( $thumbnail ) !== 0 ) {
						echo $thumbnail;
					}
					echo "\n";
					?>
					</div>
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
			} else {

				$this->logger->log()->debug( "[Lumiere][taxonomy_$this->taxonomy_title] No post found for $this->person_name_sntzd in $people" );

			}

		}

		// Restore original Post Data.
		wp_reset_postdata();

		/**
		 * If no results are found at all
		 * Say so!
		 * @phpstan-ignore-next-line 'Strict comparison using === between 0 and 0 will always evaluate to true'.
		 */
		if ( count( $check_if_no_result ) === 0 ) {

			$this->logger->log()->info( "[Lumiere][taxonomy_$this->taxonomy_title] No post found for $this->person_name_sntzd in $this->taxonomy_title" );

			echo "<div class=\"lumiere_align_center lumiere_italic lumiere_padding_five\">No post written about $this->person_name_sntzd</div>";

		}
		?>

			</div>
		</main>

		<?php
		wp_meta();

		get_footer();

	}

	/**
	 *  Polylang form: Display a form to change the language if Polylang plugin is active
	 *
	 * @param string $taxonomy -> the current taxonomy to check and build the form according to it
	 */
	private function lumiere_get_form_polylang_selection( string $taxonomy ): void {

		if ( ! function_exists( 'pll_is_translated_taxonomy' ) ) {
			$this->logger->log()->debug( '[Lumiere][taxonomy_' . $taxonomy . '] Polylang is not active.' );
			return;
		}

		// Is the current taxonomy, such as "lumiere_actor", registered and activated for translation?
		if ( ! pll_is_translated_taxonomy( $taxonomy ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy][polylang plugin] No activated taxonomy found for $this->person_name_sntzd with $taxonomy." );
			return;
		}
		$pll_lang_init = get_terms( 'term_language', [ 'hide_empty' => false ] );
		$pll_lang = is_wp_error( $pll_lang_init ) === false && is_iterable( $pll_lang_init ) ? $pll_lang_init : null;

		if ( ! isset( $pll_lang ) ) {
			$this->logger->log()->debug( "[Lumiere][taxonomy_$taxonomy] No Polylang language is set." );
			return;
		}

		// Build the form.
		echo "\n\t\t\t" . '<div align="center">';
		echo "\n\t\t\t\t" . '<form method="post" id="lang_form" name="lang_form" action="#lang_form">';
		echo "\n\t\t\t\t\t" . '<select name="tag_lang" style="width:100px;">';
		echo "\n\t\t\t\t\t\t" . '<option value="">' . esc_html__( 'All', 'lumiere-movies' ) . '</option>';

		// Build an option html tag for every language.
		foreach ( $pll_lang as $lang ) {

			if ( ( $lang instanceof \WP_Term ) === false ) {
				continue;
			}

			echo "\n\t\t\t\t\t\t" . '<option value="' . intval( $lang->term_id ) . '"';

			// @phpcs:ignore WordPress.Security.NonceVerification
			if ( ( isset( $_POST['tag_lang'] ) ) && ( intval( $lang->term_id ) === intval( $_POST['tag_lang'] ) ) && isset( $_POST['_wpnonce'] ) && ( wp_verify_nonce( $_POST['_wpnonce'], 'submit_lang' ) !== false ) ) {
				echo 'selected="selected"';
			}

			echo '>' . esc_html( ucfirst( $lang->name ) ) . '</option>';

		}
		echo "\n\t\t\t\t\t" . '</select>&nbsp;&nbsp;&nbsp;';
		echo "\n\t\t\t\t\t" . wp_nonce_field( 'submit_lang' );
		if ( function_exists( 'submit_button' ) ) {
			echo "\n\t\t\t\t\t";
			submit_button( esc_html__( 'Filter language', 'lumiere-movies' ), 'primary', 'submit_lang', false );
		} else {
			echo "\n\t\t\t\t\t" . '<input type="submit" class="button-primary" id="submit_lang" name="submit_lang" value="' . esc_html__( 'Filter language', 'lumiere-movies' ) . '">';
		}
		echo "\n\t\t\t\t" . '</form>';
		echo "\n\t\t\t" . '</div>';

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
		echo ' imdbelementTITLE_' . $this->imdb_admin_values['imdbintotheposttheme'];

		echo '">';
		echo $this->person_name_sntzd;
		echo '</div>';

		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- star photo -->';

		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
		echo ' lumiere-padding-lines-common-picture">';

		// Select pictures: big poster, if not small poster, if not 'no picture'.
		$photo_url = $this->person_class->photo_localurl( false ) !== false ? esc_url( $this->person_class->photo_localurl( false ) ) : esc_url( $this->person_class->photo_localurl( true ) ); // create big picture, thumbnail otherwise.
		$photo_url_final = strlen( $photo_url ) === 0 ? esc_url( $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif' ) : $photo_url; // take big/thumbnail picture if exists, no_pics otherwise.

		echo "\n\t\t\t\t\t" . '<a id="highslide_pic" href="' . esc_url( $photo_url_final ) . '">';
		echo "\n\t\t\t\t\t\t" . '<img loading="eager" class="imdbincluded-picture lumiere_float_right" src="'
			. esc_url( $photo_url_final )
			. '" alt="'
			. $this->person_name_sntzd . '"';

		// add width only if "Display only thumbnail" is unactive.
		if ( $this->imdb_admin_values['imdbcoversize'] === '0' ) {

			echo ' width="' . intval( $this->imdb_admin_values['imdbcoversizewidth'] ) . '"';

			// add 100px width if "Display only thumbnail" is active.
		} elseif ( $this->imdb_admin_values['imdbcoversize'] === '1' ) {

			echo ' width="100em"';

		}

		echo ' />';
		echo "\n\t\t\t\t\t" . '</a>';

		echo "\n\t\t\t\t" . '</div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
		echo '">';
		echo '<font size="-1">';

		# Birth
		$birthday = $this->person_class->born() !== null ? $this->person_class->born() : null;
		if ( $birthday !== null && count( $birthday ) !== 0 ) {
			$birthday_day = ( isset( $birthday['day'] ) ) ? intval( $birthday['day'] ) : '';
			$birthday_month = ( isset( $birthday['month'] ) ) ? sanitize_text_field( $birthday['month'] ) : '';
			$birthday_year = ( isset( $birthday['year'] ) ) ? intval( $birthday['year'] ) : '';

			echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
				. '&#9788;&nbsp;'
				. esc_html__( 'Born on', 'lumiere-movies' ) . '</span>'
				. $birthday_day . ' '
				. $birthday_month . ' '
				. $birthday_year;
		} else {
			echo '&nbsp;';
		}

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . sanitize_text_field( $birthday['place'] );
		}

		echo "\n\t\t\t\t" . '</font></div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Death -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
		echo '">';
		echo '<font size="-1">';

		# Death
		$death = ( count( $this->person_class->died() ) !== 0 ) ? $this->person_class->died() : null;
		if ( $death !== null ) {

			echo "\n\t\t\t\t\t" . '<span class="imdbincluded-subtitle">'
				. '&#8224;&nbsp;'
				. esc_html__( 'Died on', 'lumiere-movies' ) . '</span>'
				. intval( $death['day'] ) . ' '
				. sanitize_text_field( $death['month'] ) . ' '
				. intval( $death['year'] );

			if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
				echo ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . sanitize_text_field( $death['place'] );
			}

			if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
				echo ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . sanitize_text_field( $death['cause'] );
			}

		}

		echo "\n\t\t\t\t" . '</font></div>';
		echo "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
		echo "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		echo ' lumiere-lines-common_' . $this->imdb_admin_values['imdbintotheposttheme'];
		echo ' lumiere-lines-common-fix">';
		echo '<font size="-1">';

		// Biography, function in trait.
		echo $this->lumiere_medaillon_bio( $this->person_class->bio(), true );

		echo "\n\t\t\t\t\t" . '</font></div>';
		echo "\n\t\t\t\t" . '</div>';
		echo "\n\t\t\t" . '</div>';
		echo "\n\t\t\t" . '<br />';

		$this->lumiere_get_form_polylang_selection( $this->taxonomy_title );

		echo "\n\t\t\t" . '<br />';

	}

}

new Taxonomy_People_Standard();


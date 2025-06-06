<?php declare( strict_types = 1 );
/**
 * Template Movie jobs: Taxonomy for Lumière! Movies WordPress plugin (set up for standard people taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.12
 *
 * TemplateAutomaticUpdate Remove this line if you do not want this template to be automatically updated when a new template version is released
 * @package       lumieremovies
 */

namespace Lumiere\Theme;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Config\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Vendor\Imdb\Name;
use Lumiere\Frontend\Main;
use Lumiere\Frontend\Module\Person\Person_Bio;
use Lumiere\Frontend\Module\Person\Person_Born;
use Lumiere\Frontend\Module\Person\Person_Died;
use Lumiere\Frontend\Layout\Output;
use Lumiere\Config\Get_Options_Movie;
use Lumiere\Plugins\Plugins_Start;
use WP_Query;

/**
 * This template retrieves automaticaly all post related to a person taxonomy
 * It is a WordPress virtual page created according to the taxonomy saved in database
 * If used along with Polylang WordPress plugin, a form is displayed to filter the posts by language
 * How it works:
 * 1/ The taxonomy is built in Taxonomy class
 * 2/ wp-blog-header.php:19 calls template-loader.php:106 which call current taxonomy, as set in Taxonomy
 *
 * @see \Lumiere\Alteration\Taxonomy Build the taxonomy system and taxonomy pages
 * @see \Lumiere\Main Trait to get $this->link_maker var (builds the links, AMP being the most relevant), the config class, the options
 *
 * @since 4.1 Use of plugins detection, get_medaillon_bio() returns larger number of characters for introduction, Polylang form with AMP works
 * @since 4.3 More OOP, Polylang and Imdbphp plugins fully utilised, returning the current job queried only
 */
final class Taxonomy_People_Standard {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

	/**
	 * Class \Imdb\Name instanciated
	 */
	private ?Name $person_class;

	/**
	 * Name of the person sanitized
	 *
	 * @var string|null $person_name
	 */
	private ?string $person_name;

	/**
	 * Taxonomy category
	 *
	 * @var string $taxonomy_title
	 */
	private string $taxonomy_title;

	/**
	 * Constructor
	 */
	public function __construct(
		private Plugins_Start $plugins_start,
		private Output $output_class = new Output(),
		private Person_Born $person_born_class = new Person_Born(),
		private Person_Died $person_died_class = new Person_Died(),
		private Person_Bio $person_bio_class = new Person_Bio(),
	) {
		// Run on taxonomy pages only.
		if ( is_tax() === false ) {
			return;
		}

		// Get options, settings, links from Frontend trait.
		$this->start_main_trait();
		$this->start_linkmaker();

		// Full taxonomy title.
		$this->taxonomy_title = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		// Class person, find also name of the current taxo
		$this->person_class = $this->get_imdbphp_person_searched();
		$this->person_name = isset( $this->person_class ) && $this->person_class->name() !== null ? $this->person_class->name() : null;

		/**
		 * Start AMP headers if AMP page and Polylang
		 * Should allow to use AJAX in $_POST instead of $_GET
		 * Not in use
		 */
		if ( $this->plugins_start->is_plugin_active( 'amp' ) === true && $this->plugins_start->is_plugin_active( 'polylang' ) === true ) { // Method in Trait Main.
			/** @psalm-suppress InvalidArrayOffset (Cannot access value offset...value of 'polylang', expecting 'PLUGINS_ALL_KEYS') */
			$class_polylang = $this->plugins_start->plugins_classes_active['polylang'];
			add_action( 'wp_ajax_amp_comment_submit', fn(): mixed => $class_polylang->amp_form_submit() );
			add_action( 'wp_ajax_nopriv_amp_comment_submit', fn(): mixed => $class_polylang->amp_form_submit() );
		}
	}

	/**
	 * Do the search according to the page title using IMDbPHP classes
	 */
	private function get_imdbphp_person_searched(): Name|null {

		// Build the current page name from the tag taxonomy.
		// Sanitize_title() ensures that the search is made according to the URL (fails with accents otherwise)
		$get_title = sanitize_title( single_tag_title( '', false ) ?? '' );

		// If we are in a WP taxonomy page, the info from imdbphp libraries.
		/** @psalm-suppress InvalidArrayOffset (Cannot access value offset...value of 'polylang', expecting 'PLUGINS_ALL_KEYS') */
		$results = $this->plugins_start->plugins_classes_active['imdbphp']->search_person_name( $get_title, $this->logger->log_null() ); // no log, breaks layout, executed too early.
		if ( array_key_exists( 0, $results ) ) {
			return $this->plugins_start->plugins_classes_active['imdbphp']->get_name_class( esc_html( $results[0]['id'] ), $this->logger->log_null() ); // no log, breaks layout, executed too early. => search the class Name using the first result found earlier.
		}
		return null;
	}

	/**
	 * Select which layout to display: Block-based or regular theme
	 */
	public function lum_select_layout(): void {

		$kses_esc_html = [
			'div' => [
				'id' => [],
				'align' => [],
				'class' => [],
			],
			'form' => [
				'target' => [],
				'method' => [],
				'id' => [],
				'name' => [],
				'action' => [],
			],
			'h1' => [
				'id' => [],
				'class' => [],
			],
			'h2' => [
				'id' => [],
				'class' => [],
			],
			'h3' => [
				'id' => [],
				'class' => [],
			],
			'h4' => [
				'id' => [],
				'class' => [],
			],
			'select' => [
				'class' => [],
				'name' => [],
				'id' => [],
			],
			'option' => [
				'value' => [],
				'selected' => [],
			],
			'input' => [
				'type' => [],
				'id' => [],
				'name' => [],
				'value' => [],
				'required' => [],
			],
			'a' => [
				'class' => [],
				'data-*' => true,
				'title' => [],
				'href' => [],
				'rel' => [],
			],
			'p' => [ 'class' => [] ],
			'img' => [
				'src' => [],
				'loading' => [],
				'class' => [],
				'alt' => [],
				'width' => [],
				'height' => [],
			],
			'span' => [
				'id' => [],
				'data-*' => true,
				'class' => [],
				'aria-label' => [],
				'role' => [],
			],
			'font' => [ 'size' => [] ],
			'br' => [],
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
			'main' => [
				'class' => [],
				'role' => [],
				'id' => [],
			],
		];

		// The current theme is a block-based theme.
		if ( wp_is_block_theme() === true ) {
			$this->lum_taxo_template_block( $this->lum_taxo_display_content(), $kses_esc_html );
			exit;
		}

		get_header();

		$this->logger->log?->debug( '[Taxonomy_People_Standard] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log?->debug( '[Taxonomy_People_Standard] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_start->plugins_classes_active ) ) . ']' );

		echo wp_kses( $this->lum_taxo_display_content(), $kses_esc_html );

		wp_meta();

		get_footer();

		exit;
	}

	/**
	 * Use Block-based template, for modern themes
	 * @since 4.1.2
	 * @param string $text The text to be displayed inside the content group
	 * @param array<string, array<array<string, string>>|array<string|bool>> $kses_esc_html The array for escaping wp_kses()
	 * @return void The template with the text is displayed
	 */
	private function lum_taxo_template_block( string $text, array $kses_esc_html ): void {

		?><html <?php echo wp_kses( get_language_attributes(), [ 'lang' => [] ] ); ?>><head>
		<meta charset="<?php bloginfo( 'charset' ); ?>">
		<?php
		$block_content = do_blocks(
			'
			<!-- wp:group -->
			<div class="wp-block-group">' . $text . '</div>
			<!-- /wp:group -->'
		);
		?>
		<?php wp_head(); ?>
		</head><body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		<div class="wp-site-blocks">
		<header class="wp-block-template-part site-header">
		<?php block_header_area(); ?>
		</header>
		<?php
		$this->logger->log?->debug( '[Taxonomy_People_Standard] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log?->debug( '[Taxonomy_People_Standard] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_start->plugins_classes_active ) ) . ']' );
		echo wp_kses( $block_content, $kses_esc_html ); ?>
		<footer class="wp-block-template-part site-footer">
		<?php block_footer_area(); ?>
		</footer>
		</div>
		<?php wp_footer(); ?>
		</body>
		</html><?php
	}

	/**
	 * The content of the page
	 */
	private function lum_taxo_display_content(): string {

		$output = '<br>';

		// The sidebar activation is hardcoded here.
		if ( $this->activate_sidebar === true ) {
			$output .= (string) get_sidebar();
		}

		$output .= '<main id="main" class="site-main clr" role="main">';
		$output .= '<div id="content-wrap" class="container clr">';

		 // end of section if a result was found for the taxonomy.
		if ( isset( $this->person_name ) ) {

			$output .= $this->lum_taxo_portrait( $this->person_name );
			// Display the related posts part.
			$output .= $this->run_person_query( $this->person_name );

		} else {
			// No imdb result, so display a basic title.
			$title_from_tag = single_tag_title( '', false );
			$output .= "\n\t\t" . '<h1 class="pagetitle">' . esc_html__( 'Taxonomy for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . ' as <i>standard</i></h1>';
			$output .= "\n\t\t" . '<div>' . esc_html__( 'No IMDb result found for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . '</div>';
		}

		$output .= '</div>';
		$output .= '</main>';

		return $output;
	}

	/**
	 * Display results related to the current taxonomy
	 * For every type of role (writer, director) do a WP Query Loop
	 * @info: a bit strange to do a loop while it is supposed to be a 'job'-related page (only for directors, ie) but it links taxonomy pages, which is cool
	 *
	 * @return string The related post
	 */
	private function run_person_query( string $person_name ): string {

		$taxonomy_name = esc_html( $this->taxonomy_title ); // Such as 'lumiere-standard'.
		$job = str_replace( $this->imdb_admin_values['imdburlstringtaxo'], '', $taxonomy_name ); // Such as 'standard'.
		$job_translated = Get_Options_Movie::get_list_people_taxo()[ esc_html( $job ) ]; // Such as 'standard' in local language.

		// Var to include all rows and check if it is null.
		$check_if_no_result = [];

		$output = "\n\t\t\t\t" . '<div class="lumiere_taxo_results"><!-- taxo_results -->';

		// Default query.
		$base_query = [
			'post_type' => [ 'post', 'page' ],
			'post_status' => 'publish',
			'showposts' => -1,
			'fields' => 'ids',
			'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
				'taxonomy' => sanitize_text_field( $taxonomy_name ),
				'field' => 'name',
				'terms' => sanitize_text_field( $person_name ),
				],
			],
		];

		// If Polylang is installed, the filter will returns a modified query, otherwise the $base_query is returned.
		$sql_query = apply_filters(
			'lum_polylang_taxo_query', // Filter in class Polylang.
			$base_query, // Default query.
			[
				'taxonomy' => sanitize_text_field( $taxonomy_name ), // Taxonomy
				'person_name' => sanitize_text_field( $person_name ),
			]
		);

		// The Query.
		$the_query = strlen( $person_name ) > 0 ? new WP_Query( $sql_query ) : null;

		// The loop.
		if ( isset( $the_query ) && $the_query->have_posts() ) {

			$output .= "\n\t\t\t\t" . '<h2 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies' ) . ' ' . esc_html( $job_translated ) . '</h2>';

			while ( $the_query->have_posts() ) {

				$the_query->the_post();

				$the_id = get_the_ID();
				$the_id = $the_id !== false ? $the_id : 0;
				$output .= '<div class="postList">';
				$output .= '<h3 id="post-' . (string) $the_id . '">';
				$output .= '<a href="' . (string) get_the_permalink() . '" rel="bookmark" title="' . __( 'Open the blog ', 'lumiere-movies' ) . get_the_title( $the_id ) . '">';
				$output .= get_the_title( $the_id ) . '&nbsp;<span class="lumiere_font_12">(' . (string) get_the_time( 'd/m/Y' ) . ')</span>';
				$output .= '</a>';
				$output .= '</h3>';
				$output .= '<div class="lumiere_display_flex">';

				$output .= '<div class="">';
				$output .= wp_trim_excerpt();
				$output .= "\n\t\t" . '</div>';
				$output .= "\n\t" . '</div>';
				$output .= '<p class="postmetadata lumiere_align_center lumiere_padding_five">';
				$output .= '<span class="category">' . __( 'Filed under: ', 'lumiere-movies' ) . get_the_category_list( ', ' ) . '</span>';

				$tags_list = get_the_tag_list();
				if ( ( $tags_list !== false ) && ( is_wp_error( $tags_list ) === false ) ) {

						$output .= '<strong> | </strong>';
						$tags_list = get_the_tag_list( esc_html__( 'Tags: ', 'lumiere-movies' ), ' &bull; ', ' ' );
						$output .= is_string( $tags_list ) ? '<span class="tags">' . $tags_list . '</span>' : '';
				}

				$output .= "\n" . '</p>';
				$output .= "\n" . '</div>';

				$check_if_no_result[] = get_the_title( $the_id );

			}
		}

		// Restore original Post Data.
		wp_reset_postdata();

		/**
		 * If no results are found at all
		 * Say so!
		 */
		if ( count( $check_if_no_result ) === 0 && strlen( $person_name ) > 0 ) {

			/* translators: %1$s is the name of a person */
			$output .= '<div class="lumiere_align_center lumiere_italic lumiere_padding_five">' . esc_html( wp_sprintf( __( 'No post written about %1$s', 'lumiere-movies' ), $person_name ) ) . '</div>';

		}

		$output .= "\n\t\t\t\t" . '</div><!-- taxo_results -->';
		return $output;
	}

	/**
	 *  Display People data details
	 */
	private function lum_taxo_portrait( string $person_name ): string {

		$output = "\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Photo & identity -->';
		$output .= "\n\t\t" . '<div class="lumiere_container lumiere_font_em_11 lumiere_align_center">';
		$output .= "\n\t\t\t" . '<div class="lumiere_flex_auto">';

		$output .= "\n\t\t\t\t" . '<div class="imdbelementTITLE ';
		$output .= ' imdbelementTITLE_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= '">';
		$output .= esc_html( $person_name );
		$output .= '</div>';

		/**
		 * Use Highslide, Classical or No Links class links builder.
		 * Each one has its own class passed in $link_maker,
		 * according to which option the lumiere_select_link_maker() found in Frontend.
		 */
		if ( $this->imdb_cache_values['imdbusecache'] === '1' ) { // use IMDBphp pics only if cache is active
			$output .= isset( $this->person_class ) ? $this->link_maker->get_picture(
				$this->person_class->photoLocalurl( false ),
				$this->person_class->photoLocalurl( true ),
				$person_name
			) : '';
		} else { // no_pics otherwise
			$no_pic = $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif';
			$output .= $this->link_maker->get_picture( $no_pic, $no_pic, $person_name );
		}

		$born = isset( $this->person_class ) ? $this->person_born_class->get_module( $this->person_class, 'born' ) : '';
		$output .= $this->output_class->misc_layout( 'date_outside', 'Birth', $this->imdb_admin_values['imdbintotheposttheme'], $born );
		$died = isset( $this->person_class ) ? $this->person_died_class->get_module( $this->person_class, 'died' ) : '';
		$output .= $this->output_class->misc_layout( 'date_outside', 'Death', $this->imdb_admin_values['imdbintotheposttheme'], $died );

		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
		$output .= "\n\t\t\t\t" . '<div id="lum_taxo_page_bio" class="lumiere-lines-common';
		$output .= ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= ' lumiere-lines-common-fix">';
		$output .= '<span class="lumiere_font_small">';

		$output .= isset( $this->person_class ) ? $this->person_bio_class->get_module( $this->person_class, 'bio' ) : '';

		$output .= "\n\t\t\t\t\t" . '</span></div>';
		$output .= "\n\t\t\t\t" . '</div>';
		$output .= "\n\t\t\t" . '</div>';
		$output .= "\n\t\t\t" . '<br>';

		// Form for Polylang plugin: if installed, add a form to filter results by language.
		$output .= apply_filters( 'lum_polylang_form_taxonomy_people', '' );

		$output .= "\n\t\t\t" . '<br>';
		return $output;
	}
}

$lumiere_people_standard_class = new Taxonomy_People_Standard( new Plugins_Start( [ 'imdbphp' ] ) ); // always start imdbphp.
// Display the page. Must not be included into an add_action(), as should be displayed directly, since it's a template.
$lumiere_people_standard_class->lum_select_layout();

<?php declare( strict_types = 1 );
/**
 * Template People: Taxonomy for Lumière! Movies WordPress plugin (set up for standard people taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.8
 *
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Imdb\Name;
use Imdb\NameSearch;
use Lumiere\Link_Makers\Link_Factory;
use Lumiere\Frontend\Main;
use WP_Query;

/**
 * This template retrieves automaticaly all post related to a person taxonomy
 * It is a WordPress virtual page created according to the taxonomy saved in database
 * If used along with Polylang WordPress plugin, a form is displayed to filter by available language
 * How it works: 1/ The taxonomy is build in Taxonomy class 2/ wp-blog-header.php:19 calls template-loader.php:106 which call current taxonomy, as set in Taxonomy
 *
 * @see \Lumiere\Alteration\Taxonomy Build the taxonomy system and taxonomy pages
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
	 * Class \Imdb\Name
	 */
	private ?Name $person_class;

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
		 * Set $plugins_classes_active var in trait
		 * @since 3.8
		 */
		if ( count( $this->plugins_classes_active ) === 0 ) {
			$this->maybe_activate_plugins();
		}

		// Build the Link Maker var in trait.
		$this->link_maker = Link_Factory::lumiere_link_factory_start();

		// Full taxonomy title.
		$this->taxonomy_title = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] ) . 'standard';

		// Class person, find also name of the current taxo
		$this->person_class = $this->get_imdbphp_person_searched();
		$this->person_name = isset( $this->person_class ) ? $this->person_class->name() : '';

		/**
		 * Start AMP headers if AMP page and Polylang
		 * Should allow to use AJAX instead of URLs gets
		 * Not in use
		 */
		if ( $this->is_plugin_active( 'amp' ) === true && $this->is_plugin_active( 'polylang' ) === true ) { // Method in Trait Main.
			$class_polylang = $this->plugins_classes_active['polylang'];
			add_action( 'wp_ajax_amp_comment_submit', [ $class_polylang, 'amp_form_submit' ] );
			add_action( 'wp_ajax_nopriv_amp_comment_submit', [ $class_polylang, 'amp_form_submit' ] );
		}
	}

	/**
	 * Static start
	 * @since 4.2.3 Run on taxonomy pages only
	 */
	public static function lumiere_static_start(): void {

		// Run on taxonomy pages only.
		if ( is_tax() === false ) {
			return;
		}

		$class = new self();

		// Display the page. Must not be included into an add_action(), as should be displayed directly, since it's a template.
		$class->lum_select_layout();
	}

	/**
	 * Do the search according to the page title using IMDbPHP classes
	 */
	private function get_imdbphp_person_searched(): Name|null {

		// Build the current page name from the tag taxonomy.
		// Sanitize_title() ensures that the search is made according to the URL (fails with accents otherwise)
		$page_title_check = sanitize_title( single_tag_title( '', false ) ?? '' );

		// If we are in a WP taxonomy page, the info from imdbphp libraries.
		$search = new NameSearch( $this->plugins_classes_active['imdbphp'], $this->logger->log_null() ); // no log, breaks layout, executed too early.
		$results = $search->search( $page_title_check ); // search for the person using the taxonomy tag.
		if ( array_key_exists( 0, $results ) ) {
			return new Name( esc_html( $results[0]['id'] ), $this->plugins_classes_active['imdbphp'], $this->logger->log_null() ); // no log, breaks layout, executed too early. => search the class Name using the first result found earlier.
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

		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );

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

		?><html><head>
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
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_classes_active ) ) . ']' );
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
		if ( strlen( $this->person_name ) > 0 ) {

			$output .= $this->lum_taxo_portrait( $this->person_name );

		} else {

			// No imdb result, so display a basic title.
			$title_from_tag = single_tag_title( '', false );
			$output .= "\n\t\t" . '<h1 class="pagetitle">' . esc_html__( 'Taxonomy for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . ' as <i>standard</i></h1>';
			$output .= "\n\t\t" . '<div>' . esc_html__( 'No IMDb result found for ', 'lumiere-movies' ) . ' ' . esc_html( $title_from_tag ?? '' ) . '</div>';

		}

		// Display the related posts part.
		$output .= $this->run_person_query( $this->person_name );

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

		// Var to include all rows and check if it is null.
		$check_if_no_result = [];

		$output = "\n\t\t\t\t" . '<div class="lumiere_taxo_results"><!-- taxo_results -->';

		foreach ( $this->config_class->array_people as $people => $people_translated ) {

			$taxonomy_name = esc_html( str_replace( 'imdbtaxonomy', '', $this->imdb_admin_values['imdburlstringtaxo'] . $people ) ); // Such as 'lumiere-standard'

			// Default query.
			$base_query = [
				'post_type' => [ 'post', 'page' ],
				'post_status' => 'publish',
				'showposts' => -1,
				'fields' => 'ids',
				'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					[
					'taxonomy' => sanitize_text_field( $taxonomy_name ),
					'operator' => 'EXISTS',
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

				$output .= "\n\t\t\t\t" . '<h2 class="lumiere_italic lumiere_align_center">' . esc_html__( 'In the role of', 'lumiere-movies' ) . ' ' . esc_html( $people_translated ) . '</h2>';

				while ( $the_query->have_posts() ) {

					$the_query->the_post();

					$the_id = get_the_ID();
					$the_id = $the_id !== false ? $the_id : 0;
					$output .= '<div class="postList">';
					$output .= '<h3 id="post-' . $the_id . '">';
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
		}

		// Restore original Post Data.
		wp_reset_postdata();

		/**
		 * If no results are found at all
		 * Say so!
		 */
		if ( count( $check_if_no_result ) === 0 && strlen( $person_name ) > 0 ) {

			/* translators: %1$s is the name of a person */
			$output .= '<div class="lumiere_align_center lumiere_italic lumiere_padding_five">' . esc_html( sprintf( __( 'No post written about %1$s', 'lumiere-movies' ), $this->person_name ) ) . '</div>';

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
			$output .= isset( $this->person_class ) ? $this->link_maker->lumiere_link_picture(
				$this->person_class->photoLocalurl( false ),
				$this->person_class->photoLocalurl( true ),
				$person_name
			) : '';
		} else { // no_pics otherwise
			$no_pic = $this->imdb_admin_values['imdbplugindirectory'] . 'pics/no_pics.gif';
			$output .= $this->link_maker->lumiere_link_picture( $no_pic, $no_pic, $person_name );
		}

		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Birth -->';
		$output .= "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		$output .= ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= '">';
		$output .= '<span class="lumiere_font_small">';

		# Birth
		$birthday = isset( $this->person_class ) && $this->person_class->born() !== null ? array_filter( $this->person_class->born() ) : [];
		if ( count( $birthday ) > 0 ) {

			$birthday_day = isset( $birthday['day'] ) && strlen( strval( $birthday['day'] ) ) > 0 ? strval( $birthday['day'] ) . ' ' : '(' . __( 'day unknown', 'lumiere-movies' ) . ') ';
			$birthday_month = isset( $birthday['month'] ) && strlen( $birthday['month'] ) > 0 ? date_i18n( 'F', $birthday['month'] ) . ' ' : '(' . __( 'month unknown', 'lumiere-movies' ) . ') ';
			$birthday_year = isset( $birthday['year'] ) && strlen( strval( $birthday['year'] ) ) > 0 ? strval( $birthday['year'] ) : '(' . __( 'year unknown', 'lumiere-movies' ) . ')';

			$output .= "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. '&#9788;&nbsp;'
				. esc_html__( 'Born on', 'lumiere-movies' ) . '&nbsp;</span>'
				. esc_html( $birthday_day . $birthday_month . $birthday_year );
		} else {
			$output .= '&nbsp;';
		}

		if ( ( isset( $birthday['place'] ) ) && ( strlen( $birthday['place'] ) !== 0 ) ) {
			$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $birthday['place'] );
		}

		$output .= "\n\t\t\t\t" . '</span></div>';
		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Death -->';
		$output .= "\n\t\t\t\t" . '<div class="lumiere-lines-common';
		$output .= ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= '">';
		$output .= '<span class="lumiere_font_small">';

		# Death
		$death = isset( $this->person_class ) && count( $this->person_class->died() ) > 0 ? $this->person_class->died() : null;
		if ( isset( $death['status'] ) && $death['status'] === 'DEAD' ) {

			$death_day = isset( $death['day'] ) && strlen( strval( $death['day'] ) ) > 0 ? strval( $death['day'] ) . ' ' : '(' . __( '(day unknown)', 'lumiere-movies' ) . ') ';
			$death_month = isset( $death['month'] ) && strlen( $death['month'] ) > 0 ? date_i18n( 'F', $death['month'] ) . ' ' : '(' . __( '(month unknown)', 'lumiere-movies' ) . ') ';
			$death_year = isset( $death['year'] ) && strlen( strval( $death['year'] ) ) > 0 ? strval( $death['year'] ) : '(' . __( '(year unknown)', 'lumiere-movies' ) . ')';

			$output .= "\n\t\t\t\t\t" . '<span class="lum_results_section_subtitle">'
				. '&#8224;&nbsp;'
				. esc_html__( 'Died on', 'lumiere-movies' ) . '&nbsp;</span>'
				. esc_html( $death_day . $death_month . $death_year );

			if ( ( isset( $death['place'] ) ) && ( strlen( $death['place'] ) !== 0 ) ) {
				/** translators: 'in' like 'Died in' */
				$output .= ', ' . esc_html__( 'in', 'lumiere-movies' ) . ' ' . esc_html( $death['place'] );
			}

			if ( ( isset( $death['cause'] ) ) && ( strlen( $death['cause'] ) !== 0 ) ) {
				/** translators: 'cause' like 'Cause of death' */
				$output .= ', ' . esc_html__( 'cause', 'lumiere-movies' ) . ' ' . esc_html( $death['cause'] );
			}
		}

		$output .= "\n\t\t\t\t" . '</span></div>';
		$output .= "\n\n\t\t\t\t\t\t\t\t\t\t\t" . '<!-- Biography -->';
		$output .= "\n\t\t\t\t" . '<div id="lum_taxo_page_bio" class="lumiere-lines-common';
		$output .= ' lumiere-lines-common_' . esc_attr( $this->imdb_admin_values['imdbintotheposttheme'] );
		$output .= ' lumiere-lines-common-fix">';
		$output .= '<span class="lumiere_font_small">';

		// Biography, function in trait.
		$output .= isset( $this->person_class ) ? $this->link_maker->lumiere_medaillon_bio( $this->person_class->bio(), 1500 ) ?? '' : '';

		$output .= "\n\t\t\t\t\t" . '</span></div>';
		$output .= "\n\t\t\t\t" . '</div>';
		$output .= "\n\t\t\t" . '</div>';
		$output .= "\n\t\t\t" . '<br>';

		// Form for Polylang plugin: if installed, add a form to filter results by language.
		$output .= apply_filters( 'lum_polylang_form_taxonomy_people', '', $this->taxonomy_title );

		$output .= "\n\t\t\t" . '<br>';
		return $output;
	}
}

$lumiere_people_standard_class = new Taxonomy_People_Standard();
$lumiere_people_standard_class->lumiere_static_start();

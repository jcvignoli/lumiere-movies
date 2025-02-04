<?php declare( strict_types = 1 );
/**
 * Template Item: Taxonomy for Lumière! Movies WordPress plugin (set up for standard item taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.2.4
 *
 * TemplateAutomaticUpdate Remove this line if you do not want this template to be automatically updated when a new template version is released
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use Lumiere\Plugins\Plugins_Start;
use WP_Query;

/**
 * This template retrieves automaticaly all post related to an item taxonomy
 * It is a WordPress virtual page created according to the taxonomy saved in database
 * How it works: 1/ The taxonomy is build in Taxonomy class 2/ wp-blog-header.php:19 calls template-loader.php:106 which call current taxonomy, as set in Taxonomy
 *
 * @see \Lumiere\Alteration\Taxonomy That build the taxonomy system and taxonomy pages
 *
 * @since 4.0 Returns all Lumière taxonomies that can be clicked when visiting the item template page
 */
class Taxonomy_Items_Standard {

	/**
	 * Traits
	 */
	use Main;

	/**
	 * Set to true to activate the sidebar
	 */
	private bool $activate_sidebar = false;

	/**
	 * The taxonomy term to be used in the page
	 */
	private string $taxonomy;

	/**
	 * HTML allowed for use of wp_kses()
	 */
	private const ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS = [
		'a' => [
			'class' => true,
			'href' => true,
			'rel' => true,
		],
	];

	/**
	 * Constructor
	 * @since 4.0 Ban bots from display the page.
	 */
	public function __construct(
		private Plugins_Start $plugins_start,
	) {
		// Run on taxonomy pages only.
		if ( is_tax() === false ) {
			return;
		}

		// Construct Frontend trait.
		$this->start_main_trait();

		// Build the taxonomy name.
		$this->taxonomy = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] . 'standard' );
	}

	/**
	 *  Display layout
	 */
	public function lum_select_layout(): void {

		$kses_esc_html = [
			'br' => [],
			'i' => [],
			'main' => [
				'class' => [],
				'role' => [],
				'id' => [],
			],
			'div' => [
				'id' => [],
				'align' => [],
				'class' => [],
			],
			'h1' => [
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
			'a' => [
				'class' => [],
				'title' => [],
				'href' => [],
				'rel' => [],
			],
		];

		// The current theme is a block-based theme.
		if ( wp_is_block_theme() === true ) {
			$this->lum_taxo_template_block( $this->lum_taxo_display_content(), $kses_esc_html );
			exit;
		}

		get_header();

		$this->logger->log->debug( '[Lumiere][Taxonomy_Items_Standard] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log->debug( '[Lumiere][Taxonomy_Items_Standard] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_start->plugins_classes_active ) ) . ']' );

		echo wp_kses( $this->lum_taxo_display_content(), $kses_esc_html );

		wp_meta();

		get_footer();

	}

	/**
	 * The content of the page
	 */
	private function lum_taxo_display_content(): string {

		$output = '<br>';

		if ( $this->activate_sidebar === true ) {
			get_sidebar(); # selected in the above properties
		}

		$output .= '<main id="main" class="site-main clr" role="main">';
		$output .= '<div id="content-wrap" class="container clr">';
		$output .= '<h1 class="pagetitle">' . __( 'Taxonomy', 'lumiere-movies' ) . ' <i>standard</i></h1>';

		$output .= "\n\t\t" . '<div class="taxonomy">';
		$output .= "\n\t\t\t" . esc_html__( 'All Lumière taxonomies known: ', 'lumiere-movies' ) . $this->get_all_tags_links();
		$output .= "\n\t\t\t" . '<br><br>';
		$output .= "\n\t\t" . '</div>';

		$args = [
			'post_type' => [ 'post', 'page' ],
			'post_status' => 'publish',
			'showposts' => -1,
			'fields' => 'ids',
			'tax_query' => [ // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				[
					'taxonomy' => sanitize_text_field( $this->taxonomy ),
					'field' => 'slug',
					'terms' => sanitize_text_field( $this->get_term_current_page( 'slug' ) ),
				],
			],
		];

		// The Query.
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ) {

			$output .= "\n\t\t" . '<h4>' . esc_html__( 'List of posts tagged ', 'lumiere-movies' ) . ' <i>' . esc_html( $this->get_term_current_page( 'name' ) ) . '</i> :</h4>';

			while ( $the_query->have_posts() ) {
				$the_query->the_post();

				$the_id = get_the_ID();
				$the_id = $the_id !== false ? $the_id : 0;

				$output .= "\n\t\t" . '<br>';
				$output .= "\n\t\t" . '<div class="postList postsTaxonomy">';
				$output .= "\n\t\t" . '<h3 id="post-' . (string) $the_id . '">';
				$output .= "\n\t\t" . '<a href="' . (string) get_the_permalink() . '" rel="bookmark" title="' . __( 'Open the blog ', 'lumiere-movies' ) . get_the_title( $the_id ) . '">' . get_the_title( $the_id ) . '</a>';
				$output .= "\n\t\t" . '</h3>';

				$output .= "\n\t\t" . '<div class="entry">';
				$output .= wp_trim_excerpt();
				$output .= "\n\t\t" . '</div>';

				$output .= "\n\t" . '</div>';
			}

		} else { // there is no post
				$output .= __( 'No post found with this taxonomy.', 'lumiere-movies' );
				$output .= '<br><br><br>';
		}

		$output .= '</div>';
		$output .= '</main>';
		return $output;
	}

	/**
	 * Use Block-based template, for modern themes
	 * @since 4.1.2
	 * @param string $text The text to be displayed inside the content group
	 * @param array<string, array<array<string, string>>|array<string>> $kses_esc_html The array for escaping wp_kses()
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
		$this->logger->log->debug( '[Lumiere][Taxonomy_Items_Standard] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log->debug( '[Lumiere][Taxonomy_Items_Standard] The following plugins compatible with Lumière! are in use: [' . join( ', ', array_keys( $this->plugins_start->plugins_classes_active ) ) . ']' );
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
	 * Build HTML links from all taxonomies that exist for a given term
	 *
	 * @since 4.0 Function created
	 *
	 * @return string List of strings
	 */
	private function get_all_tags_links(): string {
		$existing_terms = get_terms( [ 'taxonomy' => $this->taxonomy ] );
		if ( count( (array) $existing_terms ) === 0 ) {
			return '';
		}
		$all_links = [];
		foreach ( (array) $existing_terms as $int => $taxo ) {
			$html_link = get_term_link( $taxo->slug, $this->taxonomy );
			if ( isset( $taxo->name ) && isset( $taxo->slug ) && ! $html_link instanceof \WP_Error ) {
				$all_links[] = '<a href="' . $html_link . '" rel="' . $taxo->slug . '">' . $taxo->name . '</a>';
			}
		}

		return implode( ', ', $all_links );
	}

	/**
	 * Return the terms of the current page
	 *
	 * @since 4.0 Function created
	 *
	 * @param string $type The type of object to return
	 * @phpstan-param 'slug'|'name' $type
	 * @return string the text for this $type
	 */
	private function get_term_current_page( string $type ): string {

		$current_term = get_query_var( $this->taxonomy );
		$term_obj = get_term_by( 'slug', $current_term, $this->taxonomy );

		return is_object( $term_obj ) ? $term_obj->$type : '';
	}
}

$lumiere_item_standard_class = new Taxonomy_Items_Standard( new Plugins_Start( [ 'imdbphp' ] ) );
$lumiere_item_standard_class->lum_select_layout();

<?php declare( strict_types = 1 );
/**
 * Template Item: Taxonomy for Lumière! Movies WordPress plugin (set up for standard item taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.1.1
 *
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Frontend\Main;
use WP_Query;

/**
 * This template retrieves automaticaly all post related to an item taxonomy
 * It is a virtual page created when the appropriate rules are met
 *
 * @see \Lumiere\Frontend\Taxonomy That build the taxonomy system and taxonomy pages
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

		// Build the taxonomy name.
		$this->taxonomy = esc_html( $this->imdb_admin_values['imdburlstringtaxo'] . 'standard' );
	}

	/**
	 * Static start
	 */
	public static function lumiere_static_start(): void {
		$class = new self();

		// Display the page. Must not be included into an add_action(), as should be displayed directly, since it's a template.
		$class->lum_select_layout();
	}

	/**
	 *  Display layout
	 */
	private function lum_select_layout(): void {

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

		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_active_names ) . ']' );

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
			'tax_query' => [
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

				$output .= "\n\t\t" . '<br>';
				$output .= "\n\t\t" . '<div class="postList postsTaxonomy">';
				$output .= "\n\t\t" . '<h3 id="post-' . (string) get_the_ID() . '">';
				$output .= "\n\t\t" . '<a href="' . (string) get_the_permalink() . '" rel="bookmark" title="' . __( 'Open the blog ', 'lumiere-movies' ) . get_the_title() . '">' . get_the_title() . '</a>';
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
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] Using the link maker class: ' . get_class( $this->link_maker ) );
		$this->logger->log()->debug( '[Lumiere][' . $this->classname . '] The following plugins compatible with Lumière! are in use: [' . join( ', ', $this->plugins_active_names ) . ']' );
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
		// @phan-suppress-next-line PhanAccessMethodInternal -- Cannot access internal method \get_terms() of namespace \ defined at vendor/php-stubs/wordpress-stubs/wordpress-stubs.php:133181 from namespace \Lumiere\Plugins -> PHAN got crazy with get_terms()!
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

$lumiere_item_standard_class = new Taxonomy_Items_Standard();
$lumiere_item_standard_class->lumiere_static_start();

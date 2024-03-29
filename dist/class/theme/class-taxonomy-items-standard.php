<?php declare( strict_types = 1 );
/**
 * Template Item: Taxonomy for Lumière! Movies WordPress plugin (set up for standard item taxonomy)
 * You can replace the occurences of the word s_tandar_d (without the underscores), rename this file, and then copy it in your theme folder
 * Or easier: just use Lumière admin interface to do it automatically
 *
 * Version: 3.0.2
 *
 * @package lumiere-movies
 */

namespace Lumiere;

// If this file is called directly, abort.
if ( ( ! defined( 'ABSPATH' ) ) || ( ! class_exists( '\Lumiere\Settings' ) ) ) {
	wp_die( 'You can not call directly this page' );
}

use Lumiere\Frontend\Main;

/**
 * This template retrieves automaticaly all post related to items taxonomy clicked
 * It is a virtual page created when the appropriate rules are met
 * If used along with Polylang WordPress plugin, a form is displayed to filter by available language
 * Almost compatible with AMP WordPress plugin, as WP submit_button() is yet to be made AMP compliant
 *
 * @see \Lumiere\Alteration\Virtual_Page that allows fills that virtual page, which this class fills into
 * @see \Lumiere\Frontend Trait to builds $this->link_maker var
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

		// Build the taxonomy name.
		$this->taxonomy = 'lumiere-standard';

		// Display the page.
		$this->lumiere_layout_taxo_standard();
	}

	/**
	 * Static start
	 */
	public static function lumiere_static_start(): void {
		$class = new self();
	}

	/**
	 *  Display layout
	 */
	private function lumiere_layout_taxo_standard(): void {

		get_header();

		echo '<br />';

		if ( $this->activate_sidebar === true ) {
			get_sidebar(); # selected in the above properties
		}
		?>

		<main id="main" class="site-main clr" role="main">
			<div id="content-wrap" class="container clr">
				<h1 class="pagetitle"><?php esc_html_e( 'Taxonomy', 'lumiere-movies' ); ?> <i>standard</i></h1><?php

				echo "\n\t\t" . '<div class="taxonomy">';
				echo "\n\t\t\t" . esc_html__( 'All Lumière taxonomies known: ', 'lumiere-movies' ) . wp_kses( $this->get_all_tags_links(), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS );
				echo "\n\t\t\t" . '<br /><br />';
				echo "\n\t\t" . '</div>';

				$args = [
					'post_type' => [ 'post', 'page' ],
					'tax_query' => [
						[
							'taxonomy' => $this->taxonomy,
							'field' => 'slug',
							'terms' => $this->get_term_current_page( 'slug' ),
						],
					],
				];

				// The Query.
				$the_query = new \WP_Query( $args );

				if ( $the_query->have_posts() ) {

					echo "\n\t\t" . '<h4>' . esc_html__( 'List of posts tagged ', 'lumiere-movies' ) . ' <i>' . wp_kses( $this->get_term_current_page( 'name' ), self::ALLOWED_HTML_FOR_ESC_HTML_FUNCTIONS ) . '</i> :</h4>';
					echo "\n\t\t\t" . '<br />';

					while ( $the_query->have_posts() ) {
						$the_query->the_post();
						?>

					<div class="postList postsTaxonomy">
						<h3 id="post-<?php the_ID(); ?>">
							<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php esc_html_e( 'Open the blog ', 'lumiere-movies' ); ?><?php the_title(); ?>">
								<?php the_title(); ?>
							</a>
						</h3>

						<div class="entry">
							<?php the_excerpt(); ?>
						</div>
						
						<!-- deactivated
						<p class="postmetadata">
							<span class="category"><?php esc_html_e( 'Filed under: ', 'lumiere-movies' ); ?> <?php the_category( ', ' ); ?></span> 

							<?php if ( has_tag() ) { ?>
							<strong>|</strong>
							<span class="tags"><?php the_tags( esc_html__( 'Tags: ', 'lumiere-movies' ), ' &bull; ', ' ' ); ?></span>
							<?php } ?>

							<strong>|</strong> <?php edit_post_link( 'Edit', '', ' <strong>|</strong>' ); ?>  <?php comments_popup_link( 'No Comments &#187;', '1 Comment &#187;', ' % Comments &#187;' ); ?>
						</p>
						 -->
					</div>
						<?php

					}

				} else { // there is no post
						esc_html_e( 'No post found with this taxonomy.', 'lumiere-movies' );
						echo '<br /><br /><br />';
				}
				?>


			</div>
		</main>

		<?php

		wp_meta();

		get_footer();

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
add_action( 'init', [ $lumiere_item_standard_class, 'lumiere_static_start' ] );

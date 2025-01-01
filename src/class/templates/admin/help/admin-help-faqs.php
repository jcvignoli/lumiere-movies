<?php declare( strict_types = 1 );
/**
 * Template for the FAQs in help
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 */
namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( 'Lumière Movies: You can not call directly this page' );
}

/**
 * HTML allowed for use of wp_kses()
 */
$lumiere_escape_wp_kses = [
	'i' => [],
	'strong' => [],
	'div' => [ 'class' => [] ],
	'b' => [],
	'a' => [
		'id' => [],
		'href' => [],
		'title' => [],
		'data-*' => [],
	],
	'font' => [
		'size' => [],
	],
	'blockquote' => [ 'class' => [] ],
	'br' => [],
];

// Retrieve vars from calling class.
$lumiere_faqsection_processed = get_transient( Admin_Menu::TRANSIENT_ADMIN )[0]; // text in array from the faq section in readme
?>

<div class="lumiere_wrap">

	<div class="lumiere_title_options lumiere_border_shadow">
		<h3 id="layout" name="layout"><?php esc_html_e( 'Frequently asked questions', 'lumiere-movies' ); ?></h3>
	</div>

	<div id="lumiere_help_plb_faq" class="lumiere_border_shadow">
		<ol>
	<?php
	$lumiere_count_rows = 0;
	foreach ( $lumiere_faqsection_processed as $lumiere_faq_text ) {
		if ( $lumiere_count_rows % 2 === 1 ) { // uneven number -> content ?>
		
			<li class="titresection"><?php echo wp_kses( $lumiere_faq_text, $lumiere_escape_wp_kses ); ?></li>
			<?php
			$lumiere_count_rows++;
			continue;
		}
		// even number -> title
		?>
		
			<div class="lum_padding_bottom_twenty">
				<?php
				$lumiere_faq_text = is_string( $lumiere_faq_text ) ? $lumiere_faq_text : '';
				echo wp_kses( str_replace( "\n\n", "\n", $lumiere_faq_text ), $lumiere_escape_wp_kses ); ?>
			</div>
			<?php
			$lumiere_count_rows++;
	} ?>
		</ol>
	</div>
</div>

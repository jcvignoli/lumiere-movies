<?php

 #############################################################################
 # Lumiere Movies                                                            #
 # written by Prometheus group                                               #
 # https://www.jcvignoli.com/blog                                            #
 # ------------------------------------------------------------------------- #
 # This program is free software; you can redistribute and/or modify it      #
 # under the terms of the GNU General Public License (see LICENSE)           #
 # ------------------------------------------------------------------------- #
 #									              #
 #  Function : Add widget function                                           #
 #									              #
 #############################################################################

/** Registers Lumiere Movies widget so it appears with the other available
**  widgets and can be dragged and dropped into any active sidebars
** 
*/

function lumiere_widget($args) {
	global $imdb_admin_values, $imdb_widget_values, $wp_query;
	extract($args);

	$options = get_option('widget_imdbwidget');

	$name_sanitized = get_post(intval( $filmid ));
	$title_box = empty($options['title']) ? esc_html__('IMDb data', 'lumiere-movies') : sanitize_text_field( $options['title'] ); //this is the widget title, from *wordpress* widget options

	$filmid = intval( $wp_query->post->ID );

	if ( ((is_single()) OR (is_page())) && ($imdb_admin_values['imdbdirectsearch'] == true) ) {
	// shows widget only for a post or a page, when option "direct search" is switched on

		if ( $imdb_widget_values['imdbautopostwidget'] == true) {
		// automatically takes the post name to display the movie related, according to Lumiere Movies preferences (-> widget -> misc)
			$imdballmeta[0] = sanitize_text_field( $name_sanitized->post_title );

			// Initialize lumiere_core class, add head that is only for /imdblt/ URLs
			$start = new lumiere_core();
			add_action('wp_head', $start->lumiere_add_head_blog('inc.movie') ,1 );

			echo $before_widget;
			echo $before_title . $title_box . $after_title;
			echo "<div class='imdbincluded'>";
			/*$content = "";
			echo $content;*/
			require_once( $imdb_admin_values['imdbpluginpath'] . 'inc/imdb-movie.inc.php');
			echo "</div>";
			echo $after_widget;

			add_action('wp_footer', $start->lumiere_add_footer_blog('inc.movie') ,1 );
		}

		foreach (get_post_meta($filmid, 'imdb-movie-widget', false) as $key => $value) {
		// if meta tag "imdb-movie-widget" can be found

			// Initialize lumiere_core class, add head that is only for /imdblt/ URLs
			$start = new lumiere_core();
			add_action('wp_head', $start->lumiere_add_head_blog('inc.movie') ,1 );
			
			$imdballmeta[0] = $value;
			echo $before_widget;
			echo $before_title . $title_box . $after_title;
			echo "<div class='imdbincluded'>";
			require_once( $imdb_admin_values['imdbpluginpath'] . 'inc/imdb-movie.inc.php');
			echo "</div>";
			echo $after_widget;

			add_action('wp_footer', $start->lumiere_add_footer_blog('inc.movie') ,1 );
		}
		foreach (get_post_meta($filmid, 'imdb-movie-widget-bymid', false) as $key => $value) {
		// if ID movie has been provided through "imdb-movie-widget-bymid"
			$imdballmeta = 'imdb-movie-widget-noname';
			$moviespecificid = str_pad($value, 7, "0", STR_PAD_LEFT);

			// Initialize lumiere_core class, add head that is only for /imdblt/ URLs
			$start = new lumiere_core();
			add_action('wp_head', $start->lumiere_add_head_blog('inc.movie') ,1 );

			echo $before_widget;
			echo $before_title . $title_box . $after_title;
			echo "<div class='imdbincluded'>";
			require_once( $imdb_admin_values['imdbpluginpath'] . 'inc/imdb-movie.inc.php');
			echo "</div>";
			echo $after_widget;

			add_action('wp_footer', $start->lumiere_add_footer_blog('inc.movie') ,1 );
		}
	}
}

/**
Register the optional widget control form
*/
function lumiere_widget_control() {
	$options = $newoptions = get_option('widget_imdbwidget');
	if ($_POST["imdbW-submit"]) {
		$newoptions['title'] = sanitize_text_field($_POST["imdbW-title"]);
	}
	if ($options != $newoptions) {
		$options = $newoptions;
		update_option('widget_imdbwidget', $options);
	}
	$title = sanitize_text_field( $options['title']);
	echo '<p><label for="imdbW-title">' . esc_html__( 'Title:', 'lumiere-movies' );
	echo '<input class="imdblt_width_twohundredfifty" id="imdbW-title" name="imdbW-title" type="text" value="' . $title . '" /></label></p>';
	echo '<input type="hidden" id="imdbW-submit" name="imdbW-submit" value="1" />';
}

/**
Register the Widget into the WordPress Widget API
*/
function lumiere_register_widget() {
	// Check Wordpress Widget && Lumière Configuration are available
	if ( !function_exists('wp_register_sidebar_widget') || !class_exists('lumiere_settings_conf')) {
		return;
	} else {
		wp_register_sidebar_widget('lumiere_widget_id', 'Lumière! movies', 'lumiere_widget');
		wp_register_widget_control('lumiere_widget_id', 'Lumière! movies', 'lumiere_widget_control');
	}
}	
?>

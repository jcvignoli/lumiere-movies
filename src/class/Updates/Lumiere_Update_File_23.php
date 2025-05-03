<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.6.1, update 23
 * Child of Updates class
 *
 * This class updates data for a new Lumière version
 * When writing a new update class, make sure to update only:
 * 1/ const LUMIERE_VERSION_UPDATE
 * 2/ const LUMIERE_NUMBER_UPDATE
 * 3/ function lumiere_run_local_update()
 * 4/ the classname
 *
 * This file is automatically registered and run in class-updates.php
 * @phpcs:disable WordPress.Files.FileName
 *
 * @copyright (c) 2025, Lost Highway
 * @package       lumieremovies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
final class Lumiere_Update_File_23 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.6.1';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 23;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update(): void {

		/**
		 * Execute the check in Updates parent class, passing the constants.
		 * The validating function makes sure that this update has to be run.
		 * If not, exit.
		 */
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		/**
		 * Update the number of updates already processed in Lumière options.
		 * This is executed at the beggining, so if there is an issue, it's not repeated
		 */
		$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . (string) self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );
		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * (1) Replace in all posts the options strings to be parsed in frontend
		 * It is used in admin section (post edition) to display the movie/person blocks inside the post (not the widget, so not in custom metadata)
		 * Embed the option with double quote to make sure it's not executed twice on same value
		 */
		global $wpdb;

		sleep( 5 ); // Avoids the database update to fail, dunno why exactly
		$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Updating post spans and gutenberg tags' );

		// movie_id
		$vars_movie_id = [ '"movie_id"', '"lum_movie_id"' ];
		$wpdb->flush();
		$result_movie_id = $wpdb->query( $wpdb->prepare( "UPDATE `$wpdb->posts` SET `post_content` = REPLACE( post_content, %s, %s );", $vars_movie_id ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		if ( is_bool( $result_movie_id ) ) {
			$text = "Lumière database *movie_id* bit error: a bool was returned, it shouldn't happen " . $wpdb->last_error;
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			// In test
		} elseif ( is_int( $result_movie_id ) && $result_movie_id === 0 ) {
			$text = 'Lumière database *movie_id* bit error with *lum_movie_id*: ' . $wpdb->last_error;
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( is_int( $result_movie_id ) ) {
			$text = 'Lumière database *movie_id* bit successfully updated to *lum_movie_id* for ' . strval( $result_movie_id ) . ' rows';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = "Lumière database *movie_id* bit error: $wpdb->last_error";
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		// movie_title
		$vars_movie_title = [ '"movie_title"', '"lum_movie_title"' ];
		$wpdb->flush();
		$result_movie_title = $wpdb->query( $wpdb->prepare( "UPDATE `$wpdb->posts` SET `post_content` = REPLACE( post_content, %s, %s );", $vars_movie_title ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		if ( is_bool( $result_movie_title ) ) {
			$text = "Lumière database *movie_title* bit error: a bool was returned, it shouldn't happen " . $wpdb->last_error;
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( is_int( $result_movie_title ) ) {
			$text = 'Lumière database *movie_title* bit successfully updated to *lum_movie_title* for ' . strval( $result_movie_title ) . ' rows';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = "Lumière database *movie_title* bit error: $wpdb->last_error";
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// person_name
		$vars_perso_name = [ '"person_name"', '"lum_person_name"' ];
		$wpdb->flush();
		$result_perso_name = $wpdb->query( $wpdb->prepare( "UPDATE `$wpdb->posts` SET `post_content` = REPLACE( post_content, %s, %s );", $vars_perso_name ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		if ( is_bool( $result_perso_name ) ) {
			$text = "Lumière database *person_name* bit error: a bool was returned, it shouldn't happen " . $wpdb->last_error;
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( is_int( $result_perso_name ) ) {
			$text = 'Lumière database *person_name* bit successfully updated to *lum_person_name* for ' . strval( $result_perso_name ) . ' rows';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = "Lumière database *person_name* bit error: $wpdb->last_error";
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}
		// person_id
		$vars_perso_id = [ '"person_id"', '"lum_person_id"' ];
		$wpdb->flush();
		$result_perso_id = $wpdb->query( $wpdb->prepare( "UPDATE `$wpdb->posts` SET `post_content` = REPLACE( post_content, %s, %s );", $vars_perso_id ) );// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber
		if ( is_bool( $result_perso_id ) ) {
			$text = "Lumière database *person_id* bit error: a bool was returned, it shouldn't happen " . $wpdb->last_error;
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} elseif ( is_int( $result_perso_id ) ) {
			$text = 'Lumière database *person_id* bit successfully updated to *lum_person_id* for ' . strval( $result_perso_id ) . ' rows';
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		} else {
			$text = "Lumière database *person_id* bit error: $wpdb->last_error";
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * (2) Replace Lumière widget
		 */
		$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Updating widget, removing [lumiereWidget]' );
		$current_option = get_option( 'widget_block' );
		if ( is_array( $current_option ) && count( $current_option ) > 0 ) {
			$new_option = $current_option;
			foreach ( $current_option as $key => $option ) {
				if ( isset( $option['content'] ) && is_string( $option['content'] ) && str_contains( $option['content'], '[lumiereWidget]' )  ) {
					$new_option[ $key ] = [ 'content' => str_replace( [ '[lumiereWidget]', '[/lumiereWidget]' ], '', $option['content'] ) ];
					unset( $current_option['content'] );
				}
			}
			$result_update = update_option( 'widget_block', $new_option );
			if ( $result_update === true ) {
				$text = 'Lumière database removing *[lumiereWidget][/lumiereWidget]* succesfully updated';
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			} else {
				$text = 'Lumière database *[lumiereWidget][/lumiereWidget]* not update';
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
			}
		} else {
			$text = 'Lumière database *[lumiereWidget][/lumiereWidget]* not update';
			$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] $text" );
		}

		/**
		 * (3) Update metadata
		 */
		// lumiere_widget_movieid becomes lum_movie_id_widget
		$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Updating metada' );
		$args_lumiere_widget_movieid = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_movieid' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'post',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args_lumiere_widget_movieid );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_metadata( 'post', $post->ID, 'lumiere_widget_movieid', true );
			if ( $meta_value !== false && add_metadata( 'post', $post->ID, '_lum_movie_id_widget', $meta_value ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_widget_movieid' );
				add_metadata( 'post', $post->ID, '_lum_form_type_query', 'lum_movie_id' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_movieid* in postID $post->ID to *_lum_movie_id_widget*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_movieid' in postID $post->ID metadata: " . strval( $meta_value ) );
			}
		}
		// page
		$args_lumiere_widget_movieid_page = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_movieid' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'page',
		]; // Select all relevant pages.
		$posts_array = get_posts( $args_lumiere_widget_movieid_page );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_post_meta( $post->ID, 'lumiere_widget_movieid', true );
			if ( $meta_value !== false && add_post_meta( $post->ID, '_lum_form_type_query', 'lum_movie_id' ) !== false ) {
				add_post_meta( $post->ID, '_lum_movie_id_widget', $meta_value );
				delete_post_meta( $post->ID, 'lumiere_widget_movieid' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_movieid* in Page postID $post->ID to *lum_movie_id*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_movieid' in Page postID $post->ID" );
			}
		}

		// lumiere_widget_movietitle becomes lum_movie_title_widget
		$args_lumiere_widget_movietitle = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_movietitle' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'post',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args_lumiere_widget_movietitle );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_metadata( 'post', $post->ID, 'lumiere_widget_movietitle', true );
			if ( $meta_value !== false && add_metadata( 'post', $post->ID, '_lum_movie_title_widget', $meta_value ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_widget_movietitle' );
				add_metadata( 'post', $post->ID, '_lum_form_type_query', 'lum_movie_title' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_movietitle* in postID $post->ID to *_lum_movie_title_widget*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_movietitle' in postID $post->ID metadata: " . strval( $meta_value ) );
			}
		}
		// page
		$args_lumiere_widget_movietitle_page = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_movietitle' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'page',
		]; // Select all relevant pages.
		$posts_array = get_posts( $args_lumiere_widget_movietitle_page );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_post_meta( $post->ID, 'lumiere_widget_movietitle', true );
			if ( $meta_value !== false && add_post_meta( $post->ID, '_lum_form_type_query', 'lum_movie_title' ) !== false ) {
				add_post_meta( $post->ID, '_lum_movie_title_widget', $meta_value );
				delete_post_meta( $post->ID, 'lumiere_widget_movietitle' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_movietitle* in Page postID $post->ID to *lum_movie_title*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_movietitle' in Page postID $post->ID" );
			}
		}

		// lumiere_widget_personname becomes lum_person_name_widget
		$args = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_personname' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'post',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_metadata( 'post', $post->ID, 'lumiere_widget_personname', true );
			if ( $meta_value !== false && add_metadata( 'post', $post->ID, '_lum_person_name_widget', $meta_value ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_widget_personname' );
				add_metadata( 'post', $post->ID, '_lum_form_type_query', 'lum_person_name' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_personname* in postID $post->ID to *_lum_person_name_widget*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_personname' in postID $post->ID metadata: " . strval( $meta_value ) );
			}
		}
		// page
		$args_lumiere_widget_personname_page = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_personname' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'page',
		]; // Select all relevant pages.
		$posts_array = get_posts( $args_lumiere_widget_personname_page );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_post_meta( $post->ID, 'lumiere_widget_personname', true );
			if ( $meta_value !== false && add_post_meta( $post->ID, '_lum_form_type_query', 'lum_person_name' ) !== false ) {
				add_post_meta( $post->ID, '_lum_person_name_widget', $meta_value );
				delete_post_meta( $post->ID, 'lumiere_widget_personname' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_personname* in Page postID $post->ID to *lum_person_name*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_personname' in Page postID $post->ID" );
			}
		}

		// lumiere_widget_personid becomes lum_person_id_widget
		$args_lumiere_widget_personid = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_personid' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'post',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args_lumiere_widget_personid );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_metadata( 'post', $post->ID, 'lumiere_widget_personid', true );
			if ( $meta_value !== false && add_metadata( 'post', $post->ID, '_lum_person_id_widget', $meta_value ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_widget_personid' );
				add_metadata( 'post', $post->ID, '_lum_form_type_query', 'lum_person_id' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_personid* in postID $post->ID to *_lum_person_id_widget*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_personid' in postID $post->ID" );
			}
		}
		// page
		$args_lumiere_widget_personid_page = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_widget_personid' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'page',
		]; // Select all relevant pages.
		$posts_array = get_posts( $args_lumiere_widget_personid_page );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_post_meta( $post->ID, 'lumiere_widget_personid', true );
			if ( $meta_value !== false && add_post_meta( $post->ID, '_lum_form_type_query', 'lum_person_id' ) !== false ) {
				add_post_meta( $post->ID, '_lum_person_id_widget', $meta_value );
				delete_post_meta( $post->ID, 'lumiere_widget_personid' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_widget_personid* in Page postID $post->ID to *lum_person_id*" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_widget_personid' in Page postID $post->ID" );
			}
		}

		// lumiere_autotitlewidget_perpost becomes _lum_autotitle_perpost and the value changes to boolean
		$args_lumiere_autotitlewidget_perpost = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_autotitlewidget_perpost' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'post',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args_lumiere_autotitlewidget_perpost );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_metadata( 'post', $post->ID, 'lumiere_autotitlewidget_perpost', true );
			if ( $meta_value === 'enabled' && add_metadata( 'post', $post->ID, '_lum_autotitle_perpost', '0' ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_autotitlewidget_perpost' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_autotitlewidget_perpost* in postID $post->ID to *_lum_autotitle_perpost*" );
			} elseif ( $meta_value === 'disabled' && add_metadata( 'post', $post->ID, '_lum_autotitle_perpost', '1' ) !== false ) {
				delete_metadata( 'post', $post->ID, 'lumiere_autotitlewidget_perpost' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated 'lumiere_autotitlewidget_perpost' in postID $post->ID" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_autotitlewidget_perpost' in postID $post->ID" );
			}
		}
		//page
		$args_lumiere_autotitlewidget_perpost_page = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_autotitlewidget_perpost' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'page',
		]; // Select all relevant pages.
		$posts_array = get_posts( $args_lumiere_autotitlewidget_perpost_page );
		/** @psalm-var \WP_Post $post -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post ) {
			$meta_value = get_post_meta( $post->ID, 'lumiere_autotitlewidget_perpost', true );
			if ( $meta_value === 'enabled' && add_post_meta( $post->ID, '_lum_autotitle_perpost', '0' ) !== false ) {
				delete_post_meta( $post->ID, 'lumiere_autotitlewidget_perpost' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated *lumiere_autotitlewidget_perpost* in Page postID $post->ID to *_lum_autotitle_perpost*" );
			} elseif ( $meta_value === 'disabled' && add_post_meta( $post->ID, '_lum_autotitle_perpost', '1' ) !== false ) {
				delete_post_meta( $post->ID, 'lumiere_autotitlewidget_perpost' );
				$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully updated 'lumiere_autotitlewidget_perpost' in Page postID $post->ID" );
			} else {
				$this->logger->log?->error( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Error, couldn't update 'lumiere_autotitlewidget_perpost' in Page postID $post->ID" );
			}
		}

		/**
		 * (4) Delete obsolete metadata 'lumiere_autowidget_perpost'
		 * lumiere_autowidget_perpost was replaced by lumiere_autotitlewidget_perpost, but was left behind
		 * Probably not needed, but found one in my database
		 */
		$this->logger->log?->debug( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . '] Updating metada lumiere_autowidget_perpost' );
		$args_lumiere_autowidget_perpost = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_autowidget_perpost' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => [ 'post' ],
		]; // Select all relevant posts.
		$posts_array = get_posts( $args_lumiere_autowidget_perpost );
		/** @psalm-var \WP_Post $post_array -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post_array ) {
			delete_post_meta( $post_array->ID, 'lumiere_autowidget_perpost' );
			$this->logger->log?->info( '[updateVersion' . (string) self::LUMIERE_NUMBER_UPDATE . "] Successfully deleted *lumiere_autowidget_perpost* in postID $post_array->ID" );
		}

		/** ------------------------- Editing part (end) --------------
		 */
	}
}


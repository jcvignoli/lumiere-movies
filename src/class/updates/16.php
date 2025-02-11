<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 4.1.1, update 16
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
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2024, Lost Highway
 *
 * @package lumiere-movies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_16 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '4.1.1';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 16;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update(): void {

		// Execute the check in Updates parent class, passing the constants.
		// The validating function makes sure that this update has to be run.
		// If not, exit.
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		// Update the number of updates already processed in Lumière options.
		$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', strval( $nb_of_updates ) );

		/** ------------------------- Editing part (beginning) --------------
		 */

		/**
		 * Change POSTS METADATA: keys are replaced by new ones but keep their former values, and obsolete key ones are deleted
		 * Was already in update 15, but post type was limited to "posts". Pages where not taken into account. added 'post_type' => 'any',
		 * @see \Lumiere\Admin\Metabox_Selection where those keys were changed.
		 */
		global $post;

		/** (1) Replace 'imdb-movie-widget-bymid' by 'lumiere_widget_movieid' in all posts metadata */
		$args = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'imdb-movie-widget-bymid' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'any',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args );
		/** @psalm-var \WP_Post $post_array -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post_array ) {
			$post_id = $post_array->ID;
			$value = get_post_meta( $post_id, 'imdb-movie-widget-bymid', true );
			if ( strlen( $value ) > 0 ) {
				update_post_meta( $post_id, 'lumiere_widget_movieid', $value, 'imdb-movie-widget-bymid' );
				delete_post_meta( $post_id, 'imdb-movie-widget-bymid' );
				$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Successfully replaced 'imdb-movie-widget-bymid' by 'lumiere_widget_movieid' in postID $post_id " );
			}
		}

		/** (2) Replace 'imdb-movie-widget' by 'lumiere_widget_movietitle' in all posts metadata */
		$args = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'imdb-movie-widget' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'any',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args );
		/** @psalm-var \WP_Post $post_array -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post_array ) {
			$post_id = $post_array->ID;
			$value = get_post_meta( $post_id, 'imdb-movie-widget', true );
			if ( strlen( $value ) > 0 ) {
				update_post_meta( $post_id, 'lumiere_widget_movietitle', $value, 'imdb-movie-widget' );
				delete_post_meta( $post_id, 'imdb-movie-widget' );
				$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Successfully replaced 'imdb-movie-widget' by 'lumiere_widget_movietitle' in postID $post_id " );
			}
		}

		/** (3) Delete obsolete metadatas */
		$args = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_queryid_widget' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'any',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args );
		/** @psalm-var \WP_Post $post_array -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post_array ) {
			delete_post_meta( $post_array->ID, 'lumiere_queryid_widget' );
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Successfully deleted 'lumiere_queryid_widget' in postID $post_array->ID " );
		}
		$args = [
			'posts_per_page' => -1,
			'meta_query' => [ [ 'key' => 'lumiere_queryid_widget_input' ] ], // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
			'post_type' => 'any',
		]; // Select all relevant posts.
		$posts_array = get_posts( $args );
		/** @psalm-var \WP_Post $post_array -- due to the $args passed (not using 'fields' in get_posts()), always return \WP_Post */
		foreach ( $posts_array as $post_array ) {
			delete_post_meta( $post_array->ID, 'lumiere_queryid_widget_input' );
			$this->logger->log->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] Successfully deleted 'lumiere_queryid_widget_input' in postID $post_array->ID " );
		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}

<?php declare( strict_types = 1 );
/**
 * Specific Class for updating : ************************ Lumière version 3.5, update 7
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
 * @copyright (c) 2022, Lost Highway
 *
 * @package lumiere-movies
 */

namespace Lumiere\Updates;

use Lumiere\Config\Get_Options;

/**
 * The logic is in the parent class, the data in the current child class
 * -> Everytime an update is processed, imdbHowManyUpdates is automatically increased by 1 (in child class)
 */
class Lumiere_Update_File_07 extends \Lumiere\Updates {

	/**
	 * Version of Lumière! that can trigger the update
	 */
	const LUMIERE_VERSION_UPDATE = '3.5';

	/**
	 * Number of updates that can trigger the update
	 * Must match both the filname and classname
	 * Each update child class must have an unique number
	 */
	const LUMIERE_NUMBER_UPDATE = 7;

	/**
	 * Run the local update if lumiere_check_if_run_update() was successful
	 * Everytime an update is processed, imdbHowManyUpdates is increased by 1
	 */
	protected function lumiere_run_local_update (): void {

		// Execute the check in Updates parent class, passing the constants.
		// The validating function makes sure that this update has to be run.
		// If not, exit.
		if ( $this->lumiere_check_if_run_update( self::LUMIERE_VERSION_UPDATE, self::LUMIERE_NUMBER_UPDATE ) === false ) {
			return;
		}

		// Simplify the coding.
		$logger = $this->logger->log;

		// Update the number of updates already processed in Lumière options.
		$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . '] Starting update ' . self::LUMIERE_NUMBER_UPDATE );
		$nb_of_updates = ( intval( $this->imdb_admin_values['imdbHowManyUpdates'] ) + 1 );

		$this->lumiere_update_options( Get_Options::get_admin_tablename(), 'imdbHowManyUpdates', $nb_of_updates );

		/** ------------------------- Editing part (beginning) --------------
		 */

		// Replace 'imdbwidgetcomments' by 'imdbwidgetcomment'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetcomments' ) ) {

			$text = 'Lumière option imdbwidgetcomments successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetcomments could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetcomment', false ) ) {

			$text = 'Lumière option imdbwidgetcomment successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetcomment could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Replace 'imdbwidgetcolors' by 'imdbwidgetcolor'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetcolors' ) ) {

			$text = 'Lumière option imdbwidgetcolors successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetcolors could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetcolor', false ) ) {

			$text = 'Lumière option imdbwidgetcolor successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetcolor could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Replace 'imdbwidgettaglines' by 'imdbwidgettagline'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgettaglines' ) ) {

			$text = 'Lumière option imdbwidgettaglines successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgettaglines could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgettagline', false ) ) {

			$text = 'Lumière option imdbwidgettagline successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgettagline could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Replace 'imdbwidgetquotes' by 'imdbwidgetquote'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetquotes' ) ) {

			$text = 'Lumière option imdbwidgetquotes successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetquotes could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetquote', false ) ) {

			$text = 'Lumière option imdbwidgetquote successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetquote could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Replace 'imdbwidgetgoofs' by 'imdbwidgetgoof'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetgoofs' ) ) {

			$text = 'Lumière option imdbwidgetgoofs successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetgoofs could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetgoof', false ) ) {

			$text = 'Lumière option imdbwidgetgoof successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetgoof could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		// Replace 'imdbwidgetkeywords' by 'imdbwidgetkeyword'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbwidgetkeywords' ) ) {

			$text = 'Lumière option imdbwidgetkeywords successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetkeywords could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbwidgetkeyword', false ) ) {

			$text = 'Lumière option imdbwidgetkeyword successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbwidgetkeyword could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		// Replace 'imdbtaxonomykeywords' by 'imdbtaxonomykeyword'
		// Singularizing items
		if ( true === $this->lumiere_remove_options( Get_Options::get_data_tablename(), 'imdbtaxonomykeywords' ) ) {

			$text = 'Lumière option imdbtaxonomykeywords successfully removed.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbtaxonomykeywords could not be removed.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}
		if ( true === $this->lumiere_add_options( Get_Options::get_data_tablename(), 'imdbtaxonomykeyword', false ) ) {

			$text = 'Lumière option imdbtaxonomykeyword successfully added.';

			$logger->info( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		} else {

			$text = 'Lumière option imdbtaxonomykeyword could not be added.';

			$logger->error( '[updateVersion' . self::LUMIERE_NUMBER_UPDATE . "] $text" );

		}

		/** ------------------------- Editing part (end) --------------
		 */

	}

}

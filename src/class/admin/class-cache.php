<?php declare( strict_types = 1 );
/**
 * Cache options class
 * Child of Admin
 *
 * @author        Lost Highway <https://www.jcvignoli.com/blog>
 * @copyright (c) 2021, Lost Highway
 *
 * @version       1.0
 * @package lumiere-movies
 * @TODO: rewrite and mainstream the class
 */

namespace Lumiere\Admin;

// If this file is called directly, abort.
if ( ( ! defined( 'WPINC' ) ) || ( ! class_exists( 'Lumiere\Settings' ) ) ) {
	wp_die( esc_html__( 'You can not call directly this page', 'lumiere-movies' ) );
}

// Use IMDbPHP library for cache creation
use Imdb\Title;
use Imdb\Person;
use Lumiere\Settings;
use Lumiere\Utils;
use Lumiere\Plugins\Imdbphp;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @phpstan-import-type OPTIONS_CACHE from \Lumiere\Settings
 */
class Cache extends \Lumiere\Admin {

	/**
	 * Escape html tags
	 */
	const ALLOWED_HTML = [
		'strong' => [],
	];

	/**
	 * message notification options
	 * @var array<string, string> $messages
	 */
	private array $messages = [
		'cache_options_update_success_msg' => 'Cache options saved.',
		'cache_options_refresh_success_msg' => 'Cache options successfully reset.',
		'cache_delete_all_msg' => 'All cache files deleted.',
		'cache_delete_ticked_msg' => 'Ticked file(s) deleted.',
		'cache_delete_individual_msg' => 'Selected cache file successfully deleted.',
		'cache_refresh_individual_msg' => 'Selected cache file successfully refreshed.',
	];

	/**
	 * Class \Lumiere\Imdbphp
	 *
	 */
	private Imdbphp $imdbphp_class;

	/**
	 *  Constructor
	 */
	public function __construct() {

		// Construct parent class
		parent::__construct();

		// Enter in debug mode, for development version only
		if ( ( isset( $this->imdb_admin_values['imdbdebug'] ) ) && ( $this->imdb_admin_values['imdbdebug'] === '1' ) ) {

			// Activate debugging
			$this->utils_class->lumiere_activate_debug( $this->imdb_cache_values, 'no_var_dump', null ); # don't display set_error_handler("var_dump") that gets the page stuck in an endless loop

		}

		// Start Imdbphp class.
		$this->imdbphp_class = new Imdbphp();

		// Logger: set to true to display debug on screen.
		$this->logger->lumiere_start_logger( get_class(), false );

		// Display notices.
		add_action( 'admin_notices', [ $this, 'lumiere_admin_display_messages' ] );
	}

	/**
	 * Display the layout
	 *
	 */
	public function lumiere_cache_layout(): void {

		$this->lumiere_cache_head();
		$this->lumiere_cache_display_submenu();
		$this->lumiere_cache_display_body();

	}

	/**
	 *  Display admin notices
	 */
	public function lumiere_admin_display_messages(): void {

		// If $_GET["msg"] is found, display a related notice
		if ( ( isset( $_GET['msg'] ) ) && array_key_exists( sanitize_text_field( $_GET['msg'] ), $this->messages ) ) {
			if ( sanitize_text_field( $_GET['msg'] ) === 'cache_options_update_success_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_options_update_success_msg'] ) );
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'cache_options_refresh_success_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_options_refresh_success_msg'] ) );
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'cache_delete_all_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_delete_all_msg'] ) );
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'cache_delete_ticked_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_delete_ticked_msg'] ) );
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'cache_delete_individual_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_delete_individual_msg'] ) );
			} elseif ( sanitize_text_field( $_GET['msg'] ) === 'cache_refresh_individual_msg' ) {
				echo Utils::lumiere_notice( 1, esc_html( $this->messages['cache_refresh_individual_msg'] ) );
			}
		}

	}

	/**
	 *  Display head
	 *
	 */
	private function lumiere_cache_head(): void {

		##################################### Saving options

		// save data selected
		if ( isset( $_POST['update_cache_options'] ) ) {
			check_admin_referer( 'cache_options_check', 'cache_options_check' );

			foreach ( $_POST as $key => $postvalue ) {
				// Sanitize
				$key_sanitized = sanitize_key( $key );

				$keynoimdb = str_replace( 'imdb_', '', $key_sanitized );
				if ( isset( $_POST[ $key_sanitized ] ) ) {
					/** @phpstan-var key-of<non-empty-array<OPTIONS_CACHE>> $keynoimdb
					 * @phpstan-ignore-next-line */
					$this->imdb_cache_values[ $keynoimdb ] = sanitize_text_field( $_POST[ $key_sanitized ] );
				}
			}

			update_option( Settings::LUMIERE_CACHE_OPTIONS, $this->imdb_cache_values );

			// Set up cron
			if (
				$this->imdb_cache_values['imdbcachekeepsizeunder'] === '1'
				&& intval( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ) > 0
				&& isset( $_POST['imdb_imdbcachekeepsizeunder'] )
			) {

				// Add WP cron if not already registred.
				$this->lumiere_cache_add_cron_deleteoversizedfolder();

				// Remove cron
			} elseif (
				$this->imdb_cache_values['imdbcachekeepsizeunder'] === '0'
				&& isset( $_POST['imdb_imdbcachekeepsizeunder'] )
			) {
				// Add WP cron if not already registred.
				$this->lumiere_cache_remove_cron_deleteoversizedfolder();
			}

			// display message on top
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Cache options saved.', 'lumiere-movies' ) . '</strong>' );
			if ( headers_sent() ) {

				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();

			}
		}

		// reset options selected
		if ( isset( $_POST['reset_cache_options'] ) ) {

			check_admin_referer( 'cache_options_check', 'cache_options_check' );

			delete_option( Settings::LUMIERE_CACHE_OPTIONS );

			// display message on top
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Cache options reset.', 'lumiere-movies' ) . '</strong>' );

			// Display a refresh link otherwise refreshed data is not seen
			if ( headers_sent() ) {

				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();

			}
		}

		// delete all cache files
		if ( isset( $_POST['delete_all_cache'] ) ) {

			check_admin_referer( 'cache_all_and_query_check', 'cache_all_and_query_check' );

			// prevent drama
			if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {

				wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );

			}

			// Delete cache
			Utils::lumiere_unlink_recursive( $this->imdb_cache_values['imdbcachedir'] );

			// display message on top
			echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'All cache files deleted.', 'lumiere-movies' ) . '</strong>' );
			if ( headers_sent() ) {

				echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
				exit();

			}
		}

		// delete all query cache files
		if ( isset( $_POST['delete_query_cache'] ) ) {

			check_admin_referer( 'cache_all_and_query_check', 'cache_all_and_query_check' );

			$this->cache_delete_query_cache_files();

		}

		##################################### delete several ticked files

		if ( isset( $_POST['delete_ticked_cache'] ) ) {

			check_admin_referer( 'cache_options_check', 'cache_options_check' );

			$this->cache_delete_ticked_files();

		}

		##################################### delete a specific file by clicking on it

		if ( isset( $_GET['dothis'] ) && ( $_GET['dothis'] === 'delete' ) && isset( $_GET['type'] ) ) {

			$this->cache_delete_specific_file();

		}

		##################################### refresh a specific file by clicking on it

		if ( isset( $_GET['dothis'] ) && ( $_GET['dothis'] === 'refresh' ) && isset( $_GET['type'] ) ) {

			$this->cache_refresh_specific_file();

		}
	}

	/**
	 * Delete a specific file by clicking on it
	 *
	 */
	private function cache_delete_specific_file(): void {

		global $wp_filesystem;

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) || ! isset( $_GET['where'] ) ) {
			wp_die( esc_html__( 'Cannot work this way.', 'lumiere-movies' ) );
		}

		// delete single movie.
		if ( ( $_GET['type'] ) === 'movie' ) {
			$id_sanitized = '';
			$id_sanitized = esc_html( $_GET['where'] );
			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . 'title.tt' . $id_sanitized . '*' );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) < 1 || Utils::lumiere_wp_filesystem_cred( $name_sanitized[0] ) === false ) {
				wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist or you do not have the credentials.', 'lumiere-movies' ) ) );
			}

			foreach ( $name_sanitized as $cache_to_delete ) {

				if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
					continue;
				}

				$wp_filesystem->delete( esc_url( $cache_to_delete ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}
		}

		// delete single person.
		if ( ( $_GET['type'] ) === 'people' ) {
			$id_sanitized = '';
			$id_sanitized = esc_html( $_GET['where'] );
			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . 'name.nm' . $id_sanitized . '*' );

			// if file doesn't exist or can't get credentials.
			if ( $name_sanitized === false || count( $name_sanitized ) < 1 || Utils::lumiere_wp_filesystem_cred( $name_sanitized[0] ) === false ) {
				wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist or you do not have the credentials.', 'lumiere-movies' ) ) );
			}

			foreach ( $name_sanitized as $cache_to_delete ) {

				if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
					continue;
				}

				$wp_filesystem->delete( esc_url( $cache_to_delete ) );
			}
		}

		echo Utils::lumiere_notice( 1, esc_html__( 'Selected cache file deleted.', 'lumiere-movies' ) );

	}

	/**
	 * Refresh a specific file by clicking on it
	 *
	 */
	private function cache_refresh_specific_file(): void {

		global $wp_filesystem;

		// prevent drama.
		if ( ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) || ( ! isset( $_GET['where'] ) )  ) {
			exit( esc_html__( 'Cannot work this way.', 'lumiere-movies' ) );
		}

		if ( ( $_GET['type'] ) === 'movie' ) {
			$id_sanitized = '';
			$id_sanitized = esc_html( $_GET['where'] );

			$name_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . 'title.tt' . $id_sanitized . '*' );

			// if file doesn't exist.
			if ( $name_sanitized === false || count( $name_sanitized ) < 1 || Utils::lumiere_wp_filesystem_cred( $name_sanitized[0] ) === false ) {
				wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist.', 'lumiere-movies' ) ) );
			}

			foreach ( $name_sanitized as $cache_to_delete ) {

				if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
					continue;
				}

				$wp_filesystem->delete( esc_url( $cache_to_delete ) );
			}

			// delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';

			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}

			// get again the movie.
			$movie = new Title( $id_sanitized, $this->imdbphp_class, $this->logger->log() );

			// create cache for everything.
			$movie->alsoknow();
			$movie->cast();
			$movie->colors();
			$movie->composer();
			$movie->comment_split();
			$movie->country();
			$movie->creator();
			$movie->director();
			$movie->genres();
			$movie->goofs();
			$movie->keywords();
			$movie->languages();
			$movie->officialSites();
			$movie->photo_localurl( true );
			$movie->photo_localurl( false );
			$movie->plot();
			$movie->prodCompany();
			$movie->producer();
			$movie->quotes();
			$movie->rating();
			$movie->runtime();
			$movie->soundtrack();
			$movie->taglines();
			$movie->title();
			$movie->trailers( true );
			$movie->votes();
			$movie->writing();
			$movie->year();

		}

		if ( $_GET['type'] === 'people' ) {

			$id_people_sanitized = esc_html( $_GET['where'] );
			$name_people_sanitized = glob( $this->imdb_cache_values['imdbcachedir'] . 'name.nm' . $id_people_sanitized . '*' );

			// if file doesn't exist
			if ( $name_people_sanitized === false || count( $name_people_sanitized ) < 1 || Utils::lumiere_wp_filesystem_cred( $name_people_sanitized[0] ) === false ) {
				wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist.', 'lumiere-movies' ) ) );
			}

			foreach ( $name_people_sanitized as $cache_to_delete ) {

				if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
					continue;
				}

				$wp_filesystem->delete( esc_url( $cache_to_delete ) );

			}

			// Get again the person.
			$person = new Person( $id_people_sanitized, $this->imdbphp_class, $this->logger->log() );

			// Create cache for everything.
			$person->bio();
			$person->birthname();
			$person->born();
			$person->died();
			$person->movies_all();
			$person->movies_archive();
			$person->movies_soundtrack();
			$person->movies_writer();
			$person->name();
			$person->photo_localurl();
			$person->pubmovies();
			$person->pubportraits();
			$person->quotes();
			$person->trivia();
			$person->trademark();

		}

		echo Utils::lumiere_notice( 1, esc_html__( 'Selected cache file successfully refreshed.', 'lumiere-movies' ) );

	}

	/**
	 * delete query cache files
	 *
	 */
	private function cache_delete_query_cache_files(): void {

		global $wp_filesystem;

		// prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
		}

		// Delete cache.
		$files_query = glob( $this->imdb_cache_values['imdbcachedir'] . 'find.s*' );

		// if file doesn't exist.
		if ( $files_query === false || count( $files_query ) < 1 || Utils::lumiere_wp_filesystem_cred( $files_query[0] ) === false ) {
			echo Utils::lumiere_notice( 3, esc_html__( 'No query files found.', 'lumiere-movies' ) );
			echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
			wp_die();
		}

		foreach ( $files_query as $cache_to_delete ) {

			if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
				continue;
			}

			// the file exists, it is neither . nor .., so delete!
			$wp_filesystem->delete( $cache_to_delete );
		}

		// Display messages on top.
		echo Utils::lumiere_notice( 1, '<strong>' . esc_html__( 'Query cache files deleted.', 'lumiere-movies' ) . '</strong>' );
		if ( headers_sent() ) {

			echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
			exit();

		}
	}

	/**
	 * Delete several ticked files
	 *
	 */
	private function cache_delete_ticked_files(): void {

		global $wp_filesystem;

		// Prevent drama.
		if ( ! isset( $this->imdb_cache_values['imdbcachedir'] ) ) {
			wp_die( Utils::lumiere_notice( 3, '<strong>' . esc_html__( 'No cache folder found.', 'lumiere-movies' ) . '</strong>' ) );
		}

		$id_sanitized = '';

		// For movies.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST ['imdb_cachedeletefor_movies'] ) ) {
			$count_cache_delete = count( $_POST ['imdb_cachedeletefor_movies'] );
			for ( $i = 0; $i < $count_cache_delete; $i++ ) {
				$id_sanitized = esc_html( $_POST['imdb_cachedeletefor_movies'][ $i ] );
				// phpcs:enable WordPress.Security.NonceVerification.Missing
				$cache_to_delete_files = glob( $this->imdb_cache_values['imdbcachedir'] . 'title.tt' . $id_sanitized . '*' );

				// If file doesn't exist.
				if ( $cache_to_delete_files === false || count( $cache_to_delete_files ) < 1 || Utils::lumiere_wp_filesystem_cred( $cache_to_delete_files[0] ) === false ) {
					wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist.', 'lumiere-movies' ) ) );
				}

				foreach ( $cache_to_delete_files as $cache_to_delete ) {

					if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
						continue;
					}

					$wp_filesystem->delete( esc_url( $cache_to_delete ) );

				}
			}

			// Delete pictures, small and big.
			$pic_small_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '.jpg';
			$pic_big_sanitized = $this->imdb_cache_values['imdbphotoroot'] . $id_sanitized . '_big.jpg';
			if ( file_exists( $pic_small_sanitized ) ) {
				$wp_filesystem->delete( $pic_small_sanitized );
			}
			if ( file_exists( $pic_big_sanitized ) ) {
				$wp_filesystem->delete( $pic_big_sanitized );
			}
		}

		// For people.
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST ['imdb_cachedeletefor_people'] ) ) {

			$count_cache_delete_people = count( $_POST ['imdb_cachedeletefor_people'] );

			for ( $i = 0; $i < $count_cache_delete_people; $i++ ) {

				$id_sanitized = esc_html( $_POST['imdb_cachedeletefor_people'][ $i ] );
				// phpcs:enable WordPress.Security.NonceVerification.Missing

				$files_people_delete = glob( $this->imdb_cache_values['imdbcachedir'] . 'name.nm' . $id_sanitized . '*' );

				// If files don't exist.
				if ( $files_people_delete === false || count( $files_people_delete ) < 1 || Utils::lumiere_wp_filesystem_cred( $files_people_delete[0] ) === false ) {
					echo Utils::lumiere_notice( 3, esc_html__( 'No cache people files found.', 'lumiere-movies' ) );
					echo Utils::lumiere_notice( 1, '<a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a>' );
					wp_die();
				}

				foreach ( $files_people_delete as $cache_to_delete ) {
					if ( $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '.' || $cache_to_delete === $this->imdb_cache_values['imdbcachedir'] . '..' ) {
						continue;
					}
					if ( ! file_exists( $cache_to_delete ) ) {
						wp_die( Utils::lumiere_notice( 3, esc_html__( 'This file does not exist.', 'lumiere-movies' ) ) );
					}

					$wp_filesystem->delete( esc_url( $cache_to_delete ) );

				}

			}
		}

		// Display message on top.
		echo Utils::lumiere_notice( 1, esc_html__( 'Ticked cache file(s) deleted.', 'lumiere-movies' ) );
		if ( headers_sent() ) {

			echo Utils::lumiere_notice( 1, '<div align="center"><a href="' . wp_get_referer() . '">' . esc_html__( 'Go back', 'lumiere-movies' ) . '</a></div>' );
			exit();

		}

	}

	/**
	 *  Display submenu
	 *
	 */
	private function lumiere_cache_display_submenu(): void { ?>

<div id="tabswrap">
	<div class="imdblt_double_container lumiere_padding_five">
		<div class="lumiere_flex_auto lumiere_align_center"><img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-cache-options.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache&cacheoption=option' ); ?>"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></a></div>
		<?php
		if ( '1' === $this->imdb_cache_values['imdbusecache'] ) {
			?>
		<div class="lumiere_flex_auto lumiere_align_center">&nbsp;&nbsp;<img src="<?php echo esc_url( $this->config_class->lumiere_pics_dir . 'menu/admin-cache-management.png' ); ?>" align="absmiddle" width="16px" />&nbsp;&nbsp;<a title="<?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?>" href="<?php echo esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache&cacheoption=manage' ); ?>"><?php esc_html_e( 'Manage Cache', 'lumiere-movies' ); ?></a></div>
			<?php
		};
		?>
	</div>
</div>

<div id="poststuff">

	<div class="intro_cache">
		<?php esc_html_e( 'Cache is crucial for Lumière! operations. Initial IMDb queries are quite time consuming, so if you do not want to kill your server and look for a smooth experience for your users, do not delete often your cache.', 'lumiere-movies' ); ?>
	</div>


		<?php
	}

	/**
	 *  Display the body
	 *
	 */
	private function lumiere_cache_display_body(): void {

		if ( ( ( isset( $_GET['cacheoption'] ) ) && ( $_GET['cacheoption'] === 'option' ) ) || ( ! isset( $_GET['cacheoption'] ) ) ) {

			echo "\n\t" . '<div class="postbox-container">';
			echo "\n\t\t" . '<div id="left-sortables" class="meta-box-sortables" >';

			echo "\n\t\t\t" . '<form method="post" name="imdbconfig_save" action="' . esc_url( $_SERVER['REQUEST_URI'] ) . '">';

				//------------------------------------------------------------------ =[cache options]=-
			?>

		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Cache options', 'lumiere-movies' ); ?></h3>
		</div>

	<div class="inside imblt_border_shadow">

	<div class="titresection"><?php esc_html_e( 'General options', 'lumiere-movies' ); ?></div>

		<div class="lumiere_display_flex lumiere_flex_make_responsive">
			<div class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<?php esc_html_e( 'Use cache?', 'lumiere-movies' ); ?>&nbsp;
				<input type="hidden" id="imdb_imdbusecache_no" name="imdb_imdbusecache" value="0" data-checkbox_activate="imdb_imdbcacheexpire_id" />

				<input type="checkbox" id="imdb_imdbusecache_yes" name="imdb_imdbusecache" value="1" data-checkbox_activate="imdb_imdbcacheexpire_id"
				<?php
				if ( $this->imdb_cache_values['imdbusecache'] === '1' ) {
					echo ' checked="checked"'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Whether to use a cached page to retrieve the information (if available).', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'If cache is deactived, pictures will not be displayed and it will take longer to display the page.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'Yes', 'lumiere-movies' ); ?></div>

			</div>
			<div id="imdb_imdbcacheexpire_id" class="lumiere_flex_container_content_thirty imdblt_padding_five">

				<label for="imdb_imdbcacheexpire"><?php esc_html_e( 'Cache expire', 'lumiere-movies' ); ?></label><br /><br />
				<div class="lumiere_flex_container">

					<div>
						<input type="text" id="imdb_imdbcacheexpire" name="imdb_imdbcacheexpire" size="7" value="<?php echo intval( $this->imdb_cache_values['imdbcacheexpire'] ); ?>" />
					</div>

					<div class="imdblt_padding_ten">
						<input type="checkbox" value="0" id="imdb_imdbcacheexpire_definitive" name="imdb_imdbcacheexpire_definitive" data-valuemodificator="yes" data-valuemodificator_field="imdb_imdbcacheexpire" data-valuemodificator_default="2592000"
						<?php
						if ( $this->imdb_cache_values['imdbcacheexpire'] === '0' ) {
							echo 'checked="checked"';
						};
						?>
						/>
						<label for="imdb_imdbcacheexpire"><?php esc_html_e( '(never)', 'lumiere-movies' ); ?></label>
					</div>
				</div>

				<div class="explain"><?php esc_html_e( 'Cache files older than this value (in seconds) will be automatically deleted. Insert "0" or click "never" to keep cache files forever.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "2592000" <?php esc_html_e( '(one month)', 'lumiere-movies' ); ?></div>

			</div>
		</div>



			<?php	//------------------------------------------------------------------ =[cache details]=- ?>
		<div class="titresection"><?php esc_html_e( 'Cache details', 'lumiere-movies' ); ?></div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<?php esc_html_e( 'Simplified cache details', 'lumiere-movies' ); ?>&nbsp;

				<input type="hidden" id="imdb_imdbcachedetailsshort_no" name="imdb_imdbcachedetailsshort" value="0" />
				<input type="checkbox" id="imdb_imdbcachedetailsshort_yes" name="imdb_imdbcachedetailsshort" value="1" 
				<?php
				if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) {
					echo ' checked="checked"'; }
				?>
				/>

				<div class="explain"><?php esc_html_e( 'Allows faster loading time for the "manage cache" page by displaying shorter movies and people presentation. Usefull when you have many of them.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php esc_html_e( 'No', 'lumiere-movies' ); ?></div>

			</div>
		</div>

			<?php	//------------------------------------------------------------------ =[cache cron]=- ?>
		<div class="titresection"><?php esc_html_e( 'Cache folder size limit', 'lumiere-movies' ); ?></div>

		<div class="lumiere_flex_container">

			<div class="lumiere_flex_container_content_third imdblt_padding_five">

				<div class="lumiere_flex_container">
					<div id="imdb_imdbcachekeepsizeunder_id" class="lumiere_padding_right_fifteen">
						<?php esc_html_e( 'Keep automatically cache size below a limit', 'lumiere-movies' ); ?>&nbsp;
						<input type="hidden" id="imdb_imdbcachekeepsizeunder_no" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="0" />
						<input type="checkbox" id="imdb_imdbcachekeepsizeunder_yes" name="imdb_imdbcachekeepsizeunder" data-checkbox_activate="imdb_imdbcachekeepsizeunder_sizelimit_id" value="1" <?php
						if ( $this->imdb_cache_values['imdbcachekeepsizeunder'] === '1' ) {
							echo ' checked="checked"';
						} ?> />
					</div>
					<div id="imdb_imdbcachekeepsizeunder_sizelimit_id">
						<input type="text" id="imdb_imdbcachekeepsizeunder_sizelimit"  class="lumiere_width_five_em" name="imdb_imdbcachekeepsizeunder_sizelimit" size="7" value="<?php echo esc_attr( $this->imdb_cache_values['imdbcachekeepsizeunder_sizelimit'] ); ?>" /> <i>(size in MB)</i>
					</div>
				</div>
				<div class="explain"><?php esc_html_e( 'Keep the cache folder size below a limit. Every day, WordPress will check if your cache folder is over the selected size limit and will delete the newest cache files until it meets your selected cache folder size limit.', 'lumiere-movies' ); ?> <br /><?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> <?php echo esc_html_e( 'No', 'lumiere-movies' ) . ', ' . Utils::lumiere_format_bytes( 100 * 1000000 ); // 100 MB is the default size ?></div>

			</div>

		</div>
	</div>
</div>		
			<?php
			//------------------------------------------------------------------ =[Submit selection]
			?>
			<div class="submit submit-imdb lumiere_sticky_boxshadow lumiere_align_center">
					<?php wp_nonce_field( 'cache_options_check', 'cache_options_check' ); ?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />&nbsp;&nbsp;
				<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />
			</div>
		</form>

			<?php
		}  // end $_GET['cacheoption'] == "option"

		////////////////////////////////////////////// Cache management
		if ( ( isset( $_GET['cacheoption'] ) ) && ( $_GET['cacheoption'] === 'manage' ) ) {

			// check if folder exists & store cache option is selected
			if ( file_exists( $this->imdb_cache_values['imdbcachedir'] ) ) {
				?>

	<div>
				<?php //--------------------------------------------------------- =[cache delete]=- ?>
		<div class="inside imblt_border_shadow">
			<h3 class="hndle" id="cachegeneral" name="cachegeneral"><?php esc_html_e( 'Global cache management', 'lumiere-movies' ); ?></h3>
		</div>

		<div class="inside imblt_border_shadow">
			<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" >
				<?php
				wp_nonce_field( 'cache_all_and_query_check', 'cache_all_and_query_check' );
				echo "\n";

				$imdlt_cache_file_count = $this->lumiere_cache_countfolderfiles( $this->imdb_cache_values['imdbcachedir'] );

				echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

				echo '<strong>' . esc_html__( 'Total cache size:', 'lumiere-movies' ) . ' ';
				$size_cache_total = $this->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbcachedir'] );

				/* translators: %s is replaced with the number of files */
				echo '&nbsp;' . esc_html( sprintf( _n( '%s file', '%s files', $imdlt_cache_file_count, 'lumiere-movies' ), number_format_i18n( $imdlt_cache_file_count ) ) );
				echo '&nbsp;' . esc_html__( 'using', 'lumiere-movies' );
				echo ' ' . Utils::lumiere_format_bytes( $size_cache_total );
				echo "</strong>\n";
				echo '</div>';

				// Cache files exist, offer the opportunity to delete them.
				if ( $imdlt_cache_file_count > 0 ) {

					echo '<div>';

					esc_html_e( 'If you want to reset the entire cache (this includes queries, names, and pictures) click on the button below.', 'lumiere-movies' );
					echo '<br />';
					esc_html_e( 'Beware, there is no undo.', 'lumiere-movies' );
					?>
			</div>
				<div class="submit submit-imdb" align="center">

				<input type="submit" class="button-primary" name="delete_all_cache" data-confirm="<?php esc_html_e( 'Delete all cache? Really?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete all cache', 'lumiere-movies' ); ?>" /> 

				<br />
				<br />
					<?php
					echo esc_html__( 'This button will', 'lumiere-movies' ) . '<strong> ' . esc_html__( 'delete all files', 'lumiere-movies' ) . '</strong> ' . esc_html__( 'stored in the following folder:', 'lumiere-movies' );
					echo '<br />';
					echo esc_html( $this->imdb_cache_values['imdbcachedir'] );
					?>
				</div>
					<?php

					// No files in cache
				} else {

					echo '<div class="imdblt_error">' . esc_html__( 'Lumière! cache is empty.', 'lumiere-movies' ) . '</div>';

				}
				?>

				<br />
				<?php
				$imdlt_cache_file_query = Utils::lumiere_glob_recursive( $this->imdb_cache_values['imdbcachedir'] . 'find.s*' );
				$imdlt_cache_file_query_count = count( $imdlt_cache_file_query );

				echo "\n\t\t\t" . '<div class="detailedcacheexplaination imdblt_padding_bottom_ten imdblt_align_center">';

				echo "\n\t\t\t\t" . '<strong>' . esc_html__( 'Total query cache size:', 'lumiere-movies' );
				$size_cache_query_tmp = 0;
				foreach ( $imdlt_cache_file_query as $filenamecachequery ) {
					if ( is_numeric( filesize( $filenamecachequery ) ) ) {
						$size_cache_query_tmp += intval( filesize( $filenamecachequery ) );
					}
				}
				$size_cache_query_total = $size_cache_query_tmp;
				/* translators: %s is replaced with the number of files */
				echo '&nbsp;' . sprintf( esc_html( _n( '%s file', '%s files', $imdlt_cache_file_query_count, 'lumiere-movies' ) ), intval( number_format_i18n( $imdlt_cache_file_query_count ) ) );
				echo '&nbsp;' . esc_html__( 'using', 'lumiere-movies' );
				echo ' ' . Utils::lumiere_format_bytes( intval( $size_cache_query_total ) );
				echo '</strong>';

				echo '</div>';

				// Query files exist, offer the opportunity to delete them.
				if ( $imdlt_cache_file_query_count > 0 ) {
					?>

		<div>
					<?php
					esc_html_e( 'If you want to reset the query cache (every search creates a cache file) click on the button below.', 'lumiere-movies' );
					echo '<br />';
					?>
		</div>
		<div class="submit submit-imdb" align="center">

		<input type="submit" class="button-primary" name="delete_query_cache" data-confirm="<?php esc_html_e( 'Delete query cache?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete query cache', 'lumiere-movies' ); ?>" /> 
		</div>
					<?php

					// No query files in cache.
				} else {

					echo '<div class="imdblt_error">' . esc_html__( 'Lumière! query cache is empty.', 'lumiere-movies' ) . '</div>';
				}
				?>

		</form>
	</div>
	<br />
	<br />
	<form method="post" name="lumiere_delete_ticked_cache" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" >

				<?php	//------------------------------------------------------------------ =[movies management]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachemovies" name="cachemovies"><?php esc_html_e( 'Movie\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">
				<?php
				// Scope of the files to be managed
				$files = glob( $this->imdb_cache_values['imdbcachedir'] . '{title.tt*}', GLOB_BRACE );

				// if files don't exist.
				if ( $files === false || ( count( $files ) < 1 ) ) {
					echo '<div class="imdblt_error">' . esc_html__( 'No movie\'s cache found.', 'lumiere-movies' ) . '</div>';
				}

				// if files exist.
				if ( is_array( $files ) === true && ( count( $files ) >= 1 ) && ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) ) {
					$results = [];
					foreach ( $files as $file ) {
						if ( preg_match( '!^title\.tt(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
							$results[] = new Title( $match[1], $this->imdbphp_class, $this->logger->log() );
						}
					}

					?>

		<div class="lumiere_intro_options">

					<?php esc_html_e( 'If you want to refresh movie\'s cache regardless the cache expiration time, you may either tick movie\'s checkbox(es) related to the movie you want to delete and click on "delete cache". you may also click on "refresh" to update a movie series of details.', 'lumiere-movies' ); ?>
			<br />
			<br />
					<?php esc_html_e( 'You may also select a group of movies to delete.', 'lumiere-movies' ); ?>
			<br />
			<br />
		</div>

		<div class="lumiere_flex_container">

					<?php
					$obj_sanitized = '';
					$data = [];
					if ( $results !== [] ) {
						foreach ( $results as $res ) {
							if ( get_class( $res ) === 'Imdb\Title' ) {
								$title_sanitized = esc_html( $res->title() ); // search title related to movie id
								$obj_sanitized = esc_html( $res->imdbid() );
								$filepath_sanitized = esc_url( $this->imdb_cache_values['imdbcachedir'] . 'title.tt' . substr( $obj_sanitized, 0, 8 ) );
								if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache movies' names, quicker loading
									$data[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]">' . $title_sanitized . '</label></span>' . "\n"; // send input and results into array
									flush();
								} else { // display every cache movie details, longer loading
									// get either local picture or if no local picture exists, display the default one
									if ( false === $res->photo_localurl() ) {
										$moviepicturelink = 'src="' . esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
									} else {
										$moviepicturelink = 'src="' . $this->imdb_cache_values['imdbphotodir'] . $obj_sanitized . '.jpg" alt="' . $title_sanitized . '"';
									}

									// no flex class so the browser decides how many data to display per lines
									// table so "row-actions" WordPress class works
									$filetime_movie = is_int( filemtime( $filepath_sanitized ) ) === true ? filemtime( $filepath_sanitized ) : 0;
									$data[] = '	<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
							<img id="pic_' . $title_sanitized . '" class="picfloat" ' . $moviepicturelink . ' width="40px">

							<input type="checkbox" id="imdb_cachedeletefor_movies_' . str_replace( ' ', '_', $title_sanitized ) . '" name="imdb_cachedeletefor_movies[]" value="' . $obj_sanitized . '" /><label for="imdb_cachedeletefor_movies[]" class="imdblt_bold">' . $title_sanitized . '</label> <br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_movie ) . ' 
							<div id="refresh_edit_' . $title_sanitized . '" class="row-actions">
								<span class="edit"><a id="deleteindividual_' . $title_sanitized . '" href="' . admin_url() . "admin.php?page=lumiere_options&subsection=cache&cacheoption=manage&dothis=refresh&where=$obj_sanitized&type=movie" . '" class="admin-cache-confirm-refresh" data-confirm="' . esc_html__( 'Refresh cache for *', 'lumiere-movies' ) . $title_sanitized . '*?">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span>

								<span class="delete"><a href="' . esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache&cacheoption=manage&dothis=delete&where=' . $obj_sanitized . '&type=movie' ) . '" class="admin-cache-confirm-delete" data-confirm="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '" title="' . esc_html__( 'Delete *', 'lumiere-movies' ) . $title_sanitized . esc_html__( '* from cache?', 'lumiere-movies' ) . '">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
							</div></td></tr></table>
						</div>';// send input and results into array

								} //end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort']

							}
						}
					}

					// sort alphabetically the data
					asort( $data );

					// print all lines
					foreach ( $data as $inputline ) {
						// @phpcs:ignore WordPress.Security.EscapeOutput
						echo $inputline;
					}
					?>
				</div>
				<br />

				<div class="imdblt_align_center">
					<input type="button" name="CheckAll" value="Check All" data-check-movies="">
					<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-movies="">
				</div>

				<br />
				<br />

				<div class="imdblt_align_center">
					<input type="submit" class="button-primary" name="delete_ticked_cache" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
					<br/>
					<br/>
				</div>

					<?php
				} // end if cache folder is empty
				?>
			</div>
	<br />
	<br />

				<?php //------------------------------------------------------------------------ =[people delete]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachepeople" name="cachepeople"><?php esc_html_e( 'People\'s detailed cache', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

				<?php
				// Scope of the files to be managed
				$files = glob( $this->imdb_cache_values['imdbcachedir'] . '{name.nm*}', GLOB_BRACE );

				// if files don't exist.
				if ( $files === false || ( count( $files ) < 1 ) ) {
					echo '<div class="imdblt_error">' . esc_html__( 'No people\'s cache found.', 'lumiere-movies' ) . '</div>';
				}

				// if files exist.
				if ( is_array( $files ) === true && ( count( $files ) >= 1 ) && ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) ) {
					$results = [];
					foreach ( $files as $file ) {
						if ( preg_match( '!^name\.nm(\d{7,8})$!i', basename( $file ), $match ) === 1 ) {
							$results[] = new Person( $match[1], $this->imdbphp_class, $this->logger->log() );
						}
					}
					?>

	<div class="lumiere_intro_options">
					<?php esc_html_e( 'If you want to refresh people\'s cache regardless the cache expiration time, you may either tick people checkbox(es) related to the person you want to delete and click on "delete cache", or you may click on individual people\'s "refresh". The first way will require an additional people refresh - from you post, for instance.', 'lumiere-movies' ); ?>
		<br />
		<br />
					<?php esc_html_e( 'You may also either delete individually the cache or by group.', 'lumiere-movies' ); ?>
		<br />
		<br />
	</div>

	<div class="lumiere_flex_container">

					<?php
					$datapeople = [];
					if ( $results !== [] ) {
						foreach ( $results as $res ) {
							if ( get_class( $res ) === 'Imdb\Person' ) {
								$name_sanitized = sanitize_text_field( $res->name() ); // search title related to movie id
								$objpiple_sanitized = sanitize_text_field( $res->imdbid() );
								$filepath_sanitized = esc_url( $this->imdb_cache_values['imdbcachedir'] . 'name.nm' . substr( $objpiple_sanitized, 0, 8 ) );
								if ( $this->imdb_cache_values['imdbcachedetailsshort'] === '1' ) { // display only cache peoples' names, quicker loading
									$datapeople[] = '<span class="lumiere_short_titles"><input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people[]">' . $name_sanitized . '</label></span>'; // send input and results into array

								} else { // display every cache people details, longer loading
									// get either local picture or if no local picture exists, display the default one
									if ( false === $res->photo_localurl() ) {
										$picturelink = 'src="' . esc_url( $this->config_class->lumiere_pics_dir . 'no_pics.gif' ) . '" alt="' . esc_html__( 'no picture', 'lumiere-movies' ) . '"';
									} else {
										$picturelink = 'src="' . esc_url( $this->imdb_cache_values['imdbphotodir'] . 'nm' . $objpiple_sanitized . '.jpg' ) . '" alt="' . $name_sanitized . '"';
									}
									$filetime_people = is_int( filemtime( $filepath_sanitized ) ) === true ? filemtime( $filepath_sanitized ) : 0;
									$datapeople[] = '	
						<div class="lumiere_flex_container_content_third lumiere_breakall"><table><tr><td>
							<img id="pic_' . $name_sanitized . '" class="picfloat" ' . $picturelink . ' width="40px" alt="no pic">
							<input type="checkbox" id="imdb_cachedeletefor_people_' . str_replace( ' ', '_', $name_sanitized ) . '" name="imdb_cachedeletefor_people[]" value="' . $objpiple_sanitized . '" /><label for="imdb_cachedeletefor_people_[]" class="imdblt_bold">' . $name_sanitized . '</label><br />' . esc_html__( 'last updated on ', 'lumiere-movies' ) . gmdate( 'j M Y H:i:s', $filetime_people ) . '
							
							<div class="row-actions">
								<span class="view"><a href="' . esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache&cacheoption=manage&dothis=refresh&where=' . $objpiple_sanitized . '&type=people' ) . '" class="admin-cache-confirm-refresh" data-confirm="Refresh cache for *' . $name_sanitized . '*" title="Refresh cache for *' . $name_sanitized . '*">' . esc_html__( 'refresh', 'lumiere-movies' ) . '</a></span> 

								<span class="delete"><a href="' . esc_url( admin_url() . 'admin.php?page=lumiere_options&subsection=cache&cacheoption=manage&dothis=delete&where=' . $objpiple_sanitized . '&type=people' ) . '" class="admin-cache-confirm-delete" data-confirm="You are about to delete *' . $name_sanitized . '* from cache. Click Cancel to stop or OK to continue." title="Delete cache for *' . $name_sanitized . '*">' . esc_html__( 'delete', 'lumiere-movies' ) . '</a></span>
							</div></td></tr></table>
					</div>'; // send input and results into array

									flush();
								} //end quick/long loading $this->imdb_cache_values['imdbcachedetailsshort']

							}
						}
					}

					// sort alphabetically the data
					asort( $datapeople );

					// print all lines
					foreach ( $datapeople as $inputline ) {
						// @phpcs:ignore WordPress.Security.EscapeOutput
						echo $inputline;
					}
					?>
				</div>
				<br />
					<div align="center">
						<input type="button" name="CheckAll" value="Check All" data-check-people="">
						<input type="button" name="UnCheckAll" value="Uncheck All" data-uncheck-people="">
					</div>
					<br />
					<br />

					<div align="center">
						<input type="submit" class="button-primary" data-confirm="<?php esc_html_e( 'Delete selected cache files?', 'lumiere-movies' ); ?>" name="delete_ticked_cache" value="<?php esc_html_e( 'Delete selected files', 'lumiere-movies' ); ?>" />
					</div>
					<br/>
					<br/>

			</div>
					<?php
				} // end if data found

				// End of form for ticked cache to delete
				wp_nonce_field( 'cache_options_check', 'cache_options_check' );
				?>

		</form>
	</div>
	<br />
	<br />

				<?php //------------------------------------------------------------------ =[cache directories]=- ?>

	<div class="inside imblt_border_shadow">
		<h3 class="hndle" id="cachedirectory" name="cachedirectory"><?php esc_html_e( 'Cache directories', 'lumiere-movies' ); ?></h3>
	</div>

	<div class="inside imblt_border_shadow">

		<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>" >

		<div class="titresection lumiere_padding_five"><?php esc_html_e( 'Cache directory (absolute path)', 'lumiere-movies' ); ?></div>

		<div class="lumiere_padding_five">
			<span class="imdblt_smaller">
				<?php
				// display cache folder size
				if ( $imdlt_cache_file_count > 0 ) {

					echo esc_html__( 'Movies\' cache is using', 'lumiere-movies' ) . ' ' . Utils::lumiere_format_bytes( $size_cache_total ) . "\n";

				} else {

					echo esc_html__( 'Movies\' cache is empty.', 'lumiere-movies' );

				}
				?>
			</span>

		</div>
		<div class="imdblt_padding_five">

			<div class="lumiere_breakall">
				<?php echo esc_html( WP_CONTENT_DIR ); ?>
				<input type="text" name="imdbcachedir_partial" class="lumiere_border_width_medium" value="<?php echo esc_attr( $this->imdb_cache_values['imdbcachedir_partial'] ); ?>">
			</div>

			<div class="explain">
				<?php
				if ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) { // check if folder exists
					echo '<span class="imdblt_green">';
					esc_html_e( 'Folder exists.', 'lumiere-movies' );
					echo '</span>';
				} else {
					echo '<span class="imdblt_red">';
					esc_html_e( "Folder doesn't exist!", 'lumiere-movies' );
					echo '</span>';
				}
				if ( is_dir( $this->imdb_cache_values['imdbcachedir'] ) === true ) { // check if permissions are ok
					if ( is_writable( $this->imdb_cache_values['imdbcachedir'] ) ) {
						echo ' <span class="imdblt_green">';
						esc_html_e( 'Permissions OK.', 'lumiere-movies' );
						echo '</span>';
					} else {
						echo ' <span class="imdblt_red">';
						esc_html_e( 'Check folder permissions!', 'lumiere-movies' );
						echo '</span>';
					}
				}
				?>
			</div>

			<div class="explain lumiere_breakall">
				<?php esc_html_e( 'Absolute path to store cache retrieved from the IMDb website. Has to be ', 'lumiere-movies' ); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
				<?php esc_html_e( 'by the webserver.', 'lumiere-movies' ); ?> 
				<br />
				<?php esc_html_e( 'Default:', 'lumiere-movies' ); ?> "<?php echo esc_url( WP_CONTENT_DIR . '/cache/lumiere/' ); ?>"
			</div>
		</div>


		<div>

			<div class="titresection lumiere_padding_five">
				<?php esc_html_e( 'Photo path (relative to the cache path)', 'lumiere-movies' ); ?>
			</div>

			<div class="explain">
				<?php
				// display cache folder size
				$size_cache_pics = $this->lumiere_cache_getfoldersize( $this->imdb_cache_values['imdbphotoroot'] );

				if ( $size_cache_pics > 0 ) {

					esc_html_e( 'Images cache is using', 'lumiere-movies' );
					echo ' ' . Utils::lumiere_format_bytes( $size_cache_pics ) . "\n";
				} else {
					esc_html_e( 'Image cache is empty.', 'lumiere-movies' );
					echo "\n";
				}
				?>
			</div>

			<div class="explain lumiere_breakall">
					<?php esc_html_e( 'Absolute path to store images retrieved from the IMDb website. Has to be ', 'lumiere-movies' ); ?>
				<a href="http://codex.wordpress.org/Changing_File_Permissions" title="permissions how-to on wordpress website">writable</a> 
					<?php esc_html_e( 'by the webserver.', 'lumiere-movies' ); ?>
				<br />
			</div>

			<div class="imdblt_smaller lumiere_breakall">
				<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $this->imdb_cache_values['imdbphotoroot'] ); ?>"
			</div>
			<br />

			<div class="imdblt_smaller">
				<?php
				if ( file_exists( $this->imdb_cache_values['imdbphotoroot'] ) ) { // check if folder exists
					echo '<span class="imdblt_green">';
					esc_html_e( 'Folder exists.', 'lumiere-movies' );
					echo '</span>';
				} else {
					echo '<span class="imdblt_red">';
					esc_html_e( "Folder doesn't exist!", 'lumiere-movies' );
					echo '</span>';
				}
				if ( file_exists( $this->imdb_cache_values['imdbphotoroot'] ) ) { // check if permissions are ok
					if ( is_writable( $this->imdb_cache_values['imdbphotoroot'] ) ) {
						echo ' <span class="imdblt_green">';
						esc_html_e( 'Permissions OK.', 'lumiere-movies' );
						echo '</span>';
					} else {
						echo ' <span class="imdblt_red">';
						esc_html_e( 'Check folder permissions!', 'lumiere-movies' );
						echo '</span>';
					}
				}

				?>

			</div>
		</div>

		<div>
			<div class="titresection imdblt_padding_five">
				<?php esc_html_e( 'Photo URL (relative to the website and the cache path)', 'lumiere-movies' ); ?>
			</div>			

			<div class="explain lumiere_breakall">
				<?php esc_html_e( 'URL corresponding to photo directory.', 'lumiere-movies' ); ?> 
				<br />
				<?php esc_html_e( 'Current:', 'lumiere-movies' ); ?> "<?php echo esc_url( $this->imdb_cache_values['imdbphotodir'] ); ?>"
			</div>

		</div>

	</div>

	<br />
	<br />

	<div class="submit submit-imdb" align="center">

				<?php wp_nonce_field( 'cache_options_check', 'cache_options_check' ); ?>
		<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?>" />
		<input type="submit" class="button-primary" name="update_cache_options" value="<?php esc_html_e( 'Update settings', 'lumiere-movies' ); ?>" />

		</form>
	</div>

		<?php	} else { // end if cache folder exists ?>

		<div class="inside lumiere_border_shadow_red">
				<?php esc_html_e( 'A cache folder has to be created and the cache storage option has to be activated before you can manage the cache.', 'lumiere-movies' ); ?>
			<br /><br />
				<?php esc_html_e( 'Apparently, you have no such cache folder.', 'lumiere-movies' ); ?> 
			<br /><br />
				<?php esc_html_e( 'Click on "reset settings" to refresh the values.', 'lumiere-movies' ); ?>
		</div>

		<div class="submit submit-imdb" align="center">
			<form method="post" name="imdbconfig_save" action="<?php echo esc_url( $_SERVER['REQUEST_URI'] ); ?>">
				<?php
				//check that data has been sent only once
				wp_nonce_field( 'cache_options_check', 'cache_options_check' );
				get_submit_button( esc_html__( 'Reset settings', 'lumiere-movies' ), 'primary large', 'reset_cache_options' );
				?>
				<input type="submit" class="button-primary" name="reset_cache_options" value="<?php esc_html_e( 'Reset settings', 'lumiere-movies' ); ?> " />
			</form>
		</div>
				<?php
		} // end else cache folder exists

		}  //end if $_GET['cacheoption'] == "manage"
		?>
</div>
<br clear="all">
<br />
<br />

<?php	}

	/**
	 * Retrieve all files in cache folder
	 *
	 * @return null|array<int, mixed> Sorted by size list of all files found in Lumière cache folder
	 */
	private function lumiere_cache_list_bysize(): ?array {
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $this->imdb_cache_values['imdbcachedir'], RecursiveDirectoryIterator::SKIP_DOTS )
		);
		$file_line = [];
		foreach ( $folder_iterator as $file ) {
			if ( $file->isDir() === true ) {
				continue;
			}
			$file_date_modif = $file->getMTime();
			$file_name = $file->getPathname();
			$file_size = $file->getSize();
			$file_line[] = [ $file_date_modif, $file_size, $file_name ];
		}
		sort( $file_line );
		return count( $file_line ) > 0 ? $file_line : null;
	}

	/**
	 * Get size of all files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Total size of all files found in given folder
	 */
	private function lumiere_cache_getfoldersize( ?string $folder = null ): int {
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		// After deleting all cache, the display of the cache folder can throw a fatal error if dir is null
		if ( ! is_dir( $final_folder ) ) {
			return 0;
		}
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		$final_size = 0;
		foreach ( $folder_iterator as $file ) {
			if ( $file->isDir() === true ) {
				continue;
			}
			$final_size += $file->getSize();
		}
		return $final_size;
	}

	/**
	 * Count the number of files in given folder (cache lumiere by default )
	 *
	 * @param null|string $folder Folder path, internally changed into cachedir if null
	 * @return int Number of files found in given folder
	 */
	private function lumiere_cache_countfolderfiles( ?string $folder = null ): int {
		$final_folder = $folder ?? $this->imdb_cache_values['imdbcachedir'];
		$folder_iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator( $final_folder, RecursiveDirectoryIterator::SKIP_DOTS )
		);
		return iterator_count( $folder_iterator );
	}

	/**
	 * Retrieve files that are over a given limit
	 * The size is provided in megabits and then internally processed as bits
	 *
	 * @param int $size_limit Limit in megabits ( '100' = 100 MB )
	 * @return null|array<int, string> Array of files paths that exceeds the passed size_limit
	 */
	private function lumiere_cache_find_files_over_limit( int $size_limit ): ?array {
		$arrays = $this->lumiere_cache_list_bysize() ?? [];
		$size_limit_in_bits = $size_limit * 1000000; // convert in bits
		$current_size = 0;
		$list_files_over_size_limit = [];
		foreach ( $arrays as $array ) {
			$current_size += $array[1];
			if ( $current_size >= $size_limit_in_bits ) {
				$list_files_over_size_limit[] = $array[2];
			}
		}
		return count( $list_files_over_size_limit ) > 0 ? $list_files_over_size_limit : null;
	}

	/**
	 * Delete files that are over a given limit
	 * Visibility 'public' because called in cron task in Core class
	 *
	 * @param int $size_limit Limit in megabits
	 * @return void Files exceeding provided limited are deleted
	 */
	public function lumiere_cache_delete_files_over_limit( int $size_limit ): void {
		$this->logger->log()->info( '[Lumiere] Daily Cache cron called with the following value: ' . $size_limit );
		$files = $this->lumiere_cache_find_files_over_limit( $size_limit ) ?? [];
		foreach ( $files as $file ) {
			if ( is_file( $file ) ) {
				unlink( $file );
			}
		}
		$this->logger->log()->info( '[Lumiere] Daily Cache cron deleted the following files: ' . join( $files ) );
	}

	/**
	 * Add WP Cron to delete files that are over a given limit
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_cache_add_cron_deleteoversizedfolder(): void {

		/* Set up WP Cron if it doesn't exist */
		if ( wp_next_scheduled( 'lumiere_cron_deletecacheoversized' ) === false ) {
			// Cron to run Daily, first time in 1 minute
			wp_schedule_event( time() + 60, 'daily', 'lumiere_cron_deletecacheoversized' );
			$this->logger->log()->info( '[Lumiere] Cron lumiere_cron_deletecacheoversized added' );

		}
	}

	/**
	 * Remove WP Cron that delete files that are over a given limit
	 *
	 * @return void Files exceeding provided limited are deleted
	 */
	private function lumiere_cache_remove_cron_deleteoversizedfolder(): void {
		$wp_cron_list = count( _get_cron_array() ) > 0 ? _get_cron_array() : [];
		foreach ( $wp_cron_list as $time => $hook ) {
			if ( isset( $hook['lumiere_cron_deletecacheoversized'] ) ) {
				$timestamp = wp_next_scheduled( 'lumiere_cron_deletecacheoversized' );
				if ( $timestamp !== false ) {
					wp_unschedule_event( $timestamp, 'lumiere_cron_deletecacheoversized' );
					$this->logger->log()->info( '[Lumiere] Cron lumiere_cron_deletecacheoversized removed' );
				}
			}
		}
	}

}


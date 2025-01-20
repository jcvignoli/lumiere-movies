<?php
namespace Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

/**
 * This File is loaded for LOCAL config only (loaded in suite.yml)
 * Constants can be accessed directly in code
 */
define( 'DEVELOPMENT_ENVIR', 'local' );
// WP Post including 1/ an IMDb movie link into the post 2/ A widget IMDb 3/ Inside the post movie
define( 'ADMIN_POST_ID_TESTS', '/wp-admin/post.php?post=4740&action=edit' ); // Different in remote
// For AutoTitle widget post
define( 'ADMIN_POST_AUTOTITLEWIDGET_ID', '/wp-admin/post.php?post=4767&action=edit' ); // Different in remote
// For ban bots and nonce
define( 'BAN_BOTS_MSG', 'Prevented a bad request.' ); // Always English.
define( 'BAN_NONCE_MSG', 'Invalid or missing nonce.' ); // English, usually found.
define( 'BAN_NONCE_MSG_FR', 'Nonce invalide ou manquant.' ); // French, because sometimes it switches to.

/**
 * Methods are available with $I->method_name()
 */
class AcceptanceLocal extends \Codeception\Module
{
	/**
	 * Wheter it local or remote
	 * @var REMOTE_OR_LOCAL either 'local' or 'remote'
	 * @info: can't get at this stage the bootstrap, executed before this class
	 */
	const REMOTE_OR_LOCAL = 'local';

	/**
	 * Stock the base remote URL
	 */
	public string $baseUrl = "";

	/* Stock the root remote path
	 *
	 */
	public string $basePath = "";

	function __construct() {
		// Build properties
		$this->baseUrl = $_ENV[ 'TEST_' . strtoupper( self::REMOTE_OR_LOCAL ) . '_WP_URL' ];
		$this->basePath = $_ENV[ 'WP_ROOT_' . strtoupper( self::REMOTE_OR_LOCAL ) . '_FOLDER' ];
	}
	
	public function getCustomBaseUrl(): string {
		return $this->baseUrl;
	}
	
	public function getCustomBasePath(): string {
		return $this->basePath;
	}
	
	public function getRemoteOrLocal(): string {
		return self::REMOTE_OR_LOCAL;
	}
}

<?php
namespace Tests\Support\Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

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

<?php
// Missing wordpress constants in phpstan
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', 'src/' );
}
if ( ! defined( 'WP_CONTENT_DIR' ) ) {
	define('WP_CONTENT_DIR', 'wp-content' );
}
if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes');
}

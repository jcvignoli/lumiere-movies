<?php

declare(strict_types = 1);

if ( ! class_exists( 'OCEANWP_Theme_Class' ) ) {

	/**
	 * OceanWP theme class
	 */
	final class OCEANWP_Theme_Class {

		/**
		 * Load theme classes
		 *
		 * @since   1.0.0
		 */
		public static function classes(): void {}
		
		/**
		 * Theme Setup
		 *
		 * @since   1.0.0
		 */
		public static function theme_setup(): void {}
		
		/**
		 * Registers sidebars
		 *
		 * @since   1.0.0
		 */
		public static function register_sidebars(): void {}

		/**
		 * Load front-end scripts
		 *
		 * @since   1.0.0
		 */
		public static function theme_css(): void {}
	}
}

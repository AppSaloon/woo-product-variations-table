<?php

namespace woo_pvt\lib;

/**
 * Class Appsaloon_Autoloader
 *
 * @since 1.1.0
 */
class Appsaloon_Autoloader {

	/**
	 * plugin root namespace
	 *
	 * @sice 1.1.0
	 */
	const ROOT_NAMESPACE = 'woo_pvt\\';

	/**
	 * Register autoload method
	 *
	 * @since 1.1.0
	 */
	public function __construct() {
		spl_autoload_register( array( $this, 'appsaloon_autoloader_callback' ) );
	}

	/**
	 * Includes file from the correct namespace
	 * else it will do nothing
	 *
	 * @param $class
	 *
	 * @since 1.1.0
	 */
	public function appsaloon_autoloader_callback($class) {
		if ( strpos( $class, self::ROOT_NAMESPACE ) === 0 ) {
			$path = substr( $class, strlen( self::ROOT_NAMESPACE ) );
			$path = strtolower( $path );
			$path = str_replace( '_', '-', $path );
			$path = str_replace( '\\', DIRECTORY_SEPARATOR, $path ) . '.php';
			$path = WOO_PVT_DIR . DIRECTORY_SEPARATOR . $path;

			if ( file_exists( $path ) ) {
				include $path;
			}
		}
	}
}

/**
 * Start autoloader
 *
 * @since 1.1.0
 */
new Appsaloon_Autoloader();
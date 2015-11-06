<?php

namespace Rmp\Helper;

class Composer {
	/**
	 * @return string
	 */
	private static function getBasePath() {
		return dirname( static::getConfigPath() ) . '/';
	}

	public static function getConfigPath() {
		$dir = realpath( __DIR__ . '/../../../..' ); // skip self

		while ( dirname( $dir ) != $dir ) {
			if ( is_readable( $dir . '/composer.json' ) ) {
				break;
			}

			$dir = dirname( $dir );
		}

		return $dir . '/composer.json';
	}

	public static function getVendorPath() {
		$config = json_decode( file_get_contents( static::getConfigPath() ), true );

		if ( ! isset( $config['config'] ) || ! isset( $config['config']['vendor-dir'] ) ) {
			return 'vendor';
		}

		return dirname( static::getConfigPath() ) . '/' . $config['config']['vendor-dir'];
	}

	public static function load() {
		require_once self::getBasePath() . static::getVendorPath() . '/autoload.php';
	}
}
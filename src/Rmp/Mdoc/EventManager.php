<?php


namespace Rmp\Mdoc;


class EventManager {
	protected static $observer = [ ];

	public static function registerCallable( $event, $callable ) {
		if ( ! isset( static::$observer[ $event ] ) ) {
			static::$observer[ $event ] = [ ];
		}

		static::$observer[ $event ][] = $callable;
	}

	public static function dispatch( $event ) {
		$args = array_slice( func_get_args(), 1 );

		if ( ! isset( static::$observer[ $event ] ) || ! static::$observer[ $event ] ) {
			return;
		}

		foreach ( static::$observer[ $event ] as $callable ) {
			call_user_func_array( $callable, $args );
		}

	}
}
<?php
/**
 * Autoloader for signin-google.
 *
 * @package signin-google
 */

spl_autoload_register(
	function ( $class_name ) {
		static $class_map = array(
			'SigninGoogle\\Authenticator' => __DIR__ . '/src/class-authenticator.php',
			'SigninGoogle\\GoogleClient' => __DIR__ . '/src/class-googleclient.php',
			'SigninGoogle\\Login' => __DIR__ . '/src/class-login.php',
			'SigninGoogle\\Settings' => __DIR__ . '/src/class-settings.php',
		);

		if ( isset( $class_map[ $class_name ] ) ) {
			require $class_map[ $class_name ];
		}
	}
);

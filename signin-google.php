<?php
/**
 * Plugin Name: Sign in with Google
 * Description: Authenticate users with Google.
 * Version: 1.0.0
 * Author: Jonathan Champ, rtCamp
 * Text Domain: signin-google
 * Domain Path: /languages
 * License: GPLv2+
 * Requires at least: 5.5
 * Requires PHP: 7.1
 *
 * @package signin-google
 * @since 1.0.0
 */

declare(strict_types=1);

namespace SigninGoogle;

defined( 'ABSPATH' ) || exit;

require_once __DIR__ . '/autoloader.php';

use InvalidArgumentException;

if ( version_compare( PHP_VERSION, '7.1', '<' ) ) {
	$signin_google_notice = function () {
		printf(
			'<div class="notice notice-error"><span class="notice-title">%1$s</span><p>%2$s</p></div>',
			esc_html__(
				'The Sign in with Google plugin has been deactivated',
				'signin-google'
			),
			esc_html__(
				'The Sign in with Google plugin requires PHP version 7.1 or higher.',
				'signin-google'
			)
		);

		deactivate_plugins( plugin_basename( __FILE__ ) );
	};

	add_action( 'admin_notices', $signin_google_notice );
	add_action( 'network_admin_notices', $signin_google_notice );

	return;
}

/**
 * Get a service object.
 *
 * @param string $service Service needed.
 *
 * @return object
 *
 * @throws InvalidArgumentException Exception for invalid service.
 */
function services( string $service ) {
	static $services = array(
		// Adds settings page and retrieves setting values.
		'settings' => Settings::class,

		// Hooks the login process.
		'login' => Login::class,

		// Provides a Google OAuth client.
		'google_client' => GoogleClient::class,

		// Handles WordPress authentication.
		'authenticator' => Authenticator::class,
	);

	$maybe_object = $services[ $service ] ?? throw new InvalidArgumentException();

	// Initialize objects the first time they are needed.
	if ( ! is_object( $maybe_object ) ) {
		$service_object = new $maybe_object();
		$services[ $service ] = $service_object;
	}

	return $services[ $service ];
}

// Initialize the plugin.
add_action(
	'plugins_loaded',
	function () {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! empty( $_GET['reauth'] ) && ! empty( $_COOKIE[ LOGGED_IN_COOKIE ] ) ) {
			wp_safe_redirect( wp_login_url(), 302, 'Sign in with Google' );
			exit;
		}

		$active_modules = array(
			'settings',
			'login',
		);
		foreach ( $active_modules as $module ) {
			services( $module );
		}
	},
	100
);

add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	/**
	 * Add settings link to plugin actions
	 *
	 * @param  array $actions Plugin actions.
	 * @return array
	 */
	function ( $actions ) {
		$new_actions = array(
			'settings' => sprintf(
				/* translators: %1$s: Setting name, %2$s: URL for settings page link. */
				'<a href="%1$s">%2$s</a>',
				esc_url( admin_url( 'options-general.php?page=signin-google' ) ),
				esc_html__( 'Settings', 'signin-google' )
			),
		);

		return array_merge( $new_actions, $actions );
	}
);

<?php

namespace WP_Rocket\Tests\Integration;

use League\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Event_Management\Event_Manager;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );

/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit
 * tests start.
 *
 * @since 3.5
 */
function load_original_files_before_mocking() {
	$fixtures = [
		'/functions.php',
		'/files.php',
		'/i18n.php',
		'/Abstract_Render.php',
		'/Beacon.php',
	];
	foreach ( $fixtures as $file ) {
		require_once WP_ROCKET_TESTS_FIXTURES_DIR . $file;
	}
}

// Manually load the plugin being tested.
tests_add_filter(
	'muplugins_loaded',
	function() {
		// Set the path and URL to our virtual filesystem.
		define( 'WP_ROCKET_CACHE_ROOT_PATH', 'vfs://public/wp-content/cache/' );
		define( 'WP_ROCKET_CACHE_ROOT_URL', 'http://example.org/wp-content/cache/' );

		load_original_files_before_mocking();

		$container     = new Container();
		$event_manager = new Event_Manager();

		$container->add(
			'options_api',
			function() {
				return new Options( 'wp_rocket_' );
			}
		);

		$container->add( 'options', 'WP_Rocket\Admin\Options_Data' )
			->withArgument( $container->get( 'options_api' )->get( 'settings', [] ) );

		$container->add( 'template_path', WP_ROCKET_PLUGIN_ROOT . 'views' );
		$container->add( 'beacon', 'WP_Rocket\Engine\Admin\Beacon\Beacon' )
			->withArgument( $container->get( 'options' ) )
			->withArgument( $container->get( 'template_path' ) . '/settings' );

		$container->addServiceProvider( 'WP_Rocket\Engine\CDN\RocketCDN\ServiceProvider' );

		$subscribers = [
			'rocketcdn_data_manager_subscriber',
			'rocketcdn_rest_subscriber',
			'rocketcdn_admin_subscriber',
			'rocketcdn_notices_subscriber',
		];

		foreach ( $subscribers as $subscriber ) {
			$event_manager->add_subscriber( $container->get( $subscriber ) );
		}
	}
);

<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Engine\Container\Container;
use WP_Rocket\Admin\Options;
use WP_Rocket\Event_Management\Event_Manager;

define( 'WPMEDIA_MODULE_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_PLUGIN_ROOT', WPMEDIA_MODULE_ROOT );
define( 'WPMEDIA_MODULE_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', WPMEDIA_MODULE_TESTS_FIXTURES_DIR );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

// Manually load the plugin being tested.
tests_add_filter(
	'muplugins_loaded',
	function() {
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

		$container->add( 'template_path', WPMEDIA_MODULE_ROOT . 'views' );
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
		require_once WPMEDIA_MODULE_TESTS_FIXTURES_DIR . $file;
	}
}

load_original_files_before_mocking();

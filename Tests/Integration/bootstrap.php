<?php

namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\BootstrapManager;
use function Patchwork\redefine;

define( 'WP_ROCKET_PLUGIN_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_DIR', __DIR__ );
define( 'WP_ROCKET_IS_TESTING', true );

/**
 * The original files need to loaded into memory before we mock them with Patchwork. Add files here before the unit
 * tests start.
 *
 * @since 3.5
 */
function load_original_files_before_mocking() {
	$fixtures = [
		'/functions.php',
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
	}
);

<?php

namespace WP_Rocket\Tests\Unit;

define( 'WPMEDIA_MODULE_ROOT', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WP_ROCKET_PLUGIN_ROOT', WPMEDIA_MODULE_ROOT );
define( 'WPMEDIA_MODULE_TESTS_FIXTURES_DIR', dirname( __DIR__ ) . '/Fixtures' );
define( 'WP_ROCKET_TESTS_FIXTURES_DIR', WPMEDIA_MODULE_TESTS_FIXTURES_DIR );
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
		'/Abstract_Render.php',
	];
	foreach ( $fixtures as $file ) {
		require_once WPMEDIA_MODULE_TESTS_FIXTURES_DIR . $file;
	}
}

load_original_files_before_mocking();

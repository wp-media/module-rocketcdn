<?php
/**
 * Initializes the wp-media/phpunit-wp-rocket handler, which then calls the rocket Unit test suite.
 */

define( 'WPMEDIA_PHPUNIT_ROOT_DIR', dirname( dirname( __DIR__ ) ) . DIRECTORY_SEPARATOR );
define( 'WPMEDIA_PHPUNIT_ROOT_TEST_DIR', __DIR__ );

require_once WPMEDIA_PHPUNIT_ROOT_DIR . 'vendor/wp-media/phpunit-wp-rocket/Unit/init-tests.php';

<?php

namespace WPMedia\RocketCDN\Tests\Integration\APIClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WPMedia\RocketCDN\Tests\Integration\TestCase;
use WPMedia\RocketCDN\APIClient;

/**
 * @covers \WPMedia\RocketCDN\APIClient::purge_cache_request
 * @uses   \WPMedia\RocketCDN\APIClient::get_subscription_data
 *
 * @group  APIClient
 */
class Test_PurgeCacheRequest extends TestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function tearDown() {
		parent::tearDown();

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $transient, $option, $expected, $success = false ) {
		if ( $success ) {
			$transient = [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ];
			$option    = self::getApiCredential( 'ROCKETCDN_TOKEN' );
		}
		set_transient( 'rocketcdn_status', $transient, MINUTE_IN_SECONDS );
		if ( ! empty( $option ) ) {
			update_option( 'rocketcdn_user_token', $option );
		}

		$this->assertSame(
			$expected,
			( new APIClient )->purge_cache_request()
		);
	}
}

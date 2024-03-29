<?php

namespace WP_Rocket\Tests\Integration\APIClient;

use WPMedia\PHPUnit\Integration\ApiTrait;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Tests\Integration\RocketCdnTestCase;


/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::purge_cache_request
 * @uses   \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_subscription_data
 *
 * @group  APIClient
 */
class Test_PurgeCacheRequest extends RocketCdnTestCase {
	use ApiTrait;

	protected static $api_credentials_config_file = 'rocketcdn.php';
	private $response;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public function setUp() {
		parent::setUp();
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	public function tearDown() {
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );
		remove_filter( 'pre_http_request', [ $this, 'set_response' ] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $transient, $option, $response, $expected, $success = false ) {
		$this->response = $response;

		add_filter( 'pre_http_request', [ $this, 'set_response' ] );

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

	public function set_response() {
		return $this->response;
	}
}

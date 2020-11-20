<?php

namespace WP_Rocket\Tests\Unit\DataManagerSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::validate_token_cname
 *
 * @group  DataManager
 */
class Test_ValidateTokenCname extends TestCase {
	private $data_manager;
	private $cdn_options_manager;

	public function setUp() {
		parent::setUp();

		$this->cdn_options_manager = Mockery::mock( CDNOptionsManager::class );
		$this->data_manager        = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			$this->cdn_options_manager
		);

		Functions\when( 'check_ajax_referer' )->justReturn( true );
	}

	public function testShouldSendErrorWhenNoPermissions() {
		Functions\when( 'current_user_can' )->justReturn( false );
		Functions\expect( 'wp_send_json_error' )->once()->with( [ 'message' => 'unauthorized_user' ] );

		$this->data_manager->validate_token_cname();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )->justReturn( true );

		$_POST['cdn_url']   = $config[ 'cdn_url' ];
		$_POST['cdn_token'] = $config[ 'cdn_token' ];

		if ( $expected[ 'empty' ] || $expected[ 'not_valid' ] ) {
			Functions\expect( 'wp_send_json_error' )->once()->with( $expected[ 'data' ] );
		}

		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				$key = strtolower( $key );
				return preg_replace( '/[^a-z0-9_\-]/', '', $key );
			}
		);

		Functions\when( 'wp_unslash' )->returnArg( );

		if ( isset( $expected[ 'get_option' ] ) ) {
			Functions\expect( 'get_option' )
				->once()
				->with( 'rocketcdn_user_token' )
				->andReturn( $config[ 'current_token' ] );

			$this->cdn_options_manager
				->shouldReceive( 'get_cdn_cnames' )
				->once()
				->andReturn( $config[ 'current_cname' ] );
		}

		if ( isset( $expected[ 'success' ] ) ) {
			Functions\expect( 'update_option' )
				->once()
				->with( 'rocketcdn_user_token', $config[ 'cdn_token' ] );

			Functions\expect( 'esc_url_raw' )->once()->with( $config[ 'cdn_url' ] )->andReturnFirstArg();

			$this->cdn_options_manager
				->shouldReceive( 'enable' )
				->once()
				->with( $config[ 'cdn_url' ] );

			Functions\expect( 'wp_send_json_success' )->once()->with( $expected[ 'data' ] );
		}

		$this->data_manager->validate_token_cname();
	}
}

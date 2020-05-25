<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WPMedia\RocketCDN\APIClient;
use WPMedia\RocketCDN\CDNOptionsManager;
use WPMedia\RocketCDN\DataManagerSubscriber;
use Mockery;

/**
 * @covers \WPMedia\RocketCDN\DataManagerSubscriber::get_process_status
 * @group  RocketCDN
 */
class Test_GetProcessStatus extends TestCase {
	private $data_manager;

	public function setUp() {
		parent::setUp();

		$this->data_manager = new DataManagerSubscriber(
			Mockery::mock( APIClient::class ),
			Mockery::mock( CDNOptionsManager::class )
		);
	}

	public function testShouldSendErrorWhenNoCapacity() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( false );

		Functions\expect( 'wp_send_json_error' )->once();

		$this->data_manager->get_process_status();
	}

	public function testShouldSendSuccessWhenOptionExists() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( true );

		Functions\expect( 'wp_send_json_success' )->once();

		$this->data_manager->get_process_status();
	}

	public function testShouldSendErrorWhenOptionNotExists() {
		Functions\when( 'check_ajax_referer' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( false );

		Functions\expect( 'wp_send_json_error' )->once();

		$this->data_manager->get_process_status();
	}
}

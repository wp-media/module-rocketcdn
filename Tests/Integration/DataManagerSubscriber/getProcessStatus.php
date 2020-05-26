<?php

namespace WPMedia\RocketCDN\Tests\Integration\DataManagerSubscriber;

/**
 * @covers \WPMedia\RocketCDN\DataManagerSubscriber::get_process_status
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 */
class Test_GetProcessStatus extends AjaxTestCase {
	protected static $ajax_action = 'rocketcdn_process_status';

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_rocketcdn_process_status', 'get_process_status' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendSResponse( $set_option, $expected ) {
		if ( $set_option ) {
			add_option( 'rocketcdn_process', true );
		}

		// Run it.
		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertSame( $expected['response']->success, $response->success );
	}
}

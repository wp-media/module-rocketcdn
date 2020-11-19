<?php

namespace WP_Rocket\Tests\Integration\DataManagerSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::validate_token_cname
 *
 * @group  AdminOnly
 * @group  DataManagerSubscriber
 */
class Test_ValidateTokenCname extends AjaxTestCase {
	protected static $ajax_action = 'rocketcdn_validate_token_cname';

	public function testCallbackIsRegistered() {
		$this->assertCallbackRegistered( 'wp_ajax_rocketcdn_validate_token_cname', 'validate_token_cname' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSendExpectedResponse( $config, $expected ) {
		$_POST['cdn_url']   = $config[ 'cdn_url' ];
		$_POST['cdn_token'] = $config[ 'cdn_token' ];

		if ( isset( $expected[ 'get_option' ] ) ) {
			add_option( 'rocketcdn_user_token', $config['current_token'] );
		}

		$response = $this->callAjaxAction();

		// Check the response.
		$this->assertEquals( (object) $expected['data'], $response->data );

		if ( isset( $expected[ 'success' ] ) ) {
			$this->assertSame(
				$config['cdn_token'],
				get_option( 'rocketcdn_user_token' )
			);
		}
	}
}

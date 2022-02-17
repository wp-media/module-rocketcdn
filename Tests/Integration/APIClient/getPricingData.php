<?php

namespace WP_Rocket\Tests\Integration\APIClient;

use WP_Rocket\Tests\Integration\ApiTestCase;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\APIClient::get_pricing_data
 *
 * @group  APIClient
 */
class Test_GetPricingData extends ApiTestCase {

	public function tearDown() {
		delete_transient( 'rocketcdn_pricing' );
		remove_filter( 'pre_http_request', [ $this, 'shortcut_request'] );

		parent::tearDown();
	}

	/**
	 * Test should return the pricing when set in the transient.
	 */
	public function testShouldReturnPricingWhenInTransient() {
		$status = [
			"is_discount_active"       => true,
			"discounted_price_monthly" => 5.99,
			"discounted_price_yearly"  => 59.0,
			"discount_campaign_name"   => "Launch",
			"end_date"                 => "2020-01-30",
			"monthly_price"            => 7.99,
			"annual_price"             => 79.0
		];
		set_transient( 'rocketcdn_pricing', $status, MINUTE_IN_SECONDS );

		$this->assertSame( $status, ( new APIClient )->get_pricing_data() );
	}

	/**
	 * Test should return the pricing and set the transient when successfully received from the API.
	 */
	public function testShouldReturnPricingAndSetTransientWhenReceivedFromAPI() {
		$this->assertFalse( get_transient( 'rocketcdn_pricing' ) );

		add_filter( 'pre_http_request', [ $this, 'shortcut_request'] );
		// Run it.
		$actual = ( new APIClient )->get_pricing_data();

		$this->assertArrayHasKey( 'is_discount_active', $actual );
		$this->assertArrayHasKey( 'discounted_price_monthly', $actual );
		$this->assertArrayHasKey( 'discounted_price_yearly', $actual );
		$this->assertArrayHasKey( 'discount_campaign_name', $actual );
		$this->assertArrayHasKey( 'end_date', $actual );
		$this->assertArrayHasKey( 'monthly_price', $actual );
		$this->assertArrayHasKey( 'annual_price', $actual );
		$this->assertSame( $actual, get_transient( 'rocketcdn_pricing' ) );
	}

	public function shortcut_request() {
		return [
			'headers' => [],
			'body' => json_encode(
				[
					'is_discount_active' => false,
					'discounted_price_monthly' => '6.99',
					'discounted_price_yearly' => '69.9',
					'discount_campaign_name' => 'halloween',
					'end_date' => time(),
					'monthly_price' => '7.99',
					'annual_price' => '79.9',
				],
			),
			'response' => [
				'code' => 200,
			],
			'cookies' => [],
			'filename' => '',
		];
	}
}

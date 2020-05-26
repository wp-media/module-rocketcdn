<?php

namespace WPMedia\RocketCDN\Tests\Integration\AdminPageSubscriber;

use WPMedia\RocketCDN\Tests\Integration\TestCase;

/**
 * @covers \WPMedia\RocketCDN\AdminPageSubscriber::add_subscription_modal
 * @uses   ::rocket_is_live_site
 * @uses   ::rocket_get_constant
 *
 * @group  AdminOnly
 * @group  AdminPage
 */
class Test_AddSubscriptionModal extends TestCase {

	public function setUp() {
		parent::setUp();

		add_filter( 'home_url', [ $this, 'home_url_cb' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayExpected( $config, $expected ) {
		$this->white_label = isset( $config['white_label'] ) ? $config['white_label'] : $this->white_label;
		$this->home_url = $config['home_url'];

		ob_start();
		do_action( 'rocket_settings_page_footer' );
		$actual = ob_get_clean();

		if ( ! empty ( $expected ) ) {
			$expected = $this->format_the_html( $expected );
		}

		if ( ! empty ( $actual ) ) {
			$actual = $this->format_the_html( $actual );
		}

		$this->assertSame( $expected, $actual );
	}
}
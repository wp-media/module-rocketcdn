<?php

namespace WPMedia\RocketCDN\Tests\Unit\AdminPageSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WPMedia\RocketCDN\APIClient;
use WPMedia\RocketCDN\AdminPageSubscriber;
use WPMedia\RocketCDN\Tests\StubTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

/**
 * @covers \WPMedia\RocketCDN\AdminPageSubscriber::display_manage_subscription
 *
 * @group  AdminPage
 */
class Test_DisplayManageSubscription extends TestCase {
	use StubTrait;

	protected static $mockCommonWpFunctionsInSetUp = true;
	private $api_client;
	private $page;

	public function setUp() {
		parent::setUp();

		$this->stubRocketGetConstant();

		$this->api_client = Mockery::mock( APIClient::class );
		$this->page       = new AdminPageSubscriber(
			$this->api_client,
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			'views/settings/rocketcdn'
		);
	}

	public function tearDown() {
		$this->resetStubProperties();

		parent::tearDown();
	}

	public function testShouldDisplayNothingWhenWhiteLabel() {
		$this->white_label = true;

		$this->assertNull( $this->page->display_manage_subscription() );
	}

	public function testShouldDisplayNothingWhenNotLiveSite() {
		Functions\when( 'rocket_is_live_site' )->justReturn( false );

		$this->assertNull( $this->page->display_manage_subscription() );
	}

	public function testShouldNotRenderButtonHTMLWhenSubscriptionInactive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		$this->api_client->shouldReceive( 'get_subscription_data' )
			 ->once()
			 ->andReturn( [ 'subscription_status' => 'cancelled' ] );

		$this->assertEmpty( $this->getActualHtml() );
	}

	public function testShouldRenderButtonHTMLWhenSubscriptionActive() {
		Functions\when( 'rocket_is_live_site' )->justReturn( true );

		$this->api_client->shouldReceive( 'get_subscription_data' )
			 ->once()
			 ->andReturn( [ 'subscription_status' => 'running' ] );

		$expected = <<<HTML
<p class="wpr-rocketcdn-subscription">
	<button class="wpr-rocketcdn-open" data-micromodal-trigger="wpr-rocketcdn-modal">Manage Subscription</button>
</p>
HTML;

		$this->assertSame( $this->format_the_html( $expected ), $this->getActualHtml() );
	}

	private function getActualHtml() {
		ob_start();
		$this->page->display_manage_subscription();
		return $this->format_the_html( ob_get_clean() );
	}
}
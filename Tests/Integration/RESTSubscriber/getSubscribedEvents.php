<?php

namespace WP_Rocket\Tests\Integration\RESTSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::get_subscribed_events
 *
 * @group RESTSubscriber
 */
class Test_GetSubscribedEvents extends TestCase {

	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'rest_api_init' => [
				[ 'register_enable_route' ],
				[ 'register_disable_route' ],
			],
		];

		$this->assertSame(
			$events,
			RESTSubscriber::get_subscribed_events()
		);
	}
}

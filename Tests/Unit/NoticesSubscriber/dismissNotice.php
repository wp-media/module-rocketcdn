<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Unit\TestCase;
use WPMedia\RocketCDN\NoticesSubscriber;
use Brain\Monkey\Functions;
use Mockery;

/**
 * @covers \WPMedia\RocketCDN\NoticesSubscriber::dismiss_notice
 * @group RocketCDN
 */
class Test_DismissNotice extends TestCase {
	public function testShouldReturnNullWhenNoCapacity() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( false );

		$_POST['action'] = 'rocketcdn_dismiss_notice';

		$notices = new NoticesSubscriber( Mockery::mock( 'WPMedia\RocketCDN\APIClient' ), 'views/settings/rocketcdn');
		$this->assertNull( $notices->dismiss_notice() );
	}

	public function testShouldUpdateUserMetaWhenValid() {
		Functions\when('check_ajax_referer')->justReturn(true);
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when('get_current_user_id')->justReturn(1);
		Functions\expect('update_user_meta')
			->once()
			->with( 1, 'rocketcdn_dismiss_notice', true );

		$_POST['action'] = 'rocketcdn_dismiss_notice';

		$notices = new NoticesSubscriber( Mockery::mock( 'WPMedia\RocketCDN\APIClient' ), 'views/settings/rocketcdn');
		$notices->dismiss_notice();
	}
}

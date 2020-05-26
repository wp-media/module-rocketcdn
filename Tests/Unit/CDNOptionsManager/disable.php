<?php

namespace WPMedia\RocketCDN\Tests\Unit\CDNOptionsManager;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WPMedia\RocketCDN\CDNOptionsManager;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WPMedia\RocketCDN\CDNOptionsManager::disable
 *
 * @group  CDNOptionsManager
 */
class Test_Disable extends TestCase {
	public function testShouldDisableCDNOptions() {
		$expected = [
			'cdn'        => 0,
			'cdn_cnames' => [],
			'cdn_zone'   => [],
		];

		Functions\expect( 'delete_option' )
			->once()
			->with( 'rocketcdn_user_token' );
		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocketcdn_status' );

		$options_array = Mockery::mock( Options_Data::class );
		foreach ($expected as $option_key => $option_value) {
			$options_array->shouldReceive( 'set' )
				->once()
				->with( $option_key, $option_value )
				->andReturn();
		}

		$options_array->shouldReceive( 'get_options' )
		              ->andReturn( $expected );

		$options = Mockery::mock( Options::class );
		$options->shouldReceive( 'set' )
				->once()
		        ->with( 'settings', $expected );

		Functions\expect( 'rocket_clean_domain' )->once();

		( new CDNOptionsManager(
			$options,
			$options_array
		) )->disable();
	}
}

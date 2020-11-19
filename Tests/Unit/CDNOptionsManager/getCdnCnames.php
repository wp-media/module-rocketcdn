<?php

namespace WP_Rocket\Tests\Unit\CDNOptionsManager;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager::get_cdn_cnames
 *
 * @group  CDNOptionsManager
 */
class Test_GetCdnCnames extends TestCase {
	public function testShouldGetCdnCnames() {
		$expected = [
			'cdn_cnames' => [
				'https://rocketcdn.me',
			],
		];

		$options       = Mockery::mock( Options::class );
		$options_array = Mockery::mock( Options_Data::class );

		$options_array->shouldReceive( 'get' )
				->once()
				->with( 'cdn_cnames', [] )
				->andReturn( $expected[ 'cdn_cnames' ] );

		$cdn_cnames = ( new CDNOptionsManager(
			$options,
			$options_array
		) )->get_cdn_cnames();

		$this->assertEquals( $expected[ 'cdn_cnames' ], $cdn_cnames );
	}
}

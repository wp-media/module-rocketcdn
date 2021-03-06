<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rocket\Tests\Integration\TestCase as BaseTestCase;

abstract class RocketCdnTestCase extends BaseTestCase {
	protected $cdn_names;
	protected $home_url = 'http://myexample.org';

	protected static $transients = [
		'rocketcdn_status' => null,
	];

	public static function setUpBeforeClass() {
		static::$use_settings_trait = true;
		parent::setUpBeforeClass();
	}

	public function setUp() {
		parent::setUp();

		set_current_screen( 'settings_page_wprocket' );
	}

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );
		set_current_screen( 'front' );
	}

	public function home_url_cb() {
		return $this->home_url;
	}

	public function cdn_names_cb() {
		return $this->cdn_names;
	}

	public function return_empty_string() {
		return '';
	}
}

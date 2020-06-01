<?php

defined( 'ABSPATH' ) || exit;

/**
 * Tell if a translation plugin is activated.
 *
 * @since 2.0
 * @since 3.2.1 Return an identifier on success instead of true.
 *
 * @return string|bool An identifier corresponding to the active plugin. False otherwize.
 */
function rocket_has_i18n() {
	global $sitepress, $q_config, $polylang;

	if ( ! empty( $sitepress ) && is_object( $sitepress ) && method_exists( $sitepress, 'get_active_languages' ) ) {
		// WPML.
		return 'wpml';
	}

	if ( ! empty( $polylang ) && function_exists( 'pll_languages_list' ) ) {
		$languages = pll_languages_list();

		if ( empty( $languages ) ) {
			return false;
		}

		// Polylang, Polylang Pro.
		return 'polylang';
	}

	if ( ! empty( $q_config ) && is_array( $q_config ) ) {
		if ( function_exists( 'qtranxf_convertURL' ) ) {
			// qTranslate-x.
			return 'qtranslate-x';
		}

		if ( function_exists( 'qtrans_convertURL' ) ) {
			// qTranslate.
			return 'qtranslate';
		}
	}

	return false;
}

/**
 * Get infos of all active languages.
 *
 * @since 2.0
 *
 * @return array A list of language codes.
 */
function get_rocket_i18n_code() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return false;
	}

	if ( 'wpml' === $i18n_plugin ) {
		// WPML.
		return array_keys( $GLOBALS['sitepress']->get_active_languages() );
	}

	if ( 'qtranslate' === $i18n_plugin || 'qtranslate-x' === $i18n_plugin ) {
		// qTranslate, qTranslate-x.
		return ! empty( $GLOBALS['q_config']['enabled_languages'] ) ? $GLOBALS['q_config']['enabled_languages'] : [];
	}

	if ( 'polylang' === $i18n_plugin ) {
		// Polylang, Polylang Pro.
		return pll_languages_list();
	}

	return false;
}

/**
 * Get all active languages URI.
 *
 * @since 2.0
 *
 * @return array $urls List of all active languages URI.
 */
function get_rocket_i18n_uri() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();
	$urls        = [];

	if ( 'wpml' === $i18n_plugin ) {
		// WPML.
		foreach ( get_rocket_i18n_code() as $lang ) {
			$urls[] = $GLOBALS['sitepress']->language_url( $lang );
		}
	} elseif ( 'qtranslate' === $i18n_plugin || 'qtranslate-x' === $i18n_plugin ) {
		// qTranslate, qTranslate-x.
		foreach ( get_rocket_i18n_code() as $lang ) {
			if ( 'qtranslate' === $i18n_plugin ) {
				$urls[] = qtrans_convertURL( home_url(), $lang, true );
			} else {
				$urls[] = qtranxf_convertURL( home_url(), $lang, true );
			}
		}
	} elseif ( 'polylang' === $i18n_plugin ) {
		// Polylang, Polylang Pro.
		$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

		if ( ! empty( $pll ) && is_object( $pll ) ) {
			$urls = wp_list_pluck( $pll->model->get_languages_list(), 'search_url' );
		}
	}

	if ( empty( $urls ) ) {
		$urls[] = home_url();
	}

	return $urls;
}

/**
 * Get directories paths to preserve languages ​​when purging a domain.
 * This function is required when the domains of languages (​​other than the default) are managed by subdirectories.
 * By default, when you clear the cache of the french website with the domain example.com, all subdirectory like /en/
 * and /de/ are deleted. But, if you have a domain for your english and german websites with example.com/en/ and
 * example.com/de/, you want to keep the /en/ and /de/ directory when the french domain is cleared.
 *
 * @since 3.5.5 Normalize paths + micro-optimization by passing in the cache path.
 * @since 2.0
 *
 * @param string $current_lang The current language code.
 * @param string $cache_path   Optional. WP Rocket's cache path.
 *
 * @return array                A list of directories path to preserve.
 */
function get_rocket_i18n_to_preserve( $current_lang, $cache_path = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	// Must not be an empty string.
	if ( empty( $current_lang ) ) {
		return [];
	}

	// Must not be anything else but a string.
	if ( ! is_string( $current_lang ) ) {
		return [];
	}

	$i18n_plugin = rocket_has_i18n();
	if ( ! $i18n_plugin ) {
		return [];
	}

	$langs = get_rocket_i18n_code();
	if ( empty( $langs ) ) {
		return [];
	}

	// Remove current lang to the preserve dirs.
	$langs = array_diff( $langs, [ $current_lang ] );

	if ( '' === $cache_path ) {
		$cache_path = _rocket_get_wp_rocket_cache_path();
	}

	// Stock all URLs of langs to preserve.
	$langs_to_preserve = [];
	foreach ( $langs as $lang ) {
		$parse_url           = get_rocket_parse_url( get_rocket_i18n_home_url( $lang ) );
		$langs_to_preserve[] = _rocket_normalize_path(
			"{$cache_path}{$parse_url['host']}(.*)/" . trim( $parse_url['path'], '/' ),
			true // escape directory separators for regex.
		);
	}

	/**
	 * Filter directories path to preserve of cache purge.
	 *
	 * @since 2.1
	 *
	 * @param array $langs_to_preserve List of directories path to preserve.
	 */
	return (array) apply_filters( 'rocket_langs_to_preserve', $langs_to_preserve );
}

/**
 * Get home URL of a specific lang.
 *
 * @since 2.2
 *
 * @param  string $lang The language code. Default is an empty string.
 * @return string $url
 */
function get_rocket_i18n_home_url( $lang = '' ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	$i18n_plugin = rocket_has_i18n();

	if ( ! $i18n_plugin ) {
		return home_url();
	}

	switch ( $i18n_plugin ) {
		// WPML.
		case 'wpml':
			return $GLOBALS['sitepress']->language_url( $lang );
		// qTranslate.
		case 'qtranslate':
			return qtrans_convertURL( home_url(), $lang, true );
		// qTranslate-x.
		case 'qtranslate-x':
			return qtranxf_convertURL( home_url(), $lang, true );
		// Polylang, Polylang Pro.
		case 'polylang':
			$pll = function_exists( 'PLL' ) ? PLL() : $GLOBALS['polylang'];

			if ( ! empty( $pll->options['force_lang'] ) && isset( $pll->links ) ) {
				return pll_home_url( $lang );
			}
	}

	return home_url();
}

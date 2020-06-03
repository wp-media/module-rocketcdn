<?php

if ( ! function_exists( 'rocket_clean_domain' ) ) {
	/**
	 * Remove all cache files for the domain.
	 *
	 * @since 3.5.5 Optimizes by grabbing root cache dirs once, bailing out when file/dir doesn't exist, & directly
	 *        deleting files.
	 * @since 3.5.3 Replaces glob with SPL.
	 * @since 2.0   Delete domain cache files for all users
	 * @since 1.0
	 *
	 * @param string                    $lang       Optional. The language code. Default: empty string.
	 * @param WP_Filesystem_Direct|null $filesystem Optional. Instance of filesystem handler.
	 */
	function rocket_clean_domain( $lang = '', $filesystem = null ) {
		$urls = ( ! $lang || is_object( $lang ) || is_array( $lang ) || is_int( $lang ) )
			? (array) get_rocket_i18n_uri()
			: (array) get_rocket_i18n_home_url( $lang );

		/**
		 * Filter URLs to delete all caching files from a domain.
		 *
		 * @since 2.6.4
		 *
		 * @param array     URLs that will be returned.
		 * @param string    The language code.
		 */
		$urls = (array) apply_filters( 'rocket_clean_domain_urls', $urls, $lang );
		$urls = array_filter( $urls );
		if ( empty( $urls ) ) {
			return;
		}

		/** This filter is documented in inc/front/htaccess.php */
		$url_no_dots      = (bool) apply_filters( 'rocket_url_no_dots', false );
		$cache_path       = _rocket_get_wp_rocket_cache_path();
		$dirs_to_preserve = get_rocket_i18n_to_preserve( $lang, $cache_path );

		if ( empty( $filesystem ) ) {
			$filesystem = rocket_direct_filesystem();
		}

		foreach ( $urls as $url ) {
			$parsed_url = get_rocket_parse_url( $url );

			if ( $url_no_dots ) {
				$parsed_url['host'] = str_replace( '.', '_', $parsed_url['host'] );
			}

			$root = $cache_path . $parsed_url['host'] . $parsed_url['path'];

			/**
			 * Fires before all cache files are deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $root The path of home cache file.
			 * @param string $lang The current lang to purge.
			 * @param string $url  The home url.
			 */
			do_action( 'before_rocket_clean_domain', $root, $lang, $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

			foreach ( _rocket_get_cache_dirs( $parsed_url['host'], $cache_path ) as $dir ) {
				$entry = $dir . $parsed_url['path'];
				// Skip if the dir/file does not exist.
				if ( ! $filesystem->exists( $entry ) ) {
					continue;
				}

				if ( $filesystem->is_dir( $entry ) ) {
					rocket_rrmdir( $entry, $dirs_to_preserve, $filesystem );
				} else {
					$filesystem->delete( $entry );
				}
			}

			/**
			 * Fires after all cache files was deleted.
			 *
			 * @since 1.0
			 *
			 * @param string $root The path of home cache file.
			 * @param string $lang The current lang to purge.
			 * @param string $url  The home url.
			 */
			do_action( 'after_rocket_clean_domain', $root, $lang, $url ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		}
	}
}

if ( ! function_exists( 'rocket_rrmdir' ) ) {
	/**
	 * Remove a single file or a folder recursively.
	 *
	 * @since 3.5.3 Replaces glob and optimizes.
	 * @since 1.0
	 * @since 3.5.3 Bails if given dir should be preserved; replaces glob; optimizes.
	 *
	 * @param string                    $dir              File/Directory to delete.
	 * @param array                     $dirs_to_preserve Optional. Dirs that should not be deleted.
	 * @param WP_Filesystem_Direct|null $filesystem       Optional. Instance of filesystem handler.
	 */
	function rocket_rrmdir( $dir, array $dirs_to_preserve = [], $filesystem = null ) {
		$dir = untrailingslashit( $dir );

		if ( empty( $filesystem ) ) {
			$filesystem = rocket_direct_filesystem();
		}

		/**
		 * Fires before a file/directory cache is deleted
		 *
		 * @since 1.1.0
		 *
		 * @param string $dir              File/Directory to delete.
		 * @param array  $dirs_to_preserve Directories that should not be deleted.
		 */
		do_action( 'before_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

		// Remove the hidden empty file for mobile detection on NGINX with the Rocket NGINX configuration.
		$nginx_mobile_detect_file = $dir . '/.mobile-active';

		if ( $filesystem->is_dir( $dir ) && $filesystem->exists( $nginx_mobile_detect_file ) ) {
			$filesystem->delete( $nginx_mobile_detect_file );
		}

		// Remove the hidden empty file for webp.
		$nowebp_detect_file = $dir . '/.no-webp';

		if ( $filesystem->is_dir( $dir ) && $filesystem->exists( $nowebp_detect_file ) ) {
			$filesystem->delete( $nowebp_detect_file );
		}

		if ( ! $filesystem->is_dir( $dir ) ) {
			$filesystem->delete( $dir );

			return;
		}

		// Get the directory entries.
		$entries = [];
		try {
			foreach ( new FilesystemIterator( $dir ) as $entry ) {
				$entries[] = $entry->getPathname();
			}
		} catch ( Exception $e ) { // phpcs:disable Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			// No action required, as logging not enabled.
		}

		// Exclude directories to preserve from the entries.
		if ( ! empty( $dirs_to_preserve ) && ! empty( $entries ) ) {
			$keys = [];
			foreach ( $dirs_to_preserve as $dir_to_preserve ) {
				$matches = preg_grep( "#^$dir_to_preserve$#", $entries );
				$keys[]  = reset( $matches );
			}

			if ( ! empty( $keys ) ) {
				$keys = array_filter( $keys );
				if ( ! empty( $keys ) ) {
					$entries = array_diff( $entries, $keys );
				}
			}
		}

		foreach ( $entries as $entry ) {
			// If not a directory, delete it.
			if ( ! $filesystem->is_dir( $entry ) ) {
				$filesystem->delete( $entry );
			} else {
				rocket_rrmdir( $entry, $dirs_to_preserve, $filesystem );
			}
		}

		$filesystem->delete( $dir );

		/**
		 * Fires after a file/directory cache was deleted
		 *
		 * @since 1.1.0
		 *
		 * @param string $dir              File/Directory to delete.
		 * @param array  $dirs_to_preserve Dirs that should not be deleted.
		 */
		do_action( 'after_rocket_rrmdir', $dir, $dirs_to_preserve ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
	}
}

if ( ! function_exists( 'rocket_direct_filesystem' ) ) {
	/**
	 * Instanciate the filesystem class
	 *
	 * @since 2.10
	 *
	 * @return object WP_Filesystem_Direct instance
	 */
	function rocket_direct_filesystem() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		return new WP_Filesystem_Direct( new StdClass() );
	}
}

if ( ! function_exists( '_rocket_get_cache_path_iterator' ) ) {
	/**
	 * Get the recursive iterator for the cache path.
	 *
	 * @since  3.5.4
	 * @access private
	 *
	 * @param string $cache_path Path to the cache directory.
	 *
	 * @return bool|RecursiveIteratorIterator Iterator on success; else false;
	 */
	function _rocket_get_cache_path_iterator( $cache_path ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		try {
			return new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator( $cache_path ),
				RecursiveIteratorIterator::SELF_FIRST,
				RecursiveIteratorIterator::CATCH_GET_CHILD
			);
		} catch ( Exception $e ) {
			// No logging yet.
			return false;
		}
	}
}

if ( ! function_exists( '_rocket_get_cache_dirs' ) ) {
	/**
	 * Gets the directories for the given URL host from the cache/wp-rocket/ directory or stored memory.
	 *
	 * @since  3.5.5
	 * @access private
	 *
	 * @param string $url_host   The URL's host.
	 * @param string $cache_path Cache's path, e.g. cache/wp-rocket/.
	 * @param bool   $hard_reset Optional. When true, resets the static domain directories array and bails out.
	 *
	 * @return array
	 */
	function _rocket_get_cache_dirs( $url_host, $cache_path = '', $hard_reset = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		static $domain_dirs = [];

		if ( true === $hard_reset ) {
			$domain_dirs = [];

			return;
		}

		if ( isset( $domain_dirs[ $url_host ] ) ) {
			return $domain_dirs[ $url_host ];
		}

		if ( empty( $cache_path ) ) {
			$cache_path = _rocket_get_wp_rocket_cache_path();
		}

		try {
			$iterator = new IteratorIterator(
				new FilesystemIterator( $cache_path )
			);
		} catch ( Exception $e ) {
			return [];
		}

		$regex = sprintf(
			'/%1$s%2$s(.*)/i',
			_rocket_normalize_path( $cache_path, true ),
			$url_host
		);

		try {
			$entries = new RegexIterator( $iterator, $regex );
		} catch ( Exception $e ) {
			return [];
		}

		$domain_dirs[ $url_host ] = [];
		foreach ( $entries as $entry ) {
			$domain_dirs[ $url_host ][] = $entry->getPathname();
		}

		return $domain_dirs[ $url_host ];
	}
}

if ( ! function_exists( '_rocket_normalize_path' ) ) {
	/**
	 * Normalizes the given filesystem path:
	 *  - Windows/IIS-based servers: converts all directory separators to "\\" or, when escaping, to "\\\\".
	 *  - Linux-based servers: if $forced is true, uses wp_normalize_path(); else, returns the original path.
	 *
	 * @since  3.5.5
	 * @access private
	 *
	 * @param string $path   Filesystem path (file or directory) to normalize.
	 * @param bool   $escape Optional. When true, escapes the directory separator(s).
	 * @param bool   $force  Optional. When true, forces normalizing off non-Windows' paths.
	 *
	 * @return string
	 */
	function _rocket_normalize_path( $path, $escape = false, $force = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		if ( _rocket_is_windows_fs( $path ) ) {
			$path = str_replace( '/', '\\', $path );

			return $escape
				? str_replace( '\\', '\\\\', $path )
				: $path;
		}

		if ( $escape ) {
			return str_replace( '/', '\/', $path );
		}

		if ( ! $force ) {
			return $path;
		}

		return wp_normalize_path( $path );
	}
}

if ( ! function_exists( '_rocket_is_windows_fs' ) ) {
	/**
	 * Checks if the filesystem (fs) is for Windows/IIS server.
	 *
	 * @since  3.5.5
	 * @access private
	 *
	 * @param bool $hard_reset Optional. When true, resets the memoization.
	 *
	 * @return bool
	 */
	function _rocket_is_windows_fs( $hard_reset = false ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		static $is_windows = null;

		if ( $hard_reset ) {
			$is_windows = null;
		}

		if ( is_null( $is_windows ) ) {
			$is_windows = (
				DIRECTORY_SEPARATOR === '\\'
				&&
				! rocket_get_constant( 'WP_ROCKET_RUNNING_VFS', false )
			);
		}

		return $is_windows;
	}
}

if ( ! function_exists( '_rocket_get_wp_rocket_cache_path' ) ) {
	/**
	 * Gets the normalized cache path, i.e. normalizes constant "WP_ROCKET_CACHE_PATH".
	 *
	 * @since  3.5.5
	 * @access private
	 *
	 * @return string
	 */
	function _rocket_get_wp_rocket_cache_path() { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedFunctionFound
		return _rocket_normalize_path( rocket_get_constant( 'WP_ROCKET_CACHE_PATH' ) );
	}
}

<?php

if ( ! function_exists( 'rocket_has_constant' ) ) {
	/**
	 * Checks if the constant is defined.
	 *
	 * NOTE: This function allows mocking constants when testing.
	 *
	 * @since 3.5
	 *
	 * @param string $constant_name Name of the constant to check.
	 *
	 * @return bool true when constant is defined; else, false.
	 */
	function rocket_has_constant( $constant_name ) {
		return defined( $constant_name );
	}
}

if ( ! function_exists( 'rocket_get_constant' ) ) {
	/**
	 * Gets the constant is defined.
	 *
	 * NOTE: This function allows mocking constants when testing.
	 *
	 * @since 3.5
	 *
	 * @param string     $constant_name Name of the constant to check.
	 * @param mixed|null $default       Optional. Default value to return if constant is not defined.
	 *
	 * @return bool true when constant is defined; else, false.
	 */
	function rocket_get_constant( $constant_name, $default = null ) {
		if ( ! rocket_has_constant( $constant_name ) ) {
			return $default;
		}

		return constant( $constant_name );
	}
}

if ( ! function_exists( 'get_rocket_parse_url' ) ) {
	/**
	 * Extract and return host, path, query and scheme of an URL
	 *
	 * @since 2.11.5 Supports UTF-8 URLs
	 * @since 2.1 Add $query variable
	 * @since 2.0
	 *
	 * @param string $url The URL to parse.
	 *
	 * @return array Components of an URL
	 */
	function get_rocket_parse_url( $url ) { // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
		if ( ! is_string( $url ) ) {
			return;
		}

		$encoded_url = preg_replace_callback(
			'%[^:/@?&=#]+%usD',
			function( $matches ) {
				return rawurlencode( $matches[0] );
			},
			$url
		);

		$url      = wp_parse_url( $encoded_url );
		$host     = isset( $url['host'] ) ? strtolower( urldecode( $url['host'] ) ) : '';
		$path     = isset( $url['path'] ) ? urldecode( $url['path'] ) : '';
		$scheme   = isset( $url['scheme'] ) ? urldecode( $url['scheme'] ) : '';
		$query    = isset( $url['query'] ) ? urldecode( $url['query'] ) : '';
		$fragment = isset( $url['fragment'] ) ? urldecode( $url['fragment'] ) : '';

		/**
		 * Filter components of an URL
		 *
		 * @since 2.2
		 *
		 * @param array Components of an URL
		 */
		return (array) apply_filters(
			'rocket_parse_url',
			[
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			]
		);
	}
}

if ( ! function_exists( 'rocket_is_live_site' ) ) {
	/**
	 * Check if the current URL is for a live site (not local, not staging).
	 *
	 * @since  3.5
	 * @author Remy Perona
	 *
	 * @return bool True if live, false otherwise.
	 */
	function rocket_is_live_site() {
		if ( rocket_get_constant( 'WP_ROCKET_DEBUG' ) ) {
			return true;
		}

		$host = wp_parse_url( home_url(), PHP_URL_HOST );
		if ( ! $host ) {
			return false;
		}

		// Check for local development sites.
		$local_tlds = [
			'127.0.0.1',
			'localhost',
			'.local',
			'.test',
			'.docksal',
			'.docksal.site',
			'.dev.cc',
			'.lndo.site',
		];
		foreach ( $local_tlds as $local_tld ) {
			if ( $host === $local_tld ) {
				return false;
			}

			// Check the TLD.
			if ( substr( $host, - strlen( $local_tld ) ) === $local_tld ) {
				return false;
			}
		}

		// Check for staging sites.
		$staging = [
			'.wpengine.com',
			'.pantheonsite.io',
			'.flywheelsites.com',
			'.flywheelstaging.com',
			'.kinsta.com',
			'.kinsta.cloud',
			'.cloudwaysapps.com',
			'.azurewebsites.net',
			'.wpserveur.net',
			'-liquidwebsites.com',
			'.myftpupload.com',
		];
		foreach ( $staging as $partial_host ) {
			if ( strpos( $host, $partial_host ) ) {
				return false;
			}
		}

		return true;
	}
}

if ( ! function_exists( 'rocket_notice_html' ) ) {
	/**
	 * Outputs notice HTML
	 *
	 * @since  2.11
	 * @author Remy Perona
	 *
	 * @param array $args An array of arguments used to determine the notice output.
	 *
	 * @return void
	 */
	function rocket_notice_html( $args ) {
		$defaults = [
			'status'           => 'success',
			'dismissible'      => 'is-dismissible',
			'message'          => '',
			'action'           => '',
			'dismiss_button'   => false,
			'readonly_content' => '',
		];

		$args = wp_parse_args( $args, $defaults );

		switch ( $args['action'] ) {
			case 'clear_cache':
				$args['action'] = '<a class="wp-core-ui button" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=purge_cache&type=all' ), 'purge_cache_all' ) . '">' . __( 'Clear cache', 'rocket' ) . '</a>';
				break;
			case 'stop_preload':
				$args['action'] = '<a class="wp-core-ui button" href="' . wp_nonce_url( admin_url( 'admin-post.php?action=rocket_stop_preload&type=all' ), 'rocket_stop_preload' ) . '">' . __( 'Stop Preload', 'rocket' ) . '</a>';
				break;
			case 'force_deactivation':
				/**
				 * Allow a "force deactivation" link to be printed, use at your own risks
				 *
				 * @since 2.0.0
				 *
				 * @param bool $permit_force_deactivation true will print the link.
				 */
				$permit_force_deactivation = apply_filters( 'rocket_permit_force_deactivation', true );

				// We add a link to permit "force deactivation", use at your own risks.
				if ( $permit_force_deactivation ) {
					global $status, $page, $s;
					$plugin_file  = 'wp-rocket/wp-rocket.php';
					$rocket_nonce = wp_create_nonce( 'force_deactivation' );

					$args['action'] = '<a href="' . wp_nonce_url( 'plugins.php?action=deactivate&amp;rocket_nonce=' . $rocket_nonce . '&amp;plugin=' . $plugin_file . '&amp;plugin_status=' . $status . '&amp;paged=' . $page . '&amp;s=' . $s, 'deactivate-plugin_' . $plugin_file ) . '">' . __( 'Force deactivation ', 'rocket' ) . '</a>';
				}
				break;
		}

		?>
		<div class="notice notice-<?php echo esc_attr( $args['status'] ); ?> <?php echo esc_attr( $args['dismissible'] ); ?>">
			<?php
			$tag = 0 !== strpos( $args['message'], '<p' ) && 0 !== strpos( $args['message'], '<ul' );

			echo ( $tag ? '<p>' : '' ) . $args['message'] . ( $tag ? '</p>' : '' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
			?>
			<?php if ( ! empty( $args['readonly_content'] ) ) : ?>
				<p><?php esc_html_e( 'The following code should have been written to this file:', 'rocket' ); ?>
					<br><textarea readonly="readonly" id="rules" name="rules" class="large-text readonly" rows="6"><?php echo esc_textarea( $args['readonly_content'] ); ?></textarea>
				</p>
			<?php
			endif;
			if ( $args['action'] || $args['dismiss_button'] ) :
				?>
				<p>
					<?php echo $args['action']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					?>
					<?php if ( $args['dismiss_button'] ) : ?>
						<a class="rocket-dismiss" href="<?php echo wp_nonce_url( admin_url( 'admin-post.php?action=rocket_ignore&box=' . $args['dismiss_button'] ), 'rocket_ignore_' . $args['dismiss_button'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>"><?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?></a>
					<?php endif; ?>
				</p>
			<?php endif; ?>
		</div>
		<?php
	}
}

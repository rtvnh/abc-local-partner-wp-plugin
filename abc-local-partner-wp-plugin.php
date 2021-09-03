<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/rtvnh/abc-local-partner-wp-plugin
 * @since             0.4.0
 * @package           Plugin_ABC_Manager_Local_Partner
 *
 * @wordpress-plugin
 * Plugin Name:         ABC Manager - Local Partner
 * Plugin URI:          https://github.com/rtvnh/abc-local-partner-wp-plugin
 * Description:         WordPress Plugin to post new updates to the ABC Manager of NH/AT5
 * Version:             0.8.2
 * Author:              AngryBytes B.V.
 * Author URI:          https://angrybytes.com
 * License:             GPL-2.0+
 * Text Domain:         abclocalpartner
 * Requires at least:   5.7
 * Requires PHP:        7.3
 */

// Configure our plugin updater.
require_once plugin_dir_path( __FILE__ ) . '/class-abclocalpartnerwp-updater.php';

$updater = new AbcLocalPartnerWp_Updater( __FILE__ );
$updater->set_username( 'rtvnh' );
$updater->set_repository( 'abc-local-partner-wp-plugin' );
$updater->initialize();

$abc_post_status = false;

/**
 * Register our plugin settings.
 */
function abclocalpartner_register_settings(): void {
	add_option( 'abclocalpartner_option_abc_url' );
	add_option( 'abclocalpartner_option_partner_name' );
	add_option( 'abclocalpartner_option_partner_client_id' );
	add_option( 'abclocalpartner_option_partner_client_secret' );
	add_option( 'abclocalpartner_option_access_token' );
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_abc_url'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_name'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_client_id'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_client_secret'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_access_token'
	);
}

add_action( 'admin_init', 'abclocalpartner_register_settings' );

/**
 * Register the options page for our plugin.
 */
function abclocalpartner_register_options_page(): void {
	add_options_page( 'ABC Manager - Settings', 'ABC Manager', 'manage_options', 'abclocalpartner', 'abclocalpartner_options_page' );
}

add_action( 'admin_menu', 'abclocalpartner_register_options_page' );

/**
 * Generate the options page for our plugin.
 */
function abclocalpartner_options_page(): void {
	?>
	<div>
		<h1>RTV NH/AT5 - ABC Manager</h1>
		<div>
			<img src="<?php echo esc_url( plugin_dir_url( __FILE__ ) ); ?>/assets/images/nh-at5-logo.png" height="64" width="64" alt="NH"/>
		</div>
		<form method="post" action="options.php">
			<?php settings_fields( 'abclocalpartner_options_group' ); ?>
			<h2>ABC Manager Options</h2>
			<p>Please adjust the settings for a connection with ABC.</p>
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row">
							<label for="abclocalpartner_option_abc_url">API URL</label>
						</th>
						<td>
							<input type="text" id="abclocalpartner_option_abc_url" name="abclocalpartner_option_abc_url"
								class="regular-text"
								value="<?php echo esc_url( get_option( 'abclocalpartner_option_abc_url' ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abclocalpartner_option_partner_name">Partner name</label>
						</th>
						<td>
							<input type="text" id="abclocalpartner_option_partner_name"
								name="abclocalpartner_option_partner_name" class="regular-text"
								value="<?php echo esc_attr( get_option( 'abclocalpartner_option_partner_name' ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abclocalpartner_option_partner_client_id">Client ID</label>
						</th>
						<td>
							<input type="text" id="abclocalpartner_option_partner_client_id"
								name="abclocalpartner_option_partner_client_id" class="regular-text"
								value="<?php echo esc_attr( get_option( 'abclocalpartner_option_partner_client_id' ) ); ?>"/>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="abclocalpartner_option_partner_client_secret">Client Secret</label>
						</th>
						<td>
							<input type="password" id="abclocalpartner_option_partner_client_secret"
								name="abclocalpartner_option_partner_client_secret" class="regular-text"
								value="<?php echo esc_attr( get_option( 'abclocalpartner_option_partner_client_secret' ) ); ?>"/>
						</td>
					</tr>
					<style type="text/css">
						.status-dot {
							height: 10px;
							width: 10px;
							border-radius: 1000px;
							display: inline-block;
						}

						.status-dot.is-red {
							background-color: red;
						}

						.status-dot.is-green {
							background-color: green;
						}
					</style>
					<?php
					// Are ABC Manager and WordPress connected?
					$online       = check_abc_status();
					$status       = $online ? 'Up' : 'Down';
					$status_color = $online ? 'green' : 'red';
					?>
					<tr>
						<th>
							ABC Manager status:
						</th>
						<td>
							<span class="status-dot is-<?php echo esc_attr( $status_color ); ?>"></span> <?php echo esc_html( $status ); ?>
						</td>
					</tr>

					<?php
					// Are ABC Manager credentials valid?
					$valid        = check_credentials();
					$status       = $valid ? 'Valid' : 'Invalid';
					$status_color = $valid ? 'green' : 'red';
					?>
					<tr>
						<th>
							Credentials:
						</th>
						<td>
							<span class="status-dot is-<?php echo esc_attr( $status_color ); ?>"></span> <?php echo esc_html( $status ); ?>
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

/**
 * Get a bearer token from ABC Manager.
 *
 * @param bool $force_new  With this option set to true, you can force to always retrieve a new bearer token.
 *
 * @return string|null
 */
function get_abc_bearer_token( $force_new = false ) {
	$bearer_token = get_option( 'abclocalpartner_option_access_token' );

	if ( ! empty( $bearer_token ) && ! $force_new ) {
		return $bearer_token;
	}

	$api_endpoint  = get_option( 'abclocalpartner_option_abc_url' );
	$client_id     = get_option( 'abclocalpartner_option_partner_client_id' );
	$client_secret = get_option( 'abclocalpartner_option_partner_client_secret' );

	$raw_response = wp_remote_post(
		$api_endpoint . '/oauth2/token',
		array(
			'headers' => array_merge( get_environment_headers(), array( 'Content-Type' => 'application/x-www-form-urlencoded' ) ),
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'scope'         => 'partners',
			),
		)
	);

	if ( is_array( $raw_response ) ) {
		try {
			$data = json_decode( $raw_response['body'], true, 512, JSON_THROW_ON_ERROR );

			if ( array_key_exists( 'access_token', $data ) ) {
				update_option( 'abclocalpartner_option_access_token', $data['access_token'] );

				return $data['access_token'];
			}
		} catch ( Exception $e ) {
			return null;
		}
	}

	return null;
}

/**
 * This does a simple check if the credentials are valid.
 *
 * @param int $attempts Total of attempts.
 *
 * @return bool
 */
function check_credentials( $attempts = 0 ): bool {
	$token = get_abc_bearer_token();

	$api_endpoint = get_option( 'abclocalpartner_option_abc_url' );
	$partner_name = get_option( 'abclocalpartner_option_partner_name' );

	try {
		$response = wp_remote_get(
			sprintf( '%s/partner/article?partner=%s', $api_endpoint, $partner_name ),
			array(
				'headers' => array_merge( get_environment_headers(), array( 'Authorization' => sprintf( 'Bearer %s', $token ) ) ),
			)
		);

		if ( $response instanceof WP_Error ) {
			return false;
		}

		if ( array_key_exists( 'response', $response ) ) {
			switch ( $response['response']['code'] ) {
				case 204:
					return true;
				case 401:
					if ( 1 === $attempts ) {
						return false;
					}

					get_abc_bearer_token( true );

					return check_credentials( 1 );
				default:
					return false;
			}
		}
	} catch ( \Exception $e ) {
		return false;
	}

	return false;
}

/**
 * Used for development purposes only, adds Host header to all requests
 *
 * @return array|string[]
 */
function get_environment_headers(): array {
	return wp_get_environment_type() === 'local' ? array( 'Host' => 'partner.test' ) : array();
}

/**
 * This does a simple check if ABC Manager is available
 *
 * @return bool
 */
function check_abc_status(): bool {
	$host_header = wp_get_environment_type() === 'local' ? array( 'Host' => 'partner.test' ) : array();

	$api_endpoint = get_option( 'abclocalpartner_option_abc_url' );
	$partner_name = get_option( 'abclocalpartner_option_partner_name' );

	try {
		$response = wp_remote_get(
			sprintf( '%s/partner/article?partner=%s', $api_endpoint, $partner_name ),
			array(
				'headers' => $host_header,
			)
		);

		if ( $response instanceof WP_Error ) {
			return false;
		}

		if ( array_key_exists( 'response', $response ) ) {
			switch ( $response['response']['code'] ) {
				case 401:
					return true;
				default:
					return false;
			}
		}
	} catch ( \Exception $e ) {
		return false;
	}

	return false;
}

/**
 * Post an article to ABC Manager.
 *
 * @param WP_Post  $post             The WordPress post instance.
 * @param string[] $post_galleries   A list of galleries from the post content.
 *
 * @return bool
 */
function post_article_to_abc_manager( WP_Post $post, array $post_galleries ): bool {
	$post_json      = wp_json_encode( $post );
	$galleries_json = wp_json_encode( $post_galleries );
	$api_endpoint   = get_option( 'abclocalpartner_option_abc_url' );
	$partner_name   = get_option( 'abclocalpartner_option_partner_name' );
	$bearer_token   = get_abc_bearer_token();

	if ( empty( $bearer_token ) ) {
		return false;
	}

	$response = wp_remote_post(
		$api_endpoint . '/partner/article',
		array(
			'body'    => array(
				'partner'   => $partner_name,
				'content'   => $post_json,
				'galleries' => $galleries_json,
			),
			'headers' => array_merge( get_environment_headers(), array( 'Authorization' => 'Bearer ' . $bearer_token ) ),
		)
	);

	if ( $response instanceof WP_Error ) {
		return false;
	}

	if ( 'Partner not authorized.' === $response['body'] ) {
		// TODO add an error message: Partner not authorized make sure that the partner name in the settings is correct.
		return true;
	}
	if ( 'Unauthorized' === $response['body'] ) {
		return false;
	}

	return true;
}

/**
 * Register a hook on "save_post".
 *
 * @param int $post_id The WordPress post ID.
 */
function abclocalpartner_post_to_abc( int $post_id ): void {
	if ( ! empty( get_option( 'abclocalpartner_option_abc_url' ) ) &&
		! empty( get_option( 'abclocalpartner_option_partner_client_id' ) ) &&
		! empty( get_option( 'abclocalpartner_option_partner_client_secret' ) )
	) {
		if ( get_post_status( $post_id ) === 'publish' ) {
			$post           = get_post( $post_id );
			$post_galleries = get_post_galleries( $post_id );

			if ( ! $post instanceof WP_Post ) {
				return;
			}

			global $abc_post_status;
			$abc_post_status = post_article_to_abc_manager(
				$post,
				$post_galleries
			);

			// Only works for classic editor.
			add_filter( 'redirect_post_location', 'add_notice_query_param', 99 );
		}
	}
}

/**
 * Add query param &abc=<status> to url after post, only works for classic editor.
 *
 * @param string $location Location from WordPress.
 * @return string
 */
function add_notice_query_param( $location ): string {
	global $abc_post_status;

	remove_filter( 'redirect_post_location', 'add_notice_query_param', 99 );

	return add_query_arg(
		array(
			'abc'       => $abc_post_status,
			'abc_nonce' => wp_create_nonce( 'abc-nonce' ),
		),
		$location
	);
}

/**
 * Add notice after post, only works for classic editor.
 */
function add_abc_notice_after_post_save(): void {
	$abc_status = null;

	if ( isset( $_GET['abc'] ) &&
		isset( $_GET['abc_nonce'] ) &&
		wp_verify_nonce( sanitize_key( wp_unslash( $_GET['abc_nonce'] ) ), 'abc-nonce' )
	) {
		$abc_status = sanitize_key( wp_unslash( $_GET['abc'] ) );
	}

	if ( null === $abc_status ) {
		return;
	}
	?>
	<div class="notice notice-<?php echo $abc_status ? 'success' : 'error'; ?> is-dismissible">
		<p>Het versturen van het bericht naar ABC Manager is <?php echo $abc_status ? 'gelukt' : 'mislukt'; ?>.</p>
	</div>
	<?php
}

add_action( 'save_post', 'abclocalpartner_post_to_abc' );

add_action( 'admin_notices', 'add_abc_notice_after_post_save' );

/**
 * Allow iframe HTML tags.
 *
 * @param string[] $tags    A list of HTML tags.
 * @param string   $context The context to judge allowed tags by.
 *
 * @return mixed[]
 */
function prefix_add_source_tag( array $tags, string $context ): array {
	if ( 'post' === $context ) {
		$tags['iframe'] = array(
			'src'    => true,
			'srcdoc' => true,
			'width'  => true,
			'height' => true,
		);
	}
	return $tags;
}

add_filter( 'wp_kses_allowed_html', 'prefix_add_source_tag', 10, 2 );

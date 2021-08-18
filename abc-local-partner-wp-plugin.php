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
 * Plugin Name          ABC Manager - Local Partner
 * Plugin URI:          https://github.com/rtvnh/abc-local-partner-wp-plugin
 * Description:         WordPress Plugin to post new updates to the ABC Manager of NH/AT5
 * Version:             0.8.1
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
	add_options_page( 'ABC Manager - Connect', 'ABC Manager', 'manage_options', 'abclocalpartner', 'abclocalpartner_options_page' );
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
			<p>Please adjust the settings, for connection with ABC. If your not sure to know which codes are required, please read the docs.</p>
			<table class="form-table" role="presentation">
				<tbody>
				<tr>
					<th scope="row">
						<label for="abclocalpartner_option_abc_url">ABC Manager URL API</label>
					</th>
					<td>
						<input type="text" id="abclocalpartner_option_abc_url" name="abclocalpartner_option_abc_url"
							class="regular-text"
							value="<?php echo esc_url( get_option( 'abclocalpartner_option_abc_url' ) ); ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="abclocalpartner_option_partner_name">Partner Name On ABC Manager</label>
					</th>
					<td>
						<input type="text" id="abclocalpartner_option_partner_name"
							name="abclocalpartner_option_partner_name" class="regular-text"
							value="<?php echo esc_attr( get_option( 'abclocalpartner_option_partner_name' ) ); ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="abclocalpartner_option_partner_client_id">Partner Client ID</label>
					</th>
					<td>
						<input type="text" id="abclocalpartner_option_partner_client_id"
							name="abclocalpartner_option_partner_client_id" class="regular-text"
							value="<?php echo esc_attr( get_option( 'abclocalpartner_option_partner_client_id' ) ); ?>"/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="abclocalpartner_option_partner_client_secret">Partner Client Secret</label>
					</th>
					<td>
						<input type="text" id="abclocalpartner_option_partner_client_secret"
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
				$headers = get_headers( get_option( 'abclocalpartner_option_abc_url' ) );
				// TODO should attempt to authenticate with ABC Manager.

				if ( false === $headers ) {
					$status       = 'down';
					$status_color = 'red';
				} elseif ( $headers && strpos( $headers[0], '301' ) ) {
					$status       = 'down';
					$status_color = 'red';
				} else {
					$status       = 'up';
					$status_color = 'green';
				}
				?>
				<tr>
					<th>
						Current connection state:
					</th>
					<td>
						<?php echo esc_html( $status ); ?> <span class="status-dot is-<?php echo esc_attr( $status_color ); ?>"></span>
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
 * @param string $api_endpoint  The API endpoint to send the WordPress post to.
 * @param string $client_id     The partner's client ID.
 * @param string $client_secret The partner's client secret.
 *
 * @return mixed
 */
function get_abc_bearer_token( string $api_endpoint, string $client_id, string $client_secret ) {
	$raw_response = wp_remote_post(
		$api_endpoint . '/oauth2/token',
		array(
			'method'  => 'POST',
			'headers' => array( 'Content-Type' => 'application/x-www-form-urlencoded' ),
			'body'    => array(
				'grant_type'    => 'client_credentials',
				'client_id'     => $client_id,
				'client_secret' => $client_secret,
				'scope'         => 'partners',
			),
		)
	);

	$response = null;

	if ( is_array( $raw_response ) ) {
		try {
			$data = json_decode( $raw_response['body'], true, 512, JSON_THROW_ON_ERROR );

			$response = $data['access_token'];
		} catch ( Exception $e ) {
			// TODO Handle exception.
			$response = null;
		}
	}
	// TODO handle failed response.

	return $response;
}

/**
 * Post an article to ABC Manager.
 *
 * @param WP_Post  $post             The WordPress post instance.
 * @param string[] $post_galleries   A list of galleries from the post content.
 * @param string   $api_endpoint     The API endpoint to send the WordPress post to.
 * @param string   $bearer_token     A bearer token for API authentication.
 * @param string   $partner_name     The partner's name.
 * @param bool     $is_retry         Is this a retry of a previously failed API request.
 *
 * @return bool
 */
function post_article_to_abc_manager(
		WP_Post $post,
		array $post_galleries,
		string $api_endpoint,
		string $bearer_token,
		string $partner_name,
		bool $is_retry
): bool {
	$post_json      = wp_json_encode( $post );
	$galleries_json = wp_json_encode( $post_galleries );

	$response = wp_remote_post(
		$api_endpoint . '/partner/article',
		array(
			'body'    => array(
				'partner'   => $partner_name,
				'content'   => $post_json,
				'galleries' => $galleries_json,
			),
			'headers' => array(
				'Authorization' => 'Bearer ' . $bearer_token,
			),
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
		if ( $is_retry ) {
			// TODO add an error message: An error has occurred sending the post to ABC Manager, Make sure the credentials is correct.
			return true;
		}
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
	// TODO accept all params from save_post hook (int $post_id, WP_Post $post, bool $update).
	if ( ! empty( get_option( 'abclocalpartner_option_abc_url' ) ) &&
		! empty( get_option( 'abclocalpartner_option_partner_client_id' ) ) &&
		! empty( get_option( 'abclocalpartner_option_partner_client_secret' ) )
	) {
		$api_endpoint  = get_option( 'abclocalpartner_option_abc_url' );
		$partner_name  = get_option( 'abclocalpartner_option_partner_name' );
		$client_id     = get_option( 'abclocalpartner_option_partner_client_id' );
		$client_secret = get_option( 'abclocalpartner_option_partner_client_secret' );
		$bearer_token  = get_option( 'abclocalpartner_option_access_token' );

		if ( get_post_status( $post_id ) === 'publish' ) {
			$post           = get_post( $post_id );
			$post_galleries = get_post_galleries( $post_id );

			if ( ! $post instanceof WP_Post ) {
				// TODO handle non-existing post.
				return;
			}

			if ( empty( $bearer_token ) ) {
				$bearer_token = get_abc_bearer_token( $api_endpoint, $client_id, $client_secret );
				update_option( 'abclocalpartner_option_access_token', $bearer_token );
			}

			$success = post_article_to_abc_manager(
				$post,
				$post_galleries,
				$api_endpoint,
				$bearer_token,
				$partner_name,
				false
			);

			// TODO remove? This seems unnecessary.
			if ( false === $success ) {
				$bearer_token = get_abc_bearer_token( $api_endpoint, $client_id, $client_secret );
				update_option( 'abclocalpartner_option_access_token', $bearer_token );
				post_article_to_abc_manager(
					$post,
					$post_galleries,
					$api_endpoint,
					$bearer_token,
					$partner_name,
					true
				);
			}
		}
	}
}

add_action( 'save_post', 'abclocalpartner_post_to_abc' );

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

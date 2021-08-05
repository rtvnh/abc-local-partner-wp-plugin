<?php

/**
 * @package AbcLocalPartnerWp
 *
 * Plugin Name:        ABC Manager - Local Partner Wordpress
 * Plugin URI:         https://abcmanager.nl/
 * Description:        Wordpress Plugin to post new updates to ABC Manager of NH/AT5
 * Version:            0.7.1
 * Author:             AngryBytes B.V.
 * Author URI:         https://angrybytes.com
 * License:            MIT
 * Text Domain:        abclocalpartner
 * Requires at least:  5.7
 * Requires PHP:       7.3
 */

/*
* Updater
*/
include_once plugin_dir_path( __FILE__ ) . '/updater.php';

$updater = new AbcLocalPartnerWp_Updater( __FILE__ );
$updater->set_username( 'rtvnh' );
$updater->set_repository( 'abc-local-partner-wp-plugin' );
$updater->initialize();

/*
 * Registering of settings
 */
function abclocalpartner_register_settings() {
	add_option( 'abclocalpartner_option_abc_url' );
	add_option( 'abclocalpartner_option_partner_name' );
	add_option( 'abclocalpartner_option_partner_client_id' );
	add_option( 'abclocalpartner_option_partner_client_secret' );
	add_option( 'abclocalpartner_option_access_token' );
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_abc_url',
		'abclocalpartner_callback'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_name',
		'abclocalpartner_callback'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_client_id',
		'abclocalpartner_callback'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_client_secret',
		'abclocalpartner_callback'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_access_token',
		'abclocalpartner_callback'
	);
}

add_action( 'admin_init', 'abclocalpartner_register_settings' );

/*
 * Registers the options page for ABC Manager plugin
 */
function abclocalpartner_register_options_page() {
	add_options_page( 'ABC Manager - Connect', 'ABC Manager', 'manage_options', 'abclocalpartner', 'abclocalpartner_options_page' );
}

add_action( 'admin_menu', 'abclocalpartner_register_options_page' );

/*
 * Generates the options page for ABC Manager plugin
 */
function abclocalpartner_options_page() {
	?>
    <div>
        <h1>RTV NH/AT5 - ABC Manager</h1>
        <div>
            <img src="<?php echo plugin_dir_url( __FILE__ ) ?>/assets/images/nh-at5-logo.png" height="64" width="64"
                 alt="NH"/>
        </div>
        <form method="post" action="options.php">
			<?php settings_fields( 'abclocalpartner_options_group' ); ?>
            <h2>ABC Manager Options</h2>
            <p>Please adjust the settings, for connection with ABC. If your not sure to know which codes are required,
               please read the docs.</p>
            <table class="form-table" role="presentation">
                <tbody>
                <tr>
                    <th scope="row">
                        <label for="abclocalpartner_option_abc_url">ABC Manager URL API</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_abc_url" name="abclocalpartner_option_abc_url"
                               class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_abc_url' ); ?>"/>
                    </td>
                </tr>
				<tr>
                    <th scope="row">
                        <label for="abclocalpartner_option_partner_name">Partner Name On ABC Manager</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_partner_name"
                               name="abclocalpartner_option_partner_name" class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_partner_name' ); ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="abclocalpartner_option_partner_client_id">Partner Client ID</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_partner_client_id"
                               name="abclocalpartner_option_partner_client_id" class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_partner_client_id' ); ?>"/>
                    </td>
                </tr>
				<tr>
                    <th scope="row">
                        <label for="abclocalpartner_option_partner_client_secret">Partner Client Secret</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_partner_client_secret"
                               name="abclocalpartner_option_partner_client_secret" class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_partner_client_secret' ); ?>"/>
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
				// Are ABC and Wordpress connected?
				$headers = @get_headers( get_option( 'abclocalpartner_option_abc_url' ) );

				if ( $headers === false ) {
					$status      = 'down';
					$statusColor = 'red';
				} else if ( $headers && strpos( $headers[0], '301' ) ) {
					$status      = 'down';
					$statusColor = 'red';
				} else {
					$status      = 'up';
					$statusColor = 'green';
				}
				?>
                <tr>
                    <th>
                        Current connection state:
                    </th>
                    <td>
						<?= $status ?> <span class="status-dot is-<?= $statusColor ?>"></span>
                    </td>
                </tr>
                </tbody>
            </table>
			<?php submit_button(); ?>
        </form>
    </div>
	<?php
}

/*
 * Get bearer token
 */
function get_abc_bearer_token($apiEndpoint, $clientId, $clientSecret) {
	$raw_response = wp_remote_post($apiEndpoint . '/oauth2/token', [
		'method' => 'POST',
		'headers' => array('Content-Type' => 'application/x-www-form-urlencoded'),
		'body' => array(
			'grant_type' => 'client_credentials',
			'client_id' => $clientId,
			'client_secret' => $clientSecret,
			'scope' => 'partners'
		)
	]);
	$response = json_decode($raw_response['body'], true);

	return $response['access_token'];
}

/*
 * POST Article to ABC Manager
 */
function post_article_to_abc_manager($post, $postGalleries, $apiEndpoint, $bearerToken, $partnerName, $isRetry) {
	$postJson = wp_json_encode($post);
	$galleriesJson = wp_json_encode($postGalleries);

	$response = wp_remote_post($apiEndpoint . '/partner/article', [
		'body'    => [
			'partner' => $partnerName,
			'content' => $postJson,
			'galleries' => $galleriesJson,
		],
		'headers' => [
			'Authorization' => 'Bearer ' . $bearerToken,
		]
	]);

	if ($response['body'] === 'Partner not authorized.') {
		// TODO add an error message: Partner not authorized make sure that the partner name in the settings is correct.
		return true;
	}
	if ($response['body'] === 'Unauthorized') {
		if($isRetry) {
			// TODO add an error message: An error has occurred sending the post to ABC Manager, Make sure the credentials is correct.
			return true;
		}
		return false;
	}

	return true;
}

/*
 * Performs a POST request to ABC Manager
 */
function abclocalpartner_post_to_abc($postid) {
	if (!empty(get_option('abclocalpartner_option_abc_url')) &&
		!empty(get_option('abclocalpartner_option_partner_client_id')) &&
		!empty(get_option('abclocalpartner_option_partner_client_secret'))
	) {
		$apiEndpoint = get_option('abclocalpartner_option_abc_url');
		$partnerName = get_option('abclocalpartner_option_partner_name');
		$clientId = get_option('abclocalpartner_option_partner_client_id');
		$clientSecret = get_option('abclocalpartner_option_partner_client_secret');
		$bearerToken= get_option('abclocalpartner_option_access_token');

		if (get_post_status($postid) == 'publish' ) {
			$post = get_post($postid);
			$postGalleries = get_post_galleries($postid);

			if (empty($bearerToken)) {
				$bearerToken = get_abc_bearer_token($apiEndpoint, $clientId, $clientSecret);
				update_option('abclocalpartner_option_access_token', $bearerToken);
			}

			$isPostedToAbcManager = post_article_to_abc_manager($post, $postGalleries, $apiEndpoint, $bearerToken, $partnerName, false);

			if ($isPostedToAbcManager === false) {
				$bearerToken = get_abc_bearer_token($apiEndpoint, $clientId, $clientSecret);
				update_option('abclocalpartner_option_access_token', $bearerToken);
				post_article_to_abc_manager($post, $postGalleries, $apiEndpoint, $bearerToken, $partnerName, true);
			};
		}
	}
}

add_action( 'save_post', 'abclocalpartner_post_to_abc' );

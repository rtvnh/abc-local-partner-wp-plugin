<?php

/**
 * @package AbcLocalPartnerWp
 *
 * Plugin Name:        ABC Manager - Local Partner Wordpress
 * Plugin URI:         https://abcmanager.nl/
 * Description:        Wordpress Plugin to post new updates to ABC Manager of NH/AT5
 * Version:            0.6
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
if ( (string) get_option( 'abclocalpartner_option_access_token' ) !== '' ) {
	include_once plugin_dir_path( __FILE__ ) . '/updater.php';

	$updater = new AbcLocalPartnerWp_Updater( __FILE__ );
	$updater->set_username( 'rtvnh' );
	$updater->set_repository( 'abc-local-partner-wp-plugin' );
	$updater->authorize( get_option( 'abclocalpartner_option_access_token' ) );
	$updater->initialize();
}

/*
 * Registering of settings
 */
function abclocalpartner_register_settings() {
	add_option( 'abclocalpartner_option_abc_url');
	add_option( 'abclocalpartner_option_partner_secret');
	add_option( 'abclocalpartner_option_access_token');
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_abc_url',
		'abclocalpartner_callback'
	);
	register_setting(
		'abclocalpartner_options_group',
		'abclocalpartner_option_partner_secret',
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
                        <label for="abclocalpartner_option_partner_secret">Partner Token</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_partner_secret"
                               name="abclocalpartner_option_partner_secret" class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_partner_secret' ); ?>"/>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="abclocalpartner_option_access_token">Access Token</label>
                    </th>
                    <td>
                        <input type="text" id="abclocalpartner_option_access_token"
                               name="abclocalpartner_option_access_token" class="regular-text"
                               value="<?php echo get_option( 'abclocalpartner_option_access_token' ); ?>"/>
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
 * Performs a POST request to ABC Manager
 */
function abclocalpartner_post_to_abc( $postid ) {
	if ( get_post_status( $postid ) == 'publish' ) {
		$post     = get_post( $postid );
		$postJson = wp_json_encode( $post );

		if ( ! empty( get_option( 'abclocalpartner_option_abc_url' ) ) && ! empty( get_option( 'abclocalpartner_option_partner_secret' ) ) ) {
			wp_remote_post( get_option( 'abclocalpartner_option_abc_url' ), [
				'body'    => $postJson,
				'headers' => [
					'Authorization' => 'Bearer ' . get_option( 'abclocalpartner_option_partner_secret' ),
				]
			] );
		}

		$to      = 'elmar@angrybytes.com';
		$subject = 'Test bericht vanuit Wordpress';
		$body    = $post;
		$headers = array( 'Content-Type: text/html; charset=UTF-8' );

		wp_mail( $to, $subject, $body, $headers );
	}
}

add_action( 'save_post', 'abclocalpartner_post_to_abc' );

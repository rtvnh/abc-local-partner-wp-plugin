<?php

/**
 * @package AbcLocalPartnerWp
 */

/*

Plugin Name: ABC Manager - Local Partner Wordpress

Plugin URI: https://abcmanager.nl/

Description: Wordpress Plugin to post new updates to ABC Manager of NH/AT5

Version: 0.1.2

Author: AngryBytes B.V.

Author URI: https://angrybytes.com

License: MIT

Text Domain: abclocalpartner

*/

if( ! class_exists( 'AbcLocalPartnerWp_Updater' ) ){
    include_once( plugin_dir_path( __FILE__ ) . 'updater.php' );
}

$updater = new AbcLocalPartnerWp_Updater( __FILE__ );
$updater->set_username('rtvnh');
$updater->set_repository('abc-local-partner-wp-plugin');
$updater->authorize( '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' );

$updater->initialize();

function abclocalpartner_register_settings() {
    add_option( 'abclocalpartner_option_name', 'This is my option value.');
    register_setting( 'abclocalpartner_options_group', 'abclocalpartner_option_name', 'abclocalpartner_callback' );
}
add_action( 'admin_init', 'abclocalpartner_register_settings' );

function abclocalpartner_register_options_page() {
    add_options_page('Page Title', 'Plugin Menu', 'manage_options', 'abclocalpartner', 'abclocalpartner_options_page');
}
add_action('admin_menu', 'abclocalpartner_register_options_page');

function abclocalpartner_option_page()
{
    ?>
    <div>
        <?php screen_icon(); ?>
        <h2>ABC Manager Page Title</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'abclocalpartner_options_group' ); ?>
            <h3>ABC Manager Options</h3>
            <p>Please adjust the settings, for connection with ABC</p>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="abclocalpartner_option_name">ABC Manager URL</label></th>
                    <td><input type="text" id="abclocalpartner_option_name" name="abclocalpartner_option_name" value="<?php echo get_option('abclocalpartner_option_name'); ?>" /></td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
    </div>
    <?php
}

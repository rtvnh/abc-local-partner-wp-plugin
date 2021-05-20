<?php

/**
 * @package AbcLocalPartnerWp
 */

/*

Plugin Name: ABC Manager - Local Partner Wordpress

Plugin URI: https://abcmanager.nl/

Description: Wordpress Plugin to post new updates to ABC Manager of NH/AT5

Version: 0.1

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

function abclocalpartner_add_settings_page()
{
    add_options_page('ABC Manager Page', 'ABC Manager Plugin Menu', 'manage_options', 'abclocalpartner-example-plugin', 'abclocalpartner_render_plugin_settings_page');
}

add_action('admin_menu', 'abclocalpartner_add_settings_page');

function abclocalpartner_render_plugin_settings_page()
{
    ?>
    <h2>ABC Manager Plugin Settings</h2>
    <form action="options.php" method="post">
        <?php
        settings_fields('abclocalpartner_example_plugin_options');
        do_settings_sections('abclocalpartner_example_plugin'); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e('Save'); ?>"/>
    </form>
    <?php
}

function abclocalpartner_example_plugin_options_validate($input)
{
    $newinput['api_key'] = trim($input['api_key']);
    if (!preg_match('/^[a-z0-9]{32}$/i', $newinput['api_key'])) {
        $newinput['api_key'] = '';
    }

    return $newinput;
}

function abclocalpartner_plugin_section_text()
{
    echo '<p>Here you can set all the options for using the API</p>';
}

function abclocalpartner_plugin_setting_api_key()
{
    $options = get_option('abclocalpartner_example_plugin_options');
    echo "<input id='abclocalpartner_plugin_setting_api_key' name='abclocalpartner_example_plugin_options[api_key]' type='text' value='" . esc_attr($options['api_key']) . "' />";
}

function abclocalpartner_plugin_setting_results_limit()
{
    $options = get_option('abclocalpartner_example_plugin_options');
    echo "<input id='abclocalpartner_plugin_setting_results_limit' name='abclocalpartner_example_plugin_options[results_limit]' type='text' value='" . esc_attr($options['results_limit']) . "' />";
}

function abclocalpartner_plugin_setting_start_date()
{
    $options = get_option('abclocalpartner_example_plugin_options');
    echo "<input id='abclocalpartner_plugin_setting_start_date' name='abclocalpartner_example_plugin_options[start_date]' type='text' value='" . esc_attr($options['start_date']) . "' />";
}

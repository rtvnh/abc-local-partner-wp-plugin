<?php

/**
 * Updater class to deploy the WordPress ABC Local Partner plugin using GitHub.
 */
class AbcLocalPartnerWp_Updater {

	/**
	 * The plugin's file path.
	 *
	 * @var string
	 */
	private $file;

	/**
	 * The plugin's metadata.
	 *
	 * Parsed from the starting comment in the plugin file.
	 *
	 * @var string[]
	 */
	private $plugin;

	/**
	 * The plugin's name.
	 *
	 * Parsed from the plugin file path.
	 *
	 * @var string
	 */
	private $basename;

	/**
	 * Flag that indicates if the plugin is currently active.
	 *
	 * @var bool
	 */
	private $active;

	/**
	 * The GitHub username.
	 *
	 * @var string
	 */
	private $username;

	/**
	 * The GitHub repository.
	 *
	 * @var string
	 */
	private $repository;

	/**
	 * The latest plugin version retrieved from GitHub.
	 *
	 * @var array|null
	 */
	private $latest_version;

	/**
	 * Initiate plugin properties.
	 */
	public function __construct( string $file ) {
		$this->file = $file;

		// Postpone the final plugin properties until "admin_init" is fired. This ensures that "get_plugin_data" is available.
		add_action( 'admin_init', [ $this, 'set_plugin_properties' ] );
	}

	/**
	 * Finalize plugin properties.
	 */
	public function set_plugin_properties() {
		$this->plugin   = get_plugin_data( $this->file );
		$this->basename = plugin_basename( $this->file );
		$this->active   = is_plugin_active( $this->basename );
	}

	/**
	 * Set the GitHub username.
	 */
	public function set_username( string $username ) {
		$this->username = $username;
	}

	/**
	 * Set the GitHub repository.
	 */
	public function set_repository( string $repository ) {
		$this->repository = $repository;
	}

	/**
	 * Fetch the latest plugin version from GitHub.
	 */
	private function get_latest_plugin_version() {
		if ( is_null( $this->latest_version ) ) {
			$request_uri = sprintf(
				'https://api.github.com/repos/%s/%s/releases',
				$this->username,
				$this->repository
			);

			// Fetch the plugin releases from GitHub.
			$response = json_decode(
				wp_remote_retrieve_body( wp_remote_get( $request_uri ) ),
				true
			);

			// If we have a valid response.
			if( is_array( $response ) ) {
				// Store the latest release.
				$this->latest_version = current( $response );
			}
		}
	}

	/**
	 * Initialize the plugin updater.
	 */
	public function initialize() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'modify_transient' ), 10, 1 );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'after_install' ), 10, 3 );
	}

	/**
	 * Modify a transient with our plugin information.
	 *
	 * @param object $transient
	 *
	 * @return mixed
	 */
	public function modify_transient( $transient ) {
		// Did WordPress check for updates?
		if (property_exists($transient, 'checked') && $checked = $transient->checked) {
			// Fetch the latest plugin version.
			$this->get_latest_plugin_version();

			// Compare with our current version.
			$out_of_date = version_compare(
				(string) $this->latest_version['tag_name'],
				$checked[$this->basename ], 'gt'
			);

			if ( $out_of_date ) {
				// Our plugin is out of date, setup our plugin information.
				$new_files	= $this->latest_version['zipball_url'];
				$slug		= current( explode( '/', $this->basename ) );

				$plugin = [
					'url'			=> $this->plugin['PluginURI'],
					'slug'			=> $slug,
					'package'		=> $new_files,
					'new_version'	=> (string) $this->latest_version['tag_name']
				];

				// Modify the transient with our updated plugin information.
				$transient->response[ $this->basename ] = (object) $plugin;
			}
		}

		return $transient;
	}

	/**
	 * Provide WordPress plugin popup with infromation about our plugin.
	 *
	 * @param mixed $result
	 * @param mixed $args
	 *
	 * @return false|mixed|object
	 */
	public function plugin_popup( $result, string $action, $args ) {
		if ( $action !== 'plugin_information' ) {
			return false;
		}

		// Check if the slug matches our plugin.
		if (!empty($args->slug) && $args->slug === current(explode('/', $this->basename))) {
			$this->get_latest_plugin_version();

			$plugin = [
				'name'				=> $this->plugin['Name'],
				'slug'				=> $this->basename,
				'requires'			=> '5.3',
				'tested'			=> '5.4',
				'version'			=> $this->latest_version['tag_name'],
				'author'			=> $this->plugin['AuthorName'],
				'author_profile'	=> $this->plugin['AuthorURI'],
				'last_updated'		=> $this->latest_version['published_at'],
				'homepage'			=> $this->plugin['PluginURI'],
				'short_description'	=> $this->plugin['Description'],
				'sections'			=> [
					'Description'	=> $this->plugin['Description'],
					'Updates'		=> $this->latest_version['body'],
				],
				'download_link'		=> $this->latest_version['zipball_url']
			];

			// Return our plugin formation.
			return (object) $plugin;
		}

		// Otherwise, return the default information.
		return $result;
	}

	/**
	 * Move and reactive our plugin after installation.
	 *
	 * @param bool $response
	 * @param array $hook_extra
	 * @param array $result
	 *
	 * @return array
	 */
	public function after_install( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		// Move our updated plugin files to the plugin directory.
		$install_directory = plugin_dir_path( $this->file );
		$wp_filesystem->move( $result['destination'], $install_directory );
		$result['destination'] = $install_directory;

		// If our plugin was active.
		if ( $this->active ) {
			// Reactive it.
			activate_plugin( $this->basename );
		}

		return $result;
	}
}

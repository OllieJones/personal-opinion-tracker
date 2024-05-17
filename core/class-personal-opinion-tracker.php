<?php


namespace Personal_Opinion_Tracker;
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://https://profiles.wordpress.org/olliejones/
 * @since      1.0.0
 *
 * @package    Personal_Opinion_Tracker
 * @subpackage Personal_Opinion_Tracker/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Personal_Opinion_Tracker
 * @subpackage Personal_Opinion_Tracker/includes
 * @author     Oliver Jones <oj@plumislandmedia.net>
 */
class Personal_Opinion_Tracker {

	protected string $plugin_name;

	public string $version;
	/** Plugin base directory.  @var string */
	public string $base;
	public string $url;
	public string $slug;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $base, $url, $slug, $version ) {
		$this->base        = $base;
		$this->url         = $url;
		$this->slug        = $slug;
		$this->version     = $version;
		$this->plugin_name = 'personal-opinion-tracker';

		$this->load_dependencies();
		$this->internationalize();
		if ( is_admin() ) {
			$this->admin();
		}
		$this->public();

	}

	private function load_dependencies() {

	}

	private function internationalize() {

		add_action( 'plugins_loaded', function () {
			load_plugin_textdomain(
				'personal-opinion-tracker',
				false,
				$this->base . 'languages/'
			);

		} );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 * @since    1.0.0
	 * @access   private
	 */
	private function admin() {
		require_once $this->base . 'core/class-issue.php';
		new Issue( $this );
		require_once $this->base . 'core/class-party.php';
		new Party( $this );
		require_once $this->base . 'core/class-session.php';
		new Session( $this );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function public() {

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}


	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}

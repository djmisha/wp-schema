<?php
/**
 * WordPress  Schema Plugin
 *
 * Plugin Name: WP Schema
 * Plugin URI: https://www.asburymediagroup.com/
 * Author:  WP Schema
 * Author URI: https://www.asburymediagroup.com/
 * Version: 0.7.0
 * Description: Adds admin page and fields through Advanced Custom Fields Pro to add Business and Review schema to the website head tag. Currently only supports Pro version of Advanced Custom Fields Pro.
 *
 */

if ( !defined('ABSPATH') )
	die ( 'YOU SHALL NOT PASS!' );


define( 'wp_schema_PATH', plugin_dir_path(__FILE__) );
define( 'wp_schema_URL', plugin_dir_url(__FILE__) );
define( 'wp_schema_BASE', plugin_basename( __FILE__ ) );
define( 'wp_schema_VERSION', '0.7.0' );

class wp_schema {

	// Instance of this class
	static $instance	= false;

	// Plugin slug
	static $plugin_slug	= 'wp-schema-options';

	// Plugin data
	static $plugin_data	= NULL;

	// The schema payload that will be echoed out
	private $payload		= array();

	public function __construct() {

		add_action( 'plugins_loaded', array( $this, 'plugin_init' ), 20 );

		add_action( 'admin_notices', array( $this, 'data_notifications' ), 21 );

	}

	/**
	 * Singleton
	 *
	 * @return A single instance of the current class.
	 */
	public static function singleton() {

		if ( !self::$instance )
			self::$instance = new self();

		return self::$instance;

	}


	public function plugin_init() {

		// ACF check notification
		add_action( 'admin_notices', array( $this, 'acf_check_notice' ), 22 );

		// hook into the ACF actions
		add_action( 'acf/init', array( $this, 'add_our_files' ), 23 );

	}

	public function acf_check_notice() {

		if ( !is_plugin_active( 'advanced-custom-fields-pro/acf.php' ) && !is_plugin_active( 'advanced-custom-fields/acf.php' ) ) { ?>

			<div class="notice notice-error">
				<p>WordPress  Schema plugin is activated and depends on Advanced Custom Fields, which is not Activated/Installed. Please Activate/Install Advanced Custom Fields plugin.</p>
			</div>

			<?php
		}

	}

	public function add_our_files() {

		if ( is_admin() ) {

			if ( file_exists( wp_schema_PATH .'/includes/wp-schema-acf.php' ) ) {
				include_once wp_schema_PATH .'/includes/wp-schema-acf.php';
				wp_schema_ACF::singleton();
			}

			if ( file_exists( wp_schema_PATH .'/includes/wp-schema-acf-bypass.php' ) ) {
				include_once wp_schema_PATH .'/includes/wp-schema-acf-bypass.php';
				wp_schema_ACF_Bypass::singleton();
			}

			if ( file_exists( wp_schema_PATH .'/includes/rm-open-graph-acf.php' ) ) {
				include_once wp_schema_PATH .'/includes/rm-open-graph-acf.php';
				RM_Open_Graph_ACF::singleton();
			}

		} else {

			if ( file_exists( wp_schema_PATH .'/includes/wp-schema-json-ld.php' ) ) {
				include_once wp_schema_PATH .'/includes/wp-schema-json-ld.php';
				wp_schema_JSON_LD::singleton();
			}

			if ( file_exists( wp_schema_PATH .'/includes/wp-schema-reviews.php' ) ) {
				include_once wp_schema_PATH .'/includes/wp-schema-reviews.php';
				wp_schema_Reviews::singleton();
			}

			if ( file_exists( wp_schema_PATH .'/includes/rm-open-graph-meta.php' ) ) {
				include_once wp_schema_PATH .'/includes/rm-open-graph-meta.php';
				RM_Open_Graph_Meta::singleton();
			}

		}

	}

	/**
	 * Display a notice to either let the user know there has not been any data saved yet or a link to review on the Google schema tool
	 */
	public function data_notifications() {

		$screen = get_current_screen();

		if ( strpos( $screen->id, self::$plugin_slug ) == true ) {

			$values	= get_option( 'option_'. self::$plugin_slug );

			if ( !empty( $values ) ) {

				add_meta_box( 'review_schema', __('Review/Validate','acf'), array( $this, 'render_sidebox' ), 'acf_options_page', 'side', 'high' );

			} else { ?>

				<div class="notice notice-error">
					<p>You have not saved any schema data yet. At the moment, only the most basic schema is used.</p>
				</div>

				<?php

			}

		} // END check if we are in the right screen

	}

	/**
	 * Renders the metabox to alert the user to review the Schema using Google's Structured Data Testing Tool
	 */
	public function render_sidebox( $post, $args ) {	?>
			<p>Don't forget to Review the data using Google's Schema Tool.</p>
			<a target="_blank" class="button button-primary button-large" href="https://search.google.com/structured-data/testing-tool#url=<?php echo site_url(); ?>">Review Data</a>
		<?php

	}

}

// Initiate our foundation class
wp_schema::singleton();


// Run update checker code
if ( file_exists(dirname(__FILE__) . '/plugin-update-checker/plugin-update-checker.php') ) {

	if ( !class_exists('Puc_v4_Factory') ) {
		require 'plugin-update-checker/plugin-update-checker.php';
	}

	if ( class_exists('Puc_v4_Factory') ) {
		$rmSchemaPluginChecker = Puc_v4_Factory::buildUpdateChecker(
		    'http://plugins.rosemontmedia.com/wp-update-server/?action=get_metadata&slug=wp-schema',
		    __FILE__,
		    'wp-schema',
		    24
		);
	}

}

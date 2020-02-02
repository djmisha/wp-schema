<?php

if ( !defined('ABSPATH') )
	die ( 'YOU SHALL NOT PASS!' );

/**
 * This class bypasses the method that ACF saves our data
 * ACF saves admin page options as single rows in the {$wpdb->prefix}_options table
 * That is too many queries and inefficient so this class makes it so that the data is saved as a single option with serialized data.
 */

class wp_schema_ACF_Bypass {

	// Instance of this class
	static $instance	= false;

	// Plugin slug
	static $plugin_slug	= 'wp-schema-options';

	// Plugin data
	static $plugin_data	= null;

	public function __construct() {

		// get our plugin options as serialized data as we saved into the theme options
		self::$plugin_data	= get_option( 'option_'. self::$plugin_slug );

		add_action( 'acf/save_post', array( $this, 'consolidate_data' ), 0, 1 );

		add_action( 'acf/pre_load_value', array( $this, 'display_option_data' ), 10, 3 );

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

	/**
	 * Make our data save as serialized data in the theme options instead of one option for every...single...field
	 *
	 * Basically, short-circuit ACF.
	 * In ACF v5.6.9, the 'acf/pre_update_value' filter is defined in the 'includes/api/api-value.php' file under the 'acf_update_value' function
	 *
	 * Note: Calls for fields should be done through get_option and using our plugin key
	 */
	public static function consolidate_data( $post ) {

		$screen = get_current_screen();

		if ( strpos( $screen->id, self::$plugin_slug ) == true ) {
			$post_ID	= $_POST['_acf_post_id'];

			$options_status	= update_option( 'option_'. self::$plugin_slug, $_POST['acf'] );

			// Bail on ACF trying to save our stuff
			foreach ( $_POST['acf'] as $field => $value ) {

				add_filter( 'acf/pre_update_value', function( $value, $post_ID, $field ) {
					return null;
				}, 10, 3 );

			}

		}

	}

	/**
	 * This function handles displaying our saved data in the ACF fields
	 */
	public static function display_option_data( $value, $post_id, $field ) {

		if ( !empty( self::$plugin_data[ $field['key'] ] ) ) {

			if ( $field['type'] == 'repeater' ) {

				// bail ealry if not numeric
				if ( empty( self::$plugin_data[$field['key']] ) ) return false;

				// bail early if no sub fields
				if ( empty($field['sub_fields']) ) return false;


				// vars
				$values = self::$plugin_data[ $field['key'] ];
				$rows	= array();

				foreach ( $values as $value ) {

					$rows[]	= $value;

				}

				return $rows;


			} else {
				$value	= self::$plugin_data[ $field['key'] ];
			}

		} else {
			return null;
		}

		return $value;

	}

}

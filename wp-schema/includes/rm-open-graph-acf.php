<?php

if ( !defined('ABSPATH') )
	die ( 'YOU SHALL NOT PASS!' );

/**
 * This class handles creating the metabox for posts/pages
 */

class RM_Open_Graph_ACF {

	// Instance of this class
	static $instance	= false;

	// Plugin slug
	static $plugin_slug	= 'wp-schema-options';

	// Class variable to hold our ACF groups
	static $groups		= array();

	public function __construct() {

		// Define our groups on construct
		self::$groups[]	= array(
			'key'			=> 'rm_open_graph_data',
			'title'			=> 'Rosemont Social Media Info',
			'menu_order'	=> -1
		);

		$this->add_groups();

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
	 * Create our ACF groups dynamically
	 */
	public static function add_groups() {

		$allowed_post_types = get_field('rm_review_schema_settings_types', 'options');

		$locations = array();

		foreach ($allowed_post_types as $typeName) {
			$newType = array(
				array(
					'param'		=> 'post_type',
					'operator'	=> '==',
					'value'		=> $typeName
				)
			);

			array_push($locations, $newType);
		}

		foreach ( self::$groups as $group ) {
			acf_add_local_field_group( array(
				'key'			=> $group['key'],
				'title'			=> $group['title'],
				'menu_order'	=> !empty( $group['menu_order'] ) ? $group['menu_order'] : 0,
				'location'		=> $locations,
				'instruction_placement'	=> 'field',
			) );

			// calling each function based on the key of the group (dynamic purposes)
			$method_name	= 'add_fields_' . $group['key'];
			self::{"$method_name"}($group['key']);
		}

	}

	/**
	 * Create the main fields for each group in the functions below here
	 */
	public static function add_fields_rm_open_graph_data( $parent ) {

		/**
		 * Start Site Schema fields/options
		 */
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> '_rm_og_message',
			'name'		=> '_rm_og_message',
			'label'		=> 'Notes:',
			'type'		=> 'message',
			'message'	=> 'This info will be used for Open Graph data. This data is what will appear when sharing to social media (if it\'s filled out at all of course)<br>
							Here are a few links to help you preview what the post/page will look like:<br>
							FB (must be logged in): <a target="_blank" href="https://developers.facebook.com/tools/debug/sharing/">https://developers.facebook.com/tools/debug/sharing/</a><br>
							Twitter (must be logged in): <a target="_blank" href="https://cards-dev.twitter.com/validator">https://cards-dev.twitter.com/validator</a>',
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'rm_og_title',
			'name'		=> 'rm_og_title',
			'label'		=> 'Title',
			'type'		=> 'text',
			'instructions'	=> 'If left blank, will fallback to All-In-One SEO metabox title. Final fallback option is the post/page Title.'
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'rm_og_description',
			'name'		=> 'rm_og_description',
			'label'		=> 'Description',
			'type'		=> 'textarea',
			'rows'		=> 4,
			'maxlength'	=> 200,
			'instructions'	=> 'Character limit: 200 <br>If left blank, will fallback to All-In-One SEO metabox description. Final fallback option is the post/page excerpt.'
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'rm_og_share_image',
			'name'		=> 'rm_og_share_image',
			'label'		=> 'Share Image',
			'type'		=> 'image',
			'return_value'	=> 'url',
			'mime_types'	=> 'jpg, jpeg, png',
			'min_width'		=> 280,
			'min_height'	=> 150,
			'max_width'		=> 1920,
			'max_height'	=> 1080,
			'instructions'	=> 'Only jpg/jpeg/png files allows.<br>If left blank, fallback to post/page featured image. <br>Recommendations so that it qualifies for both FB and Twitter: minimum dimensions of 600px by 315px, no larger than 1200px by 630px.',
		) );

	}

}

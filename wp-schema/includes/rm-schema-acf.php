<?php

if ( !defined('ABSPATH') )
	die ( 'YOU SHALL NOT PASS!' );

/**
 * This class handles creating the admin page and ACF fields
 *
 * Notes:
 * 		1) Separated the group and fields to keep it a bit more organized
 * 		2) To keep code organized, I added 'repeater' fields with the 'acf_add_local_field' function.
 * 		 Therefore, every 'sub_field' has to be added with the 'acf_add_local_field' function or else ACF throws a bitch fit
 */

class wp_schema_ACF {

	// Instance of this class
	static $instance	= false;

	// Plugin slug
	static $plugin_slug	= 'wp-schema-options';

	// Plugin data
	static $plugin_data	= null;

	// Class variable to hold our ACF groups
	static $groups		= array();

	public function __construct() {

		// get our plugin options as serialized data as we saved into the theme options
		self::$plugin_data	= get_option( 'option_'. self::$plugin_slug );

		// Define our groups on construct
		self::$groups[]	= array(
			'key'			=> 'rm_site_schema',
			'title'			=> 'General Site/Business Schema',
			'menu_order'	=> 0
		);

		self::$groups[]	= array(
			'key'			=> 'rm_locations_schema',
			'title'			=> 'Locations Schema',
			'menu_order'	=> 1
		);

		self::$groups[]	= array(
			'key'			=> 'rm_employees_schema',
			'title'			=> 'Employees Schema',
			'menu_order'	=> 2
		);

		self::$groups[]	= array(
			'key'			=> 'rm_review_schema',
			'title'			=> 'Review Schema',
			'menu_order'	=> 3
		);

		self::$groups[]	= array(
			'key'			=> 'rm_review_schema_settings',
			'title'			=> 'Review Schema Settings',
			'menu_order'	=> 4,
			'location'	=> 'rm_review_schema_settings'
		);

		$this->create_admin_page();
		$this->add_groups();

		// add_action( 'acf/init', array( $this, 'add_groups' ) );
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
	 * Create the settings page in the sidebar of the WP admin area
	 */
	public static function create_admin_page() {

		if ( function_exists('acf_add_options_page') ) {

			acf_add_options_page( array(
				'page_title' 	=> 'Rosemont Schema Options',
				'menu_title'	=> 'RM Schema',
				'menu_slug' 	=> self::$plugin_slug,
				'capability'	=> 'edit_posts',
				'redirect'		=> false
			) );

			acf_add_options_sub_page( array(
						'page_title' => 'RM Schema Settings',
						'menu_title'=> 'RM Schema Settings',
						'menu_slug' => 'rm_review_schema_settings',
						'parent_slug' => self::$plugin_slug,
						'capability'=> 'edit_posts',
						'redirect' => false
					) );

		}

	}

	/**
	 * Create our ACF groups dynamically
	 */
	public static function add_groups() {

		foreach ( self::$groups as $group ) {

			acf_add_local_field_group( array(
				'key'			=> $group['key'],
				'title'			=> $group['title'],
				'menu_order'	=> !empty( $group['menu_order'] ) ? $group['menu_order'] : 0,
				'location'		=> array(
					array(
						array(
							'param'		=> 'options_page',
							'operator'	=> '==',
							'value'		=> isset($group['location']) ? $group['location'] : self::$plugin_slug,
						),
					),
				),
				'instruction_placement'	=> 'field'
			) );

			// calling each function based on the key of the group (dynamic purposes)
			$method_name	= 'add_fields_' . $group['key'];
			self::{"$method_name"}($group['key']);

		}

	}

	/**
	 * Recover previous versions values to allow flawless upgrade
	 */
	public static function recoverValue( $oldSettingName ) {
		if (isset(self::$plugin_data[$oldSettingName]))
			return self::$plugin_data[$oldSettingName];

		return;
	}

	public static function add_fields_rm_review_schema_settings( $parent ) {

		$post_types = array();

		foreach (get_post_types() as $name => $label ) {
			//array_push($post_types, [$post->name => $post->label ]);
			$post_types[$name] = $label;
		}

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'rm_review_schema_settings_types',
			'name'		=> 'rm_review_schema_settings_types',
			'label'		=> 'Custom Post Types',
			'type'		=> 'checkbox',
			'choices' => $post_types,
			'instructions'	=> 'Check custom post types to be injected',
		));
	}

	/**
	 * Create the main fields for each group in the functions below here
	 */
	public static function add_fields_rm_site_schema( $parent ) {

		/**
		 * Start Site Schema fields/options
		 */
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_site_logo',
			'name'		=> 'wp_schema_site_logo',
			'label'		=> 'Site Logo',
			'type'		=> 'image',
			'return_value'	=> 'url',
			'mime_types'	=> 'jpg, jpeg, png',
			'min_width'		=> 160,
			'min_height'	=> 90,
			'max_width'		=> 1920,
			'max_height'	=> 1080,
			'instructions'	=> 'Only jpg and png files accepted. <br>Min dimensions of image: 160px width by 90px height <br>Max dimensions of image: 1920px width by 1080px height',
			'value' => self::recoverValue('site_logo')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_site_name',
			'name'		=> 'wp_schema_site_name',
			'label'		=> 'Site Name',
			'type'		=> 'text',
			'placeholder'	=> get_bloginfo('name'),
			'instructions'	=> 'If left blank, will default to the "Site Title" defined in WordPress admin area under General Settings. <br>Used for Organization and Website schema.',
			'value' => self::recoverValue('site_name')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_site_about',
			'name'		=> 'wp_schema_site_about',
			'label'		=> 'About Site',
			'type'		=> 'textarea',
			'rows'		=> 4,
			'placeholder'	=> get_bloginfo('description'),
			'instructions'	=> 'If left blank, will default to the "Site Tagline" defined in WordPress admin area under General Settings.',
			'value' => self::recoverValue('site_about')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_main_phone',
			'name'		=> 'wp_schema_main_phone',
			'label'		=> 'Phone Number',
			'type'		=> 'text',
			'placeholder'	=> '+1-555-555-5555',
			'value' => self::recoverValue('main_phone')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_about_us_link',
			'name'		=> 'wp_schema_about_us_link',
			'label'		=> 'About Us Link',
			'type'		=> 'link',
			'value' => self::recoverValue('about_us_link')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_sitelinks_searchbox',
			'name'		=> 'wp_schema_sitelinks_searchbox',
			'label'		=> 'Use Sitelinks Searchbox?',
			'type'		=> 'true_false',
			'instructions'	=> 'This is the individual searchbox that will appear in Google for the site. Example and definition <a href="https://developers.google.com/search/docs/data-types/sitelinks-searchbox" target="_blank">here</a>.',
			'value' => self::recoverValue('sitelinks_searchbox')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_organization_type',
			'name'		=> 'wp_schema_organization_type',
			'label'		=> 'Organization Type',
			'type'		=> 'radio',
			'choices'	=> array(
				'Physician'		=> 'Physician',
				'Dentist'		=> 'Dentist',
				'LocalBusiness'	=> 'LocalBusiness'
			),
			'layout'		=> 'horizontal',
			'other_choice'	=> true,
			'value' => self::recoverValue('organization_type')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_price_range',
			'name'		=> 'wp_schema_price_range',
			'label'		=> 'Price Range',
			'type'		=> 'text',
			'placeholder'	=> 'Contact For Pricing',
			'instructions'	=> 'The price range of the business. Arbitrary field. For example "$$$"" or a range "$00 - $0000".',
			'value' => self::recoverValue('price_range')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_business_image',
			'name'		=> 'wp_schema_business_image',
			'label'		=> 'Business Image',
			'type'		=> 'image',
			'return_value'	=> 'url',
			'mime_types'	=> 'jpg, jpeg, png',
			'min_width'		=> 160,
			'min_height'	=> 90,
			'max_width'		=> 1920,
			'max_height'	=> 1080,
			'instructions'	=> 'Image of the actual Business. Will default/fallback to the Site Logo if left empty. <br>Only jpg and png files accepted. <br>Min dimensions of image: 160px width by 90px height <br>Max dimensions of image: 1920px width by 1080px height',
			'value' => self::recoverValue('business_image')
		) );

		// This should probably be replaced by defining social media URLs elsewhere, like theme settings but not all blogs have theme settings
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_sameas',
			'name'		=> 'wp_schema_sameas',
			'label'		=> 'Related URLs (Social Media)',
			'type'		=> 'repeater',
			'layout'	=> 'row',
			'instructions'	=> 'Related sites/URLs and social media URLs that this website is related to.',
			'button_label'	=> 'Add URL',
			'value' => self::update_acf_subfields('sameas', [
				'url' => 'wp_schema_sameas_url',
			])
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_sameas',
			'key'		=> 'wp_schema_sameas_url',
			'name'		=> 'wp_schema_sameas_url',
			'label'		=> 'URL',
			'type'		=> 'url'
		) );

		//update_sub_field();
	}

	public static function update_acf_subfields($parent, $updates) {
		if ((self::$plugin_data == false) || (!isset(self::$plugin_data[$parent]))) return;

		$data = json_encode(self::$plugin_data[$parent], JSON_UNESCAPED_SLASHES);

		foreach($updates as $name => $value) {
			$data = str_replace('"'.$name.'"', '"'.$value.'"', $data);
		}

		return json_decode($data, true);
	}

	public static function add_fields_rm_locations_schema( $parent ) {

		// This will/should be moved to a plugin or combined with one that will work as a hub for all theme settings/options
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_locations',
			'name'		=> 'wp_schema_locations',
			'label'		=> 'Locations',
			'type'		=> 'repeater',
			'layout'	=> 'row',
			'button_label'	=> 'Add Location',
			'value' => self::update_acf_subfields('locations', [
				'business_name' => 'wp_schema_locations_business_name',
				'street_address' => 'wp_schema_locations_street_address',
				'city' => 'wp_schema_locations_city',
				'state' => 'wp_schema_locations_state',
				'zipcode' => 'wp_schema_locations_zipcode',
				'country' => 'wp_schema_locations_country',
				'phone' => 'wp_schema_locations_phone',
				'latitude' => 'wp_schema_locations_latitude',
				'longitude' => 'wp_schema_locations_longitude'
			])
		) );

		// Doing this separately to keep things organized
		self::add_location_sub_fields();

	}

	public static function add_fields_rm_employees_schema( $parent ) {

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_employees',
			'name'		=> 'wp_schema_employees',
			'label'		=> 'Employees',
			'type'		=> 'repeater',
			'layout'	=> 'row',
			'button_label'	=> 'Add Employee',
			'value' => self::update_acf_subfields('employees', [
				'name' => 'wp_schema_employees_name',
				'phone' => 'wp_schema_employees_phone',
				'link' => 'wp_schema_employees_link',
				'job_title' => 'wp_schema_employees_job_title',
				'image' => 'wp_schema_employees_image'
				])
		) );

		// Doing this separately to keep things organized
		self::add_employee_sub_fields();

	}

	public static function add_fields_rm_review_schema( $parent ) {

		/**
		 * Start Review Schema fields/options
		 */
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_reviews_status',
			'name'		=> 'wp_schema_reviews_status',
			'label'		=> 'Reviews URL',
			'type'		=> 'radio',
			'layout'	=> 'horizontal',
			'choices'	=> array(
				'off'	=> 'Off',
				'on'	=> 'On',
			),
			'default_value'	=> 'off',
			'instructions'	=> 'If status is "off", none of the below will show up on schema or markup. <br><strong>Note for DEV:</strong> Insert "<strong>do_action(\'reviews_markup\')</strong>" in a theme template to display the Reviews Schema (99% of the time you want to put this in the "<strong>footer.php</strong>" template).',
			'value' => self::recoverValue('reviews_status')
		) );
		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_reviews_url',
			'name'		=> 'wp_schema_reviews_url',
			'label'		=> 'Reviews URL',
			'type'		=> 'url',
			'instructions'	=> 'Insert full URL of link. If the URL lives on the same site, will automatically open in same browser tab, else will open in new tab.',
			'value' => self::recoverValue('reviews_url')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_rating_value',
			'name'		=> 'wp_schema_rating_value',
			'label'		=> 'Star Rating / Rating Value',
			'type'		=> 'number',
			'min'		=> 0,
			'max'		=> 5,
			'step'		=> '0.1',
			'placeholder'	=> '0.0',
			'default_value'	=> '0.0',
			'instructions'	=> 'This will appear as an actual number in the mark up.',
			'value' => self::recoverValue('rating_value')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_reviews_count',
			'name'		=> 'wp_schema_reviews_count',
			'label'		=> 'Reviews Count',
			'type'		=> 'number',
			'min'		=> 1,
			'placeholder'	=> '0',
			'default_value'	=> 1,
			'instructions'	=> 'The number of reviews for the front-end markup and schema markup.',
			'value' => self::recoverValue('reviews_count')
		) );

		acf_add_local_field( array(
			'parent'	=> $parent,
			'key'		=> 'wp_schema_reviews_schema_location',
			'name'		=> 'wp_schema_reviews_schema_location',
			'label'		=> 'Reviews Schema Location',
			'type'		=> 'radio',
			'choices'	=> array(
				'organization'	=> 'Organization',
				'person'		=> 'Physician/Dentist',
			),
			'default_value'	=> 'organization',
			'instructions'	=> 'Determine where/what to append Reviews Schema to in Schema markup.',
			'value' => self::recoverValue('reviews_schema_location')
		) );

	}

	/**
	 * Create sub fields for repeaters in the functions below
	 */
	public static function add_location_sub_fields() {

		// check for business phone number to use as placeholder to show fallback to user
		$placeholder_phonenumber	= !empty( self::$plugin_data['wp_schema_main_phone'] ) ? self::$plugin_data['wp_schema_main_phone'] : '+1-555-555-5555';

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_business_name',
			'name'		=> 'wp_schema_locations_business_name',
			'label'		=> 'Business Name',
			'type'		=> 'text',
			'placeholder'	=> !empty( self::$plugin_data['wp_schema_site_name'] ) ? self::$plugin_data['wp_schema_site_name'] : get_bloginfo('name'),
			'instructions'	=> 'If left blank, will default to Site Name.',
			'value' => self::recoverValue('business_name')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_street_address',
			'name'		=> 'wp_schema_locations_street_address',
			'label'		=> 'Street Address',
			'type'		=> 'text',
			'value' => self::recoverValue('street_address')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_city',
			'name'		=> 'wp_schema_locations_city',
			'label'		=> 'City',
			'type'		=> 'text',
			'value' => self::recoverValue('city')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_state',
			'name'		=> 'wp_schema_locations_state',
			'label'		=> 'State',
			'type'		=> 'text',
			'maxlength'	=> 3,
			'value' => self::recoverValue('state')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_zipcode',
			'name'		=> 'wp_schema_locations_zipcode',
			'label'		=> 'Postal Code',
			'type'		=> 'text',
			'maxlength'	=> 12,
			'value' => self::recoverValue('zipcode')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_country',
			'name'		=> 'wp_schema_locations_country',
			'label'		=> 'Country',
			'type'		=> 'text',
			'instructions'	=> 'Preferrence for the <a href="https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2#Officially_assigned_code_elements" target="_blank">2-letter ISO 3166-1 alpha-2 country code</a>. <br>Only really necessary if outside of US.',
			'value' => self::recoverValue('country')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_locations',
			'key'		=> 'wp_schema_locations_phone',
			'name'		=> 'wp_schema_locations_phone',
			'label'		=> 'Phone Number',
			'type'		=> 'text',
			'placeholder'	=> $placeholder_phonenumber,
			'instructions'	=> 'If left blank, will default to the general number above.',
			'value' => self::recoverValue('phone')
		) );

		acf_add_local_field( array(
			'parent'		=> 'wp_schema_locations',
			'key'			=> 'wp_schema_locations_latitude',
			'name'			=> 'wp_schema_locations_latitude',
			'label'			=> 'Latitude',
			'type'			=> 'number',
			'min'			=> -90,
			'max'			=> 90,
			'placeholder'	=> '000.0000',
			'value' => self::recoverValue('latitude')
		) );

		acf_add_local_field( array(
			'parent'		=> 'wp_schema_locations',
			'key'			=> 'wp_schema_locations_longitude',
			'name'			=> 'wp_schema_locations_longitude',
			'label'			=> 'Longitude',
			'type'			=> 'number',
			'min'			=> -180,
			'max'			=> 180,
			'placeholder'	=> '000.0000',
			'value' => self::recoverValue('longitude')
		) );

	}

	public static function add_employee_sub_fields() {

		// check for business phone number to use as placeholder to show fallback to user
		$placeholder_phonenumber	= !empty( self::$plugin_data['wp_schema_main_phone'] ) ? self::$plugin_data['wp_schema_main_phone'] : '+1-555-555-5555';

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_employees',
			'key'		=> 'wp_schema_employees_name',
			'name'		=> 'wp_schema_employees_name',
			'label'		=> 'Name',
			'type'		=> 'text'
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_employees',
			'key'		=> 'wp_schema_employees_job_title',
			'name'		=> 'wp_schema_employees_job_title',
			'label'		=> 'Job Title',
			'type'		=> 'radio',
			'choices'	=> array(
				'Physician'				=> 'Physician',
				'Dentist'				=> 'Dentist',
				'Plastic Surgeon'		=> 'Plastic Surgeon',
				'Ophthalmologist'		=> 'Ophthalmologist',
				'Dermatologist'			=> 'Dermatologist',
				'Orthopaedic Surgeon'	=> 'Orthopaedic Surgeon',
				'Bariatric Surgeon'		=> 'Bariatric Surgeon',
			),
			'layout'		=> 'horizontal',
			'other_choice'	=> true,
			'value' => self::recoverValue('job_title')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_employees',
			'key'		=> 'wp_schema_employees_image',
			'name'		=> 'wp_schema_employees_image',
			'label'		=> 'Image',
			'type'		=> 'image',
			'return_value'	=> 'url',
			'mime_types'	=> 'jpg, jpeg, png',
			'instructions'	=> 'Only jpg and png files accepted',
			'value' => self::recoverValue('image')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_employees',
			'key'		=> 'wp_schema_employees_phone',
			'name'		=> 'wp_schema_employees_phone',
			'label'		=> 'Phone Number',
			'type'		=> 'text',
			'placeholder'	=> $placeholder_phonenumber,
			'instructions'	=> 'If left blank, will default to business number.',
			'value' => self::recoverValue('phone')
		) );

		acf_add_local_field( array(
			'parent'	=> 'wp_schema_employees',
			'key'		=> 'wp_schema_employees_link',
			'name'		=> 'wp_schema_employees_link',
			'label'		=> 'Link',
			'type'		=> 'link',
			'value' => self::recoverValue('link')
		) );

	}

}

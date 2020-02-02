<?php

if ( !defined('ABSPATH') )
	die ( 'YOU SHALL NOT PASS!' );

/**
 * This class sets up our custom Open Graph data with fallback to All-in-one SEO plugin
 * (which by the way doesn't have this feature automatically turned on at the point of creation..dumb right?)
 */

class RM_Open_Graph_Meta {

	// Instance of this class
	static $instance	= false;

	// Plugin slug
	static $plugin_slug	= 'wp-schema-options';

	// Plugin data
	static $plugin_data	= null;

	static $post_data	= null;

	public function __construct() {

		// get our plugin options as serialized data as we saved into the theme options for our fallback options
		self::$plugin_data	= get_option( 'option_'. self::$plugin_slug );

		// fuck jetpack
		add_filter( 'jetpack_enable_open_graph', '__return_false' );

		// add all our regular actions that will echo out when appropriate
		add_action( 'rm_open_graph', array( $this, 'generate_og_title' ) );
		add_action( 'rm_open_graph', array( $this, 'generate_og_description' ) );
		add_action( 'rm_open_graph', array( $this, 'generate_og_image' ) );
		add_action( 'rm_open_graph', array( $this, 'generate_og_url' ) );
		add_action( 'rm_open_graph', array( $this, 'generate_og_type' ) );

		// add our stuff
		add_action( 'wp_head', array( $this, 'rm_open_graph_head' ) );

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

	public function rm_open_graph_head() {
		$post_type = get_post_type( get_queried_object()->ID );
		$allowed_post_types = get_field('rm_review_schema_settings_types', 'options');

		if( !is_array($allowed_post_types) || array_search($post_type, $allowed_post_types) === false) {
			return;
		}

		do_action( 'rm_open_graph' );
	}

	/*
	 * Generates og:image meta tag for images in social sharing
	 */
	public function generate_og_title() {

		global $post;

		if ( empty( $post ) ) return;

		$og_title	= '';

		// Prioritize custom metabox data
		$rm_title	= get_post_meta( $post->ID, 'rm_og_title', true );

		if ( $rm_title !== '' ) {
			$og_title	= $rm_title;
		}

		if ( $og_title == '' && '' !== $aioseo_title = get_post_meta( $post->ID, '_aioseop_title', true ) ) {
			$og_title = $aioseo_title;
		}

		if ( $og_title == '' ) {
			$og_title	= get_the_title();
		}

		if ( $og_title != '' ) {
			// since we at least have a title, add the general Open Graph tags
			$this->generate_og_general_tags();
			echo '<meta property="og:title" content="'. esc_attr( $og_title ) .'" />'. "\n\t\t";
		}

	}

	public function generate_og_description() {

		global $post;

		if ( empty( $post ) ) return;

		$og_description	= '';

		// Prioritize custom metabox data
		$rm_description	= get_post_meta( $post->ID, 'rm_og_description', true );

		if ( $rm_description !== '' ) {
			$og_description	= $rm_description;
		}

		if ( $og_description == '' && '' !== $aioseo_description = get_post_meta( $post->ID, '_aioseop_description', true ) ) {
			$og_description = $aioseo_description;
		}

		if ( $og_description == '' && is_single() ) {

			setup_postdata( $post );

			// Using output buffering in case someone tries to echo out the content in our filters...
			// Basically safety for other plugin developers' mistakes
			ob_start();

			$the_excerpt	= get_the_excerpt( $post->ID );

			echo $the_excerpt;

			$og_description = ob_get_clean();

			wp_reset_postdata();

			// shorten to 200 characters if it's longer
			if ( mb_strlen( $og_description ) > 200 ) {

				$og_description = substr( trim( strip_tags( $og_description ) ), 0, 198 ) .'...';

			}

		} elseif ( is_front_page() ) {

			if ( class_exists( 'All_in_One_SEO_Pack' ) ) {

				$aiosp = new All_in_One_SEO_Pack();

				$og_description	= $aiosp->get_aioseop_description( $post );

			} else {

				// fallback to the blog description in admin settings if it's not empty
				$blog_description	= get_bloginfo('description');

				$og_description		= $blog_description != '' ? $blog_description : '';

			}

		}

		if ( $og_description != '' ) {
			echo '<meta property="og:description" content="'. esc_attr( $og_description ) .'" />'. "\n\t\t";
		}

	}

	/*
	 * Generates og:image meta tag for images in social sharing
	 */
	public function generate_og_image() {

		global $post;

		if ( empty( $post ) ) return;

		// Check if the WPSEO function that will handle this exists (plugin is on)
		// If it is, let that handle the og:image meta tag instead of here (don't want double meta tags)
		if ( class_exists('WPSEO_OpenGraph_Image') ) return;

		$image_data	= false;

		// Prioritize the custom metabox data
		$rm_image_ID	= get_post_meta( $post->ID, 'rm_og_share_image', true );

		if ( $rm_image_ID !== '' ) {
			$image_data	= wp_get_attachment_image_src( $rm_image_ID, 'large' );
		}

		// If singular, use the Featured image
		if ( $image_data == false && is_singular() ) {

			$post_ID	= $post->ID;
			$has_thumb	= has_post_thumbnail( $post_ID );

			$image_data	= $has_thumb ? wp_get_attachment_image_src( get_post_thumbnail_id( $post_ID ), 'large' ) : $image_data;

		}

		// Get our schema default logo as fallback, if it was defined
		if ( $image_data == false && !empty( self::$plugin_data['wp_schema_site_logo'] ) ) {
			$image_data	= wp_get_attachment_image_src( self::$plugin_data['wp_schema_site_logo'], 'full' );
		}

		if ( false !== $image_data ) {

			echo '<meta property="og:image" content="'. esc_attr( $image_data[0] ) .'" />'. "\n\t\t";
			echo '<meta property="og:image:width" content="'. $image_data[1] .'" />'. "\n\t\t";
			echo '<meta property="og:image:height" content="'. $image_data[2] .'" />'. "\n\t\t";

		}

	}

	public function generate_og_url() {

		$og_url	= get_permalink() ?: '';

		if ( $og_url != '' ) {
			echo '<meta property="og:url" content="'. esc_url( $og_url ) .'" />'. "\n\t\t";
		}

	}

	public function generate_og_type() {

		if ( is_single() ) {
			echo '<meta property="og:type" content="article">'. "\n\t\t";
		} else {
			echo '<meta property="og:type" content="website">'. "\n\t\t";
		}

	}

	public function generate_og_general_tags() {
		echo '<meta name="twitter:card" content="summary_large_image">'. "\n\t\t";
		echo '<meta property="og:site_name" content="'. get_bloginfo('name') .'">'. "\n\t\t";
	}

}

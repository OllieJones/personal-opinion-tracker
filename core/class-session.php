<?php

namespace Personal_Opinion_Tracker;

use WP_Query;

class Session {

	private Personal_Opinion_Tracker $core;
	public static string $slug = 'opinion-session';

	public function __construct( $core ) {
		$this->core = $core;

		add_action( 'init', [ $this, 'register_post_type' ] );


	}

	public static function enumerate( $exclude_historical = true ) {
		$result = array();
		global $post;
		$status = array( 'publish' );
		if ( ! $exclude_historical ) {
			$status[] = 'historical';
		}
		$args  = array(
			'post_type'   => self::$slug,
			'post_status' => $status
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$result[ $post->post_name ] = $post->post_title;
		}
		wp_reset_postdata();
		asort( $result );

		return $result;
	}

	/**
	 * Register our custom post type. post_from_email_prof .
	 *
	 * The user interface sometimes calls this a "template" and sometimes a "profile"
	 *
	 * @return void
	 *
	 */
	public function register_post_type() {
		register_post_type(
			self::$slug,
			array(
				'name'                 => __( 'Session', 'personal-opinion-tracker' ),
				'description'          => __( 'Sessions', 'personal-opinion-tracker' ),
				'labels'               => array(
					'menu_name'                => _x( 'Sessions', 'post type menu name', 'personal-opinion-tracker' ),
					'name'                     => _x( 'Sessions', 'post type general name', 'personal-opinion-tracker' ),
					'singular_name'            => _x( 'Session', 'post type singular name', 'personal-opinion-tracker' ),
					'add_new'                  => _x( 'Add New', 'issue', 'personal-opinion-tracker' ),
					'add_new_item'             => __( 'Add new session', 'personal-opinion-tracker' ),
					'new_item'                 => __( 'New session', 'personal-opinion-tracker' ),
					'edit_item'                => __( 'Edit session', 'personal-opinion-tracker' ),
					'view_item'                => __( 'View session', 'personal-opinion-tracker' ),
					'all_items'                => __( 'All sessions', 'personal-opinion-tracker' ),
					'search_items'             => __( 'Search sessions', 'personal-opinion-tracker' ),
					'parent_item_colon'        => __( ':', 'personal-opinion-tracker' ),
					'not_found'                => __( 'No legislative sessions found. Create one.', 'personal-opinion-tracker' ),
					'not_found_in_trash'       => __( 'No sessions found in Trash.', 'personal-opinion-tracker' ),
					'archives'                 => __( 'Archive of sessions', 'personal-opinion-tracker' ),
					'insert_into_item'         => __( 'Insert into session', 'personal-opinion-tracker' ),
					'uploaded_to_this_item'    => __( 'Uploaded to this session', 'personal-opinion-tracker' ),
					'filter_items_list'        => __( 'Filter list of legislative sessions', 'personal-opinion-tracker' ),
					'items_list_navigation'    => __( 'Session list navigation', 'personal-opinion-tracker' ),
					'items_list'               => __( 'Session list', 'personal-opinion-tracker' ),
					'item_published'           => __( 'Session published', 'personal-opinion-tracker' ),
					'item_published_privately' => __( 'Private legislative session activated', 'personal-opinion-tracker' ),
					'item_reverted_to_draft'   => __( 'Session deactivated', 'personal-opinion-tracker' ),
					'item_scheduled'           => __( 'Session scheduled for activation', 'personal-opinion-tracker' ),
					'item_updated'             => __( 'Session updated', 'personal-opinion-tracker' ),

				),
				'hierarchical'         => false,
				'public'               => true, //TODO not sure how these visibility parameters interact.
				'exclude_from_search'  => true,
				'publicly_queryable'   => false,
				'show_ui'              => true,
				'show_in_menu'         => true, //TODO change this to put ui in submenu
				'show_in_nav_menus'    => false,
				'show_in_admin_bar'    => false,
				'show_in_rest'         => false, /* No block editor support */
				'menu_position'        => 42,
				'menu_icon'            => 'dashicons-bank',
				'map_meta_cap'         => true,
				'supports'             => array(
					'title',
					'editor',
					'revisions',
					'author',
					'custom_fields',
				),
				'register_meta_box_cb' => [ $this, 'make_meta_boxes' ],
				'has_archive'          => true,
				'rewrite'              => array( 'slug' => self::$slug ),
				'query_var'            => self::$slug,
				'can_export'           => true,
				'delete_with_user'     => false,
				'template'             => array(),

			)
		);
	}

	public function make_meta_boxes( $post ) {
		wp_enqueue_style( 'issue',
			$this->core->url . 'assets/css/issue.css',
			[],
			$this->core->version );

		wp_enqueue_script( 'issue',
			$this->core->url . 'assets/js/issue.js',
			[],
			$this->core->version );
	}

}
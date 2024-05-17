<?php

namespace Personal_Opinion_Tracker;

use WP_Post;
use WP_Query;

class Session {

	private Personal_Opinion_Tracker $core;
	public static string $slug = 'opinion-session';

	public function __construct( $core ) {
		$this->core = $core;

		add_action( 'init', [ $this, 'register_post_type' ] );
		add_action( 'edit_post' . '_' . self::$slug, [ $this, 'save_metadata' ], 10, 2 );
		add_filter( 'the_title', [ $this, 'the_title' ], 10, 2 );

	}

	public static function enumerate( $exclude_historical = true ) {
		$result = array();
		global $post;
		$status = array( 'publish' );
		$args   = array(
			'post_type'   => self::$slug,
			'post_status' => $status
		);
		$query  = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$historical = '' !== get_post_meta( $post->ID, self::$slug . '-historical', true );
			if ( ! $exclude_historical || ! $historical ) {
				$title = get_the_title($post->ID);
				$result[ $post->post_name ] = $title;
			}
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

	/**
     *  Filter the titles of "session" items to append (Historical) when they're no longer current
     *
	 * @param string $title
	 * @param int $post_id
	 *
	 * @return string
	 */
	public function the_title( string $title, int $post_id ): string {
        if (self::$slug === get_post_type($post_id)) {
	        $historical = '' !== get_post_meta( $post_id, self::$slug . '-historical', true );
	        if ( $historical ) {
		        return $title . ' ' . __( '(Historical)', 'personal-opinion-tracker' );
	        }
        }

		return $title;
	}

	public function make_meta_boxes( $post ) {

		wp_enqueue_script( 'issue',
			$this->core->url . 'assets/js/issue.js',
			[],
			$this->core->version );

		add_meta_box(
			'issue',
			__( 'This session', 'personal-opinion-tracker' ),
			array( $this, 'session_meta_box' ),
			null,
			'normal', /* advanced|normal|side */
			'high',
			null
		);

	}

	public function session_meta_box( $post, $callback_args ) {
		$historical = '' !== get_post_meta( $post->ID, self::$slug . '-historical', true );
		?>
        <table class="issue">
            <tr>
                <td>
                    <label for="historical"><?php esc_html_e( 'This session is', 'personal-opinion-tracker' ) ?></label>
                </td>
                <td>
                    <select id="historical" name="historical">
						<?php
						$selected = $historical ? '' : 'selected';
						echo '<option value="" ' . $selected . '>' . esc_html__( 'Active', 'personal-opinion-tracker' ) . '</option>' . PHP_EOL;
						$selected = $historical ? 'selected' : '';
						echo '<option value="historical" ' . $selected . '>' . esc_html__( 'Historical', 'personal-opinion-tracker' ) . '</option>' . PHP_EOL;
						?>
                    </select>
                </td>
            </tr>
        </table>
		<?php
	}

	/**
	 * Fires once a post has been saved.
	 *
	 * The dynamic portion of the hook name, `$post->post_type`, refers to
	 * the post type slug.
	 *
	 * @param int $profile_id Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @since 3.7.0
	 *
	 */

	public function save_metadata( $post_id, $post ) {

		if ( isset ( $_POST['action'] ) && 'editpost' !== $_POST['action'] ) {
			return;
		}
		if ( self::$slug !== $post->post_type ) {
			return;
		}
		if ( ! $post_id ) {
			return;
		}
		$historical = array_key_exists( 'historical', $_POST ) ? $_POST['historical'] : null;
		if ( null === $historical || '' === $historical ) {
			delete_post_meta( $post_id, self::$slug . '-historical' );
		} else {
			update_post_meta( $post_id, self::$slug . '-historical', $historical );
		}
	}

}
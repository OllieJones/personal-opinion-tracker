<?php

namespace Personal_Opinion_Tracker;

class Issue {

	private Personal_Opinion_Tracker $core;
	private string $slug;

	public function __construct (  $core ) {
		$this->core = $core;
		$this->slug = 'opinion-issue';

		add_action( 'init', [ $this, 'register_post_type' ] );


	}

	/**
	 * Register our custom post type. post_from_email_prof .
	 *
	 * The user interface sometimes calls this a "template" and sometimes a "profile"
	 *
	 * @return void
	 *
	 */
	public function register_post_type()
	{
		/** @noinspection SqlNoDataSourceInspection */
		register_post_type(
			$this->slug,
			array(
				'name' => __( 'Issue', 'personal-opinion-tracker' ),
				'description' => __( 'Issues', 'personal-opinion-tracker' ),
				'labels' => array(
					'menu_name' => _x( 'Issues', 'post type menu name', 'personal-opinion-tracker' ),
					'name' => _x( 'Issues', 'post type general name', 'personal-opinion-tracker' ),
					'singular_name' => _x( 'Issue', 'post type singular name', 'personal-opinion-tracker' ),
					'add_new' => _x( 'Add New', 'issue', 'personal-opinion-tracker' ),
					'add_new_item' => __( 'Add new issue', 'personal-opinion-tracker' ),
					'new_item' => __( 'New issue', 'personal-opinion-tracker' ),
					'edit_item' => __( 'Edit issue', 'personal-opinion-tracker' ),
					'view_item' => __( 'View issue', 'personal-opinion-tracker' ),
					'all_items' => __( 'All issues', 'personal-opinion-tracker' ),
					'search_items' => __( 'Search issues', 'personal-opinion-tracker' ),
					'parent_item_colon' => __( ':', 'personal-opinion-tracker' ),
					'not_found' => __( 'No issues found. Create one.', 'personal-opinion-tracker' ),
					'not_found_in_trash' => __( 'No issues found in Trash.', 'personal-opinion-tracker' ),
					'archives' => __( 'Archive of issues', 'personal-opinion-tracker' ),
					'insert_into_item' => __( 'Insert into issu', 'personal-opinion-tracker' ),
					'uploaded_to_this_item' => __( 'Uploaded to this issue', 'personal-opinion-tracker' ),
					'filter_items_list' => __( 'Filter issues list', 'personal-opinion-tracker' ),
					'items_list_navigation' => __( 'Issue list navigation', 'personal-opinion-tracker' ),
					'items_list' => __( 'Issue list', 'personal-opinion-tracker' ),
					'item_published' => __( 'Issue published', 'personal-opinion-tracker' ),
					'item_published_privately' => __( 'Private issue activated', 'personal-opinion-tracker' ),
					'item_reverted_to_draft' => __( 'Issuue deactivated', 'personal-opinion-tracker' ),
					'item_scheduled' => __( 'Issue scheduled for activation', 'personal-opinion-tracker' ),
					'item_update' => __( 'Issue updated', 'personal-opinion-tracker' ),

				),
				'hierarchical' => false,
				'public' => true, //TODO not sure how these visibility parameters interact.
				'exclude_from_search' => true,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => true, //TODO change this to put ui in submenu
				'show_in_nav_menus' => false,
				'show_in_admin_bar' => false,
				'show_in_rest' => false, /* No block editor support */
				'menu_position' => 90,
				'menu_icon' => 'dashicons-media-document',
				'map_meta_cap' => true,
				'supports' => array(
					'title',
					'editor',
					'revisions',
					'author',
					'custom_fields',
				),
				'taxonomies' => array( 'category', 'post_tag' ),
				'register_meta_box_cb' => [ $this, 'make_meta_boxes' ],
				'has_archive' => true,
				'rewrite' => array( 'slug' => $this->slug ),
				'query_var' => $this->slug,
				'can_export' => true,
				'delete_with_user' => false,
				'template' => array(),

			)
		);
	}

	public function make_meta_boxes( $post )
	{
		wp_enqueue_style( 'issue',
			$this->core->url . 'assets/css/issue.css',
			[],
			$this->core->version );

		wp_enqueue_script( 'issue',
			$this->core->url . 'assets/js/issue.js',
			[],
			$this->core->version );

		//TODO scaffolding.
		$data = array ('sessions' => 'Parliament 2022|Parliament 2024|BC Legislative Assembly 2024|TODO',
                       'parties' => 'Conservatives|Libertarians|Liberals|Greens|Whigs|No Labels|TO DO' );
		add_meta_box(
			'issue',
			__( 'This issue', 'personal-opinion-tracker' ),
			array( $this, 'issue_meta_box' ),
			null,
			'normal', /* advanced|normal|side */
			'high',
			$data
		);

		remove_meta_box( 'generate_layout_options_meta_box', null, 'side' );
	}

	public function issue_meta_box( $post, $callback_args ) 	{
		$sessions = explode('|', $callback_args['args']['sessions']);
		$parties = explode('|', $callback_args['args']['parties']);

		?>
		<table class="issue">

			<tr>
				<td>
					<label for="session"><?php esc_html_e( 'Session', 'personal-opinion-tracker' ) ?></label>
				</td>
				<td>
					<select id="session" name="session">
						<?php
                        echo '<option value="">&mdash;' . esc_html__('Choose a session', 'personal-opinion-tracker') . '&mdash;</option>' . PHP_EOL;
						foreach ($sessions as $session) {
							echo '<option value = "' . esc_attr( $session ) . '">' . esc_html( $session ) . '</option>' . PHP_EOL;
						}
						?>
					</select>
				</td>
			</tr>
            <tr>
                <td>
                    <label for="supporters"><?php esc_html_e( 'Supporters', 'personal-opinion-tracker' ) ?></label>
                </td>
                <td id="supporters">
                    <?php
						foreach ($parties as $party) {
							echo '<input type="checkbox" name="supporters[]" value = "' . esc_attr( $party ) . '">' . esc_html( $party ) . '</input><br>' . PHP_EOL;
						}
						?>
                </td>
            </tr>
            <tr>
                <td>
                    <label for="opponents"><?php esc_html_e( 'Opponents', 'personal-opinion-tracker' ) ?></label>
                </td>
                <td id="opponents">
					<?php
					foreach ($parties as $party) {
						echo '<input type="checkbox" name="opponents[]" value = "' . esc_attr( $party ) . '">' . esc_html( $party ) . '</input><br>' . PHP_EOL;
					}
					?>
                </td>
            </tr>
        </table>
		<?php
	}
}
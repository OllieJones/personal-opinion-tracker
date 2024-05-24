<?php

  namespace Personal_Opinion_Tracker;

  use Exception;
  use WP_Post;
  use WP_Query;

  class Issue {

    private Personal_Opinion_Tracker $core;
    public static string $slug = 'opinion-issue';

    public static function enumerate() {
      $result = array();
      global $post;
      $args  = array(
        'post_type'   => self::$slug,
        'post_status' => 'publish'
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


    public function __construct( $core ) {
      $this->core = $core;

      add_filter( 'manage_' . self::$slug . '_posts_columns', [ $this, 'manage_posts_columns' ] );
      add_action( 'manage_' . self::$slug . '_posts_custom_column', [ $this, 'manage_posts_custom_column' ], 10, 2 );

      add_action( 'init', [ $this, 'register_post_type' ] );
      add_action( 'edit_post' . '_' . self::$slug, [ $this, 'save_metadata' ], 10, 2 );

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
          'name'                 => __( 'Issue', 'personal-opinion-tracker' ),
          'description'          => __( 'Issues', 'personal-opinion-tracker' ),
          'labels'               => array(
            'menu_name'                => _x( 'Issues', 'post type menu name', 'personal-opinion-tracker' ),
            'name'                     => _x( 'Issues', 'post type general name', 'personal-opinion-tracker' ),
            'singular_name'            => _x( 'Issue', 'post type singular name', 'personal-opinion-tracker' ),
            'add_new'                  => _x( 'Add New', 'issue', 'personal-opinion-tracker' ),
            'add_new_item'             => __( 'Add new issue', 'personal-opinion-tracker' ),
            'new_item'                 => __( 'New issue', 'personal-opinion-tracker' ),
            'edit_item'                => __( 'Edit issue', 'personal-opinion-tracker' ),
            'view_item'                => __( 'View issue', 'personal-opinion-tracker' ),
            'all_items'                => __( 'All issues', 'personal-opinion-tracker' ),
            'search_items'             => __( 'Search issues', 'personal-opinion-tracker' ),
            'not_found'                => __( 'No issues found. Create one.', 'personal-opinion-tracker' ),
            'not_found_in_trash'       => __( 'No issues found in Trash.', 'personal-opinion-tracker' ),
            'archives'                 => __( 'Archive of issues', 'personal-opinion-tracker' ),
            'insert_into_item'         => __( 'Insert into issu', 'personal-opinion-tracker' ),
            'uploaded_to_this_item'    => __( 'Uploaded to this issue', 'personal-opinion-tracker' ),
            'filter_items_list'        => __( 'Filter issues list', 'personal-opinion-tracker' ),
            'items_list_navigation'    => __( 'Issue list navigation', 'personal-opinion-tracker' ),
            'items_list'               => __( 'Issue list', 'personal-opinion-tracker' ),
            'item_published'           => __( 'Issue published', 'personal-opinion-tracker' ),
            'item_published_privately' => __( 'Private issue activated', 'personal-opinion-tracker' ),
            'item_reverted_to_draft'   => __( 'Issuue deactivated', 'personal-opinion-tracker' ),
            'item_scheduled'           => __( 'Issue scheduled for activation', 'personal-opinion-tracker' ),
            'item_updated'             => __( 'Issue updated', 'personal-opinion-tracker' ),

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
          'menu_position'        => 38,
          'menu_icon'            => 'dashicons-media-document',
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

      add_meta_box(
        'issue',
        __( 'This issue', 'personal-opinion-tracker' ),
        array( $this, 'issue_meta_box' ),
        null,
        'normal', /* advanced|normal|side */
        'high',
        null
      );

      remove_meta_box( 'generate_layout_options_meta_box', null, 'side' );
    }

    public function issue_meta_box( $post, $callback_args ) {
      $sessions       = Session::enumerate( false );
      $session_choice = get_post_meta( $post->ID, $this->core->slug . '-session', true );
      $parties        = Party::enumerate();
      $supports       = array();
      $opposes        = array();
      foreach ( $parties as $slug => $name ) {
        $support = get_post_meta( $post->ID, $this->core->slug . '-supports-' . $slug, true );
        if ( '' !== $support ) {
          $supports [ $slug ] = $support;
        }
        $oppose = get_post_meta( $post->ID, $this->core->slug . '-opposes-' . $slug, true );
        if ( '' !== $oppose ) {
          $opposes [ $slug ] = $oppose;
        }
      }
      ?>
      <table class="issue">
        <tr>
          <td>
            <label for="session"><?php esc_html_e( 'Session', 'personal-opinion-tracker' ) ?></label>
          </td>
          <td colspan="3">
            <select id="session" name="session">
              <?php
                $selected = '' === $session_choice ? 'selected' : '';
                echo '<option value="" ' . $selected . '>&mdash;' . esc_html__( 'Choose a session', 'personal-opinion-tracker' ) . '&mdash;</option>' . PHP_EOL;
                foreach ( $sessions as $slug => $name ) {
                  $selected = $slug === $session_choice ? 'selected' : '';
                  echo '<option value = "' . esc_attr( $slug ) . '" ' . $selected . '>' . esc_html( $name ) . '</option>' . PHP_EOL;
                }
              ?>
            </select>
          </td>
        </tr>
        <tr class="header">
          <?php
            echo '<td class="center">' . esc_html( 'Party', 'personal-opinion-tracker' ) . '</td>' . PHP_EOL;
            echo '<td class="center">' . esc_html( 'Supports', 'personal-opinion-tracker' ) . '</td>' . PHP_EOL;
            echo '<td class="center">' . esc_html( 'Opposes', 'personal-opinion-tracker' ) . '</td>' . PHP_EOL;
          ?>
          <td></td>
        </tr>
        <?php
          foreach ( $parties as $slug => $name ) {
            echo '<tr>';
            echo '<td>' . esc_html( $name ) . '</td>';

            $checked = array_key_exists( $slug, $supports ) ? 'checked' : '';
            echo '<td class="center"><input type="checkbox" name="supports[]" value = "' . esc_attr( $slug ) . '"' . $checked . '/></td>' . PHP_EOL;
            $checked = array_key_exists( $slug, $opposes ) ? 'checked' : '';
            echo '<td class="center"><input type="checkbox" name="opposes[]" value = "' . esc_attr( $slug ) . '"' . $checked . '/></td>' . PHP_EOL;
            echo '<td></td></tr>' . PHP_EOL;
          }
        ?>
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
      $session = array_key_exists( 'session', $_POST ) ? $_POST['session'] : null;
      if ( null === $session ) {
        delete_post_meta( $post_id, $this->core->slug . '-session' );
      } else {
        update_post_meta( $post_id, $this->core->slug . '-session', $session );
      }

      foreach ( array( 'supports', 'opposes' ) as $action ) {
        $parties = array();
        if ( array_key_exists( $action, $_POST ) && is_array( $_POST[ $action ] ) ) {
          $items = $_POST[ $action ];
          foreach ( $items as $item ) {
            $parties[ $item ] = 1;
          }
        }
        $names = array_merge( Party::enumerate(), $parties );
        foreach ( $names as $slug => $name ) {
          if ( array_key_exists( $slug, $parties ) ) {
            update_post_meta( $post_id, $this->core->slug . '-' . $action . '-' . $slug, 1 );
          } else {
            delete_post_meta( $post_id, $this->core->slug . '-' . $action . '-' . $slug );
          }
        }
      }
    }

    public function manage_posts_columns( $columns ) {
      wp_enqueue_style( 'issue',
        $this->core->url . 'assets/css/issue.css',
        [],
        $this->core->version );

      wp_enqueue_script( 'issue',
        $this->core->url . 'assets/js/issue.js',
        [],
        $this->core->version );

      $result = array();
      foreach ( $columns as $key => $value ) {
        if ( 'author' === $key ) {
          $result['shortcode'] = __( 'Shortcode', 'personal-opinion-tracker' );
        }
        $result[ $key ] = $value;
      }

      return $result;
    }


    /**
     *  Emits the content for a [custom column](https://developer.wordpress.org/reference/hooks/manage_posts_custom_column/).
     *
     * @param string $column The name of the column.
     * @param int $post_id The post ID of the current row's post.
     *
     * @return void
     * @throws Exception
     */
    public function manage_posts_custom_column( $column, $post_id ) {
      switch ( $column ) {
        case 'shortcode' :
          $post = get_post( $post_id );
          /*  [personal-opinion i="disarm-quails"]  */
          echo '<input type="text" class="shortcode" readonly  size="25" value="[personal-opinion i=&quot;' . esc_attr( $post->post_name ) . '&quot;]">';
          break;
        default:
          throw new Exception ( 'bogus custom column name: ' . $column );
      }
    }
  }
<?php

  namespace Personal_Opinion_Tracker;

  use Exception;
  use WP_REST_Controller;
  use WP_REST_Request;
  use WP_REST_Response;

  class Vote_Controller extends WP_REST_Controller {
    /**
     * @var mixed|string
     */
    protected $namespace;
    private string $version;

    /**
     * Initial setup for REST API endpoints.
     *
     * @return void
     */
    public function init() {
      $this->version   = '1';
      $this->namespace = 'personal-opinion-tracker';
      $this->add_hooks();
    }

    /**
     * Handle vote request
     *
     * @param WP_REST_Request $req
     *
     * @return WP_REST_Response
     * @throws Exception When something is wrong.
     */
    public function vote( WP_REST_Request $req ): WP_REST_Response {
      require_once ABSPATH . 'wp-admin/includes/admin.php';
      $data = json_decode( $req->get_body() );

      $issue_name   = $data->name;
      $action       = $data->action;
      $user         = (int) $data->user;
      $checked      = $data->checked;
      $current_user = get_current_user_id();
      if ( $user !== $current_user ) {
        throw new Exception( 'bogus user id: ' . $user . ' does not match current user: ' . $current_user );
      }
      if ( 'checked' === $checked ) {
        update_user_meta( $user, 'opinion-vote-' . $action . '-' . $issue_name, 1 );
      } elseif ( 'unchecked' === $checked ) {
        delete_user_meta( $user, 'opinion-vote-' . $action . '-' . $issue_name );
      } else {
        throw new Exception ( 'bogus checked choice: ', $checked );
      }

      return new WP_REST_Response( 'OK' );
    }


    public function user_can_read( WP_REST_Request $req ): bool {
      return get_current_user_id() > 0 && current_user_can( 'read' );
    }

    /**
     * __construct helper function.
     *
     * @return void
     */
    private function add_hooks() {

      add_action( 'rest_api_init', function () {

        /* POST https://example.com/wp-json/personal-opinion-tracker/v1/vote */
        register_rest_route( "$this->namespace/v$this->version", "/vote", array(
          array(
            'callback'            => array( $this, 'vote' ),
            'methods'             => 'POST',
            'permission_callback' => array( $this, 'user_can_read' ),
          ),
        ) );

      } );
    }

  }


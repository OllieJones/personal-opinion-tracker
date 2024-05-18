<?php

namespace Personal_Opinion_Tracker;

use Exception;
use WP_Query;

class Shortcode {

	public $core;

	public function __construct( $core ) {
		$this->core = $core;

		add_action( 'init', [ $this, 'init' ] );
	}

	public function init() {
		add_shortcode( 'personal-opinion', [ $this, 'shortcode' ] );
	}

	public function shortcode( $atts, $content, $shortcode_tag ) {
		wp_enqueue_style( 'opinion-tracker',
			$this->core->url . 'assets/css/shortcode.css',
			[],
			$this->core->version );
		wp_enqueue_script( 'opinion-tracker',
			$this->core->url . 'assets/js/shortcode.js',
			[],
			$this->core->version );
		try {
			$atts = shortcode_atts( array(
				'class'   => 'personal-opinion',
				'id'      => '',
				'i'       => null,
				'iss'     => null,
				'issueid' => null,
				'iid'     => null,
				'title'   => __( 'Your Opinion Please', 'personal-opinion-tracker' ),
				'support_text' => __( 'Support', 'personal-opinion-tracker' ),
				'oppose_text'  => __( 'Oppose', 'personal-opinion-tracker' ),
			), $atts );

			$issue_name = $atts['i'];
			$issue_id = $atts['iid'];

			if ( ! is_numeric( $issue_id ) ) {
				/* look up issue by name (slug) */
				$issue_id = $this->lookup( $issue_name );
			}
			$issue = get_post( $issue_id );

			$issue_name = $issue->post_name;
			$votes      = $this->get_votes( $issue_name );

			return $this->render( $atts, $issue, $votes );

		} catch ( Exception $ex ) {
			return '<code>' . $ex->getMessage() . ' ' . $ex->getFile() . ':' . $ex->getLine() . '</code>';
		}
	}

	private function render( $atts, $issue, $votes ) {

		$atts['id'] = null === $atts['id'] ? 'post_' . $issue->ID : $atts['id'];
		ob_start();
		?>
        <div id="<?php echo esc_attr( $atts['id'] ) ?>"
             class="personal_opinion_widget <?php echo esc_attr( $atts['class'] ) ?>">
            <div class="inner">
                <div class="head">
                    <p class="caption"><?php echo esc_html( $atts['title'] ) ?></p>
                    <p class="issue"><?php echo esc_html( $issue->post_title ) ?></p>
                </div>
                <div class="votes">
                    <?php echo $this->render_shortcode('supports',$issue, $votes)  ?>
                    <div><?php echo esc_html( $atts['support_text'] ) ?></div>
                    <div class="spacer"></div>
                    <div><?php echo esc_html( $atts['oppose_text'] ) ?></div>
	                <?php echo $this->render_shortcode('opposes',$issue, $votes)  ?>


                </div>
            </div>
        </div>
		<?php
		return ob_get_clean();

	}

    private function render_shortcode ($action, $issue, $votes) {
        $result = '<div class="check ';
        $result .= $action;
        $result .= '"><input type="checkbox" ';
        $result .= array_key_exists($action, $votes) && is_numeric($votes[$action]) && 1 == $votes[$action] ? 'checked' : '';
	    $result .= ' data-id="' . esc_attr($issue->ID) . '" ';
	    $result .= ' data-action="' . esc_attr($action) . '" ';
	    $result .= ' data-user="' . esc_attr(get_current_user_id()) . '" ';
        $result .= '/></div>';
        return $result;

    }

	/**
     * Get the current user's votes for the named issue.
     *
	 * @param $issue_name
	 *
	 * @return array Like ['support' => 1]
	 */
	private function get_votes( $issue_name ): array {
		$votes = array();
		$user  = get_current_user_id();
		if ( $user <= 0 ) {
			$votes['priv'] = 'no';

			return $votes;
		}
		if ( ! current_user_can( 'read' ) ) {
			$votes['priv'] = 'no';

			return $votes;
		}
		foreach ( array( 'supports', 'opposes' ) as $action ) {
			$v = get_user_meta( $user, 'opinion-vote-' . $action . '-' . $issue_name, true );
			$v = '' === $v ? 0 : (int) $v;

			$votes[ $action ] = $v;
		}
        return $votes;
	}

	private function set_votes( $issue_name, $votes ) : void {
		$user = get_current_user_id();
		if ( $user <= 0 ) {
			return;
		}
		if ( ! current_user_can( 'read' ) ) {
			return;
		}
		$actions = array_merge( array_keys( $votes ), array( 'supports', 'opposes' ) );

		foreach ( $actions as $action ) {
			if ( array_key_exists( $action, $votes ) ) {
				update_user_meta( $user, 'opinion-vote-' . $action . '-' . $issue_name, 1 );
			} else {
				delete_user_meta( $user, 'opinion-vote-' . $action . '-' . $issue_name );
			}
		}
	}


	private function lookup( $name, $post_type = 'opinion-issue' ) : int {
		$result = null;
		global $post;
		$args  = array(
			'name'        => $name,
			'post_type'   => $post_type,
			'post_status' => 'publish'
		);
		$query = new WP_Query( $args );
		while ( $query->have_posts() ) {
			$query->the_post();
			$result = $post->ID;
		}
		wp_reset_postdata();

		return $result;

	}


}
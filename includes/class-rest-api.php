<?php
/**
 * REST API class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_REST_API class.
 *
 * @since 1.0.0
 */
class BPSFC_REST_API {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'register_routes' ) );
    }

    /**
     * Register REST API routes.
     *
     * @since 1.0.0
     */
    public function register_routes() {
        register_rest_route( 'bp-smartfeed/v1', '/feed', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_feed' ),
            'permission_callback' => array( $this, 'get_feed_permissions_check' ),
            'args' => array(
                'user_id' => array(
                    'required' => true,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ),
                'page' => array(
                    'default' => 1,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param );
                    },
                ),
                'per_page' => array(
                    'default' => 20,
                    'validate_callback' => function( $param ) {
                        return is_numeric( $param ) && $param <= 100;
                    },
                ),
                'feed_type' => array(
                    'default' => 'smart',
                    'validate_callback' => function( $param ) {
                        return in_array( $param, array( 'smart', 'standard' ) );
                    },
                ),
            ),
        ) );

        register_rest_route( 'bp-smartfeed/v1', '/interests', array(
            'methods' => 'GET',
            'callback' => array( $this, 'get_interests' ),
            'permission_callback' => array( $this, 'get_interests_permissions_check' ),
        ) );
    }

    /**
     * Get feed.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_feed( $request ) {
        $user_id = $request->get_param( 'user_id' );
        $page = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $feed_type = $request->get_param( 'feed_type' );

        // Get activities using BP functions.
        $args = array(
            'per_page' => $per_page,
            'page' => $page,
            'feed_type' => $feed_type,
        );

        if ( bp_has_activities( $args ) ) {
            $activities = array();
            while ( bp_activities() ) {
                bp_the_activity();
                $activity = new BP_Activity_Activity( bp_get_activity_id() );
                $activities[] = array(
                    'id' => $activity->id,
                    'content' => $activity->content,
                    'score' => get_post_meta( $activity->id, 'bpsfc_score', true ),
                    'user' => array(
                        'id' => $activity->user_id,
                        'name' => bp_core_get_user_displayname( $activity->user_id ),
                    ),
                    'engagement' => array(
                        'likes' => $activity->favorite_count,
                        'comments' => $activity->children ? count( $activity->children ) : 0,
                    ),
                    'explanation' => $this->get_explanation( $activity->id, $user_id ),
                );
            }

            return new WP_REST_Response( array(
                'success' => true,
                'data' => array(
                    'activities' => $activities,
                    'pagination' => array(
                        'current_page' => $page,
                        'total_pages' => ceil( bp_get_activity_count() / $per_page ),
                        'total_items' => bp_get_activity_count(),
                    ),
                ),
            ), 200 );
        }

        return new WP_REST_Response( array(
            'success' => false,
            'message' => 'No activities found.',
        ), 404 );
    }

    /**
     * Get feed permissions check.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request.
     * @return bool
     */
    public function get_feed_permissions_check( $request ) {
        return is_user_logged_in();
    }

    /**
     * Get interests.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request.
     * @return WP_REST_Response
     */
    public function get_interests( $request ) {
        $user_id = get_current_user_id();
        $db = new BPSFC_Database();
        $interests = $db->get_user_interests( $user_id );

        return new WP_REST_Response( array(
            'success' => true,
            'data' => $interests,
        ), 200 );
    }

    /**
     * Get interests permissions check.
     *
     * @since 1.0.0
     * @param WP_REST_Request $request Request.
     * @return bool
     */
    public function get_interests_permissions_check( $request ) {
        return is_user_logged_in();
    }

    /**
     * Get explanation for activity.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @return string
     */
    private function get_explanation( $activity_id, $user_id ) {
        // Simple explanation logic.
        return __( 'High engagement from your network', 'bp-smart-feed-curator' );
    }

}

<?php
/**
 * AJAX handler class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Ajax_Handler class.
 *
 * @since 1.0.0
 */
class BPSFC_Ajax_Handler {
    public function __construct() {
        add_action( 'wp_ajax_bpsfc_toggle_feed', array( $this, 'toggle_feed' ) );
        add_action( 'wp_ajax_nopriv_bpsfc_toggle_feed', array( $this, 'toggle_feed' ) );
        add_action( 'wp_ajax_bpsfc_load_feed', array( $this, 'load_feed' ) );
        add_action( 'wp_ajax_nopriv_bpsfc_load_feed', array( $this, 'load_feed' ) );
    }

    /**
     * Toggle feed type.
     *
     * @since 1.0.0
     */
    public function toggle_feed() {
        check_ajax_referer( 'bpsfc_toggle_feed', 'nonce' );

        $feed_type = sanitize_text_field( $_POST['feed_type'] );
        $user_id = get_current_user_id();

        if ( $user_id ) {
            update_user_meta( $user_id, 'bpsfc_preferred_feed_type', $feed_type );
        }

        wp_send_json_success( array( 'feed_type' => $feed_type ) );
    }

    /**
     * Load feed.
     *
     * @since 1.0.0
     */
    public function load_feed() {
        check_ajax_referer( 'bpsfc_load_feed', 'nonce' );

        $page = intval( $_POST['page'] );
        $user_id = get_current_user_id();

        // Load activities based on feed type
        $feed_type = get_user_meta( $user_id, 'bpsfc_preferred_feed_type', true ) ?: 'smart';

        if ( $feed_type === 'smart' ) {
            $activities = $this->get_smart_feed( $user_id, $page );
        } else {
            $activities = $this->get_standard_feed( $user_id, $page );
        }

        wp_send_json_success( array( 'activities' => $activities ) );
    }

    /**
     * Get smart feed.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @param int $page Page number.
     * @return array
     */
    private function get_smart_feed( $user_id, $page ) {
        if ( ! function_exists( 'bp_has_activities' ) ) {
            return array();
        }

        $per_page = apply_filters( 'bpsfc_activities_per_page', 20 );
        
        // Query activities with BuddyPress
        $args = array(
            'display_comments' => 'threaded',
            'show_hidden' => false,
            'per_page' => $per_page,
            'page' => $page,
            'feed_type' => 'smart',
        );

        // Get activities
        if ( bp_has_activities( $args ) ) {
            $activities = array();
            $scoring_engine = new BPSFC_Scoring_Engine();
            
            while ( bp_activities() ) {
                bp_the_activity();
                
                $activity_id = bp_get_activity_id();
                $score = $scoring_engine->calculate_score( $activity_id, $user_id );
                
                $activities[] = array(
                    'id' => $activity_id,
                    'content' => bp_get_activity_content_body(),
                    'action' => bp_get_activity_action(),
                    'date' => bp_get_activity_date_recorded(),
                    'user_id' => bp_get_activity_user_id(),
                    'user_name' => bp_core_get_user_displayname( bp_get_activity_user_id() ),
                    'user_avatar' => bp_core_fetch_avatar( array(
                        'item_id' => bp_get_activity_user_id(),
                        'type' => 'thumb',
                        'html' => false,
                    ) ),
                    'score' => $score,
                    'permalink' => bp_get_activity_thread_permalink(),
                );
            }
            
            // Sort by score
            usort( $activities, function( $a, $b ) {
                return $b['score'] <=> $a['score'];
            } );
            
            return $activities;
        }

        return array();
    }

    /**
     * Get standard feed.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @param int $page Page number.
     * @return array
     */
    private function get_standard_feed( $user_id, $page ) {
        if ( ! function_exists( 'bp_has_activities' ) ) {
            return array();
        }

        $per_page = apply_filters( 'bpsfc_activities_per_page', 20 );
        
        // Query activities with BuddyPress - standard chronological order
        $args = array(
            'display_comments' => 'threaded',
            'show_hidden' => false,
            'per_page' => $per_page,
            'page' => $page,
        );

        // Get activities
        if ( bp_has_activities( $args ) ) {
            $activities = array();
            
            while ( bp_activities() ) {
                bp_the_activity();
                
                $activities[] = array(
                    'id' => bp_get_activity_id(),
                    'content' => bp_get_activity_content_body(),
                    'action' => bp_get_activity_action(),
                    'date' => bp_get_activity_date_recorded(),
                    'user_id' => bp_get_activity_user_id(),
                    'user_name' => bp_core_get_user_displayname( bp_get_activity_user_id() ),
                    'user_avatar' => bp_core_fetch_avatar( array(
                        'item_id' => bp_get_activity_user_id(),
                        'type' => 'thumb',
                        'html' => false,
                    ) ),
                    'permalink' => bp_get_activity_thread_permalink(),
                );
            }
            
            return $activities;
        }

        return array();
    }

}

<?php
/**
 * Engagement tracker class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Engagement_Tracker class.
 *
 * @since 1.0.0
 */
class BPSFC_Engagement_Tracker {

    /**
     * Database instance.
     *
     * @since 1.0.0
     * @var BPSFC_Database
     */
    private $db;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->db = new BPSFC_Database();
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Track likes/favorites.
        add_action( 'bp_activity_add_user_favorite', array( $this, 'track_favorite' ), 10, 2 );
        add_action( 'bp_activity_remove_user_favorite', array( $this, 'remove_favorite' ), 10, 2 );

        // Track comments.
        add_action( 'bp_activity_comment_posted', array( $this, 'track_comment' ), 10, 3 );
        add_action( 'bp_activity_delete_comment', array( $this, 'remove_comment' ), 10, 3 );

        // Track views.
        add_action( 'bp_before_activity_loop', array( $this, 'track_activity_views' ), 10 );
    }

    /**
     * Track favorite.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     */
    public function track_favorite( $activity_id, $user_id ) {
        $score = $this->db->get_activity_score( $activity_id, $user_id );
        $like_count = $score ? $score->like_count + 1 : 1;

        $this->db->upsert_activity_score( $activity_id, $user_id, array( 'like_count' => $like_count ) );
        $this->update_total_score( $activity_id, $user_id );
    }

    /**
     * Remove favorite.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     */
    public function remove_favorite( $activity_id, $user_id ) {
        $score = $this->db->get_activity_score( $activity_id, $user_id );
        if ( $score && $score->like_count > 0 ) {
            $like_count = $score->like_count - 1;
            $this->db->upsert_activity_score( $activity_id, $user_id, array( 'like_count' => $like_count ) );
            $this->update_total_score( $activity_id, $user_id );
        }
    }

    /**
     * Track comment.
     *
     * @since 1.0.0
     * @param int $comment_id Comment ID.
     * @param array $params Parameters.
     * @param object $activity Activity object.
     */
    public function track_comment( $comment_id, $params, $activity ) {
        $activity_id = $activity->item_id;
        $user_id = $params['user_id'];

        $score = $this->db->get_activity_score( $activity_id, $user_id );
        $comment_count = $score ? $score->comment_count + 1 : 1;

        $this->db->upsert_activity_score( $activity_id, $user_id, array( 'comment_count' => $comment_count ) );
        $this->update_total_score( $activity_id, $user_id );
    }

    /**
     * Remove comment.
     *
     * @since 1.0.0
     * @param int $comment_id Comment ID.
     * @param array $params Parameters.
     * @param object $activity Activity object.
     */
    public function remove_comment( $comment_id, $params, $activity ) {
        $activity_id = $activity->item_id;
        $user_id = $params['user_id'];

        $score = $this->db->get_activity_score( $activity_id, $user_id );
        if ( $score && $score->comment_count > 0 ) {
            $comment_count = $score->comment_count - 1;
            $this->db->upsert_activity_score( $activity_id, $user_id, array( 'comment_count' => $comment_count ) );
            $this->update_total_score( $activity_id, $user_id );
        }
    }

    /**
     * Track activity views.
     *
     * @since 1.0.0
     */
    public function track_activity_views() {
        // Implementation for view tracking.
    }

    /**
     * Update total score.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     */
    private function update_total_score( $activity_id, $user_id ) {
        $score = $this->db->get_activity_score( $activity_id, $user_id );
        if ( ! $score ) {
            return;
        }

        $like_weight = get_option( 'bpsfc_like_weight', 2.0 );
        $comment_weight = get_option( 'bpsfc_comment_weight', 3.0 );
        $share_weight = get_option( 'bpsfc_share_weight', 5.0 );
        $view_weight = get_option( 'bpsfc_view_weight', 0.5 );

        $total_score = ( $score->like_count * $like_weight ) +
                       ( $score->comment_count * $comment_weight ) +
                       ( $score->share_count * $share_weight ) +
                       ( $score->view_count * $view_weight );

        $this->db->upsert_activity_score( $activity_id, $user_id, array( 'total_score' => $total_score ) );
        
        // Invalidate cache for this activity score
        $this->invalidate_score_cache( $activity_id, $user_id );
    }
    
    /**
     * Invalidate score cache for an activity.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     */
    private function invalidate_score_cache( $activity_id, $user_id ) {
        $cache_key = 'bpsfc_score_' . $activity_id . '_' . $user_id;
        delete_transient( $cache_key );
    }

}

<?php
/**
 * Database operations class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Database class.
 *
 * @since 1.0.0
 */
class BPSFC_Database {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        // Initialize database operations.
    }

    /**
     * Insert or update activity score.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @param array $data Score data.
     * @return bool|int
     */
    public function upsert_activity_score( $activity_id, $user_id, $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'bpsfc_activity_scores';

        $defaults = array(
            'like_count' => 0,
            'comment_count' => 0,
            'share_count' => 0,
            'view_count' => 0,
            'total_score' => 0.00,
        );

        $data = wp_parse_args( $data, $defaults );
        $data['activity_id'] = $activity_id;
        $data['user_id'] = $user_id;
        $data['last_updated'] = current_time( 'mysql' );

        return $wpdb->replace( $table, $data );
    }

    /**
     * Get activity score.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @return object|null
     */
    public function get_activity_score( $activity_id, $user_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'bpsfc_activity_scores';

        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE activity_id = %d AND user_id = %d",
                $activity_id,
                $user_id
            )
        );
    }

    /**
     * Insert or update user interest.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @param string $keyword Keyword.
     * @param array $data Interest data.
     * @return bool|int
     */
    public function upsert_user_interest( $user_id, $keyword, $data ) {
        global $wpdb;

        $table = $wpdb->prefix . 'bpsfc_user_interests';

        $defaults = array(
            'weight' => 1.00,
            'occurrences' => 1,
        );

        $data = wp_parse_args( $data, $defaults );
        $data['user_id'] = $user_id;
        $data['keyword'] = $keyword;
        $data['last_updated'] = current_time( 'mysql' );

        return $wpdb->replace( $table, $data );
    }

    /**
     * Get user interests.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @param int $limit Limit.
     * @return array
     */
    public function get_user_interests( $user_id, $limit = 50 ) {
        global $wpdb;

        $table = $wpdb->prefix . 'bpsfc_user_interests';

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM $table WHERE user_id = %d ORDER BY weight DESC LIMIT %d",
                $user_id,
                $limit
            )
        );
    }

}

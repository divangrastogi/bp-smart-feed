<?php
/**
 * Scoring engine class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Scoring_Engine class.
 *
 * @since 1.0.0
 */
class BPSFC_Scoring_Engine {

    /**
     * Calculate final score for an activity.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @return float
     */
    public function calculate_score( $activity_id, $user_id ) {
        // Check if caching is enabled
        $caching_enabled = get_option( 'bpsfc_enable_caching', 'yes' );
        $cache_duration = absint( get_option( 'bpsfc_cache_duration', 5 ) ) * MINUTE_IN_SECONDS;
        
        if ( 'yes' === $caching_enabled ) {
            $cache_key = 'bpsfc_score_' . $activity_id . '_' . $user_id;
            $cached_score = get_transient( $cache_key );
            
            if ( false !== $cached_score ) {
                return (float) $cached_score;
            }
        }
        
        $base_score = $this->get_base_score( $activity_id, $user_id );
        $time_decay = $this->get_time_decay_multiplier( $activity_id );
        $interest_boost = $this->get_interest_boost( $activity_id, $user_id );
        $freshness_bonus = $this->get_freshness_bonus( $activity_id );

        $final_score = $base_score * $time_decay + $interest_boost + $freshness_bonus;

        // Cache the score
        if ( 'yes' === $caching_enabled ) {
            set_transient( $cache_key, $final_score, $cache_duration );
        }

        return $final_score;
    }

    /**
     * Get base score.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @return float
     */
    private function get_base_score( $activity_id, $user_id ) {
        global $wpdb;

        $table = $wpdb->prefix . 'bpsfc_activity_scores';

        $score = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT total_score FROM $table WHERE activity_id = %d AND user_id = %d",
                $activity_id,
                $user_id
            )
        );

        return $score ? (float) $score : 0.0;
    }

    /**
     * Get time decay multiplier.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @return float
     */
    private function get_time_decay_multiplier( $activity_id ) {
        $activity = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
        if ( empty( $activity['activities'] ) ) {
            return 1.0;
        }

        $activity_date = strtotime( $activity['activities'][0]->date_recorded );
        $now = time();
        $hours_since = ( $now - $activity_date ) / 3600;

        // Handle negative hours (future dates) - treat as very recent
        if ( $hours_since < 0 ) {
            $hours_since = 0;
        }

        $decay_rate = get_option( 'bpsfc_time_decay_rate', 24 );

        return 1 / ( 1 + ( $hours_since / $decay_rate ) );
    }

    /**
     * Get interest boost.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     * @return float
     */
    private function get_interest_boost( $activity_id, $user_id ) {
        $db = new BPSFC_Database();
        $interests = $db->get_user_interests( $user_id );

        $activity = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
        if ( empty( $activity['activities'] ) ) {
            return 0.0;
        }

        $content = strtolower( $activity['activities'][0]->content );
        $boost = 0.0;

        foreach ( $interests as $interest ) {
            if ( stripos( $content, strtolower( $interest->keyword ) ) !== false ) {
                $boost += (float) $interest->weight;
            }
        }

        return $boost;
    }

    /**
     * Get freshness bonus.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @return float
     */
    private function get_freshness_bonus( $activity_id ) {
        $activity = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
        if ( empty( $activity['activities'] ) ) {
            return 0.0;
        }

        $activity_date = strtotime( $activity['activities'][0]->date_recorded );
        $now = time();
        $hours_since = ( $now - $activity_date ) / 3600;

        $threshold = get_option( 'bpsfc_freshness_threshold', 2 );

        return $hours_since < $threshold ? 10.0 : 0.0;
    }

}

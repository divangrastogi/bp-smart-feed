<?php
/**
 * Interest analyzer class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Interest_Analyzer class.
 *
 * @since 1.0.0
 */
class BPSFC_Interest_Analyzer {

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
        add_action( 'bp_activity_add_user_favorite', array( $this, 'analyze_favorite' ), 10, 2 );
        add_action( 'bp_activity_comment_posted', array( $this, 'analyze_comment' ), 10, 3 );
        add_action( 'bpsfc_daily_decay', array( $this, 'decay_interests' ) );
        
        if ( ! wp_next_scheduled( 'bpsfc_daily_decay' ) ) {
            wp_schedule_event( time(), 'daily', 'bpsfc_daily_decay' );
        }
    }

    /**
     * Analyze favorite activity.
     *
     * @since 1.0.0
     * @param int $activity_id Activity ID.
     * @param int $user_id User ID.
     */
    public function analyze_favorite( $activity_id, $user_id ) {
        $activity = bp_activity_get_specific( array( 'activity_ids' => $activity_id ) );
        if ( empty( $activity['activities'] ) ) {
            return;
        }

        $content = $activity['activities'][0]->content;
        $keywords = $this->extract_keywords( $content );

        foreach ( $keywords as $keyword ) {
            $this->update_user_interest( $user_id, $keyword, 1.5 );
        }
    }

    /**
     * Analyze comment.
     *
     * @since 1.0.0
     * @param int $comment_id Comment ID.
     * @param array $params Parameters.
     * @param object $activity Activity object.
     */
    public function analyze_comment( $comment_id, $params, $activity ) {
        $user_id = $params['user_id'];
        $content = $params['content'];
        $keywords = $this->extract_keywords( $content );

        foreach ( $keywords as $keyword ) {
            $this->update_user_interest( $user_id, $keyword, 1.2 );
        }
    }

    /**
     * Extract keywords from content.
     *
     * @since 1.0.0
     * @param string $content Content.
     * @return array
     */
    private function extract_keywords( $content ) {
        // Simple keyword extraction - remove stop words and split.
        $stop_words = array( 'the', 'is', 'at', 'which', 'on', 'a', 'an', 'and', 'or', 'but', 'in', 'with', 'for', 'to', 'of' );
        $content = wp_strip_all_tags( $content );
        $content = strtolower( $content );
        $words = preg_split( '/\s+/', $content );

        $keywords = array();
        foreach ( $words as $word ) {
            $word = trim( $word, '.,!?;:' );
            if ( strlen( $word ) > 3 && ! in_array( $word, $stop_words ) ) {
                $keywords[] = $word;
            }
        }

        return array_unique( $keywords );
    }

    /**
     * Update user interest.
     *
     * @since 1.0.0
     * @param int $user_id User ID.
     * @param string $keyword Keyword.
     * @param float $weight_increment Weight increment.
     */
    private function update_user_interest( $user_id, $keyword, $weight_increment = 1.0 ) {
        $interest = $this->db->get_user_interests( $user_id );
        $existing = null;

        foreach ( $interest as $item ) {
            if ( $item->keyword === $keyword ) {
                $existing = $item;
                break;
            }
        }

        if ( $existing ) {
            $new_weight = $existing->weight + $weight_increment;
            $new_occurrences = $existing->occurrences + 1;
            $this->db->upsert_user_interest( $user_id, $keyword, array(
                'weight' => $new_weight,
                'occurrences' => $new_occurrences,
            ) );
        } else {
            $this->db->upsert_user_interest( $user_id, $keyword, array(
                'weight' => $weight_increment,
                'occurrences' => 1,
            ) );
        }
    }
    /**
     * Decay user interests over time.
     *
     * @since 1.0.0
     */
    public function decay_interests() {
        global $wpdb;
        
        $table = $wpdb->prefix . 'bpsfc_user_interests';
        $now = current_time( 'timestamp' );
        
        // Get all interests
        $interests = $wpdb->get_results( "SELECT * FROM $table" );
        
        foreach ( $interests as $interest ) {
            $last_updated = strtotime( $interest->last_updated );
            $days_since = ( $now - $last_updated ) / DAY_IN_SECONDS;
            
            $new_weight = $interest->weight;
            
            if ( $days_since > 90 ) {
                // Remove
                $wpdb->delete( $table, array( 'id' => $interest->id ) );
            } elseif ( $days_since > 60 ) {
                $new_weight *= 0.7;
            } elseif ( $days_since > 30 ) {
                $new_weight *= 0.9;
            }
            
            if ( $new_weight !== $interest->weight ) {
                $wpdb->update( $table, array( 'weight' => $new_weight ), array( 'id' => $interest->id ) );
            }
        }
    }


}

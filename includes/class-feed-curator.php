<?php
/**
 * Feed curator class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Feed_Curator class.
 *
 * @since 1.0.0
 */
class BPSFC_Feed_Curator {

    /**
     * Scoring engine instance.
     *
     * @since 1.0.0
     * @var BPSFC_Scoring_Engine
     */
    private $scoring_engine;

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->scoring_engine = new BPSFC_Scoring_Engine();
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        // Legacy and general activity queries
        add_filter( 'bp_ajax_querystring', array( $this, 'modify_activity_query' ), 999, 2 );
        // Nouveau AJAX queries (Load More, heartbeat, filters)
        add_filter( 'bp_nouveau_ajax_querystring', array( $this, 'modify_nouveau_query' ), 999, 7 );
        // Rerank all activity results
        add_filter( 'bp_has_activities', array( $this, 'rerank_activities' ), 999, 3 );
        // Add score data attribute to activities
        add_filter( 'bp_get_activity_css_class', array( $this, 'add_score_to_activity_class' ), 10, 1 );
    }

    /**
     * Modify activity query.
     *
     * @since 1.0.0
     * @param string $query_string Query string.
     * @param string $object Object.
     * @return string
     */
    public function modify_activity_query( $query_string, $object ) {
        if ( 'activity' !== $object ) {
            return $query_string;
        }

        // Check if smart feed is enabled.
        if ( 'yes' !== get_option( 'bpsfc_enable_smart_feed', 'yes' ) ) {
            return $query_string;
        }

        // Get user's preferred feed type
        $user_id = get_current_user_id();
        $preferred_feed = get_user_meta( $user_id, 'bpsfc_preferred_feed_type', true );
        
        // Default to smart feed if no preference set
        if ( empty( $preferred_feed ) ) {
            $preferred_feed = get_option( 'bpsfc_default_feed_type', 'smart' );
        }

        // Only apply to smart feed
        if ( 'smart' !== $preferred_feed ) {
            return $query_string;
        }

        // Add custom ordering for smart feed.
        parse_str( $query_string, $args );
        
        // Mark this as a smart feed query - this will be used by rerank_activities
        $args['feed_type'] = 'smart';
        
        // Note: We don't modify the SQL query here
        // Instead, we'll sort results in the rerank_activities filter
        // This ensures ALL BuddyPress queries (initial load, Load More, filters, etc.) get sorted

        return http_build_query( $args );
    }
    
    /**
     * Modify Nouveau AJAX activity query.
     * This handles Load More, heartbeat, filters, and all AJAX requests in Nouveau theme.
     *
     * @since 1.0.0
     * @param string $query_string The query string.
     * @param string $object The type of page (activity, members, etc.).
     * @param string $filter The current filter.
     * @param string $scope The current scope.
     * @param string $page The current page.
     * @param string $search_terms Search terms.
     * @param string $extras Extra parameters.
     * @return string Modified query string.
     */
    public function modify_nouveau_query( $query_string, $object, $filter, $scope, $page, $search_terms, $extras ) {
        // Only for activity queries
        if ( 'activity' !== $object ) {
            return $query_string;
        }
        
        // Check if smart feed is enabled
        if ( 'yes' !== get_option( 'bpsfc_enable_smart_feed', 'yes' ) ) {
            return $query_string;
        }
        
        // Get user's preferred feed type
        $user_id = get_current_user_id();
        $preferred_feed = get_user_meta( $user_id, 'bpsfc_preferred_feed_type', true );
        
        // Default to smart feed if no preference set
        if ( empty( $preferred_feed ) ) {
            $preferred_feed = get_option( 'bpsfc_default_feed_type', 'smart' );
        }
        
        // Only apply to smart feed
        if ( 'smart' !== $preferred_feed ) {
            return $query_string;
        }
        
        // Parse existing query string
        parse_str( $query_string, $args );
        
        // Mark this as a smart feed query
        $args['feed_type'] = 'smart';
        
        // Rebuild query string
        return http_build_query( $args );
    }

    /**
     * Re-rank activities based on engagement scores.
     * 
     * This maintains all BuddyPress functionality (pagination, filters, Load More)
     * while simply reordering the activities by engagement score instead of date.
     *
     * @since 1.0.0
     * @param bool $has_activities Has activities.
     * @param object $activities Activities object.
     * @param array $r Query arguments.
     * @return bool
     */
    public function rerank_activities( $has_activities, $activities, $r ) {
        if ( ! $has_activities || empty( $activities->activities ) ) {
            return $has_activities;
        }

        // Check if smart feed is active
        if ( ! isset( $r['feed_type'] ) || 'smart' !== $r['feed_type'] ) {
            return $has_activities;
        }

        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return $has_activities;
        }

        // Calculate scores for all activities in this batch
        foreach ( $activities->activities as $activity ) {
            $score = $this->scoring_engine->calculate_score( $activity->id, $user_id );
            $activity->bpsfc_score = $score;
            
            // Store score as activity meta for potential future use
            bp_activity_update_meta( $activity->id, 'bpsfc_score', $score );
        }

        // Sort activities by score (highest first)
        // This preserves the activity objects completely, just changes order
        usort( $activities->activities, function( $a, $b ) {
            // Primary sort: by score (descending)
            $score_diff = $b->bpsfc_score <=> $a->bpsfc_score;
            
            // Secondary sort: if scores are equal, maintain chronological order
            if ( 0 === $score_diff ) {
                return strtotime( $b->date_recorded ) <=> strtotime( $a->date_recorded );
            }
            
            return $score_diff;
        } );

        return $has_activities;
    }
    
    /**
     * Add score data attribute to activity elements.
     * This allows JavaScript to read scores and insert new activities in correct position.
     *
     * @since 1.0.0
     * @param string $class CSS class string.
     * @return string Modified class string with data attribute.
     */
    public function add_score_to_activity_class( $class ) {
        global $activities_template;
        
        // Check if we're in the activity loop and have a score
        if ( ! empty( $activities_template->activity ) && isset( $activities_template->activity->bpsfc_score ) ) {
            $score = $activities_template->activity->bpsfc_score;
            // Add data attribute via class (will be converted by theme)
            // We'll use a filter to add the actual data attribute
            add_filter( 'bp_get_activity_css_first_class', function( $first_class ) use ( $score ) {
                return $first_class . '" data-bpsfc-score="' . esc_attr( $score );
            }, 10, 1 );
        }
        
        return $class;
    }

}

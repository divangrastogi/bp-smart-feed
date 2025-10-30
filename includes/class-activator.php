<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since 1.0.0
 */
class BPSFC_Activator {

    /**
     * Short Description. (use period)
     *
     * Long Description.
     *
     * @since 1.0.0
     */
    public static function activate() {
        self::create_tables();
        self::set_default_options();
        flush_rewrite_rules();
    }

    /**
     * Create necessary database tables.
     *
     * @since 1.0.0
     */
    private static function create_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Activity scores table.
        $table_name = $wpdb->prefix . 'bpsfc_activity_scores';

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            activity_id BIGINT(20) UNSIGNED NOT NULL,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            like_count INT(11) DEFAULT 0,
            comment_count INT(11) DEFAULT 0,
            share_count INT(11) DEFAULT 0,
            view_count INT(11) DEFAULT 0,
            total_score DECIMAL(10,2) DEFAULT 0.00,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX activity_idx (activity_id),
            INDEX user_idx (user_id),
            INDEX score_idx (total_score),
            UNIQUE KEY activity_user (activity_id, user_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        // User interests table.
        $table_name = $wpdb->prefix . 'bpsfc_user_interests';

        $sql = "CREATE TABLE $table_name (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            user_id BIGINT(20) UNSIGNED NOT NULL,
            keyword VARCHAR(255) NOT NULL,
            weight DECIMAL(5,2) DEFAULT 1.00,
            occurrences INT(11) DEFAULT 1,
            last_updated DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX user_idx (user_id),
            INDEX keyword_idx (keyword),
            UNIQUE KEY user_keyword (user_id, keyword)
        ) $charset_collate;";

        dbDelta( $sql );
    }

    /**
     * Set default plugin options.
     *
     * @since 1.0.0
     */
    private static function set_default_options() {
        add_option( 'bpsfc_version', BPSFC_VERSION );
        add_option( 'bpsfc_enable_smart_feed', 'yes' );
        add_option( 'bpsfc_default_feed_type', 'smart' );
        add_option( 'bpsfc_like_weight', 2.0 );
        add_option( 'bpsfc_comment_weight', 3.0 );
        add_option( 'bpsfc_share_weight', 5.0 );
        add_option( 'bpsfc_view_weight', 0.5 );
        add_option( 'bpsfc_time_decay_rate', 24 );
        add_option( 'bpsfc_freshness_threshold', 2 );
        add_option( 'bpsfc_enable_caching', 'yes' );
        add_option( 'bpsfc_cache_duration', 300 );
    }

}

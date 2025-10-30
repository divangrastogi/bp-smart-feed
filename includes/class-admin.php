<?php
/**
 * Admin class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Admin class.
 *
 * @since 1.0.0
 */
class BPSFC_Admin {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
    }

    /**
     * Add admin menu.
     *
     * @since 1.0.0
     */
    public function add_admin_menu() {
        add_menu_page(
            __( 'Smart Feed Settings', 'bp-smart-feed-curator' ),
            __( 'Smart Feed', 'bp-smart-feed-curator' ),
            'manage_options',
            'bpsfc-settings',
            array( $this, 'settings_page' ),
            'dashicons-feed',
            30
        );
    }

    /**
     * Register settings.
     *
     * @since 1.0.0
     */
    public function register_settings() {
        // General
        register_setting( 'bpsfc_general', 'bpsfc_enable_smart_feed' );
        register_setting( 'bpsfc_general', 'bpsfc_default_feed_type' );

        // Scoring
        register_setting( 'bpsfc_scoring', 'bpsfc_like_weight' );
        register_setting( 'bpsfc_scoring', 'bpsfc_comment_weight' );
        register_setting( 'bpsfc_scoring', 'bpsfc_share_weight' );
        register_setting( 'bpsfc_scoring', 'bpsfc_view_weight' );
        register_setting( 'bpsfc_scoring', 'bpsfc_time_decay_rate' );
        register_setting( 'bpsfc_scoring', 'bpsfc_freshness_threshold' );

        // Interest
        register_setting( 'bpsfc_interest', 'bpsfc_enable_interest_tracking' );

        // Performance
        register_setting( 'bpsfc_performance', 'bpsfc_enable_caching' );
        register_setting( 'bpsfc_performance', 'bpsfc_cache_duration' );
        register_setting( 'bpsfc_performance', 'bpsfc_view_tracking' );

        // Display
        register_setting( 'bpsfc_display', 'bpsfc_show_feed_toggle' );
        register_setting( 'bpsfc_display', 'bpsfc_show_explanations' );
    }

    /**
     * Settings page callback.
     *
     * @since 1.0.0
     */
    public function settings_page() {
        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'bp-smart-feed-curator' ) );
        }

        $active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'general';
        ?>
        <div class="bpsfc-admin-wrap">
            <div class="bpsfc-admin-header">
                <h1><?php esc_html_e( 'BP Smart Feed Curator', 'bp-smart-feed-curator' ); ?></h1>
                <p><?php esc_html_e( 'Intelligently re-rank your BuddyPress activity feeds for enhanced user engagement', 'bp-smart-feed-curator' ); ?></p>
            </div>

            <nav class="bpsfc-nav-tabs">
                <a href="?page=bpsfc-settings&tab=general" class="nav-tab <?php echo $active_tab == 'general' ? 'nav-tab-active' : ''; ?>" data-tab="general">
                    <?php esc_html_e( 'General', 'bp-smart-feed-curator' ); ?>
                </a>
                <a href="?page=bpsfc-settings&tab=scoring" class="nav-tab <?php echo $active_tab == 'scoring' ? 'nav-tab-active' : ''; ?>" data-tab="scoring">
                    <?php esc_html_e( 'Scoring', 'bp-smart-feed-curator' ); ?>
                </a>
                <a href="?page=bpsfc-settings&tab=interest" class="nav-tab <?php echo $active_tab == 'interest' ? 'nav-tab-active' : ''; ?>" data-tab="interest">
                    <?php esc_html_e( 'Interest', 'bp-smart-feed-curator' ); ?>
                </a>
                <a href="?page=bpsfc-settings&tab=performance" class="nav-tab <?php echo $active_tab == 'performance' ? 'nav-tab-active' : ''; ?>" data-tab="performance">
                    <?php esc_html_e( 'Performance', 'bp-smart-feed-curator' ); ?>
                </a>
                <a href="?page=bpsfc-settings&tab=display" class="nav-tab <?php echo $active_tab == 'display' ? 'nav-tab-active' : ''; ?>" data-tab="display">
                    <?php esc_html_e( 'Display', 'bp-smart-feed-curator' ); ?>
                </a>
                <a href="?page=bpsfc-settings&tab=analytics" class="nav-tab <?php echo $active_tab == 'analytics' ? 'nav-tab-active' : ''; ?>" data-tab="analytics">
                    <?php esc_html_e( 'Analytics', 'bp-smart-feed-curator' ); ?>
                </a>
            </nav>

            <div class="bpsfc-admin-content">
                <form method="post" action="options.php">
                    <?php
                    if ( $active_tab == 'general' ) {
                        settings_fields( 'bpsfc_general' );
                        $this->general_settings();
                    } elseif ( $active_tab == 'scoring' ) {
                        settings_fields( 'bpsfc_scoring' );
                        $this->scoring_settings();
                    } elseif ( $active_tab == 'interest' ) {
                        settings_fields( 'bpsfc_interest' );
                        $this->interest_settings();
                    } elseif ( $active_tab == 'performance' ) {
                        settings_fields( 'bpsfc_performance' );
                        $this->performance_settings();
                    } elseif ( $active_tab == 'display' ) {
                        settings_fields( 'bpsfc_display' );
                        $this->display_settings();
                    } elseif ( $active_tab == 'analytics' ) {
                        $this->analytics_dashboard();
                    }
                    ?>
                    <div class="bpsfc-submit-section">
                        <button type="submit" class="bpsfc-submit-btn">
                            <?php esc_html_e( 'Save Settings', 'bp-smart-feed-curator' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
        <?php
    }

    /**
     * General settings.
     *
     * @since 1.0.0
     */
    private function general_settings() {
        ?>
        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'General Settings', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable Smart Feed', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_enable_smart_feed" value="yes" <?php checked( get_option( 'bpsfc_enable_smart_feed', 'yes' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Enable intelligent feed re-ranking', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'When enabled, activities are re-ranked based on user engagement and interests.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Default Feed Type', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <select name="bpsfc_default_feed_type">
                            <option value="smart" <?php selected( get_option( 'bpsfc_default_feed_type', 'smart' ), 'smart' ); ?>><?php esc_html_e( 'Smart Feed', 'bp-smart-feed-curator' ); ?></option>
                            <option value="standard" <?php selected( get_option( 'bpsfc_default_feed_type', 'smart' ), 'standard' ); ?>><?php esc_html_e( 'Standard Feed', 'bp-smart-feed-curator' ); ?></option>
                        </select>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Choose the default feed type for new users.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Scoring settings.
     *
     * @since 1.0.0
     */
    private function scoring_settings() {
        ?>
        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'Scoring Weights & Algorithm', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Like Weight', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_like_weight" value="<?php echo esc_attr( get_option( 'bpsfc_like_weight', 2.0 ) ); ?>" step="0.1" min="0" max="10" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Points awarded for each like on an activity. Higher values prioritize liked content.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Comment Weight', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_comment_weight" value="<?php echo esc_attr( get_option( 'bpsfc_comment_weight', 3.0 ) ); ?>" step="0.1" min="0" max="10" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Points awarded for each comment. Comments indicate higher engagement than likes.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Share Weight', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_share_weight" value="<?php echo esc_attr( get_option( 'bpsfc_share_weight', 5.0 ) ); ?>" step="0.1" min="0" max="10" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Points awarded for each share. Shares indicate the highest level of engagement.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'View Weight', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_view_weight" value="<?php echo esc_attr( get_option( 'bpsfc_view_weight', 0.5 ) ); ?>" step="0.1" min="0" max="5" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Points awarded for each view. Lower weight as views are passive engagement.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Time Decay Rate', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_time_decay_rate" value="<?php echo esc_attr( get_option( 'bpsfc_time_decay_rate', 24 ) ); ?>" min="1" max="168" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Hours after which activity scores start decaying. Older content gets lower priority.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Freshness Threshold', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_freshness_threshold" value="<?php echo esc_attr( get_option( 'bpsfc_freshness_threshold', 2 ) ); ?>" min="0" max="24" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Activities newer than this (in hours) receive a freshness bonus.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Interest settings.
     *
     * @since 1.0.0
     */
    private function interest_settings() {
        ?>
        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'Interest Analysis & Personalization', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable Interest Tracking', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_enable_interest_tracking" value="yes" <?php checked( get_option( 'bpsfc_enable_interest_tracking', 'yes' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Learn user preferences from their interactions', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'When enabled, the system analyzes user activity to identify interests and boost relevant content in their feed.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Performance settings.
     *
     * @since 1.0.0
     */
    private function performance_settings() {
        ?>
        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'Performance & Optimization', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Enable Caching', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_enable_caching" value="yes" <?php checked( get_option( 'bpsfc_enable_caching', 'yes' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Cache calculated scores for better performance', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Stores computed activity scores in cache to reduce database queries and improve page load times.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Cache Duration', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <input type="number" name="bpsfc_cache_duration" value="<?php echo esc_attr( get_option( 'bpsfc_cache_duration', 300 ) ); ?>" min="60" max="86400" />
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'How long to cache scores (in minutes). Longer durations improve performance but may show stale data.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Enable View Tracking', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_view_tracking" value="yes" <?php checked( get_option( 'bpsfc_view_tracking', 'no' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Track activity views for scoring', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Records when users view activities. Increases database writes but provides better engagement data.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Display settings.
     *
     * @since 1.0.0
     */
    private function display_settings() {
        ?>
        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'Display & User Interface', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Show Feed Toggle', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_show_feed_toggle" value="yes" <?php checked( get_option( 'bpsfc_show_feed_toggle', 'yes' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Display Smart/Standard feed toggle button', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Allows users to switch between smart and standard feeds on the activity page.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Show Explanations', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="bpsfc_show_explanations" value="yes" <?php checked( get_option( 'bpsfc_show_explanations', 'yes' ), 'yes' ); ?> />
                            <?php esc_html_e( 'Display why activities appear in smart feed', 'bp-smart-feed-curator' ); ?>
                        </label>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Shows users brief explanations for why certain activities are prioritized in their smart feed.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Analytics dashboard.
     *
     * @since 1.0.0
     */
    private function analytics_dashboard() {
        global $wpdb;

        // Get analytics data
        $scores_table = $wpdb->prefix . 'bpsfc_activity_scores';
        $interests_table = $wpdb->prefix . 'bpsfc_user_interests';

        $total_activities = $wpdb->get_var( "SELECT COUNT(DISTINCT activity_id) FROM $scores_table" );
        $total_users = $wpdb->get_var( "SELECT COUNT(DISTINCT user_id) FROM $scores_table" );
        $total_interests = $wpdb->get_var( "SELECT COUNT(*) FROM $interests_table" );
        $avg_score = $wpdb->get_var( "SELECT AVG(total_score) FROM $scores_table" );

        // Status indicators
        $smart_feed_enabled = get_option( 'bpsfc_enable_smart_feed', 'yes' ) === 'yes';
        $interest_tracking = get_option( 'bpsfc_enable_interest_tracking', 'yes' ) === 'yes';
        $caching_enabled = get_option( 'bpsfc_enable_caching', 'yes' ) === 'yes';
        ?>
        <div class="bpsfc-analytics-grid">
            <div class="bpsfc-analytics-card">
                <h3><?php esc_html_e( 'Total Activities', 'bp-smart-feed-curator' ); ?></h3>
                <span class="bpsfc-analytics-value"><?php echo esc_html( number_format( $total_activities ?: 0 ) ); ?></span>
                <span class="bpsfc-analytics-label"><?php esc_html_e( 'Scored Activities', 'bp-smart-feed-curator' ); ?></span>
            </div>

            <div class="bpsfc-analytics-card">
                <h3><?php esc_html_e( 'Active Users', 'bp-smart-feed-curator' ); ?></h3>
                <span class="bpsfc-analytics-value"><?php echo esc_html( number_format( $total_users ?: 0 ) ); ?></span>
                <span class="bpsfc-analytics-label"><?php esc_html_e( 'With Smart Feeds', 'bp-smart-feed-curator' ); ?></span>
            </div>

            <div class="bpsfc-analytics-card">
                <h3><?php esc_html_e( 'Interest Keywords', 'bp-smart-feed-curator' ); ?></h3>
                <span class="bpsfc-analytics-value"><?php echo esc_html( number_format( $total_interests ?: 0 ) ); ?></span>
                <span class="bpsfc-analytics-label"><?php esc_html_e( 'Learned Interests', 'bp-smart-feed-curator' ); ?></span>
            </div>

            <div class="bpsfc-analytics-card">
                <h3><?php esc_html_e( 'Average Score', 'bp-smart-feed-curator' ); ?></h3>
                <span class="bpsfc-analytics-value"><?php echo esc_html( number_format( $avg_score ?: 0, 1 ) ); ?></span>
                <span class="bpsfc-analytics-label"><?php esc_html_e( 'Engagement Points', 'bp-smart-feed-curator' ); ?></span>
            </div>
        </div>

        <div class="bpsfc-settings-section">
            <h3 class="bpsfc-section-header"><?php esc_html_e( 'System Status', 'bp-smart-feed-curator' ); ?></h3>
            <table class="bpsfc-form-table">
                <tr>
                    <th><?php esc_html_e( 'Smart Feed', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <span class="bpsfc-status <?php echo $smart_feed_enabled ? 'active' : 'inactive'; ?>">
                            <?php echo $smart_feed_enabled ? esc_html__( 'Active', 'bp-smart-feed-curator' ) : esc_html__( 'Inactive', 'bp-smart-feed-curator' ); ?>
                        </span>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Smart feed re-ranking is currently enabled and processing activities.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Interest Tracking', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <span class="bpsfc-status <?php echo $interest_tracking ? 'active' : 'inactive'; ?>">
                            <?php echo $interest_tracking ? esc_html__( 'Active', 'bp-smart-feed-curator' ) : esc_html__( 'Inactive', 'bp-smart-feed-curator' ); ?>
                        </span>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'The system is learning user preferences from their interactions.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e( 'Performance Caching', 'bp-smart-feed-curator' ); ?></th>
                    <td>
                        <span class="bpsfc-status <?php echo $caching_enabled ? 'active' : 'inactive'; ?>">
                            <?php echo $caching_enabled ? esc_html__( 'Active', 'bp-smart-feed-curator' ) : esc_html__( 'Inactive', 'bp-smart-feed-curator' ); ?>
                        </span>
                        <div class="bpsfc-field-description">
                            <?php esc_html_e( 'Score caching is enabled for optimal performance.', 'bp-smart-feed-curator' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php
    }

    /**
     * Enqueue scripts.
     *
     * @since 1.0.0
     * @param string $hook Hook suffix.
     */
    public function enqueue_scripts( $hook ) {
        if ( 'toplevel_page_bpsfc-settings' !== $hook ) {
            return;
        }

        wp_enqueue_script(
            'bpsfc-admin-scripts',
            BPSFC_PLUGIN_URL . 'assets/js/admin-scripts.js',
            array( 'jquery' ),
            BPSFC_VERSION,
            true
        );

        wp_enqueue_style(
            'bpsfc-admin-styles',
            BPSFC_PLUGIN_URL . 'assets/css/admin-styles.css',
            array(),
            BPSFC_VERSION
        );
    }

}

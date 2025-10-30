<?php
/**
 * Public-facing functionality.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * BPSFC_Public class.
 *
 * @since 1.0.0
 */
class BPSFC_Public {

    /**
     * Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        // Add toggle next to activity filter
        add_action( 'bp_before_directory_activity_list', array( $this, 'add_feed_toggle' ) );
    }

    /**
     * Enqueue styles.
     *
     * @since 1.0.0
     */
    public function enqueue_styles() {
        if ( ! bp_is_activity_component() ) {
            return;
        }

        wp_enqueue_style(
            'bpsfc-frontend-styles',
            BPSFC_PLUGIN_URL . 'assets/css/frontend-styles.css',
            array(),
            BPSFC_VERSION
        );
    }

    /**
     * Enqueue scripts.
     *
     * @since 1.0.0
     */
    public function enqueue_scripts() {
        if ( ! bp_is_activity_component() ) {
            return;
        }

        wp_enqueue_script(
            'bpsfc-frontend-scripts',
            BPSFC_PLUGIN_URL . 'assets/js/frontend-scripts.js',
            array( 'jquery' ),
            BPSFC_VERSION,
            true
        );

        // Get user's preferred feed type
        $user_id = get_current_user_id();
        $preferred_feed = get_user_meta( $user_id, 'bpsfc_preferred_feed_type', true );
        
        // Default to smart feed if no preference set
        if ( empty( $preferred_feed ) ) {
            $preferred_feed = get_option( 'bpsfc_default_feed_type', 'smart' );
        }
        
        wp_localize_script(
            'bpsfc-frontend-scripts',
            'bpsfc_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'toggle_nonce' => wp_create_nonce( 'bpsfc_toggle_feed' ),
                'load_nonce' => wp_create_nonce( 'bpsfc_load_feed' ),
                'loading_text' => __( 'Loading personalized feed...', 'bp-smart-feed-curator' ),
                'current_user_id' => get_current_user_id(),
                'feed_type' => $preferred_feed,
            )
        );
    }

    /**
     * Add feed toggle.
     *
     * @since 1.0.0
     */
    public function add_feed_toggle() {
        if ( 'yes' !== get_option( 'bpsfc_show_feed_toggle', 'yes' ) ) {
            return;
        }

        include BPSFC_PLUGIN_PATH . 'templates/frontend/feed-toggle.php';
    }
    
    /**
     * Add feed toggle inline (for themes that don't support bp_before_directory_activity_list).
     *
     * @since 1.0.0
     */
    public function add_feed_toggle_inline() {
        // Only add if not already added by bp_before_directory_activity_list
        static $toggle_added = false;
        
        if ( $toggle_added ) {
            return;
        }
        
        $toggle_added = true;
        $this->add_feed_toggle();
    }

}

<?php
/**
 * Core plugin class.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * The core plugin class.
 *
 * @since 1.0.0
 */
class BPSFC_Core {

    /**
     * The single instance of the class.
     *
     * @since 1.0.0
     * @var BPSFC_Core
     */
    private static $instance = null;

    /**
     * Main BPSFC_Core Instance.
     *
     * Ensures only one instance of BPSFC_Core is loaded or can be loaded.
     *
     * @since 1.0.0
     * @return BPSFC_Core Main instance.
     */
    public static function instance() {
        if ( is_null( self::$instance ) ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * BPSFC_Core Constructor.
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->init_hooks();
        $this->includes();
    }

    /**
     * Run the plugin.
     *
     * @since 1.0.0
     */
    public function run() {
        // Plugin is initialized in constructor.
    }

    /**
     * Hook into actions and filters.
     *
     * @since 1.0.0
     */
    private function init_hooks() {
        add_action( 'init', array( $this, 'init' ), 0 );
    }

    /**
     * Include required files.
     *
     * @since 1.0.0
     */
    private function includes() {
        // Include all core classes.
        include_once BPSFC_PLUGIN_PATH . 'includes/class-admin.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-public.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-database.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-engagement-tracker.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-scoring-engine.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-interest-analyzer.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-feed-curator.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-ajax-handler.php';
        include_once BPSFC_PLUGIN_PATH . 'includes/class-rest-api.php';
        // Instantiate classes.
        new BPSFC_Admin();
        new BPSFC_Public();
        new BPSFC_Engagement_Tracker();
        new BPSFC_Interest_Analyzer();
        new BPSFC_Feed_Curator();
        new BPSFC_Ajax_Handler();
        new BPSFC_REST_API();
    }

    /**
     * Init BPSFC when WordPress Initialises.
     *
     * @since 1.0.0
     */
    public function init() {
        // Set up localization.
        $this->load_plugin_textdomain();

        // Init action.
        do_action( 'bpsfc_init' );
    }

    /**
     * Load Localisation files.
     *
     * @since 1.0.0
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'bp-smart-feed-curator',
            false,
            dirname( BPSFC_PLUGIN_BASENAME ) . '/languages/'
        );
    }

    /**
     * Get the plugin url.
     *
     * @since 1.0.0
     * @return string
     */
    public function plugin_url() {
        return BPSFC_PLUGIN_URL;
    }

    /**
     * Get the plugin path.
     *
     * @since 1.0.0
     * @return string
     */
    public function plugin_path() {
        return BPSFC_PLUGIN_PATH;
    }

    /**
     * Get the template path.
     *
     * @since 1.0.0
     * @return string
     */
    public function template_path() {
        return BPSFC_PLUGIN_PATH . 'templates/';
    }

}

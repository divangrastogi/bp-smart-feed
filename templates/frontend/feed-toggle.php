<?php
/**
 * Feed toggle template - Modern toggle switch design.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get user's preferred feed type
$user_id = get_current_user_id();
$preferred_feed = get_user_meta( $user_id, 'bpsfc_preferred_feed_type', true );

// Default to smart feed if no preference set
if ( empty( $preferred_feed ) ) {
    $preferred_feed = get_option( 'bpsfc_default_feed_type', 'smart' );
}

$is_smart = ( 'smart' === $preferred_feed );
?>

<div class="bpsfc-toggle-wrapper">
    <label class="bpsfc-toggle-switch" for="bpsfc-feed-toggle">
        <span class="bpsfc-toggle-label-text">
            <svg class="bpsfc-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
            </svg>
            <?php esc_html_e( 'Smart Feed', 'bp-smart-feed-curator' ); ?>
        </span>
        <input 
            type="checkbox" 
            id="bpsfc-feed-toggle" 
            class="bpsfc-toggle-input"
            <?php checked( $is_smart ); ?>
            aria-label="<?php esc_attr_e( 'Toggle between Smart Feed and Standard Feed', 'bp-smart-feed-curator' ); ?>"
        />
        <span class="bpsfc-toggle-slider" aria-hidden="true"></span>
    </label>
</div>

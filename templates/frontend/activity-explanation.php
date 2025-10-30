<?php
/**
 * Activity explanation template.
 *
 * @since 1.0.0
 * @package BPSFC
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

$reason = isset( $reason ) ? $reason : __( 'High engagement from your network', 'bp-smart-feed-curator' );
?>

<div class="bpsfc-explanation">
    <svg class="bpsfc-info-icon" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"/>
    </svg>
    <span class="bpsfc-explanation-text">
        <?php echo esc_html( sprintf( __( 'You\'re seeing this because: %s', 'bp-smart-feed-curator' ), $reason ) ); ?>
    </span>
</div>

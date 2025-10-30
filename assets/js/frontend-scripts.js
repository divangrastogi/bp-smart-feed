/**
 * Frontend scripts for BP Smart Feed Curator.
 *
 * @since 1.0.0
 */

(function($) {
  'use strict';

  class BPSFCFeedManager {
    constructor() {
      this.toggle = $('#bpsfc-feed-toggle');
      this.feedType = this.toggle.is(':checked') ? 'smart' : 'standard';
      this.isLoading = false;
      this.init();
    }

    init() {
      if (this.toggle.length === 0) {
        console.log('BPSFC: Toggle not found');
        return;
      }
      
      this.bindEvents();
      console.log('BPSFC: Feed Manager initialized with feed type:', this.feedType);
    }

    bindEvents() {
      // Listen for toggle change
      this.toggle.on('change', (e) => {
        const isChecked = $(e.target).is(':checked');
        const newType = isChecked ? 'smart' : 'standard';
        
        console.log('BPSFC: Toggle changed to:', newType);
        this.toggleFeedType(newType);
      });
    }

    toggleFeedType(type) {
      if (this.feedType === type) {
        console.log('BPSFC: Already on', type, 'feed');
        return;
      }
      
      this.feedType = type;
      console.log('BPSFC: Switching to', type, 'feed');
      
      // Save preference via AJAX
      const data = {
        action: 'bpsfc_toggle_feed',
        feed_type: type,
        nonce: bpsfc_ajax.toggle_nonce
      };

      $.post(bpsfc_ajax.ajax_url, data, (response) => {
        if (response.success) {
          console.log('BPSFC: Preference saved, reloading page');
          // Simple page reload - most reliable method
          window.location.reload();
        } else {
          console.error('BPSFC: Failed to save preference');
        }
      }).fail(() => {
        console.error('BPSFC: AJAX request failed');
        // Still reload to show the change
        window.location.reload();
      });
    }
  }

  // Initialize when DOM is ready
  $(document).ready(() => {
    // Check if we're on an activity page
    if ($('#buddypress').length > 0 || $('.activity').length > 0) {
      new BPSFCFeedManager();
      
      // Add body class to indicate current feed type
      if (typeof bpsfc_ajax !== 'undefined' && bpsfc_ajax.feed_type === 'smart') {
        $('body').addClass('smart-feed-active');
        console.log('BPSFC: Smart Feed active - all queries will be reranked by engagement');
      } else {
        $('body').addClass('standard-feed-active');
        console.log('BPSFC: Standard Feed active - chronological order');
      }
    }
  });

})(jQuery);


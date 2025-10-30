=== BP Smart Feed Curator ===

Contributors: wbcomdesigns
Tags: buddypress, activity, feed, smart, engagement, personalization, algorithm, social, ranking
Requires at least: 6.0
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
BuddyPress: 10.0+

Intelligently re-ranks BuddyPress activity feeds based on user engagement metrics and personalized interests. Users can toggle between smart and chronological feeds.

== Description ==

BP Smart Feed Curator transforms BuddyPress activity feeds into personalized, engaging experiences using advanced algorithms that analyze user interactions.

**🚀 Key Features:**

* **Smart Algorithm**: Re-ranks activities based on likes, comments, shares, and views
* **Personalized Interests**: Learns user preferences from their interactions
* **Time Decay**: Prioritizes recent, engaging content with intelligent decay
* **Freshness Bonus**: Highlights new activities with engagement potential
* **User Toggle**: Switch between Smart Feed and Standard chronological feed
* **Admin Dashboard**: Comprehensive analytics and performance monitoring
* **REST API**: Full API support for custom integrations
* **Responsive Design**: Works perfectly on all devices and themes
* **Accessibility**: WCAG compliant with ARIA support
* **Performance Optimized**: Caching, batch processing, and efficient queries

**How It Works:**

The plugin analyzes user engagement patterns:
- **Likes (+1.0)**: Shows approval and interest
- **Comments (+2.0)**: Indicates discussion and engagement
- **Shares (+3.0)**: Strongest signal of interest
- **Views (+0.5)**: Passive engagement tracking
- **Time Decay**: Recent activities lose less value over time
- **Personalization**: Learns and boosts content matching user interests

**Example:**
```
Activity A: 15 likes, 3 comments, 2 hours old = Score: 25.2
Activity B: 2 likes, 0 comments, 30 minutes old = Score: 2.4
Result: Activity A appears above Activity B (better engagement)
```

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/bp-smart-feed-curator` directory
2. Activate through 'Plugins' screen in WordPress
3. Go to **BuddyPress → Smart Feed Settings** to configure
4. Visit activity page to see the toggle switch

== Frequently Asked Questions ==

= Does this plugin require BuddyPress? =

Yes, this plugin requires BuddyPress 10.0+ to be installed and activated.

= How does the smart feed algorithm work? =

Activities receive scores based on engagement metrics (likes, comments, shares, views) with time decay and personalization. Higher-scoring activities appear first in Smart Feed mode.

= Can users switch back to chronological feed? =

Yes! Users see a toggle switch allowing instant switching between Smart Feed (engagement-based) and Standard Feed (chronological).

= Is it compatible with all BuddyPress themes? =

Yes, it works with any BuddyPress-compatible theme including Nouveau and Legacy templates.

= Does it affect performance? =

No, the plugin uses caching, batch processing, and optimized queries. Scores are calculated efficiently and cached for 5 minutes by default.

= Can I customize the scoring algorithm? =

Yes, through admin settings you can adjust weights for likes, comments, shares, and views. Advanced users can use WordPress filters for custom scoring logic.

= Is user data secure? =

Yes, the plugin follows WordPress security best practices with nonce verification, input sanitization, and secure data storage. GDPR compliant.

= What about accessibility? =

Fully accessible with WCAG compliance, ARIA labels, keyboard navigation, and screen reader support.

== Screenshots ==

1. Smart Feed toggle switch next to activity filters
2. Admin settings dashboard with scoring configuration
3. Analytics panel showing engagement metrics
4. User experience comparison (Smart vs Standard feed)
5. Mobile responsive design

== Changelog ==

= 1.0.0 =
* Complete rewrite with modern architecture
* Nouveau template support (Load More, heartbeat, filters)
* Advanced scoring algorithm with time decay and personalization
* User toggle between Smart and Standard feeds
* Comprehensive admin dashboard with analytics
* REST API integration for custom applications
* Performance optimizations (caching, batch processing)
* Accessibility compliance (WCAG, ARIA, keyboard navigation)
* Security hardening (nonces, sanitization, capabilities)
* Mobile responsive design
* Interest-based personalization engine
* Multiple query filter support for complete compatibility

== Upgrade Notice ==

= 1.0.0 =
Complete rewrite with modern architecture. All previous settings will be migrated. No breaking changes for users.

== Support ==

For support, please visit our [support forum](https://wbcomdesigns.com/support/) or check the comprehensive documentation included with the plugin.

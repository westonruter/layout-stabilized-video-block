> [!NOTE]  
> This plugin is obsolete because the functionality in this plugin landed in WordPress 6.9. See [section in frontend performance field guide](https://make.wordpress.org/core/2025/11/18/wordpress-6-9-frontend-performance-field-guide/#eliminate-layout-shift-in-video-block).

# Layout-stabilized Video Block #

Contributors: westonruter  
Tested up to: 6.8  
Stable tag:   0.1.0  
License:      [GPLv2](https://www.gnu.org/licenses/gpl-2.0.html) or later  
Tags:         performance

## Description ##

This plugin adds missing `width` and `height` attributes to the `video` tag in the Video block along with the `aspect-ratio` style to prevent a layout shift when the video is loaded. This improves the Cumulative Layout Shift (CLS) metric from Core Web Vitals. The functionality can be disabled by adding `?disable_layout_stabilized_video_block=1` to the URL in order to see the impact of the change.

This only applies to videos selected from the Media Library since only these would have the metadata for the dimensions available.

The fix in this plugin is the same that has been proposed in PR [#52185](https://github.com/WordPress/gutenberg/issues/52185) for Gutenberg.

See blog post with full writeup: [Eliminating Layout Shifts in the Video Block](https://weston.ruter.net/2025/06/05/eliminating-layout-shifts-in-the-video-block/).

## Installation ##

1. Download the plugin [ZIP from GitHub](https://github.com/westonruter/layout-stabilized-video-block/archive/refs/heads/main.zip) or if you have a local clone of the repo, run `npm run plugin-zip`.
2. Visit **Plugins > Add New Plugin** in the WordPress Admin.
3. Click **Upload Plugin**.
4. Select the `layout-stabilized-video-block.zip` file on your system from step 1 and click **Install Now**.
5. Click the **Activate Plugin** button.

You may also install and update via [Git Updater](https://git-updater.com/).

## Changelog ##

### 0.1.0 ###

* Initial release.

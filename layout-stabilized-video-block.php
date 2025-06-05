<?php
/**
 * Plugin Name: Layout-stabilized Video Block
 * Plugin URI: https://github.com/westonruter/layout-stabilized-video-block
 * Description: Adds missing <code>width</code> and <code>height</code> attributes to the <code>video</code> tag in the Video block along with the <code>aspect-ratio</code> style to prevent a layout shift when the video is loaded. Only applies to videos selected from the Media Library. Improves the Cumulative Layout Shift (CLS) metric from Core Web Vitals.
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Version: 0.1.0
 * Author: Weston Ruter
 * Author URI: https://weston.ruter.net/
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Update URI: https://github.com/westonruter/layout-stabilized-video-block
 * GitHub Plugin URI: https://github.com/westonruter/layout-stabilized-video-block
 * Primary Branch: main
 *
 * @package LayoutStabilizedVideoBlock
 */

namespace LayoutStabilizedVideoBlock;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // @codeCoverageIgnore
}

// Short-circuit functionality to facilitate benchmarking performance impact.
if ( isset( $_GET['disable_layout_stabilized_video_block'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	return;
}

use WP_HTML_Tag_Processor;

/**
 * Filters the content of the video block.
 *
 * @since 0.1.0
 *
 * @phpstan-param array{
 *                    "attrs": array{
 *                        "id"?: positive-int,
 *                    }
 *                } $block
 *
 * @param string|mixed $content The block content.
 * @param array        $block   The full block, including name and attributes.
 */
function filter_rendered_video_block_content( mixed $content, array $block ): mixed {
	if ( is_string( $content ) ) {
		$content = render_block_core_video( $block['attrs'], $content );
	}
	return $content;
}
add_filter( 'render_block_core/video', __NAMESPACE__ . '\filter_rendered_video_block_content', 10, 2 );

/**
 * Adds inline style for the Video blocks that have a VIDEO tag with a width and height attribute.
 *
 * @since 0.1.0
 */
function add_video_block_inline_style(): void {
	$handle = wp_should_load_separate_core_block_assets() ? 'wp-block-video' : 'wp-block-library';
	wp_add_inline_style( $handle, '.wp-block-video video{height:auto}' );
}
add_action( 'enqueue_block_assets', __NAMESPACE__ . '\add_video_block_inline_style' );

/**
 * Renders the `core/video` block on the server to supply the width and height attributes from the attachment metadata.
 *
 * @since 0.1.0
 *
 * @phpstan-param array{ "id"?: positive-int } $attributes
 *
 * @param array  $attributes The block attributes.
 * @param string $content    The block content.
 * @return string The block content with the dimensions added.
 */
function render_block_core_video( array $attributes, string $content ): string {
	// if the content lacks any video tag, abort.
	if ( ! str_contains( $content, '<video' ) ) {
		return $content;
	}

	// If the 'id' attribute is not populated for a video attachment, abort.
	if (
		! isset( $attributes['id'] ) ||
		! is_int( $attributes['id'] ) ||
		$attributes['id'] <= 0
	) {
		return $content;
	}

	// If the 'id' attribute wasn't for an attachment, abort.
	if ( get_post_type( $attributes['id'] ) !== 'attachment' ) {
		return $content;
	}

	// Get the width and height metadata for the video, and abort if absent or invalid.
	$metadata = wp_get_attachment_metadata( $attributes['id'] );
	if (
		! isset( $metadata['width'], $metadata['height'] ) ||
		! ( is_int( $metadata['width'] ) && is_int( $metadata['height'] ) ) ||
		! ( $metadata['width'] > 0 && $metadata['height'] > 0 )
	) {
		return $content;
	}

	// Locate the VIDEO tag to add the dimensions.
	$p = new WP_HTML_Tag_Processor( $content );
	if ( ! $p->next_tag( array( 'tag_name' => 'VIDEO' ) ) ) {
		return $content;
	}

	$p->set_attribute( 'width', (string) $metadata['width'] );
	$p->set_attribute( 'height', (string) $metadata['height'] );

	/*
	 * The aspect-ratio style is needed due to an issue with the CSS spec: <https://github.com/w3c/csswg-drafts/issues/7524>.
	 * Note that a style rule using attr() like the following cannot currently be used:
	 *
	 *     .wp-block-video video[width][height] {
	 *         aspect-ratio: attr(width type(<number>)) / attr(height type(<number>));
	 *     }
	 *
	 * This is because this attr() is yet only implemented in Chromium: <https://caniuse.com/css3-attr>.
	 */
	$style = $p->get_attribute( 'style' );
	if ( ! is_string( $style ) ) {
		$style = '';
	}
	$aspect_ratio_style = sprintf( 'aspect-ratio: %d / %d;', $metadata['width'], $metadata['height'] );
	$p->set_attribute( 'style', $aspect_ratio_style . $style );

	return $p->get_updated_html();
}

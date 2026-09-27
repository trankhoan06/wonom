<?php
/**
 * WordPress Performance Cleanup
 * Xóa toàn bộ JS/CSS không cần thiết mà WordPress tự load
 * Áp dụng cho custom theme - không dùng Gutenberg, không dùng default widgets
 */

// ============================================================
// 1. EMOJI - Hoàn toàn không cần thiết
// ============================================================
remove_action('wp_head',             'print_emoji_detection_script', 7);
remove_action('admin_print_scripts', 'print_emoji_detection_script');
remove_action('wp_print_styles',     'print_emoji_styles');
remove_action('admin_print_styles',  'print_emoji_styles');
remove_filter('the_content_feed',    'wp_staticize_emoji');
remove_filter('comment_text_rss',    'wp_staticize_emoji');
remove_filter('wp_mail',             'wp_staticize_emoji_for_email');
add_filter('tiny_mce_plugins', function($plugins) {
    return array_diff($plugins, ['wpemoji']);
});

// ============================================================
// 2. OEMBED - Không dùng embed WordPress
// ============================================================
remove_action('wp_head',             'wp_oembed_add_discovery_links');
remove_action('wp_head',             'wp_oembed_add_host_js');
remove_action('rest_api_init',       'wp_oembed_register_route');
remove_filter('oembed_dataparse',    'wp_filter_oembed_result');
remove_action('wp_head',             'rest_output_link_wp_head');
add_filter('embed_oembed_discover',  '__return_false');

// ============================================================
// 3. GUTENBERG BLOCK EDITOR CSS
// ============================================================
remove_action('wp_enqueue_scripts',  'wp_enqueue_global_styles');
remove_action('wp_body_open',        'wp_global_styles_render_svg_filters');
add_filter('should_load_separate_core_block_assets', '__return_false');

// Tắt block styles khi không dùng Gutenberg ở frontend
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
    wp_dequeue_style('classic-theme-styles');
    wp_dequeue_style('wc-blocks-style'); // WooCommerce nếu có
}, 200);

// ============================================================
// 4. JQUERY MẶC ĐỊNH CỦA WORDPRESS (theme đã load jQuery riêng)
// ============================================================
add_action('wp_enqueue_scripts', function() {
    if (!is_admin()) {
        wp_deregister_script('jquery');
        wp_deregister_script('jquery-core');
        wp_deregister_script('jquery-migrate');
    }
}, 1);

// ============================================================
// 5. COMMENT REPLY JS (nếu không dùng comments)
// ============================================================
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_script('comment-reply');
});

// ============================================================
// 6. WP GENERATOR META TAG (ẩn version WP - bảo mật)
// ============================================================
remove_action('wp_head', 'wp_generator');

// ============================================================
// 7. RSD LINK & WLW MANIFEST (Windows Live Writer - không cần)
// ============================================================
remove_action('wp_head', 'rsd_link');
remove_action('wp_head', 'wlwmanifest_link');

// ============================================================
// 8. SHORTLINK (thẻ <link rel="shortlink"> trong <head>)
// ============================================================
remove_action('wp_head', 'wp_shortlink_wp_head');
remove_action('template_redirect', 'wp_shortlink_header', 11);

// ============================================================
// 9. FEED LINKS (RSS không cần thiết nếu không dùng blog)
// ============================================================
// Bỏ comment dòng dưới nếu site KHÔNG có blog/RSS
// remove_action('wp_head', 'feed_links',       2);
// remove_action('wp_head', 'feed_links_extra',  3);

// ============================================================
// 10. ADJACENT POSTS LINKS (prev/next post link trong <head>)
// ============================================================
remove_action('wp_head', 'adjacent_posts_rel_link_wp_head', 10);

// ============================================================
// 11. REST API LINK HEADER (không expose endpoint)
// ============================================================
remove_action('wp_head', 'rest_output_link_wp_head', 10);
remove_action('template_redirect', 'rest_output_link_header', 11);

// ============================================================
// 12. DASHICONS FRONTEND (chỉ cần trong admin)
// ============================================================
add_action('wp_enqueue_scripts', function() {
    if (!is_admin() && !is_user_logged_in()) {
        wp_deregister_style('dashicons');
    }
});

// ============================================================
// 13. CLASSIC THEME STYLES (WordPress 6.1+)
// ============================================================
add_action('wp_enqueue_scripts', function() {
    wp_dequeue_style('classic-theme-styles');
}, 20);

// ============================================================
// 14. TẮT XML-RPC (bảo mật + performance)
// ============================================================
add_filter('xmlrpc_enabled', '__return_false');

// ============================================================
// 15. TẮT HEARTBEAT API (giảm AJAX polling liên tục)
// ============================================================
add_action('init', function() {
    if (!is_admin()) {
        wp_deregister_script('heartbeat');
    }
});

// ============================================================
// 16. RESOURCE HINTS không cần thiết
// ============================================================
remove_action('wp_head', 'wp_resource_hints', 2);

// ============================================================
// 17. SCRIPT LOADER TAG - Thêm defer cho scripts
// (Tùy chọn: defer tất cả scripts không phải jQuery)
// ============================================================
add_filter('script_loader_tag', function($tag, $handle, $src) {
    $defer_scripts = ['gsap', 'scroll-trigger', 'split-text', 'lenis', 'wonom-index-js'];
    if (in_array($handle, $defer_scripts)) {
        return '<script src="' . esc_url($src) . '" defer></script>' . "\n";
    }
    return $tag;
}, 10, 3);

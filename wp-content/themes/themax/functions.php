<?php

include 'typerocket/init.php';

require dirname(__FILE__) . '/inc/cleanup.php';
require dirname(__FILE__) . '/inc/frontend-performance.php';
require dirname(__FILE__) . '/inc/homepage-settings.php';
require dirname(__FILE__) . '/inc/home-tour-data.php';
require dirname(__FILE__) . '/inc/home-event-data.php';
require dirname(__FILE__) . '/inc/home-library-data.php';
require dirname(__FILE__) . '/inc/home-form-submissions.php';
require dirname(__FILE__) . '/inc/upload-image-optimizer.php';

add_filter('tr_theme_options_page', function () {
    return get_template_directory() . '/theme-options.php';
});

load_theme_textdomain('wonom', get_template_directory() . '/languages');
add_theme_support('title-tag');
add_theme_support('post-thumbnails');
add_filter('show_admin_bar', '__return_false');

/**
 * Load only the assets required by the current homepage.
 */
function wonom_enqueue_assets()
{
    if (!is_front_page()) {
        return;
    }

    $theme_path = get_template_directory();
    $theme_url = get_template_directory_uri();

    wp_enqueue_style(
        'wonom-custom-select',
        $theme_url . '/css/custom-select.css',
        array(),
        filemtime($theme_path . '/css/custom-select.css') ?: '1.0.0'
    );
    wp_enqueue_style(
        'wonom-swiper',
        'https://cdn.jsdelivr.net/npm/swiper@14.0.1/swiper-bundle.min.css',
        array(),
        '14.0.1'
    );
    wp_enqueue_style(
        'wonom-home',
        $theme_url . '/asset/css/style.css',
        array('wonom-swiper'),
        filemtime($theme_path . '/asset/css/style.css') ?: '1.0.0'
    );

    wp_enqueue_script(
        'wonom-custom-select',
        $theme_url . '/js/custom-select.js',
        array(),
        filemtime($theme_path . '/js/custom-select.js') ?: '1.0.0',
        true
    );
    wp_enqueue_script('jquery-3.7.1', $theme_url . '/js/jquery-3.7.1.min.js', array(), '3.7.1', true);
    wp_enqueue_script('wonom-swiper', 'https://cdn.jsdelivr.net/npm/swiper@14.0.1/swiper-bundle.min.js', array(), '14.0.1', true);
    wp_enqueue_script('gsap', $theme_url . '/js/gsap.min.js', array(), '3.13.0', true);
    wp_enqueue_script('split-text', $theme_url . '/js/SplitText.min.js', array('gsap'), '3.15.0', true);
    wp_enqueue_script('scroll-to-plugin', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollToPlugin.min.js', array('gsap'), '3.13.0', true);
    wp_enqueue_script(
        'wonom-home',
        $theme_url . '/asset/js/index.js',
        array('jquery-3.7.1', 'wonom-swiper', 'gsap', 'split-text', 'scroll-to-plugin'),
        filemtime($theme_path . '/asset/js/index.js') ?: '1.0.0',
        true
    );
    wp_localize_script('wonom-home', 'wonomHome', array(
        'assetUrl' => trailingslashit($theme_url . '/asset'),
        'pageFlipUrl' => 'https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js',
        'webglTransitionUrl' => $theme_url . '/asset/js/webgl-image-transition.js',
        'imageHoverUrl' => $theme_url . '/asset/js/global-image-hover.js',
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'submitNonce' => wp_create_nonce('wonom_submit_form'),
        'explorePopupData' => wonom_get_home_explore_popup_data(),
    ));
    wp_enqueue_script(
        'wonom-home-forms',
        $theme_url . '/js/home-forms.js',
        array('wonom-home'),
        filemtime($theme_path . '/js/home-forms.js') ?: '1.0.0',
        true
    );

    foreach (array(
        'wonom-custom-select',
        'jquery-3.7.1',
        'wonom-swiper',
        'gsap',
        'split-text',
        'scroll-to-plugin',
        'wonom-home',
        'wonom-home-forms',
    ) as $defer_handle) {
        wp_script_add_data($defer_handle, 'strategy', 'defer');
    }
}
add_action('wp_enqueue_scripts', 'wonom_enqueue_assets');

/**
 * Configure SMTP only when a host was entered in Theme Options. Leaving the
 * fields empty lets WordPress use its normal mail transport.
 */
function wonom_configure_phpmailer($phpmailer)
{
    $host = sanitize_text_field((string) tr_options_field('tr_theme_options.smtp_host'));
    if (!$host) {
        return;
    }

    $encryption = sanitize_key((string) tr_options_field('tr_theme_options.encryption'));
    if (!in_array($encryption, array('ssl', 'tls'), true)) {
        $encryption = '';
    }
    $port = absint(tr_options_field('tr_theme_options.smtp_port'));
    if (!$port) {
        $port = 'ssl' === $encryption ? 465 : 587;
    }

    $phpmailer->isSMTP();
    $phpmailer->Host = $host;
    $phpmailer->SMTPAuth = (bool) tr_options_field('tr_theme_options.authentication');
    $phpmailer->Port = $port;
    $phpmailer->Username = sanitize_text_field((string) tr_options_field('tr_theme_options.username'));
    $phpmailer->Password = (string) tr_options_field('tr_theme_options.smtp_password');
    $phpmailer->SMTPSecure = $encryption;
    $phpmailer->Timeout = 15;

    $from_email = sanitize_email((string) tr_options_field('tr_theme_options.from_email'));
    $from_name = sanitize_text_field((string) tr_options_field('tr_theme_options.from_name')) ?: get_bloginfo('name');
    if ($from_email && is_email($from_email)) {
        $phpmailer->setFrom($from_email, $from_name, false);
    }

    if (!$phpmailer->SMTPSecure) {
        $phpmailer->SMTPAutoTLS = false;
    }
}
add_action('phpmailer_init', 'wonom_configure_phpmailer');

function wonom_capture_mail_error($error)
{
    if (get_current_user_id()) {
        set_transient('wonom_mail_error_' . get_current_user_id(), $error->get_error_message(), MINUTE_IN_SECONDS);
    }
}
add_action('wp_mail_failed', 'wonom_capture_mail_error');

function wonom_handle_smtp_test()
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Bạn không có quyền thực hiện thao tác này.', 'wonom'));
    }
    check_admin_referer('wonom_test_smtp');

    $error_key = 'wonom_mail_error_' . get_current_user_id();
    delete_transient($error_key);
    $recipients = function_exists('wonom_submission_notification_emails')
        ? wonom_submission_notification_emails()
        : array(get_option('admin_email'));
    $sent = wp_mail(
        $recipients,
        '[WONOM] Kiểm tra cấu hình SMTP',
        "Email kiểm tra SMTP đã được gửi thành công từ website " . home_url('/') . " vào " . current_time('d/m/Y H:i:s') . '.',
        array('Content-Type: text/plain; charset=UTF-8')
    );

    $redirect_args = array(
        'page' => 'theme_options',
        'wonom_smtp_test' => $sent ? 'success' : 'error',
    );
    $error_message = get_transient($error_key);
    if (!$sent && $error_message) {
        $redirect_args['wonom_smtp_error'] = $error_message;
    }
    delete_transient($error_key);

    wp_safe_redirect(add_query_arg($redirect_args, admin_url('themes.php')));
    exit;
}
add_action('admin_post_wonom_test_smtp', 'wonom_handle_smtp_test');

function wonom_disable_cache_for_zalo()
{
    $user_agent = isset($_SERVER['HTTP_USER_AGENT'])
        ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
        : '';

    if (false !== stripos($user_agent, 'Zalo')) {
        nocache_headers();
    }
}
add_action('send_headers', 'wonom_disable_cache_for_zalo');

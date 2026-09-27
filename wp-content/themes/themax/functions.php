<?php

include 'typerocket/init.php';
require dirname(__FILE__) . '/inc/init.php';

// TypeRocket configs used by custom post type detail screens.
require dirname(__FILE__) . '/inc/typerocket-career-detail.php';
require dirname(__FILE__) . '/inc/typerocket-single-work.php';
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

add_theme_support('post-thumbnails');
add_theme_support('title-tag');

register_nav_menus(array(
    'header_menu' => esc_html__('Header Menu', 'wonom'),
    'footer_menu' => esc_html__('Footer Menu', 'wonom'),
));

add_image_size('post-default', 900, 480, true);

add_filter('show_admin_bar', '__return_false');

function wonom_enqueue_assets()
{
    $theme_path = get_template_directory();
    $theme_url = get_template_directory_uri();

    $custom_select_style_version = filemtime($theme_path . '/css/custom-select.css') ?: '1.0.0';
    $custom_select_script_version = filemtime($theme_path . '/js/custom-select.js') ?: '1.0.0';
    wp_enqueue_style(
        'wonom-custom-select',
        $theme_url . '/css/custom-select.css',
        array(),
        $custom_select_style_version
    );
    wp_enqueue_script(
        'wonom-custom-select',
        $theme_url . '/js/custom-select.js',
        array(),
        $custom_select_script_version,
        true
    );

    if (is_front_page()) {
        $homepage_style_version = filemtime($theme_path . '/asset/css/style.css') ?: '1.0.0';
        $homepage_script_version = filemtime($theme_path . '/asset/js/index.js') ?: '1.0.0';

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
            $homepage_style_version
        );

        // Serve jQuery and GSAP from the same origin. This removes the separate
        // code.jquery.com connection while retaining the site's Swiper 14 API.
        wp_enqueue_script('jquery-3.7.1', $theme_url . '/js/jquery-3.7.1.min.js', array(), '3.7.1', true);
        wp_enqueue_script('wonom-swiper', 'https://cdn.jsdelivr.net/npm/swiper@14.0.1/swiper-bundle.min.js', array(), '14.0.1', true);
        wp_enqueue_script('gsap', $theme_url . '/js/gsap.min.js', array(), '3.13.0', true);
        wp_enqueue_script('split-text', $theme_url . '/js/SplitText.min.js', array('gsap'), '3.13.0', true);
        wp_enqueue_script('scroll-to-plugin', 'https://cdn.jsdelivr.net/npm/gsap@3.13.0/dist/ScrollToPlugin.min.js', array('gsap'), '3.13.0', true);
        wp_enqueue_script(
            'wonom-home',
            $theme_url . '/asset/js/index.js',
            array(
                'jquery-3.7.1',
                'wonom-swiper',
                'gsap',
                'split-text',
                'scroll-to-plugin',
            ),
            $homepage_script_version,
            true
        );
        wp_localize_script('wonom-home', 'wonomHome', array(
            'assetUrl' => trailingslashit($theme_url . '/asset'),
            'pageFlipUrl' => 'https://cdn.jsdelivr.net/npm/page-flip@2.0.7/dist/js/page-flip.browser.js',
            'webglTransitionUrl' => $theme_url . '/asset/js/webgl-image-transition.js',
            'imageHoverUrl' => $theme_url . '/asset/js/global-image-hover.js',
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'submitNonce' => wp_create_nonce('wonom_submit_form'),
            'explorePopupData' => function_exists('wonom_get_home_explore_popup_data')
                ? wonom_get_home_explore_popup_data()
                : array(),
        ));
        wp_enqueue_script(
            'wonom-home-forms',
            $theme_url . '/js/home-forms.js',
            array('wonom-home'),
            filemtime($theme_path . '/js/home-forms.js') ?: '1.0.0',
            true
        );

        // All homepage scripts live in the footer and can preserve execution
        // order while yielding parsing/rendering work to the browser.
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

        return;
    }

    $style_version = filemtime($theme_path . '/css/style.css') ?: '1.0.0';
    wp_enqueue_style('wonom-style', $theme_url . '/css/style.css', array(), $style_version);

    wp_enqueue_script('jquery-3.7.1', $theme_url . '/js/jquery-3.7.1.min.js', array(), '3.7.1', true);
    wp_enqueue_script('gsap', $theme_url . '/js/gsap.min.js', array(), '1.0.0', true);
    wp_enqueue_script('scroll-trigger', $theme_url . '/js/ScrollTrigger.min.js', array('gsap'), '1.0.0', true);
    wp_enqueue_script('split-text', $theme_url . '/js/SplitText.min.js', array('gsap'), '1.0.0', true);
    wp_enqueue_script('lenis', $theme_url . '/js/lenis.min.js', array(), '1.0.0', true);

    $index_js_version = filemtime($theme_path . '/js/index.min.js') ?: '1.0.0';
    wp_enqueue_script(
        'wonom-index-js',
        $theme_url . '/js/index.min.js',
        array('jquery-3.7.1', 'gsap'),
        $index_js_version,
        true
    );

    $recaptcha_site_key = tr_options_field('tr_theme_options.recaptcha_site_key')
        ?: '6LcQlD0tAAAAALN2ByRRGHnl9FO9EO7UvIBf99mR';

    // Keep the existing object name because the shared JavaScript uses it for forms.
    wp_localize_script('wonom-index-js', 'caseStudyAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'recaptchaSiteKey' => $recaptcha_site_key,
    ));

    add_action('wp_footer', function () use ($recaptcha_site_key) {
        if (!$recaptcha_site_key) {
            return;
        }
        ?>
        <script id="lazy-recaptcha-loader">
        (function () {
            function initReCaptcha() {
                if (window.reCaptchaLoaded) return;

                window.reCaptchaLoaded = true;
                var script = document.createElement('script');
                script.src = 'https://www.google.com/recaptcha/api.js?render=<?php echo esc_js($recaptcha_site_key); ?>';
                script.async = true;
                script.defer = true;
                document.head.appendChild(script);
            }

            ['mousemove', 'touchstart', 'scroll', 'keydown', 'click', 'focus'].forEach(function (eventName) {
                window.addEventListener(eventName, initReCaptcha, { once: true, passive: true });
            });

            setTimeout(initReCaptcha, 4500);
        })();
        </script>
        <?php
    }, 99);

    if (is_singular('career')) {
        $career_detail_version = filemtime($theme_path . '/css/career-detail.css') ?: '1.0.0';
        wp_enqueue_style(
            'wonom-career-detail',
            $theme_url . '/css/career-detail.css',
            array(),
            $career_detail_version
        );
    } elseif (is_singular('case-study-detail') || is_singular('work')) {
        $case_study_detail_version = filemtime($theme_path . '/css/case-study-detail.css') ?: '1.0.0';
        wp_enqueue_style(
            'wonom-case-study-detail',
            $theme_url . '/css/case-study-detail.css',
            array(),
            $case_study_detail_version
        );
    }
}
add_action('wp_enqueue_scripts', 'wonom_enqueue_assets');

function wonom_add_defer_to_scripts($tag, $handle)
{
    if (is_front_page()) {
        return $tag;
    }

    $defer_scripts = array(
        'jquery-3.7.1',
        'gsap',
        'scroll-trigger',
        'split-text',
        'lenis',
        'wonom-index-js',
    );

    if (in_array($handle, $defer_scripts, true)) {
        return str_replace(' src', ' defer src', $tag);
    }

    if ('google-recaptcha' === $handle) {
        return str_replace(' src', ' async defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 'wonom_add_defer_to_scripts', 10, 2);

function wonom_disable_cache_for_zalo()
{
    $user_agent = isset($_SERVER['HTTP_USER_AGENT'])
        ? sanitize_text_field(wp_unslash($_SERVER['HTTP_USER_AGENT']))
        : '';

    if (stripos($user_agent, 'Zalo') !== false) {
        nocache_headers();
    }
}
add_action('send_headers', 'wonom_disable_cache_for_zalo');

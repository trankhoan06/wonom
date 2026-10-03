<?php
/**
 * Front page template for the Wonom website.
 *
 * Every top-level section is rendered explicitly from a named template part.
 * There is no secondary HTML document or regex-based section replacement.
 */

$homepage_parts = array(
    array('template-parts/layouts/home', 'header'),
    array('template-parts/home', 'hero'),
    array('template-parts/home', 'introduce'),
    array('template-parts/home', 'experience'),
    array('template-parts/home', 'explore'),
    array('template-parts/home', 'tour'),
    array('template-parts/home', 'event'),
    array('template-parts/home', 'library'),
    array('template-parts/home', 'footer'),
    array('template-parts/layouts/home', 'global-actions'),
    array('template-parts/home', 'tour-popup'),
    array('template-parts/home', 'event-popup'),
    array('template-parts/layouts/home', 'policy-popup'),
    array('template-parts/layouts/home', 'restaurant-popup'),
    array('template-parts/layouts/home', 'booking-popup'),
    array('template-parts/layouts/home', 'workshop-popup'),
    array('template-parts/layouts/home', 'member-popup'),
    array('template-parts/layouts/home', 'audio-notice'),
    array('template-parts/layouts/home', 'cursor'),
);

$homepage_template_files = array_merge(
    array(__FILE__, get_theme_file_path('/inc/frontend-performance.php')),
    glob(get_theme_file_path('/template-parts/home-*.php')) ?: array(),
    glob(get_theme_file_path('/template-parts/layouts/home-*.php')) ?: array()
);
$homepage_cache_version = 0;
foreach ($homepage_template_files as $homepage_template_file) {
    if (is_readable($homepage_template_file)) {
        $homepage_cache_version = max($homepage_cache_version, (int) filemtime($homepage_template_file));
    }
}

$homepage_cacheable = !is_user_logged_in()
    && 'GET' === strtoupper(isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET')
    && !is_customize_preview()
    && empty($_GET['preview']);
$homepage_cache_generation = (string) get_option('wonom_home_cache_generation', '1');
$homepage_cache_schema = 'direct-parts-v2-assets';
$homepage_cache_key = 'wonom_home_markup_' . md5(
    get_locale() . '|' . $homepage_cache_schema . '|' . $homepage_cache_version . '|' . $homepage_cache_generation
);
$homepage_lcp_cache_key = $homepage_cache_key . '_lcp';
$homepage_body = $homepage_cacheable ? get_transient($homepage_cache_key) : false;

if (false !== $homepage_body) {
    $GLOBALS['wonom_home_lcp_image'] = (string) get_transient($homepage_lcp_cache_key);
}

if (false === $homepage_body) {
    ob_start();
    foreach ($homepage_parts as $homepage_part) {
        get_template_part($homepage_part[0], $homepage_part[1]);
    }
    $homepage_body = ob_get_clean();

    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
    $homepage_body = preg_replace_callback(
        '/(?P<prefix>\b(?:src|href|poster)=["\']|url\(\s*["\']?)(?:\.\/|\/)?asset\//i',
        static function ($matches) use ($asset_url) {
            return $matches['prefix'] . $asset_url;
        },
        $homepage_body
    );

    // Add intrinsic dimensions and responsive Media Library sources once while
    // building the cached fragment, before applying loading priorities.
    $homepage_body = wonom_optimize_homepage_image_markup($homepage_body);
    $homepage_body = wonom_defer_homepage_popup_images($homepage_body);

    // Header and hero images remain eager. Every later image is deferred so it
    // cannot compete with the homepage LCP request.
    $first_section_end = stripos($homepage_body, '</section>');
    if (false !== $first_section_end) {
        $first_section_end += strlen('</section>');
        $critical_markup = substr($homepage_body, 0, $first_section_end);
        $deferred_markup = substr($homepage_body, $first_section_end);
        $deferred_markup = preg_replace_callback('/<img\b[^>]*>/i', static function ($match) {
            $tag = $match[0];
            if (false === stripos($tag, ' loading=')) {
                $tag = preg_replace('/<img\b/i', '<img loading="lazy"', $tag, 1);
            }
            if (false === stripos($tag, ' decoding=')) {
                $tag = preg_replace('/<img\b/i', '<img decoding="async"', $tag, 1);
            }
            return $tag;
        }, $deferred_markup);
        $homepage_body = $critical_markup . $deferred_markup;
    }

    if ($homepage_cacheable && $homepage_body) {
        set_transient($homepage_cache_key, $homepage_body, 12 * HOUR_IN_SECONDS);
        set_transient(
            $homepage_lcp_cache_key,
            isset($GLOBALS['wonom_home_lcp_image']) ? $GLOBALS['wonom_home_lcp_image'] : '',
            12 * HOUR_IN_SECONDS
        );
    }
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <?php if (!empty($GLOBALS['wonom_home_lcp_image'])) : ?>
        <?php $wonom_lcp_data = wonom_get_image_performance_data($GLOBALS['wonom_home_lcp_image']); ?>
        <link rel="preload" as="image" href="<?php echo esc_url($GLOBALS['wonom_home_lcp_image']); ?>"<?php
        if (!empty($wonom_lcp_data['srcset'])) {
            echo ' imagesrcset="' . esc_attr($wonom_lcp_data['srcset']) . '"';
            echo ' imagesizes="' . esc_attr($wonom_lcp_data['sizes']) . '"';
        }
        ?> fetchpriority="high">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class('wonom-home'); ?>>
<?php wp_body_open(); ?>

<?php echo $homepage_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php wp_footer(); ?>
</body>
</html>

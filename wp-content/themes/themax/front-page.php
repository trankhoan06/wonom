<?php
/**
 * Front page template for the Wonom website.
 *
 * The markup is kept in homepage.html so the approved static layout can be
 * maintained without mixing thousands of lines of HTML with WordPress logic.
 */

$homepage_file = get_theme_file_path('/homepage.html');
$homepage_template_files = array_merge(
    array($homepage_file),
    glob(get_theme_file_path('/template-parts/home-*.php')) ?: array()
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
$homepage_cache_key = 'wonom_home_markup_' . md5(
    get_locale() . '|' . $homepage_cache_version . '|' . $homepage_cache_generation
);
$homepage_lcp_cache_key = $homepage_cache_key . '_lcp';
$homepage_body = $homepage_cacheable ? get_transient($homepage_cache_key) : false;

if (false !== $homepage_body) {
    $GLOBALS['wonom_home_lcp_image'] = (string) get_transient($homepage_lcp_cache_key);
}

if (false === $homepage_body) {
    $homepage_html = is_readable($homepage_file) ? file_get_contents($homepage_file) : '';
    $homepage_body = '';

if ($homepage_html && preg_match('/<body[^>]*>(.*)<\/body>/is', $homepage_html, $matches)) {
    $homepage_body = $matches[1];
}

// Scripts are registered through WordPress in functions.php.
$homepage_body = preg_replace('/\s*<script\b[^>]*>.*?<\/script>/is', '', $homepage_body);
$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$homepage_body = str_replace(array('./asset/', '/asset/'), $asset_url, $homepage_body);

ob_start();
get_template_part('template-parts/home', 'hero');
$hero_markup = ob_get_clean();

if ($hero_markup) {
    $homepage_body = preg_replace_callback(
        '/<section class="home_banner pa_section">.*?<\/section>/is',
        function () use ($hero_markup) {
            return $hero_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'introduce');
$introduce_markup = ob_get_clean();

if ($introduce_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="introduce" class="home_intro relative pa_section">.*?<\/section>/is',
        function () use ($introduce_markup) {
            return $introduce_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'experience');
$experience_markup = ob_get_clean();

if ($experience_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="experience" class="home_experience pa_section">.*?<\/section>/is',
        function () use ($experience_markup) {
            return $experience_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'explore');
$explore_markup = ob_get_clean();

if ($explore_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="explore" class="home_explore pa_section">.*?<\/section>\s*(?=<section id="tour")/is',
        function () use ($explore_markup) {
            return $explore_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'tour');
$tour_markup = ob_get_clean();

if ($tour_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="tour" class="home_tour pa_section relative">.*?<\/section>\s*(?=<section id="event")/is',
        function () use ($tour_markup) {
            return $tour_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'tour-popup');
$tour_popup_markup = ob_get_clean();

if ($tour_popup_markup) {
    $homepage_body = preg_replace_callback(
        '/<section class="popup_tour tour">.*?<\/section>\s*(?=<section class="popup_tour event")/is',
        function () use ($tour_popup_markup) {
            return $tour_popup_markup;
        },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'event');
$event_markup = ob_get_clean();
if ($event_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="event" class="home_event pa_section">.*?<\/section>\s*(?=<section id="library")/is',
        function () use ($event_markup) { return $event_markup; },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'event-popup');
$event_popup_markup = ob_get_clean();
if ($event_popup_markup) {
    $homepage_body = preg_replace_callback(
        '/<section class="popup_tour event"[^>]*>.*?<\/section>\s*(?=<section class="popup_tour policy")/is',
        function () use ($event_popup_markup) { return $event_popup_markup; },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'library');
$library_markup = ob_get_clean();
if ($library_markup) {
    $homepage_body = preg_replace_callback(
        '/<section id="library" class="home_space pa_section relative">.*?<\/section>\s*(?=<footer id="contact")/is',
        function () use ($library_markup) { return $library_markup; },
        $homepage_body,
        1
    );
}

ob_start();
get_template_part('template-parts/home', 'footer');
$footer_markup = ob_get_clean();
if ($footer_markup) {
    $homepage_body = preg_replace_callback(
        '/<footer id="contact" class="footer pa_section">.*?<\/footer>/is',
        function () use ($footer_markup) { return $footer_markup; },
        $homepage_body,
        1
    );
}

// Images after the hero must not compete with the LCP image. Keep header and
// hero assets eager, then make every later image lazy and asynchronously
// decoded, including images coming from dynamic template parts.
$first_section_end = stripos($homepage_body, '</section>');
if (false !== $first_section_end) {
    $first_section_end += strlen('</section>');
    $critical_markup = substr($homepage_body, 0, $first_section_end);
    $deferred_markup = substr($homepage_body, $first_section_end);
    $deferred_markup = preg_replace_callback('/<img\b[^>]*>/i', function ($match) {
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
        // A short fragment cache skips repeated option lookups and the regex
        // assembly of the 180KB homepage while keeping CMS edits responsive.
        set_transient($homepage_cache_key, $homepage_body, 5 * MINUTE_IN_SECONDS);
        set_transient(
            $homepage_lcp_cache_key,
            isset($GLOBALS['wonom_home_lcp_image']) ? $GLOBALS['wonom_home_lcp_image'] : '',
            5 * MINUTE_IN_SECONDS
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
        <link rel="preload" as="image" href="<?php echo esc_url($GLOBALS['wonom_home_lcp_image']); ?>" fetchpriority="high">
    <?php endif; ?>
    <?php wp_head(); ?>
</head>
<body <?php body_class('wonom-home'); ?>>
<?php wp_body_open(); ?>

<?php if ($homepage_body) : ?>
    <?php echo $homepage_body; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php elseif (current_user_can('manage_options')) : ?>
    <p><?php esc_html_e('Homepage markup could not be loaded.', 'wonom'); ?></p>
<?php endif; ?>

<?php wp_footer(); ?>
</body>
</html>

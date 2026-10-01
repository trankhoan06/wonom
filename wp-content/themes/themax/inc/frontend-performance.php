<?php
/**
 * Frontend performance helpers for the homepage.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Return responsive metadata for an image URL.
 *
 * Media Library images use WordPress-generated sub-sizes. Theme assets still
 * receive intrinsic dimensions so the browser can reserve their layout space.
 *
 * @param string $url Image URL.
 * @return array{width?: int, height?: int, srcset?: string, sizes?: string}
 */
function wonom_get_image_performance_data($url)
{
    static $cache = array();

    $url = (string) $url;
    if (!$url) {
        return array();
    }
    if (isset($cache[$url])) {
        return $cache[$url];
    }

    $data = array();
    $clean_url = strtok($url, '?#');
    $attachment_id = attachment_url_to_postid($clean_url);

    if ($attachment_id) {
        $full = wp_get_attachment_image_src($attachment_id, 'full');
        if ($full) {
            $data['width'] = (int) $full[1];
            $data['height'] = (int) $full[2];
        }

        $srcset = wp_get_attachment_image_srcset($attachment_id, 'full');
        if ($srcset) {
            $data['srcset'] = $srcset;
            $data['sizes'] = !empty($data['width'])
                ? sprintf('(max-width: %1$dpx) 100vw, %1$dpx', $data['width'])
                : '100vw';
        }
    } else {
        $theme_url = trailingslashit(get_template_directory_uri());
        if (0 === strpos($clean_url, $theme_url)) {
            $relative_path = rawurldecode(substr($clean_url, strlen($theme_url)));
            $candidate = wp_normalize_path(get_template_directory() . '/' . ltrim($relative_path, '/'));
            $theme_path = trailingslashit(wp_normalize_path(get_template_directory()));
            $real_path = realpath($candidate);

            if ($real_path && 0 === strpos(wp_normalize_path($real_path), $theme_path)) {
                $dimensions = @getimagesize($real_path);
                if ($dimensions) {
                    $data['width'] = (int) $dimensions[0];
                    $data['height'] = (int) $dimensions[1];
                }
            }
        }
    }

    $cache[$url] = $data;
    return $data;
}

/**
 * Add responsive sources and intrinsic dimensions to homepage image markup.
 * This runs only while rebuilding the cached homepage fragment.
 *
 * @param string $html Homepage markup.
 * @return string
 */
function wonom_optimize_homepage_image_markup($html)
{
    if (!$html || !class_exists('WP_HTML_Tag_Processor')) {
        return $html;
    }

    $processor = new WP_HTML_Tag_Processor($html);
    while ($processor->next_tag('IMG')) {
        $src = $processor->get_attribute('src');
        if (!$src || 0 === strpos($src, 'data:')) {
            continue;
        }

        $data = wonom_get_image_performance_data($src);
        if (!$processor->get_attribute('width') && !empty($data['width'])) {
            $processor->set_attribute('width', (string) $data['width']);
        }
        if (!$processor->get_attribute('height') && !empty($data['height'])) {
            $processor->set_attribute('height', (string) $data['height']);
        }
        if (!$processor->get_attribute('srcset') && !empty($data['srcset'])) {
            $processor->set_attribute('srcset', $data['srcset']);
            $processor->set_attribute('sizes', $data['sizes']);
        }
        if (!$processor->get_attribute('decoding')) {
            $processor->set_attribute('decoding', 'async');
        }
    }

    return $processor->get_updated_html();
}

/**
 * Prevent images inside closed popups from competing with the main page.
 * Their sources are restored by index.js after window.load or when a popup is
 * opened early.
 *
 * @param string $html Homepage markup.
 * @return string
 */
function wonom_defer_homepage_popup_images($html)
{
    if (!$html || !class_exists('WP_HTML_Tag_Processor')) {
        return $html;
    }

    return preg_replace_callback(
        '/<section\b[^>]*class=(?P<quote>["\'])[^"\']*(?:popup_tour|popup_form|popup_member|workshop_detail_popup)[^"\']*\k<quote>[^>]*>.*?<\/section>/is',
        static function ($match) {
            $processor = new WP_HTML_Tag_Processor($match[0]);
            while ($processor->next_tag('IMG')) {
                $src = $processor->get_attribute('src');
                $srcset = $processor->get_attribute('srcset');

                if ($src) {
                    $processor->set_attribute('data-wonom-src', $src);
                    $processor->remove_attribute('src');
                }
                if ($srcset) {
                    $processor->set_attribute('data-wonom-srcset', $srcset);
                    $processor->remove_attribute('srcset');
                }

                $processor->set_attribute('data-wonom-popup-image', 'pending');
                $processor->set_attribute('loading', 'lazy');
                $processor->set_attribute('decoding', 'async');
            }

            return $processor->get_updated_html();
        },
        $html
    );
}

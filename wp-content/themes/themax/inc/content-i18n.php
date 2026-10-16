<?php

if (!defined('ABSPATH')) exit;

/**
 * Let Polylang manage translations for Wonom's content post types.
 * These filters must be registered early, before Polylang caches its type list.
 */
function wonom_polylang_post_types($post_types, $is_settings)
{
    $post_types['wonom_tour'] = 'wonom_tour';
    $post_types['wonom_workshop'] = 'wonom_workshop';
    $post_types['wonom_event'] = 'wonom_event';

    return $post_types;
}
add_filter('pll_get_post_types', 'wonom_polylang_post_types', 10, 2);

function wonom_polylang_taxonomies($taxonomies, $is_settings)
{
    $taxonomies['wonom_workshop_category'] = 'wonom_workshop_category';

    return $taxonomies;
}
add_filter('pll_get_taxonomies', 'wonom_polylang_taxonomies', 10, 2);

/**
 * Media is shared by all translations while text meta remains independent.
 * Polylang also uses this list when initially copying a translation.
 */
function wonom_polylang_shared_media_metas($meta_keys, $sync, $from, $to, $lang)
{
    $post_type = get_post_type($from) ?: get_post_type($to);
    $shared_meta = array();

    if ('wonom_tour' === $post_type) {
        $shared_meta = array('tour_image', 'tour_image_mobile', 'tour_popup_image', 'tour_popup_image_mobile');
    } elseif ('wonom_workshop' === $post_type) {
        $shared_meta = array('workshop_image', 'workshop_detail_images');
    } elseif ('wonom_event' === $post_type) {
        $shared_meta = array('event_image', 'event_popup_image');
    }

    return array_values(array_unique(array_merge($meta_keys, $shared_meta)));
}
add_filter('pll_copy_post_metas', 'wonom_polylang_shared_media_metas', 10, 5);

/** Refresh language-specific homepage markup after translated content changes. */
function wonom_invalidate_translated_content_cache($object_id = 0)
{
    update_option('wonom_home_cache_generation', (string) microtime(true), false);
}
function wonom_invalidate_translated_post_cache($post_id)
{
    if (!wp_is_post_revision($post_id) && !wp_is_post_autosave($post_id)) {
        wonom_invalidate_translated_content_cache();
    }
}
add_action('save_post_wonom_tour', 'wonom_invalidate_translated_post_cache');
add_action('save_post_wonom_workshop', 'wonom_invalidate_translated_post_cache');
add_action('save_post_wonom_event', 'wonom_invalidate_translated_post_cache');
add_action('before_delete_post', static function ($post_id) {
    if (in_array(get_post_type($post_id), array('wonom_tour', 'wonom_workshop', 'wonom_event'), true)) {
        wonom_invalidate_translated_content_cache();
    }
});
add_action('created_wonom_workshop_category', 'wonom_invalidate_translated_content_cache');
add_action('edited_wonom_workshop_category', 'wonom_invalidate_translated_content_cache');
add_action('delete_wonom_workshop_category', 'wonom_invalidate_translated_content_cache');

/**
 * Existing records predate language support. Mark them as Vietnamese once so
 * enabling Polylang does not make the current Vietnamese content disappear.
 */
function wonom_migrate_content_languages()
{
    if (get_option('wonom_content_languages_migrated_v1')) {
        return;
    }
    if (!function_exists('pll_languages_list') || !function_exists('pll_set_post_language')) {
        return;
    }

    $languages = (array) pll_languages_list(array('fields' => 'slug'));
    if (!$languages) {
        return;
    }

    $source_language = in_array('vi', $languages, true)
        ? 'vi'
        : (function_exists('pll_default_language') ? pll_default_language('slug') : reset($languages));
    if (!$source_language) {
        return;
    }

    $post_ids = get_posts(array(
        'post_type' => array('wonom_tour', 'wonom_workshop', 'wonom_event'),
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));
    foreach ($post_ids as $post_id) {
        if (!pll_get_post_language($post_id, 'slug')) {
            pll_set_post_language($post_id, $source_language);
        }
    }

    if (function_exists('pll_set_term_language')) {
        $term_ids = get_terms(array(
            'taxonomy' => 'wonom_workshop_category',
            'hide_empty' => false,
            'fields' => 'ids',
            'lang' => '',
        ));
        if (!is_wp_error($term_ids)) {
            foreach ($term_ids as $term_id) {
                if (!pll_get_term_language($term_id, 'slug')) {
                    pll_set_term_language($term_id, $source_language);
                }
            }
        }
    }

    update_option('wonom_content_languages_migrated_v1', 1, false);
}
add_action('admin_init', 'wonom_migrate_content_languages', 20);

<?php

/**
 * Return one homepage detail item and the remaining suggestion cards.
 */
function wonom_ajax_get_home_detail()
{
    check_ajax_referer('wonom_home_detail', 'nonce');

    $type = isset($_POST['detail_type']) ? sanitize_key(wp_unslash($_POST['detail_type'])) : '';
    $index = isset($_POST['detail_index']) ? absint($_POST['detail_index']) : 0;

    if ('tour' === $type) {
        $items = wonom_get_home_tours();
    } elseif ('event' === $type) {
        $items = wonom_get_home_events();
    } else {
        wp_send_json_error(array('message' => wonom_home_t('invalid_content')), 400);
    }

    if (!isset($items[$index])) {
        wp_send_json_error(array('message' => wonom_home_t('content_not_found')), 404);
    }

    $suggestions = array();
    foreach ($items as $suggestion_index => $item) {
        if ($suggestion_index === $index) {
            continue;
        }

        $suggestions[] = array(
            'index' => $suggestion_index,
            'image' => isset($item['image']) ? $item['image'] : '',
            'imageAlt' => isset($item['imageAlt']) ? $item['imageAlt'] : '',
            'title' => isset($item['title']) ? $item['title'] : '',
        );
    }

    wp_send_json_success(array(
        'index' => $index,
        'detail' => $items[$index],
        'suggestions' => $suggestions,
    ));
}
add_action('wp_ajax_wonom_get_home_detail', 'wonom_ajax_get_home_detail');
add_action('wp_ajax_nopriv_wonom_get_home_detail', 'wonom_ajax_get_home_detail');

<?php

if (!defined('ABSPATH')) exit;

function wonom_register_event_post_type()
{
    $labels = array(
        'name' => 'Sự kiện',
        'singular_name' => 'Sự kiện',
        'menu_name' => 'Sự kiện',
        'add_new' => 'Thêm Sự kiện',
        'add_new_item' => 'Thêm Sự kiện mới',
        'edit_item' => 'Sửa Sự kiện',
        'new_item' => 'Sự kiện mới',
        'view_item' => 'Xem Sự kiện',
        'search_items' => 'Tìm Sự kiện',
        'not_found' => 'Không tìm thấy Sự kiện',
        'not_found_in_trash' => 'Không có Sự kiện trong thùng rác',
        'all_items' => 'Tất cả Sự kiện',
    );

    $post_type = tr_post_type('Wonom Event', 'Wonom Events', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 8,
        'menu_icon' => 'dashicons-calendar-alt',
        'supports' => array('title', 'page-attributes'),
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ));
    $post_type->setTitlePlaceholder('Nhập tên Sự kiện');
    $post_type->forceDisableGutenberg();

    tr_meta_box('Thông tin Sự kiện', 'wonom_event', array(
        'priority' => 'high',
        'callback' => 'wonom_render_event_fields',
    ));
}
wonom_register_event_post_type();

function wonom_render_event_fields()
{
    global $post;

    $form = tr_form();

    echo '<h3>Thông tin hiển thị trên card</h3>';
    echo $form->image('event_image')->setLabel('Hình card');
    echo $form->text('event_apply')->setLabel('Thời gian áp dụng, ví dụ: THƯỜNG XUYÊN');
    echo $form->textarea('event_subtitle')->setLabel('Mô tả ngắn');
    echo $form->text('event_category')->setLabel('Danh mục, ví dụ: ROOFTOP');

    echo '<hr><h3>Popup chi tiết sự kiện</h3>';
    echo $form->image('event_popup_image')->setLabel('Hình trong popup (để trống sẽ dùng hình card)');
    echo $form->text('event_condition')->setLabel('Điều kiện áp dụng');
    wonom_render_workshop_richtext_editor($post, 'event_content', 'Nội dung chi tiết', 12);
}

function wonom_get_event_posts()
{
    return get_posts(array(
        'post_type' => 'wonom_event',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'),
        'suppress_filters' => false,
        'lang' => function_exists('wonom_home_current_language') ? wonom_home_current_language() : '',
    ));
}

function wonom_migrate_legacy_events()
{
    if (get_option('wonom_events_migrated')) {
        return;
    }

    $existing = get_posts(array(
        'post_type' => 'wonom_event',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));
    if ($existing) {
        update_option('wonom_events_migrated', 1, false);
        return;
    }

    $legacy_events = tr_options_field('wonom_home_options.home_events');
    if (!is_array($legacy_events) || !$legacy_events) {
        update_option('wonom_events_migrated', 1, false);
        return;
    }

    $meta_map = array(
        'image' => 'event_image',
        'apply' => 'event_apply',
        'subtitle' => 'event_subtitle',
        'category' => 'event_category',
        'condition' => 'event_condition',
        'content' => 'event_content',
    );

    foreach ($legacy_events as $index => $legacy_event) {
        if (!is_array($legacy_event)) {
            continue;
        }

        $title = isset($legacy_event['title'])
            ? sanitize_text_field(html_entity_decode($legacy_event['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            : '';
        $post_id = wp_insert_post(array(
            'post_type' => 'wonom_event',
            'post_status' => 'publish',
            'post_title' => $title !== '' ? $title : sprintf('Sự kiện %d', $index + 1),
            'menu_order' => (int) $index,
        ), true);
        if (is_wp_error($post_id)) {
            continue;
        }

        foreach ($meta_map as $legacy_key => $meta_key) {
            if (array_key_exists($legacy_key, $legacy_event)) {
                update_post_meta($post_id, $meta_key, wp_slash($legacy_event[$legacy_key]));
            }
        }

        if (function_exists('pll_set_post_language')) {
            $default_lang = function_exists('pll_default_language') ? pll_default_language('slug') : 'vi';
            if ($default_lang) {
                pll_set_post_language($post_id, $default_lang);
            }
        }
    }

    update_option('wonom_events_migrated', 1, false);
}
add_action('admin_init', 'wonom_migrate_legacy_events', 5);

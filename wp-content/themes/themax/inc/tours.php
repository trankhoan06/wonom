<?php

if (!defined('ABSPATH')) exit;

function wonom_register_tour_post_type()
{
    $labels = array(
        'name' => 'Tour',
        'singular_name' => 'Tour',
        'menu_name' => 'Tour',
        'add_new' => 'Thêm Tour',
        'add_new_item' => 'Thêm Tour mới',
        'edit_item' => 'Sửa Tour',
        'new_item' => 'Tour mới',
        'view_item' => 'Xem Tour',
        'search_items' => 'Tìm Tour',
        'not_found' => 'Không tìm thấy Tour',
        'not_found_in_trash' => 'Không có Tour trong thùng rác',
        'all_items' => 'Tất cả Tour',
    );

    $post_type = tr_post_type('Wonom Tour', 'Wonom Tours', array(
        'labels' => $labels,
        'public' => false,
        'show_ui' => true,
        'show_in_menu' => true,
        'menu_position' => 7,
        'menu_icon' => 'dashicons-location-alt',
        'supports' => array('title', 'page-attributes'),
        'has_archive' => false,
        'rewrite' => false,
        'show_in_rest' => false,
    ));
    $post_type->setTitlePlaceholder('Nhập tên Tour');
    $post_type->forceDisableGutenberg();

    tr_meta_box('Thông tin Tour', 'wonom_tour', array(
        'priority' => 'high',
        'callback' => 'wonom_render_tour_fields',
    ));
}
wonom_register_tour_post_type();

function wonom_render_tour_fields()
{
    global $post;

    $form = tr_form();

    echo '<h3>Thông tin hiển thị trên slider</h3>';
    echo $form->image('tour_image')->setLabel('Hình đại diện Tour (Desktop)');
    echo $form->image('tour_image_mobile')
        ->setLabel('Hình đại diện Tour (Mobile)')
        ->setHelp('Hình hiển thị trên thiết bị di động (nếu để trống sẽ dùng hình Desktop).');
    echo $form->repeater('tour_meta_items')
        ->setLabel('Thông tin Tour')
        ->setHeadline('label')
        ->setFields(array(
            $form->text('label')->setLabel('Label'),
            $form->text('content')->setLabel('Nội dung'),
        ));

    echo '<hr><h3>Popup chi tiết Tour</h3>';
    echo $form->image('tour_popup_image')->setLabel('Hình trong popup (Desktop)');
    echo $form->image('tour_popup_image_mobile')
        ->setLabel('Hình trong popup (Mobile)')
        ->setHelp('Hình hiển thị trong popup trên thiết bị di động (nếu để trống sẽ dùng hình Desktop).');

    echo $form->text('tour_intro_title')->setLabel('Tiêu đề giới thiệu');
    wonom_render_workshop_richtext_editor($post, 'tour_intro_content', 'Nội dung giới thiệu', 12);

    echo $form->text('tour_itinerary_title')->setLabel('Tiêu đề lịch trình');
    wonom_render_workshop_richtext_editor($post, 'tour_itinerary_content', 'Nội dung lịch trình', 12);

    echo $form->text('tour_notes_title')->setLabel('Tiêu đề lưu ý');
    wonom_render_workshop_richtext_editor($post, 'tour_notes_content', 'Nội dung lưu ý', 10);

    echo $form->text('tour_policy_title')->setLabel('Tiêu đề chính sách');
    wonom_render_workshop_richtext_editor($post, 'tour_policy_content', 'Nội dung chính sách', 10);
}

function wonom_get_tour_posts()
{
    return get_posts(array(
        'post_type' => 'wonom_tour',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'orderby' => array('menu_order' => 'ASC', 'ID' => 'ASC'),
        'suppress_filters' => false,
        'lang' => function_exists('wonom_home_current_language') ? wonom_home_current_language() : '',
    ));
}

function wonom_migrate_legacy_tours()
{
    if (get_option('wonom_tours_migrated')) {
        return;
    }

    $existing = get_posts(array(
        'post_type' => 'wonom_tour',
        'post_status' => 'any',
        'posts_per_page' => 1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));
    if ($existing) {
        update_option('wonom_tours_migrated', 1, false);
        return;
    }

    $legacy_tours = tr_options_field('wonom_home_options.home_tours');
    if (!is_array($legacy_tours) || !$legacy_tours) {
        update_option('wonom_tours_migrated', 1, false);
        return;
    }

    $meta_map = array(
        'image' => 'tour_image',
        'duration' => 'tour_duration',
        'price' => 'tour_price',
        'popup_image' => 'tour_popup_image',
        'intro_title' => 'tour_intro_title',
        'intro_content' => 'tour_intro_content',
        'itinerary_title' => 'tour_itinerary_title',
        'itinerary_content' => 'tour_itinerary_content',
        'notes_title' => 'tour_notes_title',
        'notes_content' => 'tour_notes_content',
        'policy_title' => 'tour_policy_title',
        'policy_content' => 'tour_policy_content',
    );

    foreach ($legacy_tours as $index => $legacy_tour) {
        if (!is_array($legacy_tour)) {
            continue;
        }

        $title = isset($legacy_tour['title'])
            ? sanitize_text_field(html_entity_decode($legacy_tour['title'], ENT_QUOTES | ENT_HTML5, 'UTF-8'))
            : '';
        $post_id = wp_insert_post(array(
            'post_type' => 'wonom_tour',
            'post_status' => 'publish',
            'post_title' => $title !== '' ? $title : sprintf('Tour %d', $index + 1),
            'menu_order' => (int) $index,
        ), true);
        if (is_wp_error($post_id)) {
            continue;
        }

        foreach ($meta_map as $legacy_key => $meta_key) {
            if (array_key_exists($legacy_key, $legacy_tour)) {
                update_post_meta($post_id, $meta_key, wp_slash($legacy_tour[$legacy_key]));
            }
        }
    }

    update_option('wonom_tours_migrated', 1, false);
}
add_action('admin_init', 'wonom_migrate_legacy_tours', 5);

/**
 * Convert the former fixed duration/price fields into the dynamic Tour info list.
 * The old meta is retained so this migration is reversible and safe to run once.
 */
function wonom_migrate_tour_meta_items()
{
    if (get_option('wonom_tour_meta_items_migrated')) {
        return;
    }

    $tour_ids = get_posts(array(
        'post_type' => 'wonom_tour',
        'post_status' => 'any',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'suppress_filters' => true,
        'lang' => '',
    ));

    foreach ($tour_ids as $tour_id) {
        if (metadata_exists('post', $tour_id, 'tour_meta_items')) {
            continue;
        }

        $items = array();
        $duration = trim((string) get_post_meta($tour_id, 'tour_duration', true));
        $price = trim((string) get_post_meta($tour_id, 'tour_price', true));

        if ('' !== $duration) {
            $items[] = array('label' => 'Thời gian', 'content' => $duration);
        }
        if ('' !== $price) {
            $items[] = array('label' => 'Chi phí', 'content' => $price);
        }

        if ($items) {
            update_post_meta($tour_id, 'tour_meta_items', $items);
        }
    }

    update_option('wonom_tour_meta_items_migrated', 1, false);
}
add_action('admin_init', 'wonom_migrate_tour_meta_items', 10);

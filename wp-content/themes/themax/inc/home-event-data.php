<?php

function wonom_get_home_events()
{
    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';
    $event_posts = function_exists('wonom_get_event_posts') ? wonom_get_event_posts() : array();
    $rows = array();

    foreach ($event_posts as $event_post) {
        $rows[] = array(
            'id' => $event_post->ID,
            'image' => get_post_meta($event_post->ID, 'event_image', true),
            'title' => html_entity_decode(get_the_title($event_post), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'apply' => get_post_meta($event_post->ID, 'event_apply', true),
            'subtitle' => get_post_meta($event_post->ID, 'event_subtitle', true),
            'category' => get_post_meta($event_post->ID, 'event_category', true),
            'condition' => get_post_meta($event_post->ID, 'event_condition', true),
            'popup_image' => get_post_meta($event_post->ID, 'event_popup_image', true),
            'content' => get_post_meta($event_post->ID, 'event_content', true),
        );
    }

    if (!$rows && !get_option('wonom_events_migrated')) {
        $rows = tr_options_field('wonom_home_options.home_events');
    }

    if (!is_array($rows) || !$rows) {
        $rows = array(
            array('title' => 'GIẢM 20% BAN NGÀY TRONG TUẦN', 'apply' => 'THƯỜNG XUYÊN', 'category' => 'DISCOUNT', 'condition' => 'Là thành viên Wonom', 'subtitle' => 'Giảm giá trải nghiệm mọi tầng'),
            array('title' => 'PHÒNG CHỤP HANBOK THEO MÙA', 'apply' => 'THEO MÙA', 'category' => 'TRẢI NGHIỆM', 'condition' => 'Áp dụng theo lịch chương trình', 'subtitle' => 'Lưu giữ khoảnh khắc trong không gian đậm chất Hàn Quốc'),
            array('title' => 'ĐÊM ROOFTOP DISCO', 'apply' => 'CUỐI TUẦN', 'category' => 'ROOFTOP', 'condition' => 'Áp dụng tại tầng 5', 'subtitle' => 'Đêm nhạc và cocktail trên rooftop Wonom'),
            array('title' => 'PHÒNG CHỤP HANBOK THEO MÙA', 'apply' => 'THEO MÙA', 'category' => 'TRẢI NGHIỆM', 'condition' => 'Áp dụng theo lịch chương trình', 'subtitle' => 'Lưu giữ khoảnh khắc trong không gian đậm chất Hàn Quốc'),
        );
        foreach ($rows as &$row) {
            $row['image'] = $asset_url . 'store.jpg';
        }
        unset($row);
    }

    $resolve_image = static function ($image) {
        $id = 0;
        $url = '';
        if (is_numeric($image)) {
            $id = (int) $image;
            $url = wp_get_attachment_image_url($id, 'full') ?: '';
        } elseif (is_array($image)) {
            $id = !empty($image['id']) ? (int) $image['id'] : 0;
            $url = $id ? (wp_get_attachment_image_url($id, 'full') ?: '') : '';
            $url = $url ?: (!empty($image['url']) ? esc_url_raw($image['url']) : '');
        } elseif (is_string($image)) {
            $url = esc_url_raw($image);
        }
        return $url;
    };

    $events = array();
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $image = $resolve_image(isset($row['image']) ? $row['image'] : '');
        if (!$image) {
            $image = $asset_url . 'store.jpg';
        }
        $popup_image = $resolve_image(isset($row['popup_image']) ? $row['popup_image'] : '');
        $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
        $image_alt = $title ?: 'Wonom Culture Complex';
        $events[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'image' => $image,
            'imageAlt' => $image_alt,
            'title' => $title,
            'apply' => isset($row['apply']) ? sanitize_text_field($row['apply']) : '',
            'category' => isset($row['category']) ? sanitize_text_field($row['category']) : '',
            'condition' => isset($row['condition']) ? sanitize_text_field($row['condition']) : '',
            'subtitle' => isset($row['subtitle']) ? sanitize_text_field($row['subtitle']) : '',
            'popupImage' => $popup_image ?: $image,
            'popupImageAlt' => $image_alt,
            'content' => isset($row['content']) ? wp_kses_post($row['content']) : '',
        );
    }

    return $events;
}

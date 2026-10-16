<?php

/**
 * Normalize Tour settings for the homepage slider and detail popup.
 */
function wonom_get_home_tours()
{
    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';
    $tour_posts = function_exists('wonom_get_tour_posts') ? wonom_get_tour_posts() : array();
    $rows = array();

    foreach ($tour_posts as $tour_post) {
        $rows[] = array(
            'id' => $tour_post->ID,
            'image' => get_post_meta($tour_post->ID, 'tour_image', true),
            'image_mobile' => get_post_meta($tour_post->ID, 'tour_image_mobile', true),
            'title' => html_entity_decode(get_the_title($tour_post), ENT_QUOTES | ENT_HTML5, 'UTF-8'),
            'meta_items' => get_post_meta($tour_post->ID, 'tour_meta_items', true),
            'duration' => get_post_meta($tour_post->ID, 'tour_duration', true),
            'price' => get_post_meta($tour_post->ID, 'tour_price', true),
            'popup_image' => get_post_meta($tour_post->ID, 'tour_popup_image', true),
            'popup_image_mobile' => get_post_meta($tour_post->ID, 'tour_popup_image_mobile', true),
            'intro_title' => get_post_meta($tour_post->ID, 'tour_intro_title', true),
            'intro_content' => get_post_meta($tour_post->ID, 'tour_intro_content', true),
            'itinerary_title' => get_post_meta($tour_post->ID, 'tour_itinerary_title', true),
            'itinerary_content' => get_post_meta($tour_post->ID, 'tour_itinerary_content', true),
            'notes_title' => get_post_meta($tour_post->ID, 'tour_notes_title', true),
            'notes_content' => get_post_meta($tour_post->ID, 'tour_notes_content', true),
            'policy_title' => get_post_meta($tour_post->ID, 'tour_policy_title', true),
            'policy_content' => get_post_meta($tour_post->ID, 'tour_policy_content', true),
        );
    }

    if (!$rows && !get_option('wonom_tours_migrated')) {
        $rows = tr_options_field('wonom_home_options.home_tours');
    }

    if ((!is_array($rows) || !$rows) && !get_option('wonom_tours_migrated')) {
        $legacy_gallery = tr_options_field('wonom_home_options.home_tour_gallery');
        $legacy_image = is_array($legacy_gallery) && $legacy_gallery ? reset($legacy_gallery) : $asset_url . 'tour_bg.webp';
        $rows = array(array(
            'image' => $legacy_image,
            'title' => tr_options_field('wonom_home_options.home_tour_title') ?: 'Tour chụp ảnh câu cá + đánh golf ngoại cảnh',
            'duration' => tr_options_field('wonom_home_options.home_tour_duration') ?: '120 phút',
            'price' => tr_options_field('wonom_home_options.home_tour_price') ?: '2,000,000đ/người',
            'popup_image' => tr_options_field('wonom_home_options.home_tour_popup_image') ?: $asset_url . 'img_popup.webp',
            'intro_title' => tr_options_field('wonom_home_options.home_tour_intro_title') ?: 'Giới thiệu tour',
            'intro_content' => tr_options_field('wonom_home_options.home_tour_intro_content') ?: '',
            'itinerary_title' => tr_options_field('wonom_home_options.home_tour_itinerary_title') ?: 'LỊCH TRÌNH TRẢI NGHIỆM',
            'itinerary_content' => tr_options_field('wonom_home_options.home_tour_itinerary_content') ?: '',
            'notes_title' => tr_options_field('wonom_home_options.home_tour_notes_title') ?: 'THÔNG TIN CẦN LƯU Ý',
            'notes_content' => tr_options_field('wonom_home_options.home_tour_notes_content') ?: '',
            'policy_title' => tr_options_field('wonom_home_options.home_tour_policy_title') ?: 'CHÍNH SÁCH ĐẶT LỊCH',
            'policy_content' => tr_options_field('wonom_home_options.home_tour_policy_content') ?: '',
        ));
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

    $tours = array();
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $image = $resolve_image(isset($row['image']) ? $row['image'] : '');
        if (!$image) {
            $image = $asset_url . 'tour_bg.webp';
        }
        $popup_image = $resolve_image(isset($row['popup_image']) ? $row['popup_image'] : '');
        $popup_image_mobile = $resolve_image(isset($row['popup_image_mobile']) ? $row['popup_image_mobile'] : '');
        $image_mobile = $resolve_image(isset($row['image_mobile']) ? $row['image_mobile'] : '');
        $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
        $image_alt = $title ?: 'Wonom Culture Complex';
        $meta_items = array();
        if (array_key_exists('meta_items', $row)) {
            $raw_meta_items = is_array($row['meta_items']) ? $row['meta_items'] : array();
        } else {
            // Support legacy option rows until they have been migrated to Tour posts.
            $raw_meta_items = array();
            if (!empty($row['duration'])) {
                $raw_meta_items[] = array('label' => 'Thời gian', 'content' => $row['duration']);
            }
            if (!empty($row['price'])) {
                $raw_meta_items[] = array('label' => 'Chi phí', 'content' => $row['price']);
            }
        }
        foreach ($raw_meta_items as $raw_meta_item) {
            if (!is_array($raw_meta_item)) {
                continue;
            }
            $label = isset($raw_meta_item['label']) ? sanitize_text_field($raw_meta_item['label']) : '';
            $content = isset($raw_meta_item['content']) ? sanitize_text_field($raw_meta_item['content']) : '';
            if ('' === $label && '' === $content) {
                continue;
            }
            $meta_items[] = array('label' => $label, 'content' => $content);
        }
        $tours[] = array(
            'id' => isset($row['id']) ? (int) $row['id'] : 0,
            'image' => $image,
            'imageMobile' => $image_mobile,
            'imageAlt' => $image_alt,
            'title' => $title,
            'metaItems' => $meta_items,
            'duration' => isset($row['duration']) ? sanitize_text_field($row['duration']) : '',
            'price' => isset($row['price']) ? sanitize_text_field($row['price']) : '',
            'popupImage' => $popup_image ?: $asset_url . 'img_popup.webp',
            'popupImageMobile' => $popup_image_mobile,
            'popupImageAlt' => $title ?: 'Wonom Culture Complex',
            'introTitle' => isset($row['intro_title']) ? sanitize_text_field($row['intro_title']) : '',
            'introContent' => isset($row['intro_content']) ? wp_kses_post($row['intro_content']) : '',
            'itineraryTitle' => isset($row['itinerary_title']) ? sanitize_text_field($row['itinerary_title']) : '',
            'itineraryContent' => isset($row['itinerary_content']) ? wp_kses_post($row['itinerary_content']) : '',
            'notesTitle' => isset($row['notes_title']) ? sanitize_text_field($row['notes_title']) : '',
            'notesContent' => isset($row['notes_content']) ? wp_kses_post($row['notes_content']) : '',
            'policyTitle' => isset($row['policy_title']) ? sanitize_text_field($row['policy_title']) : '',
            'policyContent' => isset($row['policy_content']) ? wp_kses_post($row['policy_content']) : '',
        );
    }

    return $tours;
}

<?php

/**
 * Normalize Tour settings for the homepage slider and detail popup.
 */
function wonom_get_home_tours()
{
    $rows = tr_options_field('wonom_home_options.home_tours');
    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';

    if (!is_array($rows) || !$rows) {
        $legacy_gallery = tr_options_field('wonom_home_options.home_tour_gallery');
        $legacy_image = is_array($legacy_gallery) && $legacy_gallery ? reset($legacy_gallery) : $asset_url . 'tour_bg.webp';
        $rows = array(array(
            'image' => $legacy_image,
            'image_alt' => 'Không gian tour Wonom',
            'title' => tr_options_field('wonom_home_options.home_tour_title') ?: 'Tour chụp ảnh câu cá + đánh golf ngoại cảnh',
            'duration' => tr_options_field('wonom_home_options.home_tour_duration') ?: '120 phút',
            'price' => tr_options_field('wonom_home_options.home_tour_price') ?: '2,000,000đ/người',
            'popup_image' => tr_options_field('wonom_home_options.home_tour_popup_image') ?: $asset_url . 'img_popup.webp',
            'popup_image_alt' => tr_options_field('wonom_home_options.home_tour_popup_image_alt') ?: '',
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
        return array(
            'url' => $url,
            'alt' => $id ? sanitize_text_field(get_post_meta($id, '_wp_attachment_image_alt', true)) : '',
        );
    };

    $tours = array();
    foreach ($rows as $row) {
        if (!is_array($row)) {
            continue;
        }
        $image = $resolve_image(isset($row['image']) ? $row['image'] : '');
        if (!$image['url']) {
            continue;
        }
        $popup_image = $resolve_image(isset($row['popup_image']) ? $row['popup_image'] : '');
        $title = isset($row['title']) ? sanitize_text_field($row['title']) : '';
        $tours[] = array(
            'image' => $image['url'],
            'imageAlt' => !empty($row['image_alt']) ? sanitize_text_field($row['image_alt']) : $image['alt'],
            'title' => $title,
            'duration' => isset($row['duration']) ? sanitize_text_field($row['duration']) : '',
            'price' => isset($row['price']) ? sanitize_text_field($row['price']) : '',
            'popupImage' => $popup_image['url'] ?: $image['url'],
            'popupImageAlt' => !empty($row['popup_image_alt']) ? sanitize_text_field($row['popup_image_alt']) : ($popup_image['alt'] ?: $title),
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

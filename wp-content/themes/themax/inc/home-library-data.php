<?php

function wonom_get_home_library_images()
{
    $rows = tr_options_field('wonom_home_options.home_library_images');
    $asset_url = trailingslashit(get_template_directory_uri()) . 'asset/img/';
    if (!is_array($rows) || !$rows) {
        $defaults = array('home_banner.webp', 'home-hero2.webp', 'home-hero3.webp', 'store.jpg', 'img_popup.webp', 'home_banner.webp', 'home-hero2.webp', 'store.jpg');
        $rows = array_map(static function ($filename) use ($asset_url) {
            return array('image' => $asset_url . $filename, 'tag' => 'WONOM');
        }, $defaults);
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

    $images = array();
    foreach ($rows as $row) {
        if (!is_array($row)) continue;
        $image = $resolve_image(isset($row['image']) ? $row['image'] : '');
        if (!$image) continue;
        $tag = isset($row['tag']) ? sanitize_text_field($row['tag']) : '';
        $clean_tag = ltrim($tag, '#');
        $image_alt = $clean_tag && 0 !== strcasecmp($clean_tag, 'WONOM')
            ? $clean_tag . ' · Wonom'
            : 'Wonom Culture Complex';
        $images[] = array(
            'src' => $image,
            'alt' => $image_alt,
            'tag' => $tag,
            'category' => 'all',
        );
    }
    return $images;
}

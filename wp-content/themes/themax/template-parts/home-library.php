<?php

ob_start();
get_template_part('template-parts/layouts/home', 'library');
$library_markup = ob_get_clean();
$library_images = wonom_get_home_library_images();
if (!$library_markup || !$library_images || !class_exists('DOMDocument')) {
    echo $library_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$library_dom = new DOMDocument('1.0', 'UTF-8');
$library_state = libxml_use_internal_errors(true);
$library_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-library-root">' . $library_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($library_state);
$library_xpath = new DOMXPath($library_dom);
$library_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$library_first = static function ($query, $context = null) use ($library_xpath) {
    $nodes = $library_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$library_set_text = static function ($node, $text) use ($library_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    $node->appendChild($library_dom->createTextNode((string) $text));
};
$library_option = static function ($field, $fallback) {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$section = $library_dom->getElementById('library');
$library_set_text($library_first('.//*[' . $library_class('home_space_left_subtitle') . ']', $section), $library_option('home_library_subtitle', 'BÊN TRONG WONOM THẾ NÀO!'));
$library_set_text($library_first('.//*[' . $library_class('home_event_title') . ']', $section), $library_option('home_library_title', 'Những khoảnh khắc của không gian.'));
$library_set_text($library_first('.//*[' . $library_class('home_space_left_des') . ']', $section), $library_option('home_library_description', 'Không gian chạm và tận hưởng văn hóa đời thường Hàn Quốc ngay tại Sài Gòn, mở ra nhịp cầu kết nối văn hóa hai quốc gia.'));
$library_set_text($library_first('.//*[' . $library_class('home_space_left_button') . ']//*[' . $library_class('btn-inner') . ']', $section), 'XEM TẤT CẢ HÌNH ẢNH');

$tracks = $library_xpath->query('.//*[' . $library_class('home_space_right_card_wrap') . ']', $section);
if ($tracks && $tracks->length >= 2) {
    foreach ($tracks as $track) while ($track->firstChild) $track->removeChild($track->firstChild);
    $track_positions = array(0, 0);
    foreach ($library_images as $index => $item) {
        $track_index = $index % 2;
        $track_position = $track_positions[$track_index]++;
        $is_square = 0 === $track_index
            ? 0 === $track_position % 2
            : 1 === $track_position % 2;
        $track = $tracks->item($track_index);
        $slide = $library_dom->createElement('div');
        $slide->setAttribute(
            'class',
            'home_space_right_card_item swiper-slide ' . ($is_square ? 'is-square' : 'is-portrait')
        );
        $image_wrap = $library_dom->createElement('div');
        $image_wrap->setAttribute('class', 'home_space_right_card_item_img img_fullfill');
        $image = $library_dom->createElement('img');
        $image->setAttribute('src', $item['src']);
        $image->setAttribute('alt', $item['alt']);
        $image_wrap->appendChild($image);
        $tag = $library_dom->createElement('div');
        $tag->setAttribute('class', 'home_space_right_card_item_tag txt_14 txt_bold');
        $tag->appendChild($library_dom->createTextNode($item['tag']));
        $slide->appendChild($image_wrap);
        $slide->appendChild($tag);
        $track->appendChild($slide);
    }
}

$output = $section ? $library_dom->saveHTML($section) : $library_markup;
$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $asset_url, $output);
echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

<?php

$tour_source_file = get_theme_file_path('/homepage.html');
$tour_source = is_readable($tour_source_file) ? file_get_contents($tour_source_file) : '';
$tour_markup = '';

if ($tour_source && preg_match('/(<section id="tour" class="home_tour pa_section relative">.*?<\/section>)\s*(?=<section id="event")/is', $tour_source, $tour_match)) {
    $tour_markup = $tour_match[1];
}

$tours = wonom_get_home_tours();
if (!$tour_markup || !$tours || !class_exists('DOMDocument')) {
    echo $tour_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$tour_dom = new DOMDocument('1.0', 'UTF-8');
$tour_libxml_state = libxml_use_internal_errors(true);
$tour_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-tour-root">' . $tour_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($tour_libxml_state);
$tour_xpath = new DOMXPath($tour_dom);
$tour_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$tour_first = static function ($query) use ($tour_xpath) {
    $nodes = $tour_xpath->query($query);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$tour_set_text = static function ($node, $text) use ($tour_dom) {
    if (!$node) {
        return;
    }
    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }
    $node->appendChild($tour_dom->createTextNode((string) $text));
};

$main_wrap = $tour_first('//*[' . $tour_class('home_tour_main_wrap') . ']');
$thumb_wrap = $tour_first('//*[' . $tour_class('home_tour_card_slide_wrap') . ']');
if ($main_wrap && $thumb_wrap) {
    while ($main_wrap->firstChild) {
        $main_wrap->removeChild($main_wrap->firstChild);
    }
    while ($thumb_wrap->firstChild) {
        $thumb_wrap->removeChild($thumb_wrap->firstChild);
    }
    foreach ($tours as $index => $tour) {
        $slide = $tour_dom->createElement('div');
        $slide->setAttribute('class', 'home_tour_main_item swiper-slide');
        $slide->setAttribute('data-tour-index', (string) $index);
        $image = $tour_dom->createElement('img');
        $image->setAttribute('src', $tour['image']);
        $image->setAttribute('alt', $tour['imageAlt']);
        $slide->appendChild($image);
        $main_wrap->appendChild($slide);

        $thumb = $tour_dom->createElement('button');
        $thumb->setAttribute('class', 'home_tour_card_slide_item swiper-slide');
        $thumb->setAttribute('type', 'button');
        $thumb->setAttribute('data-tour-index', (string) $index);
        $thumb->setAttribute('aria-label', sprintf('Xem tour %s', $tour['title']));
        $thumb_image = $tour_dom->createElement('img');
        $thumb_image->setAttribute('src', $tour['image']);
        $thumb_image->setAttribute('alt', $tour['imageAlt']);
        $thumb->appendChild($thumb_image);
        $thumb_wrap->appendChild($thumb);
    }
}

$title_node = $tour_first('//*[' . $tour_class('home_tour_card_title') . ']');
if ($title_node) {
    $tour_set_text($title_node, $tours[0]['title']);
}
$tour_set_text($tour_first('//*[' . $tour_class('home_tour_card_detail_see_txt') . ']'), 'XEM CHI TIẾT');
$section = $tour_dom->getElementById('tour');
if ($section) {
    $data_script = $tour_dom->createElement('script');
    $data_script->setAttribute('id', 'wonom-tour-data');
    $data_script->setAttribute('type', 'application/json');
    $data_script->appendChild($tour_dom->createTextNode(wp_json_encode($tours, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)));
    $section->appendChild($data_script);
}

$tour_output = $section ? $tour_dom->saveHTML($section) : $tour_markup;
$tour_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$tour_output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $tour_asset_url, $tour_output);
echo $tour_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

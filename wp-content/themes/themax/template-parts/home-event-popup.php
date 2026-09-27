<?php

$event_popup_source_file = get_theme_file_path('/homepage.html');
$event_popup_source = is_readable($event_popup_source_file) ? file_get_contents($event_popup_source_file) : '';
$event_popup_markup = '';
if ($event_popup_source && preg_match('/(<section class="popup_tour event"[^>]*>.*?<\/section>)\s*(?=<section class="popup_tour policy")/is', $event_popup_source, $event_popup_match)) {
    $event_popup_markup = $event_popup_match[1];
}
$events = wonom_get_home_events();
if (!$event_popup_markup || !$events || !class_exists('DOMDocument')) {
    echo $event_popup_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$popup_dom = new DOMDocument('1.0', 'UTF-8');
$popup_state = libxml_use_internal_errors(true);
$popup_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-event-popup-root">' . $event_popup_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($popup_state);
$popup_xpath = new DOMXPath($popup_dom);
$popup_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$popup_first = static function ($query, $context = null) use ($popup_xpath) {
    $nodes = $popup_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$popup_set_text = static function ($node, $text) use ($popup_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    $node->appendChild($popup_dom->createTextNode((string) $text));
};
$popup_set_html = static function ($node, $html) use ($popup_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    if ('' === $html) return;
    $fragment = new DOMDocument('1.0', 'UTF-8');
    $state = libxml_use_internal_errors(true);
    $fragment->loadHTML('<?xml encoding="utf-8" ?><div id="event-fragment">' . wp_kses_post($html) . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($state);
    $root = $fragment->getElementById('event-fragment');
    if ($root) foreach (iterator_to_array($root->childNodes) as $child) $node->appendChild($popup_dom->importNode($child, true));
};

$event = $events[0];
$popup_set_text($popup_first('//*[' . $popup_class('popup_tour_title') . ']'), 'SỰ KIỆN ĐANG DIỄN RA');
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_sidebar_card_title') . ']'), $event['title']);
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_apply') . ']'), $event['apply']);
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_category') . ']'), $event['category']);
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_condition') . ']'), $event['condition']);
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_content_title') . ']'), $event['title']);
$popup_set_text($popup_first('//*[' . $popup_class('event_popup_content_subtitle') . ']'), $event['subtitle']);
$popup_set_html($popup_first('//*[' . $popup_class('popup_tour_content_des') . ']'), $event['content']);
$image = $popup_first('//*[' . $popup_class('event_popup_content_img') . ']//img');
if ($image) {
    $image->setAttribute('src', $event['popupImage']);
    $image->setAttribute('alt', $event['popupImageAlt']);
}

$list = $popup_first('//*[' . $popup_class('popup_tour_sidebar_suggest') . ']');
if ($list) {
    while ($list->firstChild) $list->removeChild($list->firstChild);
    foreach ($events as $index => $item_data) {
        $button = $popup_dom->createElement('button');
        $button->setAttribute('class', 'popup_tour_sidebar_suggest_item' . (0 === $index ? ' active' : ''));
        $button->setAttribute('type', 'button');
        $button->setAttribute('data-event-index', (string) $index);
        $image_wrap = $popup_dom->createElement('span');
        $image_wrap->setAttribute('class', 'popup_tour_sidebar_suggest_item_img img_full');
        $thumb = $popup_dom->createElement('img');
        $thumb->setAttribute('src', $item_data['image']);
        $thumb->setAttribute('alt', $item_data['imageAlt']);
        $image_wrap->appendChild($thumb);
        $title = $popup_dom->createElement('span');
        $title->setAttribute('class', 'popup_tour_sidebar_suggest_item_title txt_bold');
        $title->appendChild($popup_dom->createTextNode($item_data['title']));
        $button->appendChild($image_wrap);
        $button->appendChild($title);
        $list->appendChild($button);
    }
}

$root = $popup_dom->getElementById('wonom-event-popup-root');
$section = $root ? $popup_first('./section', $root) : null;
$output = $section ? $popup_dom->saveHTML($section) : $event_popup_markup;
$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $asset_url, $output);
echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

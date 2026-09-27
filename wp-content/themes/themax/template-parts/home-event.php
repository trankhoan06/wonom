<?php

$event_source_file = get_theme_file_path('/homepage.html');
$event_source = is_readable($event_source_file) ? file_get_contents($event_source_file) : '';
$event_markup = '';
if ($event_source && preg_match('/(<section id="event" class="home_event pa_section">.*?<\/section>)\s*(?=<section id="library")/is', $event_source, $event_match)) {
    $event_markup = $event_match[1];
}
$events = wonom_get_home_events();
if (!$event_markup || !$events || !class_exists('DOMDocument')) {
    echo $event_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$event_dom = new DOMDocument('1.0', 'UTF-8');
$event_libxml_state = libxml_use_internal_errors(true);
$event_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-event-root">' . $event_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($event_libxml_state);
$event_xpath = new DOMXPath($event_dom);
$event_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$event_first = static function ($query, $context = null) use ($event_xpath) {
    $nodes = $event_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$event_set_text = static function ($node, $text) use ($event_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    $node->appendChild($event_dom->createTextNode((string) $text));
};
$event_option = static function ($field, $fallback) {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$event_set_text($event_first('//*[' . $event_class('home_event_subtitle') . ']'), $event_option('home_event_subtitle', 'ĐANG DIỄN RA'));
$title_node = $event_first('//*[@id="event"]//*[' . $event_class('home_event_title') . ']');
if ($title_node) {
    while ($title_node->firstChild) $title_node->removeChild($title_node->firstChild);
    $title_lines = preg_split('/\r\n|\r|\n/', (string) $event_option('home_event_title', "Sự kiện đang diễn ra &\nchương trình theo mùa"));
    foreach ($title_lines as $index => $line) {
        if ($index) $title_node->appendChild($event_dom->createElement('br'));
        $title_node->appendChild($event_dom->createTextNode($line));
    }
}

$card_wrap = $event_first('//*[' . $event_class('home_event_card_wrap') . ']');
$prototype = $card_wrap ? $event_first('./*[' . $event_class('home_event_card_item') . ']', $card_wrap) : null;
if ($card_wrap && $prototype) {
    while ($card_wrap->firstChild) $card_wrap->removeChild($card_wrap->firstChild);
    foreach ($events as $index => $event) {
        $card = $prototype->cloneNode(true);
        $card->setAttribute('data-event-index', (string) $index);
        $card->setAttribute('role', 'button');
        $card->setAttribute('tabindex', '0');
        $image = $event_first('.//*[' . $event_class('home_event_card_item_img') . ']//img', $card);
        if ($image) {
            $image->setAttribute('src', $event['image']);
            $image->setAttribute('alt', $event['imageAlt']);
        }
        $event_set_text($event_first('.//*[' . $event_class('home_event_card_item_txt_subtitle') . ']', $card), $event['apply']);
        $event_set_text($event_first('.//*[' . $event_class('home_event_card_item_txt_title') . ']', $card), $event['title']);
        $event_set_text($event_first('.//*[' . $event_class('home_event_card_item_txt_des') . ']', $card), $event['subtitle']);
        $event_set_text($event_first('.//*[' . $event_class('home_event_card_item_label') . ']', $card), $event['category']);
        $card_wrap->appendChild($card);
    }
}

$event_section = $event_dom->getElementById('event');
if ($event_section) {
    $data_script = $event_dom->createElement('script');
    $data_script->setAttribute('id', 'wonom-event-data');
    $data_script->setAttribute('type', 'application/json');
    $data_script->appendChild($event_dom->createTextNode(wp_json_encode($events, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)));
    $event_section->appendChild($data_script);
}
$event_output = $event_section ? $event_dom->saveHTML($event_section) : $event_markup;
$event_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$event_output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $event_asset_url, $event_output);
echo $event_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

<?php

$popup_source_file = get_theme_file_path('/homepage.html');
$popup_source = is_readable($popup_source_file) ? file_get_contents($popup_source_file) : '';
$popup_markup = '';
if ($popup_source && preg_match('/(<section class="popup_tour tour">.*?<\/section>)\s*(?=<section class="popup_tour event")/is', $popup_source, $popup_match)) {
    $popup_markup = $popup_match[1];
}

$tours = wonom_get_home_tours();
if (!$popup_markup || !$tours || !class_exists('DOMDocument')) {
    echo $popup_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$popup_dom = new DOMDocument('1.0', 'UTF-8');
$popup_libxml_state = libxml_use_internal_errors(true);
$popup_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-tour-popup-root">' . $popup_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($popup_libxml_state);
$popup_xpath = new DOMXPath($popup_dom);
$popup_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$popup_first = static function ($query, $context = null) use ($popup_xpath) {
    $nodes = $popup_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$popup_set_node_text = static function ($node, $value) use ($popup_dom) {
    if (!$node) {
        return;
    }
    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }
    $node->appendChild($popup_dom->createTextNode((string) $value));
};
$popup_set_text = static function ($class_name, $value) use ($popup_first, $popup_class, $popup_set_node_text) {
    $node = $popup_first('//*[' . $popup_class($class_name) . ']');
    if ($node && null !== $value && '' !== $value) {
        $popup_set_node_text($node, $value);
    }
};
$popup_set_html = static function ($node, $html) use ($popup_dom) {
    if (!$node) {
        return;
    }
    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }
    if ('' === $html) {
        return;
    }
    $fragment = new DOMDocument('1.0', 'UTF-8');
    $fragment_state = libxml_use_internal_errors(true);
    $fragment->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-tour-fragment">' . wp_kses_post($html) . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();
    libxml_use_internal_errors($fragment_state);
    $root = $fragment->getElementById('wonom-tour-fragment');
    if ($root) {
        foreach (iterator_to_array($root->childNodes) as $child) {
            $node->appendChild($popup_dom->importNode($child, true));
        }
    }
};
$tour = $tours[0];
$popup_set_text('popup_tour_title', 'TOUR THAM QUAN');
$popup_set_text('popup_tour_sidebar_card_title', $tour['title']);
$popup_set_text('popup_tour_content_title', $tour['title']);
$popup_set_text('popup_tour_sidebar_card_time', 'THỜI GIAN');
$popup_set_text('popup_tour_sidebar_card_timedes', $tour['duration']);
$popup_set_text('popup_tour_sidebar_card_cost', 'CHI PHÍ');
$popup_set_text('popup_tour_sidebar_card_costdes', $tour['price']);
$popup_set_text('popup_tour_sidebar_card_button_inner', 'ĐẶT LỊCH CHUYẾN ĐI');
$popup_set_text('popup_tour_sidebar_title', 'GỢI Ý CÁC TOUR KHÁC');
$popup_set_text('tour_detail_form_submit', 'GỬI YÊU CẦU ĐẶT LỊCH');
$popup_set_text('tour_detail_form_note', 'Bạn sẽ được chuyển sang Zalo để xác nhận lịch với nhân viên.');
$popup_set_text('tour_detail_form_divider', 'HOẶC GỌI HỖ TRỢ');

$popup_image = $popup_first('//*[' . $popup_class('popup_tour_content_img') . ']//img');
if ($popup_image) {
    $popup_image->setAttribute('src', $tour['popupImage']);
    $popup_image->setAttribute('alt', $tour['popupImageAlt']);
}

$content_titles = $popup_xpath->query('//*[' . $popup_class('popup_tour_content_txt') . ']/*[' . $popup_class('popup_tour_content_subtitle') . ']');
$content_bodies = $popup_xpath->query('//*[' . $popup_class('popup_tour_content_txt') . ']/*[' . $popup_class('popup_tour_content_des') . ']');
$sections = array(
    array($tour['introTitle'], $tour['introContent']),
    array($tour['itineraryTitle'], $tour['itineraryContent']),
    array($tour['notesTitle'], $tour['notesContent']),
    array($tour['policyTitle'], $tour['policyContent']),
);
foreach ($sections as $index => $section) {
    if ($content_titles && $content_titles->item($index)) {
        $popup_set_node_text($content_titles->item($index), $section[0]);
    }
    if ($content_bodies && $content_bodies->item($index)) {
        $popup_set_html($content_bodies->item($index), $section[1]);
    }
}

$suggestions_wrap = $popup_first('//*[' . $popup_class('popup_tour_sidebar_suggest') . ']');
if ($suggestions_wrap) {
    while ($suggestions_wrap->firstChild) {
        $suggestions_wrap->removeChild($suggestions_wrap->firstChild);
    }
    foreach ($tours as $index => $suggestion) {
        if (0 === $index) {
            continue;
        }
        $item = $popup_dom->createElement('button');
        $item->setAttribute('class', 'popup_tour_sidebar_suggest_item');
        $item->setAttribute('type', 'button');
        $item->setAttribute('data-tour-index', (string) $index);
        $image_wrap = $popup_dom->createElement('span');
        $image_wrap->setAttribute('class', 'popup_tour_sidebar_suggest_item_img img_full');
        $image = $popup_dom->createElement('img');
        $image->setAttribute('src', $suggestion['image']);
        $image->setAttribute('alt', $suggestion['imageAlt']);
        $image_wrap->appendChild($image);
        $title = $popup_dom->createElement('span');
        $title->setAttribute('class', 'popup_tour_sidebar_suggest_item_title txt_bold txt_16 cl_dark_brown');
        $title->appendChild($popup_dom->createTextNode($suggestion['title']));
        $item->appendChild($image_wrap);
        $item->appendChild($title);
        $suggestions_wrap->appendChild($item);
    }
}

$hidden_name = $popup_first('//input[@name="tour_name"]');
if ($hidden_name) {
    $hidden_name->setAttribute('value', $tour['title']);
}
$phone = '0968 487 096';
$phone_node = $popup_first('//*[' . $popup_class('tour_detail_form_phone') . ']');
if ($phone_node) {
    $popup_set_node_text($phone_node, $phone);
    $phone_node->setAttribute('href', 'tel:' . preg_replace('/[^0-9+]/', '', $phone));
}

$popup_root = $popup_dom->getElementById('wonom-tour-popup-root');
$popup_section = $popup_root ? $popup_first('./section', $popup_root) : null;
$popup_output = $popup_section ? $popup_dom->saveHTML($popup_section) : $popup_markup;
$popup_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$popup_output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $popup_asset_url, $popup_output);
echo $popup_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

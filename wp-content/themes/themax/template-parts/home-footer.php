<?php

ob_start();
get_template_part('template-parts/layouts/home', 'footer');
$footer_markup = ob_get_clean();
if (!$footer_markup || !class_exists('DOMDocument')) {
    echo $footer_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$footer_dom = new DOMDocument('1.0', 'UTF-8');
$footer_state = libxml_use_internal_errors(true);
$footer_dom->loadHTML('<?xml encoding="utf-8" ?><div id="wonom-footer-root">' . $footer_markup . '</div>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
libxml_clear_errors();
libxml_use_internal_errors($footer_state);
$footer_xpath = new DOMXPath($footer_dom);
$footer_class = static function ($name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $name . ' ")';
};
$footer_first = static function ($query, $context = null) use ($footer_xpath) {
    $nodes = $footer_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};
$footer_set_text = static function ($node, $text) use ($footer_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    $node->appendChild($footer_dom->createTextNode((string) $text));
};
$footer_set_lines = static function ($node, $text) use ($footer_dom) {
    if (!$node) return;
    while ($node->firstChild) $node->removeChild($node->firstChild);
    foreach (preg_split('/\r\n|\r|\n/', (string) $text) as $index => $line) {
        if ($index) $node->appendChild($footer_dom->createElement('br'));
        $node->appendChild($footer_dom->createTextNode($line));
    }
};
$footer_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$footer = $footer_dom->getElementById('contact');
$footer_set_text($footer_first('.//*[' . $footer_class('footer_info_subtitle') . ']', $footer), $footer_option('home_footer_subtitle', 'GHÉ THĂM'));
$footer_set_lines($footer_first('.//*[' . $footer_class('footer_info_title') . ']', $footer), $footer_option('home_footer_title', "Hẹn gặp bạn\ntại WONOM - HCMC"));

$info_items = $footer_xpath->query('.//*[' . $footer_class('footer_info_tag_item_txt') . ']', $footer);
$info_values = array(
    $footer_option('home_footer_address', '40A Trần Cao Vân, Phường Xuân Hòa, TP Hồ Chí Minh, Việt Nam'),
    $footer_option('home_footer_hours', 'Hằng ngày 11:00 – 23:00 & Rooftop đến 01:00'),
    $footer_option('home_footer_email', 'support@wonom.vn'),
);
foreach ($info_values as $index => $value) {
    if ($info_items && $info_items->item($index)) $footer_set_text($info_items->item($index), $value);
}

$footer_set_text($footer_first('.//*[' . $footer_class('footer_contact_title') . ']', $footer), $footer_option('home_footer_hotline_title', 'GỌI HOTLINE TƯ VẤN'));
$footer_set_text($footer_first('.//*[' . $footer_class('footer_contact_des') . ']', $footer), $footer_option('home_footer_hotline_description', 'Gọi Hotline hoặc liên hệ Zalo để giữ chỗ nhanh nhất ngay!!!'));
$phone = sanitize_text_field($footer_option('home_footer_phone', '0968 487 096'));
$footer_set_text($footer_first('.//*[' . $footer_class('footer_contact_num_txt') . ']', $footer), $phone);
$phone_wrap = $footer_first('.//*[' . $footer_class('footer_contact_num') . ']', $footer);
if ($phone_wrap) {
    $phone_wrap->setAttribute('role', 'link');
    $phone_wrap->setAttribute('tabindex', '0');
    $phone_wrap->setAttribute('data-footer-href', 'tel:' . preg_replace('/[^0-9+]/', '', $phone));
}
$zalo_button = $footer_first('.//*[' . $footer_class('footer_contact_button') . ']', $footer);
$zalo_url = esc_url($footer_option('home_footer_zalo_url'));
if ($zalo_button && $zalo_url) {
    $zalo_button->setAttribute('role', 'link');
    $zalo_button->setAttribute('tabindex', '0');
    $zalo_button->setAttribute('data-footer-href', $zalo_url);
}
$footer_set_text($footer_first('.//*[' . $footer_class('footer_contact_info_locate') . ']', $footer), $footer_option('home_footer_location_line', '안녕! Ban Ga Wo · Ho Chi Minh City, Vietnam'));

$social_links = $footer_xpath->query('.//a[' . $footer_class('footer_contact_info_social_icon') . ']', $footer);
$social_urls = array(
    esc_url($footer_option('home_footer_facebook_url', '#')),
    esc_url($footer_option('home_footer_instagram_url', '#')),
    esc_url($footer_option('home_footer_tiktok_url', '#')),
);
foreach ($social_urls as $index => $url) {
    if ($social_links && $social_links->item($index)) {
        $social_links->item($index)->setAttribute('href', $url ?: '#');
        if ($url && '#' !== $url) {
            $social_links->item($index)->setAttribute('target', '_blank');
            $social_links->item($index)->setAttribute('rel', 'noopener noreferrer');
        }
    }
}

for ($floor = 1; $floor <= 5; $floor++) {
    $floor_item = $footer_first('.//*[@data-explore-tab="tab' . $floor . 'f"]', $footer);
    if (!$floor_item) continue;
    $floor_name = $footer_option('home_explore_floor_' . $floor . '_nav_title', array(1 => 'Nhà Hàng', 2 => 'Culture Studio', 3 => 'Workshop', 4 => 'Stress Room', 5 => 'Rooftop')[$floor]);
    $footer_set_text($footer_first('.//*[' . $footer_class('footer_bot_left_list_item_txt') . ']', $floor_item), $floor_name);
}

$output = $footer ? $footer_dom->saveHTML($footer) : $footer_markup;
$asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $asset_url, $output);
echo $output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

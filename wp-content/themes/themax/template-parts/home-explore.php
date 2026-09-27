<?php

$explore_source_file = get_theme_file_path('/homepage.html');
$explore_source = is_readable($explore_source_file) ? file_get_contents($explore_source_file) : '';
$explore_markup = '';

if ($explore_source && preg_match('/(<section id="explore" class="home_explore pa_section">.*?<\/section>)\s*(?=<section id="tour")/is', $explore_source, $explore_match)) {
    $explore_markup = $explore_match[1];
}

if (!$explore_markup || !class_exists('DOMDocument')) {
    echo $explore_markup; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    return;
}

$explore_defaults = array(
    1 => array('nav' => 'Nhà hàng', 'subtitle' => '1F - NHÀ HÀNG MUJIGE', 'title' => "Ẩm thực fusion\nHàn-Việt", 'description' => 'Thưởng thức shabu-shabu, bulgogi và kimbap dưới bức họa pop-art khổng lồ.', 'tags' => '#Không gian, #Ẩm thực, #Hàn Quốc', 'meta_1_label' => 'GIỜ MỞ CỬA:', 'meta_1_value' => 'Hằng ngày từ 11:00 đến 23:00', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM THỰC ĐƠN', 'booking' => 'ĐẶT BÀN GIỮ CHỖ'),
    2 => array('nav' => 'Culture Studio', 'subtitle' => '2F - TRANG PHỤC TRUYỀN THỐNG', 'title' => 'CHO THUÊ HANBOK VÁY + STUDIO CHỤP ẢNH', 'description' => 'Thuê hanbok, váy và tự chụp những bức ảnh nghệ thuật với ánh sáng chuyên nghiệp.', 'tags' => '#Trải nghiệm hanbok, #Váy, #Không gian, #Selfie', 'meta_1_label' => 'GIỜ THUÊ:', 'meta_1_value' => '60 phút/người', 'meta_2_label' => '', 'meta_2_value' => '', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH THUÊ'),
    3 => array('nav' => 'Workshop', 'subtitle' => '3F - WORKSHOP', 'title' => "XƯỞNG LÀM ĐỒ\nTHỦ CÔNG Ở WONOM", 'description' => 'Tự tay tạo nên món quà mang dấu ấn riêng. Trải nghiệm lớp học thủ công, thỏa sức sáng tạo và mang về tác phẩm do chính bạn hoàn thành.', 'tags' => '#Lớp thủ công, #Quà lưu niệm, #Đồ handmade', 'meta_1_label' => 'THỜI GIAN', 'meta_1_value' => '60 phút', 'meta_2_label' => 'SỐ LƯỢNG', 'meta_2_value' => '200+ Mẫu', 'primary' => 'KHÁM PHÁ NGAY', 'booking' => 'ĐẶT LỊCH HẸN'),
    4 => array('nav' => 'Stress Room', 'subtitle' => '4F - STRESS ROOM', 'title' => "PHÒNG GIẢI TỎA\nCƠN GIẬN", 'description' => 'Phòng giải tỏa căng thẳng bằng cách đập phá đồ vật trong không gian riêng biệt, trang bị đầy đủ đồ bảo hộ an toàn.', 'tags' => '#Đồ bảo hộ, #Theo suất, #Ném chai bia', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM BẢNG GIÁ', 'booking' => 'ĐẶT LỊCH HẸN'),
    5 => array('nav' => 'Rooftop Disco', 'subtitle' => '5F - ROOFTOP DISCO', 'title' => "QUẦY BAR MIRROR BALL\n· LOUNGE VỀ ĐÊM", 'description' => 'Tận hưởng những ly cocktail đầy cảm hứng dưới ánh đèn neon và mirror ball sôi động, thư giãn và ngắm nhìn thành phố về đêm.', 'tags' => '#Soju-cocktail, #Chill, #View thành phố, #Nhạc', 'meta_1_label' => 'HÌNH THỨC', 'meta_1_value' => 'Lượt, Phòng', 'meta_2_label' => 'QUY ĐỊNH', 'meta_2_value' => 'Có đồ bảo hộ', 'primary' => 'XEM MENU', 'booking' => 'ĐẶT CHỖ NHANH'),
);

$explore_option = static function ($field, $fallback = '') {
    $value = tr_options_field('wonom_home_options.' . $field);
    return null === $value || false === $value || '' === $value ? $fallback : $value;
};

$explore_image_url = static function ($image) {
    if (is_numeric($image)) {
        return wp_get_attachment_image_url((int) $image, 'full') ?: '';
    }
    if (is_array($image)) {
        $attachment_id = isset($image['id']) ? (int) $image['id'] : 0;
        $url = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'full') : '';
        return $url ?: (isset($image['url']) ? $image['url'] : '');
    }
    return is_string($image) ? $image : '';
};

$explore_dom = new DOMDocument('1.0', 'UTF-8');
$previous_libxml_state = libxml_use_internal_errors(true);
$explore_dom->loadHTML(
    '<?xml encoding="utf-8" ?><div id="wonom-explore-root">' . $explore_markup . '</div>',
    LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
);
libxml_clear_errors();
libxml_use_internal_errors($previous_libxml_state);
$explore_xpath = new DOMXPath($explore_dom);

$explore_first = static function ($query, $context = null) use ($explore_xpath) {
    $nodes = $explore_xpath->query($query, $context);
    return $nodes && $nodes->length ? $nodes->item(0) : null;
};

$explore_set_text = static function ($node, $text) {
    if ($node) {
        $node->nodeValue = $text;
    }
};

$explore_set_html = static function ($node, $html) use ($explore_dom) {
    if (!$node) {
        return;
    }

    while ($node->firstChild) {
        $node->removeChild($node->firstChild);
    }

    $fragment_dom = new DOMDocument('1.0', 'UTF-8');
    $previous_state = libxml_use_internal_errors(true);
    $fragment_dom->loadHTML(
        '<?xml encoding="utf-8" ?><div id="wonom-fragment">' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();
    libxml_use_internal_errors($previous_state);

    $fragment_root = $fragment_dom->getElementById('wonom-fragment');
    if (!$fragment_root) {
        return;
    }

    foreach (iterator_to_array($fragment_root->childNodes) as $child) {
        $node->appendChild($explore_dom->importNode($child, true));
    }
};

$class_query = static function ($class_name) {
    return 'contains(concat(" ", normalize-space(@class), " "), " ' . $class_name . ' ")';
};

$sidebar_title = $explore_first('//*[@id="explore"]//*[contains(concat(" ", normalize-space(@class), " "), " home_explore_sidebar_title ")]');
$explore_set_text($sidebar_title, $explore_option('home_explore_sidebar_title', 'Hãy chọn tầng.. bạn muốn đến!!!'));

foreach ($explore_defaults as $floor => $defaults) {
    $prefix = 'home_explore_floor_' . $floor . '_';
    $tab_key = 'tab' . $floor . 'f';
    $nav_title = $explore_option($prefix . 'nav_title', $defaults['nav']);
    $subtitle = $explore_option($prefix . 'subtitle', $defaults['subtitle']);
    $title = $explore_option($prefix . 'title', $defaults['title']);
    $description = $explore_option($prefix . 'description', $defaults['description']);
    $tags_value = $explore_option($prefix . 'tags', $defaults['tags']);

    $sidebar_item = $explore_first('//*[@data-tab="' . $tab_key . '" and ' . $class_query('home_explore_sidebar_tab_item') . ']');
    if ($sidebar_item) {
        $explore_set_text($explore_first('.//*[' . $class_query('home_explore_list_card_item_content_num') . ']', $sidebar_item), $floor . 'F');
        $explore_set_text($explore_first('.//*[' . $class_query('home_explore_list_card_item_content_txt') . ']', $sidebar_item), $nav_title);
    }

    $floor_section = $explore_first('//*[@data-floor-section="' . $tab_key . '"]');
    if (!$floor_section) {
        continue;
    }

    $subtitle_nodes = $explore_xpath->query('.//*[' . $class_query('home_explore_content_subtitle') . ']', $floor_section);
    if ($subtitle_nodes) {
        foreach ($subtitle_nodes as $subtitle_node) {
            $explore_set_text($subtitle_node, $subtitle);
        }
    }

    $explore_set_html(
        $explore_first('.//*[' . $class_query('home_explore_content_title') . ']', $floor_section),
        nl2br(esc_html($title))
    );
    $explore_set_html(
        $explore_first('.//*[' . $class_query('home_explore_content_des') . ']', $floor_section),
        wp_kses_post($description)
    );

    $tags_wrap = $explore_first('.//*[' . $class_query('home_explore_content_tag') . ']', $floor_section);
    if ($tags_wrap) {
        while ($tags_wrap->firstChild) {
            $tags_wrap->removeChild($tags_wrap->firstChild);
        }
        $tags = preg_split('/[\r\n,]+/', $tags_value);
        foreach ($tags as $tag) {
            $tag = trim($tag);
            if ('' === $tag) {
                continue;
            }
            $tag_node = $explore_dom->createElement('div');
            $tag_node->setAttribute('class', 'home_explore_content_tag_item txt_14 txt_bold');
            $tag_node->appendChild($explore_dom->createTextNode($tag));
            $tags_wrap->appendChild($tag_node);
        }
    }

    $single_meta_label = $explore_first('.//*[' . $class_query('home_explore_content_time') . ']', $floor_section);
    if ($single_meta_label) {
        $explore_set_text($single_meta_label, $explore_option($prefix . 'meta_1_label', $defaults['meta_1_label']));
        $explore_set_text(
            $explore_first('.//*[' . $class_query('home_explore_content_txttime') . ']', $floor_section),
            $explore_option($prefix . 'meta_1_value', $defaults['meta_1_value'])
        );
    } else {
        $meta_items = $explore_xpath->query('.//*[' . $class_query('workshop_meta_item') . ']', $floor_section);
        if ($meta_items) {
            for ($meta_index = 0; $meta_index < min(2, $meta_items->length); $meta_index++) {
                $meta_item = $meta_items->item($meta_index);
                $number = $meta_index + 1;
                $explore_set_text($explore_first('.//*[' . $class_query('cl_meta') . ']', $meta_item), $explore_option($prefix . 'meta_' . $number . '_label', $defaults['meta_' . $number . '_label']));
                $explore_set_text($explore_first('.//*[' . $class_query('txt_bold') . ']', $meta_item), $explore_option($prefix . 'meta_' . $number . '_value', $defaults['meta_' . $number . '_value']));
            }
        }
    }

    $primary_button = $explore_first('.//*[' . $class_query('home_explore_content_button_see') . ']', $floor_section);
    $booking_button = $explore_first('.//*[' . $class_query('home_explore_content_button_book') . ']', $floor_section);
    $explore_set_text($explore_first('.//*[' . $class_query('btn-inner') . ']', $primary_button), $explore_option($prefix . 'primary_button', $defaults['primary']));
    $explore_set_text($explore_first('.//*[' . $class_query('btn-inner') . ']', $booking_button), $explore_option($prefix . 'booking_button', $defaults['booking']));
    if ($booking_button) {
        $booking_title_defaults = array(1 => 'ĐẶT BÀN ONLINE', 2 => 'ĐẶT LỊCH THUÊ', 3 => 'ĐẶT LỊCH WORKSHOP', 4 => 'ĐẶT LỊCH HẸN', 5 => 'ĐẶT CHỖ ONLINE');
        $booking_submit_defaults = array(1 => 'GỬI YÊU CẦU ĐẶT LỊCH', 2 => 'GỬI YÊU CẦU ĐẶT LỊCH', 3 => 'GỬI YÊU CẦU ĐẶT LỊCH', 4 => 'GỬI YÊU CẦU ĐẶT LỊCH', 5 => 'GỬI YÊU CẦU ĐẶT CHỖ');
        $booking_button->setAttribute('data-booking-title', $explore_option($prefix . 'booking_title', $booking_title_defaults[$floor]));
        $booking_button->setAttribute('data-booking-subtitle', $subtitle);
        $booking_button->setAttribute('data-booking-submit', $explore_option($prefix . 'booking_submit', $booking_submit_defaults[$floor]));
        $booking_button->setAttribute('data-booking-venue', $nav_title);
    }

    if (3 === $floor) {
        $product_filters_wrap = $explore_first('.//*[' . $class_query('workshop_catalog_filters') . ']', $floor_section);
        if ($product_filters_wrap) {
            while ($product_filters_wrap->firstChild) {
                $product_filters_wrap->removeChild($product_filters_wrap->firstChild);
            }
            $product_filters = array_merge(
                array(array('slug' => 'all', 'label' => 'TẤT CẢ')),
                wonom_get_home_workshop_categories()
            );
            foreach ($product_filters as $filter_index => $filter) {
                $filter_button = $explore_dom->createElement('button');
                $filter_button->setAttribute('class', 'workshop_filter btn txt_14 txt_bold' . (0 === $filter_index ? ' active' : ''));
                $filter_button->setAttribute('type', 'button');
                $filter_button->setAttribute('data-workshop-filter', $filter['slug']);
                $filter_inner = $explore_dom->createElement('span');
                $filter_inner->setAttribute('class', 'btn-inner');
                $filter_inner->appendChild($explore_dom->createTextNode($filter['label']));
                $filter_button->appendChild($filter_inner);
                $product_filters_wrap->appendChild($filter_button);
            }
        }

        $workshop_category_labels = array();
        foreach (wonom_get_home_workshop_categories() as $workshop_category) {
            $workshop_category_labels[$workshop_category['slug']] = $workshop_category['label'];
        }

        $workshops = $explore_option($prefix . 'workshops', array());
        $workshop_list = $explore_first('.//*[' . $class_query('workshop_catalog_list') . ']', $floor_section);
        $prototype = $workshop_list ? $explore_first('.//*[' . $class_query('workshop_card') . ']', $workshop_list) : null;

        if (is_array($workshops) && $workshops && $workshop_list && $prototype) {
            while ($workshop_list->firstChild) {
                $workshop_list->removeChild($workshop_list->firstChild);
            }
            foreach ($workshops as $index => $workshop) {
                $card = $prototype->cloneNode(true);
                $category = !empty($workshop['category']) ? sanitize_key($workshop['category']) : 'all';
                $workshop_tag = isset($workshop_category_labels[$category])
                    ? '#' . ltrim($workshop_category_labels[$category], '#')
                    : (!empty($workshop['tag']) ? sanitize_text_field($workshop['tag']) : '#' . $category);
                $workshop_title = isset($workshop['title']) ? $workshop['title'] : '';
                $card->setAttribute('data-product-id', 'workshop-' . $category . '-' . ($index + 1));
                $card->setAttribute('data-workshop-category', $category);
                $detail_images = array();
                if (!empty($workshop['detail_images']) && is_array($workshop['detail_images'])) {
                    foreach ($workshop['detail_images'] as $detail_image) {
                        $detail_image_url = $explore_image_url($detail_image);
                        if ($detail_image_url) {
                            $detail_images[] = $detail_image_url;
                        }
                    }
                }
                $card->setAttribute('data-detail-images', wp_json_encode($detail_images));
                $card->setAttribute('data-detail-content', isset($workshop['detail_content']) ? wp_kses_post($workshop['detail_content']) : '');
                $card->setAttribute('data-detail-includes', isset($workshop['detail_includes']) ? wp_kses_post($workshop['detail_includes']) : '');
                $card->setAttribute('data-detail-duration', isset($workshop['detail_duration']) ? sanitize_text_field($workshop['detail_duration']) : '60-90 phút');
                $image_node = $explore_first('.//*[' . $class_query('workshop_card_image') . ']//img', $card);
                $image_url = $explore_image_url(isset($workshop['image']) ? $workshop['image'] : '');
                if ($image_node && $image_url) {
                    $image_node->setAttribute('src', $image_url);
                    $image_node->setAttribute('alt', !empty($workshop['image_alt']) ? $workshop['image_alt'] : $workshop_title);
                }
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_tag') . ']', $card), $workshop_tag);
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_title') . ']', $card), $workshop_title);
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_description') . ']', $card), isset($workshop['description']) ? $workshop['description'] : '');
                $explore_set_text($explore_first('.//*[' . $class_query('workshop_card_price') . ']', $card), isset($workshop['price']) ? $workshop['price'] : '');
                $workshop_list->appendChild($card);
            }
        }
    } else {
        $gallery = $explore_option($prefix . 'gallery', array());
        $gallery_wrap = $explore_first('.//*[' . $class_query('home_explore_list_inner') . ']', $floor_section);

        if (is_array($gallery) && $gallery && $gallery_wrap) {
            while ($gallery_wrap->firstChild) {
                $gallery_wrap->removeChild($gallery_wrap->firstChild);
            }
            foreach ($gallery as $gallery_image) {
                $image_url = $explore_image_url(isset($gallery_image['image']) ? $gallery_image['image'] : '');
                if (!$image_url) {
                    continue;
                }
                $slide = $explore_dom->createElement('div');
                $slide->setAttribute('class', 'home_explore_list_item swiper-slide img_abs');
                $image = $explore_dom->createElement('img');
                $image->setAttribute('src', $image_url);
                $image->setAttribute('alt', isset($gallery_image['alt']) ? $gallery_image['alt'] : '');
                $image->setAttribute('loading', 'lazy');
                $slide->appendChild($image);
                $gallery_wrap->appendChild($slide);
            }
        }
    }
}

$explore_section = $explore_dom->getElementById('explore');
$explore_output = $explore_section ? $explore_dom->saveHTML($explore_section) : $explore_markup;
$explore_asset_url = trailingslashit(get_template_directory_uri()) . 'asset/';
$explore_output = preg_replace('/(?<=["\'])(?:\.\/|\/)?asset\//', $explore_asset_url, $explore_output);

echo $explore_output; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
